# ⚡ QUICK START GUIDE - RentHub Issues & Solutions

**Scop**: Răspunsuri rapide la întrebări comune  
**Format**: Q&A cu acțiuni concrete  
**Data**: 7 Noiembrie 2025

---

## 🎯 CELE MAI FRECVENTE ÎNTREBĂRI

### Q1: Care e starea proiectului? Îl pot lansa acum?
**A**: ✅ **85% ready** - Puteți lansa în 2-3 săptămâni cu muncă concentrată.

**Ce mai lipsește**:
- [ ] PostgreSQL setup (vs current SQLite) - 3 zile
- [ ] Environment variables production - 1 zi
- [ ] External services integration - 2-3 zile
- [ ] Full testing - 2 zile

**Action**: Citiți PLAN_ACTIUNE_CONCRET.md și urmați phases

---

### Q2: Ce e problema cu SQLite?
**A**: SQLite nu merge în producție cu multiple users simultani.

**Problema**:
```
SQLite file-based → Lock contention
10+ users → Timeouts & crashes
Concurrent requests → Race conditions
```

**Soluție**:
```bash
# 1. Setup PostgreSQL
docker run -d --name postgres postgres:16-alpine

# 2. Migrate data
cd backend
php artisan migrate

# 3. Verify
php artisan tinker
>>> DB::table('users')->count()
```

**Timeline**: 3 zile  
**Ressurse**: 1 inginer backend

---

### Q3: De ce nu merge API-ul?
**A**: Probabil datorită:

1. **Backend not running**
   ```bash
   cd backend
   php artisan serve
   ```

2. **Database error**
   ```bash
   php artisan migrate:status
   php artisan migrate
   ```

3. **Missing .env**
   ```bash
   cp backend/.env.example backend/.env
   php artisan key:generate
   ```

4. **CORS issue**
   ```bash
   # Check config/cors.php
   # Whitelist your frontend URL
   ```

**Quick Fix**:
```bash
cd backend
php artisan serve
# Then try: curl http://localhost:8000/api/v1/properties
```

---

### Q4: Frontend nu se conectează la backend?
**A**: Problema e probabil în URL-ul API.

**Fix**:
```bash
# File: frontend/.env.local
NEXT_PUBLIC_API_URL=http://localhost:8000

# Or production:
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
```

**Test**:
```bash
cd frontend
npm run dev
# Deschide: http://localhost:3000
# Verific console pentru API errors
```

---

### Q5: Care sunt serviciile externe necesare?
**A**: 8 servicii externe trebuie configurate:

| Serviciu | De ce | Configurare |
|----------|-------|-------------|
| **Stripe** | Payments | 2 ore |
| **SendGrid** | Email | 1 oră |
| **Google OAuth** | Social login | 1 oră |
| **Facebook OAuth** | Social login | 1 oră |
| **GitHub OAuth** | Social login | 1 oră |
| **AWS S3** | File storage | 1 oră |
| **Twilio** | SMS | 1 oră |
| **Mapbox** | Maps | 30 min |

**Total effort**: 10 ore  
**Total cost**: $50-500/lună

---

### Q6: De ce nu merge autentificarea?
**A**: Probabil NextAuth.js nu e configurat corect.

**Fix**:
```bash
# File: frontend/.env.local
NEXTAUTH_URL=http://localhost:3000
NEXTAUTH_SECRET=any-random-string-here

# File: backend/.env
SANCTUM_STATEFUL_DOMAINS=localhost
```

**Test**:
```bash
# 1. Try to login at http://localhost:3000/login
# 2. Check browser console for errors
# 3. Check API response: curl -v http://localhost:8000/api/v1/auth/user
```

---

### Q7: Cum setez baza de date pentru producție?
**A**: Folosiți PostgreSQL hosted (Render, AWS, etc.)

```bash
# File: backend/.env (production)
DB_CONNECTION=pgsql
DB_HOST=db.render.com
DB_PORT=5432
DB_DATABASE=renthub
DB_USERNAME=postgres
DB_PASSWORD=***SECURE***

# Test connection:
php artisan migrate --force
```

**Providers recomandate**:
- Render (gratuit - $7/lună)
- AWS RDS ($15+/lună)
- DigitalOcean ($15+/lună)
- PlanetScale ($24+/lună)

---

### Q8: Cum implementez monitoring?
**A**: Setup Sentry (ușor și gratuit)

```bash
# 1. Create account: https://sentry.io/
# 2. Create project: Laravel
# 3. Get DSN: https://***@sentry.io/***
# 4. Add to .env:
SENTRY_LARAVEL_DSN=https://***@sentry.io/***

# 5. Install:
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"

# 6. Test:
php artisan tinker
>>> throw new Exception("Test");
>>> exit

# 7. Check Sentry dashboard
```

**Cost**: Gratuit până la 5000 errors/lună

---

### Q9: Cum testez că totul funcționează?
**A**: Folosiți checklist de testing:

```bash
# Backend
✅ php artisan --version
✅ php artisan migrate:status
✅ php artisan route:list | grep api
✅ curl http://localhost:8000/api/v1/properties

# Frontend
✅ npm run build (no errors)
✅ npm run dev (starts on 3000)
✅ Browser: http://localhost:3000

# Integration
✅ Login works
✅ Can see properties
✅ Search works
✅ Booking flow works
```

