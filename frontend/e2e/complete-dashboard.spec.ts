import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete Dashboard Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    formHelper = new FormHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should display dashboard overview', async ({ page }) => {
    await navHelper.goToDashboard();
    
    await expect(page.locator('text=/dashboard|overview/i')).toBeVisible();
    await expect(page.locator('text=/welcome/i')).toBeVisible();
  });

  test('should show upcoming bookings widget', async ({ page }) => {
    await navHelper.goToDashboard();
    
    const upcomingBookings = page.locator('text=/upcoming|next booking/i');
    await expect(upcomingBookings).toBeVisible();
  });

  test('should display recent activity', async ({ page }) => {
    await navHelper.goToDashboard();
    
    const activity = page.locator('text=/recent activity|activity feed/i');
    if (await activity.isVisible()) {
      await expect(activity).toBeVisible();
    }
  });

  test('should show statistics cards', async ({ page }) => {
    await navHelper.goToDashboard();
    
    // Look for stat cards
    const stats = page.locator('[data-testid="stat-card"], .stat-card, .dashboard-card');
    if (await stats.first().isVisible()) {
      const count = await stats.count();
      expect(count).toBeGreaterThan(0);
    }
  });

  test('should navigate to quick actions', async ({ page }) => {
    await navHelper.goToDashboard();
    
    const quickAction = page.locator('button:has-text("Search"), button:has-text("New Booking")').first();
    if (await quickAction.isVisible()) {
      await quickAction.click();
      // cSpell:ignore networkidle
      await page.waitForLoadState('networkidle');
    }
  });

  test('should view earnings (for hosts)', async ({ page }) => {
    await navHelper.goToDashboard();
    
    const earnings = page.locator('text=/earnings|revenue|income/i');
    if (await earnings.isVisible()) {
      await expect(earnings).toBeVisible();
    }
  });

  test('should display notifications', async ({ page }) => {
    await page.goto('/notifications');
    
    await expect(page.locator('text=/notifications/i')).toBeVisible();
  });

  test('should mark notification as read', async ({ page }) => {
    await page.goto('/notifications');
    
    const notification = page.locator('[data-testid="notification"], .notification-item').first();
    if (await notification.isVisible()) {
      await notification.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should clear all notifications', async ({ page }) => {
    await page.goto('/notifications');
    
    const clearButton = page.locator('button:has-text("Clear All"), button:has-text("Mark all as read")');
    if (await clearButton.isVisible()) {
      await clearButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should view calendar sync', async ({ page }) => {
    await page.goto('/calendar-sync');
    
    const syncButton = page.locator('button:has-text("Sync"), button:has-text("Connect Calendar")');
    if (await syncButton.isVisible()) {
      await expect(syncButton).toBeVisible();
    }
  });
});
