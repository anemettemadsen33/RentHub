'use client'

import React, { useEffect, useCallback } from 'react'
import { usePathname } from 'next/navigation'
import { cn } from '@/lib/utils'
import { DesktopNavigation } from './DesktopNavigation'
import { SmartBottomNavigation } from './BottomNavigation'
import { useNavigationStore, useUserRole } from './store'
import { getNavigationItems } from './config'

interface NavigationContainerProps {
  children: React.ReactNode
  className?: string
  onNavigationChange?: (path: string) => void
  enableAnalytics?: boolean
}

export const NavigationContainer: React.FC<NavigationContainerProps> = ({
  children,
  className,
  onNavigationChange,
  enableAnalytics = true,
}) => {
  const pathname = usePathname()
  const { setActiveSection, setActiveItem, trackNavigation } = useNavigationStore()
  const { role } = useUserRole()

  // Sync navigation state with current path
  useEffect(() => {
    const navigationItems = getNavigationItems(role)
    const currentItem = navigationItems.find(item => {
      if (item.exact) {
        return pathname === item.href
      }
      return pathname.startsWith(item.href)
    })

    if (currentItem) {
      setActiveItem(currentItem)
      setActiveSection(currentItem.section || 'home')
    } else {
      // Set default section based on path
      const pathSegments = pathname.split('/').filter(Boolean)
      const defaultSection = pathSegments[0] || 'home'
      setActiveSection(defaultSection)
      setActiveItem(null)
    }

    // Track navigation change
    if (enableAnalytics) {
      trackNavigation('page_view', { path: pathname })
    }

    // Call external handler
    if (onNavigationChange) {
      onNavigationChange(pathname)
    }
  }, [pathname, role, setActiveSection, setActiveItem, trackNavigation, onNavigationChange, enableAnalytics])

  // Handle keyboard navigation
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      // Alt + M to toggle navigation
      if (event.altKey && event.key === 'm') {
        event.preventDefault()
        // Toggle mobile navigation
        const mobileNavButton = document.querySelector('[aria-label*="navigation"]') as HTMLButtonElement
        if (mobileNavButton) {
          mobileNavButton.click()
        }
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [])

  // Handle resize events
  useEffect(() => {
    const handleResize = () => {
      // Close mobile navigation on desktop
      if (window.innerWidth >= 768) {
        const { isOpen, close } = useNavigationStore.getState()
        if (isOpen) {
          close()
        }
      }
    }

    window.addEventListener('resize', handleResize)
    return () => window.removeEventListener('resize', handleResize)
  }, [])

  return (
    <div className={cn('min-h-screen flex flex-col md:flex-row', className)}>
      {/* Desktop Navigation */}
      <DesktopNavigation />
      
      {/* Main Content */}
      <main className="flex-1 md:pl-64 pb-16 md:pb-0">
        {children}
      </main>
      
      {/* Bottom Navigation for Mobile */}
      <SmartBottomNavigation />
    </div>
  )
}

// Hook for navigation management
export const useNavigation = () => {
  const store = useNavigationStore()
  const { role } = useUserRole()
  
  return {
    // State
    isOpen: store.isOpen,
    activeSection: store.activeSection,
    activeItem: store.activeItem,
    userRole: role,
    isLoading: store.isLoading,
    error: store.error,
    
    // Actions
    toggleNavigation: store.toggleNavigation,
    openNavigation: store.openNavigation,
    closeNavigation: store.closeNavigation,
    setActiveSection: store.setActiveSection,
    setActiveItem: store.setActiveItem,
    navigateToItem: store.navigateToItem,
    setUserRole: store.setUserRole,
    
    // Preferences
    preferences: store.preferences,
    updatePreferences: store.updatePreferences,
    
    // Utility
    clearError: store.clearError,
    resetNavigation: store.resetNavigation,
    trackNavigation: store.trackNavigation,
  }
}

// Provider component for easier integration
export const NavigationProvider: React.FC<{
  children: React.ReactNode
  initialRole?: 'guest' | 'user' | 'landlord' | 'admin'
  enableAnalytics?: boolean
}> = ({ 
  children, 
  initialRole = 'guest',
  enableAnalytics = true 
}) => {
  const { setUserRole } = useNavigationStore()
  
  useEffect(() => {
    setUserRole(initialRole)
  }, [initialRole, setUserRole])
  
  return (
    <NavigationContainer enableAnalytics={enableAnalytics}>
      {children}
    </NavigationContainer>
  )
}