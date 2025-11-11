import { test, expect } from '@playwright/test';

// Basic visual regression snapshots. Run first with: npx playwright test --update-snapshots
// Ensure consistent visuals by disabling animations and waiting for network idle.
async function prepareStableUI(page: any) {
  await page.addStyleTag({ content: '* { animation: none !important; transition: none !important; }' });
  // Hide dynamic toast / cookie bars
  await page.evaluate(() => {
    document.querySelectorAll('[class*="toast"], [class*="cookie"], [class*="banner"]').forEach(el => {
      (el as HTMLElement).style.display = 'none';
    });
  });
}

// Opt-in via VISUAL=1 to avoid failures on first run or dynamic content changes.
const VISUAL_ENABLED = process.env.VISUAL === '1';

test.describe('Visual Snapshots', () => {
  test.skip(!VISUAL_ENABLED, 'Visual snapshots disabled. Set VISUAL=1 to enable.');
  test('homepage snapshot', async ({ page }) => {
    await page.goto('http://localhost:3000');
    await page.waitForLoadState('networkidle');
    await prepareStableUI(page);
    expect(await page.screenshot()).toMatchSnapshot('home.png');
  });

  test('register page snapshot', async ({ page }) => {
    await page.goto('http://localhost:3000/auth/register');
    await page.waitForLoadState('networkidle');
    await prepareStableUI(page);
    expect(await page.screenshot()).toMatchSnapshot('register.png');
  });

  test('login page snapshot', async ({ page }) => {
    await page.goto('http://localhost:3000/auth/login');
    await page.waitForLoadState('networkidle');
    await prepareStableUI(page);
    expect(await page.screenshot()).toMatchSnapshot('login.png');
  });
});
