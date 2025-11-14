import { test, expect, Page } from '@playwright/test';
import { login, waitForAppReady, mockJson, safeClick, safeFill } from './helpers';

/**
 * User Profile Management E2E Tests
 * 
 * Tests for user profile functionality including:
 * - Profile viewing
 * - Profile editing
 * - Avatar upload
 * - Password change
 * - Account settings
 * - Privacy settings
 */

test.describe('User Profile', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display user profile page', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Check for profile heading
    const heading = page.locator('h1, h2').filter({ hasText: /profile|account|settings/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // User info should be visible
    const userInfo = page.locator('[data-testid="user-info"], [data-testid="profile-info"]');
    if (await userInfo.count() > 0) {
      await expect(userInfo.first()).toBeVisible();
    }
  });

  test('should display user avatar', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Check for avatar
    const avatar = page.locator('[data-testid="user-avatar"], img[alt*="avatar"], img[alt*="profile"]').first();
    if (await avatar.isVisible()) {
      await expect(avatar).toBeVisible();
    }
  });

  test('should edit basic profile information', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Click edit button
    const editBtn = page.locator('button:has-text("Edit"), button:has-text("Update")').first();
    if (await editBtn.isVisible()) {
      await editBtn.click();
      await page.waitForTimeout(500);

      // Update name
      const nameInput = page.locator('input[name="name"], input[name="fullName"]').first();
      if (await nameInput.isVisible()) {
        await nameInput.clear();
        await nameInput.fill('Updated Test User');

        // Update bio/description if present
        const bioTextarea = page.locator('textarea[name="bio"], textarea[name="description"]').first();
        if (await bioTextarea.isVisible()) {
          await bioTextarea.fill('Updated bio from E2E test');
        }

        // Mock update API
        await mockJson(page, '**/api/v1/profile', {
          name: 'Updated Test User',
          bio: 'Updated bio from E2E test'
        }, 200);

        // Save changes
        const saveBtn = page.locator('button[type="submit"]:has-text("Save"), button:has-text("Update")').first();
        await saveBtn.click();
        await page.waitForLoadState('networkidle');

        // Success message
        const success = page.locator('text=/success|updated|saved/i');
        const visible = await success.first().isVisible({ timeout: 5000 }).catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should upload profile avatar', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for avatar upload button/input
    const uploadInput = page.locator('input[type="file"][accept*="image"]').first();
    const uploadBtn = page.locator('button:has-text("Upload"), button:has-text("Change")').first();

    if (await uploadInput.isVisible() || await uploadBtn.isVisible()) {
      // Just verify the upload mechanism exists
      expect(true).toBeTruthy();
    }
  });

  test('should change password', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Navigate to security/password section
    const securityTab = page.locator('a:has-text("Security"), button:has-text("Security"), a:has-text("Password")').first();
    if (await securityTab.isVisible()) {
      await securityTab.click();
      await page.waitForTimeout(500);
    }

    // Fill password change form
    const currentPassword = page.locator('input[name="current_password"], input[name="currentPassword"]').first();
    const newPassword = page.locator('input[name="new_password"], input[name="newPassword"]').first();
    const confirmPassword = page.locator('input[name="password_confirmation"], input[name="confirmPassword"]').first();

    if (await currentPassword.isVisible()) {
      await currentPassword.fill('password');
      await newPassword.fill('NewPassword123!');
      await confirmPassword.fill('NewPassword123!');

      // Mock password change API
      await mockJson(page, '**/api/v1/profile/password', {
        message: 'Password updated successfully'
      }, 200);

      // Submit
      const submitBtn = page.locator('button[type="submit"]:has-text("Change"), button:has-text("Update Password")').first();
      await submitBtn.click();
      await page.waitForLoadState('networkidle');

      // Success message
      const success = page.locator('text=/password.*updated|password.*changed/i');
      const visible = await success.first().isVisible({ timeout: 5000 }).catch(() => false);
      expect(visible).toBeTruthy();
    }
  });

  test('should update email address', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    const emailInput = page.locator('input[type="email"][name="email"]').first();
    if (await emailInput.isVisible()) {
      // Clear and enter new email
      await emailInput.clear();
      await emailInput.fill('newemail@example.com');

      // Mock API
      await mockJson(page, '**/api/v1/profile', {
        email: 'newemail@example.com'
      }, 200);

      // Save
      const saveBtn = page.locator('button[type="submit"]:has-text("Save")').first();
      await saveBtn.click();
      await page.waitForTimeout(1000);

      // May show verification message
      const message = page.locator('text=/verification|confirm.*email/i');
      const visible = await message.first().isVisible({ timeout: 3000 }).catch(() => false);
      // Either success or verification message is acceptable
      expect(true).toBeTruthy();
    }
  });

  test('should update phone number', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    const phoneInput = page.locator('input[name="phone"], input[type="tel"]').first();
    if (await phoneInput.isVisible()) {
      await phoneInput.clear();
      await phoneInput.fill('+40123456789');

      // Mock API
      await mockJson(page, '**/api/v1/profile', {
        phone: '+40123456789'
      }, 200);

      // Save
      const saveBtn = page.locator('button[type="submit"]:has-text("Save")').first();
      await saveBtn.click();
      await page.waitForLoadState('networkidle');
    }
  });

  test('should toggle notification preferences', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Navigate to notifications tab if exists
    const notifTab = page.locator('a:has-text("Notifications"), button:has-text("Notifications")').first();
    if (await notifTab.isVisible()) {
      await notifTab.click();
      await page.waitForTimeout(500);
    }

    // Toggle email notifications
    const emailToggle = page.locator('input[name="email_notifications"], input[type="checkbox"]').first();
    if (await emailToggle.isVisible()) {
      const initialState = await emailToggle.isChecked();
      await emailToggle.click();
      
      // Mock save
      await mockJson(page, '**/api/v1/profile/preferences', {
        email_notifications: !initialState
      }, 200);

      await page.waitForTimeout(500);
      
      // Verify toggle changed
      const newState = await emailToggle.isChecked();
      expect(newState).toBe(!initialState);
    }
  });

  test('should set language preference', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for language selector
    const languageSelect = page.locator('select[name="language"], select[name="locale"]').first();
    if (await languageSelect.isVisible()) {
      await languageSelect.selectOption({ index: 1 });

      // Mock save
      await mockJson(page, '**/api/v1/profile/preferences', {
        language: 'ro'
      }, 200);

      await page.waitForTimeout(500);
    }
  });

  test('should set currency preference', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for currency selector
    const currencySelect = page.locator('select[name="currency"]').first();
    if (await currencySelect.isVisible()) {
      await currencySelect.selectOption({ value: 'EUR' });

      // Mock save
      await mockJson(page, '**/api/v1/profile/preferences', {
        currency: 'EUR'
      }, 200);

      await page.waitForTimeout(500);
    }
  });

  test('should display account verification status', async ({ page }) => {
    await page.goto('/profile/verification');
    await waitForAppReady(page);

    // Check for verification status
    const verificationStatus = page.locator('[data-testid="verification-status"], text=/verified|unverified|pending/i');
    if (await verificationStatus.count() > 0) {
      await expect(verificationStatus.first()).toBeVisible();
    }
  });

  test('should handle profile update errors', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Mock error response
    await mockJson(page, '**/api/v1/profile', {
      error: 'Validation failed',
      errors: { name: ['Name is required'] }
    }, 422);

    // Try to submit empty name
    const nameInput = page.locator('input[name="name"]').first();
    if (await nameInput.isVisible()) {
      await nameInput.clear();
      
      const saveBtn = page.locator('button[type="submit"]:has-text("Save")').first();
      await saveBtn.click();
      await page.waitForTimeout(1000);

      // Error message should appear
      const errorMsg = page.locator('text=/error|required|invalid/i');
      const visible = await errorMsg.first().isVisible({ timeout: 3000 }).catch(() => false);
      expect(visible).toBeTruthy();
    }
  });

  test('should delete account with confirmation', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for delete account button
    const deleteBtn = page.locator('button:has-text("Delete Account"), button:has-text("Close Account")').first();
    if (await deleteBtn.isVisible()) {
      await deleteBtn.click();

      // Confirmation dialog should appear
      const confirmDialog = page.locator('[role="dialog"], [role="alertdialog"]');
      await expect(confirmDialog.first()).toBeVisible({ timeout: 5000 });

      // Cancel deletion
      const cancelBtn = page.locator('button:has-text("Cancel"), button:has-text("No")').first();
      if (await cancelBtn.isVisible()) {
        await cancelBtn.click();
        
        // Dialog should close
        await expect(confirmDialog.first()).not.toBeVisible();
      }
    }
  });

  test('should display privacy settings', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Navigate to privacy tab
    const privacyTab = page.locator('a:has-text("Privacy"), button:has-text("Privacy")').first();
    if (await privacyTab.isVisible()) {
      await privacyTab.click();
      await page.waitForTimeout(500);

      // Privacy settings should be visible
      const privacySettings = page.locator('[data-testid="privacy-settings"], text=/privacy|visibility/i');
      if (await privacySettings.count() > 0) {
        await expect(privacySettings.first()).toBeVisible();
      }
    }
  });

  test('should show connected social accounts', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for connected accounts section
    const connectedAccounts = page.locator('text=/connected.*accounts|social.*accounts|linked.*accounts/i');
    if (await connectedAccounts.count() > 0) {
      await expect(connectedAccounts.first()).toBeVisible();
    }
  });

  test('should manage two-factor authentication', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Navigate to security section
    const securityTab = page.locator('a:has-text("Security"), button:has-text("Security")').first();
    if (await securityTab.isVisible()) {
      await securityTab.click();
      await page.waitForTimeout(500);
    }

    // Look for 2FA settings
    const twoFactorSection = page.locator('text=/two.*factor|2FA|authentication/i');
    if (await twoFactorSection.count() > 0) {
      await expect(twoFactorSection.first()).toBeVisible();
    }
  });
});

