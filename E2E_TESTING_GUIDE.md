# 🧪 E2E Testing Guide for RentHub

## Overview

This document provides comprehensive information about the End-to-End (E2E) testing suite for RentHub, including test coverage, setup instructions, and how to run and maintain the tests.

## 📊 Test Coverage

### Total Test Files: 26

#### Core Functionality (11 files)
1. **auth.spec.ts** - Complete authentication flows
   - Registration (with validation)
   - Login/Logout
   - Password reset
   - Email verification
   - 2FA (if enabled)
   - Social authentication
   - Session persistence

2. **search.spec.ts** - Property search and filtering
   - Basic search
   - Advanced filters
   - Sort options
   - Results display

3. **booking-flow.spec.ts** - End-to-end booking process
   - Property selection
   - Date selection
   - Guest details
   - Payment processing
   - Confirmation

4. **booking-detail.spec.ts** - Booking management
   - View booking details
   - Modify bookings
   - Cancel bookings
   - Booking history

5. **payments.spec.ts** - Payment processing
   - Payment methods management
   - Transaction processing
   - Payment history
   - Refunds

6. **invoices.spec.ts** - Invoice management
   - Generate invoices
   - View invoices
   - Download/Print invoices
   - Invoice history

7. **insurance.spec.ts** - Insurance features
   - View insurance plans
   - Purchase insurance
   - Claims process
   - Insurance coverage details

8. **wishlists.spec.ts** - Favorites and wishlists
   - Add to wishlist
   - Remove from wishlist
   - View wishlists
   - Share wishlists

9. **property-access.spec.ts** - Property access management
   - Smart lock integration
   - Access codes
   - Activity logs
   - Guest access

10. **property-calendar.spec.ts** - Calendar management
    - View availability
    - Block dates
    - Sync with external calendars
    - Booking calendar

11. **integration.spec.ts** - Third-party integrations
    - Payment gateways
    - Calendar sync
    - External services

#### Advanced Features (5 files - NEW)
12. **dashboard-owner.spec.ts** - Owner dashboard
    - Overview statistics
    - Property management (create, edit, delete)
    - Booking management
    - Revenue tracking
    - Settings

13. **messaging.spec.ts** - Messaging system
    - Conversation list
    - Send/receive messages
    - Real-time updates
    - Message notifications
    - Unread indicators
    - File attachments

14. **profile-management.spec.ts** - User profile
    - View profile
    - Edit personal information
    - Upload avatar
    - Change password
    - Update email/phone
    - Notification preferences
    - Language/currency settings
    - Account deletion

15. **reviews-ratings.spec.ts** - Reviews and ratings
    - View reviews
    - Submit reviews
    - Rating system
    - Filter/sort reviews
    - Owner responses
    - Report inappropriate reviews
    - Edit/delete own reviews

16. **advanced-features.spec.ts** - Additional features
    - Saved searches
    - Property comparison
    - Referral program
    - Loyalty rewards
    - Calendar sync
    - Payment methods
    - Stripe integration
    - Help/FAQ

#### Quality Assurance (10 files)
17. **accessibility.spec.ts** - WCAG 2.1 Level AA compliance
18. **axe-accessibility.spec.ts** - Automated accessibility testing
19. **a11y.spec.ts** - Additional accessibility checks
20. **security-audit.spec.ts** - Security vulnerability testing
21. **smoke.spec.ts** - Critical path smoke tests
22. **main-flows.spec.ts** - Primary user journeys
23. **offline.spec.ts** - PWA offline functionality
24. **visual.spec.ts** - Visual regression testing
25. **localization.spec.ts** - Multi-language support
26. **profile-verification.spec.ts** - Identity verification

## 🚀 Running Tests

### Prerequisites
- Node.js 18+
- PHP 8.2+
- MySQL/SQLite
- Composer

### Local Development

#### 1. Install Dependencies
```bash
# Frontend
cd frontend
npm install
npx playwright install chromium

# Backend
cd backend
composer install
```

#### 2. Setup Environment
```bash
# Backend
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed --class=E2ESeeder

# Start backend server
php artisan serve
```

#### 3. Run Tests
```bash
cd frontend

# Run all E2E tests
npm run e2e

# Run specific test file
npx playwright test tests/e2e/auth.spec.ts

# Run in headed mode (see browser)
npm run e2e:headed

# Run in debug mode
npx playwright test --debug

# Run specific browser
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit

# Run with UI mode
npx playwright test --ui
```

### CI/CD Environment

Tests run automatically on:
- Every push to `master`, `main`, or `develop`
- Every pull request
- Manual workflow dispatch

The workflow:
1. Sets up MySQL and Redis services
2. Installs backend dependencies
3. Runs migrations and seeds E2E data
4. Starts Laravel backend server
5. Installs frontend dependencies
6. Installs Playwright browsers
7. Runs all E2E tests
8. Uploads test reports as artifacts
9. Comments results on pull requests

