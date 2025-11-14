'use client'

import React, { useMemo } from 'react'
import { cn } from '@/lib/utils'
import { NavigationItem } from './NavigationItem'
import { useUserRole } from './store'
import { getNavigationItems } from './config'
import { filterItemsByRole, sortItemsByPriority } from './utils'

interface BottomNavigationProps {
  className?: string
  showLabels?: boolean
  maxItems?: number
}

export const BottomNavigation: React.FC<BottomNavigationProps> = React.memo(({
  className,
  showLabels = true,
  maxItems = 5,
}) => {
  const { role } = useUserRole()
  
  // Get navigation items for current user role
  const navigationItems = useMemo(() => {
    const allItems = getNavigationItems(role)
    const filtered = filterItemsByRole(allItems, role)
    const sorted = sortItemsByPriority(filtered)
    
    // For bottom navigation, we want primary items only
    const primaryItems = sorted.filter(item => 
      item.priority >= 80 && !item.hideFromBottomNav
    )
    
    // Limit to maxItems
    return primaryItems.slice(0, maxItems)
  }, [role, maxItems])

  if (navigationItems.length === 0) {
    return null
  }

  return (
    <nav 
      className={cn(
        'fixed bottom-0 left-0 right-0 z-50',
        'bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700',
        'md:hidden', // Hide on desktop
        'safe-area-pb', // iOS safe area
        className
      )}
      role="navigation"
      aria-label="Bottom navigation"
    >
      <div className="flex items-center justify-around h-16 px-2">
        {navigationItems.map((item, index) => (
          <NavigationItem
            key={`${item.id}-${index}`}
            item={item}
            variant="bottom"
            showLabel={showLabels}
            compact={true}
          />
        ))}
      </div>
    </nav>
  )
})

BottomNavigation.displayName = 'BottomNavigation'

// Component for authenticated users with additional features
export const AuthenticatedBottomNavigation: React.FC<BottomNavigationProps> = React.memo(({
  className,
  showLabels = true,
  maxItems = 5,
}) => {
  const { role } = useUserRole()
  
  const navigationItems = useMemo(() => {
    const allItems = getNavigationItems(role)
    const filtered = filterItemsByRole(allItems, role)
    const sorted = sortItemsByPriority(filtered)
    
    // Include both primary and secondary items for authenticated users
    const items = sorted.filter(item => 
      item.priority >= 60 && !item.hideFromBottomNav
    )
    
    return items.slice(0, maxItems)
  }, [role, maxItems])

  if (navigationItems.length === 0) {
    return null
  }

  return (
    <nav 
      className={cn(
        'fixed bottom-0 left-0 right-0 z-50',
        'bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700',
        'md:hidden shadow-lg',
        'safe-area-pb',
        className
      )}
      role="navigation"
      aria-label="Authenticated bottom navigation"
    >
      <div className="flex items-center justify-around h-16 px-2">
        {navigationItems.map((item, index) => (
          <div key={`${item.id}-${index}`} className="relative flex-1">
            <NavigationItem
              item={item}
              variant="bottom"
              showLabel={showLabels}
              compact={true}
            />
          </div>
        ))}
      </div>
    </nav>
  )
})

AuthenticatedBottomNavigation.displayName = 'AuthenticatedBottomNavigation'

// Wrapper component that automatically shows the right navigation based on user role
export const SmartBottomNavigation: React.FC<BottomNavigationProps> = React.memo((props) => {
  const { role } = useUserRole()
  
  // For guest users, show basic navigation
  if (role === 'guest') {
    return <BottomNavigation {...props} />
  }
  
  // For authenticated users, show enhanced navigation
  return <AuthenticatedBottomNavigation {...props} />
})

SmartBottomNavigation.displayName = 'SmartBottomNavigation'