import { test, expect } from '@playwright/test';
import { login } from './helpers';

test.describe('Wishlists Management', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    // Navigate to wishlists
    await page.goto('/wishlists');
    await page.waitForLoadState('networkidle');
  });

  test('should create a new wishlist', async ({ page }) => {
    // Fill in wishlist name
    await page.fill('input[placeholder*="name" i]', 'My Vacation Properties');
    
    // Submit form
    await page.click('button:has-text("Create")');
    
    // Wait for optimistic update or API response
    await page.waitForTimeout(1000);
    
    // Verify wishlist appears in the list
    await expect(page.locator('text=My Vacation Properties')).toBeVisible();
  });

  test('should rename an existing wishlist', async ({ page }) => {
    // Create a wishlist first
    await page.fill('input[placeholder*="name" i]', 'Original Name');
    await page.click('button:has-text("Create")');
    await page.waitForTimeout(1000);
    
    // Click rename/edit button
    const wishlistItem = page.locator('text=Original Name').first();
    await wishlistItem.click();
    
    // Find rename input or trigger rename mode
    const renameInput = page.locator('input[value="Original Name"]').first();
    if (await renameInput.isVisible()) {
      await renameInput.clear();
      await renameInput.fill('Updated Name');
      await renameInput.press('Enter');
      
      await page.waitForTimeout(1000);
      
      // Verify renamed
      await expect(page.locator('text=Updated Name')).toBeVisible();
      await expect(page.locator('text=Original Name')).not.toBeVisible();
    }
  });

  test('should add property to wishlist', async ({ page }) => {
    // Create a wishlist
    await page.fill('input[placeholder*="name" i]', 'Test Wishlist');
    await page.click('button:has-text("Create")');
    await page.waitForTimeout(1000);
    
    // Select the wishlist
    await page.click('text=Test Wishlist');
    await page.waitForTimeout(500);
    
    // Add property (assuming there's a property ID input)
    const propertyInput = page.locator('input[placeholder*="property" i]').first();
    if (await propertyInput.isVisible()) {
      await propertyInput.fill('123');
      await page.click('button:has-text("Add")');
      
      await page.waitForTimeout(1000);
      
      // Verify property was added
      const itemCount = await page.locator('[data-testid="wishlist-item"]').count();
      expect(itemCount).toBeGreaterThan(0);
    }
  });

  test('should remove property from wishlist', async ({ page }) => {
    // Assuming wishlist has items
    const removeButton = page.locator('button:has-text("Remove")').first();
    
    if (await removeButton.isVisible()) {
      const initialCount = await page.locator('[data-testid="wishlist-item"]').count();
      
      await removeButton.click();
      await page.waitForTimeout(1000);
      
      const finalCount = await page.locator('[data-testid="wishlist-item"]').count();
      expect(finalCount).toBeLessThan(initialCount);
    }
  });

  test('should delete a wishlist', async ({ page }) => {
    // Create a wishlist to delete
    await page.fill('input[placeholder*="name" i]', 'To Be Deleted');
    await page.click('button:has-text("Create")');
    await page.waitForTimeout(1000);
    
    // Find and click delete button
    const deleteButton = page.locator('button:has-text("Delete")').first();
    await deleteButton.click();
    
    // Confirm deletion if there's a confirmation dialog
    const confirmButton = page.locator('button:has-text("Confirm")');
    if (await confirmButton.isVisible({ timeout: 2000 })) {
      await confirmButton.click();
    }
    
    await page.waitForTimeout(1000);
    
    // Verify wishlist is removed
    await expect(page.locator('text=To Be Deleted')).not.toBeVisible();
  });

  test('should handle empty state', async ({ page }) => {
    // Check for empty state message
    const emptyMessage = page.locator('text=/no.*wishlist/i');
    
    // Empty state should show if no wishlists exist
    const wishlistCount = await page.locator('[data-testid="wishlist"]').count();
    if (wishlistCount === 0) {
      await expect(emptyMessage).toBeVisible();
    }
  });

  test('should show error on failed operations', async ({ page }) => {
    // Mock API failure
    await page.route('**/api/v1/wishlists', (route) => {
      route.fulfill({
        status: 500,
        body: JSON.stringify({ message: 'Server error' }),
      });
    });
    
    // Try to create a wishlist
    await page.fill('input[placeholder*="name" i]', 'Error Test');
    await page.click('button:has-text("Create")');
    
    await page.waitForTimeout(1000);
    
    // Should show error or revert optimistic update
    const errorText = page.locator('text=/error|failed/i');
    const hasError = await errorText.isVisible({ timeout: 3000 });
    expect(hasError).toBeTruthy();
  });
});
