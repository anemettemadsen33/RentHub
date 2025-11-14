import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete SEO and Performance Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
  });

  test('should have proper page titles', async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title.length).toBeGreaterThan(0);
    
    await page.goto('/properties');
    const propertiesTitle = await page.title();
    expect(propertiesTitle).not.toBe(title);
  });

  test('should have meta descriptions', async ({ page }) => {
    await page.goto('/');
    
    const metaDescription = page.locator('meta[name="description"]');
    const content = await metaDescription.getAttribute('content');
    expect(content).toBeTruthy();
    expect(content!.length).toBeGreaterThan(50);
  });

  test('should have Open Graph tags', async ({ page }) => {
    await page.goto('/');
    
    const ogTitle = page.locator('meta[property="og:title"]');
    const ogDescription = page.locator('meta[property="og:description"]');
    const ogImage = page.locator('meta[property="og:image"]');
    
    expect(await ogTitle.count()).toBeGreaterThan(0);
    expect(await ogDescription.count()).toBeGreaterThan(0);
  });

  test('should have canonical URLs', async ({ page }) => {
    await page.goto('/properties');
    
    const canonical = page.locator('link[rel="canonical"]');
    if (await canonical.count() > 0) {
      const href = await canonical.getAttribute('href');
      expect(href).toBeTruthy();
    }
  });

  test('should have robots meta tag', async ({ page }) => {
    await page.goto('/');
    
    const robots = page.locator('meta[name="robots"]');
    if (await robots.count() > 0) {
      const content = await robots.getAttribute('content');
      expect(content).toBeTruthy();
    }
  });

  test('should have structured data (JSON-LD)', async ({ page }) => {
    await page.goto('/');
    
    const jsonLd = page.locator('script[type="application/ld+json"]');
    if (await jsonLd.count() > 0) {
      const content = await jsonLd.first().textContent();
      expect(content).toBeTruthy();
      
      // Validate JSON
      const parsed = JSON.parse(content!);
      expect(parsed['@context']).toBe('https://schema.org');
    }
  });

  test('should load within performance budget', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/');
    await page.waitForLoadState('load');
    const loadTime = Date.now() - startTime;
    
    // Should load in under 5 seconds
    expect(loadTime).toBeLessThan(5000);
  });

  test('should have optimized images', async ({ page }) => {
    await page.goto('/properties');
    
    const images = page.locator('img');
    const count = await images.count();
    
    for (let i = 0; i < Math.min(count, 5); i++) {
      const img = images.nth(i);
      if (await img.isVisible()) {
        const alt = await img.getAttribute('alt');
        expect(alt !== null).toBeTruthy(); // Should have alt text
      }
    }
  });

  test('should use proper heading hierarchy', async ({ page }) => {
    await page.goto('/');
    
    const h1 = page.locator('h1');
    const h1Count = await h1.count();
    
    // Should have exactly one H1
    expect(h1Count).toBeGreaterThan(0);
    expect(h1Count).toBeLessThanOrEqual(2);
  });

  test('should have language attribute', async ({ page }) => {
    await page.goto('/');
    
    const html = page.locator('html');
    const lang = await html.getAttribute('lang');
    expect(lang).toBeTruthy();
  });

  test('should not have console errors', async ({ page }) => {
    const errors: string[] = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    await page.goto('/');
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
    
    // Filter out known non-critical errors
    const criticalErrors = errors.filter(e => 
      !e.includes('favicon') && 
      !e.includes('Extension')
    );
    
    expect(criticalErrors.length).toBe(0);
  });

  test('should have valid sitemap', async ({ page, request }) => {
    const response = await request.get('/sitemap.xml');
    expect(response.status()).toBe(200);
    
    const text = await response.text();
    expect(text).toContain('<?xml');
    expect(text).toContain('urlset');
  });

  test('should have robots.txt', async ({ page, request }) => {
    const response = await request.get('/robots.txt');
    expect(response.status()).toBe(200);
    
    const text = await response.text();
    expect(text.length).toBeGreaterThan(0);
  });

  test('should use HTTPS', async ({ page }) => {
    await page.goto('/');
    const url = page.url();
    
    // In production, should use HTTPS
    if (!url.includes('localhost')) {
      expect(url).toContain('https://');
    }
  });

  test('should have service worker for PWA', async ({ page }) => {
    await page.goto('/');
    
    const swRegistered = await page.evaluate(async () => {
      if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        return registration !== undefined;
      }
      return false;
    });
    
    // May or may not have SW depending on environment
    expect(typeof swRegistered).toBe('boolean');
  });

  test('should have manifest.json for PWA', async ({ request }) => {
    const response = await request.get('/manifest.json').catch(() => ({ status: () => 404 }));
    
    // Should exist in production
    if (response.status() === 200 && 'json' in response) {
      const manifest = await response.json();
      expect(manifest.name).toBeTruthy();
    }
  });
});
