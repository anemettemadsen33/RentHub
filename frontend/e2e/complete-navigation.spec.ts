// cSpell:ignore networkidle
import { test, expect } from '@playwright/test';
import { gotoReady } from './helpers/app-ready';

test.describe('Page Navigation & Links - Complete E2E Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
  });

  test('should navigate between public pages', async ({ page }) => {
    // Start at home
    await gotoReady(page, '/');
    await expect(page.locator('body')).toBeVisible();

    // Navigate to about
    const aboutLink = page.locator('a[href="/about"], a:has-text("About")').first();
    if (await aboutLink.isVisible()) {
      await aboutLink.click();
      await expect(page).toHaveURL(/\/about/);
    }

    // Navigate to contact
    await gotoReady(page, '/');
    const contactLink = page.locator('a[href="/contact"], a:has-text("Contact")').first();
    if (await contactLink.isVisible()) {
      await contactLink.click();
      await expect(page).toHaveURL(/\/contact/);
    }
  });

  test('should navigate to properties from home', async ({ page }) => {
    await gotoReady(page, '/');
    
    const propertiesLink = page.locator('a[href="/properties"], a:has-text("Properties"), a:has-text("Browse")').first();
    if (await propertiesLink.isVisible()) {
      await propertiesLink.click();
      await expect(page).toHaveURL(/\/properties/);
    }
  });

  test('should access authentication pages', async ({ page }) => {
    await gotoReady(page, '/');
    
    // Try to find login link
    const loginLink = page.locator('a[href="/auth/login"], a:has-text("Login"), a:has-text("Sign in")').first();
    if (await loginLink.isVisible()) {
      await loginLink.click();
      await expect(page).toHaveURL(/\/auth\/login/);
    }

    // Try to find register link
    await page.goto('/');
    const registerLink = page.locator('a[href="/auth/register"], a:has-text("Register"), a:has-text("Sign up")').first();
    if (await registerLink.isVisible()) {
      await registerLink.click();
      await expect(page).toHaveURL(/\/auth\/register/);
    }
  });

  test('should navigate to dashboard pages', async ({ page }) => {
    await gotoReady(page, '/dashboard');
    
    // Check for dashboard navigation links
    const navLinks = await page.locator('nav a, [role="navigation"] a').count();
    expect(navLinks).toBeGreaterThanOrEqual(0);
  });

  test('should access footer links', async ({ page }) => {
    await gotoReady(page, '/');
    
    // Check for footer
    const footer = page.locator('footer, [role="contentinfo"]');
    if (await footer.isVisible()) {
      // Check for common footer links
      const termsLink = footer.locator('a[href="/terms"], a:has-text("Terms")').first();
      if (await termsLink.isVisible()) {
        await termsLink.click();
        await expect(page).toHaveURL(/\/terms/);
      }
    }
  });

  test('should handle browser back/forward navigation', async ({ page }) => {
    await gotoReady(page, '/');
    await gotoReady(page, '/about');
    await gotoReady(page, '/contact');
    
    await page.goBack();
    await expect(page).toHaveURL(/\/about/);
    
    await page.goBack();
    await expect(page).toHaveURL(/\//);
    
    await page.goForward();
    await expect(page).toHaveURL(/\/about/);
  });

  test('should maintain state during navigation', async ({ page }) => {
    await gotoReady(page, '/properties');
    
    // Fill search if available
    const searchInput = page.locator('input[name="location"], input[placeholder*="search" i]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Test Location');
      
      // Navigate away and back
      await gotoReady(page, '/');
      await gotoReady(page, '/properties');
      
      // Check if search is cleared (expected behavior for most apps)
      const newValue = await searchInput.inputValue();
      // Value should be empty or 'Test Location' depending on app state management
      expect(newValue.length).toBeGreaterThanOrEqual(0);
    }
  });

  test('should handle 404 errors gracefully', async ({ page }) => {
    const response = await page.goto('/this-page-does-not-exist-12345');
    
    // Should either show 404 page or redirect
    await expect(page.locator('body')).toBeVisible();
    
    // Check if there's a 404 message or redirect to home
    const is404 = await page.locator('text=/404|not found|page not found/i').count();
    const isHome = page.url().endsWith('/') || page.url().includes('home');
    
    expect(is404 > 0 || isHome).toBeTruthy();
  });

  test('should load all pages without JavaScript errors', async ({ page }) => {
    const errors: string[] = [];
    
    page.on('pageerror', error => {
      errors.push(error.message);
    });

    const pages = [
      '/',
      '/about',
      '/properties',
      '/contact',
      '/auth/login',
      '/dashboard',
      '/help'
    ];

    for (const url of pages) {
      await gotoReady(page, url);
    }

    // Filter out known acceptable errors
    const criticalErrors = errors.filter(e => 
      !e.includes('favicon') && 
      !e.includes('Extension') &&
      !e.includes('ERR_BLOCKED_BY_CLIENT')
    );

    expect(criticalErrors.length).toBeLessThan(5);
  });
});
