# 🔧 FORGE BACKEND - Environment Setup Guide

**Server**: https://renthub-tbj7yxj7.on-forge.com  
**Date**: 2025-11-12

---

## 🚨 CRITICAL ISSUES FOUND

### ❌ Issue 1: API 500 Error
**Problem**: `/api/v1/properties` returns 500 Internal Server Error

**Possible Causes**:
1. Database not configured
2. Missing APP_KEY
3. Migrations not run
4. Wrong permissions on storage/
5. Missing .env file

### ❌ Issue 2: CORS Not Working
**Problem**: No CORS headers in response

**Cause**: Laravel CORS middleware not enabled or misconfigured

### ❌ Issue 3: /api Route 404
**Problem**: `/api` base route doesn't exist

---

## 📋 STEP-BY-STEP FIX GUIDE

### 🔐 Step 1: Access Forge Server

#### Option A: Via Forge Dashboard
1. Go to https://forge.laravel.com
2. Select your server
3. Click "Sites" → Select your site
4. Use the Web-based terminal

#### Option B: Via SSH
```bash
# Find your server IP in Forge Dashboard
ssh forge@YOUR_SERVER_IP

# Navigate to project
cd /home/forge/renthub-tbj7yxj7.on-forge.com
```

---

### 🔍 Step 2: Diagnostic Commands

```bash
# 1. Check if project exists
pwd
ls -la

# 2. Check .env file
cat .env

# 3. Check Laravel logs
tail -100 storage/logs/laravel.log

# 4. Check Nginx error logs
sudo tail -50 /var/log/nginx/error.log

# 5. Check PHP version
php -v

# 6. Check if database is accessible
php artisan tinker
# Then in tinker:
DB::connection()->getPdo();
exit
```

---

### 🛠️ Step 3: Fix Common Issues

#### Fix 1: Generate APP_KEY (if missing)
```bash
php artisan key:generate
```

#### Fix 2: Fix Permissions
```bash
# Storage and cache directories need write permissions
chmod -R 775 storage bootstrap/cache
chown -R forge:www-data storage bootstrap/cache
```

#### Fix 3: Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Fix 4: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

#### Fix 5: Run Migrations
```bash
# Check migration status
php artisan migrate:status

# Run migrations
php artisan migrate --force
```

#### Fix 6: Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### ⚙️ Step 4: Update .env File on Forge

Go to Forge Dashboard → Your Site → Environment

**Critical Variables to Set:**

```env
# Application
APP_NAME=RentHub
APP_ENV=production
APP_KEY=                              # IMPORTANT: Generate with php artisan key:generate
APP_DEBUG=false
APP_URL=https://renthub-tbj7yxj7.on-forge.com

# Database (Forge provides these)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge                     # Check in Forge Dashboard → Database
DB_USERNAME=forge
DB_PASSWORD=                          # Get from Forge Dashboard

# Frontend URL (for CORS)
FRONTEND_URL=https://rent-hub-beta.vercel.app

# Session & Auth
SESSION_DRIVER=database
SESSION_LIFETIME=120
SANCTUM_STATEFUL_DOMAINS=rent-hub-beta.vercel.app,*.vercel.app

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (Configure later)
MAIL_MAILER=log                       # For now, just log emails
MAIL_FROM_ADDRESS=hello@renthub.com
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error                       # Change to 'debug' temporarily to see errors

# Disable services that need configuration
GOOGLE_MAPS_ENABLED=false
STRIPE_ENABLED=false
TWILIO_ENABLED=false
```

**After updating .env:**
```bash
php artisan config:cache
```

---

### 🔄 Step 5: Deploy Latest Code

#### Option A: Via Forge Dashboard
1. Go to your site in Forge
2. Click "Deploy Now"

#### Option B: Via SSH
```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Pull latest code
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

---

### ✅ Step 6: Verify Fix

```bash
# Test API locally on server
curl http://localhost/api/v1/properties

# Check logs for errors
tail -f storage/logs/laravel.log
```

From your computer, run:
```powershell
.\test-backend-api.ps1
```

---

## 🔧 CORS Configuration Fix

### Update CORS Config

The CORS config should already be correct in the code, but let's verify on the server:

```bash
# Check current CORS config
cat config/cors.php

# If needed, update manually or redeploy
```

### Enable CORS Middleware

Check `app/Http/Kernel.php` to ensure CORS middleware is enabled:

```php
protected $middleware = [
    // ...
    \Fruitcake\Cors\HandleCors::class, // Should be here
];
```

After any changes:
```bash
php artisan config:cache
```

---

## 📊 Database Setup

### Check Database Status

```bash
# List databases
mysql -u forge -p -e "SHOW DATABASES;"

# Check tables in your database
mysql -u forge -p forge -e "SHOW TABLES;"

# Count records
mysql -u forge -p forge -e "SELECT COUNT(*) FROM properties;"
```

### Create Database (if doesn't exist)

Usually Forge creates it automatically, but if not:

```bash
mysql -u forge -p
CREATE DATABASE forge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

### Seed Sample Data (Optional)

```bash
php artisan db:seed --class=PropertySeeder
```

---

## 🎯 QUICK FIX SCRIPT

Create a file `fix-production.sh` on the server:

```bash
#!/bin/bash
echo "🔧 Fixing RentHub Production..."

# Navigate to project
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Pull latest code
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R forge:www-data storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test API
echo "Testing API..."
curl -s http://localhost/api/v1/properties | head -20

echo "✅ Fix complete!"
```

Make it executable and run:
```bash
chmod +x fix-production.sh
./fix-production.sh
```

---

## 🆘 TROUBLESHOOTING

### Problem: Still getting 500 errors

**Check Laravel logs:**
```bash
tail -100 storage/logs/laravel.log
```

**Enable debug mode temporarily:**
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Then try the API again and check the error message.

**Don't forget to disable debug after:**
```env
APP_DEBUG=false
LOG_LEVEL=error
```

### Problem: Database connection errors

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::table('users')->count();
```

**Check .env:**
```bash
cat .env | grep DB_
```

### Problem: Permission denied errors

```bash
# Fix all permissions
sudo chown -R forge:www-data /home/forge/renthub-tbj7yxj7.on-forge.com
chmod -R 775 storage bootstrap/cache
```

### Problem: Composer dependencies missing

```bash
# Reinstall everything
rm -rf vendor
composer install --no-dev --optimize-autoloader
```

---

## 📞 NEXT STEPS

1. ✅ Fix .env file on Forge
2. ✅ Run migrations
3. ✅ Fix permissions
4. ✅ Clear and rebuild caches
5. ✅ Test API endpoints
6. ✅ Check CORS headers
7. ✅ Verify frontend can connect

---

**Created**: 2025-11-12  
**Status**: 🔴 CRITICAL - Complete ASAP
