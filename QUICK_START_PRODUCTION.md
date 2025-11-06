# Quick Start: Production Deployment

This is a quick reference for deploying the CORS fixes to your production environment.

## 🚀 Backend Setup (Laravel Forge)

### 1. Set Environment Variables

In Laravel Forge → Your Site → Environment:

```bash
FRONTEND_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app
SANCTUM_STATEFUL_DOMAINS=rent-iy9dy0oxj-madsens-projects.vercel.app
APP_URL=https://renthub-dji696t0.on-forge.com
```

### 2. Deploy Code

In Laravel Forge → Your Site → Deploy:
- Click "Deploy Now" or push to your git branch

### 3. Clear Cache

SSH into your server and run:
```bash
cd /home/forge/your-site-directory
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 🚀 Frontend Setup (Vercel)

### 1. Set Environment Variables

In Vercel → Your Project → Settings → Environment Variables:

```bash
NEXT_PUBLIC_API_URL=https://renthub-dji696t0.on-forge.com
NEXT_PUBLIC_SITE_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app
NEXTAUTH_URL=https://rent-iy9dy0oxj-madsens-projects.vercel.app
NEXTAUTH_SECRET=<run: openssl rand -base64 32>
```

### 2. Redeploy

In Vercel → Your Project → Deployments:
- Click on latest deployment → "Redeploy"

## ✅ Verify It Works

### Test CORS Headers

```bash
curl -X OPTIONS https://renthub-dji696t0.on-forge.com/api/v1/property-comparison \
  -H "Origin: https://rent-iy9dy0oxj-madsens-projects.vercel.app" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

Look for these headers in the response:
```
Access-Control-Allow-Origin: https://rent-iy9dy0oxj-madsens-projects.vercel.app
Access-Control-Allow-Credentials: true
```

### Test in Browser

1. Open your Vercel app: https://rent-iy9dy0oxj-madsens-projects.vercel.app
2. Open DevTools (F12) → Console tab
3. ✅ Should NOT see CORS errors
4. Open Network tab
5. ✅ API requests should show status 200 (or appropriate, not 503)

## 📚 Detailed Guides

Need more help? Check these detailed guides:

- **PRODUCTION_ENV_SETUP.md** - Complete production setup guide
- **CORS_CONFIGURATION.md** - In-depth CORS configuration and troubleshooting
- **FIXES_APPLIED.md** - Detailed explanation of all fixes

## 🆘 Troubleshooting

### Still seeing CORS errors?

1. **Verify environment variables are saved**
   - Check Forge dashboard
   - Check Vercel dashboard

2. **Clear all caches**
   ```bash
   # Backend
   php artisan config:clear
   php artisan cache:clear
   
   # Frontend - redeploy in Vercel
   ```

3. **Check logs**
   - Backend: Forge → Your Site → Logs
   - Frontend: Vercel → Your Project → Logs

### 503 Service Unavailable?

The web-vitals endpoint 503 error is non-critical (performance monitoring only).
Main API endpoints should work fine.

To fix it, ensure Redis is configured:
```bash
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## 📞 Need Help?

1. Check the detailed guides mentioned above
2. Verify all steps in this quick start
3. Open a GitHub issue with error details

---

**Estimated Time:** 10-15 minutes to apply all fixes
