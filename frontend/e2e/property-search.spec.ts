import { test, expect } from '@playwright/test';

test.describe('Property Search and Booking', () => {
  test('should search for properties and view details', async ({ page }) => {
    await page.goto('http://localhost:3000');
    
    // Search for properties
    await page.fill('input[placeholder*="location"]', 'Paris');
    await page.fill('input[name="check_in"]', '2025-03-01');
    await page.fill('input[name="check_out"]', '2025-03-05');
    await page.fill('input[name="guests"]', '2');
    
    await page.click('button:has-text("Search")');
    
    // Wait for results
    await expect(page.locator('[data-testid="property-card"]').first()).toBeVisible();
    
    // Click first property
    await page.locator('[data-testid="property-card"]').first().click();
    
    // Verify property details page
    await expect(page.locator('h1')).toContainText(/property|apartment|house/i);
    await expect(page.locator('text=$')).toBeVisible(); // Price visible
    await expect(page.locator('text=Book now')).toBeVisible();
  });

  test('should filter properties by price range', async ({ page }) => {
    await page.goto('http://localhost:3000/properties');
    
    // Set price filter
    await page.fill('input[name="min_price"]', '50');
    await page.fill('input[name="max_price"]', '150');
    await page.click('button:has-text("Apply filters")');
    
    // Verify filtered results
    const prices = await page.locator('[data-testid="property-price"]').allTextContents();
    
    prices.forEach(priceText => {
      const price = parseInt(priceText.replace(/[^0-9]/g, ''));
      expect(price).toBeGreaterThanOrEqual(50);
      expect(price).toBeLessThanOrEqual(150);
    });
  });

  test('should create booking for logged-in user', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:3000/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    
    // Search and select property
    await page.goto('http://localhost:3000/properties');
    await page.locator('[data-testid="property-card"]').first().click();
    
    // Fill booking form
    await page.fill('input[name="check_in"]', '2025-03-10');
    await page.fill('input[name="check_out"]', '2025-03-15');
    await page.fill('input[name="guests"]', '2');
    
    // Submit booking
    await page.click('button:has-text("Book now")');
    
    // Verify booking confirmation
    await expect(page.locator('text=Booking confirmed')).toBeVisible();
    await expect(page.locator('text=Total amount')).toBeVisible();
  });
});
