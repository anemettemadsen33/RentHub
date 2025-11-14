# RentHub Navigation System

## Overview

The RentHub Navigation System is a comprehensive, modular navigation solution built with React, Next.js, and TypeScript. It provides a flexible, role-based navigation system that automatically adapts to different user types and device sizes.

## Features

### 🎯 Core Features
- **Role-based navigation**: Automatically shows/hides navigation items based on user role (guest, user, landlord, admin)
- **Responsive design**: Seamlessly switches between desktop sidebar and mobile bottom navigation
- **State management**: Centralized state management using Zustand with persistence
- **Accessibility**: Full keyboard navigation and screen reader support
- **Performance**: Optimized with React.memo, code splitting, and lazy loading
- **Analytics**: Built-in navigation tracking and analytics

### 📱 Device Support
- **Desktop**: Collapsible sidebar navigation with sections
- **Mobile**: Bottom navigation bar with up to 5 primary items
- **Tablet**: Adaptive layout that switches between desktop and mobile modes

### 🎨 Customization
- **Themes**: Full dark/light mode support
- **Icons**: Lucide React icons with consistent styling
- **Animations**: Smooth transitions and hover effects
- **Badges**: Notification badges with pulse animations

## Architecture

### Component Structure
```
navigation/
├── NavigationContainer.tsx     # Main container and provider
├── DesktopNavigation.tsx       # Desktop sidebar component
├── BottomNavigation.tsx        # Mobile bottom navigation
├── NavigationItem.tsx          # Individual navigation item
├── store.ts                    # Zustand state management
├── config.ts                   # Navigation configuration
├── types.ts                    # TypeScript interfaces
├── utils.ts                    # Utility functions
└── __tests__/                  # Unit tests
```

### State Management
The system uses Zustand for state management with the following features:
- Persistent state (preferences and history)
- Optimized re-renders with selectors
- Async navigation actions
- Error handling and loading states
- Analytics tracking

### Navigation Configuration
Navigation items are configured with:
- **Role-based visibility**: Show/hide based on user role
- **Priority sorting**: Control item order in navigation
- **Section grouping**: Organize items into logical groups
- **Icons and labels**: Visual representation and accessibility
- **Badges**: Notification counts and status indicators

## Usage

### Basic Implementation

```tsx
import { NavigationProvider, useNavigation } from '@/components/navigation'

function App() {
  return (
    <NavigationProvider initialRole="guest" enableAnalytics={true}>
      <YourAppContent />
    </NavigationProvider>
  )
}
```

### Using Navigation Hooks

```tsx
import { useNavigation, useUserRole } from '@/components/navigation'

function MyComponent() {
  const { toggleNavigation, activeSection } = useNavigation()
  const { role, setRole } = useUserRole()
  
  return (
    <div>
      <p>Current role: {role}</p>
      <p>Active section: {activeSection}</p>
      <button onClick={() => setRole('user')}>Upgrade to User</button>
    </div>
  )
}
```

### Custom Navigation Items

```tsx
import { createNavItem } from '@/components/navigation'

const customItem = createNavItem({
  id: 'custom-item',
  label: 'Custom Item',
  href: '/custom',
  icon: MyIcon,
  section: 'custom',
  priority: 80,
  roles: ['user', 'admin'],
  badge: '5'
})
```

## API Reference

### Components

#### NavigationProvider
Provides navigation context to the application.

**Props:**
- `children`: React.ReactNode
- `initialRole?`: 'guest' | 'user' | 'landlord' | 'admin'
- `enableAnalytics?`: boolean

#### NavigationContainer
Main container that orchestrates desktop and mobile navigation.

**Props:**
- `children`: React.ReactNode
- `className?`: string
- `onNavigationChange?`: (path: string) => void
- `enableAnalytics?`: boolean

#### DesktopNavigation
Desktop sidebar navigation component.

**Props:**
- `className?`: string
- `compact?`: boolean
- `showLabels?`: boolean
- `collapsible?`: boolean

#### BottomNavigation
Mobile bottom navigation component.

**Props:**
- `className?`: string
- `showLabels?`: boolean
- `maxItems?`: number

### Hooks

#### useNavigation()
Main navigation hook providing access to all navigation state and actions.

