import React from 'react'
import { render, screen, fireEvent } from '@testing-library/react'
import { NavigationItem } from '../NavigationItem'
import { NavigationProvider } from '../NavigationContainer'
import { Home } from 'lucide-react'
import { NavItem } from '../types'

// Mock Next.js router
jest.mock('next/navigation', () => ({
  usePathname: () => '/test',
  useRouter: () => ({
    push: jest.fn(),
    replace: jest.fn(),
    prefetch: jest.fn(),
  }),
}))

const mockNavItem: NavItem = {
  id: 'test-item',
  label: 'Test Item',
  href: '/test',
  icon: Home,
  section: 'test',
  priority: 100,
  roles: ['guest', 'user'],
}

describe('NavigationItem', () => {
  const renderNavigationItem = (props = {}) => {
    return render(
      <NavigationProvider>
        <NavigationItem item={mockNavItem} {...props} />
      </NavigationProvider>
    )
  }

  beforeEach(() => {
    jest.clearAllMocks()
  })

  test('renders navigation item with label', () => {
    renderNavigationItem()
    expect(screen.getByText('Test Item')).toBeInTheDocument()
  })

  test('renders navigation item with icon', () => {
    renderNavigationItem()
    const icon = screen.getByRole('img', { hidden: true })
    expect(icon).toBeInTheDocument()
  })

  test('applies active state when current path matches', () => {
    renderNavigationItem()
    const link = screen.getByRole('link')
    expect(link).toHaveAttribute('aria-current', 'page')
  })

  test('handles click events', () => {
    const onClick = jest.fn()
    renderNavigationItem({ onClick })
    
    const link = screen.getByRole('link')
    fireEvent.click(link)
    
    expect(onClick).toHaveBeenCalledTimes(1)
  })

  test('renders with different variants', () => {
    const { rerender } = renderNavigationItem({ variant: 'desktop' })
    expect(screen.getByRole('link')).toHaveClass('desktop')

    rerender(
      <NavigationProvider>
        <NavigationItem item={mockNavItem} variant="mobile" />
      </NavigationProvider>
    )
    expect(screen.getByRole('link')).toHaveClass('mobile')

    rerender(
      <NavigationProvider>
        <NavigationItem item={mockNavItem} variant="bottom" />
      </NavigationProvider>
    )
    expect(screen.getByRole('link')).toHaveClass('bottom')
  })

  test('renders compact mode', () => {
    renderNavigationItem({ compact: true })
    const link = screen.getByRole('link')
    expect(link).toHaveClass('compact')
  })

  test('hides label when showLabel is false', () => {
    renderNavigationItem({ showLabel: false })
    expect(screen.queryByText('Test Item')).not.toBeInTheDocument()
  })

  test('renders badge when provided', () => {
    const itemWithBadge = { ...mockNavItem, badge: '5' }
    render(
      <NavigationProvider>
        <NavigationItem item={itemWithBadge} />
      </NavigationProvider>
    )
    
    expect(screen.getByText('5')).toBeInTheDocument()
  })

  test('handles disabled state', () => {
    const disabledItem = { ...mockNavItem, disabled: true }
    render(
      <NavigationProvider>
        <NavigationItem item={disabledItem} />
      </NavigationProvider>
    )
    
    const link = screen.getByRole('link')
    expect(link).toHaveAttribute('aria-disabled', 'true')
  })

  test('renders as button when no href provided', () => {
    const buttonItem = { ...mockNavItem, href: undefined }
    render(
      <NavigationProvider>
        <NavigationItem item={buttonItem} />
      </NavigationProvider>
    )
    
    expect(screen.getByRole('button')).toBeInTheDocument()
  })

  test('applies custom CSS classes', () => {
    renderNavigationItem({ className: 'custom-class' })
    const link = screen.getByRole('link')
    expect(link).toHaveClass('custom-class')
  })
})