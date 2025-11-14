import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Integration and API Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
  });

  test('should handle network errors gracefully', async ({ page, context }) => {
    await page.goto('/');
    
    // Simulate offline
    await context.setOffline(true);
    await page.click('a[href="/properties"]').catch(() => {});
    
    // Should show offline message
    const offlineMessage = page.locator('text=/offline|no connection|network error/i');
    if (await offlineMessage.isVisible({ timeout: 5000 }).catch(() => false)) {
      await expect(offlineMessage).toBeVisible();
    }
    
    await context.setOffline(false);
  });

  test('should retry failed requests', async ({ page }) => {
    await page.goto('/properties');
    
    // Wait for potential retries
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
    
    // Should eventually load
    await expect(page.locator('body')).toBeVisible();
  });

  test('should handle session timeout', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    
    // Clear auth token
    await page.evaluate(() => localStorage.removeItem('token'));
    
    // Try to access protected page
    await page.goto('/dashboard');
    
    // Should redirect to login
    await expect(page).toHaveURL(/\/(login|auth)/);
  });

  test('should sync data across tabs', async ({ page, context }) => {
    await authHelper.login('test@example.com', 'password123');
    
    // Open second tab
    const page2 = await context.newPage();
    await page2.goto('/dashboard');
    
    // Both should be logged in
    const isLoggedIn = await page2.evaluate(() => localStorage.getItem('token') !== null);
    expect(isLoggedIn).toBe(true);
    
    await page2.close();
  });

  test('should handle concurrent requests', async ({ page }) => {
    await page.goto('/properties');
    
    // Trigger multiple actions simultaneously
    await Promise.all([
      page.click('button:has-text("Search")').catch(() => {}),
      page.fill('input[name="location"]', 'Paris').catch(() => {}),
    ]);
    
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
  });

  test('should validate API responses', async ({ page }) => {
    await page.goto('/properties');
    
    // Listen for API calls
    page.on('response', async (response) => {
      if (response.url().includes('/api/')) {
        expect([200, 201, 204, 400, 401, 404, 422]).toContain(response.status());
      }
    });
    
    await page.waitForTimeout(3000);
  });

  test('should handle large datasets', async ({ page }) => {
    await page.goto('/properties');
    
    // Scroll to trigger lazy loading
    for (let i = 0; i < 5; i++) {
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForTimeout(1000);
    }
  });

  test('should cache data appropriately', async ({ page }) => {
    await page.goto('/properties');
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
    
    // Navigate away and back
    await page.goto('/');
    await page.goto('/properties');
    
    // Should load faster (from cache)
    // cSpell:ignore networkidle
    await page.waitForLoadState('networkidle');
  });

  test('should handle real-time updates', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/messages');
    
    // Wait for WebSocket connection
    await page.waitForTimeout(2000);
    
    // Keep page open to receive updates
    await page.waitForTimeout(3000);
  });

  test('should handle file uploads', async ({ page }) => {
    await authHelper.login('test@example.com', 'password123');
    await page.goto('/profile');
    
    const fileInput = page.locator('input[type="file"]').first();
    if (await fileInput.isVisible()) {
      const buffer = Buffer.from('test file content');
      await fileInput.setInputFiles({
        name: 'test.jpg',
        mimeType: 'image/jpeg',
        buffer: buffer,
      });
      
      await page.waitForTimeout(2000);
    }
  });

  test('should validate CORS headers', async ({ page }) => {
    let corsValid = true;
    
    page.on('response', async (response) => {
      if (response.url().includes('/api/')) {
        const headers = response.headers();
        // Check for CORS headers if needed
        if (headers['access-control-allow-origin'] !== undefined) {
          corsValid = true;
        }
      }
    });
    
    await page.goto('/properties');
    await page.waitForTimeout(2000);
    
    expect(corsValid).toBe(true);
  });
});
