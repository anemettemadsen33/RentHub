// Main components
export { NavigationContainer, NavigationProvider, useNavigation } from './NavigationContainer'
export { DesktopNavigation } from './DesktopNavigation'
export { BottomNavigation, AuthenticatedBottomNavigation, SmartBottomNavigation } from './BottomNavigation'
export { NavigationItem } from './NavigationItem'

// Store and hooks
export { 
  useNavigationStore, 
  useNavigationToggle, 
  useNavigationState, 
  useUserRole, 
  useNavigationPreferences,
  selectNavigationOpen,
  selectActiveSection,
  selectUserRole,
  selectPreferences,
  selectIsLoading,
  selectError
} from './store'

// Export types
export type {
  NavItem,
  NavSection,
  NavigationConfig,
  NavigationProps,
  NavigationItemProps,
  NavigationSectionProps,
  NavigationState,
  NavigationActions,
  UseNavigationReturn,
  NavigationUtils,
  UserRole,
  BottomNavItem,
  UserMenuProps,
  AuthMenuProps,
  NavigationPerformanceOptions,
  NavigationAccessibilityOptions,
} from './types'

// Utilities
export { 
  filterItemsByRole,
  sortItemsByPriority,
  groupItemsBySection,
  findItemById,
  findItemByHref,
  getNavItemClasses,
  validateNavItem,
  createNavItem,
  generateNavId,
  focusFirstItem,
  focusLastItem,
  focusNextItem,
  focusPrevItem,
  getItemIndex,
  debounceNavigation,
  throttleNavigation
} from './utils'

// Configuration
// Export config and utilities
export { getNavigationItems, navigationConfig } from './config'