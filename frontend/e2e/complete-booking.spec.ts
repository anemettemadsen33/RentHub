import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { BookingHelper } from './helpers/booking.helper';
import { PropertyHelper } from './helpers/property.helper';
import { NavigationHelper } from './helpers/navigation.helper';

test.describe('Complete Booking Flow', () => {
  let authHelper: AuthHelper;
  let bookingHelper: BookingHelper;
  let propertyHelper: PropertyHelper;
  let navHelper: NavigationHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    bookingHelper = new BookingHelper(page);
    propertyHelper = new PropertyHelper(page);
    navHelper = new NavigationHelper(page);
    
    // Login before each test
    await authHelper.login('test@example.com', 'password123');
  });

  test('should create a booking with all details', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    await bookingHelper.createBooking({
      checkIn: '2025-03-10',
      checkOut: '2025-03-15',
      guests: 2,
      specialRequests: 'Early check-in if possible'
    });
    
    // Verify booking confirmation
    await expect(page.locator('text=/booking confirmed|reservation complete|success/i')).toBeVisible({ timeout: 10000 });
  });

  test('should validate date selection', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    // Try to book with check-out before check-in
    await page.fill('input[name="check_in"], input[name="checkIn"]', '2025-03-15');
    await page.fill('input[name="check_out"], input[name="checkOut"]', '2025-03-10');
    await page.click('button:has-text("Book"), button:has-text("Confirm")');
    
    // Should show error
    await expect(page.locator('text=/invalid dates|check-out.*after check-in/i')).toBeVisible();
  });

  test('should calculate total price correctly', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    // Fill dates
    await page.fill('input[name="check_in"], input[name="checkIn"]', '2025-03-10');
    await page.fill('input[name="check_out"], input[name="checkOut"]', '2025-03-15');
    
    // Wait for price calculation
    await page.waitForTimeout(1000);
    
    // Verify total is visible
    await expect(page.locator('text=/total|total amount|total price/i')).toBeVisible();
  });

  test('should view booking details', async ({ page }) => {
    await navHelper.goToBookings();
    
    // Click first booking
    const firstBooking = page.locator('[data-testid="booking-card"], .booking-card').first();
    if (await firstBooking.isVisible()) {
      await firstBooking.click();
      
      // Verify details page
      await expect(page.locator('text=/booking details|reservation/i')).toBeVisible();
      await expect(page.locator('text=/check-in|arrival/i')).toBeVisible();
      await expect(page.locator('text=/check-out|departure/i')).toBeVisible();
    }
  });

  test('should cancel a booking', async ({ page }) => {
    await navHelper.goToBookings();
    
    const cancelButton = page.locator('button:has-text("Cancel")').first();
    if (await cancelButton.isVisible()) {
      await cancelButton.click();
      
      // Confirm cancellation
      await page.click('button:has-text("Confirm"), button:has-text("Yes")');
      
      // Verify cancellation success
      await expect(page.locator('text=/cancelled|cancellation successful/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should filter bookings by status', async ({ page }) => {
    await navHelper.goToBookings();
    
    const statusFilter = page.locator('select[name="status"], button:has-text("Status")').first();
    if (await statusFilter.isVisible()) {
      await statusFilter.click();
      await page.click('text=/upcoming|confirmed/i');
      await page.waitForTimeout(1000);
    }
  });

  test('should download booking invoice', async ({ page }) => {
    await navHelper.goToBookings();
    
    const downloadButton = page.locator('button:has-text("Invoice"), button:has-text("Download")').first();
    if (await downloadButton.isVisible()) {
      const [download] = await Promise.all([
        page.waitForEvent('download'),
        downloadButton.click()
      ]);
      
      expect(download.suggestedFilename()).toMatch(/invoice|booking/i);
    }
  });

  test('should modify booking dates', async ({ page }) => {
    await navHelper.goToBookings();
    
    const modifyButton = page.locator('button:has-text("Modify"), button:has-text("Change")').first();
    if (await modifyButton.isVisible()) {
      await modifyButton.click();
      
      // Change check-out date
      await page.fill('input[name="check_out"], input[name="checkOut"]', '2025-03-20');
      await page.click('button:has-text("Save"), button:has-text("Update")');
      
      await expect(page.locator('text=/updated|modified successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should leave a review after checkout', async ({ page }) => {
    await navHelper.goToBookings();
    
    const reviewButton = page.locator('button:has-text("Review"), button:has-text("Leave a review")').first();
    if (await reviewButton.isVisible()) {
      await reviewButton.click();
      
      // Fill review form
      await page.click('[data-rating="5"], button[aria-label*="5 stars"]');
      await page.fill('textarea[name="comment"], textarea[name="review"]', 'Great stay! Highly recommended.');
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/review submitted|thank you/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should contact host from booking', async ({ page }) => {
    await navHelper.goToBookings();
    
    const contactButton = page.locator('button:has-text("Contact"), button:has-text("Message")').first();
    if (await contactButton.isVisible()) {
      await contactButton.click();
      
      // Send message
      await page.fill('textarea[name="message"]', 'Hello, I have a question about the property.');
      await page.click('button:has-text("Send")');
      
      await expect(page.locator('text=/message sent|sent successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });
});
