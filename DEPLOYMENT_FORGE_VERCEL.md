# 🚀 RentHub - Deployment Guide (Laravel Forge + Vercel)

**Date**: 2025-11-11 19:15  
**Backend**: Laravel Forge  
**Frontend**: Vercel  
**Repository**: https://github.com/anemettemadsen33/RentHub

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### GitHub Repository
- ✅ Code pushed to GitHub
- ✅ Master branch ready
- ✅ All tests passing
- ✅ Documentation complete

### Accounts Ready
- ✅ Laravel Forge account connected to GitHub
- ✅ Vercel account connected to GitHub
- ✅ Repository accessible by both services

---

## 🔧 PART 1: Backend Deployment (Laravel Forge)

### Step 1: Create Server on Forge

**Server Specifications:**
```
Provider: Choose your cloud provider (DigitalOcean, AWS, Linode, etc.)
Server Size: Minimum 2GB RAM, 1 CPU
PHP Version: 8.3
Database: MySQL 8.0
Server Type: App Server
Region: Choose closest to your users
```

**Required Services:**
- ✅ Nginx
- ✅ MySQL 8.0
- ✅ PHP 8.3
- ✅ Redis
- ✅ Node.js (for queue workers)

### Step 2: Create Site on Forge

1. **Go to Forge Dashboard** → "Sites" → "New Site"

2. **Site Configuration:**
```
Root Domain: api.renthub.com (or your domain)
Project Type: General PHP / Laravel
Web Directory: /public
PHP Version: PHP 8.3
```

### Step 3: Connect GitHub Repository

1. In Forge, go to your site → "Git Repository"
2. **Repository Settings:**
```
Provider: GitHub
Repository: anemettemadsen33/RentHub
Branch: master
Deploy Path: /backend
```

3. **Enable Quick Deploy**: ✅ (auto-deploy on push)

### Step 4: Environment Variables

Go to your site → "Environment" and update `.env`:

```env
# Application
APP_NAME=RentHub
APP_ENV=production
APP_KEY=                    # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://api.renthub.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=                # Forge will provide this

# Cache & Queue (Redis)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=              # Your SMTP credentials
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@renthub.com
MAIL_FROM_NAME="${APP_NAME}"

# Pusher (for real-time features)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# Stripe (for payments)
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=https://api.renthub.com/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URL=https://api.renthub.com/auth/facebook/callback

# Frontend URL (CORS)
FRONTEND_URL=https://renthub.vercel.app
SANCTUM_STATEFUL_DOMAINS=renthub.vercel.app,localhost:3000

# Session Configuration
SESSION_DOMAIN=.renthub.com
SESSION_SECURE_COOKIE=true
```

### Step 5: Deploy Script

Go to your site → "Deployment Script" and update:

```bash
cd /home/forge/api.renthub.com
git pull origin master

# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations (careful on production!)
# php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart PHP-FPM
sudo -S service php8.3-fpm reload

# Restart Queue Worker
php artisan queue:restart
```

### Step 6: SSL Certificate

1. Go to your site → "SSL" → "LetsEncrypt"
2. Click "Obtain Certificate"
3. Wait for SSL to activate (1-2 minutes)

### Step 7: Queue Worker

1. Go to your site → "Queue"
2. Add new queue worker:
```
Connection: redis
Queue: default
Processes: 1
Timeout: 90
Sleep: 3
Tries: 3
```

### Step 8: Scheduler

1. Go to your site → "Scheduler"
2. Enable scheduler (runs `php artisan schedule:run` every minute)

### Step 9: Database Setup

