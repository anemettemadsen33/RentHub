# 🎯 FORGE DEPLOYMENT - FINAL WORKING SCRIPT

## ✅ This is the CORRECT Deploy Script for RentHub

Use this **EXACT** script in Forge → Deployment Script:

```bash
cd /home/forge/renthub-mnnzqvzb.on-forge.com
git pull origin master

cd backend

composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan optimize:clear

php artisan config:cache

chmod -R 775 storage bootstrap/cache

sudo -S service php8.3-fpm reload
```

---

## ⚠️ IMPORTANT: What NOT to do

**DO NOT** use these commands in deploy script:
- ❌ `php artisan route:cache` - Breaks with dynamic routes
- ❌ `php artisan view:cache` - Breaks Filament components
- ❌ `composer install --no-dev` - Excludes Filament dependencies

---

## ⚙️ Configure Web Directory

**IMPORTANT**: After initial deployment, update web directory:

**In Forge UI:**
1. Go to: Sites → renthub-mnnzqvzb.on-forge.com → Meta/Settings
2. Find "Web Directory"
3. Change from: `/public`
4. Change to: `/backend/public`
5. Click "Update"

**Or edit Nginx manually:**
1. Sites → Files → Edit Nginx Configuration
2. Find: `root /home/forge/renthub-mnnzqvzb.on-forge.com/public;`
3. Change to: `root /home/forge/renthub-mnnzqvzb.on-forge.com/backend/public;`
4. Click "Update"

---

## 🔧 First Time Setup (via SSH)

After first deployment, SSH into server and run:

```bash
cd /home/forge/renthub-mnnzqvzb.on-forge.com/backend

# Generate APP_KEY
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force

# Test
curl http://localhost/api/health
```

---

## 📊 Verify Deployment

After deployment succeeds, test:

```bash
# In SSH:
curl http://localhost/api/health

# In browser:
https://renthub-mnnzqvzb.on-forge.com/api/health

# Should return:
{"status":"ok","timestamp":"2025-11-11T..."}
```

---

## 🚨 If Deployment Still Fails

SSH and run:

```bash
cd /home/forge/renthub-mnnzqvzb.on-forge.com/backend

# Nuclear option - clear everything
php artisan optimize:clear
rm -rf bootstrap/cache/*.php
composer clear-cache
composer dump-autoload

# Reinstall
composer install --optimize-autoloader

# Only cache config
php artisan config:cache

# Test
php artisan
```

---

## 📝 Why This Works

1. **No `--no-dev`**: Filament needs all dependencies
2. **No `route:cache`**: Dynamic routes break with caching
3. **No `view:cache`**: Filament components are dynamic
4. **Only `config:cache`**: Safe to cache configuration

---

## ✅ After This Works

Your backend will be accessible at:
- **API**: https://renthub-mnnzqvzb.on-forge.com/api/v1/*
- **Admin**: https://renthub-mnnzqvzb.on-forge.com/admin
- **Health**: https://renthub-mnnzqvzb.on-forge.com/api/health

Then update Vercel frontend environment variable:
```
NEXT_PUBLIC_API_URL=https://renthub-mnnzqvzb.on-forge.com
NEXT_PUBLIC_API_BASE_URL=https://renthub-mnnzqvzb.on-forge.com/api/v1
```

---

**This should work! 🎉**
