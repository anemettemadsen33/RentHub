# 🔧 FORGE BACKEND FIX - QUICK GUIDE

## Problem: Backend API returns 500 errors

### Root Cause:
1. ❌ DB_PASSWORD empty in .env
2. ❌ APP_URL incorrect
3. ❌ Database not configured
4. ❌ Migrations not run

---

## 🚀 SOLUTION - 3 STEPS:

### Step 1: Connect to Forge Server

**Option A - Via Forge Dashboard:**
1. Go to https://forge.laravel.com
2. Click on site: `renthub-tbj7yxj7.on-forge.com`
3. Click **"SSH"** button
4. Copy SSH command and run in terminal

**Option B - Direct SSH:**
```bash
ssh forge@renthub-tbj7yxj7.on-forge.com
```

---

### Step 2: Run Fix Script

Once connected to server:

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
chmod +x forge-complete-fix.sh
./forge-complete-fix.sh
```

**Or manual commands:**

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Create SQLite database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Update .env
echo "DB_CONNECTION=sqlite" >> .env
echo "APP_URL=https://renthub-tbj7yxj7.on-forge.com" >> .env

# Generate key & migrate
php artisan key:generate --force
php artisan migrate:fresh --force --seed

# Clear & cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Permissions
chmod -R 755 storage bootstrap/cache
```

---

### Step 3: Update Forge Deployment Script

In Forge Dashboard:

1. Go to **Deployments** → **Deploy Script**
2. Replace with:

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
git pull origin main

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

php artisan config:clear
php artisan cache:clear
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 755 storage bootstrap/cache
```

3. Click **Save**
4. Click **Deploy Now**

---

## ✅ VERIFICATION:

Test these URLs in browser:

### 1. Health Check:
```
https://renthub-tbj7yxj7.on-forge.com/api/health
```
**Expected**: `{"status":"ok"}`

### 2. Properties API:
```
https://renthub-tbj7yxj7.on-forge.com/api/v1/properties
```
**Expected**: JSON with properties list

### 3. API Docs:
```
https://renthub-tbj7yxj7.on-forge.com/api/documentation
```
**Expected**: Swagger UI

---

## 🐛 TROUBLESHOOTING:

### If still 500 errors:

**Check Laravel logs:**
```bash
ssh forge@renthub-tbj7yxj7.on-forge.com
cd /home/forge/renthub-tbj7yxj7.on-forge.com
tail -50 storage/logs/laravel.log
```

**Common issues:**

1. **Permission denied**:
```bash
sudo chown -R forge:forge /home/forge/renthub-tbj7yxj7.on-forge.com
chmod -R 755 storage bootstrap/cache
```

2. **Database locked**:
```bash
php artisan cache:clear
php artisan config:clear
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --force
```

3. **Key not generated**:
```bash
php artisan key:generate --force
php artisan config:cache
```

---

## 📊 AFTER FIX:

Once backend is working:

### 1. Update Frontend API URL

In Vercel:
1. Go to **Settings** → **Environment Variables**
2. Verify:
   - `NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com/api`
   - `NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1`
3. **Redeploy** if needed

### 2. Re-enable Frontend Pages

```bash
cd C:\laragon\www\RentHub\frontend\src\app

# Re-enable properties
Move-Item "_properties.disabled" "properties"

# Re-enable bookings
Move-Item "_bookings.disabled" "bookings"

# Commit & push
git add -A
git commit -m "feat: re-enable pages after backend fix"
git push origin master
```

---

## 🎯 QUICK SUMMARY:

**3-Minute Fix:**

1. SSH to Forge: `ssh forge@renthub-tbj7yxj7.on-forge.com`
2. Run commands:
```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
touch database/database.sqlite
php artisan migrate:fresh --force --seed
php artisan config:cache
```
3. Test: https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

**Done!** ✅

---

## 📞 Need Help?

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Force fresh start:
```bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan cache:clear
php artisan config:cache
```

---

**Status**: 🟡 Waiting for SSH access to Forge  
**ETA**: 5 minutes after SSH access  
**Result**: Backend will be 100% functional ✅
