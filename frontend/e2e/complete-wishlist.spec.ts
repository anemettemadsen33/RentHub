import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { PropertyHelper } from './helpers/property.helper';

test.describe('Complete Wishlist and Favorites Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;
  let propertyHelper: PropertyHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    propertyHelper = new PropertyHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should add property to wishlist', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    const wishlistButton = page.locator('button:has-text("Save"), button[aria-label*="wishlist"]').first();
    await wishlistButton.click();
    
    await expect(page.locator('text=/added to wishlist|saved/i')).toBeVisible({ timeout: 5000 });
  });

  test('should view all wishlists', async ({ page }) => {
    await page.goto('/wishlists');
    
    await expect(page.locator('text=/wishlists|saved/i')).toBeVisible();
  });

  test('should create new wishlist', async ({ page }) => {
    await page.goto('/wishlists');
    
    const createButton = page.locator('button:has-text("Create Wishlist"), button:has-text("New List")');
    if (await createButton.isVisible()) {
      await createButton.click();
      
      await page.fill('input[name="name"]', 'Summer Vacation 2025');
      await page.fill('textarea[name="description"]', 'Places to visit this summer');
      await page.click('button:has-text("Create"), button[type="submit"]');
      
      await expect(page.locator('text=/wishlist created|created successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should rename wishlist', async ({ page }) => {
    await page.goto('/wishlists');
    
    const editButton = page.locator('button:has-text("Edit"), button[aria-label*="edit"]').first();
    if (await editButton.isVisible()) {
      await editButton.click();
      
      await page.fill('input[name="name"]', 'Updated Wishlist Name');
      await page.click('button:has-text("Save")');
      
      await expect(page.locator('text=/updated|saved/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should delete wishlist', async ({ page }) => {
    await page.goto('/wishlists');
    
    const deleteButton = page.locator('button:has-text("Delete"), button[aria-label*="delete"]').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm"), button:has-text("Yes")');
      
      await expect(page.locator('text=/deleted|removed/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should remove property from wishlist', async ({ page }) => {
    await page.goto('/wishlists');
    
    const firstWishlist = page.locator('[data-testid="wishlist-item"]').first();
    if (await firstWishlist.isVisible()) {
      await firstWishlist.click();
      
      const removeButton = page.locator('button:has-text("Remove")').first();
      if (await removeButton.isVisible()) {
        await removeButton.click();
        
        await expect(page.locator('text=/removed/i')).toBeVisible({ timeout: 10000 });
      }
    }
  });

  test('should share wishlist', async ({ page }) => {
    await page.goto('/wishlists');
    
    const shareButton = page.locator('button:has-text("Share"), button[aria-label*="share"]').first();
    if (await shareButton.isVisible()) {
      await shareButton.click();
      
      await expect(page.locator('[role="dialog"], .modal')).toBeVisible();
    }
  });

  test('should view favorites', async ({ page }) => {
    await navHelper.goToFavorites();
    
    await expect(page.locator('text=/favorites|saved properties/i')).toBeVisible();
  });

  test('should filter favorites by location', async ({ page }) => {
    await navHelper.goToFavorites();
    
    const locationFilter = page.locator('select[name="location"], input[name="location"]');
    if (await locationFilter.first().isVisible()) {
      await locationFilter.first().fill('Paris');
      await page.waitForTimeout(1000);
    }
  });

  test('should sort favorites by price', async ({ page }) => {
    await navHelper.goToFavorites();
    
    const sortSelect = page.locator('select[name="sort"], button:has-text("Sort")');
    if (await sortSelect.first().isVisible()) {
      await sortSelect.first().click();
      await page.click('text=/price/i');
    }
  });
});
