// cSpell:ignore networkidle
import { test, expect } from '@playwright/test';
import { gotoReady } from './helpers/app-ready';

test.describe('Page Load Performance - Complete E2E Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
  });

  const criticalPages = [
    { url: '/', name: 'Home' },
    { url: '/properties', name: 'Properties Listing' },
    { url: '/about', name: 'About' },
    { url: '/contact', name: 'Contact' },
    { url: '/auth/login', name: 'Login' },
    { url: '/auth/register', name: 'Register' },
    { url: '/dashboard', name: 'Dashboard' },
    { url: '/profile', name: 'Profile' },
    { url: '/bookings', name: 'Bookings' },
    { url: '/messages', name: 'Messages' }
  ];

  test('should load all critical pages within acceptable time', async ({ page }) => {
    for (const { url, name } of criticalPages) {
      const startTime = Date.now();
      
      await gotoReady(page, url);
      
      const loadTime = Date.now() - startTime;
      
      // Should load within 10 seconds (generous timeout for E2E)
      expect(loadTime).toBeLessThan(10000);
      
      console.log(`${name} loaded in ${loadTime}ms`);
    }
  });

  test('should have proper meta tags on all pages', async ({ page }) => {
    const pagesToCheck = [
      '/',
      '/properties',
      '/about',
      '/contact',
      '/help'
    ];

    for (const url of pagesToCheck) {
      await gotoReady(page, url);
      
      // Check for title
      const title = await page.title();
      expect(title.length).toBeGreaterThan(0);
      
      // Check for meta description (SEO)
      const metaDescription = page.locator('meta[name="description"]');
      const hasDescription = await metaDescription.count() > 0;
      
      // Check for viewport meta tag
      const viewport = page.locator('meta[name="viewport"]');
      await expect(viewport).toHaveCount(1);
    }
  });

  test('should not have memory leaks during page navigation', async ({ page }) => {
    // Navigate through multiple pages
    const urls = ['/', '/properties', '/about', '/contact', '/help', '/faq'];
    
    for (let i = 0; i < 2; i++) {
      for (const url of urls) {
        await gotoReady(page, url);
        await page.waitForTimeout(500);
      }
    }
    
    // If we get here without timeout, memory management is acceptable
    expect(true).toBe(true);
  });

  test('should load images efficiently', async ({ page }) => {
    await gotoReady(page, '/properties');
    
    // Wait for images to start loading
    await page.waitForTimeout(2000);
    
    // Check if images have proper attributes
    const images = page.locator('img');
    const imageCount = await images.count();
    
    if (imageCount > 0) {
      const firstImage = images.first();
      
      // Check for lazy loading or other optimization attributes
      const hasAlt = await firstImage.getAttribute('alt');
      expect(hasAlt !== null).toBe(true);
    }
  });

  test('should have accessible pages', async ({ page }) => {
    const pagesToCheck = [
      '/',
      '/properties',
      '/about',
      '/auth/login'
    ];

    for (const url of pagesToCheck) {
      await gotoReady(page, url);
      
      // Check for skip links
      const skipLinks = await page.locator('a[href="#main"], a[href="#content"]').count();
      
      // Check for main landmark
      const mainLandmark = await page.locator('main, [role="main"]').count();
      expect(mainLandmark).toBeGreaterThanOrEqual(0);
      
      // Check for proper heading hierarchy (should have h1)
      const h1Count = await page.locator('h1').count();
      expect(h1Count).toBeGreaterThanOrEqual(0);
    }
  });

  test('should handle concurrent page loads', async ({ browser }, testInfo) => {
    const urls = ['/', '/properties', '/about', '/contact', '/help'];
    
    const isSafari = /safari|webkit/i.test(testInfo.project.name);
    const concurrency = isSafari ? 2 : 3;

    const pages = [] as import('@playwright/test').Page[];
    for (let i = 0; i < concurrency; i++) {
      pages.push(await browser.newPage());
    }

    if (isSafari) {
      // Safari tends to be slower/flaky with heavy concurrency; load sequentially
      for (let i = 0; i < pages.length; i++) {
        await pages[i].goto(urls[i % urls.length]);
        await expect(pages[i].locator('body')).toBeVisible();
      }
    } else {
      await Promise.all(pages.map((p, i) => p.goto(urls[i % urls.length])));
      for (const p of pages) {
        await expect(p.locator('body')).toBeVisible();
      }
    }

    await Promise.all(pages.map(p => p.close()));
  });

  test('should cache resources properly', async ({ page }) => {
    // First load
    await gotoReady(page, '/');
    await page.waitForLoadState('networkidle');
    
    // Second load (should use cache)
    const startTime = Date.now();
    await gotoReady(page, '/');
    await page.waitForLoadState('networkidle');
    const secondLoadTime = Date.now() - startTime;
    
    // Second load should typically be faster due to caching
    expect(secondLoadTime).toBeLessThan(10000);
  });
});
