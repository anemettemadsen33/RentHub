# 🔍 Debugging 500 Error on Forge Production

**Error URL:** https://renthub-tbj7yxj7.on-forge.com/admin  
**Error Type:** 500 Server Error  
**Date:** November 15, 2025

---

## 🚨 Immediate Actions Required

### 1. Check Forge Logs (Most Important!)

**Via Forge Dashboard:**
1. Login to Laravel Forge: https://forge.laravel.com
2. Go to your server
3. Click on "Logs" tab
4. Check:
   - **Application Logs** (storage/logs/laravel.log)
   - **Nginx Error Log** (/var/log/nginx/error.log)
   - **PHP Error Log** (/var/log/php8.3-fpm.log)

**Via SSH:**
```bash
# Connect to Forge server
ssh forge@your-server-ip

# Check Laravel logs (last 100 lines)
tail -n 100 /home/forge/renthub-tbj7yxj7.on-forge.com/storage/logs/laravel.log

# Check for today's errors only
grep "$(date +%Y-%m-%d)" /home/forge/renthub-tbj7yxj7.on-forge.com/storage/logs/laravel.log

# Check Nginx errors
sudo tail -n 50 /var/log/nginx/renthub-tbj7yxj7.on-forge.com-error.log

# Check PHP-FPM errors
sudo tail -n 50 /var/log/php8.3-fpm.log
```

---

## 🔧 Common Causes & Solutions

### Cause 1: Missing .env Configuration

**Symptoms:** 500 error, APP_KEY not set

**Solution:**
```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Check if .env exists
ls -la .env

# Generate APP_KEY if missing
php artisan key:generate

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Cause 2: Database Connection Failed

**Symptoms:** "SQLSTATE[HY000] [2002] Connection refused"

**Check .env:**
```bash
# Verify database credentials
cat .env | grep DB_

# Should show:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=your_password
```

**Test connection:**
```bash
php artisan tinker
DB::connection()->getPdo();
# Should not throw error
```

**Fix:**
```bash
# Update .env with correct credentials from Forge dashboard
nano .env

# Then clear config
php artisan config:clear
```

### Cause 3: Storage Permissions

**Symptoms:** "The stream or file could not be opened in append mode"

**Solution:**
```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Fix permissions
sudo chown -R forge:forge storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Create storage link if missing
php artisan storage:link
```

### Cause 4: Missing Dependencies

**Symptoms:** "Class not found"

**Solution:**
```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Reinstall dependencies
composer install --optimize-autoloader --no-dev

# Dump autoload
composer dump-autoload
```

### Cause 5: Migration Issues

**Symptoms:** "Table doesn't exist"

**Solution:**
```bash
# Run migrations
php artisan migrate --force

# Check migration status
php artisan migrate:status

# If needed, rollback and re-migrate
php artisan migrate:fresh --force --seed
```

### Cause 6: Admin Middleware Not Working

**Symptoms:** 500 on /admin route specifically

**Check:**
```bash
# Verify admin routes exist
php artisan route:list | grep admin

# Check if AdminMiddleware exists
ls -la app/Http/Middleware/AdminMiddleware.php

# Verify middleware is registered
cat app/Http/Kernel.php | grep -A 5 "protected \$middlewareAliases"
```

**Fix if middleware missing:**
```bash
# Check if using role or isAdmin check
grep -r "isAdmin" app/Http/Controllers/
grep -r "role:admin" routes/
```

---

## 📋 Step-by-Step Debugging

### Step 1: Enable Debug Mode (TEMPORARILY!)

```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Edit .env
nano .env

# Change:
APP_DEBUG=false
# To:
APP_DEBUG=true

# Save and exit (Ctrl+X, Y, Enter)

# Clear config
php artisan config:clear
```

**⚠️ WARNING:** Visit https://renthub-tbj7yxj7.on-forge.com/admin again

You'll see detailed error message. **COPY THE ERROR!**

**Then IMMEDIATELY disable debug:**
```bash
nano .env
# Change back to:
APP_DEBUG=false

php artisan config:clear
```

### Step 2: Check Laravel Logs

```bash
# View real-time logs
tail -f storage/logs/laravel.log

# Then visit the URL again
# You'll see errors appear in real-time
```

### Step 3: Check Nginx Configuration

```bash
# View Nginx config
cat /etc/nginx/sites-available/renthub-tbj7yxj7.on-forge.com

