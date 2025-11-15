# 🔴 TASK 2: Configurare Backend API Connection

**Status:** IN PROGRESS  
**Deadline:** Înainte de deployment Luni  
**Estimated Time:** 10 minutes

---

## ✅ Progress Checklist

### Pas 1: Verificare Backend Laravel (DONE ✅)

```powershell
cd C:\laragon\www\RentHub\backend
php artisan serve
```

**Rezultat:**
- ✅ Backend rulează pe `http://127.0.0.1:8000`
- ✅ API Routes verificate: 76 rute pentru properties
- ✅ Process ID: 16552

**Test Endpoint:**
```powershell
curl http://localhost:8000/api/v1/properties
```

---

### Pas 2: Configurare Frontend .env.local (DONE ✅)

**Fișier:** `C:\laragon\www\RentHub\frontend\.env.local`

```env
# API Configuration (LOCAL)
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_FRONTEND_URL=http://localhost:3000
NEXT_PUBLIC_SITE_URL=http://localhost:3000

# Application Info
NEXT_PUBLIC_APP_NAME="RentHub"
NEXT_PUBLIC_APP_ENV=local

# Real-time (Local WebSocket)
NEXT_PUBLIC_PUSHER_APP_KEY=local
NEXT_PUBLIC_PUSHER_APP_CLUSTER=mt1
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=6001
NEXT_PUBLIC_REVERB_SCHEME=http
NEXT_PUBLIC_WEBSOCKET_URL=ws://localhost:6001
NEXT_PUBLIC_WEBSOCKET_ENABLED=true

# Stripe Test Keys
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_test_51QJlCEP24vDgZqFZ9y1vBeCpZW0BIWp9H0S6TqQBHdRpN5lrD3QxRZlEVxnpHDJBLPxnbwR0BflFdaziA9KvbRB900QNKt0xFd

# Feature Flags (Development)
NEXT_PUBLIC_ENABLE_ANALYTICS=false
NEXT_PUBLIC_ENABLE_SENTRY=false
NEXT_PUBLIC_ENABLE_PWA=true
```

**Status:** ✅ ACTUALIZAT

---

### Pas 3: Pornire Frontend Next.js (IN PROGRESS 🔄)

**Problema detectată:** Turbopack deprecated warning

**Soluție - Rulează FĂRĂ Turbopack:**

```powershell
cd C:\laragon\www\RentHub\frontend
npm run dev -- --no-turbo
```

**SAU editează package.json:**

```json
{
  "scripts": {
    "dev": "next dev",
    "dev:turbo": "next dev --turbopack",
    "build": "next build",
    "start": "next start"
  }
}
```

Apoi:
```powershell
npm run dev
```

---

### Pas 4: Test API Connection (PENDING ⏳)

După ce Next.js pornește pe `http://localhost:3000`:

**Test 1: Homepage**
- Deschide browser: `http://localhost:3000`
- Verifică că se încarcă fără erori console

**Test 2: Properties Page**
- Navigate to: `http://localhost:3000/properties`
- Verifică console pentru API calls:
  - ✅ Request: `GET http://localhost:8000/api/v1/properties`
  - ✅ Response: 200 OK cu date

**Test 3: Property Detail**
- Navigate to: `http://localhost:3000/properties/1`
- Verifică detalii proprietate se încarcă

**Test 4: Network Tab**
Open Chrome DevTools → Network:
- Filter: `XHR`
- Refresh page
- Verifică requests la `localhost:8000`

---

## 🔧 Troubleshooting

### Error: "CORS Policy"

**Backend Fix:**
```php
// backend/config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

### Error: "Failed to fetch"

**Verifică:**
1. Backend rulează: `netstat -ano | findstr :8000`
2. .env.local corect: `NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1`
3. apiClient configurare în `frontend/src/lib/api-client.ts`

### Error: "Network Error"

**Fix temporar:**
```typescript
// frontend/src/lib/api-client.ts
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  timeout: 30000,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});
```

---

## 📋 Final Verification Checklist

După finalizare, verifică:

- [ ] Backend Laravel: `http://localhost:8000` ✅ funcționează
- [ ] Frontend Next.js: `http://localhost:3000` ⏳ pornește
- [ ] API Connection: Requests din frontend → backend funcționează
- [ ] CORS configurare: Nu apar erori CORS în console
- [ ] Test Properties: `/properties` page încarcă date
- [ ] Test Booking: Create booking funcționează
- [ ] Console Errors: Zero erori critice

---

## 🎯 Next Steps După Finalizare

După ce Task 2 e completat:
1. ✅ Mark task as completed
2. 🔄 Move to Task 3: WebSocket Server
3. 📝 Document any issues encountered

---

## ⚡ Quick Start Commands

**Terminal 1 (Backend):**
```powershell
cd C:\laragon\www\RentHub\backend
php artisan serve
```

**Terminal 2 (Frontend):**
```powershell
cd C:\laragon\www\RentHub\frontend
npm run dev -- --no-turbo
```

**Terminal 3 (Database - Optional):**
```powershell
cd C:\laragon\www\RentHub\backend
php artisan queue:work
```

---

## 📊 Status Summary

| Component | Port | Status | URL |
|-----------|------|--------|-----|
| Backend API | 8000 | ✅ RUNNING | http://localhost:8000 |
| Frontend Next.js | 3000 | ⏳ STARTING | http://localhost:3000 |
| Database | 3306 | ✅ READY | MySQL (Laragon) |
| Redis | 6379 | ⚠️ OPTIONAL | For caching |

**Overall Progress:** 75% Complete (3/4 steps done)

---

## ✅ Task Completion

Marchează task-ul complet când:
- [x] Backend server runs on port 8000
- [x] Frontend .env.local configured
- [ ] Frontend server runs on port 3000
- [ ] API requests succeed (test în browser)

**Status:** 🔄 IN PROGRESS  
**Next Action:** Pornește Next.js fără Turbopack
