# RentHub QA & Testing - Quick Reference

## 🚀 Quick Start (5 Minutes)

### 1. Install Dependencies
```bash
# Frontend
cd frontend
npm install

# Backend
cd backend
composer install
```

### 2. Run Tests
```bash
# Frontend
cd frontend
npm test              # Unit tests (Vitest)
npm run e2e           # E2E tests (Playwright)

# Backend
cd backend
php artisan test      # All tests (PHPUnit)
```

### 3. Check Code Quality
```bash
# Frontend
cd frontend
npm run lint          # ESLint
npm run type-check    # TypeScript

# Backend
cd backend
./vendor/bin/pint --test     # Code style
./vendor/bin/phpstan analyse # Static analysis
```

### 4. API Health Check
```bash
npx tsx tools/api-health-check.ts --env production
```

---

## 📚 Full Documentation

| Document | Description | Path |
|----------|-------------|------|
| **Testing Guide** | Complete testing handbook | [TESTING_GUIDE.md](./TESTING_GUIDE.md) |
| **Routes & Forms** | All routes and forms reference | [ROUTES_AND_FORMS_INVENTORY.md](./ROUTES_AND_FORMS_INVENTORY.md) |
| **Audit Report** | Project analysis and findings | [COMPREHENSIVE_AUDIT_REPORT.md](./COMPREHENSIVE_AUDIT_REPORT.md) |
| **Final Summary** | Implementation summary | [FINAL_SUMMARY.md](./FINAL_SUMMARY.md) |

---

## ✅ Pre-Commit Checklist

Before pushing code:

- [ ] `npm run lint` (frontend)
- [ ] `npm run type-check` (frontend)
- [ ] `npm test` (frontend)
- [ ] `php artisan test` (backend)
- [ ] `./vendor/bin/pint --test` (backend)

---

## 🔧 Common Commands

### Frontend
```bash
npm run dev           # Start dev server
npm run build         # Build for production
npm test              # Unit tests
npm run test:watch    # Tests in watch mode
npm run e2e           # E2E tests
npm run e2e:ui        # E2E with UI
npm run lint          # Lint code
npm run type-check    # Check types
```

### Backend
```bash
php artisan serve                    # Start dev server
php artisan test                     # Run tests
php artisan test --parallel          # Parallel tests
php artisan test --coverage          # With coverage
./vendor/bin/pint                    # Fix code style
./vendor/bin/pint --test             # Check code style
./vendor/bin/phpstan analyse         # Static analysis
composer validate                    # Validate composer.json
```

### E2E Tests
```bash
npm run e2e                          # All tests
npm run e2e:headed                   # With browser
npm run e2e:ui                       # Interactive
npm run e2e:chrome                   # Chrome only
npm run e2e:firefox                  # Firefox only
npm run e2e:all-browsers             # All browsers
npm run e2e:mobile                   # Mobile viewports
```

---

## 🐛 Troubleshooting

### "vitest: not found"
```bash
cd frontend && npm install
```

### "phpunit: command not found"
```bash
cd backend && composer install
```

### TypeScript errors
```bash
cd frontend
rm -rf .next node_modules
npm install
npm run type-check
```

### Database errors (backend tests)
```bash
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --env=testing
```

---

## 🎯 Test Coverage

- **Frontend**: 81 pages, 25+ E2E specs
- **Backend**: 90+ models, 30+ feature tests
- **E2E**: Excellent coverage (all critical flows)
- **Unit**: Good (expandable)

---

## 🔗 CI/CD

⚠️ **CI/CD workflow is disabled by default** to prevent deployment conflicts.

**Location**: `.github/workflows/quality.yml.disabled`

**To enable**: Rename to `quality.yml` (remove `.disabled`)

**Why disabled**: Vercel and Laravel Forge handle deployments.

**If enabled, runs on**:
- Push to main/master/develop
- Pull requests
- Manual trigger

**View results**: https://github.com/anemettemadsen33/RentHub/actions

---

## 📞 Need Help?

1. Check [TESTING_GUIDE.md](./TESTING_GUIDE.md)
2. Review error messages carefully
3. Check GitHub Actions logs
4. Consult team documentation

---

**Created**: 2025-11-14  
**Updated**: 2025-11-14  
**Status**: ✅ Production Ready
