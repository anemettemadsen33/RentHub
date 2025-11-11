import { test, expect, Page } from '@playwright/test';
import { login, mockJson } from './helpers';

// Security Audit tests
// Covers: logs visible, anomalies mocked and visible, error handling on fetch.


test.describe('Security Audit', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    // Navigate to security audit view; assuming a property-scoped page
    await page.goto('/properties/1/analytics');
    await page.waitForLoadState('networkidle');
  });

  test('should display security logs or empty state', async ({ page }) => {
    const logItem = page.locator('[data-testid="security-log"], .security-log');
    const count = await logItem.count();
    if (count === 0) {
      await expect(page.locator('text=/no.*log|no.*activity/i')).toBeVisible();
    } else {
      expect(count).toBeGreaterThan(0);
    }
  });

  test('should display anomalies when mocked', async ({ page }) => {
    await mockJson(page, '**/api/v1/security/anomalies*', [
      { id: 1, type: 'door-forced', at: new Date().toISOString() },
      { id: 2, type: 'multiple-failed-attempts', at: new Date().toISOString() }
    ]);
    await page.reload();
    await page.waitForTimeout(500);
    await expect(page.locator('text=/door|forced|failed/i')).toBeVisible();
  });

  test('should handle audit fetch error', async ({ page }) => {
    await mockJson(page, '**/api/v1/security/logs*', { message: 'Server error' }, 500);
    await page.reload();
    await page.waitForTimeout(500);
    const error = page.locator('text=/error|failed|server/i');
    const hasError = await error.first().isVisible().catch(() => false);
    expect(hasError).toBeTruthy();
  });
});
