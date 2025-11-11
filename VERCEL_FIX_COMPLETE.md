# 🚀 Vercel Deployment - COMPLETE FIX GUIDE

## 🔴 PROBLEM: 404 on all pages except homepage

**Cause**: Vercel not configured correctly for Next.js 15 App Router in monorepo

---

## ✅ SOLUTION: Complete Vercel Setup

### STEP 1: Delete Current Vercel Project

1. Go to **Vercel Dashboard**: https://vercel.com/dashboard
2. Find your **RentHub** project
3. Click **Settings** → Scroll to bottom
4. Click **"Delete Project"** → Confirm

---

### STEP 2: Re-import with Correct Settings

1. Click **"Add New..."** → **"Project"**
2. Click **"Import Git Repository"**
3. Select: **anemettemadsen33/RentHub**
4. Click **"Import"**

---

### STEP 3: Configure Build Settings

**CRITICAL - Set these EXACTLY:**

```
Framework Preset: Next.js
Root Directory: frontend  ← MUST SELECT THIS!
Build Command: npm run build
Output Directory: .next
Install Command: npm install
Node.js Version: 20.x
```

**HOW TO SET ROOT DIRECTORY:**
- Click **"Edit"** next to Root Directory
- Click the **folder icon** 
- Select **"frontend"** from the dropdown
- You should see: `frontend` selected

---

### STEP 4: Environment Variables

Click **"Environment Variables"** and add:

**Production Environment:**
```
NEXT_PUBLIC_API_URL = https://renthub-mnnzqvzb.on-forge.com
NEXT_PUBLIC_API_BASE_URL = https://renthub-mnnzqvzb.on-forge.com/api/v1
NEXT_PUBLIC_APP_NAME = RentHub
NEXT_PUBLIC_APP_URL = https://renthub.vercel.app
NODE_ENV = production
```

**Preview Environment (optional):**
Same as above

**Development Environment (optional):**
```
NEXT_PUBLIC_API_URL = http://localhost:8000
NEXT_PUBLIC_API_BASE_URL = http://localhost:8000/api/v1
NODE_ENV = development
```

---

### STEP 5: Deploy

1. Click **"Deploy"**
2. Wait 2-3 minutes for build
3. Vercel will give you a URL: `https://renthub-xyz.vercel.app`

---

## ✅ VERIFICATION

After deployment succeeds, test ALL these URLs:

```
✅ Homepage: https://renthub-xyz.vercel.app/
✅ Properties: https://renthub-xyz.vercel.app/properties
✅ Login: https://renthub-xyz.vercel.app/auth/login
✅ Register: https://renthub-xyz.vercel.app/auth/register
✅ About: https://renthub-xyz.vercel.app/about
✅ Contact: https://renthub-xyz.vercel.app/contact
```

**ALL should work** - no 404 errors!

---

## 🚨 TROUBLESHOOTING

### Issue: Still getting 404 errors

**Check Build Logs:**
1. Vercel → Deployments → Click latest deployment
2. Click **"Building"** → View logs
3. Look for errors

**Common issues:**

**1. Wrong Root Directory**
```
❌ Root Directory: / (root)
✅ Root Directory: frontend
```

**2. Missing Environment Variables**
- Must have `NEXT_PUBLIC_API_URL`
- Variables must start with `NEXT_PUBLIC_`
- Must redeploy after adding variables

**3. Build Command Wrong**
```
❌ Build Command: cd frontend && npm run build
✅ Build Command: npm run build (Vercel handles root directory)
```

**4. Output Directory Wrong**
```
❌ Output Directory: frontend/.next
✅ Output Directory: .next (relative to root directory)
```

---

### Issue: Build fails with "Module not found"

**Solution:**
```bash
# Locally, verify build works:
cd frontend
rm -rf .next node_modules
npm install
npm run build

# If it works locally, push to GitHub:
git add .
git commit -m "Fix dependencies"
git push

# Vercel will auto-deploy
```

---

### Issue: Pages work but API calls fail (CORS errors)

**Check Backend .env on Forge:**
```env
FRONTEND_URL=https://renthub-xyz.vercel.app
SANCTUM_STATEFUL_DOMAINS=renthub-xyz.vercel.app
```

**Then redeploy backend in Forge**

---

## 📊 Expected Build Output

When build succeeds, you should see:

```
✓ Creating an optimized production build
✓ Compiled successfully
✓ Linting and checking validity of types
✓ Collecting page data
✓ Generating static pages (197/197)
✓ Collecting build traces
✓ Finalizing page optimization

Route (app)                              Size     First Load JS
┌ ○ /                                    5.2 kB         95 kB
├ ○ /about                               3.1 kB         92 kB
├ ○ /auth/login                          4.5 kB         94 kB
├ ○ /properties                          8.3 kB        102 kB
└ ○ /properties/[id]                     6.7 kB         98 kB
```

All routes should show **○** (static) or **λ** (server-side)

---

## 🎯 CHECKLIST

Before deploying, verify:

- [ ] Root Directory = `frontend`
- [ ] Framework = Next.js
- [ ] Build Command = `npm run build`
- [ ] Output Directory = `.next`
- [ ] Node.js Version = 20.x
- [ ] Environment variables added
- [ ] Latest code pushed to GitHub

After deploying, verify:

- [ ] Build completes successfully
- [ ] Homepage loads
- [ ] Other pages load (no 404)
- [ ] Images load correctly
- [ ] Console has no errors (F12)
- [ ] API calls work (check Network tab)

---

## 🎉 SUCCESS CRITERIA

Deployment is successful when:

✅ All pages load without 404 errors
✅ Navigation between pages works
✅ Images display correctly
✅ No console errors
✅ API calls reach backend (even if they fail due to CORS, the URL should be correct)

---

## 📝 FINAL NOTES

**Vercel Auto-Deploy:**
- Pushes to `master` = automatic production deploy
- Pull requests = automatic preview deploys

**Custom Domain (Optional):**
1. Vercel → Settings → Domains
2. Add your domain
3. Configure DNS records
4. Update backend CORS settings

**Performance:**
- Vercel CDN = automatic
- Image optimization = automatic
- Edge caching = automatic
- No configuration needed!

---

**Follow this guide step-by-step and frontend will be 100% functional!** 🚀
