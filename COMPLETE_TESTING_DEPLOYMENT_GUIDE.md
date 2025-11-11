# ===================================
# RentHub - Complete Testing & Deployment Guide
# ===================================

## 📋 Table of Contents

1. [Testing Backend (Laravel)](#testing-backend)
2. [Testing Frontend (Next.js)](#testing-frontend)
3. [Pre-Deployment Checklist](#pre-deployment-checklist)
4. [Backend Deployment (Laravel Forge)](#backend-deployment)
5. [Frontend Deployment (Vercel)](#frontend-deployment)
6. [Post-Deployment Validation](#post-deployment-validation)

---

## 🧪 Testing Backend

### Run All Tests

```bash
cd backend

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/Api/PropertyApiTest.php

# Run parallel tests (faster)
php artisan test --parallel
```

### Static Analysis

```bash
# PHPStan
./vendor/bin/phpstan analyse

# Laravel Pint (code style)
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Database Tests

```bash
# Refresh database and run seeds for testing
php artisan migrate:fresh --seed --env=testing

# Run database tests
php artisan test --testsuite=Feature
```

### API Tests

```bash
# Test authentication
php artisan test tests/Feature/Api/AuthenticationApiTest.php

# Test properties
php artisan test tests/Feature/Api/PropertyApiTest.php

# Test bookings
php artisan test tests/Feature/Api/BookingApiTest.php

# Test payments
php artisan test tests/Feature/Api/PaymentApiTest.php
```

---

## 🎨 Testing Frontend

### Run All Tests

```bash
cd frontend

# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Run with coverage
npm test -- --coverage

# Run specific test file
npm test -- tests/hooks/use-properties.test.tsx
```

### E2E Tests (Playwright)

```bash
# Run E2E tests
npm run e2e

# Run in headed mode (see browser)
npm run e2e:headed

# Run specific test
npx playwright test tests/e2e/booking-flow.spec.ts

# Show test report
npx playwright show-report
```

### Type Checking

```bash
# TypeScript type check
npm run type-check

# Linting
npm run lint

# Fix linting issues
npm run lint -- --fix
```

### Build Test

```bash
# Test production build
npm run build

# Analyze bundle size
ANALYZE=true npm run build
```

---

## ✅ Pre-Deployment Checklist

### Backend Checklist

- [ ] All tests passing (`php artisan test`)
- [ ] No PHPStan errors (`./vendor/bin/phpstan analyse`)
- [ ] Code style compliant (`./vendor/bin/pint --test`)
- [ ] Environment variables documented in `.env.production`
- [ ] Database migrations ready
- [ ] Seeders configured
- [ ] Queue workers configured
- [ ] Scheduled tasks configured
- [ ] API documentation updated
- [ ] Rate limiting configured
- [ ] CORS settings configured
- [ ] SSL certificate ready
- [ ] Backup strategy configured
- [ ] Monitoring tools setup (Pulse, Telescope)

### Frontend Checklist

- [ ] All tests passing (`npm test`)
- [ ] E2E tests passing (`npm run e2e`)
- [ ] No TypeScript errors (`npm run type-check`)
- [ ] No linting errors (`npm run lint`)
- [ ] Production build successful (`npm run build`)
- [ ] Environment variables documented in `.env.production`
- [ ] API endpoints configured
- [ ] Authentication working
- [ ] Images optimized
- [ ] PWA configured
- [ ] Analytics integrated
- [ ] Error tracking setup (Sentry)
- [ ] Performance benchmarks met
- [ ] Accessibility checks passed
- [ ] SEO optimizations complete

### Security Checklist

- [ ] No hardcoded secrets
- [ ] Environment variables secured
- [ ] API keys rotated for production
- [ ] HTTPS enforced
- [ ] CSRF protection enabled
- [ ] XSS protection enabled
- [ ] SQL injection protection (Eloquent)
- [ ] Rate limiting configured
- [ ] Input validation implemented
- [ ] File upload restrictions
- [ ] Security headers configured
- [ ] Dependencies updated (no known vulnerabilities)

---

## 🚀 Backend Deployment (Laravel Forge)

### Step 1: Prepare Repository

```bash
cd backend

# Ensure latest code
git pull origin master

# Run tests
php artisan test

# Commit any changes
git add .
git commit -m "Prepare for production deployment"
git push origin master
```

### Step 2: Configure Forge

1. **Create Server** (See `FORGE_DEPLOYMENT.md`)
2. **Create Site**
3. **Install Repository**
4. **Configure Environment**

```bash
# Copy .env.production to Forge
# Update these critical values:
APP_URL=https://api.yourdomain.com
DB_PASSWORD=your-secure-password
REDIS_PASSWORD=your-redis-password
```

5. **Setup SSL Certificate**
6. **Configure Deployment Script** (use `deploy.sh`)

### Step 3: Deploy

```bash
# Via Forge Dashboard
# Click "Deploy Now"

# Or enable Quick Deploy for automatic deployment on push
```

### Step 4: Post-Deployment Tasks

```bash
# SSH into server
forge ssh

cd yourdomain.com

# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force

# Clear and cache
php artisan optimize

# Verify
php artisan about
```

---

## 🌐 Frontend Deployment (Vercel)

### Step 1: Prepare Repository

```bash
cd frontend

# Run all tests
npm test
npm run e2e
npm run type-check
npm run lint

# Test build
npm run build

# Commit changes
git add .
git commit -m "Prepare for production deployment"
git push origin master
```

### Step 2: Configure Vercel

1. **Import Project** (See `VERCEL_DEPLOYMENT.md`)
2. **Configure Environment Variables**

```bash
# Add to Vercel Dashboard -> Environment Variables
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
NEXTAUTH_SECRET=your-secret
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=your-key
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_live_...
```

3. **Configure Build Settings**

```
Build Command: npm run build
Output Directory: .next
Install Command: npm install
```

### Step 3: Deploy

```bash
# Via Vercel Dashboard
# Automatic deployment on push to master

# Or use CLI
cd frontend
vercel --prod
```

### Step 4: Configure Custom Domain

1. Add domain in Vercel Dashboard
2. Update DNS records
3. Wait for SSL certificate provisioning

---

## ✨ Post-Deployment Validation

### Backend Validation

```bash
# Test API endpoints
curl https://api.yourdomain.com/api/health
curl https://api.yourdomain.com/api/properties

# Check application status
ssh forge@server
cd yourdomain.com
php artisan about
php artisan queue:monitor

# Monitor logs
tail -f storage/logs/laravel.log

# Check database
php artisan db:show
```

### Frontend Validation

```bash
# Test homepage
curl https://yourdomain.com

# Test API connectivity
curl https://yourdomain.com/api/test

# Lighthouse audit
npx lighthouse https://yourdomain.com --view

# Check deployment
vercel ls
vercel logs
```

### Performance Testing

```bash
# Backend API performance
ab -n 1000 -c 10 https://api.yourdomain.com/api/properties

# Frontend load time
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com
```

### Monitoring Setup

**Backend:**
- Laravel Pulse: https://api.yourdomain.com/pulse
- Logs: `storage/logs/laravel.log`
- Queue monitoring: `php artisan queue:monitor`

**Frontend:**
- Vercel Analytics: Dashboard
- Sentry: https://sentry.io
- Google Analytics: Dashboard

---

## 🐛 Troubleshooting

### Common Backend Issues

**500 Error:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan optimize
```

**Queue Not Working:**
```bash
# Restart workers
php artisan queue:restart
sudo supervisorctl restart all
```

**Database Issues:**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Run migrations
php artisan migrate --force
```

### Common Frontend Issues

**Build Failures:**
```bash
# Check logs
vercel logs [deployment-url]

# Local build test
npm run build
```

**Environment Variables Missing:**
```bash
# Verify in Vercel Dashboard
# Or use CLI
vercel env ls
```

**API Connection Issues:**
```bash
# Check CORS configuration
# Verify API_URL environment variable
# Check network tab in browser dev tools
```

---

## 📊 Testing Commands Summary

### Backend
```bash
# Complete test suite
php artisan test --parallel --coverage

# Code quality
./vendor/bin/phpstan analyse
./vendor/bin/pint
```

### Frontend
```bash
# Complete test suite
npm test -- --coverage
npm run e2e
npm run type-check
npm run lint
npm run build
```

---

## 🔄 Continuous Integration

### GitHub Actions (Optional)

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: cd backend && composer install
      - name: Run Tests
        run: cd backend && php artisan test

  frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      - name: Install Dependencies
        run: cd frontend && npm install
      - name: Run Tests
        run: cd frontend && npm test
```

---

## 📝 Deployment Logs

### Keep Track

```bash
# Backend deployment log
echo "$(date): Deployed version $(git rev-parse HEAD)" >> deployment.log

# Frontend deployment
vercel ls > deployment-history.txt
```

---

## 🎯 Success Criteria

Deployment is successful when:

✅ All backend tests pass
✅ All frontend tests pass
✅ API responds correctly
✅ Frontend loads without errors
✅ Database migrations applied
✅ Queue workers running
✅ SSL certificates active
✅ Monitoring tools active
✅ Performance metrics acceptable
✅ Error tracking configured

---

## 📞 Support

If you encounter issues:

1. Check logs (backend & frontend)
2. Review this guide
3. Check Laravel Forge documentation
4. Check Vercel documentation
5. Review error tracking (Sentry)

---

**Good luck with your deployment! 🚀**
