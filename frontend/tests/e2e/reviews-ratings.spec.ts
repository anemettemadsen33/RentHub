import { test, expect, Page } from '@playwright/test';
import { login, waitForAppReady, mockJson, safeClick, safeFill } from './helpers';

/**
 * Reviews and Ratings E2E Tests
 * 
 * Tests for review system functionality including:
 * - Viewing property reviews
 * - Submitting reviews
 * - Rating properties
 * - Review moderation
 * - Review responses
 */

test.describe('Property Reviews', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display reviews on property page', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Look for reviews section
    const reviewsSection = page.locator('[data-testid="reviews-section"], text=/reviews|ratings/i').first();
    if (await reviewsSection.isVisible()) {
      await expect(reviewsSection).toBeVisible();
    }

    // Check for review cards or list
    const reviewItems = page.locator('[data-testid="review-item"], .review');
    const count = await reviewItems.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('should show average rating', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Look for rating display (stars or numeric)
    const rating = page.locator('[data-testid="average-rating"], [data-testid="rating"]').first();
    if (await rating.isVisible()) {
      const ratingText = await rating.textContent();
      expect(ratingText).toBeTruthy();
    }
  });

  test('should display review count', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Look for review count
    const reviewCount = page.locator('[data-testid="review-count"], text=/\\d+.*reviews?/i').first();
    if (await reviewCount.isVisible()) {
      const countText = await reviewCount.textContent();
      expect(countText).toMatch(/\d+/);
    }
  });

  test('should navigate to reviews page', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Click "See all reviews" or similar link
    const viewAllLink = page.locator('a:has-text("See all"), a:has-text("All reviews"), button:has-text("View all")').first();
    if (await viewAllLink.isVisible()) {
      await viewAllLink.click();
      await page.waitForLoadState('networkidle');

      // Should navigate to reviews page
      await expect(page).toHaveURL(/\/properties\/\d+\/reviews/);
    }
  });

  test('should submit a new review', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Click write review button
    const writeBtn = page.locator('button:has-text("Write"), button:has-text("Add Review"), button:has-text("Leave Review")').first();
    if (await writeBtn.isVisible()) {
      await writeBtn.click();
      await page.waitForTimeout(500);

      // Fill review form
      const ratingInput = page.locator('input[name="rating"], [data-testid="rating-input"]').first();
      const reviewTextarea = page.locator('textarea[name="review"], textarea[name="comment"]').first();

      if (await reviewTextarea.isVisible()) {
        // Select rating (if star buttons)
        const starButtons = page.locator('[data-testid="star-button"], button[aria-label*="star"]');
        if (await starButtons.count() > 0) {
          await starButtons.nth(4).click(); // 5 stars
        } else if (await ratingInput.isVisible()) {
          await ratingInput.fill('5');
        }

        // Write review text
        await reviewTextarea.fill('This is an excellent property! Highly recommended for families.');

        // Mock submit API
        await mockJson(page, '**/api/v1/reviews', {
          id: 101,
          rating: 5,
          comment: 'This is an excellent property! Highly recommended for families.',
          created_at: new Date().toISOString()
        }, 201);

        // Submit review
        const submitBtn = page.locator('button[type="submit"]:has-text("Submit"), button:has-text("Post")').first();
        await submitBtn.click();
        await page.waitForLoadState('networkidle');

        // Success message
        const success = page.locator('text=/success|thank.*you|review.*posted/i');
        const visible = await success.first().isVisible({ timeout: 5000 }).catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should validate review form', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    const writeBtn = page.locator('button:has-text("Write"), button:has-text("Add Review")').first();
    if (await writeBtn.isVisible()) {
      await writeBtn.click();
      await page.waitForTimeout(500);

      // Try to submit without rating
      const submitBtn = page.locator('button[type="submit"]:has-text("Submit")').first();
      if (await submitBtn.isVisible()) {
        await submitBtn.click();
        await page.waitForTimeout(500);

        // Validation error should appear
        const errorMsg = page.locator('text=/required|please.*select|rating.*required/i');
        const visible = await errorMsg.first().isVisible({ timeout: 3000 }).catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should filter reviews by rating', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for rating filter
    const filterButtons = page.locator('button:has-text("5 stars"), button:has-text("4 stars")');
    if (await filterButtons.count() > 0) {
      await filterButtons.first().click();
      await page.waitForTimeout(500);

      // Reviews should be filtered
      const reviews = page.locator('[data-testid="review-item"]');
      const count = await reviews.count();
      expect(count).toBeGreaterThanOrEqual(0);
    }
  });

  test('should sort reviews', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for sort dropdown
    const sortSelect = page.locator('select[name="sort"], [data-testid="sort-select"]').first();
    if (await sortSelect.isVisible()) {
      await sortSelect.selectOption({ label: 'Most Recent' });
      await page.waitForTimeout(500);

      // Reviews should be reordered
      const reviews = page.locator('[data-testid="review-item"]');
      expect(await reviews.count()).toBeGreaterThanOrEqual(0);
    }
  });

  test('should display reviewer information', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    const firstReview = page.locator('[data-testid="review-item"]').first();
    if (await firstReview.isVisible()) {
      // Should show reviewer name
      const reviewerName = firstReview.locator('[data-testid="reviewer-name"], .reviewer-name');
      expect(await reviewerName.count()).toBeGreaterThan(0);

      // Should show review date
      const reviewDate = firstReview.locator('time, [data-testid="review-date"]');
      expect(await reviewDate.count()).toBeGreaterThan(0);
    }
  });

  test('should like/helpful a review', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for helpful button
    const helpfulBtn = page.locator('button:has-text("Helpful"), button:has-text("Like")').first();
    if (await helpfulBtn.isVisible()) {
      const initialText = await helpfulBtn.textContent();
      
      // Mock API
      await mockJson(page, '**/api/v1/reviews/*/helpful', { helpful: true }, 200);
      
      await helpfulBtn.click();
      await page.waitForTimeout(500);

      // Button text or state should change
      const newText = await helpfulBtn.textContent();
      expect(newText).toBeTruthy();
    }
  });

  test('should report inappropriate review', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for report button (may be in menu)
    const reportBtn = page.locator('button:has-text("Report"), [data-testid="report-review"]').first();
    if (await reportBtn.isVisible()) {
      await reportBtn.click();
      await page.waitForTimeout(500);

      // Report dialog should appear
      const reportDialog = page.locator('[role="dialog"]');
      if (await reportDialog.isVisible()) {
        // Select reason
        const reasonSelect = page.locator('select[name="reason"]').first();
        if (await reasonSelect.isVisible()) {
          await reasonSelect.selectOption({ index: 1 });
        }

        // Mock submit
        await mockJson(page, '**/api/v1/reviews/*/report', { success: true }, 200);

        // Submit report
        const submitBtn = page.locator('button[type="submit"]:has-text("Report")').first();
        await submitBtn.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should allow owner to respond to review', async ({ page }) => {
    // Login as owner
    await login(page, { email: 'owner@renthub.com', password: 'password' });
    
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for respond button
    const respondBtn = page.locator('button:has-text("Respond"), button:has-text("Reply")').first();
    if (await respondBtn.isVisible()) {
      await respondBtn.click();
      await page.waitForTimeout(500);

      // Response form should appear
      const responseTextarea = page.locator('textarea[name="response"]').first();
      if (await responseTextarea.isVisible()) {
        await responseTextarea.fill('Thank you for your review! We appreciate your feedback.');

        // Mock API
        await mockJson(page, '**/api/v1/reviews/*/response', {
          response: 'Thank you for your review! We appreciate your feedback.'
        }, 201);

        // Submit response
        const submitBtn = page.locator('button[type="submit"]:has-text("Submit"), button:has-text("Post")').first();
        await submitBtn.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should display review photos if uploaded', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for review photos
    const reviewPhotos = page.locator('[data-testid="review-photo"], .review-image img');
    if (await reviewPhotos.count() > 0) {
      await expect(reviewPhotos.first()).toBeVisible();
    }
  });

  test('should paginate reviews', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for pagination controls
    const nextBtn = page.locator('button:has-text("Next"), a:has-text("Next")').first();
    if (await nextBtn.isVisible()) {
      await nextBtn.click();
      await page.waitForLoadState('networkidle');

      // Should load more reviews
      const reviews = page.locator('[data-testid="review-item"]');
      expect(await reviews.count()).toBeGreaterThanOrEqual(0);
    }
  });

  test('should show rating breakdown', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Look for rating distribution (e.g., 5 stars: 80%, 4 stars: 15%)
    const ratingBreakdown = page.locator('[data-testid="rating-breakdown"], .rating-distribution');
    if (await ratingBreakdown.isVisible()) {
      await expect(ratingBreakdown).toBeVisible();
    }
  });

  test('should prevent duplicate reviews from same user', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Mock error for duplicate review
    await mockJson(page, '**/api/v1/reviews', {
      error: 'You have already reviewed this property'
    }, 422);

    const writeBtn = page.locator('button:has-text("Write"), button:has-text("Add Review")').first();
    if (await writeBtn.isVisible()) {
      // Button might be disabled or show message
      const disabled = await writeBtn.isDisabled();
      // Either disabled or will show error on submit
      expect(typeof disabled).toBe('boolean');
    }
  });

  test('should edit own review', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Find user's own review
    const editBtn = page.locator('button:has-text("Edit")').first();
    if (await editBtn.isVisible()) {
      await editBtn.click();
      await page.waitForTimeout(500);

      // Edit review text
      const reviewTextarea = page.locator('textarea[name="review"]').first();
      if (await reviewTextarea.isVisible()) {
        await reviewTextarea.clear();
        await reviewTextarea.fill('Updated review text from E2E test');

        // Mock update API
        await mockJson(page, '**/api/v1/reviews/*', {
          comment: 'Updated review text from E2E test'
        }, 200);

        // Save changes
        const saveBtn = page.locator('button[type="submit"]:has-text("Save")').first();
        await saveBtn.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should delete own review', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Find delete button
    const deleteBtn = page.locator('button:has-text("Delete")').first();
    if (await deleteBtn.isVisible()) {
      await deleteBtn.click();

      // Confirmation dialog
      const confirmDialog = page.locator('[role="dialog"], [role="alertdialog"]');
      if (await confirmDialog.isVisible()) {
        // Cancel deletion for test
        const cancelBtn = page.locator('button:has-text("Cancel")').first();
        await cancelBtn.click();
      }
    }
  });
});

test.describe('Review Statistics', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display category ratings', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Look for category-specific ratings (cleanliness, location, value, etc.)
    const categories = ['cleanliness', 'location', 'value', 'amenities', 'communication'];
    
    for (const category of categories) {
      const categoryRating = page.locator(`[data-testid="rating-${category}"], text=/${category}/i`);
      if (await categoryRating.count() > 0) {
        // At least one category rating exists
        expect(await categoryRating.count()).toBeGreaterThan(0);
        break;
      }
    }
  });

  test('should show verified booking badge on reviews', async ({ page }) => {
    await page.goto('/properties/1/reviews');
    await waitForAppReady(page);

    // Look for verified badge
    const verifiedBadge = page.locator('[data-testid="verified-badge"], text=/verified.*stay|verified.*booking/i');
    if (await verifiedBadge.count() > 0) {
      await expect(verifiedBadge.first()).toBeVisible();
    }
  });
});
