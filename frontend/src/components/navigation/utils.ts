/**
 * Navigation Utilities
 * Helper functions for navigation logic and filtering
 */

import { usePathname } from 'next/navigation';
import type { 
  NavItem, 
  NavSection, 
  UserRole, 
  NavigationUtils,
  NavigationPerformanceOptions,
  NavigationAccessibilityOptions 
} from './types';

/**
 * Check if user has required role for navigation item
 */
export const hasRole = (userRoles: UserRole[], requiredRoles: UserRole[]): boolean => {
  if (requiredRoles.includes('guest')) return true;
  return requiredRoles.some(role => userRoles.includes(role));
};

/**
 * Check if navigation item is visible for user role
 */
export const isItemVisible = (item: NavItem, userRole: UserRole): boolean => {
  return item.roles.includes(userRole);
};

/**
 * Check if navigation section is visible for user role
 */
export const isSectionVisible = (section: NavSection, userRole: UserRole): boolean => {
  return section.roles.includes(userRole);
};

/**
 * Get active navigation item from list
 */
export const getActiveItem = (items: NavItem[], currentPath: string): NavItem | undefined => {
  // Exact match first
  let activeItem = items.find(item => item.href === currentPath);
  
  // Then prefix match for nested routes
  if (!activeItem) {
    activeItem = items.find(item => 
      currentPath.startsWith(item.href) && item.href !== '/'
    );
  }
  
  // Home as fallback for root
  if (!activeItem && currentPath === '/') {
    activeItem = items.find(item => item.href === '/');
  }
  
  return activeItem;
};

/**
 * Filter items by user role
 */
export const filterByRole = <T extends { roles: UserRole[] }>(
  items: T[], 
  userRole: UserRole
): T[] => {
  return items.filter(item => item.roles.includes(userRole));
};

/**
 * Sort items by priority for specific variant
 */
export const sortByPriority = <T extends { 
  mobile?: { priority?: number }; 
  desktop?: { priority?: number } 
}>(
  items: T[],
  variant: 'mobile' | 'desktop'
): T[] => {
  return [...items].sort((a, b) => {
    const priorityA = (variant === 'mobile' ? a.mobile?.priority : a.desktop?.priority) || 0;
    const priorityB = (variant === 'mobile' ? b.mobile?.priority : b.desktop?.priority) || 0;
    return priorityB - priorityA; // Higher priority first
  });
};

/**
 * Get visible navigation items for user role and variant
 */
export const getVisibleItems = (
  items: NavItem[],
  userRole: UserRole,
  variant: 'mobile' | 'desktop',
  limit?: number
): NavItem[] => {
  const visible = items.filter(item => isItemVisible(item, userRole));
  const sorted = sortByPriority(visible, variant);
  return limit ? sorted.slice(0, limit) : sorted;
};

/**
 * Get visible navigation sections for user role
 */
export const getVisibleSections = (
  sections: NavSection[],
  userRole: UserRole,
  variant: 'mobile' | 'desktop'
): NavSection[] => {
  const visibleSections = sections.filter(section => 
    isSectionVisible(section, userRole) && 
    (variant === 'mobile' ? section.mobile?.showSection : section.desktop?.showSection)
  );
  
  return visibleSections.map(section => ({
    ...section,
    items: getVisibleItems(section.items, userRole, variant)
  }));
};

/**
 * Get bottom navigation items (max 5 items)
 */
export const getBottomNavItems = (
  items: NavItem[],
  userRole: UserRole,
  badgeCounts: Record<string, number> = {}
): NavItem[] => {
  const visibleItems = getVisibleItems(items, userRole, 'mobile');
  const sortedItems = sortByPriority(visibleItems, 'mobile');
  
  return sortedItems
    .slice(0, 5) // Max 5 items for bottom navigation
    .map(item => ({
      ...item,
      badge: badgeCounts[item.id] ? {
        count: badgeCounts[item.id],
        variant: item.id === 'notifications' ? 'destructive' : 'default'
      } : undefined
    }));
};

/**
 * Navigation utilities object
 */
export const navigationUtils: NavigationUtils = {
  isItemVisible,
  isSectionVisible,
  getActiveItem,
  filterByRole,
  sortByPriority
};

/**
 * Default performance options
 */
export const defaultPerformanceOptions: NavigationPerformanceOptions = {
  enableMemoization: true,
  enableVirtualization: false,
  maxVisibleItems: 50,
  debounceMs: 16, // ~60fps
  prefetchLinks: true
};

/**
 * Default accessibility options
 */
export const defaultAccessibilityOptions: NavigationAccessibilityOptions = {
  enableKeyboardNavigation: true,
  enableScreenReaderSupport: true,
  enableFocusManagement: true,
  enableReducedMotion: false,
  ariaLabels: {
    mainNavigation: 'Main navigation',
    userMenu: 'User menu',
    mobileMenu: 'Mobile menu',
    bottomNavigation: 'Bottom navigation'
  }
};

