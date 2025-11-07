# 🚀 RentHub Backend - Forge Deployment Summary

## ✅ Deployment Files Created

All necessary files for Forge deployment have been created and pushed to GitHub:

### 1. **forge-deploy.sh** - Automated Deployment Script
- Handles git pull, composer install, migrations, cache clearing
- Optimizes application for production
- Manages maintenance mode
- Restarts queue workers

### 2. **.env.forge** - Production Environment Template
- Pre-configured for Forge server
- Frontend URL: `https://rent-hub-six.vercel.app`
- Backend URL: `https://rental-platform.private.on-forge.com`
- Production optimizations enabled
- CORS and Sanctum configured

### 3. **DEPLOYMENT.md** - Complete Deployment Guide
- Step-by-step instructions for Forge setup
- Database configuration
- SSL setup
- Queue workers configuration
- Troubleshooting guide

### 4. **Updated CORS Configuration**
- Explicitly allows Vercel frontend
- Configured for *.vercel.app domains
- Configured for *.on-forge.com domains

---

## 📋 Next Steps - Forge Deployment

### Step 1: Access Forge Dashboard
1. Log in to Laravel Forge
2. Navigate to your server (178.128.135.24)

### Step 2: Create New Site
In Forge Dashboard:
- **Root Domain**: `rental-platform.private.on-forge.com`
- **Project Type**: Laravel
- **Web Directory**: `/public`
- **PHP Version**: 8.2+

### Step 3: Install Repository
**Apps → Git Repository:**
```
Repository: anemettemadsen33/RentHub
Branch: master
```
✅ Check: "Install Composer Dependencies"

### Step 4: Set Environment Variables
**Apps → Environment:**

Copy from `backend/.env.forge` and update:
```env
APP_KEY=                    # Will generate via artisan
DB_PASSWORD=                # From Forge database settings
MAIL_HOST=                  # Your SMTP settings
MAIL_USERNAME=              # Your SMTP settings
MAIL_PASSWORD=              # Your SMTP settings
```

### Step 5: Update Deployment Script
**Apps → Deploy Script:**
```bash
cd /home/forge/rental-platform.private.on-forge.com
bash backend/forge-deploy.sh
```

### Step 6: SSH Initial Setup
```bash
ssh forge@178.128.135.24

cd /home/forge/rental-platform.private.on-forge.com/backend

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Configure Queue Worker
**Queue → New Worker:**
- Connection: `redis`
- Queue: `default`
- Processes: `1`
- Max Tries: `3`
- Sleep: `3`
- Timeout: `60`

### Step 8: Enable SSL
**SSL → LetsEncrypt:**
- Domain: `rental-platform.private.on-forge.com`
- Click "Obtain Certificate"

### Step 9: Deploy
Click "Deploy Now" button in Forge Dashboard

---

## 🔗 Important URLs

| Service | URL |
|---------|-----|
| **Backend API** | https://rental-platform.private.on-forge.com |
| **Frontend** | https://rent-hub-six.vercel.app |
| **Server IP** | 178.128.135.24 |
| **GitHub Repo** | https://github.com/anemettemadsen33/RentHub |

---

## 🧪 Test Endpoints After Deployment

```bash
# Health check
curl https://rental-platform.private.on-forge.com/api/health

# API root
curl https://rental-platform.private.on-forge.com/api

# Check CORS (from browser console on Vercel frontend)
fetch('https://rental-platform.private.on-forge.com/api/health', {
  credentials: 'include'
}).then(r => r.json()).then(console.log)
```

---

## ⚙️ Configuration Summary

### CORS Configuration ✅
```php
'allowed_origins' => [
    'https://rent-hub-six.vercel.app',
    // Localhost for development
    'http://localhost:3000',
    'http://127.0.0.1:3000',
],

'allowed_origins_patterns' => [
    '#^https?://([\w-]+\.)?vercel\.app$#i',
    '#^https?://([\w-]+\.)?on-forge\.com$#i',
],

'supports_credentials' => true,
```

### Sanctum Configuration ✅
```env
SANCTUM_STATEFUL_DOMAINS=rent-hub-six.vercel.app,rental-platform.private.on-forge.com
```

### Production Settings ✅
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

FRONTEND_URL=https://rent-hub-six.vercel.app
```

---

## 📊 Database Configuration

Forge automatically creates:
- **Database**: `forge`
- **User**: `forge`
- **Password**: Available in Forge Dashboard

Update `.env` with these credentials.

---

## 🔐 Security Checklist

- ✅ APP_DEBUG=false
- ✅ APP_KEY will be generated
- ✅ SSL certificate to be installed
- ✅ CORS properly configured
- ✅ Sanctum domains configured
- ✅ Production error logging
- ✅ Redis for cache/sessions
- ✅ Queue workers enabled

---

## 📝 Common Commands

```bash
# SSH to server
ssh forge@178.128.135.24

# Navigate to project
cd /home/forge/rental-platform.private.on-forge.com/backend

# View logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart queue
php artisan queue:restart

# Run migrations
php artisan migrate --force
```

---

## 🆘 Troubleshooting

### 500 Error
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### CORS Issues
- Verify `.env` has correct FRONTEND_URL
- Check `config/cors.php` is cached: `php artisan config:cache`
- Verify Vercel frontend is using correct API URL

### Queue Not Processing
```bash
# Check queue worker status in Forge
php artisan queue:restart
```

### Database Connection
- Verify credentials in `.env`
- Check database exists in Forge
- Test connection: `php artisan migrate:status`

---

## 📞 Support

- **Forge Docs**: https://forge.laravel.com/docs
- **Laravel Docs**: https://laravel.com/docs
- **Repository**: https://github.com/anemettemadsen33/RentHub

---

## ✨ Deployment Status

🎉 **Ready for deployment!**

All configuration files have been created and pushed to GitHub. Follow the steps above to complete the Forge deployment.

**Last Updated**: 2025-11-07
**Version**: Production v1.0
**Repository Branch**: master

---

## 🔄 Auto-Deploy (Optional)

Enable Quick Deploy in Forge to automatically deploy when pushing to master:

**Apps → Quick Deploy → Enable**

This will trigger deployment automatically on every git push to master branch.
