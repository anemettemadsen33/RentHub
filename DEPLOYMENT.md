# 🚀 RentHub Deployment Guide

Complete guide for deploying RentHub to production (Laravel Forge + Vercel).

---

## 📋 Prerequisites

- **Backend**: Laravel Forge account with server
- **Frontend**: Vercel account
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis 7+
- **PHP**: 8.2 or 8.3
- **Node.js**: 20+

---

## 🔧 Backend Deployment (Laravel Forge)

### 1. Server Setup

1. **Create server** on Laravel Forge
2. **Install PHP 8.3** and required extensions
3. **Install Redis** for caching and queues
4. **Setup MySQL** database

### 2. Environment Configuration

Copy `.env.forge` to `.env` and configure:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

FRONTEND_URL=https://your-frontend.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-frontend.vercel.app
```

### 3. Deployment Script

Forge deployment script:

```bash
cd /home/forge/your-site.com
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### 4. Queue Workers

Setup queue worker on Forge:
- Command: `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`
- User: `forge`
- Processes: `1`

---

## 🎨 Frontend Deployment (Vercel)

### 1. Connect Repository

1. Go to Vercel dashboard
2. Import Git repository
3. Select `frontend` as root directory

### 2. Environment Variables

Add in Vercel settings:

```bash
NEXT_PUBLIC_API_URL=https://your-backend.com
NEXT_PUBLIC_APP_URL=https://your-frontend.vercel.app
```

### 3. Build Settings

```json
{
  "buildCommand": "npm run build",
  "outputDirectory": ".next",
  "installCommand": "npm install",
  "framework": "nextjs"
}
```

### 4. Deploy

Vercel auto-deploys on every push to `master` branch.

---

## ✅ Post-Deployment Checks

### Backend Health
```bash
curl https://your-backend.com/api/health
```

Expected response:
```json
{
  "status": "ok",
  "environment": "production",
  "overall_health": "healthy"
}
```

### Frontend Check
Visit `https://your-frontend.vercel.app` and verify:
- ✅ Homepage loads
- ✅ Navigation works
- ✅ API calls succeed
- ✅ No CORS errors

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] CORS domains configured
- [ ] SSL certificates active
- [ ] Firewall rules configured
- [ ] Queue workers running
- [ ] Backups scheduled

---

## 🐛 Troubleshooting

### CORS Issues
Check `backend/config/cors.php`:
```php
'allowed_origins' => [
    env('FRONTEND_URL'),
],
'supports_credentials' => true,
```

### 404 on API Routes
Check `.htaccess` or nginx config for proper routing.

### Queue Not Processing
Restart queue worker:
```bash
php artisan queue:restart
```

---

## 📞 Support

- **Issues**: https://github.com/anemettemadsen33/RentHub/issues
- **Docs**: See `/docs` directory

---

**Last updated:** November 14, 2025
