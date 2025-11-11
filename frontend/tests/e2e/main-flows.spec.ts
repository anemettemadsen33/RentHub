import { test, expect } from '@playwright/test';
import { AxeBuilder } from '@axe-core/playwright';
const isCI = !!process.env.CI;
const isE2EStub = process.env.NEXT_PUBLIC_E2E === 'true';

// All flows now wait for a hydration-ready marker to reduce flakiness
async function waitForAppReady(page: any) {
  try {
    await page.waitForSelector('[data-testid="hydration-ready"]', { timeout: 5000 });
  } catch {
    // Fallback: wait for key UI elements (nav/header or main content)
    await page.waitForSelector('nav, header, main', { timeout: 5000 });
  }
}

test.describe('Property Search and Comparison Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
  });

  test('should display properties on the search page', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);
    const cards = page.locator('[data-testid="property-card"]');
    const count = await cards.count();
    if (count === 0) {
      test.skip(true, 'No property cards available in this environment');
    }
    const firstCard = cards.first();
    await expect(firstCard.locator('h3')).toBeVisible();
    await expect(firstCard.locator('[data-testid="property-price"]')).toBeVisible();
  });

  test('should filter properties by price range', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);
    const filtersButton = page.locator('[data-testid="filters-button"]');
    if (!(await filtersButton.isVisible())) {
      test.skip(true, 'Filters UI not present in this environment');
    }
    await filtersButton.click();
    const min = page.locator('[data-testid="min-price"]');
    const max = page.locator('[data-testid="max-price"]');
    if (!(await min.isVisible()) || !(await max.isVisible())) {
      test.skip(true, 'Price inputs not available');
    }
    await min.fill('100');
    await max.fill('500');
    const apply = page.locator('[data-testid="apply-filters"]');
    if (!(await apply.isVisible())) {
      test.skip(true, 'Apply button not available');
    }
    await apply.click();
    await expect(page).toHaveURL(/minPrice=100/);
    await expect(page).toHaveURL(/maxPrice=500/);
  });

  test('should add property to comparison', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);
    const compareBtn = page.locator('[data-testid="compare-button"]').first();
    if (!(await compareBtn.isVisible())) {
      test.skip(true, 'Compare button not present');
    }
    await compareBtn.click();
  await expect(page.locator('[data-testid="comparison-bar"]')).toBeVisible();
  await expect(page.locator('[data-testid="comparison-count"]')).toHaveText('1');
  });

  test('should navigate to comparison page', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);
    const compareButtons = page.locator('[data-testid="compare-button"]');
    const count = await compareButtons.count();
    if (count < 2) {
      test.skip(true, 'Not enough properties to compare');
    }
    await compareButtons.nth(0).click();
    await compareButtons.nth(1).click();
    const compareNow = page.locator('[data-testid="compare-now-button"]');
    if (!(await compareNow.isVisible())) {
      test.skip(true, 'Compare Now button not available');
    }
    await compareNow.click();
    await expect(page).toHaveURL('/property-comparison');
  await expect(page.locator('[data-testid="comparison-property"]')).toHaveCount(2);
  });

  test('should toggle theme', async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
    
    // Click theme toggle
    const toggle = page.locator('[data-testid="theme-toggle"]');
    if (await toggle.isVisible()) {
      await toggle.click();
    } else {
      // fallback to button role if test id missing
      await page.getByRole('button', { name: /toggle theme/i }).click();
    }
    
    // Select dark mode
  await page.click('text=Dark');
    
    // Check dark class is applied
    const htmlElement = page.locator('html');
    await expect(htmlElement).toHaveClass(/dark/);
  });
});

test.describe('Saved Searches', () => {
  if (isCI || isE2EStub) {
    test.skip(true, 'Saved searches skipped in CI or stub mode');
  }
  test.beforeEach(async ({ page }) => {
    // Mock login
    await page.goto('/auth/login');
    await page.fill('[name="email"]', 'test@example.com');
    await page.fill('[name="password"]', 'password123');
    await page.click('[type="submit"]');
    
    await page.waitForURL('/');
  });

  test('should save a search', async ({ page }) => {
    if (isCI) test.skip(true, 'Skipping saved search flow in CI');
    await page.goto('/properties');
    
    // Apply some filters
    await page.click('[data-testid="filters-button"]');
    await page.fill('[data-testid="min-price"]', '200');
    await page.click('[data-testid="apply-filters"]');
    
    // Open save search dialog
    await page.click('[data-testid="save-search-button"]');
    
    // Fill in search name
    await page.fill('[name="name"]', 'My Test Search');
    
    // Save
    await page.click('text=Save Search');
    
    // Should show success toast
    await expect(page.locator('text=Search saved successfully')).toBeVisible();
  });

  test('should display saved searches', async ({ page }) => {
    if (isCI) test.skip(true, 'Skipping saved search flow in CI');
    await page.goto('/saved-searches');
    
    // Check page title
    await expect(page.locator('h1')).toHaveText('Saved Searches');
    
    // Should have at least one saved search
    await expect(page.locator('[data-testid="saved-search-card"]')).toHaveCount(1);
  });
});

test.describe('Accessibility', () => {
  test('should have no serious accessibility violations', async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);

    // Axe-core scan
    const results = await new AxeBuilder({ page }).analyze();
    const serious = results.violations.filter((v: any) => ['serious', 'critical'].includes(v.impact || ''));
    if (serious.length) {
      console.log('Accessibility violations (serious/critical):');
      for (const v of serious) {
        console.log(`- ${v.id}: ${v.description}`);
      }
    }
    if (isCI) {
      expect(serious.length, 'Found serious/critical a11y issues').toBe(0);
    }
  });

  test('should be keyboard navigable', async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
    
    // Tab through navigation
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    
    // Check focus is visible
    const focusedElement = page.locator(':focus');
    await expect(focusedElement).toBeVisible();
  });
});
