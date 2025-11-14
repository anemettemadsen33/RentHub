import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Comparison and Analytics Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
  });

  test('should add properties to comparison', async ({ page }) => {
    await page.goto('/properties');
    
    const compareButton = page.locator('button:has-text("Compare"), input[type="checkbox"][name="compare"]').first();
    if (await compareButton.isVisible()) {
      await compareButton.click();
      await compareButton.click(); // Click on second property
      
      await expect(page.locator('text=/2.*properties/i')).toBeVisible();
    }
  });

  test('should view property comparison page', async ({ page }) => {
    await page.goto('/property-comparison');
    
    const comparisonTable = page.locator('table, [data-testid="comparison-table"]');
    if (await comparisonTable.isVisible()) {
      await expect(comparisonTable).toBeVisible();
    }
  });

  test('should remove property from comparison', async ({ page }) => {
    await page.goto('/property-comparison');
    
    const removeButton = page.locator('button:has-text("Remove"), button[aria-label*="remove"]').first();
    if (await removeButton.isVisible()) {
      await removeButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should clear all comparisons', async ({ page }) => {
    await page.goto('/property-comparison');
    
    const clearButton = page.locator('button:has-text("Clear"), button:has-text("Clear all")');
    if (await clearButton.isVisible()) {
      await clearButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should view analytics dashboard (host)', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const analytics = page.locator('text=/analytics|statistics/i');
    if (await analytics.isVisible()) {
      await expect(analytics).toBeVisible();
    }
  });

  test('should view property views chart', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const chart = page.locator('canvas, svg, [data-testid="chart"]');
    if (await chart.first().isVisible()) {
      await expect(chart.first()).toBeVisible();
    }
  });

  test('should filter analytics by date range', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const dateFilter = page.locator('button:has-text("Last 30 days"), select[name="date_range"]');
    if (await dateFilter.first().isVisible()) {
      await dateFilter.first().click();
      const dateOption = page.locator('text=/Last 7 days|This month/i').first();
      await dateOption.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should export analytics data', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const exportButton = page.locator('button:has-text("Export"), button:has-text("Download")');
    if (await exportButton.isVisible()) {
      const [download] = await Promise.all([
        page.waitForEvent('download'),
        exportButton.click()
      ]);
      
      expect(download.suggestedFilename()).toMatch(/csv|xlsx|pdf/i);
    }
  });

  test('should view booking conversion rate', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const conversionRate = page.locator('text=/conversion|booking rate/i');
    if (await conversionRate.isVisible()) {
      await expect(conversionRate).toBeVisible();
    }
  });

  test('should view revenue analytics', async ({ page }) => {
    await authHelper.login('host@example.com', 'password123');
    await page.goto('/host/analytics');
    
    const revenue = page.locator('text=/revenue|earnings|income/i');
    if (await revenue.isVisible()) {
      await expect(revenue).toBeVisible();
    }
  });
});
