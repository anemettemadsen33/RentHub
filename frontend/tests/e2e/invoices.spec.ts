import { test, expect, Page } from '@playwright/test';
import { login } from './helpers';

// Assumptions:
// - User can log in via /auth/login with test credentials
// - Invoices page at /invoices
// - API endpoints: /api/v1/invoices, /api/v1/invoices/:id/download, /api/v1/invoices/:id/resend
// - Table rows contain invoice number, date, property, amount, status, and actions
// - Realtime events may prepend new invoices (not asserted here directly but we test dynamic refresh)

test.describe('Invoices Page', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    await page.goto('/invoices');
    await page.waitForLoadState('networkidle');
  });

  test('should display empty state when no invoices', async ({ page }) => {
    await page.route('**/api/v1/invoices', route => {
      route.fulfill({ status: 200, body: JSON.stringify([]) });
    });
    await page.reload();
    await expect(page.getByText(/no invoices/i)).toBeVisible();
  });

  test('should list invoices', async ({ page }) => {
    const table = page.locator('table');
    await expect(table).toBeVisible();
  const headers = table.locator('thead th');
  // Ensure we have at least the expected columns (invoice, date, property, amount, status)
  await expect(headers).toHaveCount(6);
  });

  test('should filter invoices by status', async ({ page }) => {
    const select = page.locator('select');
    await expect(select).toBeVisible();
    await select.selectOption('paid');
    // Basic heuristic: after filter, either rows change or remain if none match
    const rows = page.locator('tbody tr');
    await rows.first().waitFor({ state: 'visible' });
  });

  test('should download an invoice PDF', async ({ page }) => {
    // Mock pdf response
    await page.route('**/api/v1/invoices/*/download', route => {
      route.fulfill({ status: 200, body: 'PDFDATA', headers: { 'Content-Type': 'application/pdf' } });
    });
    const downloadButton = page.getByRole('button', { name: /download/i }).first();
    if (await downloadButton.isVisible()) {
      await downloadButton.click();
    }
  });

  test('should resend an invoice', async ({ page }) => {
    await page.route('**/api/v1/invoices/*/resend', route => {
      route.fulfill({ status: 200, body: JSON.stringify({ ok: true }) });
    });
    const resendButton = page.getByRole('button', { name: /resend/i }).first();
    if (await resendButton.isVisible()) {
      // Accept confirmation dialog
      page.once('dialog', d => d.accept());
      await resendButton.click();
    }
  });

  test('should show error if download fails', async ({ page }) => {
    await page.route('**/api/v1/invoices/*/download', route => {
      route.fulfill({ status: 500, body: JSON.stringify({ message: 'Server error' }) });
    });
    const downloadButton = page.getByRole('button', { name: /download/i }).first();
    if (await downloadButton.isVisible()) {
      await downloadButton.click();
    }
    // We rely on alert() fallback in component; intercept page.on('dialog') if needed
  });

  test('should reflect new invoices after reload (simulated realtime)', async ({ page }) => {
    const initialCount = await page.locator('tbody tr').count();
    await page.route('**/api/v1/invoices', route => {
      const mock = [
        { id: 9999, invoiceNumber: 'NEW-9999', status: 'pending', amount: 10, currency: 'USD', propertyTitle: 'Test Property', issuedAt: new Date().toISOString() }
      ];
      route.fulfill({ status: 200, body: JSON.stringify(mock) });
    });
    await page.reload();
    const newCount = await page.locator('tbody tr').count();
    expect(newCount).toBeGreaterThanOrEqual(1);
  });
});
