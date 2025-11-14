# RentHub - Testing & Quality Assurance Guide

## Overview

This guide provides comprehensive instructions for running tests, quality checks, and maintaining code quality in the RentHub project.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Frontend Testing](#frontend-testing)
3. [Backend Testing](#backend-testing)
4. [E2E Testing](#e2e-testing)
5. [API Health Monitoring](#api-health-monitoring)
6. [CI/CD Pipeline](#cicd-pipeline)
7. [Code Quality Tools](#code-quality-tools)
8. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Developer Checklist

Before committing code, run through this checklist:

#### Frontend Checks
- [ ] `cd frontend && npm run lint` - ESLint passes
- [ ] `cd frontend && npm run type-check` - TypeScript compiles
- [ ] `cd frontend && npm test` - Unit tests pass
- [ ] `cd frontend && npm run build` - Build succeeds
- [ ] `cd frontend && npm run e2e` - E2E tests pass (optional)

#### Backend Checks
- [ ] `cd backend && composer validate` - composer.json is valid
- [ ] `cd backend && ./vendor/bin/pint --test` - Code style is correct
- [ ] `cd backend && ./vendor/bin/phpstan analyse` - Static analysis passes
- [ ] `cd backend && php artisan test` - All tests pass
- [ ] `cd backend && php artisan migrate:fresh --seed` - Migrations work

#### Integration Checks
- [ ] `npx tsx tools/api-health-check.ts` - API endpoints are healthy
- [ ] GitHub Actions workflow passes

---

## Frontend Testing

### Unit Tests (Vitest + Testing Library)

**Run all tests:**
```bash
cd frontend
npm test
```

**Run tests in watch mode:**
```bash
npm run test:watch
```

**Run tests with coverage:**
```bash
npm test -- --coverage
```

**Run specific test file:**
```bash
npm test -- src/components/navbar.test.tsx
```

### Component Testing

Component tests are located in:
- `frontend/__tests__/components/`
- `frontend/__tests__/contexts/`
- `frontend/src/components/**/__tests__/`

Example test structure:
```typescript
import { render, screen } from '@testing-library/react';
import { MyComponent } from './MyComponent';

describe('MyComponent', () => {
  it('renders correctly', () => {
    render(<MyComponent />);
    expect(screen.getByText('Expected Text')).toBeInTheDocument();
  });
});
```

### Type Checking

**Check TypeScript types:**
```bash
cd frontend
npm run type-check
```

**Fix common TypeScript issues:**
- Missing imports
- Incorrect prop types
- `any` types (avoid when possible)

### Linting

**Run ESLint:**
```bash
cd frontend
npm run lint
```

**Auto-fix linting issues:**
```bash
npm run lint -- --fix
```

### Building

**Build for production:**
```bash
cd frontend
npm run build
```

**Start production server:**
```bash
npm run start
```

**Development server:**
```bash
npm run dev
```

---

## Backend Testing

### PHPUnit Tests

**Run all tests:**
```bash
cd backend
php artisan test
```

**Run tests in parallel:**
```bash
php artisan test --parallel
```

**Run tests with coverage:**
```bash
php artisan test --coverage
```

**Run specific test:**
```bash
php artisan test --filter=AuthenticationTest
```

**Run specific test method:**
```bash
php artisan test --filter=test_user_can_login
```

### Test Organization

- **Unit Tests**: `backend/tests/Unit/`
  - Service classes
  - Helper functions
  - Standalone logic
  
- **Feature Tests**: `backend/tests/Feature/`
  - API endpoints
  - Authentication flows
  - Database interactions
  - Integration tests

### Database Testing

**Refresh database before tests:**
```bash
php artisan migrate:fresh --env=testing
```

**Seed test database:**
```bash
php artisan db:seed --env=testing
```

### Code Quality

**Run Laravel Pint (code formatter):**
```bash
cd backend
./vendor/bin/pint
```

**Check code style without fixing:**
```bash
./vendor/bin/pint --test
```

**Run PHPStan (static analysis):**
```bash
./vendor/bin/phpstan analyse
```

**Validate composer.json:**
```bash
composer validate
```

**Check platform requirements:**
```bash
composer check-platform-reqs
```

### Laravel Artisan Commands

**List all routes:**
```bash
php artisan route:list
```

**List routes as JSON:**
```bash
php artisan route:list --json
```

**Clear caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## E2E Testing

### Playwright Tests

**Run all E2E tests:**
```bash
cd frontend
npm run e2e
```

**Run tests in headed mode (see browser):**
```bash
npm run e2e:headed
```

**Run tests in UI mode (interactive):**
```bash
npm run e2e:ui
```

**Debug specific test:**
```bash
npm run e2e:debug
```

**Run tests in specific browser:**
```bash
npm run e2e:chrome      # Chromium only
npm run e2e:firefox     # Firefox only
npm run e2e:safari      # WebKit (Safari) only
```

**Run all browsers:**
```bash
npm run e2e:all-browsers
```

**Run specific test suite:**
```bash
npm run e2e:all-pages          # All pages test
npm run e2e:auth               # Authentication tests
npm run e2e:booking            # Booking flow tests
npm run e2e:dashboard          # Dashboard tests
npm run e2e:messaging          # Messaging tests
npm run e2e:payments           # Payment tests
npm run e2e:performance        # Performance tests
```

**View test report:**
```bash
npm run e2e:report
```

**Generate test code:**
```bash
npm run e2e:codegen
```

### Writing E2E Tests

Example test structure:
```typescript
import { test, expect } from '@playwright/test';

test.describe('Feature Name', () => {
  test('should perform action', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/RentHub/);
    
    await page.click('button:has-text("Login")');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/dashboard/);
  });
});
```

---

## API Health Monitoring

### Health Check Script

The API health check script validates all critical endpoints.

**Run health check (local):**
```bash
npx tsx tools/api-health-check.ts
```

**Run health check (production):**
```bash
npx tsx tools/api-health-check.ts --env production
```

**Run with retry:**
```bash
npx tsx tools/api-health-check.ts --env production --retry
```

**Run continuous monitoring:**
```bash
npx tsx tools/api-health-check.ts --continuous --interval=30000
```

### Available Environments

- `local` - http://localhost:8000/api
- `production` - https://renthub-tbj7yxj7.on-forge.com/api
- `staging` - http://staging.renthub.local/api (if configured)

### Health Check Endpoints

The script tests:
- Health check endpoints
- Public API endpoints
- Authentication endpoints
- Protected endpoints (expect 401)

---

## CI/CD Pipeline

### GitHub Actions Workflow

⚠️ **IMPORTANT**: The quality workflow is **disabled by default** to prevent conflicts with existing Vercel and Laravel Forge deployments.

**Location**: `.github/workflows/quality.yml.disabled`

**To enable**: Rename the file to `quality.yml` (remove `.disabled` extension)

**Why disabled**: This repository uses:
- Vercel for frontend deployments
- Laravel Forge for backend deployments

Enabling GitHub Actions may interfere with these deployment processes.

**Triggers (when enabled):**
- Push to `main`, `master`, or `develop` branches
- Pull requests to these branches
- Manual workflow dispatch

**Jobs:**

1. **Frontend Lint & Type Check**
   - ESLint validation
   - TypeScript type checking

2. **Frontend Unit Tests**
   - Vitest tests
   - Coverage reporting

3. **Frontend Build**
   - Next.js build validation
   - Build artifacts upload

4. **Backend Validation**
   - composer.json validation
   - Platform requirements check

5. **Backend Code Quality**
   - Laravel Pint (code style)
   - PHPStan (static analysis)

6. **Backend Tests**
   - PHPUnit tests with MySQL
   - Coverage reporting

7. **E2E Tests** (optional)
   - Playwright tests
   - Test reports upload

8. **API Health Check** (production only)
   - Production API validation

9. **Security Audit**
   - npm audit
   - composer audit

10. **Quality Gate**
    - Overall status check
    - Fail if critical jobs fail

### Viewing CI/CD Results

1. Go to GitHub repository
2. Click **Actions** tab
3. Select workflow run
4. View job results and logs

### Downloading Artifacts

After workflow completion:
- Frontend build artifacts
- Playwright test reports
- Test results
- Coverage reports

---

## Code Quality Tools

### Frontend Tools

- **ESLint**: JavaScript/TypeScript linting
- **Prettier**: Code formatting (via ESLint plugin)
- **TypeScript**: Static type checking
- **Vitest**: Unit testing
- **Playwright**: E2E testing
- **Testing Library**: Component testing utilities

### Backend Tools

- **Laravel Pint**: Code formatting (based on PSR-12)
- **PHPStan**: Static analysis (level 5+)
- **PHPUnit**: Unit and feature testing
- **Larastan**: Laravel-specific PHPStan rules
- **Composer**: Dependency management and validation

---

## Troubleshooting

### Frontend Issues

**TypeScript errors:**
```bash
# Clear Next.js cache
rm -rf .next

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Run type check
npm run type-check
```

**Test failures:**
```bash
# Clear Vitest cache
npx vitest run --clearCache

# Run specific test in debug mode
npm test -- --reporter=verbose MyComponent.test.tsx
```

**Build failures:**
```bash
# Check environment variables
cat .env.local

# Build with verbose output
npm run build -- --debug
```

**E2E test failures:**
```bash
# Update Playwright browsers
npx playwright install --with-deps

# Run in headed mode to see what's happening
npm run e2e:headed

# Generate new test code
npm run e2e:codegen
```

### Backend Issues

**Test database issues:**
```bash
# Reset test database
php artisan migrate:fresh --env=testing

# Seed test data
php artisan db:seed --env=testing

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

**Composer issues:**
```bash
# Clear Composer cache
composer clear-cache

# Update dependencies
composer update

# Dump autoload
composer dump-autoload
```

**PHPUnit failures:**
```bash
# Run single test with verbose output
php artisan test --filter=MyTest --verbose

# Check test database configuration
cat phpunit.xml
```

**Code style issues:**
```bash
# Auto-fix with Pint
./vendor/bin/pint

# Check what would be fixed
./vendor/bin/pint --test --verbose
```

### API Health Check Issues

**Connection refused:**
- Ensure backend server is running
- Check the correct environment is selected
- Verify firewall/network settings

**401 Unauthorized (expected):**
- This is normal for protected endpoints
- Tests verify auth is working

**500 Internal Server Error:**
- Check backend logs: `php artisan pail`
- Verify database is running
- Check .env configuration

---

## Best Practices

### Writing Tests

1. **Unit Tests**
   - Test one thing at a time
   - Use descriptive test names
   - Arrange, Act, Assert pattern
   - Mock external dependencies

2. **Feature Tests**
   - Test real user scenarios
   - Use database factories
   - Clean up after tests
   - Test both success and failure cases

3. **E2E Tests**
   - Test critical user journeys
   - Use page objects for reusability
   - Add explicit waits when needed
   - Test across different viewports

### Code Quality

1. **TypeScript**
   - Avoid `any` types
   - Use strict mode
   - Define interfaces for complex objects
   - Use type guards when needed

2. **PHP**
   - Follow PSR-12 standards
   - Use type hints
   - Write docblocks
   - Use strict types

3. **General**
   - Write self-documenting code
   - Keep functions small and focused
   - Remove dead code
   - Keep tests up to date

---

## Additional Resources

- [Next.js Documentation](https://nextjs.org/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [Vitest Documentation](https://vitest.dev/)
- [Playwright Documentation](https://playwright.dev/)
- [PHPUnit Documentation](https://phpunit.de/)
- [Filament Documentation](https://filamentphp.com/docs)

---

## Getting Help

If you encounter issues:

1. Check this guide's troubleshooting section
2. Review test output and error messages
3. Check GitHub Actions logs
4. Review recent commits for breaking changes
5. Ask the team in the development channel

---

**Last Updated**: 2025-11-14  
**Version**: 1.0.0
