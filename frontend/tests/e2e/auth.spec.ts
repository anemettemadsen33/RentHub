import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
  const testEmail = `test${Date.now()}@example.com`;
  const testPassword = 'Test123!@#';
  const testName = 'Test User';

  test('should complete full registration flow', async ({ page }) => {
    // Navigate directly to registration page
    await page.goto('/auth/register');
    
    // Fill registration form
    await page.fill('input[name="name"]', testName);
    await page.fill('input[name="email"]', testEmail);
    await page.fill('input[name="password"]', testPassword);
    await page.fill('input[name="password_confirmation"]', testPassword);
    
    // Accept terms
    await page.check('input[name="terms"]');
    
    // Submit form
    await page.click('button[type="submit"]:has-text("Create Account")');
    
    // Should redirect to email verification page or dashboard
    await expect(page).toHaveURL(/\/(verify-email|dashboard)/, { timeout: 10000 });
    
    // Check for success message or profile presence
    const successOrProfile = page.locator('text=/Account created|Welcome|Profile/i');
    await expect(successOrProfile.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show validation errors for invalid registration', async ({ page }) => {
    await page.goto('/auth/register');
    
    // Submit without filling form
    await page.click('button[type="submit"]:has-text("Create Account")');
    
    // Should show validation errors (wait a bit for client-side validation)
    await page.waitForTimeout(500);
    const errorVisible = await page.locator('text=/required|must|invalid/i').first().isVisible().catch(() => false);
    expect(errorVisible).toBeTruthy();
  });

  test('should not allow registration with existing email', async ({ page }) => {
    await page.goto('/auth/register');
    
    // Try to register with admin@renthub.com (should exist from seeding)
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', 'admin@renthub.com');
    await page.fill('input[name="password"]', testPassword);
    await page.fill('input[name="password_confirmation"]', testPassword);
    await page.check('input[name="terms"]');
    
    await page.click('button[type="submit"]:has-text("Create Account")');
    
    // Should show error
    await expect(page.locator('text=/email.*already.*taken/i')).toBeVisible({ timeout: 5000 });
  });

  test('should login with valid credentials', async ({ page }) => {
    // Navigate directly to login page
    await page.goto('/auth/login');
    
    // Fill login form with seeded user
    await page.fill('input[name="email"]', 'guest@renthub.com');
    await page.fill('input[name="password"]', 'password');
    
    // Submit
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    // Should redirect to dashboard
    await expect(page).toHaveURL(/\/dashboard/, { timeout: 10000 });
    
    // Should show user info or welcome
    const userInfo = page.locator('text=/John|Guest|Welcome/i');
    await expect(userInfo.first()).toBeVisible({ timeout: 5000 });
  });

  test('should show error for invalid login credentials', async ({ page }) => {
    await page.goto('/auth/login');
    
    await page.fill('input[name="email"]', 'nonexistent@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    // Should show error message
    await expect(page.locator('text=/invalid.*credentials/i')).toBeVisible({ timeout: 5000 });
  });

  test('should handle password reset flow', async ({ page }) => {
    await page.goto('/auth/login');
    
    // Click forgot password
    await page.click('text=Forgot password?');
    
    // Should navigate to password reset page
    await expect(page).toHaveURL(/\/forgot-password/, { timeout: 5000 });
    
    // Fill email
    await page.fill('input[name="email"]', 'guest@renthub.com');
    
    // Submit
    await page.click('button[type="submit"]:has-text("Send Reset Link")');
    
    // Should show success message
    await expect(page.locator('text=/reset.*link.*sent/i')).toBeVisible({ timeout: 5000 });
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await page.goto('/auth/login');
    await page.fill('input[name="email"]', 'guest@renthub.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    // Wait for dashboard
    await page.waitForURL(/\/dashboard/);
    
    // Open user menu
    await page.click('[data-testid="user-menu"]', { timeout: 5000 });
    
    // Click logout
    await page.click('text=Logout');
    
    // Should redirect to home
    await expect(page).toHaveURL('/', { timeout: 5000 });
    
    // Login button should be visible again
    await expect(page.locator('text=Login')).toBeVisible();
  });

  test('should handle 2FA flow if enabled', async ({ page }) => {
    // This test assumes 2FA is enabled for a test user
    // Skip if 2FA is not configured in test environment
    test.skip(true, '2FA not enabled in test environment');
    
    await page.click('text=Login');
    await page.fill('input[name="email"]', '2fa-user@renthub.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    // Should show 2FA code input
    await expect(page.locator('text=Enter verification code')).toBeVisible({ timeout: 5000 });
    
    // In test environment, code might be displayed for development
    const codeElement = page.locator('[data-testid="2fa-code"]');
    if (await codeElement.isVisible()) {
      const code = await codeElement.textContent();
      if (code) {
        // Enter the code
        await page.fill('input[name="code"]', code.trim());
        await page.click('button[type="submit"]:has-text("Verify")');
        
        // Should redirect to dashboard
        await expect(page).toHaveURL(/\/dashboard/);
      }
    }
  });

  test('should persist authentication across page reloads', async ({ page }) => {
    // Login
    await page.click('text=Login');
    await page.fill('input[name="email"]', 'guest@renthub.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    await page.waitForURL(/\/dashboard/);
    
    // Reload page
    await page.reload();
    
    // Should still be logged in
    await expect(page).toHaveURL(/\/dashboard/);
    await expect(page.locator('text=John Guest')).toBeVisible();
  });

  test('should redirect to login when accessing protected route', async ({ page }) => {
    // Try to access protected route without login
    await page.goto('/dashboard');
    
    // Should redirect to login
    await expect(page).toHaveURL(/\/login/);
    
    // Should show message
    await expect(page.locator('text=/please.*login/i')).toBeVisible({ timeout: 5000 });
  });

  test('should remember me functionality', async ({ page }) => {
    await page.click('text=Login');
    
    // Check remember me
    await page.check('input[name="remember"]');
    
    await page.fill('input[name="email"]', 'guest@renthub.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]:has-text("Sign In")');
    
    await page.waitForURL(/\/dashboard/);
    
    // Check that auth token is set with longer expiration
    const cookies = await page.context().cookies();
    const authCookie = cookies.find(c => c.name.includes('auth') || c.name.includes('token'));
    
    if (authCookie) {
      // Should have extended expiration (more than 1 day)
      const expirationDate = new Date(authCookie.expires * 1000);
      const oneDayFromNow = new Date(Date.now() + 24 * 60 * 60 * 1000);
      expect(expirationDate > oneDayFromNow).toBeTruthy();
    }
  });
});

test.describe('Social Authentication', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should show social login buttons', async ({ page }) => {
    await page.click('text=Login');
    
    // Should show social login options
    await expect(page.locator('text=Continue with Google')).toBeVisible();
    await expect(page.locator('text=Continue with Facebook')).toBeVisible();
    await expect(page.locator('text=Continue with GitHub')).toBeVisible();
  });

  test('social login redirect (mock)', async ({ page }) => {
    // Note: Actual social login testing requires OAuth mocks
    // This is a basic redirect test
    
    await page.click('text=Login');
    
    // Click Google login
    const [popup] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('text=Continue with Google')
    ]);
    
    // In real test, would need to mock OAuth flow
    // For now, just verify redirect happened
    expect(popup.url()).toContain('google');
    
    await popup.close();
  });
});

test.describe('Email Verification', () => {
  test('should show email verification notice', async ({ page }) => {
    // This would need a newly registered user
    // For now, test the UI exists
    await page.goto('/verify-email');
    
    await expect(page.locator('text=/verify.*email/i')).toBeVisible();
    await expect(page.locator('text=/check.*inbox/i')).toBeVisible();
  });

  test('should allow resending verification email', async ({ page }) => {
    await page.goto('/verify-email');
    
    const resendButton = page.locator('button:has-text("Resend")');
    if (await resendButton.isVisible()) {
      await resendButton.click();
      
      // Should show success message
      await expect(page.locator('text=/verification.*sent/i')).toBeVisible({ timeout: 5000 });
    }
  });
});