/**
 * Keyboard navigation key mappings
 */
export const keyboardShortcuts = {
  ESCAPE: 'Escape',
  ARROW_UP: 'ArrowUp',
  ARROW_DOWN: 'ArrowDown',
  ARROW_LEFT: 'ArrowLeft',
  ARROW_RIGHT: 'ArrowRight',
  ENTER: 'Enter',
  SPACE: ' ',
  TAB: 'Tab',
  HOME: 'Home',
  END: 'End'
};

/**
 * Focus management utilities
 */
export const focusManagement = {
  /**
   * Get focusable elements within container
   */
  getFocusableElements: (container: HTMLElement): HTMLElement[] => {
    const focusableSelectors = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable="true"]'
    ].join(', ');
    
    return Array.from(container.querySelectorAll(focusableSelectors)) as HTMLElement[];
  },

  /**
   * Trap focus within container
   */
  trapFocus: (container: HTMLElement, event: KeyboardEvent): void => {
    const focusableElements = focusManagement.getFocusableElements(container);
    if (focusableElements.length === 0) return;
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    if (event.key === keyboardShortcuts.TAB) {
      if (event.shiftKey) {
        if (document.activeElement === firstElement) {
          lastElement.focus();
          event.preventDefault();
        }
      } else {
        if (document.activeElement === lastElement) {
          firstElement.focus();
          event.preventDefault();
        }
      }
    }
  },

  /**
   * Set initial focus
   */
  setInitialFocus: (container: HTMLElement, selector?: string): void => {
    const focusableElements = focusManagement.getFocusableElements(container);
    if (focusableElements.length === 0) return;
    
    let elementToFocus: HTMLElement;
    
    if (selector) {
      elementToFocus = container.querySelector(selector) as HTMLElement || focusableElements[0];
    } else {
      elementToFocus = focusableElements[0];
    }
    
    elementToFocus.focus();
  }
};

/**
 * Performance optimization utilities
 */