1. SSH into server (use Forge's "SSH" button)
2. Run initial migrations:
```bash
cd /home/forge/api.renthub.com/backend
php artisan migrate --force
php artisan db:seed --force
```

### Step 10: Test Backend

```bash
# Test API health
curl https://api.renthub.com/api/health

# Should return: {"status":"ok","timestamp":"..."}
```

---

## 🎨 PART 2: Frontend Deployment (Vercel)

### Step 1: Import Project to Vercel

1. Go to https://vercel.com/new
2. Click "Import Git Repository"
3. Select: `anemettemadsen33/RentHub`
4. Click "Import"

### Step 2: Configure Project

**Framework Preset**: Next.js  
**Root Directory**: `frontend`  
**Build Command**: `npm run build`  
**Output Directory**: `.next`  
**Install Command**: `npm install`

### Step 3: Environment Variables

Add these environment variables in Vercel:

```env
# Backend API
NEXT_PUBLIC_API_URL=https://api.renthub.com
NEXT_PUBLIC_API_BASE_URL=https://api.renthub.com/api/v1

# App Configuration
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=https://renthub.vercel.app

# Google Maps (if using)
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=

# Stripe (frontend key)
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=

# Pusher (real-time)
NEXT_PUBLIC_PUSHER_APP_KEY=
NEXT_PUBLIC_PUSHER_CLUSTER=
NEXT_PUBLIC_PUSHER_APP_ID=

# Feature Flags
NEXT_PUBLIC_ENABLE_PWA=true
NEXT_PUBLIC_ENABLE_ANALYTICS=true

# Node Environment
NODE_ENV=production
```

### Step 4: Deploy

1. Click "Deploy"
2. Wait for deployment (2-3 minutes)
3. Vercel will provide a URL: `https://renthub.vercel.app`

### Step 5: Custom Domain (Optional)

1. Go to Vercel project → "Settings" → "Domains"
2. Add your custom domain: `renthub.com`
3. Configure DNS records as shown by Vercel
4. Wait for DNS propagation (5-60 minutes)

### Step 6: Update Backend CORS

Update backend `.env` on Forge:
```env
FRONTEND_URL=https://renthub.vercel.app
SANCTUM_STATEFUL_DOMAINS=renthub.vercel.app
```

Then redeploy backend to apply changes.

---

## 🔗 PART 3: Connect Backend & Frontend

### Update Backend CORS Configuration

SSH into Forge server and edit:

```bash
cd /home/forge/api.renthub.com/backend
nano config/cors.php
```

Ensure it has:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [env('FRONTEND_URL')],
'supports_credentials' => true,
```

### Test Connection

1. Visit: `https://renthub.vercel.app`
2. Try to login/register
3. Check browser console for errors
4. Verify API calls are going to `https://api.renthub.com`

---

## ✅ POST-DEPLOYMENT VERIFICATION

### Backend Checks

```bash
# 1. API Health
curl https://api.renthub.com/api/health

# 2. Database Connection
curl https://api.renthub.com/api/v1/properties

# 3. CORS
curl -H "Origin: https://renthub.vercel.app" \
  https://api.renthub.com/sanctum/csrf-cookie

# 4. Queue Worker
# Check Forge dashboard → Queue → Status: Running
```

### Frontend Checks

```bash
# 1. Homepage loads
curl -I https://renthub.vercel.app

# 2. Check build logs in Vercel
# Should show: "Build completed successfully"

# 3. Test routes
- https://renthub.vercel.app/
- https://renthub.vercel.app/auth/login
- https://renthub.vercel.app/properties
```

### Integration Checks

1. **Registration Flow**
   - Open frontend
   - Register new account
   - Verify email sent (check mail logs)

2. **Authentication**
   - Login with test account
   - Check dashboard loads
   - Verify API calls work

3. **Property Browsing**
   - Visit properties page
   - Check properties load from API
   - Test search/filters

---

## 🚨 TROUBLESHOOTING

### Backend Issues

**Error: 500 Internal Server Error**
```bash
# Check Laravel logs
tail -f /home/forge/api.renthub.com/backend/storage/logs/laravel.log

# Check Nginx error log
sudo tail -f /var/log/nginx/api.renthub.com-error.log

# Check PHP-FPM log
sudo tail -f /var/log/php8.3-fpm.log
```

**Error: Database Connection Failed**
```bash
# Test database connection
mysql -u forge -p forge

# Check .env has correct credentials
cd /home/forge/api.renthub.com/backend
cat .env | grep DB_
```

**Error: Queue Worker Not Running**
```bash
# Restart queue worker via Forge dashboard
# Or manually:
php artisan queue:restart
```

### Frontend Issues

**Error: Failed to Fetch**
- Check CORS configuration on backend
- Verify `NEXT_PUBLIC_API_URL` is correct
- Check browser console for exact error

**Error: 404 on Pages**
- Rebuild frontend in Vercel
- Check route configuration
- Verify `frontend/src/app` structure

**Error: Environment Variables Not Loading**
- Redeploy in Vercel (env vars need redeploy)
- Check variable names start with `NEXT_PUBLIC_`
- Verify no typos in variable names

---

## 📊 MONITORING & MAINTENANCE

### Backend Monitoring (Forge)

1. **Server Metrics**
   - CPU usage
   - Memory usage
   - Disk space
   - MySQL queries

2. **Application Logs**
   - Laravel logs: `storage/logs/laravel.log`
   - Queue logs: Forge dashboard
   - Cron logs: Forge dashboard

### Frontend Monitoring (Vercel)

1. **Analytics**
   - Page views
   - Response times
   - Error rates

2. **Build Logs**
   - Deployment history
   - Build duration
   - Build errors

### Performance Optimization

**Backend**:
```bash
# Enable OPcache
sudo nano /etc/php/8.3/fpm/php.ini
# Set: opcache.enable=1

# Restart PHP-FPM
sudo service php8.3-fpm restart
```

**Frontend**:
- Use Vercel's built-in CDN (automatic)
- Enable image optimization (automatic)
- Monitor Core Web Vitals in Vercel dashboard

---

## 🎯 DEPLOYMENT COMMANDS SUMMARY

### Initial Backend Deploy (Forge)
```bash
# Done via Forge UI:
1. Create server
2. Create site
3. Connect GitHub repo
4. Set environment variables
5. Deploy
6. Run migrations
7. Enable SSL
8. Setup queue worker
```

### Initial Frontend Deploy (Vercel)
```bash
# Done via Vercel UI:
1. Import GitHub repo
2. Set root directory to "frontend"
3. Add environment variables
4. Deploy
5. (Optional) Add custom domain
```

### Subsequent Deploys
```bash
# Both auto-deploy on git push to master!
git add .
git commit -m "Your changes"
git push origin master

# Forge: Auto-deploys backend
# Vercel: Auto-deploys frontend
```

---

## 📝 CREDENTIALS TO SAVE

### Backend (Forge)
- Server IP: `___________________`
- SSH User: `forge`
- SSH Password/Key: `___________________`
- Database Name: `forge`
- Database User: `forge`
- Database Password: `___________________`
- Site URL: `https://api.renthub.com`

### Frontend (Vercel)
- Project URL: `https://renthub.vercel.app`
- Custom Domain: `https://renthub.com` (optional)
- Vercel Team: `___________________`

### Admin Account
- Email: `admin@renthub.com`
- Password: `Admin@123456`
- **⚠️ CHANGE IMMEDIATELY AFTER FIRST LOGIN**

---

## 🎉 SUCCESS CRITERIA

Deployment is successful when:

- ✅ Backend API responds at `https://api.renthub.com/api/health`
- ✅ Frontend loads at `https://renthub.vercel.app`
- ✅ User can register/login
- ✅ Properties page loads data
- ✅ SSL certificates are active
- ✅ Queue worker is running
- ✅ Emails are sent successfully
- ✅ No errors in logs

---

**Ready to deploy!** 🚀

Follow this guide step-by-step, and your application will be live in ~30 minutes.
