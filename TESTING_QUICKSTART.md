# 🚀 RentHub Testing - Quick Start Guide

**Updated:** November 11, 2025

---

## ⚡ Quick Commands

### Backend (Laravel)
```bash
cd backend

# Install dependencies
composer install

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=AuthenticationTest

# Static analysis
vendor/bin/phpstan analyse

# Code style check
./vendor/bin/pint --test

# Code style fix
./vendor/bin/pint

# Security audit
composer audit
```

### Frontend (Next.js)
```bash
cd frontend

# Install dependencies
npm install

# Type check
npm run type-check

# Lint
npm run lint

# Unit tests
npm test

# E2E tests
npm run e2e

# Build
npm run build

# Security audit
npm audit
```

---

## 🔧 Setup (First Time)

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Frontend Setup
```bash
cd frontend
npm install
cp .env.example .env.local
# Edit .env.local and set NEXT_PUBLIC_API_URL=http://localhost:8000
```

---

## ✅ Pre-Commit Checklist

Before committing code, run:

```bash
# Backend
cd backend
./vendor/bin/pint              # Fix code style
php artisan test               # Run tests
vendor/bin/phpstan analyse     # Static analysis

# Frontend
cd frontend
npm run type-check             # TypeScript check
npm run lint                   # ESLint
npm test                       # Unit tests
```

---

## 🐛 Debugging Failed Tests

### Backend Test Failures
```bash
# Run with verbose output
php artisan test --verbose

# Run specific test file
php artisan test tests/Feature/AuthenticationTest.php

# Run with debugging
php artisan test --filter=testUserCanLogin --stop-on-failure
```

### Frontend Test Failures
```bash
# Run specific test
npm test -- PropertyCard.test.tsx

# Run with watch mode
npm run test:watch

# E2E with browser visible
npm run e2e:headed

# E2E debug mode
npx playwright test --debug
```

---

## 📊 CI/CD Status

Check the status of your branch:
```bash
# View recent commits
git log --oneline -5

# Check which workflows will run
# Push to develop → Staging deployment
# Push to main → Production deployment
# PR to any branch → All checks
```

---

## 🎯 Common Issues & Solutions

### Issue: Composer Install Fails
```bash
# Clear cache
composer clear-cache

# Update dependencies
composer update

# Install without scripts
composer install --no-scripts
```

### Issue: npm Install Fails
```bash
# Clear cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Issue: Tests Fail Locally But Pass in CI
```bash
# Ensure same PHP version
php --version  # Should be 8.3+

# Ensure same Node version
node --version  # Should be 20.x

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue: E2E Tests Fail
```bash
# Install Playwright browsers
npx playwright install --with-deps

# Start backend server
cd backend && php artisan serve

# In another terminal, start frontend
cd frontend && npm run dev

# In another terminal, run E2E
cd frontend && npm run e2e
```

---

## 📝 Test Coverage

### Check Coverage
```bash
# Backend
php artisan test --coverage --min=80

# Frontend
npm test -- --coverage
```

### View HTML Reports
```bash
# Backend (if configured)
open backend/coverage/index.html

# Frontend
open frontend/coverage/index.html
```

---

## 🔐 Security

### Run Security Audits
```bash
# Backend
cd backend && composer audit

# Frontend
cd frontend && npm audit

# Fix frontend vulnerabilities
npm audit fix
```

---

## 🎨 Code Style

### Auto-fix Style Issues
```bash
# Backend (Laravel Pint)
cd backend && ./vendor/bin/pint

# Frontend (ESLint)
cd frontend && npm run lint -- --fix
```

---

## 📚 More Information

- Full Report: [TESTING_FIXES_REPORT.md](TESTING_FIXES_REPORT.md)
- CI/CD Workflows: `.github/workflows/`
- PHPUnit Config: `backend/phpunit.xml`
- PHPStan Config: `backend/phpstan.neon.dist`
- Playwright Config: `frontend/playwright.config.ts`
- TypeScript Config: `frontend/tsconfig.json`

---

## 💡 Tips

1. **Run tests frequently** - Don't wait until commit time
2. **Use watch mode** - For rapid development (`npm run test:watch`)
3. **Check CI early** - Push to a feature branch and check CI status
4. **Keep dependencies updated** - Run `composer update` and `npm update` regularly
5. **Read error messages** - They usually tell you exactly what's wrong

---

**Happy Testing! 🎉**
