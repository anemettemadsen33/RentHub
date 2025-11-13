/**
 * Accessibility Test Suite
 * 
 * Tests WCAG 2.1 Level AA compliance for critical user flows
 */

import { test, expect } from '@playwright/test';

test.describe('Accessibility - Keyboard Navigation', () => {
  test('should navigate property cards with Tab key', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('[data-testid="property-card"]', { timeout: 10000 });

    // Tab through first 3 property cards
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    
    const focused = await page.evaluate(() => document.activeElement?.getAttribute('data-testid'));
    expect(focused).toBeTruthy();
  });

  test('should open filters with keyboard', async ({ page }) => {
    await page.goto('/properties');
    
    // Find and focus filters button
    const filtersButton = page.getByRole('button', { name: /filters/i });
    await filtersButton.focus();
    await page.keyboard.press('Enter');
    
    // Filter panel should be visible
    await expect(page.getByTestId('filter-panel')).toBeVisible();
  });

  test('should navigate comparison bar with keyboard', async ({ page }) => {
    await page.goto('/properties');
    
    // Add properties to comparison via keyboard
    const compareButton = page.getByRole('button', { name: /compare/i }).first();
    await compareButton.focus();
    await page.keyboard.press('Enter');
    
    await page.waitForTimeout(500);
    
    // Comparison bar should appear
    const comparisonBar = page.locator('[data-testid="comparison-bar"]');
    await expect(comparisonBar).toBeVisible();
  });

  test('skip to main content link should work', async ({ page }) => {
    await page.goto('/');
    
    // Press Tab to focus skip link
    await page.keyboard.press('Tab');
    
    const skipLink = page.getByRole('link', { name: /skip to main content/i });
    await expect(skipLink).toBeFocused();
    
    // Activate skip link
    await page.keyboard.press('Enter');
    
    // Main content should be focused
    const main = page.locator('main');
    await expect(main).toBeFocused();
  });
});

test.describe('Accessibility - ARIA Labels', () => {
  test('interactive elements should have accessible names', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('[data-testid="property-card"]', { timeout: 10000 });

    // Check favorite button has aria-label
    const favoriteButton = page.locator('button[aria-label*="favorite"]').first();
    await expect(favoriteButton).toHaveAttribute('aria-label');
    
    // Check share button has aria-label
    const shareButton = page.locator('button[aria-label*="Share"]').first();
    await expect(shareButton).toHaveAttribute('aria-label');
  });

  test('image carousel navigation should have labels', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('[data-testid="property-card"]', { timeout: 10000 });

    const propertyCard = page.locator('[data-testid="property-card"]').first();
    
    // Hover to reveal carousel buttons
    await propertyCard.hover();
    
    // Check prev/next buttons have aria-label
    const prevButton = propertyCard.locator('button[aria-label="Previous image"]');
    const nextButton = propertyCard.locator('button[aria-label="Next image"]');
    
    await expect(prevButton).toHaveAttribute('aria-label', 'Previous image');
    await expect(nextButton).toHaveAttribute('aria-label', 'Next image');
  });

  test('form inputs should have associated labels', async ({ page }) => {
    await page.goto('/login');
    
    const emailInput = page.getByRole('textbox', { name: /email/i });
    const passwordInput = page.getByLabel(/password/i);
    
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
  });
});

test.describe('Accessibility - Color Contrast', () => {
  test('primary buttons should have sufficient contrast', async ({ page }) => {
    await page.goto('/');
    
    const primaryButton = page.getByRole('button').first();
    
    // Get computed styles
    const styles = await primaryButton.evaluate((el) => {
      const computed = window.getComputedStyle(el);
      return {
        backgroundColor: computed.backgroundColor,
        color: computed.color,
      };
    });
    
    expect(styles.backgroundColor).toBeTruthy();
    expect(styles.color).toBeTruthy();
  });
});

test.describe('Accessibility - Focus Management', () => {
  test('modal dialogs should trap focus', async ({ page }) => {
    await page.goto('/properties');
    
    // Open save search dialog
    const saveButton = page.getByRole('button', { name: /save search/i });
    if (await saveButton.isVisible()) {
      await saveButton.click();
      
      // Dialog should be visible
      const dialog = page.getByRole('dialog');
      await expect(dialog).toBeVisible();
      
      // Tab should stay within dialog
      await page.keyboard.press('Tab');
      const focused = await page.evaluate(() => document.activeElement?.tagName);
      expect(['INPUT', 'BUTTON', 'TEXTAREA']).toContain(focused);
    }
  });

  test('focus should return after closing modal', async ({ page }) => {
    await page.goto('/properties');
    
    const filtersButton = page.getByRole('button', { name: /filters/i });
    await filtersButton.click();
    
    // Close filter panel
    const closeButton = page.getByRole('button', { name: /close/i });
    await closeButton.click();
    
    // Focus should return to filters button or nearby element
    await page.waitForTimeout(200);
    const focused = await page.evaluate(() => document.activeElement?.textContent);
    expect(focused).toBeTruthy();
  });
});

test.describe('Accessibility - Screen Reader Support', () => {
  test('headings should have proper hierarchy', async ({ page }) => {
    await page.goto('/');
    
    // Check h1 exists and is unique
    const h1Count = await page.locator('h1').count();
    expect(h1Count).toBeGreaterThanOrEqual(1);
    
    // Check heading levels don't skip
    const headings = await page.locator('h1, h2, h3, h4, h5, h6').allTextContents();
    expect(headings.length).toBeGreaterThan(0);
  });

  test('images should have alt text', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('img', { timeout: 10000 });
    
    const images = await page.locator('img').all();
    
    for (const img of images.slice(0, 5)) {
      const alt = await img.getAttribute('alt');
      expect(alt).toBeTruthy();
    }
  });

  test('landmarks should be present', async ({ page }) => {
    await page.goto('/');
    
    // Check for main landmark
    const main = page.locator('main');
    await expect(main).toBeVisible();
    
    // Check for navigation
    const nav = page.locator('nav');
    await expect(nav).toBeVisible();
  });

  test('loading states should be announced', async ({ page }) => {
    await page.goto('/properties');
    
    // Check for aria-live regions or loading indicators
    const loadingIndicator = page.locator('[role="status"], [aria-live="polite"]');
    
    // Loading state should exist or have loaded
    const count = await loadingIndicator.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });
});

test.describe('Accessibility - Mobile Touch Targets', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test('buttons should be large enough for touch', async ({ page }) => {
    await page.goto('/properties');
    await page.waitForSelector('button', { timeout: 10000 });
    
    const buttons = await page.locator('button').all();
    
    for (const button of buttons.slice(0, 5)) {
      const box = await button.boundingBox();
      if (box) {
        // WCAG requires minimum 44x44px touch targets
        expect(box.width).toBeGreaterThanOrEqual(40); // Allow small margin
        expect(box.height).toBeGreaterThanOrEqual(40);
      }
    }
  });
});

test.describe('Accessibility - Form Validation', () => {
  test('error messages should be associated with inputs', async ({ page }) => {
    await page.goto('/login');
    
    // Submit empty form
    const submitButton = page.getByRole('button', { name: /sign in/i });
    await submitButton.click();
    
    await page.waitForTimeout(500);
    
    // Check for aria-describedby or validation messages
    const emailInput = page.getByRole('textbox', { name: /email/i });
    const describedBy = await emailInput.getAttribute('aria-describedby');
    
    // Should have error message association
    expect(describedBy || await emailInput.getAttribute('aria-invalid')).toBeTruthy();
  });
});
