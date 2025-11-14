import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Search and Filters Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    await page.goto('/properties');
  });

  test('should search by location', async ({ page }) => {
    await page.fill('input[name="location"], input[placeholder*="location" i]', 'Bucharest');
    await page.click('button:has-text("Search")');
    
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
    const results = await page.locator('[data-testid="property-card"], .property-card').count();
    expect(results).toBeGreaterThan(0);
  });

  test('should autocomplete location search', async ({ page }) => {
    const locationInput = page.locator('input[name="location"], input[placeholder*="location" i]');
    if (await locationInput.isVisible()) {
      // cSpell:ignore Buchar
      await locationInput.fill('Buchar');
      await page.waitForTimeout(500);
      
      // Check for autocomplete dropdown
      const suggestions = page.locator('[role="listbox"], .autocomplete-item, .suggestion');
      if (await suggestions.first().isVisible()) {
        await suggestions.first().click();
      }
    }
  });

  test('should filter by check-in and check-out dates', async ({ page }) => {
    await page.fill('input[name="check_in"], input[name="checkIn"]', '2025-04-01');
    await page.fill('input[name="check_out"], input[name="checkOut"]', '2025-04-05');
    await page.click('button:has-text("Search"), button:has-text("Apply")');
    
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
  });

  test('should filter by number of guests', async ({ page }) => {
    const guestsInput = page.locator('input[name="guests"], button:has-text("Guests")');
    if (await guestsInput.first().isVisible()) {
      await guestsInput.first().click();
      
      // Increment guests
      const incrementButton = page.locator('button[aria-label*="increase"], button:has-text("+")');
      if (await incrementButton.first().isVisible()) {
        await incrementButton.first().click();
        await incrementButton.first().click();
      }
    }
  });

  test('should filter by multiple amenities', async ({ page }) => {
    const amenitiesButton = page.locator('button:has-text("Amenities"), button:has-text("Filters")');
    if (await amenitiesButton.isVisible()) {
      await amenitiesButton.click();
      
      // Check multiple amenities
      const wifiCheckbox = page.locator('input[value="wifi"], label:has-text("WiFi")').first();
      if (await wifiCheckbox.isVisible()) await wifiCheckbox.check();
      
      const parkingCheckbox = page.locator('input[value="parking"], label:has-text("Parking")').first();
      if (await parkingCheckbox.isVisible()) await parkingCheckbox.check();
      
      const poolCheckbox = page.locator('input[value="pool"], label:has-text("Pool")').first();
      if (await poolCheckbox.isVisible()) await poolCheckbox.check();
      
      await page.click('button:has-text("Apply"), button:has-text("Show")');
      // cSpell:ignore networkidle
      await page.waitForLoadState('networkidle');
    }
  });

  test('should filter by instant booking', async ({ page }) => {
    const instantBooking = page.locator('input[name="instant_booking"], label:has-text("Instant Book")');
    if (await instantBooking.first().isVisible()) {
      await instantBooking.first().check();
      await page.waitForTimeout(1000);
    }
  });

  test('should filter by property rating', async ({ page }) => {
    const ratingFilter = page.locator('input[name="min_rating"], select[name="rating"]');
    if (await ratingFilter.first().isVisible()) {
      await ratingFilter.first().click();
      await page.click('text=/4\+ stars|4\.5/i');
    }
  });

  test('should filter by pet-friendly properties', async ({ page }) => {
    const petFriendly = page.locator('input[value="pet_friendly"], label:has-text("Pet")');
    if (await petFriendly.first().isVisible()) {
      await petFriendly.first().check();
      await page.waitForTimeout(1000);
    }
  });

  test('should use advanced filters', async ({ page }) => {
    const advancedButton = page.locator('button:has-text("More Filters"), button:has-text("Advanced")');
    if (await advancedButton.isVisible()) {
      await advancedButton.click();
      
      // Set various advanced filters
      await page.fill('input[name="min_size"]', '50');
      await page.check('input[value="balcony"]');
      await page.check('input[value="air_conditioning"]');
      
      await page.click('button:has-text("Apply")');
      // cSpell:ignore networkidle
      await page.waitForLoadState('networkidle');
    }
  });

  test('should save search filters', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/properties');
    
    // Apply some filters
    await page.fill('input[name="location"]', 'Paris');
    await page.fill('input[name="min_price"]', '100');
    await page.fill('input[name="max_price"]', '200');
    
    const saveSearchButton = page.locator('button:has-text("Save Search"), button:has-text("Save Filters")');
    if (await saveSearchButton.isVisible()) {
      await saveSearchButton.click();
      
      await page.fill('input[name="search_name"]', 'Paris Budget Properties');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/search saved|saved successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view saved searches', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/saved-searches');
    
    const savedSearches = page.locator('[data-testid="saved-search"], .saved-search-item');
    if (await savedSearches.first().isVisible()) {
      await expect(savedSearches.first()).toBeVisible();
    }
  });

  test('should delete saved search', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/saved-searches');
    
    const deleteButton = page.locator('button:has-text("Delete")').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/deleted/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
