# RentHub Testing - Quick Reference

## Run Tests

### Backend (Laravel)
```bash
cd backend
php artisan test                 # All tests (40/40 ✅)
php artisan test --filter=Auth   # Specific suite
```

### Frontend Unit (Vitest)
```bash
cd frontend
npm run test                     # All unit tests
npm run test:watch              # Watch mode
```

### Frontend E2E (Playwright)
```bash
cd frontend
npx playwright test             # All E2E (13/15 ✅)
npx playwright test --headed    # With browser UI
npx playwright show-report      # View results
```

## Key Features

✅ **Auto-backend start** - Playwright launches Laravel automatically  
✅ **Fresh DB per run** - SQLite with migrations + seeds  
✅ **13/15 E2E passing** - Core flows verified  
✅ **CI/CD ready** - GitHub Actions workflow included  
✅ **Visual regression** - Opt-in with `VISUAL=1`

## Quick Fixes

**Backend won't start:**
```bash
php -v                          # Check PHP available
php -m | grep sqlite            # Verify SQLite extension
```

**Port conflict:**
```bash
# Windows
Get-Process -Name php | Stop-Process -Force

# Linux/Mac
pkill php
```

**Reset E2E environment:**
```bash
rm backend/database/e2e-test.sqlite
rm backend/storage/framework/backend-ready.lock
```

## Test Status

| Suite | Status | Pass Rate | Runtime |
|-------|--------|-----------|---------|
| Backend Feature | ✅ | 40/40 (100%) | ~3s |
| Frontend Unit | ✅ | All passing | ~1s |
| Frontend E2E | 🟡 | 13/15 (87%) | ~3m |

**Known Issues:**
- Registration E2E: redirect not happening (under investigation)
- CORS test: backend unreachable late in suite (isolation issue)

See [TESTING.md](./TESTING.md) for complete documentation.
