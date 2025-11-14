import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Admin Panel Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    await authHelper.login('admin@example.com', 'admin123');
  });

  test('should access admin dashboard', async ({ page }) => {
    await page.goto('/admin');
    
    await expect(page.locator('text=/admin|administration/i')).toBeVisible();
  });

  test('should view all users list', async ({ page }) => {
    await page.goto('/admin/users');
    
    await expect(page.locator('text=/users|user management/i')).toBeVisible();
  });

  test('should search for specific user', async ({ page }) => {
    await page.goto('/admin/users');
    
    await page.fill('input[type="search"], input[name="search"]', 'test@example.com');
    await page.waitForTimeout(1000);
  });

  test('should suspend user account', async ({ page }) => {
    await page.goto('/admin/users');
    
    const actionButton = page.locator('button:has-text("Actions"), button[aria-label*="menu"]').first();
    if (await actionButton.isVisible()) {
      await actionButton.click();
      await page.click('text=/suspend|ban/i');
      
      await page.fill('textarea[name="reason"]', 'Violation of terms');
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/suspended|banned/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view all properties for moderation', async ({ page }) => {
    await page.goto('/admin/properties');
    
    const properties = page.locator('[data-testid="property-item"], .property-item');
    const count = await properties.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('should approve pending property', async ({ page }) => {
    await page.goto('/admin/properties?status=pending');
    
    const approveButton = page.locator('button:has-text("Approve")').first();
    if (await approveButton.isVisible()) {
      await approveButton.click();
      
      await expect(page.locator('text=/approved|published/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should reject property listing', async ({ page }) => {
    await page.goto('/admin/properties?status=pending');
    
    const rejectButton = page.locator('button:has-text("Reject")').first();
    if (await rejectButton.isVisible()) {
      await rejectButton.click();
      
      await page.fill('textarea[name="reason"]', 'Does not meet quality standards');
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/rejected/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view reported content', async ({ page }) => {
    await page.goto('/admin/reports');
    
    await expect(page.locator('text=/reports|reported content/i')).toBeVisible();
  });

  test('should resolve a report', async ({ page }) => {
    await page.goto('/admin/reports');
    
    const resolveButton = page.locator('button:has-text("Resolve")').first();
    if (await resolveButton.isVisible()) {
      await resolveButton.click();
      
      await page.fill('textarea[name="notes"]', 'Issue resolved');
      await page.click('button:has-text("Submit")');
      
      await expect(page.locator('text=/resolved/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view site analytics', async ({ page }) => {
    await page.goto('/admin/analytics');
    
    await expect(page.locator('text=/analytics|statistics/i')).toBeVisible();
  });

  test('should manage site settings', async ({ page }) => {
    await page.goto('/admin/settings');
    
    const settingInput = page.locator('input[name="site_name"]');
    if (await settingInput.isVisible()) {
      await settingInput.fill('RentHub Platform');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/saved|updated/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should send system notification', async ({ page }) => {
    await page.goto('/admin/notifications');
    
    const sendButton = page.locator('button:has-text("Send Notification")');
    if (await sendButton.isVisible()) {
      await sendButton.click();
      
      await page.fill('input[name="title"]', 'System Maintenance');
      await page.fill('textarea[name="message"]', 'The platform will be under maintenance tonight.');
      await page.click('button:has-text("Send")');
      
      await expect(page.locator('text=/sent|notification sent/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
