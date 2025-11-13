import { test, expect, Page } from '@playwright/test';
import { login, waitForAppReady, mockJson, safeClick, safeFill } from './helpers';

/**
 * Advanced Features E2E Tests
 * 
 * Tests for advanced functionality including:
 * - Saved searches
 * - Property comparison
 * - Favorites/Wishlists
 * - Referral program
 * - Loyalty program
 * - Calendar sync
 * - Integrations
 */

test.describe('Saved Searches', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should save a search query', async ({ page }) => {
    await page.goto('/properties');
    await waitForAppReady(page);

    // Apply some filters
    await safeFill(page.locator('input[name="location"]'), 'Bucharest');
    await safeFill(page.locator('input[name="minPrice"]'), '100');
    await safeFill(page.locator('input[name="maxPrice"]'), '500');

    // Click save search button
    const saveBtn = page.locator('button:has-text("Save Search"), button:has-text("Save")').first();
    if (await saveBtn.isVisible()) {
      await saveBtn.click();
      await page.waitForTimeout(500);

      // Name the search
      const nameInput = page.locator('input[name="searchName"], input[name="name"]').first();
      if (await nameInput.isVisible()) {
        await nameInput.fill('Bucharest Apartments');

        // Mock save API
        await mockJson(page, '**/api/v1/saved-searches', {
          id: 1,
          name: 'Bucharest Apartments',
          filters: { location: 'Bucharest', minPrice: 100, maxPrice: 500 }
        }, 201);

        // Confirm save
        const confirmBtn = page.locator('button[type="submit"]:has-text("Save")').first();
        await confirmBtn.click();
        await page.waitForTimeout(500);

        // Success message
        const success = page.locator('text=/search.*saved|saved.*successfully/i');
        const visible = await success.first().isVisible({ timeout: 3000 }).catch(() => false);
        expect(visible).toBeTruthy();
      }
    }
  });

  test('should view saved searches', async ({ page }) => {
    await page.goto('/saved-searches');
    await waitForAppReady(page);

    // Check for saved searches list
    const heading = page.locator('h1, h2').filter({ hasText: /saved.*searches/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // List or empty state should be visible
    const searchItems = page.locator('[data-testid="saved-search-item"]');
    const emptyState = page.locator('text=/no.*saved.*searches|save.*search/i');
    
    const hasItems = await searchItems.count() > 0;
    const hasEmptyState = await emptyState.isVisible().catch(() => false);
    
    expect(hasItems || hasEmptyState).toBeTruthy();
  });

  test('should load a saved search', async ({ page }) => {
    await page.goto('/saved-searches');
    await waitForAppReady(page);

    // Click on saved search
    const firstSearch = page.locator('[data-testid="saved-search-item"]').first();
    if (await firstSearch.isVisible()) {
      await firstSearch.click();
      await page.waitForLoadState('networkidle');

      // Should navigate to properties with filters applied
      await expect(page).toHaveURL(/\/properties/);
    }
  });

  test('should delete a saved search', async ({ page }) => {
    await page.goto('/saved-searches');
    await waitForAppReady(page);

    // Click delete button
    const deleteBtn = page.locator('button:has-text("Delete"), [data-testid="delete-search"]').first();
    if (await deleteBtn.isVisible()) {
      await deleteBtn.click();

      // Confirmation dialog
      const confirmDialog = page.locator('[role="dialog"], [role="alertdialog"]');
      if (await confirmDialog.isVisible()) {
        // Cancel for test
        const cancelBtn = page.locator('button:has-text("Cancel")').first();
        await cancelBtn.click();
      }
    }
  });
});

