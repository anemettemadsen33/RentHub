# 🚀 Quick Deployment Checklist

**Time Required**: ~30 minutes  
**Prerequisites**: ✅ Forge account, ✅ Vercel account, ✅ GitHub connected

---

## 📋 BACKEND (Laravel Forge) - 20 minutes

### Step 1: Create Server (5 min)
- [ ] Go to https://forge.laravel.com/servers
- [ ] Click "Create Server"
- [ ] Select cloud provider (DigitalOcean recommended)
- [ ] Choose: **2GB RAM, PHP 8.3, MySQL 8.0**
- [ ] Enable: **Redis**
- [ ] Wait for server provision (~3-5 min)

### Step 2: Create Site (2 min)
- [ ] Click "Sites" → "New Site"
- [ ] Domain: `api.renthub.com` (or your domain)
- [ ] Web Directory: `/public`
- [ ] PHP Version: **8.3**
- [ ] Click "Add Site"

### Step 3: Connect GitHub (2 min)
- [ ] Go to site → "Git Repository"
- [ ] Provider: **GitHub**
- [ ] Repository: `anemettemadsen33/RentHub`
- [ ] Branch: `master`
- [ ] Deploy Path: `/backend`
- [ ] ✅ Enable Quick Deploy
- [ ] Click "Install Repository"

### Step 4: Environment Variables (5 min)
- [ ] Go to site → "Environment"
- [ ] Copy content from `backend/.env.example`
- [ ] Update these values:
  ```
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://api.renthub.com
  
  DB_DATABASE=forge
  DB_USERNAME=forge
  DB_PASSWORD=[copy from Forge]
  
  CACHE_STORE=redis
  QUEUE_CONNECTION=redis
  SESSION_DRIVER=redis
  
  FRONTEND_URL=https://renthub.vercel.app
  ```
- [ ] Click "Save"

### Step 5: Deploy Script (1 min)
- [ ] Go to site → "Deployment Script"
- [ ] Paste this:
  ```bash
  cd /home/forge/api.renthub.com/backend
  composer install --no-dev --optimize-autoloader
  php artisan config:cache
  php artisan route:cache
  php artisan optimize
  php artisan queue:restart
  sudo service php8.3-fpm reload
  ```
- [ ] Click "Save"

### Step 6: First Deploy (2 min)
- [ ] Click "Deploy Now"
- [ ] Wait for deployment to finish
- [ ] SSH into server (click "SSH" button)
- [ ] Run:
  ```bash
  cd /home/forge/api.renthub.com/backend
  php artisan migrate --force
  php artisan db:seed --force
  ```

### Step 7: Setup Services (3 min)
- [ ] Go to "SSL" → Click "LetsEncrypt" → Get certificate
- [ ] Go to "Queue" → Add worker:
  - Connection: `redis`
  - Queue: `default`
  - Processes: `1`
- [ ] Go to "Scheduler" → Enable

### Step 8: Test Backend ✅
- [ ] Visit: `https://api.renthub.com/api/health`
- [ ] Should return: `{"status":"ok"}`

---

## 🎨 FRONTEND (Vercel) - 5 minutes

### Step 1: Import Project (2 min)
- [ ] Go to https://vercel.com/new
- [ ] Click "Import Git Repository"
- [ ] Select: `anemettemadsen33/RentHub`
- [ ] Configure:
  - Framework: **Next.js**
  - Root Directory: `frontend`
  - Build Command: `npm run build`
  - Output Directory: `.next`

### Step 2: Environment Variables (2 min)
- [ ] Click "Environment Variables"
- [ ] Add these:
  ```
  NEXT_PUBLIC_API_URL = https://api.renthub.com
  NEXT_PUBLIC_API_BASE_URL = https://api.renthub.com/api/v1
  NEXT_PUBLIC_APP_NAME = RentHub
  NODE_ENV = production
  ```
- [ ] Click "Deploy"

### Step 3: Wait for Deploy (1 min)
- [ ] Wait ~2-3 minutes for build
- [ ] Vercel will provide URL: `https://renthub-xyz.vercel.app`

### Step 4: Test Frontend ✅
- [ ] Visit your Vercel URL
- [ ] Homepage should load
- [ ] Try login page

---

## 🔗 CONNECT BACKEND & FRONTEND - 5 minutes

### Step 1: Update Backend CORS
- [ ] SSH into Forge server
- [ ] Edit `.env`:
  ```bash
  cd /home/forge/api.renthub.com/backend
  nano .env
  ```
- [ ] Update:
  ```
  FRONTEND_URL=https://renthub-xyz.vercel.app
  SANCTUM_STATEFUL_DOMAINS=renthub-xyz.vercel.app
  ```
- [ ] Save and exit (Ctrl+X, Y, Enter)
- [ ] Redeploy via Forge dashboard

### Step 2: Test Integration ✅
- [ ] Open frontend
- [ ] Try to register account
- [ ] Check if API calls work
- [ ] Open browser console - should see no errors

---

## ✅ FINAL VERIFICATION

### Backend Checklist
- [ ] `https://api.renthub.com/api/health` returns OK
- [ ] `https://api.renthub.com/api/v1/properties` returns data
- [ ] SSL certificate is active (padlock in browser)
- [ ] Queue worker status: Running (in Forge)
- [ ] Scheduler enabled (in Forge)

### Frontend Checklist
- [ ] Homepage loads correctly
- [ ] Login page loads
- [ ] Properties page loads
- [ ] No console errors
- [ ] Images load correctly

### Integration Checklist
- [ ] Can register new account
- [ ] Can login
- [ ] Dashboard loads after login
- [ ] Properties load from API
- [ ] Search/filters work

---

## 🎉 SUCCESS!

If all checkboxes are checked, your app is LIVE!

**Backend**: `https://api.renthub.com`  
**Frontend**: `https://renthub.vercel.app`

### Next Steps (Optional)

1. **Custom Domain**
   - Add your domain in Vercel
   - Update DNS records
   - Update backend CORS settings

2. **Add Integrations**
   - Stripe API keys
   - Google OAuth credentials
   - Pusher credentials
   - Twilio for SMS

3. **Monitoring**
   - Set up error tracking (Sentry)
   - Configure uptime monitoring
   - Enable analytics

4. **Security**
   - Change admin password
   - Review security settings
   - Configure firewall rules

---

## 🆘 NEED HELP?

**Quick Fixes:**

1. **Backend 500 error**
   ```bash
   # Check logs
   cd /home/forge/api.renthub.com/backend
   tail -f storage/logs/laravel.log
   ```

2. **Frontend build fails**
   - Check Vercel build logs
   - Verify environment variables
   - Check for TypeScript errors

3. **CORS errors**
   - Verify FRONTEND_URL in backend .env
   - Check SANCTUM_STATEFUL_DOMAINS
   - Redeploy backend after changes

4. **Database errors**
   - Check DB credentials in .env
   - Verify migrations ran: `php artisan migrate:status`

---

**Full Guide**: See `DEPLOYMENT_FORGE_VERCEL.md`

**Estimated Total Time**: 30 minutes  
**Difficulty**: Easy ⭐⭐☆☆☆

**Ready? Let's deploy!** 🚀