## 📝 Test Data

### E2E Seeder
The `E2ESeeder` creates deterministic test data:

```php
// Users
- test@example.com (password: password123)
- owner@renthub.com (password: password)
- admin@renthub.com (password: password)

// Properties
- E2E Test Property (ID: varies)

// Bookings
- Sample booking for test user

// Invoices
- Sample invoice for test booking
```

## 🔧 Maintenance

### Adding New Tests

1. **Create test file** in `frontend/tests/e2e/`
2. **Import helpers** from `./helpers`
3. **Follow naming convention**: `feature-name.spec.ts`
4. **Use describe blocks** for grouping related tests
5. **Add setup/teardown** in `beforeEach`/`afterEach`
6. **Mock API calls** when appropriate
7. **Use data-testid** attributes for reliable selectors

Example:
```typescript
import { test, expect } from '@playwright/test';
import { login, waitForAppReady } from './helpers';

test.describe('Feature Name', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should do something', async ({ page }) => {
    await page.goto('/feature');
    await waitForAppReady(page);
    
    // Test logic
    await expect(page.locator('h1')).toBeVisible();
  });
});
```

### Updating Existing Tests

1. Identify failing test
2. Review error message and screenshot
3. Update selectors or logic
4. Verify fix locally
5. Commit changes

### Best Practices

- ✅ Use `data-testid` attributes for stable selectors
- ✅ Wait for elements before interacting
- ✅ Use helper functions for common actions
- ✅ Mock external services
- ✅ Clean up test data
- ✅ Keep tests independent
- ✅ Use meaningful test names
- ❌ Don't use brittle selectors (classes, complex CSS)
- ❌ Don't hardcode delays
- ❌ Don't test third-party functionality
- ❌ Don't make tests depend on each other

## 📊 Test Reports

### Local Reports
After running tests locally:
```bash
npx playwright show-report
```

### CI Reports
- HTML report: Available as artifact in GitHub Actions
- JSON report: Used for PR comments
- Traces: Captured on first retry
- Screenshots: Captured on failure

## 🐛 Debugging Failed Tests

### Locally
```bash
# Run with headed browser
npm run e2e:headed

# Run with debug mode
npx playwright test --debug tests/e2e/failing-test.spec.ts

# Generate trace
npx playwright test --trace on
npx playwright show-trace trace.zip
```

### In CI
1. Download artifacts from GitHub Actions run
2. Extract `playwright-report` artifact
3. Open `index.html` in browser
4. Review screenshots and traces

## 🔒 Security Testing

The `security-audit.spec.ts` file includes tests for:
- XSS prevention
- CSRF protection
- SQL injection prevention
- Authentication bypass attempts
- Authorization checks
- Sensitive data exposure

## ♿ Accessibility Testing

Multiple files ensure WCAG 2.1 Level AA compliance:
- Keyboard navigation
- Screen reader compatibility
- Color contrast
- Form labels
- ARIA attributes
- Focus management

## 🌍 Localization Testing

The `localization.spec.ts` file verifies:
- Multiple language support
- RTL layouts (if applicable)
- Date/time formatting
- Currency formatting
- Translated content

## 📱 PWA Testing

The `offline.spec.ts` file tests:
- Service worker registration
- Offline functionality
- Cache management
- App install prompt

## 🎨 Visual Regression

The `visual.spec.ts` file captures screenshots to detect:
- Unintended UI changes
- Layout shifts
- Styling regressions
- Cross-browser differences

## 🔄 Continuous Improvement

### Regular Tasks
- [ ] Review and update test coverage monthly
- [ ] Remove flaky tests or fix them
- [ ] Update selectors when UI changes
- [ ] Add tests for new features
- [ ] Monitor test execution time
- [ ] Keep dependencies updated

### Metrics to Track
- Test pass rate
- Test execution time
- Code coverage
- Number of flaky tests
- Time to fix failing tests

## 📚 Resources

- [Playwright Documentation](https://playwright.dev/)
- [Testing Best Practices](https://playwright.dev/docs/best-practices)
- [Debugging Guide](https://playwright.dev/docs/debug)
- [CI/CD Integration](https://playwright.dev/docs/ci)

## 🤝 Contributing

When adding new features:
1. Write E2E tests before or alongside feature development
2. Ensure tests pass locally
3. Verify tests pass in CI
4. Document any special setup requirements
5. Update this guide if needed

## 📞 Support

For issues with E2E tests:
- Check test output and screenshots
- Review this documentation
- Check GitHub Actions logs
- Ask in team chat or create an issue

---

**Last Updated**: 2025-11-13
**Total Tests**: 26 test files covering all major features
**Status**: ✅ Comprehensive coverage complete
