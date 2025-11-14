import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete Authentication Tests', () => {
  let authHelper: AuthHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    formHelper = new FormHelper(page);
    await page.goto('/');
  });

  test('should register a new user with all fields', async ({ page }) => {
    const email = `test${Date.now()}@example.com`;
    await authHelper.register(email, 'Password123!', 'John Doe');
    
    // Verify success message or redirect
    await expect(page.locator('text=/registration successful|welcome|verify email/i')).toBeVisible({ timeout: 10000 });
  });

  test('should show validation errors for invalid registration', async ({ page }) => {
    await page.goto('/auth/register');
    
    // Submit empty form
    await formHelper.submitForm();
    
    // Check for validation errors
    const errorCount = await page.locator('[role="alert"], .error, [data-error]').count();
    expect(errorCount).toBeGreaterThan(0);
  });

  test('should validate email format', async ({ page }) => {
    await page.goto('/auth/register');
    await page.fill('input[name="email"]', 'invalid-email');
    await page.fill('input[name="password"]', 'Password123!');
    await formHelper.submitForm();
    
    await expect(page.locator('text=/invalid email|email format/i')).toBeVisible();
  });

  test('should validate password strength', async ({ page }) => {
    await page.goto('/auth/register');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', '123');
    await formHelper.submitForm();
    
    await expect(page.locator('text=/password.*short|password.*weak|minimum.*characters/i')).toBeVisible();
  });

  test('should login with valid credentials', async ({ page }) => {
    await page.goto('/auth/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await formHelper.submitForm();
    
    // Wait for redirect
    await page.waitForURL(/\/(dashboard|properties|profile)/, { timeout: 10000 });
  });

  test('should show error for invalid login credentials', async ({ page }) => {
    await page.goto('/auth/login');
    await page.fill('input[name="email"]', 'wrong@example.com');
    // cSpell:ignore wrongpassword
    await page.fill('input[name="password"]', 'wrongpassword');
    await formHelper.submitForm();
    
    await expect(page.locator('text=/invalid credentials|incorrect password|login failed/i')).toBeVisible();
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await authHelper.login('test@example.com', 'password123');
    
    // Logout
    await authHelper.logout();
    
    // Verify redirect to home and token removed
    await expect(page).toHaveURL(/\/(|login|auth)/);
    const isLoggedIn = await authHelper.isLoggedIn();
    expect(isLoggedIn).toBe(false);
  });

  test('should handle forgot password flow', async ({ page }) => {
    await page.goto('/auth/forgot-password');
    await page.fill('input[name="email"]', 'test@example.com');
    await formHelper.submitForm();
    
    await expect(page.locator('text=/reset link sent|check your email/i')).toBeVisible({ timeout: 10000 });
  });

  test('should toggle password visibility', async ({ page }) => {
    await page.goto('/auth/login');
    const passwordInput = page.locator('input[name="password"]');
    const toggleButton = page.locator('button[aria-label*="password"], button:has-text("Show")');
    
    // Initially hidden
    await expect(passwordInput).toHaveAttribute('type', 'password');
    
    // Click toggle
    if (await toggleButton.isVisible()) {
      await toggleButton.click();
      await expect(passwordInput).toHaveAttribute('type', 'text');
    }
  });

  test('should persist login across page refreshes', async ({ page, context }) => {
    await authHelper.login('test@example.com', 'password123');
    
    // Refresh page
    await page.reload();
    
    // User should still be logged in
    const isLoggedIn = await authHelper.isLoggedIn();
    expect(isLoggedIn).toBe(true);
  });
});
