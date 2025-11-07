# PLAN DE ACȚIUNE CONCRET - RentHub
**Data**: November 7, 2025  
**Prioritate**: URGENT - Pentru go-live production  
**Responsabil**: DevOps/Backend Team

---

## 📅 TIMELINE RECOMANDAT

```
Week 1: Configuration & Setup
  ├─ Mon: Database migration to PostgreSQL
  ├─ Tue: Environment variables setup
  ├─ Wed: External services integration
  ├─ Thu: Testing & verification
  └─ Fri: Security audit

Week 2: Optimization & Monitoring
  ├─ Mon-Tue: Performance tuning
  ├─ Wed: Monitoring setup
  ├─ Thu: Load testing
  └─ Fri: Documentation & training

Week 3: Staging & Go-Live
  ├─ Mon-Tue: Staging deployment
  ├─ Wed-Thu: Pre-production testing
  └─ Fri: Production deployment

Total: 15 days (realistic estimate)
```

---

## 🎯 ACTIONABLE TASKS (Priorități)

### PHASE 1: DATABASE MIGRATION (3 zile)

#### Task 1.1: Setup PostgreSQL Instance
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 4-6 ore  
**Responsabil**: DevOps  

**Substeps**:
```bash
# 1. Install PostgreSQL 16 on server/Docker
docker run -d \
  --name renthub-postgres \
  -e POSTGRES_DB=renthub \
  -e POSTGRES_USER=renthub_user \
  -e POSTGRES_PASSWORD=***SECURE*** \
  -v postgres_data:/var/lib/postgresql/data \
  postgres:16-alpine

# 2. Create user and database
psql -U postgres -c "CREATE USER renthub_user WITH PASSWORD '***';"
psql -U postgres -c "CREATE DATABASE renthub OWNER renthub_user;"
psql -U postgres -c "ALTER USER renthub_user CREATEDB;"

# 3. Configure connection
DB_CONNECTION=pgsql
DB_HOST=localhost  # or your server
DB_PORT=5432
DB_DATABASE=renthub
DB_USERNAME=renthub_user
DB_PASSWORD=***SECURE***

# 4. Test connection
PGPASSWORD=*** psql -h localhost -U renthub_user -d renthub -c "SELECT 1;"

# 5. Verify PostgreSQL is ready
```

**Deliverable**: ✅ PostgreSQL running and accessible

---

#### Task 1.2: Migrate Data from SQLite to PostgreSQL
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 ore  
**Responsabil**: Backend Engineer  

**Substeps**:
```bash
# 1. Backup current SQLite database
cp backend/database/database.sqlite backend/database/database.sqlite.backup

# 2. Update .env configuration
# Change: DB_CONNECTION=sqlite
# To:     DB_CONNECTION=pgsql

# 3. Run fresh migrations on PostgreSQL
cd backend
php artisan migrate --force

# 4. Seed initial data (if needed)
php artisan db:seed

# 5. Verify all tables
php artisan migrate:status

# 6. Test connection
php artisan tinker
>>> DB::table('users')->count();
>>> exit;

# 7. Verify data integrity
php artisan db:integrity-check  # Custom command
```

**Checklist**:
- [ ] All migrations ran successfully
- [ ] All table structures correct
- [ ] Data imported correctly
- [ ] Foreign keys verified
- [ ] Indexes present
- [ ] Sequences initialized

**Deliverable**: ✅ PostgreSQL database fully initialized

---

#### Task 1.3: Test Database Failover
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 ore  
**Responsabil**: DevOps/QA  

**Substeps**:
```bash
# 1. Test API against PostgreSQL
curl -X GET http://localhost:8000/api/v1/properties

# 2. Test authentication
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# 3. Test search functionality
curl -X GET "http://localhost:8000/api/v1/properties/search?q=apartments"

# 4. Test complex queries
# Properties with bookings, reviews, etc.

# 5. Performance test
# Compare SQLite vs PostgreSQL response times

# 6. Monitor queries
# Enable query logging
# Check slow query logs
```

**Expected Results**:
- [ ] All API endpoints respond correctly
- [ ] Database queries return expected data
- [ ] Response times acceptable (< 500ms)
- [ ] No connection errors
- [ ] No data corruption

**Deliverable**: ✅ Database operational and tested

---

### PHASE 2: ENVIRONMENT CONFIGURATION (2 zile)

