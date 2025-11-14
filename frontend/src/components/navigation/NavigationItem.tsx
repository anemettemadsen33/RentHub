'use client'

import React, { useCallback, useMemo } from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { cn } from '@/lib/utils'
import { NavItem } from './types'
import { useNavigationStore } from './store'
import { getNavItemClasses } from './utils'

interface NavigationItemProps {
  item: NavItem
  variant?: 'desktop' | 'mobile' | 'bottom'
  compact?: boolean
  showLabel?: boolean
  onClick?: () => void
}

export const NavigationItem: React.FC<NavigationItemProps> = React.memo(({
  item,
  variant = 'desktop',
  compact = false,
  showLabel = true,
  onClick,
}) => {
  const pathname = usePathname()
  const { navigateToItem, activeItem, isLoading } = useNavigationStore()
  
  const isActive = useMemo(() => {
    if (item.exact) {
      return pathname === item.href
    }
    return pathname.startsWith(item.href)
  }, [pathname, item.href, item.exact])

  const isDisabled = item.disabled || isLoading

  const handleClick = useCallback(async (e: React.MouseEvent) => {
    if (isDisabled) {
      e.preventDefault()
      return
    }

    // Track navigation analytics
    if (typeof window !== 'undefined' && window.dataLayer) {
      window.dataLayer.push({
        event: 'navigation_item_click',
        item_id: item.id,
        item_label: item.label,
        item_href: item.href,
        variant,
      })
    }

    // Handle custom onClick
    if (onClick) {
      onClick()
      return
    }

    // Handle navigation
    if (item.onClick) {
      e.preventDefault()
      await navigateToItem(item)
      item.onClick()
    } else {
      await navigateToItem(item)
    }
  }, [item, isDisabled, onClick, navigateToItem, variant])

  const itemClasses = useMemo(() => {
    return getNavItemClasses({
      variant,
      isActive,
      isDisabled,
      compact,
      hasIcon: !!item.icon,
      hasLabel: showLabel && !!item.label,
    })
  }, [variant, isActive, isDisabled, compact, showLabel, item.icon, item.label])

  const content = useMemo(() => {
    const iconElement = item.icon && (
      <item.icon 
        className={cn(
          'flex-shrink-0',
          variant === 'bottom' ? 'w-5 h-5' : 'w-4 h-4',
          item.badge && 'relative'
        )}
        aria-hidden="true"
      />
    )

    const labelElement = showLabel && item.label && (
      <span className={cn(
        'transition-all duration-200',
        variant === 'bottom' ? 'text-xs mt-1' : 'text-sm',
        compact && variant === 'desktop' && 'opacity-0 w-0 overflow-hidden'
      )}>
        {item.label}
      </span>
    )

    const badgeElement = item.badge && (
      <span className={cn(
        'absolute -top-1 -right-1',
        'bg-red-500 text-white text-xs rounded-full',
        'w-4 h-4 flex items-center justify-center',
        'animate-pulse'
      )}>
        {item.badge}
      </span>
    )

    if (variant === 'bottom') {
      return (
        <div className="flex flex-col items-center justify-center">
          <div className="relative">
            {iconElement}
            {badgeElement}
          </div>
          {labelElement}
        </div>
      )
    }

    return (
      <div className={cn(
        'flex items-center',
        variant === 'mobile' ? 'justify-between' : 'justify-start'
      )}>
        <div className="relative">
          {iconElement}
          {badgeElement}
        </div>
        {labelElement}
        {variant === 'mobile' && item.children && (
          <svg
            className={cn(
              'w-4 h-4 transition-transform duration-200',
              isActive && 'rotate-90'
            )}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        )}
      </div>
    )
  }, [item, variant, isActive, showLabel, compact])

  const linkProps = {
    href: item.href,
    className: itemClasses,
    onClick: handleClick,
    'aria-label': item.ariaLabel || item.label,
    'aria-disabled': isDisabled,
    'aria-current': isActive ? 'page' : undefined,
    title: item.tooltip || item.label,
  }

  // Render as button if no href or has onClick
  if (!item.href || item.onClick) {
    return (
      <button
        {...linkProps}
        type="button"
        disabled={isDisabled}
        className={cn(linkProps.className, 'w-full text-left')}
      >
        {content}
      </button>
    )
  }

  return (
    <Link {...linkProps}>
      {content}
    </Link>
  )
})

NavigationItem.displayName = 'NavigationItem'