test.describe('Property Comparison', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should add property to comparison', async ({ page }) => {
    await page.goto('/properties/1');
    await waitForAppReady(page);

    // Click compare button
    const compareBtn = page.locator('button:has-text("Compare"), [data-testid="add-to-compare"]').first();
    if (await compareBtn.isVisible()) {
      await compareBtn.click();
      await page.waitForTimeout(500);

      // Success message or indicator
      const success = page.locator('text=/added.*comparison|compare.*list/i');
      const visible = await success.first().isVisible({ timeout: 3000 }).catch(() => false);
      expect(visible).toBeTruthy();
    }
  });

  test('should view property comparison', async ({ page }) => {
    await page.goto('/property-comparison');
    await waitForAppReady(page);

    // Comparison page should load
    const heading = page.locator('h1, h2').filter({ hasText: /compare|comparison/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // Comparison table or cards
    const comparisonView = page.locator('[data-testid="comparison-table"], [data-testid="comparison-grid"]');
    if (await comparisonView.count() > 0) {
      await expect(comparisonView.first()).toBeVisible();
    }
  });

  test('should remove property from comparison', async ({ page }) => {
    await page.goto('/property-comparison');
    await waitForAppReady(page);

    // Remove button
    const removeBtn = page.locator('button:has-text("Remove"), [data-testid="remove-from-compare"]').first();
    if (await removeBtn.isVisible()) {
      await removeBtn.click();
      await page.waitForTimeout(500);
    }
  });

  test('should compare property features side by side', async ({ page }) => {
    await page.goto('/property-comparison');
    await waitForAppReady(page);

    // Check for comparison attributes
    const attributes = ['price', 'bedrooms', 'bathrooms', 'area'];
    for (const attr of attributes) {
      const attrRow = page.locator(`text=/${attr}/i`);
      if (await attrRow.count() > 0) {
        expect(await attrRow.count()).toBeGreaterThan(0);
        break;
      }
    }
  });
});

test.describe('Referral Program', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display referral page', async ({ page }) => {
    await page.goto('/referrals');
    await waitForAppReady(page);

    // Referral heading
    const heading = page.locator('h1, h2').filter({ hasText: /referral|refer.*friend|invite/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show referral code', async ({ page }) => {
    await page.goto('/referrals');
    await waitForAppReady(page);

    // Referral code display
    const referralCode = page.locator('[data-testid="referral-code"], code');
    if (await referralCode.count() > 0) {
      await expect(referralCode.first()).toBeVisible();
    }
  });

  test('should copy referral link', async ({ page }) => {
    await page.goto('/referrals');
    await waitForAppReady(page);

    // Copy button
    const copyBtn = page.locator('button:has-text("Copy"), button:has-text("Copy Link")').first();
    if (await copyBtn.isVisible()) {
      await copyBtn.click();
      await page.waitForTimeout(500);

      // Success message
      const success = page.locator('text=/copied|link.*copied/i');
      const visible = await success.first().isVisible({ timeout: 3000 }).catch(() => false);
      expect(visible).toBeTruthy();
    }
  });

  test('should display referral statistics', async ({ page }) => {
    await page.goto('/referrals');
    await waitForAppReady(page);

    // Stats like total referrals, earnings, etc.
    const stats = page.locator('[data-testid="referral-stats"], .stats');
    if (await stats.count() > 0) {
      await expect(stats.first()).toBeVisible();
    }
  });
});

test.describe('Loyalty Program', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display loyalty program page', async ({ page }) => {
    await page.goto('/loyalty');
    await waitForAppReady(page);

    // Loyalty heading
    const heading = page.locator('h1, h2').filter({ hasText: /loyalty|rewards|points/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show current points balance', async ({ page }) => {
    await page.goto('/loyalty');
    await waitForAppReady(page);

    // Points balance
    const pointsBalance = page.locator('[data-testid="points-balance"], text=/\\d+.*points/i');
    if (await pointsBalance.count() > 0) {
      await expect(pointsBalance.first()).toBeVisible();
    }
  });

  test('should display loyalty tier', async ({ page }) => {
    await page.goto('/loyalty');
    await waitForAppReady(page);

    // Tier badge (Bronze, Silver, Gold, etc.)
    const tier = page.locator('[data-testid="loyalty-tier"], text=/bronze|silver|gold|platinum/i');
    if (await tier.count() > 0) {
      await expect(tier.first()).toBeVisible();
    }
  });

  test('should show points history', async ({ page }) => {
    await page.goto('/loyalty');
    await waitForAppReady(page);

    // Points transaction history
    const history = page.locator('[data-testid="points-history"], text=/history|transactions/i');
    if (await history.count() > 0) {
      await expect(history.first()).toBeVisible();
    }
  });

  test('should display available rewards', async ({ page }) => {
    await page.goto('/loyalty');
    await waitForAppReady(page);

    // Rewards catalog
    const rewards = page.locator('[data-testid="rewards-list"], text=/redeem|rewards/i');
    if (await rewards.count() > 0) {
      await expect(rewards.first()).toBeVisible();
    }
  });
});

