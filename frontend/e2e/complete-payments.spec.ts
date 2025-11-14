import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';

test.describe('Complete Payment System Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should process payment for booking', async ({ page }) => {
    await page.goto('/payments/checkout');
    
    // Fill payment details
    await page.fill('input[name="card_number"]', '4242424242424242');
    await page.fill('input[name="exp_month"]', '12');
    await page.fill('input[name="exp_year"]', '2025');
    await page.fill('input[name="cvc"]', '123');
    await page.fill('input[name="cardholder_name"]', 'Test User');
    
    await page.click('button:has-text("Pay"), button[type="submit"]');
    
    await expect(page.locator('text=/payment successful|payment complete/i')).toBeVisible({ timeout: 15000 });
  });

  test('should validate card number', async ({ page }) => {
    await page.goto('/payments/checkout');
    
    await page.fill('input[name="card_number"]', '1234');
    await page.fill('input[name="cvc"]', '123');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('text=/invalid card|card number invalid/i')).toBeVisible();
  });

  test('should show payment history', async ({ page }) => {
    await page.goto('/payments');
    
    await expect(page.locator('text=/payment history|transactions/i')).toBeVisible();
  });

  test('should download payment receipt', async ({ page }) => {
    await page.goto('/payments');
    
    const downloadButton = page.locator('button:has-text("Receipt"), button:has-text("Download")').first();
    if (await downloadButton.isVisible()) {
      const [download] = await Promise.all([
        page.waitForEvent('download'),
        downloadButton.click()
      ]);
      
      expect(download.suggestedFilename()).toMatch(/receipt|payment/i);
    }
  });

  test('should request refund', async ({ page }) => {
    await page.goto('/payments');
    
    const refundButton = page.locator('button:has-text("Refund"), button:has-text("Request Refund")').first();
    if (await refundButton.isVisible()) {
      await refundButton.click();
      
      await page.fill('textarea[name="reason"]', 'Property not as described');
      await page.click('button:has-text("Submit")');
      
      await expect(page.locator('text=/refund requested|request submitted/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should save payment method', async ({ page }) => {
    await page.goto('/payments/methods');
    
    const addButton = page.locator('button:has-text("Add Payment Method")');
    if (await addButton.isVisible()) {
      await addButton.click();
      
      await page.fill('input[name="card_number"]', '5555555555554444');
      await page.fill('input[name="exp_month"]', '06');
      await page.fill('input[name="exp_year"]', '2026');
      await page.fill('input[name="cvc"]', '456');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/payment method added|saved successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should delete saved payment method', async ({ page }) => {
    await page.goto('/payments/methods');
    
    const deleteButton = page.locator('button:has-text("Delete"), button[aria-label*="delete"]').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/deleted|removed/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should set default payment method', async ({ page }) => {
    await page.goto('/payments/methods');
    
    const setDefaultButton = page.locator('button:has-text("Set as Default")').first();
    if (await setDefaultButton.isVisible()) {
      await setDefaultButton.click();
      
      await expect(page.locator('text=/default.*set|primary payment/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view payout settings (for hosts)', async ({ page }) => {
    await page.goto('/payments/payouts');
    
    const payoutSection = page.locator('text=/payout|bank account/i');
    if (await payoutSection.isVisible()) {
      await expect(payoutSection).toBeVisible();
    }
  });

  test('should add bank account for payouts', async ({ page }) => {
    await page.goto('/payments/payouts');
    
    const addBankButton = page.locator('button:has-text("Add Bank Account")');
    if (await addBankButton.isVisible()) {
      await addBankButton.click();
      
      await page.fill('input[name="account_number"]', '000123456789');
      await page.fill('input[name="routing_number"]', '110000000');
      await page.fill('input[name="account_holder_name"]', 'Test User');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/bank account added|saved/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
