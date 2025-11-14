import { act, renderHook } from '@testing-library/react'
import { useNavigationStore, useNavigationToggle, useNavigationState, useUserRole, useNavigationPreferences } from '../store'

describe('Navigation Store', () => {
  beforeEach(() => {
    // Reset store state before each test
    act(() => {
      useNavigationStore.getState().resetNavigation()
    })
  })

  describe('useNavigationStore', () => {
    test('initializes with default state', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      expect(result.current.isOpen).toBe(false)
      expect(result.current.activeSection).toBe('home')
      expect(result.current.activeItem).toBeNull()
      expect(result.current.userRole).toBe('guest')
      expect(result.current.isLoading).toBe(false)
      expect(result.current.error).toBeNull()
    })

    test('toggles navigation open/close', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      expect(result.current.isOpen).toBe(false)
      
      act(() => {
        result.current.toggleNavigation()
      })
      
      expect(result.current.isOpen).toBe(true)
      
      act(() => {
        result.current.toggleNavigation()
      })
      
      expect(result.current.isOpen).toBe(false)
    })

    test('opens navigation explicitly', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.openNavigation()
      })
      
      expect(result.current.isOpen).toBe(true)
    })

    test('closes navigation explicitly', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.openNavigation()
      })
      expect(result.current.isOpen).toBe(true)
      
      act(() => {
        result.current.closeNavigation()
      })
      
      expect(result.current.isOpen).toBe(false)
    })

    test('sets active section', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setActiveSection('properties')
      })
      
      expect(result.current.activeSection).toBe('properties')
    })

    test('sets active item', () => {
      const { result } = renderHook(() => useNavigationStore())
      const mockItem = {
        id: 'test-item',
        label: 'Test Item',
        href: '/test',
        section: 'test',
        priority: 100,
        roles: ['guest'],
      }
      
      act(() => {
        result.current.setActiveItem(mockItem)
      })
      
      expect(result.current.activeItem).toEqual(mockItem)
    })

    test('sets user role', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setUserRole('user')
      })
      
      expect(result.current.userRole).toBe('user')
    })

    test('sets loading state', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setLoading(true)
      })
      
      expect(result.current.isLoading).toBe(true)
      
      act(() => {
        result.current.setLoading(false)
      })
      
      expect(result.current.isLoading).toBe(false)
    })

    test('sets and clears error', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setError('Test error')
      })
      
      expect(result.current.error).toBe('Test error')
      
      act(() => {
        result.current.clearError()
      })
      
      expect(result.current.error).toBeNull()
    })

    test('updates preferences', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.updatePreferences({ compactMode: true })
      })
      
      expect(result.current.preferences.compactMode).toBe(true)
    })

    test('clears history', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setActiveSection('test')
      })
      
      expect(result.current.history.length).toBeGreaterThan(0)
      
      act(() => {
        result.current.clearHistory()
      })
      
      expect(result.current.history).toEqual([])
    })

    test('resets navigation to initial state', () => {
      const { result } = renderHook(() => useNavigationStore())
      
      act(() => {
        result.current.setUserRole('user')
        result.current.setActiveSection('test')
        result.current.openNavigation()
      })
      
      expect(result.current.userRole).toBe('user')
      expect(result.current.activeSection).toBe('test')
      expect(result.current.isOpen).toBe(true)
      
      act(() => {
        result.current.resetNavigation()
      })
      
      expect(result.current.userRole).toBe('guest')
      expect(result.current.activeSection).toBe('home')
      expect(result.current.isOpen).toBe(false)
    })
  })

  describe('Hook utilities', () => {
    test('useNavigationToggle returns correct values', () => {
      const { result } = renderHook(() => useNavigationToggle())
      
      expect(result.current).toHaveProperty('isOpen')
      expect(result.current).toHaveProperty('toggle')
      expect(result.current).toHaveProperty('open')
      expect(result.current).toHaveProperty('close')
    })

    test('useNavigationState returns correct values', () => {
      const { result } = renderHook(() => useNavigationState())
      
      expect(result.current).toHaveProperty('activeSection')
      expect(result.current).toHaveProperty('activeItem')
      expect(result.current).toHaveProperty('setActiveSection')
      expect(result.current).toHaveProperty('setActiveItem')
    })

    test('useUserRole returns correct values', () => {
      const { result } = renderHook(() => useUserRole())
      
      expect(result.current).toHaveProperty('role')
      expect(result.current).toHaveProperty('setRole')
    })

    test('useNavigationPreferences returns correct values', () => {
      const { result } = renderHook(() => useNavigationPreferences())
      
      expect(result.current).toHaveProperty('preferences')
      expect(result.current).toHaveProperty('updatePreferences')
    })
  })
})