import { test, expect } from '@playwright/test';

async function waitForAppReady(page: any) {
  try {
    await page.waitForSelector('[data-testid="hydration-ready"]', { timeout: 5000 });
  } catch {
    // Fallback: wait for key UI elements
    await page.waitForSelector('nav, header, main', { timeout: 5000 });
  }
}

// Expanded smoke + basic search filter interaction
test.describe('Smoke & Basic Search', () => {
  test('home page loads and shows hero heading', async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
    await expect(page.getByRole('heading', { level: 1 })).toContainText(/RentHub/i);
  });

  test('properties page lists cards and allows simple price filter', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);
    // Wait for property cards
    await expect(page.locator('[data-testid="property-card"]').first()).toBeVisible({ timeout: 15000 });

    // Open filters (if present) and apply a min price
    const filtersButton = page.locator('[data-testid="filters-button"]');
    if (await filtersButton.isVisible()) {
      await filtersButton.click();
      const minPrice = page.locator('[data-testid="min-price"]');
      if (await minPrice.isVisible()) {
        await minPrice.fill('100');
        const apply = page.locator('[data-testid="apply-filters"]');
        if (await apply.isVisible()) {
          await apply.click();
          // URL should reflect param
          await expect(page).toHaveURL(/minPrice=100/);
        }
      }
    }

    // Ensure at least one visible attribute like Bedrooms or Price remains
    await expect(page.getByText(/Bedrooms/i).first()).toBeVisible();
  });
});
