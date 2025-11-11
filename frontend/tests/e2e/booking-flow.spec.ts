import { test, expect, Page } from '@playwright/test';
import { login, waitForAppReady, mockJson, safeClick, safeFill } from './helpers';

// Booking Flow E2E (happy path with light mocking where necessary)
// Steps:
// 1) User visits /properties and filters/searches
// 2) Opens a property detail page
// 3) Starts booking flow (dates -> guest details)
// 4) Proceeds to payment and completes (mocked)

async function selectFirstProperty(page: Page) {
  const cards = page.locator('[data-testid="property-card"]');
  const count = await cards.count();
  if (count === 0) {
    // fallback: find any link to property detail
    const anyLink = page.locator('a[href^="/properties/"]').first();
    await anyLink.click();
  } else {
    await cards.first().click();
  }
}

test.describe('Booking Flow', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should complete a booking with mocked payment', async ({ page }) => {
    // Search/filters
    await page.goto('/properties');
    await waitForAppReady(page);
    await safeClick(page.locator('[data-testid="filters-button"]'));
    await safeFill(page.locator('[data-testid="min-price"]'), '100');
    await safeClick(page.locator('[data-testid="apply-filters"]'));

    // Open property
    await selectFirstProperty(page);
    await waitForAppReady(page);

    // Choose dates (heuristic: pick today + tomorrow if date picker present)
    const dateInputs = page.locator('input[type="date"], [data-testid="checkin"], [data-testid="checkout"]');
    const hasDateInputs = (await dateInputs.count()) > 0;
    if (hasDateInputs) {
      const today = new Date();
      const ymd = (d: Date) => d.toISOString().slice(0,10);
      const tomorrow = new Date(today.getTime() + 86400000);
      await safeFill(dateInputs.nth(0), ymd(today));
      if ((await dateInputs.count()) > 1) await safeFill(dateInputs.nth(1), ymd(tomorrow));
    }

    // Start booking
    await safeClick(page.locator('button:has-text("Book"), button:has-text("Reserve"), a:has-text("Book")').first());
    await page.waitForLoadState('networkidle');

    // Guest details (heuristic fields)
    await safeFill(page.locator('input[name="firstName"]'), 'John');
    await safeFill(page.locator('input[name="lastName"]'), 'Doe');
    await safeFill(page.locator('input[name="email"]'), 'john.doe@example.com');

    // Continue to payment
    await safeClick(page.locator('button:has-text("Continue"), button:has-text("Next")').first());

    // Mock payment endpoint
    await mockJson(page, '**/api/v1/payments/charge', { id: 'ch_123', status: 'succeeded' }, 200);

    // Enter card details (if present) - placeholder fields
    await safeFill(page.locator('input[name="cardNumber"]'), '4242424242424242');
    await safeFill(page.locator('input[name="exp"]'), '12/30');
    await safeFill(page.locator('input[name="cvc"]'), '123');

    // Pay
    await safeClick(page.locator('button:has-text("Pay"), button:has-text("Complete Payment")').first());

    // Confirmation page assertion
    await page.waitForLoadState('networkidle');
    const success = page.locator('text=/booking confirmed|thank you|payment successful/i');
    const visible = await success.first().isVisible().catch(() => false);
    expect(visible).toBeTruthy();
  });
});
