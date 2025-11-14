import { Page, expect } from '@playwright/test';

export async function waitForAppReady(page: Page, timeout = 15000) {
  const start = Date.now();
  // Ensure DOM is loaded first
  await page.waitForLoadState('domcontentloaded', { timeout });

  const remaining = () => Math.max(500, timeout - (Date.now() - start));

  // Prefer an explicit app-ready marker if present
  try {
    await page.waitForSelector('[data-app-ready="true"]', { state: 'visible', timeout: remaining() });
    return;
  } catch {}

  // Otherwise, wait for body to be visible and not hidden
  try {
    await expect(page.locator('body')).toBeVisible({ timeout: remaining() });
  } catch {}

  // Then wait for any meaningful content area
  const candidates = ['main:visible', '[role="main"]:visible', '#__next :visible'];
  for (const sel of candidates) {
    try {
      await page.waitForSelector(sel, { state: 'visible', timeout: remaining() });
      return;
    } catch {}
  }
}

export async function gotoReady(page: Page, url: string, timeout = 15000) {
  await page.goto(url);
  await waitForAppReady(page, timeout);
}
