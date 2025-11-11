import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const paths = ['/property-comparison', '/saved-searches'];

for (const path of paths) {
  test(`axe: ${path} has no serious violations`, async ({ page }) => {
    await page.goto(path);
    // Wait for main content or a reasonable selector to stabilize the page
    await page.waitForLoadState('networkidle');

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa'])
      .analyze();

    const serious = results.violations.filter(v => ['serious', 'critical'].includes(v.impact || 'minor'));

    // Surface violations if any
    if (serious.length) {
      console.log(`Axe violations on ${path}:`);
      for (const v of serious) {
        console.log(`- ${v.id}: ${v.help} (${v.impact})`);
      }
    }

    expect(serious, `Axe serious violations found on ${path}`).toHaveLength(0);
  });
}