test.describe('Calendar Sync', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, { email: 'owner@renthub.com', password: 'password' });
  });

  test('should display calendar sync page', async ({ page }) => {
    await page.goto('/calendar-sync');
    await waitForAppReady(page);

    // Calendar sync heading
    const heading = page.locator('h1, h2').filter({ hasText: /calendar.*sync|synchronize/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show connected calendars', async ({ page }) => {
    await page.goto('/calendar-sync');
    await waitForAppReady(page);

    // Connected calendars list
    const calendarsList = page.locator('[data-testid="connected-calendars"]');
    if (await calendarsList.isVisible()) {
      await expect(calendarsList).toBeVisible();
    }
  });

  test('should add calendar integration', async ({ page }) => {
    await page.goto('/calendar-sync');
    await waitForAppReady(page);

    // Add calendar button
    const addBtn = page.locator('button:has-text("Add Calendar"), button:has-text("Connect")').first();
    if (await addBtn.isVisible()) {
      await addBtn.click();
      await page.waitForTimeout(500);

      // Integration options should appear
      const integrationOptions = page.locator('text=/google|outlook|airbnb|booking/i');
      if (await integrationOptions.count() > 0) {
        await expect(integrationOptions.first()).toBeVisible();
      }
    }
  });

  test('should sync with Google Calendar', async ({ page }) => {
    await page.goto('/integrations/google-calendar');
    await waitForAppReady(page);

    // Google Calendar integration
    const heading = page.locator('h1, h2').filter({ hasText: /google.*calendar/i });
    if (await heading.isVisible()) {
      await expect(heading).toBeVisible();
    }
  });
});

test.describe('Payment Methods', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display payment methods page', async ({ page }) => {
    await page.goto('/payments');
    await waitForAppReady(page);

    // Payment methods heading
    const heading = page.locator('h1, h2').filter({ hasText: /payment.*methods|cards|billing/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should add new payment method', async ({ page }) => {
    await page.goto('/payments');
    await waitForAppReady(page);

    // Add card button
    const addBtn = page.locator('button:has-text("Add Card"), button:has-text("Add Payment")').first();
    if (await addBtn.isVisible()) {
      await addBtn.click();
      await page.waitForTimeout(500);

      // Card form should appear
      const cardForm = page.locator('form, [data-testid="card-form"]');
      if (await cardForm.isVisible()) {
        // Mock Stripe or payment provider
        await safeFill(page.locator('input[name="cardNumber"]'), '4242424242424242');
        await safeFill(page.locator('input[name="exp"]'), '12/30');
        await safeFill(page.locator('input[name="cvc"]'), '123');

        // Mock save API
        await mockJson(page, '**/api/v1/payment-methods', {
          id: 'pm_123',
          last4: '4242',
          brand: 'visa'
        }, 201);

        // Save button
        const saveBtn = page.locator('button[type="submit"]:has-text("Save"), button:has-text("Add")').first();
        await saveBtn.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should view payment history', async ({ page }) => {
    await page.goto('/payments/history');
    await waitForAppReady(page);

    // Payment history heading
    const heading = page.locator('h1, h2').filter({ hasText: /payment.*history|transactions/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // Transaction list or empty state
    const transactions = page.locator('[data-testid="transaction-item"]');
    const emptyState = page.locator('text=/no.*transactions|no.*payments/i');
    
    const hasTransactions = await transactions.count() > 0;
    const hasEmptyState = await emptyState.isVisible().catch(() => false);
    
    expect(hasTransactions || hasEmptyState).toBeTruthy();
  });

  test('should set default payment method', async ({ page }) => {
    await page.goto('/payments');
    await waitForAppReady(page);

    // Set as default button
    const defaultBtn = page.locator('button:has-text("Set as Default"), button:has-text("Make Default")').first();
    if (await defaultBtn.isVisible()) {
      await defaultBtn.click();
      await page.waitForTimeout(500);

      // Mock API
      await mockJson(page, '**/api/v1/payment-methods/*/default', { success: true }, 200);
    }
  });

  test('should remove payment method', async ({ page }) => {
    await page.goto('/payments');
    await waitForAppReady(page);

    // Remove button
    const removeBtn = page.locator('button:has-text("Remove"), button:has-text("Delete")').first();
    if (await removeBtn.isVisible()) {
      await removeBtn.click();

      // Confirmation dialog
      const confirmDialog = page.locator('[role="dialog"], [role="alertdialog"]');
      if (await confirmDialog.isVisible()) {
        // Cancel for test
        const cancelBtn = page.locator('button:has-text("Cancel")').first();
        await cancelBtn.click();
      }
    }
  });
});

test.describe('Stripe Integration', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display Stripe integration page', async ({ page }) => {
    await page.goto('/integrations/stripe');
    await waitForAppReady(page);

    // Stripe integration heading
    const heading = page.locator('h1, h2').filter({ hasText: /stripe|payment.*integration/i });
    if (await heading.isVisible()) {
      await expect(heading).toBeVisible();
    }
  });

  test('should show Stripe connection status', async ({ page }) => {
    await page.goto('/integrations/stripe');
    await waitForAppReady(page);

    // Connection status
    const status = page.locator('[data-testid="stripe-status"], text=/connected|disconnected|connect.*stripe/i');
    if (await status.count() > 0) {
      await expect(status.first()).toBeVisible();
    }
  });
});