**Returns:**
```typescript
{
  // State
  isOpen: boolean
  activeSection: string
  activeItem: NavItem | null
  userRole: UserRole
  isLoading: boolean
  error: string | null
  
  // Actions
  toggleNavigation: () => void
  openNavigation: () => void
  closeNavigation: () => void
  setActiveSection: (section: string) => void
  setActiveItem: (item: NavItem | null) => void
  navigateToItem: (item: NavItem) => Promise<void>
  setUserRole: (role: UserRole) => void
  
  // Preferences
  preferences: NavigationPreferences
  updatePreferences: (preferences: Partial<NavigationPreferences>) => void
  
  // Utility
  clearError: () => void
  resetNavigation: () => void
  trackNavigation: (action: string, metadata?: Record<string, any>) => void
}
```

#### useUserRole()
Hook for managing user role state.

**Returns:**
```typescript
{
  role: UserRole
  setRole: (role: UserRole) => void
}
```

#### useNavigationPreferences()
Hook for managing navigation preferences.

**Returns:**
```typescript
{
  preferences: NavigationPreferences
  updatePreferences: (preferences: Partial<NavigationPreferences>) => void
}
```

## Configuration

### Navigation Items
Navigation items are configured in `config.ts` with role-based access control:

```typescript
const navigationConfig = {
  guest: [
    {
      id: 'home',
      label: 'Home',
      href: '/',
      icon: HomeIcon,
      section: 'main',
      priority: 100,
      roles: ['guest', 'user', 'landlord', 'admin']
    },
    {
      id: 'properties',
      label: 'Properties',
      href: '/properties',
      icon: BuildingIcon,
      section: 'main',
      priority: 90,
      roles: ['guest', 'user', 'landlord', 'admin']
    }
  ],
  user: [
    // Additional items for authenticated users
  ],
  landlord: [
    // Landlord-specific navigation
  ],
  admin: [
    // Admin-only navigation items
  ]
}
```

### Performance Options
```typescript
const performanceOptions = {
  enableMemoization: true,
  enableVirtualization: false,
  maxVisibleItems: 50,
  debounceMs: 16, // ~60fps
  prefetchLinks: true
}
```

### Accessibility Options
```typescript
const accessibilityOptions = {
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
}
```

## Testing

### Unit Tests
```bash
npm run test -- tests/unit/navigation
```

### Integration Tests
```bash
npm run e2e -- complete-navigation
```

### Test Coverage
The navigation system includes comprehensive tests for:
- Component rendering and props
- State management functionality
- User role switching
- Responsive behavior
- Accessibility features
- Performance optimizations

## Browser Support

### Desktop Browsers
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Mobile Browsers
- iOS Safari 14+
- Chrome for Android 90+
- Samsung Internet 15+

## Performance

### Optimizations
- **React.memo**: All components are memoized for optimal re-renders
- **Code splitting**: Navigation components are lazy-loaded
- **Debounced updates**: Navigation state updates are debounced
- **Virtual scrolling**: Large navigation lists are virtualized
- **Prefetching**: Links are prefetched for faster navigation

### Bundle Size
- Core navigation: ~15KB gzipped
- State management: ~5KB gzipped
- Total impact: ~20KB gzipped

## Migration Guide

### From Legacy Navbar
The new navigation system is backward compatible. To migrate:

1. Replace `<Navbar />` with `<NavigationProvider />` in your layout
2. Update any direct navbar references to use navigation hooks
3. Configure navigation items in the new config system
4. Test responsive behavior across devices

### Gradual Migration
You can use both systems simultaneously during migration:
```tsx
// Old navbar (temporary)
import { Navbar } from '@/components/navbar'

// New navigation system
import { NavigationProvider } from '@/components/navigation'

function App() {
  return (
    <NavigationProvider>
      <Navbar /> {/* Legacy component */}
      {/* New navigation will be rendered by NavigationProvider */}
    </NavigationProvider>
  )
}
```

## Troubleshooting

### Common Issues

**Navigation not appearing**
- Ensure NavigationProvider wraps your application
- Check user role configuration
- Verify navigation items have correct roles

**Mobile navigation not working**
- Check viewport meta tag
- Ensure proper CSS classes are applied
- Test on actual mobile device

**Performance issues**
- Enable memoization in performance options
- Reduce number of navigation items
- Use virtualization for large lists

### Debug Mode
Enable debug mode to see navigation state:
```tsx
const { preferences } = useNavigationPreferences()

// Enable debug mode
preferences.debug = true
```

## Contributing

### Development Setup
```bash
# Install dependencies
npm install

# Run development server
npm run dev

# Run tests
npm run test

# Run E2E tests
npm run e2e
```

### Code Standards
- TypeScript for all components
- React hooks for state management
- Accessibility-first design
- Performance optimizations
- Comprehensive testing

## License

This navigation system is part of the RentHub project and follows the