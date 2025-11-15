# RentHub Production Deployment Guide

**Target Date:** Monday, November 18, 2025  
**Last Updated:** November 15, 2025  
**Status:** Pre-Production Setup

---

## 📋 Quick Start Checklist

### Phase 1: Environment Setup (30 minutes)
- [ ] SSH into Laravel Forge server: `ssh forge@178.128.135.24`
- [ ] Navigate to project: `cd /home/forge/renthub-tbj7yxj7.on-forge.com`
- [ ] Run setup script: `bash backend/setup-production-env.sh`
- [ ] Add generated keys to Forge Environment Variables (see script output)
- [ ] **CRITICAL:** Rotate SendGrid API key (old one leaked in git)

### Phase 2: Database Initialization (15 minutes)
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed production data: `php artisan db:seed --class=ProductionSeeder`
- [ ] Seed sample properties: `php artisan db:seed --class=SamplePropertiesSeeder`
- [ ] Verify admin access: https://renthub-tbj7yxj7.on-forge.com/admin
- [ ] Change admin password from `Admin@123456`

### Phase 3: Frontend Configuration (10 minutes)
- [ ] Update Vercel environment variables (see PRODUCTION_SECRETS_CHECKLIST.md)
- [ ] Add `NEXT_PUBLIC_REVERB_KEY` (match backend)
- [ ] Add `NEXT_PUBLIC_SENTRY_DSN`
- [ ] Trigger redeployment: `git push origin master`

### Phase 4: Integration Testing (45 minutes)
- [ ] Test property creation via admin panel
- [ ] Test property search on frontend
- [ ] Test user registration/login
- [ ] Test real-time messaging (if enabled)
- [ ] Test payment flow (Stripe test mode)
- [ ] Test email notifications

### Phase 5: Monitoring & Observability (20 minutes)
- [ ] Configure Sentry error tracking
- [ ] Enable Forge monitoring
- [ ] Set up uptime monitoring (UptimeRobot/Pingdom)
- [ ] Test error reporting (trigger test exception)

---

## 🔐 Critical Security Actions

### Immediate (Before Monday)
1. **Rotate SendGrid API Key**
   ```bash
   # 1. Login: https://app.sendgrid.com/
   # 2. Settings → API Keys → Create API Key
   # 3. Name: "RentHub Production - Nov 2025"
   # 4. Permissions: Mail Send (Full Access)
   # 5. Copy key (shown once!)
   # 6. Add to Forge Environment: MAIL_PASSWORD=SG.new_key_here
   # 7. Revoke old key: SG.4p9fVE7... (in git history)
   ```

2. **Generate Application Keys**
   ```bash
   # On Forge server
   php artisan key:generate
   php artisan tinker --execute="\$keys = \Minishlink\WebPush\VAPID::createVapidKeys(); echo 'VAPID_PUBLIC_KEY=' . \$keys['publicKey'] . PHP_EOL; echo 'VAPID_PRIVATE_KEY=' . \$keys['privateKey'] . PHP_EOL;"
   ```

3. **Set Strong Passwords**
   - Change Redis password from default "secret"
   - Generate Meilisearch master key: `openssl rand -base64 48`
   - Generate Reverb keys: `openssl rand -base64 32` (APP_KEY), `openssl rand -base64 48` (SECRET)

---

## 🚀 Deployment Commands

### Backend (Laravel Forge)
```bash
# SSH into server
ssh forge@178.128.135.24

# Navigate to project
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Pull latest changes
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Seed production data (first time only)
php artisan db:seed --class=ProductionSeeder
php artisan db:seed --class=SamplePropertiesSeeder

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
php artisan queue:restart
php artisan reverb:restart
```

### Frontend (Vercel)
```bash
# Automatic deployment on git push
git add .
git commit -m "Production deployment"
git push origin master

# Or via Vercel CLI
vercel --prod
```

---

## 🧪 Testing Checklist

### Backend API Tests
```bash
# Run on local development first
composer test

# Or specific test suites
php artisan test --filter PropertyTest
php artisan test --filter BookingTest
php artisan test --filter AuthenticationTest
```

### Frontend Tests
```bash
cd frontend

# Unit tests
npm run test

# E2E tests (Playwright)
npm run test:e2e

# Lighthouse performance
npm run lighthouse
```