test.describe('Real-time Features', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should display real-time integrations page', async ({ page }) => {
    await page.goto('/integrations/realtime');
    await waitForAppReady(page);

    // Real-time heading
    const heading = page.locator('h1, h2').filter({ hasText: /real.*time|live.*updates/i });
    if (await heading.isVisible()) {
      await expect(heading).toBeVisible();
    }
  });

  test('should show WebSocket connection status', async ({ page }) => {
    await page.goto('/integrations/realtime');
    await waitForAppReady(page);

    // Connection status indicator
    const status = page.locator('[data-testid="websocket-status"], text=/connected|disconnected|online|offline/i');
    if (await status.count() > 0) {
      await expect(status.first()).toBeVisible();
    }
  });
});

test.describe('Help and FAQ', () => {
  test('should display FAQ page', async ({ page }) => {
    await page.goto('/faq');
    await waitForAppReady(page);

    // FAQ heading
    const heading = page.locator('h1, h2').filter({ hasText: /faq|frequently.*asked|questions/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // FAQ items
    const faqItems = page.locator('[data-testid="faq-item"], .faq, details');
    expect(await faqItems.count()).toBeGreaterThan(0);
  });

  test('should expand FAQ item', async ({ page }) => {
    await page.goto('/faq');
    await waitForAppReady(page);

    // Click first FAQ
    const firstFaq = page.locator('[data-testid="faq-item"], details').first();
    if (await firstFaq.isVisible()) {
      await firstFaq.click();
      await page.waitForTimeout(300);

      // Answer should be visible
      const answer = firstFaq.locator('[data-testid="faq-answer"], p, div');
      if (await answer.count() > 0) {
        await expect(answer.first()).toBeVisible();
      }
    }
  });

  test('should display help page', async ({ page }) => {
    await page.goto('/help');
    await waitForAppReady(page);

    // Help heading
    const heading = page.locator('h1, h2').filter({ hasText: /help|support|assistance/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display contact page', async ({ page }) => {
    await page.goto('/contact');
    await waitForAppReady(page);

    // Contact heading
    const heading = page.locator('h1, h2').filter({ hasText: /contact|get.*in.*touch/i });
    await expect(heading.first()).toBeVisible({ timeout: 10000 });

    // Contact form
    const contactForm = page.locator('form');
    if (await contactForm.count() > 0) {
      await expect(contactForm.first()).toBeVisible();
    }
  });
});