#### Task 2.1: Create Production .env File
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 1 oră  
**Responsabil**: Backend Engineer  

**Fișier**: `backend/.env.production`

```bash
# Application
APP_NAME=RentHub
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://api.yourdomain.com
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=5432
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

# Cache (Redis)
CACHE_STORE=redis
REDIS_HOST=${REDIS_HOST}
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379
CACHE_PREFIX=renthub_prod_

# Queue (Redis)
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

# Authentication
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SANCTUM_ENCRYPPTION_KEY=${SANCTUM_KEY}

# Email
MAIL_DRIVER=sendgrid
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=RentHub
SENDGRID_API_KEY=${SENDGRID_API_KEY}

# Payments
STRIPE_PUBLIC_KEY=${STRIPE_PUBLIC_KEY}
STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET}

# Social Auth
SOCIALITE_REDIRECT_URI=https://api.yourdomain.com/auth/social/callback
GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}
GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}
FACEBOOK_CLIENT_ID=${FACEBOOK_CLIENT_ID}
FACEBOOK_CLIENT_SECRET=${FACEBOOK_CLIENT_SECRET}
GITHUB_CLIENT_ID=${GITHUB_CLIENT_ID}
GITHUB_CLIENT_SECRET=${GITHUB_CLIENT_SECRET}

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=renthub-production
AWS_URL=https://s3.amazonaws.com/renthub-production

# SMS/Communication
TWILIO_SID=${TWILIO_SID}
TWILIO_TOKEN=${TWILIO_TOKEN}
TWILIO_PHONE=${TWILIO_PHONE}

# Maps
MAPBOX_PUBLIC_TOKEN=${MAPBOX_PUBLIC_TOKEN}

# Monitoring
SENTRY_LARAVEL_DSN=${SENTRY_DSN}
LOG_CHANNEL=stack
LOG_LEVEL=warning

# Security
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
TRUSTED_PROXIES=*
SECURE_HEADERS_ENABLED=true
```

**Checklist**:
- [ ] All required variables present
- [ ] No hardcoded secrets
- [ ] Using environment variables
- [ ] File permissions set correctly (640)
- [ ] Backed up safely

**Deliverable**: ✅ Production .env file created

---

#### Task 2.2: Setup Environment Variables in Deployment Platform
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 1-2 ore  
**Responsabil**: DevOps  

**Pentru Laravel Forge**:
```bash
# 1. Login to Laravel Forge dashboard
# 2. Navigate to your site
# 3. Go to Environment section
# 4. Add each variable:

STRIPE_PUBLIC_KEY=pk_live_***
STRIPE_SECRET_KEY=sk_live_***
# ... (all others)

# 5. Save and deploy
# 6. Verify with: ssh into server and check
```

**Pentru Vercel (Frontend)**:
```bash
# 1. Login to Vercel dashboard
# 2. Select RentHub project
# 3. Go to Settings → Environment Variables
# 4. Add:

NEXT_PUBLIC_API_URL=https://api.yourdomain.com
NEXT_PUBLIC_SITE_URL=https://yourdomain.com
NEXTAUTH_URL=https://yourdomain.com
NEXTAUTH_SECRET=***GENERATE_NEW***
# ... (all others)

# 5. Redeploy
```

**Checklist**:
- [ ] All secrets securely stored
- [ ] No hardcoded values
- [ ] Different values for staging/production
- [ ] Encryption enabled
- [ ] Access logs enabled

**Deliverable**: ✅ All environment variables configured

---

#### Task 2.3: Generate and Store Secure Keys
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 30 min  
**Responsabil**: Security/DevOps  

```bash
# 1. Generate APP_KEY if missing
cd backend
php artisan key:generate

# 2. Generate SANCTUM encryption key
php artisan sanctum:install

# 3. Generate JWT secret (if using JWT)
php artisan jwt:generate

# 4. Generate session encryption key
php artisan tinker
>>> Illuminate\Support\Str::random(32)
>>> exit

# 5. Generate CSRF token
# Automatically handled by Laravel

# 6. Store all keys in secure vault
# Use AWS Secrets Manager, HashiCorp Vault, or similar
```

**Security Requirements**:
- [ ] Keys stored in vault, not in git
- [ ] Keys rotated periodically
- [ ] Access logged and audited
- [ ] Multiple team members can access if needed
- [ ] Backup recovery plan documented

