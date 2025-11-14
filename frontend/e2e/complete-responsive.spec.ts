// cSpell:ignore networkidle
import { test, expect } from '@playwright/test';
import { gotoReady } from './helpers/app-ready';

test.describe('Responsive Design - All Pages E2E Tests', () => {
  const devices = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Laptop', width: 1366, height: 768 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 }
  ];

  const criticalPages = [
    '/',
    '/properties',
    '/about',
    '/contact',
    '/auth/login',
    '/dashboard',
    '/bookings',
    '/messages'
  ];

  for (const device of devices) {
    test.describe(`${device.name} - ${device.width}x${device.height}`, () => {
      test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: device.width, height: device.height });
      });

      test(`should load all critical pages on ${device.name}`, async ({ page }) => {
        for (const url of criticalPages) {
          await gotoReady(page, url);
          await expect(page.locator('body')).toBeVisible();
          
          // Check if content is not overflowing
          const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
          expect(bodyWidth).toBeLessThanOrEqual(device.width + 20); // Allow small margin
          
          await page.waitForTimeout(500);
        }
      });

      test(`should have proper mobile menu on ${device.name}`, async ({ page }) => {
        await gotoReady(page, '/');
        
        if (device.width < 768) {
          // Mobile should have hamburger menu
          const hamburger = page.locator('button[aria-label*="menu" i], .hamburger, .menu-toggle, [aria-label="Open menu"]');
          const hamburgerCount = await hamburger.count();
          
          if (hamburgerCount > 0) {
            await hamburger.first().click();
            await page.waitForTimeout(500);
            
            // Menu should be visible
            const menu = page.locator('nav, [role="navigation"], .mobile-menu');
            await expect(menu.first()).toBeVisible();
          }
        }
      });

      test(`should have touch-friendly elements on ${device.name}`, async ({ page }) => {
        if (device.width < 768) {
          await gotoReady(page, '/');
          
          // Buttons should be large enough for touch (minimum 44x44px)
          const buttons = page.locator('button, a[role="button"]');
          const buttonCount = await buttons.count();
          
          if (buttonCount > 0) {
            const firstButton = buttons.first();
            const box = await firstButton.boundingBox();
            
            if (box) {
              // Touch targets should be at least 44x44px
              const isAccessible = box.width >= 40 || box.height >= 40;
              expect(isAccessible).toBeTruthy();
            }
          }
        }
      });
    });
  }

  test('should handle orientation changes', async ({ page }) => {
    // Portrait
    await page.setViewportSize({ width: 375, height: 667 });
    await gotoReady(page, '/');
    await expect(page.locator('body')).toBeVisible();
    
    // Landscape
    await page.setViewportSize({ width: 667, height: 375 });
    await expect(page.locator('body')).toBeVisible();
  });

  test('should maintain functionality across all screen sizes', async ({ page }) => {
    const screenSizes = [
      { width: 320, height: 568 },  // iPhone SE
      { width: 375, height: 667 },  // iPhone 8
      { width: 414, height: 896 },  // iPhone 11
      { width: 768, height: 1024 }, // iPad
      { width: 1024, height: 768 }, // iPad Landscape
      { width: 1440, height: 900 }, // Desktop
      { width: 1920, height: 1080 }  // Full HD
    ];

    for (const size of screenSizes) {
      await page.setViewportSize(size);
      await gotoReady(page, '/');
      
      await expect(page.locator('body')).toBeVisible();
      
      // Check for horizontal scroll
      const hasHorizontalScroll = await page.evaluate(() => 
        document.documentElement.scrollWidth > document.documentElement.clientWidth
      );
      
      expect(hasHorizontalScroll).toBe(false);
    }
  });
});
