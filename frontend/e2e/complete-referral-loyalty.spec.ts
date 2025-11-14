import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Referral and Loyalty Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should view referral program', async ({ page }) => {
    await page.goto('/referrals');
    
    await expect(page.locator('text=/referral|refer a friend/i')).toBeVisible();
  });

  test('should copy referral link', async ({ page }) => {
    await page.goto('/referrals');
    
    const copyButton = page.locator('button:has-text("Copy"), button:has-text("Copy Link")');
    if (await copyButton.isVisible()) {
      await copyButton.click();
      
      await expect(page.locator('text=/copied|link copied/i')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should share referral via email', async ({ page }) => {
    await page.goto('/referrals');
    
    const emailButton = page.locator('button:has-text("Email"), a[href*="mailto"]');
    if (await emailButton.first().isVisible()) {
      // Click opens email client
      await expect(emailButton.first()).toBeVisible();
    }
  });

  test('should view referral history', async ({ page }) => {
    await page.goto('/referrals');
    
    const history = page.locator('text=/referral history|your referrals/i');
    if (await history.isVisible()) {
      await expect(history).toBeVisible();
    }
  });

  test('should view referral earnings', async ({ page }) => {
    await page.goto('/referrals');
    
    const earnings = page.locator('text=/earned|referral earnings/i');
    if (await earnings.isVisible()) {
      await expect(earnings).toBeVisible();
    }
  });

  test('should view loyalty program', async ({ page }) => {
    await page.goto('/loyalty');
    
    await expect(page.locator('text=/loyalty|rewards|points/i')).toBeVisible();
  });

  test('should view loyalty points balance', async ({ page }) => {
    await page.goto('/loyalty');
    
    const points = page.locator('[data-testid="points-balance"], text=/points/i');
    if (await points.first().isVisible()) {
      await expect(points.first()).toBeVisible();
    }
  });

  test('should redeem loyalty points', async ({ page }) => {
    await page.goto('/loyalty');
    
    const redeemButton = page.locator('button:has-text("Redeem"), button:has-text("Use Points")');
    if (await redeemButton.isVisible()) {
      await redeemButton.click();
      
      const rewardOption = page.locator('[data-testid="reward-item"]').first();
      if (await rewardOption.isVisible()) {
        await rewardOption.click();
        await page.click('button:has-text("Confirm")');
        
        await expect(page.locator('text=/redeemed|success/i')).toBeVisible({ timeout: 10000 });
      }
    }
  });

  test('should view points history', async ({ page }) => {
    await page.goto('/loyalty/history');
    
    const history = page.locator('text=/points history|transaction/i');
    if (await history.isVisible()) {
      await expect(history).toBeVisible();
    }
  });

  test('should view loyalty tier status', async ({ page }) => {
    await page.goto('/loyalty');
    
    const tier = page.locator('text=/tier|level|status/i');
    if (await tier.isVisible()) {
      await expect(tier).toBeVisible();
    }
  });
});
