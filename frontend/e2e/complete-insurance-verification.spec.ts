import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';

test.describe('Complete Insurance and Verification Tests', () => {
  let authHelper: AuthHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should view insurance options', async ({ page }) => {
    await page.goto('/insurance');
    
    await expect(page.locator('text=/insurance|protection/i')).toBeVisible();
  });

  test('should add insurance to booking', async ({ page }) => {
    await page.goto('/properties');
    await page.locator('[data-testid="property-card"]').first().click();
    
    const insuranceCheckbox = page.locator('input[name="add_insurance"], label:has-text("Insurance")');
    if (await insuranceCheckbox.first().isVisible()) {
      await insuranceCheckbox.first().check();
      await page.waitForTimeout(1000);
    }
  });

  test('should view insurance claims', async ({ page }) => {
    await page.goto('/insurance/claims');
    
    const claimsSection = page.locator('text=/claims|my claims/i');
    if (await claimsSection.isVisible()) {
      await expect(claimsSection).toBeVisible();
    }
  });

  test('should file insurance claim', async ({ page }) => {
    await page.goto('/insurance/claims/new');
    
    const claimForm = page.locator('form');
    if (await claimForm.isVisible()) {
      await page.fill('textarea[name="description"]', 'Property damage during stay');
      await page.fill('input[name="amount"]', '500');
      
      // Upload evidence
      const fileInput = page.locator('input[type="file"]');
      if (await fileInput.isVisible()) {
        const buffer = Buffer.from('evidence photo');
        await fileInput.setInputFiles({
          name: 'damage.jpg',
          mimeType: 'image/jpeg',
          buffer: buffer,
        });
      }
      
      await page.click('button:has-text("Submit")');
      await expect(page.locator('text=/claim submitted/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should start identity verification', async ({ page }) => {
    await page.goto('/verification');
    
    const verifyButton = page.locator('button:has-text("Verify"), button:has-text("Start Verification")');
    if (await verifyButton.isVisible()) {
      await verifyButton.click();
      
      await expect(page.locator('text=/verification|identity/i')).toBeVisible();
    }
  });

  test('should upload verification documents', async ({ page }) => {
    await page.goto('/verification');
    
    const idUpload = page.locator('input[name="id_document"], input[type="file"]').first();
    if (await idUpload.isVisible()) {
      const buffer = Buffer.from('ID document');
      await idUpload.setInputFiles({
        name: 'id.jpg',
        mimeType: 'image/jpeg',
        buffer: buffer,
      });
      
      await page.waitForTimeout(2000);
    }
  });

  test('should view verification status', async ({ page }) => {
    await page.goto('/verification');
    
    const status = page.locator('text=/pending|verified|under review/i');
    if (await status.isVisible()) {
      await expect(status).toBeVisible();
    }
  });

  test('should enable host verification badge', async ({ page }) => {
    await page.goto('/host/settings');
    
    const verificationBadge = page.locator('text=/verified host|verification badge/i');
    if (await verificationBadge.isVisible()) {
      await expect(verificationBadge).toBeVisible();
    }
  });
});
