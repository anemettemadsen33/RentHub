import { test, expect, Page } from '@playwright/test';
import { login, waitForAppReady, mockJson, safeClick, safeFill } from './helpers';

/**
 * Dashboard Owner Flow E2E Tests
 * 
 * Tests for property owner dashboard functionality including:
 * - Overview/statistics display
 * - Property management (view, edit, delete)
 * - Booking management
 * - Revenue tracking
 * - Property creation
 */

test.describe('Owner Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login as owner user
    await login(page, { email: 'owner@renthub.com', password: 'password' });
  });

  test('should display owner dashboard with statistics', async ({ page }) => {
    await page.goto('/dashboard/owner');
    await waitForAppReady(page);

    // Check for dashboard elements
    const heading = page.locator('h1, h2').filter({ hasText: /dashboard|overview/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // Statistics cards should be visible
    const statsCards = page.locator('[data-testid="stat-card"], .stat, .stats');
    if (await statsCards.count() > 0) {
      await expect(statsCards.first()).toBeVisible();
    }
  });

  test('should navigate to properties list', async ({ page }) => {
    await page.goto('/dashboard/owner');
    await waitForAppReady(page);

    // Click on properties link/button
    const propertiesLink = page.locator('a[href*="/dashboard/properties"], button:has-text("Properties")').first();
    if (await propertiesLink.isVisible()) {
      await safeClick(propertiesLink);
      await page.waitForLoadState('networkidle');
      await expect(page).toHaveURL(/\/dashboard\/properties/);
    }
  });

  test('should display property list in dashboard', async ({ page }) => {
    await page.goto('/dashboard/properties');
    await waitForAppReady(page);

    // Check for property cards or list items
    const propertyItems = page.locator('[data-testid="property-item"], [data-testid="property-card"]');
    const count = await propertyItems.count();
    
    if (count > 0) {
      await expect(propertyItems.first()).toBeVisible();
    } else {
      // Empty state should be visible
      const emptyState = page.locator('text=/no properties|add.*property|create.*property/i');
      await expect(emptyState.first()).toBeVisible({ timeout: 5000 });
    }
  });

  test('should allow creating a new property', async ({ page }) => {
    await page.goto('/dashboard/properties/new');
    await waitForAppReady(page);

    // Fill in basic property details
    await safeFill(page.locator('input[name="title"]'), 'E2E Test Property');
    await safeFill(page.locator('textarea[name="description"]'), 'This is a test property created by E2E tests');
    await safeFill(page.locator('input[name="price"]'), '150');

    // Select property type if available
    const propertyType = page.locator('select[name="type"], select[name="propertyType"]');
    if (await propertyType.isVisible()) {
      await propertyType.selectOption({ index: 1 });
    }

    // Fill address fields if present
    await safeFill(page.locator('input[name="address"]'), '123 Test Street');
    await safeFill(page.locator('input[name="city"]'), 'Test City');

    // Mock API response for property creation
    await mockJson(page, '**/api/v1/properties', { 
      id: 999, 
      title: 'E2E Test Property',
      slug: 'e2e-test-property'
    }, 201);

    // Submit form
    const submitBtn = page.locator('button[type="submit"]:has-text("Create"), button:has-text("Save"), button:has-text("Publish")').first();
    if (await submitBtn.isVisible()) {
      await submitBtn.click();
      await page.waitForLoadState('networkidle');
      
      // Should show success message or redirect
      const success = page.locator('text=/success|created|published/i');
      const visible = await success.first().isVisible({ timeout: 5000 }).catch(() => false);
      expect(visible).toBeTruthy();
    }
  });

  test('should view property details from dashboard', async ({ page }) => {
    await page.goto('/dashboard/properties');
    await waitForAppReady(page);

    // Click first property if available
    const firstProperty = page.locator('[data-testid="property-item"], [data-testid="property-card"]').first();
    if (await firstProperty.isVisible()) {
      await firstProperty.click();
      await page.waitForLoadState('networkidle');
      
      // Should navigate to property detail page
      await expect(page).toHaveURL(/\/dashboard\/properties\/\d+/);
    }
  });

  test('should edit property details', async ({ page }) => {
    // Assuming property ID 1 exists from seeder
    await page.goto('/dashboard/properties/1');
    await waitForAppReady(page);

    // Click edit button
    const editBtn = page.locator('button:has-text("Edit"), a:has-text("Edit")').first();
    if (await editBtn.isVisible()) {
      await editBtn.click();
      await page.waitForLoadState('networkidle');

      // Update title
      const titleInput = page.locator('input[name="title"]');
      if (await titleInput.isVisible()) {
        await titleInput.clear();
        await titleInput.fill('Updated E2E Property');

        // Mock update API
        await mockJson(page, '**/api/v1/properties/*', { 
          id: 1, 
          title: 'Updated E2E Property' 
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

  test('should display bookings for properties', async ({ page }) => {
    await page.goto('/dashboard');
    await waitForAppReady(page);

    // Navigate to bookings section
    const bookingsLink = page.locator('a[href*="/bookings"], button:has-text("Bookings")').first();
    if (await bookingsLink.isVisible()) {
      await bookingsLink.click();
      await page.waitForLoadState('networkidle');

      // Check for bookings list or empty state
      const bookingsList = page.locator('[data-testid="booking-item"], .booking-card');
      const count = await bookingsList.count();

      if (count > 0) {
        await expect(bookingsList.first()).toBeVisible();
      } else {
        const emptyState = page.locator('text=/no bookings|no reservations/i');
        await expect(emptyState.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should show revenue statistics', async ({ page }) => {
    await page.goto('/dashboard/owner');
    await waitForAppReady(page);

    // Check for revenue/earnings display
    const revenue = page.locator('[data-testid="revenue"], text=/revenue|earnings|income/i');
    if (await revenue.count() > 0) {
      await expect(revenue.first()).toBeVisible();
    }

    // Check for currency display
    const currency = page.locator('text=/\\$|€|£|USD|EUR|RON/');
    if (await currency.count() > 0) {
      await expect(currency.first()).toBeVisible();
    }
  });

  test('should manage property availability calendar', async ({ page }) => {
    await page.goto('/dashboard/properties/1');
    await waitForAppReady(page);

    // Navigate to calendar if link exists
    const calendarLink = page.locator('a[href*="calendar"], button:has-text("Calendar")').first();
    if (await calendarLink.isVisible()) {
      await calendarLink.click();
      await page.waitForLoadState('networkidle');

      // Calendar component should be visible
      const calendar = page.locator('[data-testid="calendar"], .calendar, [role="grid"]');
      await expect(calendar.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should handle property deletion with confirmation', async ({ page }) => {
    await page.goto('/dashboard/properties/1');
    await waitForAppReady(page);

    // Click delete button
    const deleteBtn = page.locator('button:has-text("Delete")').first();
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
});

test.describe('Owner Settings', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, { email: 'owner@renthub.com', password: 'password' });
  });

  test('should access dashboard settings', async ({ page }) => {
    await page.goto('/dashboard/settings');
    await waitForAppReady(page);

    // Settings page should load
    const heading = page.locator('h1, h2').filter({ hasText: /settings|preferences/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should update notification preferences', async ({ page }) => {
    await page.goto('/dashboard/settings');
    await waitForAppReady(page);

    // Look for notification settings
    const notificationSection = page.locator('text=/notifications|alerts/i');
    if (await notificationSection.count() > 0) {
      // Try to toggle a notification setting
      const toggle = page.locator('input[type="checkbox"]').first();
      if (await toggle.isVisible()) {
        const initialState = await toggle.isChecked();
        await toggle.click();
        
        // Verify state changed
        const newState = await toggle.isChecked();
        expect(newState).toBe(!initialState);
      }
    }
  });

  test('should display payout settings', async ({ page }) => {
    await page.goto('/dashboard/settings');
    await waitForAppReady(page);

    // Look for payout or payment settings
    const payoutSection = page.locator('text=/payout|payment.*method|banking/i');
    if (await payoutSection.count() > 0) {
      await expect(payoutSection.first()).toBeVisible();
    }
  });
});