export const performanceUtils = {
  /**
   * Debounce function
   */
  debounce: <T extends (...args: any[]) => any>(
    func: T,
    wait: number
  ): ((...args: Parameters<T>) => void) => {
    let timeout: NodeJS.Timeout;
    return (...args: Parameters<T>) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  },

  /**
   * Throttle function
   */
  throttle: <T extends (...args: any[]) => any>(
    func: T,
    limit: number
  ): ((...args: Parameters<T>) => void) => {
    let inThrottle: boolean;
    return (...args: Parameters<T>) => {
      if (!inThrottle) {
        func(...args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  },

  /**
   * Prefetch link
   */
  prefetchLink: (href: string): void => {
    if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
      const link = document.createElement('link');
      link.rel = 'prefetch';
      link.href = href;
      document.head.appendChild(link);
    }
  }
};

/**
 * Utility functions for navigation state
 */
export const stateUtils = {
  /**
   * Check if path is active
   */
  isActivePath: (currentPath: string, targetPath: string, exact = false): boolean => {
    if (exact) {
      return currentPath === targetPath;
    }
    return currentPath === targetPath || currentPath.startsWith(targetPath + '/');
  },

  /**
   * Get relative path from URL
   */
  getRelativePath: (url: string): string => {
    try {
      const urlObj = new URL(url, window.location.origin);
      return urlObj.pathname;
    } catch {
      return url;
    }
  },

  /**
   * Normalize path
   */
  normalizePath: (path: string): string => {
    return path.replace(/\/+/g, '/').replace(/\/$/, '') || '/';
  }
};

/**
 * Filter items by user role (alias for filterByRole)
 */
export const filterItemsByRole = filterByRole;

/**
 * Sort items by priority (alias for sortByPriority)
 */
export const sortItemsByPriority = sortByPriority;

/**
 * Group items by section
 */
export const groupItemsBySection = (items: NavItem[]): { id: string; label: string; items: NavItem[] }[] => {
  const groups: Record<string, NavItem[]> = {};
  
  items.forEach(item => {
    const section = item.section || 'default';
    if (!groups[section]) {
      groups[section] = [];
    }
    groups[section].push(item);
  });
  
  return Object.entries(groups).map(([id, items]) => ({
    id,
    label: id.charAt(0).toUpperCase() + id.slice(1),
    items
  }));
};

/**
 * Find item by ID
 */
export const findItemById = (items: NavItem[], id: string): NavItem | undefined => {
  return items.find(item => item.id === id);
};

/**
 * Find item by href
 */
export const findItemByHref = (items: NavItem[], href: string): NavItem | undefined => {
  return items.find(item => item.href === href);
};

/**
 * Get navigation item CSS classes
 */
export const getNavItemClasses = (options: {
  variant?: 'desktop' | 'mobile' | 'bottom';
  isActive?: boolean;
  isDisabled?: boolean;
  compact?: boolean;
  hasIcon?: boolean;
  hasLabel?: boolean;
}): string => {
  const {
    variant = 'desktop',
    isActive = false,
    isDisabled = false,
    compact = false,
    hasIcon = false,
    hasLabel = false
  } = options;

  const baseClasses = [
    'flex items-center justify-center',
    'rounded-lg transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
    'hover:bg-gray-100 dark:hover:bg-gray-800',
    'text-gray-700 dark:text-gray-300'
  ];

  if (isActive) {
    baseClasses.push('bg-primary/10 text-primary font-semibold');
  }

  if (isDisabled) {
    baseClasses.push('opacity-50 cursor-not-allowed');
  }

  if (variant === 'bottom') {
    baseClasses.push('flex-col h-16 flex-1 px-2 py-1');
  } else {
    baseClasses.push('h-10 px-3 gap-2');
  }

  if (compact) {
    baseClasses.push('w-10');
  } else {
    baseClasses.push('w-full');
  }

  return baseClasses.join(' ');
};

/**
 * Validate navigation item
 */
export const validateNavItem = (item: Partial<NavItem>): string[] => {
  const errors: string[] = [];
  
  if (!item.id) errors.push('ID is required');
  if (!item.label) errors.push('Label is required');
  if (!item.href && !item.onClick) errors.push('Either href or onClick is required');
  if (!item.roles || item.roles.length === 0) errors.push('At least one role is required');
  
  return errors;
};

/**
 * Create navigation item
 */
export const createNavItem = (item: Partial<NavItem>): NavItem => {
  const errors = validateNavItem(item);
  if (errors.length > 0) {
    throw new Error(`Invalid navigation item: ${errors.join(', ')}`);
  }
  
  return {
    id: item.id!,
    label: item.label!,
    href: item.href || '#',
    icon: item.icon,
    section: item.section || 'default',
    priority: item.priority || 50,
    roles: item.roles || ['guest'],
    exact: item.exact || false,
    disabled: item.disabled || false,
    hideFromBottomNav: item.hideFromBottomNav || false,
    ariaLabel: item.ariaLabel,
    tooltip: item.tooltip,
    onClick: item.onClick,
    children: item.children
  };
};

/**
 * Generate navigation ID
 */
export const generateNavId = (label: string, prefix?: string): string => {
  const cleanLabel = label.toLowerCase().replace(/[^a-z0-9]+/g, '-');
  return prefix ? `${prefix}-${cleanLabel}` : cleanLabel;
};

/**
 * Focus management functions
 */
export const focusFirstItem = (container: HTMLElement): void => {
  const firstItem = container.querySelector('[role="menuitem"], a, button') as HTMLElement;
  firstItem?.focus();
};

export const focusLastItem = (container: HTMLElement): void => {
  const items = container.querySelectorAll('[role="menuitem"], a, button');
  const lastItem = items[items.length - 1] as HTMLElement;
  lastItem?.focus();
};

export const focusNextItem = (container: HTMLElement): void => {
  const activeElement = document.activeElement as HTMLElement;
  const items = Array.from(container.querySelectorAll('[role="menuitem"], a, button'));
  const currentIndex = items.indexOf(activeElement);
  
  if (currentIndex !== -1 && currentIndex < items.length - 1) {
    (items[currentIndex + 1] as HTMLElement).focus();
  } else {
    focusFirstItem(container);
  }
};

export const focusPrevItem = (container: HTMLElement): void => {
  const activeElement = document.activeElement as HTMLElement;
  const items = Array.from(container.querySelectorAll('[role="menuitem"], a, button'));
  const currentIndex = items.indexOf(activeElement);
  
  if (currentIndex > 0) {
    (items[currentIndex - 1] as HTMLElement).focus();
  } else {
    focusLastItem(container);
  }
};

export const getItemIndex = (container: HTMLElement, element: HTMLElement): number => {
  const items = Array.from(container.querySelectorAll('[role="menuitem"], a, button'));
  return items.indexOf(element);
};

/**
 * Debounced navigation function
 */
export const debounceNavigation = (func: Function, delay: number = 300): Function => {
  let timeoutId: NodeJS.Timeout;
  return (...args: any[]) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func(...args), delay);
  };
};

/**
 * Throttled navigation function
 */
export const throttleNavigation = (func: Function, limit: number = 100): Function => {
  let inThrottle: boolean;
  return (...args: any[]) => {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

/**
 * Export all utilities
 */
export default {
  hasRole,
  isItemVisible,
  isSectionVisible,
  getActiveItem,
  filterByRole,
  sortByPriority,
  getVisibleItems,
  getVisibleSections,
  getBottomNavItems,
  navigationUtils,
  defaultPerformanceOptions,
  defaultAccessibilityOptions,
  keyboardShortcuts,
  focusManagement,
  performanceUtils,
  stateUtils
};