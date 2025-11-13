# RentHub Deployment Guide

## 🚀 Quick Deploy (Automated)

### One-Command Deployment
```powershell
# Deploy everything (frontend + backend)
.\deploy.ps1 -Target all -Message "Your deployment message"

# Check if ready to deploy
.\deploy.ps1 -Target check

# Deploy only frontend
.\deploy.ps1 -Target frontend

# Deploy only backend
.\deploy.ps1 -Target backend
```

---

## 📋 Setup Instructions

### 1️⃣ GitHub Setup (One-time)

Already configured! Just push your code:
```bash
git add .
git commit -m "Your message"
git push origin master
```

### 2️⃣ Vercel Setup (Frontend - One-time)

**Option A: GitHub Auto-Deploy (Recommended)**
1. Go to https://vercel.com
2. Click "Add New Project"
3. Import from GitHub: `anemettemadsen33/RentHub`
4. Framework: Next.js
5. Root Directory: `frontend`
6. Click "Deploy"

**Environment Variables in Vercel:**
```env
NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com/api
NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1
NEXT_PUBLIC_APP_URL=https://rent-ljgrpeajm-madsens-projects.vercel.app
```

**Option B: Vercel CLI**
```bash
npm i -g vercel
cd frontend
vercel login
vercel --prod
```

### 3️⃣ Laravel Forge Setup (Backend - One-time)

1. **Connect Repository:**
   - Go to https://forge.laravel.com
   - Select your site: `renthub-tbj7yxj7.on-forge.com`
   - App → Source Control → GitHub
   - Repository: `anemettemadsen33/RentHub`
   - Branch: `master`

2. **Set Deployment Script:**
   - Go to App → Deployment Script
   - Copy content from `backend/forge-deploy.sh`
   - Paste and Save

3. **Enable Quick Deploy:**
   - Toggle "Quick Deploy" ON
   - Now pushes to `master` will auto-deploy

4. **Environment Variables:**
   - Go to App → Environment
   - Update `.env` with production values:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://renthub-tbj7yxj7.on-forge.com
   
   FRONTEND_URL=https://rent-ljgrpeajm-madsens-projects.vercel.app
   SANCTUM_STATEFUL_DOMAINS=rent-ljgrpeajm-madsens-projects.vercel.app
   
   DB_CONNECTION=mysql
   DB_DATABASE=forge
   DB_USERNAME=forge
   DB_PASSWORD=your_password_from_forge
   
   # Add other keys: Stripe, AWS, etc.
   ```

---

## 🔄 Deployment Workflow

### Automatic (Recommended)
```bash
# 1. Make changes to code
# 2. Test locally
npm run dev  # frontend
php artisan serve  # backend

# 3. Commit and push
git add .
git commit -m "Feature: Add new functionality"
git push origin master

# ✅ Done! Both platforms auto-deploy via GitHub
```

### Using Deploy Script
```powershell
# Check everything is ready
.\deploy.ps1 -Target check

# Deploy with custom message
.\deploy.ps1 -Target all -Message "Deploy: New feature XYZ"
```

### Manual Deployment

**Frontend (Vercel):**
```bash
cd frontend
vercel --prod
```

**Backend (Forge):**
```bash
# SSH to Forge server
ssh forge@renthub-tbj7yxj7.on-forge.com

# Run deployment
cd /home/forge/renthub-tbj7yxj7.on-forge.com
git pull origin master
php artisan migrate --force
php artisan config:cache
```

---

## 📊 Monitor Deployments

### Frontend (Vercel)
- Dashboard: https://vercel.com/madsens-projects
- Logs: Click on deployment → "View Function Logs"
- Status: Automatic status checks

### Backend (Forge)
- Dashboard: https://forge.laravel.com
- Logs: SSH → `tail -f storage/logs/laravel.log`
- Queue: `php artisan queue:monitor`

---

## 🆘 Troubleshooting

### Deployment Failed
```bash
# Check build logs in Vercel/Forge dashboard
# Common fixes:

# Frontend build issues
cd frontend
npm install
npm run build

# Backend issues
cd backend
composer install
php artisan migrate
php artisan config:cache
```

### CORS Errors
- Verify `FRONTEND_URL` in backend `.env`
- Check `SANCTUM_STATEFUL_DOMAINS`
- Verify Vercel URL matches exactly

### Environment Variables
```bash
# Vercel: Check Settings → Environment Variables
# Forge: Check App → Environment

# Must match:
# Frontend NEXT_PUBLIC_API_URL = Backend APP_URL
```

---

## ✅ Deployment Checklist

Before deploying:
- [ ] Run `.\deploy.ps1 -Target check`
- [ ] Test locally (frontend + backend)
- [ ] Update `.env` variables if needed
- [ ] Check database migrations
- [ ] Verify CORS settings
- [ ] Test API endpoints
- [ ] Commit all changes

After deploying:
- [ ] Check Vercel deployment status
- [ ] Check Forge deployment logs
- [ ] Test production URLs
- [ ] Verify authentication works
- [ ] Test critical user flows
- [ ] Monitor error logs (first 30 min)

---

## 🔗 Quick Links

- **Frontend Production:** https://rent-ljgrpeajm-madsens-projects.vercel.app
- **Backend API:** https://renthub-tbj7yxj7.on-forge.com/api
- **Vercel Dashboard:** https://vercel.com/madsens-projects
- **Forge Dashboard:** https://forge.laravel.com
- **GitHub Repository:** https://github.com/anemettemadsen33/RentHub

---

## 📞 Need Help?

Run deployment check:
```powershell
.\deploy.ps1 -Target check
```

This will verify:
- Git repository status
- Dependencies installed
- Build succeeds
- Environment configured
- Ready to deploy
