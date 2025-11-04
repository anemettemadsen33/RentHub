# RentHub Deployment Guide

This guide provides step-by-step instructions for deploying RentHub to production.

## Recent Fixes Applied ✅

The following deployment issues have been fixed:

1. **Backend (Forge)**: Removed hardcoded deployment script path that caused deployment errors
2. **Frontend (Vercel)**: Fixed hardcoded localhost URLs to use environment variables
3. **Frontend (Vercel)**: All pages now properly use API URL from environment configuration

## Prerequisites

- Laravel Forge account (for backend)
- Vercel account (for frontend)
- Domain name (optional but recommended)
- GitHub repository access

## Part 1: Deploy Backend to Laravel Forge

### Step 1: Create New Site in Forge

1. Log into [Laravel Forge](https://forge.laravel.com)
2. Click **"Create New Site"**
3. Configure:
   - **Server**: Select your server or create a new one
   - **Root Domain**: Your domain (e.g., `api.renthub.com`)
   - **Project Type**: Select **"Laravel"**
   - **Web Directory**: `/backend/public` ⚠️ **IMPORTANT!**
   - **PHP Version**: 8.2 or higher
   - **Create Site Certificate**: Enable (for HTTPS)

### Step 2: Install Repository

1. Go to **Apps** tab in your site
2. Click **"Install Repository"**
3. Configure:
   - **Repository**: `anemettemadsen33/RentHub`
   - **Branch**: `main`
   - **Install Composer Dependencies**: ✅ Check this

### Step 3: Update Deployment Script

In the **Apps** tab, update the deployment script to:

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

# Run the deployment script from root
bash forge-deploy.sh
```

The `forge-deploy.sh` script at the repository root will:
- Navigate to the backend directory automatically
- Install dependencies
- Run migrations
- Clear and cache configurations
- Restart queue workers

### Step 4: Configure Environment Variables

1. Go to **Environment** tab
2. Update the `.env` file with production values:

```env
APP_NAME=RentHub
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://api.renthub.com

# Database - Update with your actual database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=forge
DB_PASSWORD=YOUR_SECURE_PASSWORD

# Cache & Session (use redis for production)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Frontend URL - UPDATE THIS AFTER DEPLOYING TO VERCEL
FRONTEND_URL=https://your-app.vercel.app

# Sanctum - UPDATE THIS AFTER DEPLOYING TO VERCEL
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@renthub.com"
```

### Step 5: Run Initial Deployment

1. Click **"Deploy Now"** in the Apps tab
2. Wait for deployment to complete
3. SSH into your server and run:

```bash
cd /home/forge/YOUR_SITE_NAME/backend
php artisan migrate --force
php artisan storage:link
php artisan config:cache
```

### Step 6: Setup Queue Worker (Optional but Recommended)

1. Go to **Daemons** tab
2. Click **"Create Daemon"**
3. Configure:
   - **Command**: `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`
   - **Directory**: `/home/forge/YOUR_SITE_NAME/backend`

### Step 7: Enable SSL

1. Go to **SSL** tab
2. Click **"Let's Encrypt"**
3. Follow the steps to enable free SSL certificate

## Part 2: Deploy Frontend to Vercel

### Step 1: Import Project

1. Log into [Vercel](https://vercel.com)
2. Click **"Add New..."** → **"Project"**
3. Select your GitHub repository: `anemettemadsen33/RentHub`
4. Click **"Import"**

### Step 2: Configure Build Settings

Vercel will auto-detect Next.js. Configure:

- **Framework Preset**: Next.js (auto-detected)
- **Root Directory**: `frontend` ⚠️ **IMPORTANT!**
- **Build Command**: `npm run build` (default)
- **Output Directory**: `.next` (default)
- **Install Command**: `npm install` (default)

### Step 3: Set Environment Variables

Click **"Environment Variables"** and add:

```env
NEXT_PUBLIC_API_URL=https://api.renthub.com
NEXT_PUBLIC_SITE_URL=https://your-app.vercel.app
NEXT_PUBLIC_AMP_ENABLED=false
NEXTAUTH_URL=https://your-app.vercel.app
NEXTAUTH_SECRET=generate-using-openssl-rand-base64-32
NODE_ENV=production
```

To generate `NEXTAUTH_SECRET`:
```bash
openssl rand -base64 32
```

### Step 4: Deploy

1. Click **"Deploy"**
2. Wait 2-3 minutes for build to complete
3. Vercel will provide a URL like: `https://rent-hub-xxxxx.vercel.app`

### Step 5: Update Backend CORS

After getting your Vercel URL, update your backend environment in Forge:

1. Go to Forge → Your Site → **Environment** tab
2. Update these values:

```env
FRONTEND_URL=https://your-actual-vercel-url.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-actual-vercel-url.vercel.app
```

3. SSH into your server and run:

```bash
cd /home/forge/YOUR_SITE_NAME/backend
php artisan config:cache
php artisan config:clear
php artisan cache:clear
```

## Part 3: Configure Custom Domain (Optional)

### For Backend (Forge)

1. In your domain DNS settings, add A record:
   - **Host**: `api` (or `@` for root domain)
   - **Value**: Your Forge server IP address
2. In Forge, update site to use your custom domain
3. Re-enable SSL certificate for the new domain

### For Frontend (Vercel)

1. In Vercel → Your Project → **Settings** → **Domains**
2. Click **"Add"**
3. Enter your domain (e.g., `renthub.com`)
4. Follow Vercel's DNS configuration instructions
5. Update environment variables with new domain:
   - `NEXT_PUBLIC_SITE_URL=https://renthub.com`
   - `NEXTAUTH_URL=https://renthub.com`
6. Update backend environment with new frontend URL

## Verification Checklist

After deployment, verify everything works:

### Backend Checks
- [ ] Site loads without errors
- [ ] API endpoints respond: `https://api.renthub.com/api/properties`
- [ ] Admin panel accessible: `https://api.renthub.com/admin`
- [ ] Database migrations completed
- [ ] Queue worker running (if configured)
- [ ] SSL certificate active (HTTPS)

### Frontend Checks
- [ ] Homepage loads correctly
- [ ] No hardcoded localhost URLs visible
- [ ] Can navigate to all pages: `/properties`, `/auth/login`, etc.
- [ ] API connection works (check browser console for errors)
- [ ] Images load correctly
- [ ] SSL certificate active (HTTPS)

### Integration Checks
- [ ] Frontend can fetch data from backend API
- [ ] CORS working (no CORS errors in browser console)
- [ ] Authentication flow works end-to-end
- [ ] File uploads work (if applicable)

## Troubleshooting

### Issue: "CORS Error" in browser console

**Solution**: 
1. Verify `FRONTEND_URL` in backend `.env` matches your Vercel URL exactly
2. Clear config cache: `php artisan config:cache`
3. Check `backend/config/cors.php` has correct settings

### Issue: "404 Not Found" on API requests

**Solution**:
1. Verify `NEXT_PUBLIC_API_URL` in Vercel environment variables
2. Make sure API URL includes the protocol: `https://`
3. Redeploy frontend after changing environment variables

### Issue: Pages show "localhost" URLs

**Solution**:
- This has been fixed in the latest deployment
- Make sure you have the latest code from the repository
- Verify environment variables are set in Vercel

### Issue: Forge deployment fails

**Solution**:
1. Check deployment logs in Forge
2. Verify Web Directory is set to `/backend/public`
3. Ensure deployment script uses `bash forge-deploy.sh` from root
4. Check composer dependencies are installing correctly

### Issue: Database connection errors

**Solution**:
1. Verify database credentials in `.env`
2. Ensure database exists: Create it in Forge under Database tab
3. Run migrations: `php artisan migrate --force`

## Automatic Deployments

### Backend (Forge)
- Automatically deploys on push to `main` branch
- Uses the `forge-deploy.sh` script from repository root
- Handles migrations, caching, and optimization automatically

### Frontend (Vercel)
- **Production**: Automatically deploys on push to `main` branch
- **Preview**: Creates preview deployments for all other branches and pull requests
- Includes automatic HTTPS, CDN, and image optimization

## Maintenance

### Update Backend
```bash
cd /home/forge/YOUR_SITE_NAME/backend
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

### Update Frontend
Simply push to GitHub - Vercel will deploy automatically

## Security Recommendations

1. ✅ Use strong database passwords
2. ✅ Enable 2FA on Forge and Vercel accounts
3. ✅ Keep `APP_DEBUG=false` in production
4. ✅ Use HTTPS for all domains (SSL certificates)
5. ✅ Regularly update dependencies
6. ✅ Configure rate limiting in Laravel
7. ✅ Set up error monitoring (Sentry, Bugsnag, etc.)
8. ✅ Regular database backups

## Support

If you encounter issues not covered in this guide:
1. Check deployment logs in Forge/Vercel
2. Review browser console for errors
3. Check Laravel logs: `storage/logs/laravel.log`
4. Open an issue in the GitHub repository

---

**Last Updated**: 2025-11-04  
**Version**: 1.1.0
