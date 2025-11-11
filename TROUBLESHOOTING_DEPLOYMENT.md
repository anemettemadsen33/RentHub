# 🔧 Deployment Troubleshooting Guide

## 🔴 COMMON FORGE (Backend) ERRORS

### Error 1: "Composer install failed"
**Cause**: Missing dependencies or wrong PHP version

**Fix:**
```bash
# SSH into server
cd /home/forge/YOUR_SITE/backend

# Check PHP version
php -v  # Should be 8.3

# Try manual install
composer install --no-interaction --prefer-dist
```

### Error 2: "Class not found" or "Namespace error"
**Cause**: Autoloader not refreshed after API→Api rename

**Fix:**
```bash
cd /home/forge/YOUR_SITE/backend
composer dump-autoload
php artisan cache:clear
php artisan config:clear
```

### Error 3: "Database connection failed"
**Cause**: Wrong DB credentials in .env

**Fix:**
```bash
# Check database credentials
cd /home/forge/YOUR_SITE/backend
cat .env | grep DB_

# Should match Forge database settings:
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=[from Forge DB tab]
```

### Error 4: "Storage/logs not writable"
**Cause**: Permission issues

**Fix:**
```bash
cd /home/forge/YOUR_SITE/backend
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R forge:forge storage
chown -R forge:forge bootstrap/cache
```

### Error 5: "APP_KEY not set"
**Cause**: Missing application key

**Fix:**
```bash
cd /home/forge/YOUR_SITE/backend
php artisan key:generate --force
```

---

## 🔴 COMMON VERCEL (Frontend) ERRORS

### Error 1: "Module not found" during build
**Cause**: Missing dependencies in package.json

**Fix in Vercel:**
- Go to Settings → Environment Variables
- Make sure `NODE_ENV=production`
- Redeploy

**Or update locally:**
```bash
cd frontend
npm install
git add package-lock.json
git commit -m "Update dependencies"
git push
```

### Error 2: "NEXT_PUBLIC_API_URL is undefined"
**Cause**: Environment variables not set or wrong prefix

**Fix:**
- Go to Vercel → Settings → Environment Variables
- Verify all variables start with `NEXT_PUBLIC_`
- After adding/changing env vars, **redeploy**

Required variables:
```
NEXT_PUBLIC_API_URL=https://api.your-domain.com
NEXT_PUBLIC_API_BASE_URL=https://api.your-domain.com/api/v1
NODE_ENV=production
```

### Error 3: "Build timed out"
**Cause**: Build taking too long

**Fix:**
```bash
# Optimize build locally first
cd frontend
npm run build

# If it works locally, the issue is Vercel timeout
# Upgrade Vercel plan or optimize bundle size
```

### Error 4: "Type error: Cannot find module"
**Cause**: TypeScript errors

**Fix:**
```bash
cd frontend
npm run type-check

# Fix any TypeScript errors shown
# Then push to GitHub
```

### Error 5: "Failed to load SWC binary"
**Cause**: Platform-specific build issue

**Fix in Vercel:**
- Settings → General → Node.js Version
- Try Node.js **20.x** (recommended)
- Redeploy

---

## 🔴 INTEGRATION ERRORS

### Error 1: "CORS policy blocked"
**Browser Console**: `Access-Control-Allow-Origin`

**Fix in Backend .env:**
```env
FRONTEND_URL=https://your-app.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app
SESSION_DOMAIN=.your-domain.com
```

**Then:**
```bash
# SSH to Forge
cd /home/forge/YOUR_SITE/backend
php artisan config:cache
```

### Error 2: "CSRF token mismatch"
**Cause**: Wrong session configuration

**Fix in Backend .env:**
```env
SESSION_DRIVER=redis
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app
```

### Error 3: "Failed to fetch" on API calls
**Cause**: Backend not accessible or wrong URL

**Check:**
1. Backend is running: `curl https://api.your-domain.com/api/health`
2. SSL is active (https://)
3. Vercel has correct `NEXT_PUBLIC_API_URL`

---

## 📋 QUICK DIAGNOSTIC COMMANDS

### Forge (SSH into server):
```bash
# Check PHP version
php -v

# Check Composer
composer --version

# Check database
mysql -u forge -p forge

# Check Laravel
cd /home/forge/YOUR_SITE/backend
php artisan --version

# Check logs
tail -50 storage/logs/laravel.log

# Check permissions
ls -la storage/

# Test artisan
php artisan route:list --json
```

### Vercel (Local):
```bash
cd frontend

# Check build locally
npm run build

# Check types
npm run type-check

# Check lint
npm run lint

# Test production build
npm run start
```

---

## 🆘 EMERGENCY FIXES

### Backend won't deploy? Reset everything:
```bash
# SSH into Forge server
cd /home/forge/YOUR_SITE/backend

# Nuclear option - clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer clear-cache
composer dump-autoload
rm -rf bootstrap/cache/*
php artisan optimize
```

### Frontend won't build? Force clean:
```bash
cd frontend
rm -rf .next
rm -rf node_modules
rm package-lock.json
npm install
npm run build
```

---

## 📊 DEBUGGING CHECKLIST

### Backend Checklist:
- [ ] PHP version is 8.3
- [ ] Composer install succeeded
- [ ] .env file has correct credentials
- [ ] APP_KEY is generated
- [ ] Database migrations ran
- [ ] Storage permissions correct (775)
- [ ] Redis is running
- [ ] Queue worker is active
- [ ] SSL certificate is installed

### Frontend Checklist:
- [ ] Build succeeds locally
- [ ] No TypeScript errors
- [ ] All env vars start with NEXT_PUBLIC_
- [ ] Environment variables saved in Vercel
- [ ] Redeployed after adding env vars
- [ ] Root directory set to "frontend"
- [ ] Build command is "npm run build"
- [ ] Node version is 20.x

---

## 🔍 HOW TO SHARE ERRORS WITH ME

To help you faster, please provide:

**For Forge errors:**
```
1. Copy the deployment log (last 50 lines)
2. Share any error messages in red
3. Tell me at what step it fails
```

**For Vercel errors:**
```
1. Go to your deployment → View Build Logs
2. Copy the error section
3. Share the exact error message
```

**Screenshots:**
- Forge: Deployment log screen
- Vercel: Build log screen
- Browser: Console errors (F12)

---

## 💡 MOST LIKELY ISSUES

Based on our codebase:

**90% chance it's one of these:**

1. **Forge**: Wrong deploy path
   - Should be: `/backend` (not root)
   - Fix in Forge → Git → Deploy Path

2. **Forge**: Database not migrated
   - SSH in and run: `php artisan migrate --force`

3. **Vercel**: Environment variables not set
   - Must redeploy after adding env vars

4. **Vercel**: Root directory not set to "frontend"
   - Fix in Settings → General → Root Directory

5. **Both**: CORS misconfiguration
   - Backend .env needs correct FRONTEND_URL

---

**Now, please share your specific errors and I'll help you fix them!** 🚀
