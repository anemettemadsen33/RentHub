# Quick Deploy Guide - RentHub

## 🚀 Ready for Deployment!

Both backend and frontend are configured and tested. Follow these quick steps:

## Backend → Laravel Forge (5 Minutes)

### 1. Create Site in Forge
- Domain: `api.yoursite.com`
- **Web Directory**: `/backend/public` ⚠️
- PHP: 8.2+
- Type: Laravel

### 2. Connect GitHub Repo
- Repo: `anemettemadsen33/RentHub`
- Branch: `main`

### 3. Update Deployment Script
Replace default script with:
```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH
bash forge-deploy.sh
```

### 4. Set Environment Variables
Copy from `backend/.env.example` and update:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yoursite.com
# ... database, mail, etc.
```

### 5. Deploy & Initialize
Click "Deploy Now", then SSH:
```bash
cd /home/forge/yoursite/backend
php artisan migrate --force
php artisan storage:link
```

### 6. Add Queue Worker (Optional)
In Forge Daemons:
```bash
php artisan queue:work redis --sleep=3 --tries=3
```

### 7. Enable SSL
Use Let's Encrypt in Forge SSL tab

## Frontend → Vercel (3 Minutes)

### 1. Import in Vercel
- Login to [vercel.com](https://vercel.com)
- "Add New" → "Project"
- Select: `anemettemadsen33/RentHub`

### 2. Configure
- **Root Directory**: `frontend` ⚠️
- Framework: Next.js (auto)
- Leave other settings as default

### 3. Set Environment Variables
```env
NEXT_PUBLIC_API_URL=https://api.yoursite.com
NEXT_PUBLIC_SITE_URL=https://yourapp.vercel.app
NEXTAUTH_URL=https://yourapp.vercel.app
NEXTAUTH_SECRET=<run: openssl rand -base64 32>
```

### 4. Deploy
Click "Deploy" and wait ~2 minutes

### 5. Update Backend CORS
In backend `.env`:
```env
FRONTEND_URL=https://yourapp.vercel.app
SANCTUM_STATEFUL_DOMAINS=yourapp.vercel.app
```

Redeploy backend in Forge.

## ✅ Verify

### Backend
- Visit: `https://api.yoursite.com`
- Admin: `https://api.yoursite.com/admin`

### Frontend
- Visit: `https://yourapp.vercel.app`
- Test login/registration

### Integration
- Test API calls from frontend
- Verify authentication works

## 🆘 Need Help?

See detailed guides:
- [DEPLOYMENT_STATUS.md](DEPLOYMENT_STATUS.md) - Complete guide
- [FORGE_DEPLOYMENT.md](FORGE_DEPLOYMENT.md) - Backend details
- [VERCEL_DEPLOYMENT.md](VERCEL_DEPLOYMENT.md) - Frontend details

## 📞 Support

For issues, check:
1. Deployment logs (Forge → Deployments, Vercel → Deployments)
2. Application logs (`backend/storage/logs/laravel.log`)
3. Browser console for frontend errors

---

**Total Time**: ~10 minutes
**Difficulty**: Easy (with provided scripts)
**Cost**: Free tier available on both platforms
