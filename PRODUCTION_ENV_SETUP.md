# Production Environment Setup

This document provides the exact environment variables needed for your production deployment based on your current setup.

## Current Deployment URLs

Based on the error logs:
- **Frontend (Vercel)**: `https://rent-iy9dy0oxj-madsens-projects.vercel.app`
- **Backend (Forge)**: `https://renthub-dji696t0.on-forge.com`

## Backend Environment Variables (Laravel Forge)

Add these environment variables in your Laravel Forge dashboard under your site's "Environment" section:

```bash
# Application
APP_URL=https://renthub-dji696t0.on-forge.com

# Frontend URL for CORS
FRONTEND_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app

# Sanctum Configuration (domain only, no https://)
SANCTUM_STATEFUL_DOMAINS=rent-iy9dy0oxj-madsens-projects.vercel.app

# Other required variables (if not already set)
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YourGeneratedKeyHere

# Database (as configured on Forge)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**After setting these variables:**
1. Click "Save" in Forge
2. Forge will automatically restart PHP-FPM
3. Clear the Laravel cache by running:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

## Frontend Environment Variables (Vercel)

Add these environment variables in your Vercel project dashboard under Settings > Environment Variables:

```bash
# Backend API URL (no /api/v1 suffix, added automatically)
NEXT_PUBLIC_API_URL=https://renthub-dji696t0.on-forge.com

# Site URL (your Vercel deployment URL)
NEXT_PUBLIC_SITE_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app

# NextAuth Configuration
NEXTAUTH_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app
NEXTAUTH_SECRET=generate-this-with-openssl-rand-base64-32

# Feature Flags
NEXT_PUBLIC_AMP_ENABLED=false

# Node Environment
NODE_ENV=production
```

**Important Notes:**
- Generate `NEXTAUTH_SECRET` by running: `openssl rand -base64 32`
- Make sure to add these for "Production" environment in Vercel
- After adding, redeploy your Vercel app to apply the changes

**After setting these variables:**
1. Click "Save" in Vercel
2. Trigger a new deployment (Vercel > Deployments > Redeploy)

## Testing the Setup

### 1. Test Backend CORS

Run this command from your terminal (replace URLs with your actual ones):

```bash
curl -X OPTIONS https://renthub-dji696t0.on-forge.com/api/v1/property-comparison \
  -H "Origin: https://rent-iy9dy0oxj-madsens-projects.vercel.app" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v
```

**Expected output should include:**
```
< Access-Control-Allow-Origin: https://rent-iy9dy0oxj-madsens-projects.vercel.app
< Access-Control-Allow-Credentials: true
< Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
```

### 2. Test Frontend Connection

1. Open your Vercel app: `https://rent-iy9dy0oxj-madsens-projects.vercel.app`
2. Open browser DevTools (F12) > Network tab
3. Try to access a page that makes API calls
4. Check the network requests:
   - Should see requests to `renthub-dji696t0.on-forge.com`
   - Should NOT see CORS errors in Console
   - Status should be 200 (or appropriate code, not 503)

## Troubleshooting

### Still seeing CORS errors?

1. **Clear Laravel cache on Forge:**
   ```bash
   cd /home/forge/renthub-dji696t0.on-forge.com
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

2. **Verify environment variables are loaded:**
   ```bash
   php artisan tinker
   >>> env('FRONTEND_URL')
   >>> env('SANCTUM_STATEFUL_DOMAINS')
   ```

3. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### 503 Service Unavailable Error?

This could be:
1. **Backend down**: Check if your Forge site is running
2. **Database issue**: Verify database credentials
3. **PHP error**: Check error logs in Forge
4. **Queue worker**: Ensure queue workers are running if needed

### Vercel Build Failing?

1. Check build logs in Vercel dashboard
2. Ensure all environment variables are set
3. Try rebuilding: Vercel > Deployments > Redeploy

## Custom Domain Setup (Optional)

If you want to use custom domains instead of Vercel/Forge subdomains:

### Frontend Custom Domain (e.g., renthub.com)
1. Add domain in Vercel dashboard
2. Update DNS records as instructed by Vercel
3. Update all environment variables to use new domain

### Backend Custom Domain (e.g., api.renthub.com)
1. Add domain in Forge dashboard
2. Update DNS A record to point to Forge server
3. Enable SSL in Forge
4. Update all environment variables to use new domain

## Need Help?

If you're still experiencing issues after following this guide:

1. **Check logs:**
   - Backend: Forge > Your Site > Logs
   - Frontend: Vercel > Your Project > Logs

2. **Verify configuration:**
   - Run the test commands above
   - Check browser DevTools Console and Network tabs

3. **Get support:**
   - Open an issue: https://github.com/anemettemadsen33/RentHub/issues
   - Include error messages, logs, and what you've tried

## Quick Reference

| Setting | Backend (Forge) | Frontend (Vercel) |
|---------|----------------|-------------------|
| URL | https://renthub-dji696t0.on-forge.com | https://rent-iy9dy0oxj-madsens-projects.vercel.app |
| `APP_URL` / `NEXT_PUBLIC_SITE_URL` | Backend URL | Frontend URL |
| `FRONTEND_URL` | Frontend URL | N/A |
| `SANCTUM_STATEFUL_DOMAINS` | Frontend domain (no https) | N/A |
| `NEXT_PUBLIC_API_URL` | N/A | Backend URL |

---

**Ready to deploy?** Follow the steps above in order, test after each major change, and you should have a working production deployment!
