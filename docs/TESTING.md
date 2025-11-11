# Testing Infrastructure Documentation

## Overview
Complete testing setup for RentHub with backend and frontend test coverage, E2E automation, and CI/CD integration.

## Test Suites

### Backend Tests (Laravel/PHPUnit)
**Location:** `backend/tests/Feature/`
**Status:** ✅ 40/40 passing

**Coverage:**
- Authentication (login, registration, logout)
- API endpoints (health, metrics, languages, currencies)
- Authorization & permissions
- Database operations

**Run commands:**
```bash
cd backend
php artisan test                    # All tests
php artisan test --filter=AuthTest  # Specific test
```

### Frontend Unit Tests (Vitest)
**Location:** `frontend/tests/unit/`
**Status:** ✅ All passing

**Coverage:**
- Utility functions (map-provider, logger, etc.)
- Component logic
- Service layer functions

**Run commands:**
```bash
cd frontend
npm run test              # All unit tests
npm run test:watch        # Watch mode
npm run test:coverage     # With coverage report
```

### Frontend E2E Tests (Playwright)
**Location:** `frontend/tests/e2e/`
**Status:** ✅ 13/15 passing (87% pass rate)

**Test files:**
- `integration.spec.ts` - Core user flows (auth, homepage, API integration)
- `visual.spec.ts` - Visual regression (opt-in with VISUAL=1)
- `main-flows.spec.ts` - Property search, comparison, booking flows
- `smoke.spec.ts` - Critical path smoke tests
- `offline.spec.ts` - Offline functionality

**Run commands:**
```bash
cd frontend
npx playwright test                           # All E2E tests
npx playwright test --project=chromium        # Chromium only
npx playwright test integration.spec.ts       # Specific file
npx playwright test --headed                  # With browser UI
npx playwright show-report                    # View HTML report
```

**Visual snapshots:**
```bash
VISUAL=1 npx playwright test visual.spec.ts   # Create/update baselines
```

## Backend Auto-Start for E2E

### Architecture
Playwright automatically starts the Laravel backend before running E2E tests using a multi-server configuration.

### Components

**1. Backend Launcher Script** (`scripts/start-backend-for-e2e.js`)
- Auto-detects PHP binary (Laragon-aware)
- Creates fresh SQLite database (e2e-test.sqlite)
- Runs migrations with seeding
- Starts `php artisan serve` on port 8000
- Waits for health endpoint before confirming ready
- Creates ready marker file for detection

**2. Playwright Wrapper** (`scripts/playwright-start-backend.js`)
- Launches backend launcher in detached mode
- Waits for ready marker + health check
- Exits after confirmation (backend continues running)

**3. Playwright Config** (`frontend/playwright.config.ts`)
```typescript
webServer: [
  {
    command: 'npm run dev',           // Frontend (Next.js)
    url: 'http://localhost:3000',
    timeout: 120_000,
  },
  {
    command: 'node ../scripts/playwright-start-backend.js',  // Backend
    port: 8000,
    timeout: 180_000,  // Extended for migrations
  },
]
```

### Environment Variables
Backend E2E environment:
- `APP_ENV=testing`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=backend/database/e2e-test.sqlite`
- `CACHE_DRIVER=array`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=array`

### Seeded Data
- Admin user: admin@renthub.com / Admin@123456
- Languages (en, es, fr, etc.)
- Currencies (USD, EUR, GBP, etc.)
- Default settings

## CI/CD Integration

### GitHub Actions Workflow
**Location:** `.github/workflows/e2e.yml`

**Triggers:**
- Push to main/master
- Pull requests

**Jobs:**
1. **E2E Tests**
   - Sets up Node.js 20 & PHP 8.2
   - Installs dependencies (backend composer, frontend npm)
   - Installs Playwright browsers
   - Runs Chromium E2E tests
   - Uploads HTML report as artifact

2. **Visual Regression** (PR only)
   - Runs with VISUAL=1
   - Compares against baseline snapshots
   - Fails if visual differences detected

**View results:**
- GitHub Actions tab → Workflow run → Artifacts → playwright-report

## Key Fixes Applied

### 1. Backend Auto-Start
✅ SQLite file-based DB (not :memory:) for persistence
✅ Detached process model for Playwright compatibility  
✅ Ready marker + health polling for reliable detection
✅ Extended timeouts for migration completion

### 2. Frontend Token Storage
✅ `authService.register()` now stores token in localStorage (matching login)
✅ E2E tests verify both redirect AND token presence

