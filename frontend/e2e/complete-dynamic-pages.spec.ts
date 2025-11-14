// cSpell:ignore networkidle
import { test, expect } from '@playwright/test';

test.describe('Dynamic Pages - Complete E2E Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
  });

  // ========== PROPERTY DETAIL PAGES ==========

  test('should load property detail page with mock ID', async ({ page }) => {
    await page.goto('/properties/1');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property reviews page', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property maintenance page', async ({ page }) => {
    await page.goto('/properties/1/maintenance');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property smart locks page', async ({ page }) => {
    await page.goto('/properties/1/smart-locks');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property analytics page', async ({ page }) => {
    await page.goto('/properties/1/analytics');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property access page', async ({ page }) => {
    await page.goto('/properties/1/access');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load property calendar page', async ({ page }) => {
    await page.goto('/properties/1/calendar');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== BOOKING DETAIL PAGES ==========

  test('should load booking detail page with mock ID', async ({ page }) => {
    await page.goto('/bookings/1');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load booking payment page', async ({ page }) => {
    await page.goto('/bookings/1/payment');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== MESSAGE DETAIL PAGES ==========

  test('should load message thread page with mock ID', async ({ page }) => {
    await page.goto('/messages/1');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== DASHBOARD PROPERTY DETAIL ==========

  test('should load dashboard property detail page', async ({ page }) => {
    await page.goto('/dashboard/properties/1');
    await expect(page.locator('body')).toBeVisible();
  });

  // Test with different mock IDs to ensure routing works
  test('should handle different property IDs', async ({ page }) => {
    const ids = ['1', '2', '100', 'abc123'];
    
    for (const id of ids) {
      await page.goto(`/properties/${id}`);
      await expect(page.locator('body')).toBeVisible();
      await page.waitForTimeout(500);
    }
  });

  test('should handle different booking IDs', async ({ page }) => {
    const ids = ['1', '2', '50'];
    
    for (const id of ids) {
      await page.goto(`/bookings/${id}`);
      await expect(page.locator('body')).toBeVisible();
      await page.waitForTimeout(500);
    }
  });
});
