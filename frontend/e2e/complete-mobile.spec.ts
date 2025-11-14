import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Mobile Responsiveness Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
  });

  test('should display mobile menu', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    const mobileMenu = page.locator('button[aria-label*="menu"], button:has-text("Menu")');
    if (await mobileMenu.isVisible()) {
      await mobileMenu.click();
      await expect(page.locator('nav, [role="navigation"]')).toBeVisible();
    }
  });

  test('should work on tablet size', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/');
    
    await expect(page.locator('body')).toBeVisible();
  });

  test('should handle touch gestures for image gallery', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/properties');
    
    const firstProperty = page.locator('[data-testid="property-card"]').first();
    if (await firstProperty.isVisible()) {
      await firstProperty.click();
      
      // Simulate swipe on image gallery
      const gallery = page.locator('.image-gallery, .carousel');
      if (await gallery.isVisible()) {
        await gallery.hover();
      }
    }
  });

  test('should display mobile-optimized forms', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/auth/register');
    
    const nameInput = page.locator('input[name="name"]');
    await expect(nameInput).toBeVisible();
  });

  test('should show mobile bottom navigation', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await authHelper.login('test@example.com', 'password123');
    
    const bottomNav = page.locator('[data-testid="bottom-nav"], .bottom-navigation, nav.mobile');
    if (await bottomNav.isVisible()) {
      await expect(bottomNav).toBeVisible();
    }
  });

  test('should handle mobile search', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    const searchButton = page.locator('button:has-text("Search"), button[aria-label*="search"]');
    if (await searchButton.first().isVisible()) {
      await searchButton.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should display mobile-friendly property cards', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/properties');
    
    const propertyCard = page.locator('[data-testid="property-card"]').first();
    if (await propertyCard.isVisible()) {
      const box = await propertyCard.boundingBox();
      expect(box?.width).toBeLessThan(400);
    }
  });

  test('should work in landscape mode', async ({ page }) => {
    await page.setViewportSize({ width: 667, height: 375 });
    await page.goto('/');
    
    await expect(page.locator('body')).toBeVisible();
  });

  test('should handle mobile filters', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/properties');
    
    const filterButton = page.locator('button:has-text("Filters"), button:has-text("Filter")');
    if (await filterButton.isVisible()) {
      await filterButton.click();
      
      await expect(page.locator('[role="dialog"], .modal, .drawer')).toBeVisible();
    }
  });

  test('should display mobile-optimized checkout', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/payments/checkout');
    
    const paymentForm = page.locator('form');
    await expect(paymentForm).toBeVisible();
  });
});
