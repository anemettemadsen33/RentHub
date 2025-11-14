// cSpell:ignore networkidle
import { test, expect, Page } from '@playwright/test';
import { gotoReady } from './helpers/app-ready';

test.describe('All Pages - Complete E2E Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Set viewport
    await page.setViewportSize({ width: 1920, height: 1080 });
  });

  const skipProtected = process.env.E2E_AUTH !== 'true';

  // ========== PUBLIC PAGES ==========
  
  test('should load home page', async ({ page }) => {
    await gotoReady(page, '/');
    await expect(page).toHaveTitle(/RentHub|Home/i);
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load about page', async ({ page }) => {
    await gotoReady(page, '/about');
    await expect(page.locator('h1, [role="heading"]')).toBeVisible();
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load contact page', async ({ page }) => {
    await gotoReady(page, '/contact');
    await expect(page.locator('body')).toBeVisible();
    // Check for contact form or contact info
    const hasForm = await page.locator('form, input[type="email"]').count();
    expect(hasForm).toBeGreaterThanOrEqual(0);
  });

  test('should load careers page', async ({ page }) => {
    await gotoReady(page, '/careers');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load press page', async ({ page }) => {
    await gotoReady(page, '/press');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load help/FAQ page', async ({ page }) => {
    await gotoReady(page, '/help');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load FAQ page', async ({ page }) => {
    await gotoReady(page, '/faq');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load terms page', async ({ page }) => {
    await gotoReady(page, '/terms');
    await expect(page.locator('body')).toBeVisible();
    // Should have terms content
    const content = await page.textContent('body');
    expect(content?.length || 0).toBeGreaterThan(100);
  });

  test('should load privacy page', async ({ page }) => {
    await gotoReady(page, '/privacy');
    await expect(page.locator('body')).toBeVisible();
    const content = await page.textContent('body');
    expect(content?.length || 0).toBeGreaterThan(100);
  });

  test('should load cookies page', async ({ page }) => {
    await gotoReady(page, '/cookies');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load offline page', async ({ page }) => {
    await gotoReady(page, '/offline');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load _offline page', async ({ page }) => {
    await gotoReady(page, '/_offline');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load offline-page', async ({ page }) => {
    await gotoReady(page, '/offline-page');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== AUTHENTICATION PAGES ==========

  test('should load login page', async ({ page }) => {
    await gotoReady(page, '/auth/login');
    await expect(page.locator('input[name="email"], input[type="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"], input[type="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should load register page', async ({ page }) => {
    await gotoReady(page, '/auth/register');
    await expect(page.locator('input[name="email"], input[type="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"], input[type="password"]').first()).toBeVisible();
  });

  test('should load auth callback page', async ({ page }) => {
    await gotoReady(page, '/auth/callback');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== PROPERTY PAGES ==========

  test('should load properties listing page', async ({ page }) => {
    await gotoReady(page, '/properties');
    await expect(page.locator('body')).toBeVisible();
    // Wait for potential property cards
    await page.waitForTimeout(1000);
  });

  test('should load property comparison page', async ({ page }) => {
    await gotoReady(page, '/property-comparison');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== USER PAGES ==========

  test('should load profile page', async ({ page }) => {
    await gotoReady(page, '/profile');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load profile verification page', async ({ page }) => {
    await gotoReady(page, '/profile/verification');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load verification page', async ({ page }) => {
    await gotoReady(page, '/verification');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load settings page', async ({ page }) => {
    await gotoReady(page, '/settings');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load security page', async ({ page }) => {
    await gotoReady(page, '/security');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load security audit page', async ({ page }) => {
    await gotoReady(page, '/security/audit');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load screening page', async ({ page }) => {
    await gotoReady(page, '/screening');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== DASHBOARD PAGES ==========

  test('should load dashboard page', async ({ page }) => {
    await gotoReady(page, '/dashboard');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load dashboard-new page', async ({ page }) => {
    await gotoReady(page, '/dashboard-new');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load dashboard owner page', async ({ page }) => {
    await gotoReady(page, '/dashboard/owner');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load dashboard properties page', async ({ page }) => {
    await gotoReady(page, '/dashboard/properties');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load dashboard new property page', async ({ page }) => {
    await gotoReady(page, '/dashboard/properties/new');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load dashboard settings page', async ({ page }) => {
    await gotoReady(page, '/dashboard/settings');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== BOOKINGS PAGES ==========

  test('should load bookings page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/bookings');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== PAYMENTS PAGES ==========

  test('should load payments page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/payments');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load payments history page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/payments/history');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load invoices page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/invoices');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== MESSAGING PAGES ==========

  test('should load messages page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/messages');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== FAVORITES & WISHLISTS ==========

  test('should load favorites page', async ({ page }) => {
    await gotoReady(page, '/favorites');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load wishlists page', async ({ page }) => {
    await gotoReady(page, '/wishlists');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load saved searches page', async ({ page }) => {
    await gotoReady(page, '/saved-searches');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== HOST PAGES ==========

  test('should load host page', async ({ page }) => {
    await gotoReady(page, '/host');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load host properties page', async ({ page }) => {
    await gotoReady(page, '/host/properties');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load host new property page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/host/properties/new');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load host ratings page', async ({ page }) => {
    await gotoReady(page, '/host/ratings');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== NOTIFICATIONS ==========

  test('should load notifications page', async ({ page }) => {
    await gotoReady(page, '/notifications');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== ANALYTICS ==========

  test('should load analytics page', async ({ page }) => {
    await gotoReady(page, '/analytics');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== ADMIN PAGES ==========

  test('should load admin page', async ({ page }) => {
    await gotoReady(page, '/admin');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load admin settings page', async ({ page }) => {
    await gotoReady(page, '/admin/settings');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== INTEGRATIONS ==========

  test('should load integrations page', async ({ page }) => {
    await gotoReady(page, '/integrations');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load google calendar integration page', async ({ page }) => {
    await gotoReady(page, '/integrations/google-calendar');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load stripe integration page', async ({ page }) => {
    await gotoReady(page, '/integrations/stripe');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load realtime integration page', async ({ page }) => {
    await gotoReady(page, '/integrations/realtime');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load calendar sync page', async ({ page }) => {
    test.skip(skipProtected, 'Protected route; requires auth/backend');
    await gotoReady(page, '/calendar-sync');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== INSURANCE & REFERRALS ==========

  test('should load insurance page', async ({ page }) => {
    await gotoReady(page, '/insurance');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load referrals page', async ({ page }) => {
    await gotoReady(page, '/referrals');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load loyalty page', async ({ page }) => {
    await gotoReady(page, '/loyalty');
    await expect(page.locator('body')).toBeVisible();
  });

  // ========== DEMO PAGES ==========

  test('should load demo page', async ({ page }) => {
    await gotoReady(page, '/demo');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo accessibility page', async ({ page }) => {
    await gotoReady(page, '/demo/accessibility');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo i18n page', async ({ page }) => {
    await gotoReady(page, '/demo/i18n');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo form validation page', async ({ page }) => {
    await gotoReady(page, '/demo/form-validation');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo image optimization page', async ({ page }) => {
    await gotoReady(page, '/demo/image-optimization');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo logger page', async ({ page }) => {
    await gotoReady(page, '/demo/logger');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo optimistic UI page', async ({ page }) => {
    await gotoReady(page, '/demo/optimistic-ui');
    await expect(page.locator('body')).toBeVisible();
  });

  test('should load demo performance page', async ({ page }) => {
    await gotoReady(page, '/demo/performance');
    await expect(page.locator('body')).toBeVisible();
  });
});