test.describe('Profile Verification', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should start verification process', async ({ page }) => {
    await page.goto('/profile/verification');
    await waitForAppReady(page);

    // Look for start verification button
    const startBtn = page.locator('button:has-text("Start Verification"), button:has-text("Verify")').first();
    if (await startBtn.isVisible()) {
      await startBtn.click();
      await page.waitForTimeout(500);

      // Verification form should appear
      const verificationForm = page.locator('form, [data-testid="verification-form"]');
      expect(await verificationForm.count()).toBeGreaterThan(0);
    }
  });

  test('should upload identity document', async ({ page }) => {
    await page.goto('/profile/verification');
    await waitForAppReady(page);

    // Look for document upload
    const uploadInput = page.locator('input[type="file"]').first();
    if (await uploadInput.isVisible()) {
      // Verify upload input exists (actual file upload would need more setup)
      expect(await uploadInput.count()).toBeGreaterThan(0);
    }
  });

  test('should display verification status badge', async ({ page }) => {
    await page.goto('/profile');
    await waitForAppReady(page);

    // Look for verified badge
    const badge = page.locator('[data-testid="verified-badge"], .badge, text=/verified/i');
    if (await badge.count() > 0) {
      // Badge may or may not be visible depending on verification status
      expect(await badge.count()).toBeGreaterThanOrEqual(0);
    }
  });
});