**Deliverable**: ✅ All security keys generated and stored

---

### PHASE 3: EXTERNAL SERVICES (3 zile)

#### Task 3.1: Setup Payment Processing (Stripe)
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 4-6 ore  
**Responsabil**: Backend Engineer + Finance  

**Substeps**:
```bash
# 1. Login to Stripe dashboard
# https://dashboard.stripe.com

# 2. Get API keys
Stripe Public Key (Publishable): pk_live_***
Stripe Secret Key: sk_live_***
Webhook Secret: whsec_***

# 3. Add to .env
STRIPE_PUBLIC_KEY=pk_live_***
STRIPE_SECRET_KEY=sk_live_***
STRIPE_WEBHOOK_SECRET=whsec_***

# 4. Configure webhooks in Stripe dashboard
# Endpoint: https://api.yourdomain.com/stripe/webhook
# Events:
#   - payment_intent.succeeded
#   - payment_intent.payment_failed
#   - invoice.payment_succeeded
#   - invoice.payment_failed
#   - charge.refunded

# 5. Test payment flow
curl -X POST http://localhost:8000/api/v1/payments/create \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "currency": "usd",
    "description": "Test booking"
  }'

# 6. Verify webhook delivery
# Check Stripe dashboard for successful deliveries

# 7. Document payment workflow
```

**Testing Checklist**:
- [ ] Successful payment
- [ ] Failed payment handling
- [ ] Refund processing
- [ ] Invoice generation
- [ ] Email notifications
- [ ] Webhook delivery
- [ ] Idempotency handling

**Deliverable**: ✅ Stripe integration tested and working

---

#### Task 3.2: Setup Email Service (SendGrid)
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 ore  
**Responsabil**: Backend Engineer  

**Substeps**:
```bash
# 1. Login to SendGrid
# https://app.sendgrid.com/

# 2. Create API key
# Settings → API Keys → Create API Key
# Scope: Mail Send

# 3. Add to .env
SENDGRID_API_KEY=SG.***

# 4. Configure mail settings
MAIL_DRIVER=sendgrid
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=RentHub

# 5. Test email sending
php artisan tinker
>>> Mail::raw('Test email', function($message) {
    $message->to('test@example.com');
    $message->subject('Test');
});
>>> exit

# 6. Verify in SendGrid dashboard
# Check Activity Feed for delivered emails

# 7. Create email templates
# Welcome email
# Password reset
# Booking confirmation
# Payment receipt
# etc.
```

**Email Templates to Create**:
- [ ] Welcome email
- [ ] Email verification
- [ ] Password reset
- [ ] Booking confirmation
- [ ] Payment receipt
- [ ] Refund notification
- [ ] Message notification
- [ ] Review request
- [ ] Account deletion

**Deliverable**: ✅ Email service configured and tested

---

#### Task 3.3: Setup Social Authentication
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 6-8 hours  
**Responsabil**: Backend Engineer  

**Google OAuth**:
```bash
# 1. Go to Google Cloud Console
# https://console.cloud.google.com/

# 2. Create project (or use existing)

# 3. Enable Google+ API
# APIs & Services → Library → Search "Google+"
# Click "Enable"

# 4. Create OAuth 2.0 Credentials
# APIs & Services → Credentials
# Create OAuth 2.0 Client ID
# Application type: Web application
# Authorized redirect URIs:
#   - https://api.yourdomain.com/auth/social/callback
#   - https://yourdomain.com/auth/callback/google

# 5. Get credentials
GOOGLE_CLIENT_ID=***.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=***

# 6. Test login flow
# Click "Sign in with Google" button
# Verify user data is captured
```

**Facebook OAuth**:
```bash
# 1. Go to Facebook Developers
# https://developers.facebook.com/

# 2. Create app
# My Apps → Create App

# 3. Add Facebook Login product

# 4. Configure redirect URIs
# Settings → Basic
# Valid OAuth Redirect URIs:
#   - https://api.yourdomain.com/auth/social/callback
#   - https://yourdomain.com/auth/callback/facebook

# 5. Get credentials
FACEBOOK_CLIENT_ID=***
FACEBOOK_CLIENT_SECRET=***

# 6. Test login flow
```

