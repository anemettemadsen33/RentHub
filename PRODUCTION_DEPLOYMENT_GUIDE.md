# Production Deployment Guide

This guide explains how to deploy RentHub with the configured production URLs.

## Overview

- **Backend (Laravel)**: https://renthub-dji696t0.on-forge.com/
- **Frontend (Next.js)**: https://rent-hub-six.vercel.app/
- **Database**: MySQL on Forge with password `TRKqxZJypXmdr81y0n63`

## Backend Deployment on Laravel Forge

### 1. Environment Configuration

In your Laravel Forge dashboard:

1. Go to your site **renthub-dji696t0.on-forge.com**
2. Navigate to the **Environment** tab
3. Replace the entire content with the following configuration:

```env
APP_NAME=RentHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://renthub-dji696t0.on-forge.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=TRKqxZJypXmdr81y0n63

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.renthub-dji696t0.on-forge.com

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@renthub.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Frontend URL for CORS
FRONTEND_URL=https://rent-hub-six.vercel.app

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=rent-hub-six.vercel.app,renthub-dji696t0.on-forge.com

# Social Authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/api/v1/auth/google/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/api/v1/auth/facebook/callback"

# Twilio SMS Configuration
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=
```

4. Click **Save**

### 2. Generate Application Key

If `APP_KEY` is empty, SSH into your server and run:

```bash
cd /home/forge/renthub-dji696t0.on-forge.com/backend
php artisan key:generate
```

Then copy the generated key back to the Forge Environment editor.

### 3. Run Migrations

SSH into your server and run:

```bash
cd /home/forge/renthub-dji696t0.on-forge.com/backend
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Deploy

In Forge, click the **Deploy Now** button to deploy your application with the new configuration.

## Frontend Deployment on Vercel

### 1. Environment Variables

In your Vercel project dashboard:

1. Go to your project **rent-hub-six**
2. Navigate to **Settings** → **Environment Variables**
3. Add the following variables:

| Name | Value | Environments |
|------|-------|--------------|
| `NEXT_PUBLIC_API_URL` | `https://renthub-dji696t0.on-forge.com` | Production, Preview, Development |
| `NEXT_PUBLIC_SITE_URL` | `https://rent-hub-six.vercel.app` | Production |
| `NEXT_PUBLIC_SITE_URL` | `https://rent-hub-git-*.vercel.app` | Preview, Development |
| `NEXT_PUBLIC_AMP_ENABLED` | `false` | All |
| `NEXTAUTH_URL` | `https://rent-hub-six.vercel.app` | Production |
| `NEXTAUTH_SECRET` | Generate with: `openssl rand -base64 32` | All |
| `NODE_ENV` | `production` | Production |

### 2. Generate NEXTAUTH_SECRET

Run this command on your local machine or in a terminal:

```bash
openssl rand -base64 32
```

Copy the output and use it as the value for `NEXTAUTH_SECRET`.

### 3. Redeploy

After adding the environment variables:

1. Go to the **Deployments** tab
2. Find the latest deployment
3. Click the **...** menu
4. Select **Redeploy**

Or push a new commit to trigger a deployment.

## Verification

### 1. Backend Health Check

Visit your backend URL:
```
https://renthub-dji696t0.on-forge.com
```

You should see the Laravel welcome page or API response.

### 2. Frontend Access

Visit your frontend URL:
```
https://rent-hub-six.vercel.app
```

You should see the RentHub homepage.

### 3. API Connection Test

Open your browser's developer console on the frontend site and check:
- Network tab for API calls to `https://renthub-dji696t0.on-forge.com/api/*`
- Console for any CORS errors (there should be none)

## Common Issues and Solutions

### CORS Errors

If you see CORS errors in the browser console:

1. Verify `FRONTEND_URL` is set correctly in backend environment
2. Verify `SANCTUM_STATEFUL_DOMAINS` includes your Vercel domain
3. Clear backend cache: `php artisan config:cache`
4. Redeploy backend

### 401 Unauthorized on API Calls

This usually means Sanctum is not configured correctly:

1. Check that the frontend domain is in `SANCTUM_STATEFUL_DOMAINS`
2. Ensure cookies are being sent with requests
3. Verify SSL certificates are valid on both domains

### Frontend Not Connecting to Backend

1. Check `NEXT_PUBLIC_API_URL` in Vercel environment variables
2. Verify the backend URL is accessible (test in browser)
3. Check Vercel build logs for any errors
4. Redeploy frontend after changing environment variables

### Database Connection Errors

1. Verify database credentials in Forge environment
2. Check that MySQL is running on Forge server
3. Ensure the database exists and user has proper permissions
4. Run migrations: `php artisan migrate --force`

## Security Checklist

- [x] `APP_DEBUG` is set to `false` in production
- [x] `APP_ENV` is set to `production`
- [x] Strong database password is used
- [x] CORS is configured to only allow frontend domain
- [x] Sanctum stateful domains are properly configured
- [ ] SSL certificates are valid on both domains (Let's Encrypt on Forge, automatic on Vercel)
- [ ] Rate limiting is enabled (already configured in Laravel)
- [ ] Regular backups are configured in Forge

## Next Steps

1. **Configure Email**: Update `MAIL_*` variables in backend environment for transactional emails
2. **Social Auth**: Add Google/Facebook credentials if using social login
3. **SMS**: Configure Twilio credentials if using SMS notifications
4. **Monitoring**: Set up error tracking (Sentry, Bugsnag, etc.)
5. **Custom Domain**: Configure custom domains instead of default Forge/Vercel URLs
6. **Backups**: Set up automated database backups in Forge

## Support

- Laravel Forge: https://forge.laravel.com/docs
- Vercel Documentation: https://vercel.com/docs
- RentHub Issues: https://github.com/anemettemadsen33/RentHub/issues
