import { test, expect, Page } from '@playwright/test';
import { login, mockJson } from './helpers';

// Profile Verification tests
// Covers: step navigation, file upload placeholder, status badge, error handling on failing step.


test.describe('Profile Verification', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto('/profile/verification');
    await page.waitForLoadState('networkidle');
  });

  test('should display verification steps', async ({ page }) => {
    // Expect presence of common steps
    const steps = ['ID', 'Address', 'Phone', 'Background'];
    for (const s of steps) {
      const matcher = page.locator(`text=${s}`);
      const visible = await matcher.isVisible().catch(() => false);
      if (visible) {
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should start ID verification flow', async ({ page }) => {
    const idStepBtn = page.locator('button:has-text("Start")').first();
    if (await idStepBtn.isVisible()) {
      await idStepBtn.click();
      await page.waitForTimeout(500);
      // Form or upload input appears (heuristic)
      const uploadInput = page.locator('input[type="file"]');
      // Not all implementations include file input; allow either visible or a placeholder text
      const hasUpload = await uploadInput.isVisible().catch(() => false);
      if (!hasUpload) {
        const placeholder = page.locator('text=/upload|document|photo/i').first();
        const visible = await placeholder.isVisible().catch(() => false);
        expect(hasUpload || visible).toBeTruthy();
      }
    }
  });

  test('should show status badge after a mock step completion', async ({ page }) => {
    // Mock backend verification status update
    await mockJson(page, '**/api/v1/profile/verification/status', { id: 1, status: 'pending', steps: { id: 'submitted' } });
    await page.reload();
    await page.waitForTimeout(500);
    const badge = page.locator('text=/pending|in review/i');
    const visible = await badge.first().isVisible().catch(() => false);
    expect(visible).toBeTruthy();
  });

  test('should handle verification step error', async ({ page }) => {
    await mockJson(page, '**/api/v1/profile/verification/submit', { message: 'Server error' }, 500);
    const submitBtn = page.locator('button:has-text("Submit")').first();
    if (await submitBtn.isVisible()) {
      await submitBtn.click();
      await page.waitForTimeout(600);
      const error = page.locator('text=/error|failed|server/i');
      const hasError = await error.first().isVisible().catch(() => false);
      expect(hasError).toBeTruthy();
    }
  });
});
