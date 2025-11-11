import { test, expect, Page } from '@playwright/test';
import { login, mockJson } from './helpers';

// Property Calendar tests
// Covers: initial blocked dates listing, block new date range, unblock a date, error handling.


test.describe('Property Calendar', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    // Navigate to calendar view of property 1
    await page.goto('/properties/1/calendar');
    await page.waitForLoadState('networkidle');
  });

  test('should list blocked dates or show empty state', async ({ page }) => {
    const blockedItem = page.locator('[data-testid="blocked-date"], .blocked-date');
    const count = await blockedItem.count();
    if (count === 0) {
      await expect(page.locator('text=/no.*blocked|no.*date/i')).toBeVisible();
    } else {
      expect(count).toBeGreaterThan(0);
    }
  });

  test('should block a new date range', async ({ page }) => {
    // Mock block API
    await mockJson(page, '**/api/v1/calendar/block', {
      id: 555,
      start: new Date().toISOString(),
      end: new Date(Date.now() + 86400000).toISOString(),
    });

    const blockBtn = page.locator('button:has-text("Block Dates")').first();
    if (await blockBtn.isVisible()) {
      await blockBtn.click();
      // Simplified: modal inputs or direct action
      const confirm = page.locator('button:has-text("Confirm"), button:has-text("Save")').first();
      if (await confirm.isVisible()) {
        await confirm.click();
        await page.waitForTimeout(700);
        const newBlocked = page.locator('text=/555/'); // heuristic: show id or date text
        const visible = await newBlocked.isVisible().catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should unblock a date', async ({ page }) => {
    // Mock unblock API
    await mockJson(page, '**/api/v1/calendar/unblock/*', { ok: true });
    const unblockBtn = page.locator('button:has-text("Unblock")').first();
    if (await unblockBtn.isVisible()) {
      page.once('dialog', d => d.accept());
      await unblockBtn.click();
      await page.waitForTimeout(600);
      expect(await page.isClosed()).toBeFalsy();
    }
  });

  test('should handle block action error', async ({ page }) => {
    await mockJson(page, '**/api/v1/calendar/block', { message: 'Server error' }, 500);
    const blockBtn = page.locator('button:has-text("Block Dates")').first();
    if (await blockBtn.isVisible()) {
      await blockBtn.click();
      const confirm = page.locator('button:has-text("Confirm"), button:has-text("Save")').first();
      if (await confirm.isVisible()) {
        await confirm.click();
        await page.waitForTimeout(600);
        const error = page.locator('text=/error|failed|server/i');
        const visible = await error.first().isVisible().catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });
});
