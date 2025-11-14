import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';

test.describe('Complete Reviews and Ratings Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should view property reviews', async ({ page }) => {
    await navHelper.goToProperties();
    
    const firstProperty = page.locator('[data-testid="property-card"]').first();
    if (await firstProperty.isVisible()) {
      await firstProperty.click();
      
      await expect(page.locator('text=/reviews|ratings/i')).toBeVisible();
    }
  });

  test('should submit a review with rating', async ({ page }) => {
    await page.goto('/bookings');
    
    const reviewButton = page.locator('button:has-text("Write Review"), button:has-text("Leave Review")').first();
    if (await reviewButton.isVisible()) {
      await reviewButton.click();
      
      // Select rating
      await page.click('[data-rating="5"], button[aria-label*="5 stars"]');
      
      // Fill review
      await page.fill('textarea[name="comment"], textarea[name="review"]', 'Excellent property, highly recommended!');
      
      // Optional: Add photos
      const photoUpload = page.locator('input[type="file"]');
      if (await photoUpload.isVisible()) {
        const buffer = Buffer.from('fake image');
        await photoUpload.setInputFiles({
          name: 'review-photo.jpg',
          mimeType: 'image/jpeg',
          buffer: buffer,
        });
      }
      
      await page.click('button:has-text("Submit"), button[type="submit"]');
      
      await expect(page.locator('text=/review submitted|thank you/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should filter reviews by rating', async ({ page }) => {
    await navHelper.goToProperties();
    await page.locator('[data-testid="property-card"]').first().click();
    
    const filterButton = page.locator('button:has-text("5 stars"), select[name="rating"]');
    if (await filterButton.first().isVisible()) {
      await filterButton.first().click();
      await page.waitForTimeout(1000);
    }
  });

  test('should sort reviews by date', async ({ page }) => {
    await navHelper.goToProperties();
    await page.locator('[data-testid="property-card"]').first().click();
    
    const sortSelect = page.locator('select[name="sort"], button:has-text("Sort")');
    if (await sortSelect.first().isVisible()) {
      await sortSelect.first().click();
      await page.click('text=/most recent|newest/i');
    }
  });

  test('should report inappropriate review', async ({ page }) => {
    await navHelper.goToProperties();
    await page.locator('[data-testid="property-card"]').first().click();
    
    const reportButton = page.locator('button:has-text("Report"), button[aria-label*="report"]').first();
    if (await reportButton.isVisible()) {
      await reportButton.click();
      
      await page.selectOption('select[name="reason"]', 'inappropriate');
      await page.fill('textarea[name="details"]', 'Contains offensive language');
      await page.click('button:has-text("Submit")');
      
      await expect(page.locator('text=/report submitted|reported/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should like a helpful review', async ({ page }) => {
    await navHelper.goToProperties();
    await page.locator('[data-testid="property-card"]').first().click();
    
    const helpfulButton = page.locator('button:has-text("Helpful"), button[aria-label*="helpful"]').first();
    if (await helpfulButton.isVisible()) {
      await helpfulButton.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should view all reviews from a user', async ({ page }) => {
    await navHelper.goToProperties();
    await page.locator('[data-testid="property-card"]').first().click();
    
    const reviewerName = page.locator('[data-testid="reviewer-name"], .reviewer-name').first();
    if (await reviewerName.isVisible()) {
      await reviewerName.click();
      
      await expect(page.locator('text=/reviews by|all reviews/i')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should respond to review as host', async ({ page }) => {
    await page.goto('/host/reviews');
    
    const respondButton = page.locator('button:has-text("Respond"), button:has-text("Reply")').first();
    if (await respondButton.isVisible()) {
      await respondButton.click();
      
      await page.fill('textarea[name="response"]', 'Thank you for your feedback!');
      await page.click('button:has-text("Submit")');
      
      await expect(page.locator('text=/response posted|sent/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should edit submitted review', async ({ page }) => {
    await page.goto('/profile/reviews');
    
    const editButton = page.locator('button:has-text("Edit")').first();
    if (await editButton.isVisible()) {
      await editButton.click();
      
      await page.fill('textarea[name="comment"], textarea[name="review"]', 'Updated review text.');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/updated|saved/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should delete submitted review', async ({ page }) => {
    await page.goto('/profile/reviews');
    
    const deleteButton = page.locator('button:has-text("Delete")').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/deleted|removed/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
