import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete User Profile Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    formHelper = new FormHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should view user profile', async ({ page }) => {
    await navHelper.goToProfile();
    
    await expect(page.locator('h1, h2')).toContainText(/profile|account/i);
    const inputs = page.locator('input[name="name"], input[name="email"]');
    const count = await inputs.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });

  test('should update profile information', async ({ page }) => {
    await navHelper.goToProfile();
    
    await page.fill('input[name="name"]', 'Updated Name');
    await page.fill('input[name="phone"]', '+1234567890');
    await formHelper.submitForm();
    
    await expect(page.locator('text=/updated|saved successfully/i')).toBeVisible({ timeout: 10000 });
  });

  test('should upload profile picture', async ({ page }) => {
    await navHelper.goToProfile();
    
    const uploadButton = page.locator('input[type="file"], button:has-text("Upload")');
    if (await uploadButton.first().isVisible()) {
      // Create a test file
      const buffer = Buffer.from('fake image data');
      await uploadButton.first().setInputFiles({
        name: 'avatar.jpg',
        mimeType: 'image/jpeg',
        buffer: buffer,
      });
      
      await page.waitForTimeout(2000);
    }
  });

  test('should change password', async ({ page }) => {
    await navHelper.goToProfile();
    
    const changePasswordButton = page.locator('button:has-text("Change Password"), a:has-text("Change Password")');
    if (await changePasswordButton.isVisible()) {
      await changePasswordButton.click();
      
      await page.fill('input[name="current_password"]', 'password123');
      await page.fill('input[name="new_password"]', 'NewPassword123!');
      await page.fill('input[name="new_password_confirmation"]', 'NewPassword123!');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/password.*changed|password.*updated/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should update notification preferences', async ({ page }) => {
    await navHelper.goToSettings();
    
    const emailNotifications = page.locator('input[name="email_notifications"], input[type="checkbox"]').first();
    if (await emailNotifications.isVisible()) {
      await emailNotifications.check();
      await formHelper.submitForm();
      
      await expect(page.locator('text=/saved|updated/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should add payment method', async ({ page }) => {
    await navHelper.goToSettings();
    
    const addPaymentButton = page.locator('button:has-text("Add Payment"), button:has-text("Add Card")');
    if (await addPaymentButton.isVisible()) {
      await addPaymentButton.click();
      
      await page.fill('input[name="card_number"]', '4242424242424242');
      await page.fill('input[name="exp_month"]', '12');
      await page.fill('input[name="exp_year"]', '2025');
      await page.fill('input[name="cvc"]', '123');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/payment method added|card added/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view transaction history', async ({ page }) => {
    await page.goto('/payments');
    
    await expect(page.locator('text=/transactions|payment history/i')).toBeVisible();
  });

  test('should delete account with confirmation', async ({ page }) => {
    await navHelper.goToSettings();
    
    const deleteButton = page.locator('button:has-text("Delete Account"), button:has-text("Close Account")');
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      
      // Should show confirmation dialog
      await expect(page.locator('[role="dialog"], .modal')).toBeVisible();
      
      // Cancel deletion
      await page.click('button:has-text("Cancel")');
    }
  });

  test('should update language preference', async ({ page }) => {
    await navHelper.goToSettings();
    
    const languageSelect = page.locator('select[name="language"], button:has-text("Language")');
    if (await languageSelect.first().isVisible()) {
      await languageSelect.first().click();
      // cSpell:ignore Română
      const languageOption = page.locator('text=/English|Română/i').first();
      await languageOption.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should enable two-factor authentication', async ({ page }) => {
    await navHelper.goToSettings();
    
    const twoFactorButton = page.locator('button:has-text("Enable 2FA"), button:has-text("Two-Factor")');
    if (await twoFactorButton.isVisible()) {
      await twoFactorButton.click();
      
      // Should show QR code or setup instructions
      await expect(page.locator('text=/scan|QR code|authenticator/i')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should view and edit bio', async ({ page }) => {
    await navHelper.goToProfile();
    
    const bioField = page.locator('textarea[name="bio"], textarea[name="description"]');
    if (await bioField.isVisible()) {
      await bioField.fill('This is my updated bio text.');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/saved|updated/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
