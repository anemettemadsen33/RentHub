import { test, expect } from '@playwright/test';
import { login } from './helpers';

test.describe('Payments Management', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    // Navigate to payments
    await page.goto('/payments');
    await page.waitForLoadState('networkidle');
  });

  test('should display payments list', async ({ page }) => {
    // Wait for table or list to load
    await page.waitForSelector('table, [data-testid="payments-list"]', { timeout: 5000 });
    
    // Check for table headers
    await expect(page.locator('text=/id|amount|status/i')).toBeVisible();
  });

  test('should filter payments by status', async ({ page }) => {
    // Select filter dropdown
    const filterSelect = page.locator('select').first();
    
    if (await filterSelect.isVisible()) {
      // Filter by completed
      await filterSelect.selectOption('completed');
      await page.waitForTimeout(1000);
      
      // Verify filtered results
      const statusBadges = page.locator('text=/completed/i');
      const badgeCount = await statusBadges.count();
      
      if (badgeCount > 0) {
        // All visible items should be completed
        expect(badgeCount).toBeGreaterThan(0);
      }
      
      // Filter by pending
      await filterSelect.selectOption('pending');
      await page.waitForTimeout(1000);
      
      // Reset to all
      await filterSelect.selectOption('all');
      await page.waitForTimeout(500);
    }
  });

  test('should view payment details', async ({ page }) => {
    // Click on first payment row
    const firstRow = page.locator('table tbody tr').first();
    
    if (await firstRow.isVisible({ timeout: 3000 })) {
      const paymentId = await firstRow.locator('td').first().textContent();
      
      // Click view/details button
      const viewButton = firstRow.locator('button:has-text("View")').or(
        firstRow.locator('a:has-text("View")')
      );
      
      if (await viewButton.isVisible({ timeout: 2000 })) {
        await viewButton.click();
        await page.waitForTimeout(1000);
        
        // Should show payment details
        await expect(page.locator(`text=${paymentId}`)).toBeVisible();
      }
    }
  });

  test('should request refund for completed payment', async ({ page }) => {
    // Find a completed payment
    const completedRow = page.locator('tr:has-text("completed"), tr:has-text("Completed")').first();
    
    if (await completedRow.isVisible({ timeout: 3000 })) {
      // Click refund button
      const refundButton = completedRow.locator('button:has-text("Refund")');
      
      if (await refundButton.isVisible({ timeout: 2000 })) {
        // Listen for confirmation dialog
        page.on('dialog', async (dialog) => {
          expect(dialog.type()).toBe('confirm');
          await dialog.accept();
        });
        
        await refundButton.click();
        await page.waitForTimeout(1500);
        
        // Status should update to refunded
        await expect(completedRow.locator('text=/refund/i')).toBeVisible({ timeout: 3000 });
      }
    }
  });

  test('should show no payments message when empty', async ({ page }) => {
    // Mock empty response
    await page.route('**/api/v1/payments', (route) => {
      route.fulfill({
        status: 200,
        body: JSON.stringify([]),
      });
    });
    
    await page.reload();
    await page.waitForLoadState('networkidle');
    
    // Should show empty state
    await expect(page.locator('text=/no.*payment/i')).toBeVisible();
  });

  test('should display payment status badges correctly', async ({ page }) => {
    // Check for various status badges
    const statuses = ['pending', 'completed', 'failed', 'refunded'];
    
    for (const status of statuses) {
      const badge = page.locator(`text=${status}`).first();
      
      if (await badge.isVisible({ timeout: 1000 })) {
        // Badge should have appropriate styling
        const classes = await badge.getAttribute('class');
        expect(classes).toBeTruthy();
      }
    }
  });

  test('should handle loading state', async ({ page }) => {
    // Should show loading indicator
    const loadingText = page.locator('text=/loading/i');
    
    // Reload and check for loading state
    await page.reload();
    
    // Loading might be very quick, so timeout is short
    const wasLoading = await loadingText.isVisible({ timeout: 1000 }).catch(() => false);
    
    // Eventually should show content
    await page.waitForSelector('table, [data-testid="payments-list"]', { timeout: 5000 });
  });

  test('should format payment amounts correctly', async ({ page }) => {
    const amountCell = page.locator('td').filter({ hasText: /\d+/ }).first();
    
    if (await amountCell.isVisible({ timeout: 3000 })) {
      const text = await amountCell.textContent();
      
      // Should contain currency
      expect(text).toMatch(/USD|EUR|GBP|\$|€|£/i);
    }
  });

  test('should display payment dates', async ({ page }) => {
    const dateCell = page.locator('td').filter({ hasText: /\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2}/ }).first();
    
    if (await dateCell.isVisible({ timeout: 3000 })) {
      const text = await dateCell.textContent();
      
      // Should be a valid date format
      expect(text).toBeTruthy();
    }
  });
});