**GitHub OAuth**:
```bash
# 1. Go to GitHub Settings
# https://github.com/settings/developers

# 2. Create OAuth App
# New OAuth App

# 3. Configure
# Application name: RentHub
# Homepage URL: https://yourdomain.com
# Authorization callback URL: https://api.yourdomain.com/auth/social/callback

# 4. Get credentials
GITHUB_CLIENT_ID=***
GITHUB_CLIENT_SECRET=***

# 5. Test login flow
```

**Testing**:
- [ ] Google login works
- [ ] Facebook login works
- [ ] GitHub login works
- [ ] User data captured correctly
- [ ] Avatar imported
- [ ] Email verified
- [ ] Token refresh works

**Deliverable**: ✅ All OAuth providers configured

---

### PHASE 4: SECURITY & MONITORING (2 zile)

#### Task 4.1: Setup Monitoring & Error Tracking
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 4-6 ore  
**Responsabil**: DevOps/Backend  

**Option A: Sentry (Recommended)**
```bash
# 1. Create Sentry account
# https://sentry.io/

# 2. Create project
# New Project → Laravel

# 3. Get DSN
SENTRY_LARAVEL_DSN=https://***@sentry.io/***

# 4. Install Sentry package
composer require sentry/sentry-laravel

# 5. Publish configuration
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"

# 6. Configure
config/sentry.php:
'dsn' => env('SENTRY_LARAVEL_DSN'),
'traces_sample_rate' => 0.1,

# 7. Test error reporting
php artisan tinker
>>> throw new Exception("Test error");
>>> exit

# 8. Verify in Sentry dashboard
```

**Option B: Datadog**
```bash
# Similar setup with Datadog agent
# https://app.datadoghq.com/
```

**Key Metrics to Monitor**:
- [ ] Error rate
- [ ] Response time
- [ ] Database performance
- [ ] API latency
- [ ] Queue depth
- [ ] Memory usage
- [ ] CPU usage
- [ ] Disk usage
- [ ] Cache hit rate
- [ ] Failed logins

**Deliverable**: ✅ Error tracking and monitoring active

---

#### Task 4.2: Setup Application Logging
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 ore  
**Responsabil**: DevOps/Backend  

**Configuration**:
```bash
# backend/.env
LOG_CHANNEL=stack
LOG_LEVEL=warning

# backend/config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'sentry'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],
    'sentry' => [
        'driver' => 'sentry',
    ],
]

# Enable in .env for production
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

**Log Retention**:
```bash
# Rotate logs weekly
# Keep 14 days of logs
# Archive to S3

config/logging.php:
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => 'debug',
    'days' => 14,
    'permission' => 0664,
]
```

**Deliverable**: ✅ Logging configured and operational

---

#### Task 4.3: Setup Rate Limiting
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 hours  
**Responsabil**: Backend Engineer  

**Configuration**:
```php
// app/Http/Middleware/ThrottleRequests.php
protected $limiters = [
    'api' => 'rate_limit:api',
    'login' => 'rate_limit:login',
    'register' => 'rate_limit:register',
];

// config/rate-limiting.php
'limiters' => [
    'api' => [
        'limit' => '60:1',  // 60 requests per minute
        'response' => 'Too many requests',
    ],
    'login' => [
        'limit' => '5:1',   // 5 login attempts per minute
        'response' => 'Too many login attempts',
    ],
    'register' => [
        'limit' => '1:60',  // 1 registration per hour
        'response' => 'Too many registrations',
    ],
];
```

**Testing**:
```bash
# Test rate limiting
for i in {1..65}; do
  curl -X GET http://localhost:8000/api/v1/properties
done

# Should see 429 Too Many Requests after 60 requests
```

**Deliverable**: ✅ Rate limiting configured

---

### PHASE 5: TESTING & QA (2 zile)

#### Task 5.1: API Integration Testing
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 8-10 hours  
**Responsabil**: QA Engineer  

**Test Cases**:
```bash
# 1. Authentication
POST /api/v1/auth/login
POST /api/v1/auth/register
POST /api/v1/auth/logout
GET /api/v1/auth/user
POST /api/v1/auth/refresh

# 2. Properties
GET /api/v1/properties
GET /api/v1/properties/{id}
POST /api/v1/properties (owner only)
PUT /api/v1/properties/{id} (owner only)
DELETE /api/v1/properties/{id} (owner only)