# Check if root path is correct
# Should be: /home/forge/renthub-tbj7yxj7.on-forge.com/public

# Test Nginx config
sudo nginx -t

# Reload Nginx if needed
sudo service nginx reload
```

### Step 4: Check PHP-FPM

```bash
# Check PHP-FPM status
sudo service php8.3-fpm status

# Restart if needed
sudo service php8.3-fpm restart
```

---

## 🎯 Quick Diagnostic Script

Run this on the Forge server:

```bash
ssh forge@your-server-ip

# Create diagnostic script
cat > /tmp/diagnose.sh << 'EOF'
#!/bin/bash
echo "=== RentHub Diagnostic ==="
echo ""
echo "1. Directory Check:"
pwd
ls -la

echo ""
echo "2. .env exists:"
[ -f .env ] && echo "✅ Yes" || echo "❌ No"

echo ""
echo "3. APP_KEY set:"
grep "APP_KEY=" .env | grep -v "APP_KEY=$" && echo "✅ Yes" || echo "❌ No"

echo ""
echo "4. Database Config:"
grep "DB_" .env

echo ""
echo "5. Storage Permissions:"
ls -ld storage bootstrap/cache

echo ""
echo "6. PHP Version:"
php -v | head -n 1

echo ""
echo "7. Composer Installed:"
[ -d vendor ] && echo "✅ Yes ($(du -sh vendor | cut -f1))" || echo "❌ No"

echo ""
echo "8. Recent Laravel Errors:"
tail -n 20 storage/logs/laravel.log 2>/dev/null || echo "No log file"

echo ""
echo "9. Nginx Config:"
sudo nginx -t 2>&1

echo ""
echo "10. PHP-FPM Status:"
sudo service php8.3-fpm status | grep Active
EOF

chmod +x /tmp/diagnose.sh

# Run diagnostic
cd /home/forge/renthub-tbj7yxj7.on-forge.com
/tmp/diagnose.sh
```

---

## 🔍 Specific to /admin Route

Since error is on `/admin`, check:

### 1. Admin Route Configuration

```bash
# Check if route exists
php artisan route:list | grep admin

# Expected output:
# GET|HEAD  admin ........ AdminController@index
```

### 2. Admin Controller Exists

```bash
# Check controller
ls -la app/Http/Controllers/AdminController.php

# Or Admin folder
ls -la app/Http/Controllers/Admin/
```

### 3. Admin Middleware

```bash
# Check middleware
cat app/Http/Middleware/AdminMiddleware.php

# Verify registration in Kernel.php
grep "admin" app/Http/Kernel.php
```

### 4. Database Has Admin User

```bash
php artisan tinker
User::where('role', 'admin')->first();
# Should return admin user
```

---

## 📞 Get Actual Error

**Fastest way to see exact error:**

```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Watch logs in real-time
tail -f storage/logs/laravel.log

# In another terminal/browser, visit:
# https://renthub-tbj7yxj7.on-forge.com/admin

# Check the log output for exact error
```

---

## 🚀 Most Likely Issues (Priority Order)

1. **Missing APP_KEY** (30% chance)
   - Fix: `php artisan key:generate`

2. **Wrong database credentials** (25% chance)
   - Fix: Update .env with Forge database credentials

3. **Storage permissions** (20% chance)
   - Fix: `sudo chmod -R 775 storage bootstrap/cache`

4. **Missing composer dependencies** (15% chance)
   - Fix: `composer install --no-dev`

5. **Admin route/controller missing** (10% chance)
   - Fix: Deploy latest code from GitHub

---

## ✅ Quick Fix Checklist

Run these in order:

```bash
ssh forge@your-server-ip
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# 1. Fix permissions
sudo chown -R forge:forge .
sudo chmod -R 775 storage bootstrap/cache

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Ensure APP_KEY is set
php artisan key:generate --force

# 4. Test database connection
php artisan migrate:status

# 5. Check logs
tail -n 50 storage/logs/laravel.log

# 6. Restart PHP-FPM
sudo service php8.3-fpm restart
```

---

## 📊 After Getting Error Details

**Once you have the actual error message, tell me:**

1. What's the exact error message?
2. What's the stack trace?
3. Which file/line is causing it?

Then I can provide specific fix! 🎯