### 3. Test Stability
✅ Hydration fallback: waits for `nav, header, main` if marker hidden
✅ Overlay dismissal utilities
✅ Attribute selectors over brittle role selectors
✅ Session cleanup between tests

### 4. Visual Regression
✅ Gated behind VISUAL=1 to avoid failing without baselines
✅ Network idle + animation disable for consistent snapshots
✅ Separate CI job for visual checks on PRs

## Current Test Results

### Backend Feature Tests
```
✅ 40 tests passing
⏱️ Runtime: ~2-3 seconds
```

### Frontend E2E (Chromium)
```
✅ 13 passing
❌ 2 failing (investigation needed)
⏱️ Runtime: ~3 minutes
```

**Passing tests:**
- Homepage loads and displays content
- Navigation works
- Login/logout flows
- Form validation
- API health endpoint accessible
- Languages endpoint returns data
- Error handling
- Accessibility checks
- Session persistence

**Known issues:**
1. **Registration redirect** - Stays on page (possible validation error from backend)
2. **CORS test late in suite** - Backend unreachable (test isolation/cleanup issue)

## Next Steps

### Short-term
1. Investigate registration E2E failure via Playwright trace
2. Improve test isolation (ensure backend persists across all tests)
3. Add more API integration tests
4. Create visual snapshot baselines

### Long-term
1. Increase E2E coverage for property booking flows
2. Add performance testing (Lighthouse CI)
3. Implement contract testing (Pact)
4. Mobile E2E tests (iOS Safari, Android Chrome)
5. Load testing (Artillery/k6)

## Running Tests Locally

### Prerequisites
- PHP 8.2+ with SQLite extension
- Node.js 20+
- Composer
- Laragon (optional, auto-detected)

### Full Test Suite
```bash
# Backend tests
cd backend && php artisan test

# Frontend unit tests
cd frontend && npm run test

# Frontend E2E tests (auto-starts backend)
cd frontend && npx playwright test

# All tests
npm run test:all  # (if package.json script exists)
```

### Quick Smoke Test
```bash
cd frontend
npx playwright test smoke.spec.ts --headed
```

## Troubleshooting

### Backend won't start for E2E
- Check PHP is in PATH: `php -v`
- Verify SQLite extension: `php -m | grep sqlite`
- Check port 8000 availability: `netstat -an | grep 8000`
- Review logs in Playwright HTML report

### E2E tests flaky
- Increase timeouts in playwright.config.ts
- Run with `--workers=1` for serial execution
- Check for race conditions in async operations

### Visual tests failing
- Regenerate baselines: `VISUAL=1 npx playwright test visual.spec.ts --update-snapshots`
- Ensure consistent viewport and disable animations
- Review diff images in test results

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────┐
│                    Playwright Test Runner                │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  ┌─────────────────┐          ┌──────────────────┐      │
│  │  Frontend       │          │  Backend         │      │
│  │  (Next.js)      │◄────────►│  (Laravel)       │      │
│  │  localhost:3000 │   API    │  localhost:8000  │      │
│  └─────────────────┘          └──────────────────┘      │
│         ▲                              ▲                 │
│         │                              │                 │
│         │                              │                 │
│  ┌──────┴──────────┐          ┌────────┴──────────┐     │
│  │  npm run dev    │          │  node script +    │     │
│  │  (webServer[0]) │          │  php artisan serve│     │
│  └─────────────────┘          │  (webServer[1])   │     │
│                                └───────────────────┘     │
│                                         │                │
│                                         ▼                │
│                                ┌─────────────────┐       │
│                                │  SQLite         │       │
│                                │  e2e-test.sqlite│       │
│                                │  (migrations +  │       │
│                                │   seed data)    │       │
│                                └─────────────────┘       │
└─────────────────────────────────────────────────────────┘
```

## Performance Metrics

### Backend Tests
- Average test time: 50-75ms per test
- Total suite: ~2-3 seconds
- Database: In-memory SQLite

### E2E Tests  
- Setup (servers start): ~20-30 seconds
- Average test: 5-15 seconds
- Total suite: ~3-4 minutes
- Browser: Chromium (headless)

## Maintenance

### Weekly
- Review failed tests in CI
- Update snapshots if UI changed intentionally
- Check for flaky tests (retry patterns)

### Monthly
- Update dependencies (Playwright, PHPUnit, Vitest)
- Review test coverage metrics
- Audit slow tests for optimization

### Quarterly
- Review test architecture
- Add tests for new features
- Refactor brittle tests
- Update documentation

---

**Last Updated:** November 8, 2025  
**Test Infrastructure Version:** 1.0  
**Maintainer:** Development Team
