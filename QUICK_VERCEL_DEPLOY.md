# ⚡ Quick Vercel Deployment Guide

## 🚀 One-Page Quick Reference

### Step 1: Import to Vercel
1. Go to [vercel.com/dashboard](https://vercel.com/dashboard)
2. Click "Add New..." → "Project"
3. Select repository: `anemettemadsen33/RentHub`

### Step 2: Configure Project ⚠️ CRITICAL

```
Framework Preset: Next.js ✓ (auto-detected)
Root Directory:  frontend  ← MUST SET THIS!
Build Command:   npm run build ✓ (auto-detected)
Output Dir:      .next ✓ (auto-detected)
```

**🔴 IMPORTANT**: If you don't set Root Directory to `frontend`, deployment will fail!

### Step 3: Set Environment Variables

Click "Environment Variables" and add:

```env
# Required
NEXT_PUBLIC_API_URL=https://your-backend-url.com
NEXT_PUBLIC_SITE_URL=https://your-app.vercel.app
NEXTAUTH_URL=https://your-app.vercel.app
NEXTAUTH_SECRET=generate-with-openssl-rand-base64-32

# Optional
NEXT_PUBLIC_MAPBOX_TOKEN=your-mapbox-token
NEXT_PUBLIC_GA_MEASUREMENT_ID=your-ga-id
```

### Step 4: Deploy

Click "Deploy" button and wait 2-3 minutes.

### Step 5: Update URLs

After deployment, update these variables with your actual Vercel URL:
- `NEXT_PUBLIC_SITE_URL`
- `NEXTAUTH_URL`

### Step 6: Configure Backend

Update your backend `.env`:

```env
FRONTEND_URL=https://your-vercel-url.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-vercel-url.vercel.app
```

Update `backend/config/cors.php`:

```php
'allowed_origins' => [
    'https://your-vercel-url.vercel.app',
],
```

### Step 7: Verify

Visit these URLs to confirm everything works:
- https://your-app.vercel.app/
- https://your-app.vercel.app/properties
- https://your-app.vercel.app/auth/login

---

## ✅ Checklist

- [ ] Root Directory set to `frontend`
- [ ] All environment variables configured
- [ ] Deployment successful
- [ ] Updated environment variables with actual Vercel URL
- [ ] Backend CORS configured
- [ ] Frontend loads successfully
- [ ] Can navigate to different pages
- [ ] API calls work (or show proper errors if backend not ready)

---

## 🆘 Common Issues

### Build Fails

**Error**: "No such file or directory"
**Fix**: Ensure Root Directory is set to `frontend`

### Pages Show 404

**Error**: Routes not found
**Fix**: Check build logs, verify Next.js config is correct

### API Calls Fail

**Error**: CORS or Network errors
**Fix**: 
1. Check `NEXT_PUBLIC_API_URL` is correct
2. Verify backend CORS allows Vercel domain
3. Ensure backend is accessible publicly

---

## 📚 Full Documentation

For detailed instructions, see:
- **VERCEL_SETUP.md** - Complete deployment guide
- **FRONTEND_COMPLETION_SUMMARY.md** - Project status
- **FRONTEND_README.md** - Technical documentation

---

## 🎯 Current Status

✅ **27/27 pages complete**
✅ **0 security vulnerabilities**
✅ **Ready for production**

**Last Build**: Successful
**Compilation**: 8.4s
**TypeScript**: No errors