# 3. Bookings
GET /api/v1/bookings
POST /api/v1/bookings
PUT /api/v1/bookings/{id}/cancel

# 4. Payments
POST /api/v1/payments/create
GET /api/v1/payments/{id}
POST /api/v1/payments/{id}/refund (admin only)

# 5. Search
GET /api/v1/properties/search?q=apartment
GET /api/v1/properties/filter?city=New+York&price_min=100

# 6. Error Cases
Missing authentication
Invalid parameters
Resource not found (404)
Unauthorized access (403)
```

**Automated Testing Script**:
```bash
#!/bin/bash
# tests/api/integration-test.sh

API_URL="http://localhost:8000/api/v1"
TOKEN=""

# 1. Register user
curl -X POST $API_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123"
  }'

# 2. Login
RESPONSE=$(curl -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }')
TOKEN=$(echo $RESPONSE | jq -r '.token')

# 3. Get properties
curl -X GET $API_URL/properties \
  -H "Authorization: Bearer $TOKEN"

# ... more tests
```

**Deliverable**: ✅ All critical APIs tested and working

---

#### Task 5.2: Performance Testing
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 4-6 hours  
**Responsabil**: QA/DevOps  

**Load Testing Setup**:
```bash
# Install k6 or Apache JMeter

# k6 script (load-test.js)
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '1m', target: 100 },    // Ramp-up
    { duration: '5m', target: 100 },    // Stay
    { duration: '1m', target: 0 },      // Ramp-down
  ],
};

export default function() {
  // Test properties listing
  let response = http.get('http://localhost:8000/api/v1/properties');
  check(response, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
  });
  
  sleep(1);
}

# Run test
k6 run load-test.js
```

**Performance Metrics Target**:
```
✅ API Response Time: < 500ms (avg)
✅ Throughput: > 100 req/sec
✅ Error Rate: < 0.1%
✅ P99 Latency: < 2000ms
✅ Database: < 100ms queries
```

**Deliverable**: ✅ Performance baseline established

---

#### Task 5.3: Security Testing
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 6-8 hours  
**Responsabil**: Security/QA  

**Security Test Cases**:
```bash
# 1. SQL Injection
curl -X GET "http://localhost:8000/api/v1/properties?id=1' OR '1'='1"
# Expected: Safe query or error (not exposed data)

# 2. XSS Prevention
curl -X POST http://localhost:8000/api/v1/properties \
  -d '{"title":"<script>alert(1)</script>"}'
# Expected: Script escaped or sanitized

# 3. CSRF Protection
# POST without CSRF token
# Expected: 403 or 419 error

# 4. Authentication Bypass
curl -X GET http://localhost:8000/api/v1/admin/users
# Expected: 401 Unauthorized

# 5. Rate Limiting
for i in {1..100}; do curl http://localhost:8000/api/v1/properties; done
# Expected: 429 Too Many Requests

# 6. CORS Bypass
curl -H "Origin: http://malicious.com" \
  http://localhost:8000/api/v1/properties
# Expected: CORS error or safe headers

# 7. Broken Access Control
# Login as user A, try to modify user B's data
# Expected: 403 Forbidden
```

**Automated Security Scan**:
```bash
# Using OWASP ZAP
docker run -t owasp/zap2docker-stable \
  zap-baseline.py -t http://localhost:8000

# Using npm audit
npm audit --production

# Using composer audit
composer audit
```

**Deliverable**: ✅ Security tests passed

---

### PHASE 6: DEPLOYMENT (1 day)

#### Task 6.1: Staging Deployment
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-3 hours  
**Responsabil**: DevOps  

**Steps**:
```bash
# 1. Deploy to staging environment
# (Mirror of production but with test data)

# 2. Run full test suite
npm run test              # Frontend
php artisan test          # Backend

# 3. Verify all integrations
# - Database ✓
# - Redis ✓
# - Email ✓
# - Payment gateway ✓
# - Storage ✓
# - Monitoring ✓

# 4. Performance verification
# Response times acceptable
# No memory leaks
# No database slowness

