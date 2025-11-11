import { test, expect } from '@playwright/test';

// NOTE: This is a placeholder. Replace `EXISTING_BOOKING_ID` with a real booking id from seeded data.
const EXISTING_BOOKING_ID = process.env.E2E_BOOKING_ID || '1';

test.describe('Booking Detail Page', () => {
  test('loads booking detail (requires authenticated session)', async ({ page }) => {
    // Precondition: user should already be authenticated (e.g., via global setup or storage state)
    await page.goto(`/bookings/${EXISTING_BOOKING_ID}`);
    // Basic smoke assertions
    await expect(page.locator('h1')).toContainText(/Booking Details|Detalii Rezervare/i);
    // Timeline component should render
    await expect(page.locator('text=Status')).toBeVisible();
  });
});
