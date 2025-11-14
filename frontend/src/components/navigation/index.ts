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

// Types
export type {
  NavItem,
  NavSection,
  NavigationConfig,
  NavigationState,
  NavigationActions,
  UserRole,
  NavigationPreferences,
  NavigationHistoryItem,
  NavigationItemClasses,
  NavigationUtils
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
export { getNavigationItems, createNavigationConfig } from './config'