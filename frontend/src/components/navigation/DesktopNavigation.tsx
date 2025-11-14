'use client'

import React, { useMemo, useState } from 'react'
import { cn } from '@/lib/utils'
import { NavigationItem } from './NavigationItem'
import { useNavigationToggle, useUserRole } from './store'
import { getNavigationItems } from './config'
import { filterItemsByRole, sortItemsByPriority, groupItemsBySection } from './utils'
import { ChevronLeft, Menu, X } from 'lucide-react'

interface DesktopNavigationProps {
  className?: string
  compact?: boolean
  showLabels?: boolean
  collapsible?: boolean
}

export const DesktopNavigation: React.FC<DesktopNavigationProps> = React.memo(({
  className,
  compact = false,
  showLabels = true,
  collapsible = true,
}) => {
  const { role } = useUserRole()
  const { isOpen, toggle, open, close } = useNavigationToggle()
  const [isCollapsed, setIsCollapsed] = useState(compact)

  // Get and organize navigation items
  const navigationData = useMemo(() => {
    const allItems = getNavigationItems(role)
    const filtered = filterItemsByRole(allItems, role)
    const sorted = sortItemsByPriority(filtered)
    const grouped = groupItemsBySection(sorted)
    
    return { items: sorted, grouped }
  }, [role])

  const toggleCollapse = () => {
    setIsCollapsed(!isCollapsed)
  }

  const navClasses = cn(
    'hidden md:flex flex-col h-full',
    'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700',
    'transition-all duration-300 ease-in-out',
    isCollapsed ? 'w-16' : 'w-64',
    className
  )

  const headerClasses = cn(
    'flex items-center justify-between p-4 border-b',
    'border-gray-200 dark:border-gray-700',
    isCollapsed && 'justify-center'
  )

  return (
    <nav className={navClasses} role="navigation" aria-label="Desktop navigation">
      {/* Header */}
      <div className={headerClasses}>
        {!isCollapsed && (
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
            Navigation
          </h2>
        )}
        {collapsible && (
          <button
            onClick={toggleCollapse}
            className={cn(
              'p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800',
              'text-gray-600 dark:text-gray-400',
              'transition-colors duration-200'
            )}
            aria-label={isCollapsed ? 'Expand navigation' : 'Collapse navigation'}
            title={isCollapsed ? 'Expand' : 'Collapse'}
          >
            <ChevronLeft className={cn(
              'w-4 h-4 transition-transform duration-300',
              isCollapsed && 'rotate-180'
            )} />
          </button>
        )}
      </div>

      {/* Navigation Items */}
      <div className="flex-1 overflow-y-auto py-4">
        {navigationData.grouped.map((section) => (
          <div key={section.id} className="mb-6">
            {!isCollapsed && section.label && (
              <h3 className={cn(
                'px-4 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                'transition-opacity duration-200',
                isCollapsed && 'opacity-0'
              )}>
                {section.label}
              </h3>
            )}
            <ul className="space-y-1">
              {section.items.map((item) => (
                <li key={item.id}>
                  <NavigationItem
                    item={item}
                    variant="desktop"
                    compact={isCollapsed}
                    showLabel={showLabels && !isCollapsed}
                  />
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>

      {/* Footer */}
      <div className="p-4 border-t border-gray-200 dark:border-gray-700">
        <div className={cn(
          'flex items-center',
          isCollapsed ? 'justify-center' : 'justify-between'
        )}>
          {!isCollapsed && (
            <span className="text-sm text-gray-600 dark:text-gray-400">
              {role.charAt(0).toUpperCase() + role.slice(1)}
            </span>
          )}
          <button
            onClick={() => isOpen ? close() : open()}
            className={cn(
              'p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800',
              'text-gray-600 dark:text-gray-400',
              'transition-colors duration-200'
            )}
            aria-label={isOpen ? 'Close navigation' : 'Open navigation'}
          >
            {isOpen ? <X className="w-4 h-4" /> : <Menu className="w-4 h-4" />}
          </button>
        </div>
      </div>
    </nav>
  )
})

DesktopNavigation.displayName = 'DesktopNavigation'