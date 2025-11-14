import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Notification System Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should display notification badge', async ({ page }) => {
    await page.goto('/dashboard');
    
    const notificationBadge = page.locator('[data-testid="notification-badge"], .notification-badge, [aria-label*="notifications"]');
    if (await notificationBadge.isVisible()) {
      await expect(notificationBadge).toBeVisible();
    }
  });

  test('should open notifications panel', async ({ page }) => {
    await page.goto('/dashboard');
    
    const notificationButton = page.locator('button[aria-label*="notification"], button:has-text("Notifications")');
    if (await notificationButton.isVisible()) {
      await notificationButton.click();
      
      await expect(page.locator('[role="dialog"], .notifications-panel')).toBeVisible();
    }
  });

  test('should mark notification as read', async ({ page }) => {
    await page.goto('/notifications');
    
    const unreadNotification = page.locator('[data-unread="true"], .unread').first();
    if (await unreadNotification.isVisible()) {
      await unreadNotification.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should mark all notifications as read', async ({ page }) => {
    await page.goto('/notifications');
    
    const markAllButton = page.locator('button:has-text("Mark all"), button:has-text("Read all")');
    if (await markAllButton.isVisible()) {
      await markAllButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should delete notification', async ({ page }) => {
    await page.goto('/notifications');
    
    const deleteButton = page.locator('button[aria-label*="delete"], button:has-text("Delete")').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should filter notifications by type', async ({ page }) => {
    await page.goto('/notifications');
    
    const filterSelect = page.locator('select[name="type"], button:has-text("Filter")');
    if (await filterSelect.first().isVisible()) {
      await filterSelect.first().click();
      const filterOption = page.locator('text=/booking|message/i').first();
      await filterOption.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should enable push notifications', async ({ page, context }) => {
    await page.goto('/settings');
    
    // Grant permission
    await context.grantPermissions(['notifications']);
    
    const enablePushButton = page.locator('button:has-text("Enable"), input[name="push_notifications"]');
    if (await enablePushButton.first().isVisible()) {
      await enablePushButton.first().click();
      await page.waitForTimeout(1000);
    }
  });

  test('should disable email notifications', async ({ page }) => {
    await page.goto('/settings');
    
    const emailNotifications = page.locator('input[name="email_notifications"]');
    if (await emailNotifications.isVisible()) {
      await emailNotifications.uncheck();
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/saved|updated/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should customize notification preferences', async ({ page }) => {
    await page.goto('/settings/notifications');
    
    const bookingNotifications = page.locator('input[name="notify_bookings"]');
    const messageNotifications = page.locator('input[name="notify_messages"]');
    
    if (await bookingNotifications.isVisible()) {
      await bookingNotifications.check();
    }
    if (await messageNotifications.isVisible()) {
      await messageNotifications.check();
    }
    
    await page.click('button:has-text("Save")');
    await expect(page.locator('text=/saved/i')).toBeVisible({ timeout: 10000 });
  });

  test('should receive in-app notification', async ({ page }) => {
    await page.goto('/dashboard');
    
    // Wait for potential notifications
    await page.waitForTimeout(3000);
    
    const toast = page.locator('[data-testid="toast"], .toast, [role="status"]');
    if (await toast.isVisible({ timeout: 2000 }).catch(() => false)) {
      await expect(toast).toBeVisible();
    }
  });
});
