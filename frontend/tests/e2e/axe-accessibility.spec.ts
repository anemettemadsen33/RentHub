/**
 * Automated Accessibility Testing with Axe-core
 * 
 * Runs axe-core accessibility checker on critical pages
 */

import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const criticalPages = [
  { url: '/', name: 'Homepage' },
  { url: '/properties', name: 'Property Listings' },
  { url: '/login', name: 'Login Page' },
  { url: '/register', name: 'Registration Page' },
  { url: '/dashboard', name: 'Dashboard' },
];

test.describe('Axe Accessibility Audit', () => {
  for (const page of criticalPages) {
    test(`${page.name} should have no WCAG violations`, async ({ page: playwright }) => {
      await playwright.goto(page.url);
      
      // Wait for page to be fully loaded
      await playwright.waitForLoadState('networkidle');
      await playwright.waitForTimeout(2000);

      const accessibilityScanResults = await new AxeBuilder({ page: playwright })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

      // Log violations for debugging
      if (accessibilityScanResults.violations.length > 0) {
        console.log(`\n🔴 Accessibility violations on ${page.name}:`);
        accessibilityScanResults.violations.forEach((violation, index) => {
          console.log(`\n${index + 1}. ${violation.id} (${violation.impact})`);
          console.log(`   ${violation.description}`);
          console.log(`   Help: ${violation.helpUrl}`);
          console.log(`   Affected elements: ${violation.nodes.length}`);
          violation.nodes.slice(0, 3).forEach(node => {
            console.log(`   - ${node.html}`);
          });
        });
      }

      // Assert no critical or serious violations
      const criticalViolations = accessibilityScanResults.violations.filter(
        v => v.impact === 'critical' || v.impact === 'serious'
      );

      expect(criticalViolations).toHaveLength(0);
    });
  }
});

test.describe('Axe Accessibility - Specific Components', () => {
  test('Property card should be accessible', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('[data-testid="property-card"]', { timeout: 10000 });

    const accessibilityScanResults = await new AxeBuilder({ page })
      .include('[data-testid="property-card"]')
      .analyze();

    expect(accessibilityScanResults.violations).toHaveLength(0);
  });

  test('Navigation menu should be accessible', async ({ page }) => {
    await page.goto('/');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .include('nav')
      .analyze();

    const criticalViolations = accessibilityScanResults.violations.filter(
      v => v.impact === 'critical' || v.impact === 'serious'
    );

    expect(criticalViolations).toHaveLength(0);
  });

  test('Filter panel should be accessible', async ({ page }) => {
    await page.goto('/properties');
    
    const filtersButton = page.getByRole('button', { name: /filters/i });
    await filtersButton.click();
    
    await page.waitForSelector('[data-testid="filter-panel"]', { timeout: 5000 });

    const accessibilityScanResults = await new AxeBuilder({ page })
      .include('[data-testid="filter-panel"]')
      .analyze();

    expect(accessibilityScanResults.violations).toHaveLength(0);
  });
});

test.describe('Axe Accessibility - Color Contrast', () => {
  test('should have sufficient color contrast ratios', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .withTags(['cat.color'])
      .analyze();

    // Log contrast violations
    const contrastViolations = accessibilityScanResults.violations.filter(
      v => v.id === 'color-contrast'
    );

    if (contrastViolations.length > 0) {
      console.log('\n⚠️ Color contrast violations:');
      contrastViolations.forEach(violation => {
        violation.nodes.forEach(node => {
          console.log(`   - ${node.html}`);
          console.log(`     ${node.failureSummary}`);
        });
      });
    }

    expect(contrastViolations).toHaveLength(0);
  });
});

test.describe('Axe Accessibility - Form Controls', () => {
  test('form inputs should have labels', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .withTags(['cat.forms'])
      .analyze();

    const formViolations = accessibilityScanResults.violations.filter(
      v => ['label', 'form-field-multiple-labels'].includes(v.id)
    );

    expect(formViolations).toHaveLength(0);
  });
});

test.describe('Axe Accessibility - Keyboard Navigation', () => {
  test('interactive elements should be keyboard accessible', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForLoadState('networkidle');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .withTags(['cat.keyboard'])
      .analyze();

    expect(accessibilityScanResults.violations).toHaveLength(0);
  });
});
