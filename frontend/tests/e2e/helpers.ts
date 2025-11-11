import { Page, expect } from '@playwright/test';

/** Shared login helper (email/password test user). */
export async function login(page: Page, opts: { email?: string; password?: string } = {}) {
  const { email = 'test@example.com', password = 'password123' } = opts;
  await page.goto('/auth/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  
  // Wait for any toast/overlay to disappear before clicking submit
  // Check for common overlay classes (adjust to match your app's actual selectors)
  const overlay = page.locator('[class*="fixed"][class*="z-50"]:visible, [role="dialog"]:visible');
  const overlayCount = await overlay.count();
  if (overlayCount > 0) {
    // Wait for overlay to be hidden (max 3s)
    await page.waitForFunction(
      () => {
        const els = document.querySelectorAll('[class*="fixed"][class*="z-50"], [role="dialog"]');
        return Array.from(els).every(el => {
          const rect = el.getBoundingClientRect();
          return rect.width === 0 || rect.height === 0 || window.getComputedStyle(el).display === 'none';
        });
      },
      { timeout: 3000 }
    ).catch(() => {});
  }
  
  // Force click in center of submit button to bypass any remaining overlays
  const submitBtn = page.locator('button[type="submit"]').first();
  await submitBtn.click({ force: true });
  
  // Dashboard or root redirect
  await page.waitForURL(/dashboard|\/?$/, { timeout: 10000 });
}

/** Wait for app hydration marker or fallback to main layout elements. */
export async function waitForAppReady(page: Page) {
  try {
    await page.waitForSelector('[data-testid="hydration-ready"]', { timeout: 5000 });
  } catch {
    await page.waitForSelector('nav, header, main', { timeout: 5000 });
  }
}

/** Generic route mock returning JSON. */
export async function mockJson(page: Page, pattern: string, body: any, status = 200, headers: Record<string,string> = {}) {
  await page.route(pattern, route => {
    route.fulfill({ status, body: JSON.stringify(body), headers: { 'Content-Type': 'application/json', ...headers } });
  });
}

/** Assert toast presence by partial text (case-insensitive). */
export async function expectToast(page: Page, textPattern: RegExp) {
  const toast = page.locator(`text=${textPattern.source}`);
  const visible = await toast.first().isVisible().catch(() => false);
  expect(visible).toBeTruthy();
}

/** Safe click: only clicks if visible to reduce flakiness. */
export async function safeClick(locator: ReturnType<Page['locator']>) {
  if (await locator.isVisible()) {
    await locator.click();
  }
}

/** Fill input if visible. */
export async function safeFill(locator: ReturnType<Page['locator']>, value: string) {
  if (await locator.isVisible()) {
    await locator.fill(value);
  }
}
