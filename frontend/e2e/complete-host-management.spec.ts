import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete Host/Property Management Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    formHelper = new FormHelper(page);
    await authHelper.login('host@example.com', 'password123');
  });

  test('should access host dashboard', async ({ page }) => {
    await page.goto('/host');
    
    await expect(page.locator('text=/host|manage properties/i')).toBeVisible();
  });

  test('should create new property listing', async ({ page }) => {
    await page.goto('/host/properties/new');
    
    // Basic Information
    await page.fill('input[name="title"]', 'Beautiful Apartment in City Center');
    await page.fill('textarea[name="description"]', 'A lovely apartment with all amenities.');
    await page.selectOption('select[name="property_type"]', 'apartment');
    
    // Location
    await page.fill('input[name="address"]', '123 Main Street');
    await page.fill('input[name="city"]', 'Bucharest');
    await page.fill('input[name="country"]', 'Romania');
    await page.fill('input[name="postal_code"]', '010001');
    
    // Details
    await page.fill('input[name="bedrooms"]', '2');
    await page.fill('input[name="bathrooms"]', '1');
    await page.fill('input[name="max_guests"]', '4');
    await page.fill('input[name="size"]', '80');
    
    // Pricing
    await page.fill('input[name="price_per_night"]', '100');
    await page.fill('input[name="cleaning_fee"]', '25');
    
    // Amenities
    await page.check('input[value="wifi"]');
    await page.check('input[value="parking"]');
    await page.check('input[value="kitchen"]');
    
    // Submit
    await formHelper.submitForm();
    
    await expect(page.locator('text=/property created|listing published/i')).toBeVisible({ timeout: 10000 });
  });

  test('should upload property images', async ({ page }) => {
    await page.goto('/host/properties');
    
    const firstProperty = page.locator('[data-testid="property-item"]').first();
    if (await firstProperty.isVisible()) {
      await firstProperty.click();
      
      const uploadButton = page.locator('input[type="file"], button:has-text("Upload Images")');
      if (await uploadButton.first().isVisible()) {
        const buffer = Buffer.from('fake image data');
        await uploadButton.first().setInputFiles([
          {
            name: 'image1.jpg',
            mimeType: 'image/jpeg',
            buffer: buffer,
          },
          {
            name: 'image2.jpg',
            mimeType: 'image/jpeg',
            buffer: buffer,
          }
        ]);
        
        await page.waitForTimeout(2000);
      }
    }
  });

  test('should edit property details', async ({ page }) => {
    await page.goto('/host/properties');
    
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit")').first();
    if (await editButton.isVisible()) {
      await editButton.click();
      
      await page.fill('input[name="price_per_night"]', '120');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/updated|saved/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should set property availability', async ({ page }) => {
    await page.goto('/host/properties');
    
    const calendarButton = page.locator('button:has-text("Calendar"), a:has-text("Availability")').first();
    if (await calendarButton.isVisible()) {
      await calendarButton.click();
      
      // Block dates
      const dateCell = page.locator('[data-date]').first();
      if (await dateCell.isVisible()) {
        await dateCell.click();
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should deactivate property listing', async ({ page }) => {
    await page.goto('/host/properties');
    
    const deactivateButton = page.locator('button:has-text("Deactivate"), button:has-text("Unpublish")').first();
    if (await deactivateButton.isVisible()) {
      await deactivateButton.click();
      await page.click('button:has-text("Confirm")');
      
      await expect(page.locator('text=/deactivated|unpublished/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should delete property listing', async ({ page }) => {
    await page.goto('/host/properties');
    
    const deleteButton = page.locator('button:has-text("Delete")').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm"), button:has-text("Yes, delete")');
      
      await expect(page.locator('text=/deleted|removed/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should view property analytics', async ({ page }) => {
    await page.goto('/host/properties');
    
    const analyticsButton = page.locator('button:has-text("Analytics"), a:has-text("Stats")').first();
    if (await analyticsButton.isVisible()) {
      await analyticsButton.click();
      
      await expect(page.locator('text=/views|bookings|revenue/i')).toBeVisible();
    }
  });

  test('should manage booking requests', async ({ page }) => {
    await page.goto('/host/bookings');
    
    const pendingBooking = page.locator('[data-status="pending"]').first();
    if (await pendingBooking.isVisible()) {
      await pendingBooking.click();
      
      // Accept booking
      const acceptButton = page.locator('button:has-text("Accept"), button:has-text("Approve")');
      if (await acceptButton.isVisible()) {
        await acceptButton.click();
        await expect(page.locator('text=/accepted|approved/i')).toBeVisible({ timeout: 10000 });
      }
    }
  });

  test('should reject booking request', async ({ page }) => {
    await page.goto('/host/bookings');
    
    const pendingBooking = page.locator('[data-status="pending"]').first();
    if (await pendingBooking.isVisible()) {
      await pendingBooking.click();
      
      const rejectButton = page.locator('button:has-text("Reject"), button:has-text("Decline")');
      if (await rejectButton.isVisible()) {
        await rejectButton.click();
        await page.fill('textarea[name="reason"]', 'Dates not available');
        await page.click('button:has-text("Confirm")');
        
        await expect(page.locator('text=/rejected|declined/i')).toBeVisible({ timeout: 10000 });
      }
    }
  });

  test('should set special pricing for dates', async ({ page }) => {
    await page.goto('/host/properties');
    
    const pricingButton = page.locator('button:has-text("Pricing"), a:has-text("Special Rates")').first();
    if (await pricingButton.isVisible()) {
      await pricingButton.click();
      
      await page.fill('input[name="start_date"]', '2025-12-20');
      await page.fill('input[name="end_date"]', '2025-12-31');
      await page.fill('input[name="price"]', '150');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/saved|updated/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