# 5. Get stakeholder sign-off
# QA approved
# Product approved
# Security approved
```

**Deliverable**: ✅ Staging environment verified

---

#### Task 6.2: Production Deployment
**Status**: ⚠️ NOT STARTED  
**Timp estimat**: 2-4 hours  
**Responsabil**: DevOps + Team Lead  

**Pre-Deployment Checklist**:
- [ ] All tests passed
- [ ] Staging verified
- [ ] Backups created
- [ ] Rollback plan documented
- [ ] Team on-call
- [ ] Maintenance window scheduled (if needed)
- [ ] Communication to users sent
- [ ] Monitoring alerts configured
- [ ] Runbook prepared

**Deployment Steps**:
```bash
# 1. Create backup
mysqldump renthub > backup-$(date +%Y%m%d-%H%M%S).sql

# 2. Deploy backend (Forge)
# Push to main branch
# Forge auto-deploys
# Or manual: bash forge-deploy.sh

# 3. Deploy frontend (Vercel)
# Push to main branch
# Vercel auto-deploys

# 4. Run migrations (if any)
ssh user@server
cd /home/forge/yourdomain.com
php artisan migrate --force

# 5. Clear caches
php artisan cache:clear
php artisan route:cache
php artisan config:cache

# 6. Verify deployment
# Check logs: tail -f storage/logs/laravel.log
# Test API: curl https://api.yourdomain.com/health
# Test frontend: https://yourdomain.com

# 7. Monitoring
# Watch error rates
# Monitor response times
# Check database performance
# Check queue depth
```

**Post-Deployment**:
- [ ] Verify all critical paths work
- [ ] Check error logs
- [ ] Monitor performance
- [ ] User feedback (if any)
- [ ] Keep team on-call for 24 hours

**Deliverable**: ✅ Live in production

---

## 📊 STATUS TRACKER

| Phase | Task | Status | Due | Owner |
|-------|------|--------|-----|-------|
| 1 | Database Migration | ⏳ | Day 1-2 | DevOps |
| 1 | Data Migration | ⏳ | Day 2-3 | Backend |
| 1 | Database Testing | ⏳ | Day 3 | QA |
| 2 | Environment Setup | ⏳ | Day 3 | Backend |
| 2 | External Env Vars | ⏳ | Day 3 | DevOps |
| 2 | Security Keys | ⏳ | Day 3 | Security |
| 3 | Stripe Setup | ⏳ | Day 4 | Backend/Finance |
| 3 | Email Setup | ⏳ | Day 4 | Backend |
| 3 | Social Auth | ⏳ | Day 4-5 | Backend |
| 4 | Monitoring | ⏳ | Day 5 | DevOps |
| 4 | Logging | ⏳ | Day 5 | DevOps |
| 4 | Rate Limiting | ⏳ | Day 5 | Backend |
| 5 | API Testing | ⏳ | Day 6 | QA |
| 5 | Performance Test | ⏳ | Day 6 | QA |
| 5 | Security Test | ⏳ | Day 6 | Security |
| 6 | Staging Deploy | ⏳ | Day 7 | DevOps |
| 6 | Production Deploy | ⏳ | Day 8 | DevOps |

---

## ✅ FINAL CHECKLIST

Before go-live, verify:

- [ ] **Database**: PostgreSQL running, all migrations passed
- [ ] **Environment**: All variables configured
- [ ] **Payments**: Stripe integrated and tested
- [ ] **Email**: SendGrid working
- [ ] **Auth**: Social login working
- [ ] **Storage**: S3 or cloud storage configured
- [ ] **Monitoring**: Sentry/Datadog active
- [ ] **Logging**: Logs flowing correctly
- [ ] **Rate Limiting**: Configured
- [ ] **Performance**: Load testing passed
- [ ] **Security**: Security testing passed
- [ ] **Testing**: All critical paths tested
- [ ] **Staging**: Verified in staging
- [ ] **Backups**: Backup plan in place
- [ ] **Runbook**: Deployment documented
- [ ] **Team**: All team members trained
- [ ] **Communication**: Users notified

---

## 📞 EMERGENCY CONTACTS

| Role | Name | Contact | Availability |
|------|------|---------|--------------|
| Tech Lead | TBD | phone | 24/7 |
| DevOps | TBD | phone | 24/7 |
| Backend | TBD | phone | 24/7 |
| Frontend | TBD | phone | 24/7 |
| CEO | TBD | phone | Business hours |

---

**Plan versioning**: v1.0  
**Last updated**: November 7, 2025  
**Status**: READY FOR EXECUTION ✅
