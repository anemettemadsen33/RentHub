# 🚀 RentHub Deployment Fix Guide
**Updated:** November 15, 2025

## ✅ UPDATES COMPLETED

### Backend (Laravel 11)
- ✅ PHP version support: `^8.2 || ^8.3 || ^8.4` (now compatible with Forge PHP 8.4)
- ✅ Laravel Framework: Updated to `^11.46` (latest stable)
- ✅ Predis: Updated to `^2.4` (latest)
- ✅ PHPUnit: Updated to `^11.5` (latest)
- ✅ PHPStan: Updated to `^1.12` (latest)
- ✅ Larastan: Migrated from deprecated `nunomaduro/larastan` to `larastan/larastan ^2.11`

### Frontend (Next.js 15)
- ✅ Next.js: `^15.5.6` (Turbopack enabled)
- ✅ React: `^18.3.1` (stable)
- ✅ TypeScript: `^5.9.3`
- ✅ Dependencies: All packages verified compatible

---

## 🔴 CRITICAL: Database Connection Fix (Forge)

### Current Problem
```
SQLSTATE[HY000] [1045] Access denied for user 'forge'@'localhost'
```

### Solution Steps

#### 1. **Configure Database on Forge Dashboard**

Go to your Forge site: `https://forge.laravel.com/servers/YOUR_SERVER/sites/YOUR_SITE`

Navigate to: **Database** tab

Create/configure:
- Database name: `renthub_production`
- Database user: `forge` (or create new user)
- Set strong password

#### 2. **Update Environment Variables on Forge**

Navigate to: **Environment** tab

Update these variables:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub_production
DB_USERNAME=forge
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE

# App Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://renthub-tbj7yxj7.on-forge.com

# Frontend URL (Vercel)
FRONTEND_URL=https://rent-hub-beta.vercel.app
SANCTUM_STATEFUL_DOMAINS=rent-hub-beta.vercel.app

# Redis Configuration (if using Redis on Forge)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
BROADCAST_CONNECTION=reverb

# Laravel Reverb (WebSocket)
REVERB_APP_ID=renthub-prod
REVERB_APP_KEY=YOUR_REVERB_KEY
REVERB_APP_SECRET=YOUR_REVERB_SECRET
REVERB_HOST=renthub-tbj7yxj7.on-forge.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Add all API keys (Stripe, SendGrid, Twilio, etc.)
# Check backend/.env.example for full list
```

#### 3. **Run Commands on Forge**

After updating environment variables, run these commands in Forge terminal:

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Seed production data (if needed)
php artisan db:seed --class=ProductionSeeder --force

# Re-cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link

# Restart queue workers
php artisan queue:restart
```

#### 4. **Setup Queue Worker (Supervisor)**

Navigate to: **Daemons** tab → **New Daemon**

```
Command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Directory: /home/forge/renthub-tbj7yxj7.on-forge.com
User: forge
```

#### 5. **Setup Scheduled Tasks (Cron)**

Navigate to: **Scheduler** tab → Enable scheduler

Should run: `php artisan schedule:run` every minute

---

## 🌐 VERCEL FRONTEND CONFIGURATION

### Current Deployment
- URL: `https://rent-hub-beta.vercel.app`
- Framework: Next.js 15.5.6
- Node.js: 18.x or higher required

### Environment Variables to Set on Vercel

Go to: **Vercel Dashboard** → **Your Project** → **Settings** → **Environment Variables**

```bash
# API Configuration
NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com
NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1
NEXT_PUBLIC_WEBSOCKET_URL=wss://renthub-tbj7yxj7.on-forge.com

# App Configuration
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=https://rent-hub-beta.vercel.app
NEXT_PUBLIC_SITE_URL=https://rent-hub-beta.vercel.app

# Map Provider
NEXT_PUBLIC_MAP_PROVIDER=leaflet
NEXT_PUBLIC_MAP_CLUSTER_THRESHOLD=40

# Pusher/Reverb
NEXT_PUBLIC_USE_REVERB=true
NEXT_PUBLIC_REVERB_HOST=renthub-tbj7yxj7.on-forge.com
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=wss
NEXT_PUBLIC_REVERB_KEY=YOUR_REVERB_KEY

# Sentry (Optional - for error tracking)
NEXT_PUBLIC_SENTRY_DSN=YOUR_SENTRY_DSN
SENTRY_DSN=YOUR_SENTRY_DSN
```

### Vercel Build Settings

**Root Directory:** `frontend`

**Build Command:**
```bash
npm run build
```

**Output Directory:** `.next`

**Node.js Version:** 18.17.0 or higher

---

## 🔒 CORS & SANCTUM CONFIGURATION

### Backend (Laravel)

File: `backend/config/cors.php`

```php
'allowed_origins' => [
    'https://rent-hub-beta.vercel.app',
    'http://localhost:3000', // for local dev
],
```

File: `backend/config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'rent-hub-beta.vercel.app')),
```

---

## 🧪 TESTING DEPLOYMENT

### 1. Test Backend Health

```bash
curl https://renthub-tbj7yxj7.on-forge.com/api/health
```

Expected response:
```json
{
  "status": "healthy",
  "database": "ok",
  "cache": "ok",
  "redis": "ok"
}
```

### 2. Test Frontend

Visit: `https://rent-hub-beta.vercel.app`

- Homepage should load
- Check browser console for API connection errors

### 3. Test API Connection

Open browser console on frontend:
```javascript
fetch('https://renthub-tbj7yxj7.on-forge.com/api/v1/properties')
  .then(r => r.json())
  .then(console.log)
```

---

## 📊 MONITORING & LOGS

### Forge Logs
Navigate to: **Logs** tab
- View application logs
- Check for PHP errors
- Monitor queue jobs

### Vercel Logs
Navigate to: **Deployments** → **View Logs**
- Check build logs
- Runtime logs
- Function logs

---

## 🐛 COMMON ISSUES

### Issue: "CORS policy blocked"
**Solution:** Update `SANCTUM_STATEFUL_DOMAINS` and CORS settings

### Issue: "419 Page Expired"
**Solution:** 
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Queue jobs not processing
**Solution:** Restart queue daemon on Forge

### Issue: Turbopack errors on Vercel
**Solution:** Ensure Node.js version is 18.17+

---

## 📝 COMMIT CHANGES

After this update, commit and push:

```bash
git add .
git commit -m "Update dependencies: PHP 8.4 support, Laravel 11.46, Next.js 15.5.6"
git push origin master
```

This will trigger automatic deployment on both Forge and Vercel.

---

## ✅ VERIFICATION CHECKLIST

- [ ] Database credentials updated on Forge
- [ ] Environment variables set on Forge
- [ ] Migrations run successfully
- [ ] Queue daemon configured
- [ ] Scheduler enabled
- [ ] Vercel environment variables set
- [ ] Frontend builds successfully
- [ ] API connection working
- [ ] WebSocket connection working
- [ ] Authentication working
- [ ] File uploads working (storage link)

---

## 🆘 SUPPORT

If issues persist after following this guide:

1. Check Forge logs: `/home/forge/renthub-tbj7yxj7.on-forge.com/storage/logs/laravel.log`
2. Check Vercel deployment logs
3. Verify all API keys are set correctly
4. Test database connection: `php artisan tinker` → `DB::connection()->getPdo();`

---

**Last Updated:** November 15, 2025
**PHP:** 8.2/8.3/8.4
**Laravel:** 11.46+
**Next.js:** 15.5.6
