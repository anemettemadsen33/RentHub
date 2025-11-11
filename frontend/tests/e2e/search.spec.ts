import { test, expect } from '@playwright/test';

// Basic search flow covering filter inputs and result presence
// This test is defensive and will no-op gracefully if certain controls are absent.

test.describe('Property search basics', () => {
  test('applies price and bedrooms filters and shows results', async ({ page }) => {
    await page.goto('/properties');

    // Open filters panel if available
    const filtersButton = page.locator('[data-testid="filters-button"]');
    if (await filtersButton.isVisible()) {
      await filtersButton.click();
    }

    // Fill min/max price if fields are present
    const minPrice = page.locator('[data-testid="min-price"]');
    const maxPrice = page.locator('[data-testid="max-price"]');
    if (await minPrice.isVisible()) await minPrice.fill('200');
    if (await maxPrice.isVisible()) await maxPrice.fill('1200');

    // Bedrooms control (select or input)
    const bedsSelect = page.locator('[data-testid="bedrooms"]');
    if (await bedsSelect.isVisible()) {
      await bedsSelect.selectOption('2');
    }

    // Apply filters
    const apply = page.locator('[data-testid="apply-filters"]');
    if (await apply.isVisible()) await apply.click();

    // URL should reflect at least one param
    await expect(page).toHaveURL(/(minPrice|maxPrice|bedrooms)=/);

    // Results should render
    await expect(page.locator('[data-testid="property-card"]').first()).toBeVisible({ timeout: 15000 });
  });
});
