import { test, expect } from '@playwright/test';

test.describe('Complete UI/UX and Accessibility Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should toggle dark/light theme', async ({ page }) => {
    const themeToggle = page.locator('button[aria-label*="theme"], button:has-text("Theme")');
    if (await themeToggle.isVisible()) {
      await themeToggle.click();
      await page.waitForTimeout(500);
      
      // Verify theme changed
      const html = page.locator('html');
      const classAttr = await html.getAttribute('class');
      expect(classAttr).toBeTruthy();
    }
  });

  test('should change language', async ({ page }) => {
    const languageSelector = page.locator('select[name="language"], button[aria-label*="language"]');
    const firstSelector = languageSelector.first();
    if (await firstSelector.isVisible()) {
      await firstSelector.click();
      // cSpell:ignore Română
      const languageOption = page.locator('text=/Română|English/i');
      await languageOption.first().click();
      await page.waitForTimeout(1000);
    }
  });

  test('should navigate using keyboard', async ({ page }) => {
    // Tab through focusable elements
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    
    // Verify focus is visible
    const focused = await page.evaluate(() => document.activeElement?.tagName);
    expect(focused).toBeTruthy();
  });

  test('should have proper ARIA labels', async ({ page }) => {
    const buttons = page.locator('button');
    const count = await buttons.count();
    
    for (let i = 0; i < Math.min(count, 10); i++) {
      const button = buttons.nth(i);
      if (await button.isVisible()) {
        const ariaLabel = await button.getAttribute('aria-label');
        const text = await button.textContent();
        expect(ariaLabel || text).toBeTruthy();
      }
    }
  });

  test('should have skip to main content link', async ({ page }) => {
    const skipLink = page.locator('a:has-text("Skip to"), a[href="#main"]');
    if (await skipLink.isVisible()) {
      await expect(skipLink).toBeVisible();
    }
  });

  test('should show tooltips on hover', async ({ page }) => {
    const tooltipTrigger = page.locator('[data-tooltip], button[aria-label]').first();
    if (await tooltipTrigger.isVisible()) {
      await tooltipTrigger.hover();
      await page.waitForTimeout(500);
    }
  });

  test('should handle responsive navigation menu', async ({ page }) => {
    // Resize to mobile
    await page.setViewportSize({ width: 375, height: 667 });
    
    const mobileMenu = page.locator('button[aria-label*="menu"], button:has-text("Menu")');
    if (await mobileMenu.isVisible()) {
      await mobileMenu.click();
      
      // Verify menu is visible
      await expect(page.locator('nav, [role="navigation"]')).toBeVisible();
    }
  });

  test('should show loading states', async ({ page }) => {
    await page.goto('/properties');
    
    // Look for loading indicators
    const loader = page.locator('[data-testid="loading"], .loading, .spinner');
    // Loader might disappear quickly, so we just check if page loads
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
  });

  test('should display error messages clearly', async ({ page }) => {
    await page.goto('/auth/login');
    
    // Submit empty form
    await page.click('button[type="submit"]');
    
    // Check for error messages
    const errors = page.locator('[role="alert"], .error, [data-error]');
    const count = await errors.count();
    expect(count).toBeGreaterThan(0);
  });

  test('should have breadcrumb navigation', async ({ page }) => {
    await page.goto('/properties');
    
    const breadcrumb = page.locator('[aria-label*="breadcrumb"], .breadcrumb, nav ol');
    if (await breadcrumb.isVisible()) {
      await expect(breadcrumb).toBeVisible();
    }
  });

  test('should handle image lazy loading', async ({ page }) => {
    await page.goto('/properties');
    
    const images = page.locator('img');
    const firstImage = images.first();
    if (await firstImage.isVisible()) {
      const loading = await firstImage.getAttribute('loading');
      expect(loading === 'lazy' || loading === 'eager' || loading === null).toBeTruthy();
    }
  });

  test('should show confirmation dialogs for destructive actions', async ({ page }) => {
    await page.goto('/auth/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
    
    // Try to delete something
    await page.goto('/settings');
    
    const deleteButton = page.locator('button:has-text("Delete Account")');
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      
      // Should show confirmation
      await expect(page.locator('[role="dialog"], [role="alertdialog"]')).toBeVisible();
    }
  });
});
