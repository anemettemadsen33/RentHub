import React from 'react'
import { render, screen } from '@testing-library/react'
import { BottomNavigation, AuthenticatedBottomNavigation, SmartBottomNavigation } from '../BottomNavigation'
import { NavigationProvider } from '../NavigationContainer'

describe('BottomNavigation', () => {
  test('renders bottom navigation for guest users', () => {
    render(
      <NavigationProvider initialRole="guest">
        <BottomNavigation />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation', { name: /bottom navigation/i })
    expect(navigation).toBeInTheDocument()
    expect(navigation).toHaveClass('md:hidden')
  })

  test('renders navigation items for guest users', () => {
    render(
      <NavigationProvider initialRole="guest">
        <BottomNavigation />
      </NavigationProvider>
    )
    
    // Should show basic navigation items for guests
    expect(screen.getByText('Home')).toBeInTheDocument()
    expect(screen.getByText('Browse')).toBeInTheDocument()
  })

  test('hides on desktop screens', () => {
    render(
      <NavigationProvider initialRole="guest">
        <BottomNavigation />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('md:hidden')
  })

  test('applies custom className', () => {
    render(
      <NavigationProvider initialRole="guest">
        <BottomNavigation className="custom-class" />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('custom-class')
  })

  test('respects maxItems prop', () => {
    render(
      <NavigationProvider initialRole="guest">
        <BottomNavigation maxItems={3} />
      </NavigationProvider>
    )
    
    const items = screen.getAllByRole('link')
    expect(items.length).toBeLessThanOrEqual(3)
  })
})

describe('AuthenticatedBottomNavigation', () => {
  test('renders enhanced navigation for authenticated users', () => {
    render(
      <NavigationProvider initialRole="user">
        <AuthenticatedBottomNavigation />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation', { name: /authenticated bottom navigation/i })
    expect(navigation).toBeInTheDocument()
  })

  test('shows additional items for authenticated users', () => {
    render(
      <NavigationProvider initialRole="user">
        <AuthenticatedBottomNavigation />
      </NavigationProvider>
    )
    
    // Should show more items for authenticated users
    const items = screen.getAllByRole('link')
    expect(items.length).toBeGreaterThan(2)
  })
})

describe('SmartBottomNavigation', () => {
  test('shows basic navigation for guest users', () => {
    render(
      <NavigationProvider initialRole="guest">
        <SmartBottomNavigation />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation', { name: /bottom navigation/i })
    expect(navigation).toBeInTheDocument()
  })

  test('shows enhanced navigation for authenticated users', () => {
    render(
      <NavigationProvider initialRole="user">
        <SmartBottomNavigation />
      </NavigationProvider>
    )
    
    const navigation = screen.getByRole('navigation', { name: /authenticated bottom navigation/i })
    expect(navigation).toBeInTheDocument()
  })

  test('switches navigation based on user role', () => {
    const { rerender } = render(
      <NavigationProvider initialRole="guest">
        <SmartBottomNavigation />
      </NavigationProvider>
    )
    
    expect(screen.getByRole('navigation', { name: /bottom navigation/i })).toBeInTheDocument()
    
    rerender(
      <NavigationProvider initialRole="user">
        <SmartBottomNavigation />
      </NavigationProvider>
    )
    
    expect(screen.getByRole('navigation', { name: /authenticated bottom navigation/i })).toBeInTheDocument()
  })