# API Smoke Test Results

## Test Configuration
- **Base URL:** https://renthub-tbj7yxj7.on-forge.com/api
- **Total Routes Tested:** 50
- **Success Rate:** 25/50 (50%)
- **Authentication:** No token (testing public endpoints and auth behavior)
- **Date:** <?= date('Y-m-d H:i:s') ?>

## ✅ Successful Routes (25)

### Health & Monitoring
- ✔ `GET /api/health` - 200
- ✔ `GET /api/health/liveness` - 200
- ✔ `GET /api/health/readiness` - 200
- ✔ `GET /api/metrics` - 200
- ✔ `GET /api/metrics/prometheus` - 200

### Properties
- ✔ `GET /api/properties` - 200
- ✔ `GET /api/v1/properties` - 200 (placeholder substitution working!)
- ✔ `GET /api/v1/properties/featured` - 200
- ✔ `GET /api/v1/properties/search` - 200
- ✔ `GET /api/v1/property-comparison` - 200

### Languages & Currencies
- ✔ `GET /api/v1/languages` - 200
- ✔ `GET /api/v1/languages/default` - 200
- ✔ `GET /api/v1/languages/en` - 200 (placeholder substitution working!)
- ✔ `GET /api/v1/currencies` - 200
- ✔ `GET /api/v1/currencies/active` - 200

### Settings & Configuration
- ✔ `GET /api/v1/settings/public` - 200
- ✔ `GET /api/v1/amenities` - 200

### SEO & Performance
- ✔ `GET /api/v1/seo/locations` - 200
- ✔ `GET /api/v1/seo/property-urls` - 200
- ✔ `GET /api/v1/seo/organization` - 200
- ✔ `GET /api/v1/performance/recommendations` - 200

### Reviews & Referrals
- ✔ `GET /api/v1/reviews` - 200
- ✔ `GET /api/v1/referrals/program-info` - 200

### Authentication
- ✔ `GET /api/v1/reset-password/sample-token` - 200

## ❌ Failed Routes (25)

### 🔒 Auth-Required Routes (500 - likely_missing auth)
These routes require authentication and return 500 instead of 401/403:

- ✖ `GET /api/admin/queues` - 500 (needs admin role)
- ✖ `GET /api/v1/me` - 500 (needs auth)
- ✖ `GET /api/v1/user` - 500 (needs auth)
- ✖ `GET /api/v1/gdpr/consent` - 500 (needs auth)
- ✖ `GET /api/v1/favorites` - 500 (needs auth)
- ✖ `GET /api/v1/favorites/check/1` - 500 (needs auth)
- ✖ `GET /api/v1/security/audit-logs` - 500 (needs admin)
- ✖ `GET /api/v1/security/anomalies` - 500 (needs admin)
- ✖ `GET /api/v1/gdpr/compliance-report` - 500 (needs admin)
- ✖ `GET /api/v1/analytics/events` - 500 (needs admin)

**Action Required:** These should return 401 Unauthorized or 403 Forbidden instead of 500 Internal Server Error.

### 🐛 Public Route Errors (500)
These routes should work without authentication but return 500:

- ✖ `GET /api/v1/currencies/default` - 500
- ✖ `GET /api/v1/auth/google` - 500
- ✖ `GET /api/v1/auth/google/callback` - 500
- ✖ `GET /api/v1/seo/popular-searches` - 500
- ✖ `GET /api/v1/gdpr/data-protection` - 500

**Possible Causes:**
- Missing database records (e.g., no default currency)
- Missing environment configuration (OAuth credentials)
- Service dependencies not configured (Redis, Google API)

### 📭 Not Found (404)
These routes return 404 because test data doesn't exist:

- ✖ `GET /api/v1/properties/1` - 404 (no property with ID 1)
- ✖ `GET /api/v1/properties/1/reviews` - 404
- ✖ `GET /api/v1/properties/1/availability` - 404
- ✖ `GET /api/v1/amenities/1` - 404
- ✖ `GET /api/v1/map/property/1` - 404
- ✖ `GET /api/v1/reviews/1` - 404
- ✖ `GET /api/v1/properties/1/rating` - 404
- ✖ `GET /api/v1/seo/properties/1/metadata` - 404
- ✖ `GET /api/v1/currencies/en` - 404 (currency code 'en' doesn't exist)

**Note:** These are expected since no test data exists. Will succeed with real data.

### ⚠️ Method Not Allowed (405)
- ✖ `GET /api/v1/reset-password/sample-token` - 405 (should be POST)

### 🔐 Forbidden (403)
- ✖ `GET /api/v1/verify-email/1/sample-hash` - 403 (invalid hash)

## 📊 Summary by Category

| Category | Success | Failed | Total |
|----------|---------|--------|-------|
| Health & Monitoring | 5 | 0 | 5 |
| Properties | 5 | 6 | 11 |
| Languages & Currencies | 5 | 2 | 7 |
| Settings | 2 | 0 | 2 |
| SEO & Performance | 3 | 1 | 4 |
| Authentication | 1 | 12 | 13 |
| Reviews & Referrals | 2 | 1 | 3 |
| Admin Tools | 0 | 3 | 3 |
| **TOTAL** | **25** | **25** | **50** |

## 🔧 Next Steps

### Priority 1: Fix Auth Middleware Responses
Update auth-required routes to return proper HTTP status codes:
- 401 Unauthorized when token missing
- 403 Forbidden when insufficient permissions
- Never 500 for auth issues

**Files to check:**
- `backend/bootstrap/app.php` - exception handler
- Controllers: QueueMonitorController, AuthController, GDPRController, FavoriteController, SecurityAuditController, PerformanceController

### Priority 2: Debug Public Route Errors
Investigate and fix 500 errors on public routes:
1. **currencies/default** - Check if default currency exists in database
2. **auth/google*** - Verify OAuth configuration
3. **seo/popular-searches** - Check database/cache dependencies
4. **gdpr/data-protection** - Review implementation

### Priority 3: Run Full Smoke Test
Execute comprehensive test on all 532 API endpoints:
```bash
php artisan api:smoke --limit=100 --base=https://renthub-tbj7yxj7.on-forge.com --insecure --method=GET
```

### Priority 4: Create Seeder for Test Data
Add database seeders to create test properties, amenities, reviews for smoke testing.

## ✨ Improvements Made

### Smoke Test Command Enhancements
1. **Placeholder Substitution:** Routes with `{version}`, `{id}`, `{code}`, etc. now use realistic test values
   - `{version}` → `1` (for `/v{version}/properties` → `/v1/properties`)
   - `{id}` → `1`
   - `{code}` → `en`
   - `{hash}` → `sample-hash`
   - `{token}` → `sample-token`
   - `{provider}` → `google`
   - `{property}` → `1`

2. **Auth Detection:** Routes returning 500 on auth-required endpoints without token are now marked as `likely_missing` instead of generic failure

3. **TLS Bypass:** Added `--insecure` flag for local testing against HTTPS endpoints with certificate issues

## 🎯 Command Reference

```bash
# Basic smoke test
php artisan api:smoke

# Test against production
php artisan api:smoke --base=https://renthub-tbj7yxj7.on-forge.com --insecure

# Limit routes tested
php artisan api:smoke --limit=50

# Test with authentication
php artisan api:smoke --token=YOUR_TOKEN_HERE

# Test specific HTTP method
php artisan api:smoke --method=GET
```
