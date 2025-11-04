import { test, expect } from '@playwright/test';

test.describe('Properties', () => {
  test('should display properties list', async ({ page }) => {
    await page.goto('/properties');
    await expect(page.locator('h1')).toContainText('Properties');
    await expect(page.locator('[data-testid="property-card"]')).toHaveCount(10);
  });

  test('should search properties', async ({ page }) => {
    await page.goto('/properties');
    await page.fill('[data-testid="search-input"]', 'apartment');
    await page.click('[data-testid="search-button"]');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('[data-testid="property-card"]').first()).toBeVisible();
  });

  test('should filter by price', async ({ page }) => {
    await page.goto('/properties');
    await page.click('[data-testid="filters-button"]');
    await page.fill('[data-testid="min-price"]', '100');
    await page.fill('[data-testid="max-price"]', '500');
    await page.click('[data-testid="apply-filters"]');
    await page.waitForLoadState('networkidle');
    const prices = await page.locator('[data-testid="property-price"]').allTextContents();
    prices.forEach(price => {
      const amount = parseFloat(price.replace(/[^0-9.]/g, ''));
      expect(amount).toBeGreaterThanOrEqual(100);
      expect(amount).toBeLessThanOrEqual(500);
    });
  });

  test('should view property details', async ({ page }) => {
    await page.goto('/properties');
    await page.click('[data-testid="property-card"]');
    await expect(page).toHaveURL(/\/properties\/\d+/);
    await expect(page.locator('[data-testid="property-title"]')).toBeVisible();
    await expect(page.locator('[data-testid="book-now-button"]')).toBeVisible();
  });

  test('should add property to wishlist', async ({ page }) => {
    await page.goto('/properties');
    await page.click('[data-testid="wishlist-button"]');
    await expect(page.locator('[data-testid="wishlist-button"]')).toHaveClass(/active/);
  });
});

test.describe('Booking', () => {
  test('should create booking', async ({ page }) => {
    await page.goto('/properties/1');
    await page.fill('[data-testid="check-in"]', '2025-12-01');
    await page.fill('[data-testid="check-out"]', '2025-12-05');
    await page.fill('[data-testid="guests"]', '2');
    await page.click('[data-testid="book-now-button"]');
    await expect(page).toHaveURL(/\/bookings\/\d+/);
    await expect(page.locator('[data-testid="booking-confirmation"]')).toBeVisible();
  });
});