---

### Q10: Cum implementez caching pentru performance?
**A**: Setup Redis caching:

```bash
# 1. Setup Redis
docker run -d -p 6379:6379 redis:7

# 2. Configure:
# File: backend/.env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# 3. Use in code:
Cache::remember('properties', 3600, function() {
    return Property::all();
});

# 4. Clear cache:
php artisan cache:clear
```

**Expected**: 5-10x speed improvement

---

## ⚡ QUICK TROUBLESHOOTING

### "502 Bad Gateway"
```
→ Backend not running
→ Or CORS issue
→ Check: php artisan serve
→ Check: config/cors.php
```

### "CORS Error in Console"
```
→ Frontend URL not whitelisted
→ Fix: config/cors.php
→ Add your URL to whitelist
```

### "Database connection error"
```
→ SQLite locked or corrupted
→ Migrate to PostgreSQL
→ Or: remove database.sqlite and run migrate
```

### "NextAuth session not working"
```
→ Missing NEXTAUTH_SECRET
→ Missing NEXTAUTH_URL
→ Backend sanctum not configured
→ Check: /auth/callback route
```

### "Images not uploading"
```
→ Storage not configured
→ Or AWS S3 credentials missing
→ Check: php artisan storage:link
→ Or: configure S3 in .env
```

### "Emails not sending"
```
→ SendGrid API key missing
→ Or wrong email driver
→ Fix: MAIL_DRIVER=sendgrid
→ Add: SENDGRID_API_KEY=***
```

### "Payments failing"
```
→ Stripe keys missing
→ Or using sandbox credentials
→ For production: Use live keys
→ Test flow in Stripe dashboard
```

---

## 🔧 QUICK FIXES

### Fix 1: Clear Everything
```bash
cd backend
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

### Fix 2: Reset Database
```bash
cd backend
php artisan migrate:refresh --seed
```

### Fix 3: Reinstall Dependencies
```bash
cd backend
composer install

cd ../frontend
npm install
```

### Fix 4: Check Logs
```bash
# Backend
tail -f backend/storage/logs/laravel.log

# Frontend (console)
# Open browser DevTools → Console tab
```

### Fix 5: Verify Ports
```bash
# Check if ports are in use
netstat -an | grep LISTEN | grep -E "3000|8000|5432|6379"

# If occupied, kill process:
# macOS/Linux: kill -9 PID
# Windows: taskkill /PID PID /F
```

---

## 📋 PRE-LAUNCH CHECKLIST

### 24 Hours Before Launch
- [ ] Database backed up
- [ ] All environment variables set
- [ ] External services tested
- [ ] Load testing passed
- [ ] Error tracking active
- [ ] SSL certificate ready
- [ ] Team on-call
- [ ] Rollback plan ready

### Day of Launch
- [ ] Create pre-launch backup
- [ ] Deploy backend
- [ ] Deploy frontend
- [ ] Run migrations
- [ ] Clear caches
- [ ] Verify critical paths
- [ ] Monitor error logs
- [ ] Keep team standing by

### After Launch (First 48 Hours)
- [ ] Monitor error rate
- [ ] Check response times
- [ ] Watch user feedback
- [ ] Monitor database
- [ ] Keep team available
- [ ] Prepare for quick fixes

---

## 💰 QUICK COST ESTIMATE

### Development Costs
```
Backend Engineer:    2 weeks × 40h = 80h
DevOps Engineer:     2 weeks × 40h = 80h
QA:                  1 week × 40h  = 40h
────────────────────────────────────
TOTAL: 200 hours
COST: $200 - $20,000 (depending on rates)
```

### Monthly Operating Costs
```
Database (PostgreSQL):     $20-100
Cache (Redis):             $10-50
Email (SendGrid):          $20-100
Monitoring (Sentry):       $0-500
Storage (S3):              $5-50
CDN (CloudFront):          $5-50
Hosting (Forge/Vercel):    $50-300
────────────────────────────────
TOTAL: $110-1150/month
```

---

## 🆘 NEED HELP?

### Problems & Solutions

| Problem | Solution | Time |
|---------|----------|------|
| SQLite slow | Migrate to PostgreSQL | 3 days |
| No monitoring | Setup Sentry | 1 day |
| Missing emails | Configure SendGrid | 2 hours |
| Payments failing | Setup Stripe | 3 hours |
| Slow API | Add Redis caching | 2 hours |
| Can't login | Fix NextAuth | 2 hours |
| Images broken | Setup S3 storage | 2 hours |

---

## 📖 FURTHER READING

For complete details, see:
- **Overview**: ANALIZA_COMPLETA.md
- **Technical**: ANALIZA_TEHNICA_DETALIATA.md
- **Tasks**: PLAN_ACTIUNE_CONCRET.md
- **Executive**: REZUMAT_EXECUTIV.md

---

## ✅ DONE!

You now have:
- ✅ Quick understanding of issues
- ✅ Fast solutions for common problems
- ✅ Clear next steps
- ✅ Resource estimates

**Next**: Pick a task from PLAN_ACTIUNE_CONCRET.md and start executing!

---

**Last updated**: 7 Noiembrie 2025  
**Status**: Ready to deploy ✅