### Manual Testing Priority
1. **Property CRUD** - Create, view, edit, delete property via admin
2. **Search & Filters** - Frontend property search with filters
3. **User Registration** - Email verification flow
4. **Booking Flow** - Create booking, payment (test mode)
5. **Messaging** - Send message, real-time delivery
6. **Notifications** - Email, push, in-app notifications

---

## 📊 Performance Baselines

### Target Metrics (Lighthouse)
- Performance: ≥ 90
- Accessibility: ≥ 95
- Best Practices: ≥ 95
- SEO: ≥ 95

### API Response Times
- Property List: < 200ms
- Property Detail: < 150ms
- Search Query: < 300ms
- Booking Creation: < 500ms

### Database Optimization
```bash
# Index verification
php artisan db:show
php artisan db:table properties --show-indexes

# Query optimization
php artisan telescope:prune --hours=48
```

---

## 🔍 Monitoring Setup

### Sentry Configuration
```bash
# Backend
SENTRY_LARAVEL_DSN=https://PUBLIC_KEY@o123456.ingest.sentry.io/PROJECT_ID
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_ENVIRONMENT=production

# Frontend (Vercel)
NEXT_PUBLIC_SENTRY_DSN=https://PUBLIC_KEY@o123456.ingest.sentry.io/PROJECT_ID
NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Laravel Forge Monitoring
- Enable: Server Monitoring
- Metrics: CPU, RAM, Disk, Network
- Alerts: Email on > 80% resource usage

### Uptime Monitoring
- Service: UptimeRobot (free tier)
- URLs to monitor:
  - https://renthub.international (frontend)
  - https://renthub-tbj7yxj7.on-forge.com (backend)
  - https://renthub-tbj7yxj7.on-forge.com/api/health (health check)
- Check interval: 5 minutes
- Alert contacts: Your email

---

## 🐛 Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**2. Database Connection Failed**
```bash
# Test connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check credentials in .env
cat .env | grep DB_
```

**3. Queue Jobs Not Processing**
```bash
# Check queue worker
sudo supervisorctl status

# Restart queue
php artisan queue:restart
sudo supervisorctl restart all
```

**4. Real-time Not Working**
```bash
# Check Reverb status
ps aux | grep reverb

# Restart Reverb
php artisan reverb:restart

# Test WebSocket
wscat -c wss://renthub-tbj7yxj7.on-forge.com:8080
```

**5. Frontend Build Errors (Vercel)**
- Check Vercel deployment logs
- Verify environment variables are set
- Check Node.js version (18+)
- Clear build cache and redeploy

---

## 📧 Default Credentials

### Admin Access
- **URL:** https://renthub-tbj7yxj7.on-forge.com/admin
- **Email:** admin@renthub.com
- **Password:** Admin@123456
- **⚠️ MUST CHANGE:** Immediately after first login!

### Demo Owner (Sample Properties)
- **Email:** demo@renthub.international
- **Password:** DemoPassword2025!
- **Use:** Testing property management features

### Test Owner (Development)
- **Email:** owner@renthub.test
- **Password:** password123
- **Use:** Local development only

---

## 📞 Support Contacts

- **Laravel Forge:** forge@laravel.com
- **Vercel Support:** https://vercel.com/support
- **Stripe Support:** https://support.stripe.com/
- **SendGrid Support:** https://sendgrid.com/contact/

---

## 🎯 Post-Deployment Tasks

### Week 1
- [ ] Monitor error rates in Sentry
- [ ] Review performance metrics in Vercel Analytics
- [ ] Check email delivery rates (SendGrid)
- [ ] Verify payment processing (Stripe Dashboard)
- [ ] Collect user feedback

### Week 2
- [ ] Optimize database queries (slow query log)
- [ ] Add more sample properties (5-10 per major city)
- [ ] Implement automated backups (daily)
- [ ] Set up staging environment

### Month 1
- [ ] Security audit (dependency updates)
- [ ] Performance optimization round 2
- [ ] SEO audit & improvements
- [ ] Marketing integration (Google Ads, Facebook Pixel)

---

## ✅ Success Criteria

**Deployment is successful when:**
- [ ] All 10 priority tasks completed (see todo list)
- [ ] Frontend loads without errors
- [ ] Backend API responds with 200 status
- [ ] Admin panel accessible
- [ ] At least 5 sample properties visible
- [ ] User registration flow works
- [ ] Email notifications sent successfully
- [ ] Real-time messaging functional
- [ ] Lighthouse score ≥ 90
- [ ] Zero critical errors in Sentry (first 24h)

---

**Ready for Monday launch! 🚀**
