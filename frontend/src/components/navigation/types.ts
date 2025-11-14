/**
 * Navigation Types and Interfaces
 * Defines the complete structure for modular navigation system
 */

import { ReactNode } from 'react';
import { LucideIcon } from 'lucide-react';

/**
 * User roles for navigation permissions
 */
export type UserRole = 'guest' | 'user' | 'landlord' | 'admin';

/**
 * Navigation item structure
 */
export interface NavItem {
  id: string;
  label: string;
  href: string;
  icon: LucideIcon;
  description?: string;
  roles: UserRole[];
  badge?: {
    count: number;
    maxCount?: number;
    variant?: 'default' | 'destructive' | 'secondary';
  };
  mobile?: {
    priority: number; // 1-10, higher = more important
    showLabel?: boolean;
  };
  desktop?: {
    priority: number;
    showLabel?: boolean;
  };
  submenu?: NavItem[];
  external?: boolean;
  target?: string;
}

/**
 * Navigation section structure
 */
export interface NavSection {
  id: string;
  title: string;
  items: NavItem[];
  roles: UserRole[];
  mobile?: {
    showSection: boolean;
    collapsible?: boolean;
  };
  desktop?: {
    showSection: boolean;
    orientation?: 'horizontal' | 'vertical';
  };
}

/**
 * Navigation configuration
 */
export interface NavigationConfig {
  sections: NavSection[];
  userMenu: NavItem[];
  authMenu: NavItem[];
  footer: {
    copyright: string;
    links: NavItem[];
  };
}

/**
 * Navigation component props
 */
export interface NavigationProps {
  variant?: 'desktop' | 'mobile' | 'bottom';
  className?: string;
  onNavigate?: (href: string) => void;
}

/**
 * Navigation item component props
 */
export interface NavigationItemProps {
  item: NavItem;
  variant: 'desktop' | 'mobile' | 'bottom';
  isActive?: boolean;
  onClick?: () => void;
  className?: string;
}

/**
 * Navigation section component props
 */
export interface NavigationSectionProps {
  section: NavSection;
  variant: 'desktop' | 'mobile' | 'bottom';
  className?: string;
  onNavigate?: (href: string) => void;
}

/**
 * Bottom navigation specific types
 */
export interface BottomNavItem extends NavItem {
  mobile: {
    priority: number;
    showLabel: boolean;
  };
}

/**
 * User menu props
 */
export interface UserMenuProps {
  user: {
    id: string;
    name: string;
    email: string;
    role: UserRole;
    avatar?: string;
  };
  onLogout: () => void;
  onNavigate?: (href: string) => void;
  className?: string;
}

/**
 * Auth menu props
 */
export interface AuthMenuProps {
  onNavigate?: (href: string) => void;
  className?: string;
}

/**
 * Navigation state management
 */
export interface NavigationState {
  activePath: string;
  expandedSections: string[];
  mobileMenuOpen: boolean;
  userMenuOpen: boolean;
}

/**
 * Navigation actions
 */
export interface NavigationActions {
  setActivePath: (path: string) => void;
  toggleSection: (sectionId: string) => void;
  toggleMobileMenu: () => void;
  toggleUserMenu: () => void;
  closeAllMenus: () => void;
}

/**
 * Hook return type for navigation
 */
export interface UseNavigationReturn {
  state: NavigationState;
  actions: NavigationActions;
  config: NavigationConfig;
  visibleSections: NavSection[];
  visibleItems: NavItem[];
  isLoading: boolean;
}

/**
 * Navigation utilities
 */
export interface NavigationUtils {
  isItemVisible: (item: NavItem, userRole: UserRole) => boolean;
  isSectionVisible: (section: NavSection, userRole: UserRole) => boolean;
  getActiveItem: (items: NavItem[], currentPath: string) => NavItem | undefined;
  filterByRole: <T extends { roles: UserRole[] }>(items: T[], userRole: UserRole) => T[];
  sortByPriority: <T extends { mobile?: { priority?: number }; desktop?: { priority?: number } }>(
    items: T[],
    variant: 'mobile' | 'desktop'
  ) => T[];
}

/**
 * Performance optimization options
 */
export interface NavigationPerformanceOptions {
  enableMemoization: boolean;
  enableVirtualization: boolean;
  maxVisibleItems: number;
  debounceMs: number;
  prefetchLinks: boolean;
}

/**
 * Accessibility options
 */
export interface NavigationAccessibilityOptions {
  enableKeyboardNavigation: boolean;
  enableScreenReaderSupport: boolean;
  enableFocusManagement: boolean;
  enableReducedMotion: boolean;
  ariaLabels: {
    mainNavigation: string;
    userMenu: string;
    mobileMenu: string;
    bottomNavigation: string;
  };
}