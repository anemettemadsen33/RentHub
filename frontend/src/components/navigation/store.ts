'use client'

import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import { NavigationState, NavigationActions, UserRole, NavItem } from './types'

const initialState: NavigationState = {
  isOpen: false,
  activeSection: 'home',
  activeItem: null,
  userRole: 'guest',
  isLoading: false,
  error: null,
  history: [],
  preferences: {
    autoCollapse: true,
    showLabels: true,
    compactMode: false,
    animations: true,
  },
}

export const useNavigationStore = create<NavigationState & NavigationActions>()(
  persist(
    (set, get) => ({
      ...initialState,

      // Actions
      toggleNavigation: () => {
        const { isOpen } = get()
        set({ isOpen: !isOpen })
      },

      openNavigation: () => set({ isOpen: true }),
      
      closeNavigation: () => set({ isOpen: false }),

      setActiveSection: (section: string) => {
        const { history } = get()
        const newHistory = [...history, { section, timestamp: Date.now() }].slice(-10)
        set({ 
          activeSection: section,
          history: newHistory
        })
      },

      setActiveItem: (item: NavItem | null) => {
        set({ activeItem: item })
      },

      setUserRole: (role: UserRole) => {
        set({ userRole: role })
      },

      setLoading: (loading: boolean) => {
        set({ isLoading: loading })
      },

      setError: (error: string | null) => {
        set({ error })
      },

      clearError: () => {
        set({ error: null })
      },

      updatePreferences: (preferences: Partial<NavigationState['preferences']>) => {
        const currentPrefs = get().preferences
        set({ 
          preferences: { ...currentPrefs, ...preferences }
        })
      },

      clearHistory: () => {
        set({ history: [] })
      },

      resetNavigation: () => {
        set({ ...initialState })
      },

      // Async actions
      navigateToItem: async (item: NavItem) => {
        const { setActiveItem, setActiveSection, setLoading, setError } = get()
        
        try {
          setLoading(true)
          setError(null)
          
          // Simulate navigation delay for async operations
          await new Promise(resolve => setTimeout(resolve, 100))
          
          setActiveItem(item)
          setActiveSection(item.section || 'home')
          
          // Close mobile navigation after navigation
          if (window.innerWidth < 768) {
            set({ isOpen: false })
          }
          
        } catch (error) {
          setError(error instanceof Error ? error.message : 'Navigation failed')
        } finally {
          setLoading(false)
        }
      },

      // Performance optimization
      batchUpdate: (updates: Partial<NavigationState>) => {
        set(updates)
      },

      // Analytics
      trackNavigation: (action: string, metadata?: Record<string, any>) => {
        if (typeof window !== 'undefined' && window.dataLayer) {
          window.dataLayer.push({
            event: 'navigation_interaction',
            action,
            section: get().activeSection,
            role: get().userRole,
            timestamp: Date.now(),
            ...metadata,
          })
        }
      },
    }),
    {
      name: 'navigation-store',
      partialize: (state) => ({
        preferences: state.preferences,
        history: state.history,
      }),
    }
  )
)

// Selectors for optimized re-renders
export const selectNavigationOpen = (state: NavigationState) => state.isOpen
export const selectActiveSection = (state: NavigationState) => state.activeSection
export const selectUserRole = (state: NavigationState) => state.userRole
export const selectPreferences = (state: NavigationState) => state.preferences
export const selectIsLoading = (state: NavigationState) => state.isLoading
export const selectError = (state: NavigationState) => state.error

// Hooks for common operations
export const useNavigationToggle = () => {
  const store = useNavigationStore()
  return {
    isOpen: store.isOpen,
    toggle: store.toggleNavigation,
    open: store.openNavigation,
    close: store.closeNavigation,
  }
}

export const useNavigationState = () => {
  const store = useNavigationStore()
  return {
    activeSection: store.activeSection,
    activeItem: store.activeItem,
    setActiveSection: store.setActiveSection,
    setActiveItem: store.setActiveItem,
  }
}

export const useUserRole = () => {
  const store = useNavigationStore()
  return {
    role: store.userRole,
    setRole: store.setUserRole,
  }
}

export const useNavigationPreferences = () => {
  const store = useNavigationStore()
  return {
    preferences: store.preferences,
    updatePreferences: store.updatePreferences,
  }
}