# RentHub Deployment Checklist

Use this checklist to deploy your configured RentHub application.

## Pre-Deployment Configuration

✅ Backend configured with Forge URL: `https://renthub-dji696t0.on-forge.com`
✅ Frontend configured with Vercel URL: `https://rent-hub-six.vercel.app`
✅ Database password configured (stored in .env file)
✅ CORS configured to allow frontend domain
✅ Sanctum configured for stateful authentication

## Backend Deployment (Forge)

### 1. Set Environment Variables
- [ ] Log into Laravel Forge
- [ ] Navigate to site: renthub-dji696t0.on-forge.com
- [ ] Go to **Environment** tab
- [ ] Copy entire contents of `backend/.env` from this repo
- [ ] Paste into Forge Environment editor
- [ ] Click **Save**

### 2. Generate Application Key
- [ ] SSH into server
- [ ] Run: `cd /home/forge/renthub-dji696t0.on-forge.com/backend`
- [ ] Run: `php artisan key:generate`
- [ ] Copy generated key
- [ ] Update APP_KEY in Forge Environment editor

### 3. Database Setup
- [ ] Run: `php artisan migrate --force`
- [ ] Run: `php artisan storage:link`

### 4. Optimize Application
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`

### 5. Deploy
- [ ] Click **Deploy Now** in Forge dashboard
- [ ] Wait for deployment to complete
- [ ] Check deployment logs for errors

### 6. Verify Backend
- [ ] Visit: https://renthub-dji696t0.on-forge.com
- [ ] Check that site loads without 500 errors
- [ ] Check logs: `/home/forge/renthub-dji696t0.on-forge.com/backend/storage/logs/laravel.log`

## Frontend Deployment (Vercel)

### 1. Set Environment Variables
- [ ] Log into Vercel
- [ ] Navigate to project: rent-hub-six
- [ ] Go to **Settings** → **Environment Variables**
- [ ] Add: `NEXT_PUBLIC_API_URL` = `https://renthub-dji696t0.on-forge.com`
- [ ] Add: `NEXT_PUBLIC_SITE_URL` = `https://rent-hub-six.vercel.app`
- [ ] Add: `NEXTAUTH_URL` = `https://rent-hub-six.vercel.app`
- [ ] Generate secret: `openssl rand -base64 32`
- [ ] Add: `NEXTAUTH_SECRET` = (generated secret)
- [ ] Add: `NODE_ENV` = `production`
- [ ] Click **Save**

### 2. Deploy
- [ ] Go to **Deployments** tab
- [ ] Click **...** on latest deployment
- [ ] Select **Redeploy**
- [ ] Wait for build to complete

### 3. Verify Frontend
- [ ] Visit: https://rent-hub-six.vercel.app
- [ ] Check that site loads
- [ ] Open browser DevTools → Console
- [ ] Verify no CORS errors
- [ ] Check Network tab for API calls to backend

## Integration Testing

### 1. CORS and API Connection
- [ ] Open frontend site
- [ ] Open browser DevTools
- [ ] Try to make an API call (e.g., login, register)
- [ ] Verify API calls reach backend successfully
- [ ] Check for CORS errors (should be none)

### 2. Authentication
- [ ] Try to register a new account
- [ ] Try to log in
- [ ] Verify cookies are being set
- [ ] Check that authentication persists on page refresh

### 3. Image Loading
- [ ] Upload a property image (if functionality exists)
- [ ] Verify images load from Forge backend
- [ ] Check Network tab for image requests

## Post-Deployment

### Optional: Additional Configuration

#### Email Service
- [ ] Configure MAIL_* variables in Forge if sending emails
- [ ] Test email functionality

#### Social Authentication
- [ ] Add Google/Facebook credentials if using social login
- [ ] Test social authentication flows

#### SMS Notifications
- [ ] Add Twilio credentials if using SMS
- [ ] Test SMS functionality

#### Custom Domain
- [ ] Configure custom domain in Vercel (if desired)
- [ ] Update Forge environment variables with new domain
- [ ] Update Sanctum stateful domains

### Security
- [ ] Verify SSL certificates are active on both domains
- [ ] Check that APP_DEBUG is false in production
- [ ] Verify database password is strong
- [ ] Review Laravel logs for any security warnings
- [ ] Set up Forge scheduled backups

### Monitoring
- [ ] Set up error tracking (Sentry, Bugsnag, etc.)
- [ ] Configure uptime monitoring
- [ ] Set up alerts for deployment failures

## Troubleshooting

If you encounter issues, see **PRODUCTION_DEPLOYMENT_GUIDE.md** for detailed troubleshooting steps.

Common issues:
- ❌ **CORS errors**: Check FRONTEND_URL and SANCTUM_STATEFUL_DOMAINS in backend
- ❌ **401 errors**: Verify Sanctum configuration and cookies
- ❌ **500 errors**: Check Laravel logs and run `php artisan config:cache`
- ❌ **Images not loading**: Verify image hostname in next.config.ts
- ❌ **Database connection failed**: Check DB_* credentials in Forge

## Support Resources

- 📖 **SETUP_INSTRUCTIONS.md** - Quick setup guide
- 📖 **PRODUCTION_DEPLOYMENT_GUIDE.md** - Detailed deployment guide
- 🔗 [Laravel Forge Docs](https://forge.laravel.com/docs)
- 🔗 [Vercel Docs](https://vercel.com/docs)
- 🐛 [GitHub Issues](https://github.com/anemettemadsen33/RentHub/issues)

---

**Last Updated**: 2025-11-04
**Configured By**: GitHub Copilot
