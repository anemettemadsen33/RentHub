# 🚀 RentHub - Quick Start Guide

**Ultima actualizare:** 2025-11-15  
**Timp estimat:** 2 minute

---

## ⚡ START RAPID (Metoda Automată)

### Windows - Double-click:

**Batch Script (CMD):**
```
start-dev.bat
```

**PowerShell Script:**
```powershell
.\start-dev.ps1
```

Scriptul va porni automat:
1. ✅ Backend Laravel (port 8000)
2. ✅ Reverb WebSocket (port 8080)
3. ✅ Frontend Next.js (port 3000)
4. ✅ Browser pe `http://localhost:3000`

---

## 🔧 START MANUAL (Pas cu Pas)

### Pasul 1: Backend Laravel

**Terminal 1:**
```powershell
cd C:\laragon\www\RentHub\backend
php artisan serve
```

**Verificare:**
- ✅ Mesaj: `Laravel development server started: http://127.0.0.1:8000`
- ✅ Test: `curl http://localhost:8000/api/v1/properties`

---

### Pasul 2: Reverb WebSocket

**Terminal 2:**
```powershell
cd C:\laragon\www\RentHub\backend
php artisan reverb:start
```

**Verificare:**
- ✅ Mesaj: `Starting server on 0.0.0.0:8080`
- ✅ Test: `netstat -ano | findstr :8080` → LISTENING

---

### Pasul 3: Frontend Next.js

**Terminal 3:**
```powershell
cd C:\laragon\www\RentHub\frontend
npm run dev
```

**Verificare:**
- ✅ Mesaj: `Ready in Xms`
- ✅ URL: `http://localhost:3000`

---

## 🧪 VERIFICARE RAPIDĂ

### 1. Backend API
```powershell
curl http://localhost:8000/api/v1/properties
```
**Expected:** JSON cu listă de properties

### 2. WebSocket
```powershell
netstat -ano | findstr :8080
```
**Expected:** `TCP 0.0.0.0:8080 ... LISTENING`

### 3. Frontend
**Browser:** `http://localhost:3000`
- ✅ Homepage se încarcă
- ✅ Console fără erori critice

---

## 📊 MONITORING

### Check Running Services:
```powershell
netstat -ano | Select-String ":8000|:8080|:3000"
```

**Expected Output:**
```
TCP    127.0.0.1:8000    ...    LISTENING    16552
TCP    0.0.0.0:8080      ...    LISTENING    11160
TCP    127.0.0.1:3000    ...    LISTENING    23456
```

---

## 🔴 TROUBLESHOOTING

### Port Already in Use

**Error:** `Address already in use`

**Fix:**
```powershell
# Find process on port 8000
netstat -ano | findstr :8000
# Kill process (replace PID)
taskkill /PID <PID> /F
```

### Backend 500 Error

**Check:**
```powershell
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Frontend Build Error

**Fix:**
```powershell
cd frontend
Remove-Item -Recurse -Force .next
npm run dev
```

### WebSocket Not Connecting

**Verifică:**
1. Reverb rulează: `netstat -ano | findstr :8080`
2. .env.local: `NEXT_PUBLIC_REVERB_HOST=localhost`
3. Browser console pentru errors

---

## 🎯 NEXT STEPS

După pornire, testează:

1. **Homepage:** `http://localhost:3000`
2. **Properties:** `http://localhost:3000/properties`
3. **Property Detail:** `http://localhost:3000/properties/1`
4. **Messages (WebSocket):** `http://localhost:3000/messages`
5. **Dashboard:** `http://localhost:3000/dashboard`

---

## 🔐 TEST ACCOUNTS

**From BookingTestSeeder:**

**Owner:**
- Email: `owner@renthub.test`
- Password: `password123`

**Guest:**
- Email: `guest@renthub.test`
- Password: `password123`

---

## 📝 USEFUL COMMANDS

### Backend:
```powershell
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate:fresh --seed

# Run queue worker
php artisan queue:work

# Tinker (REPL)
php artisan tinker
```

### Frontend:
```powershell
# Build for production
npm run build

# Start production server
npm start

# Lint check
npm run lint

# Type check
npm run type-check
```

---

## 🛑 STOP SERVICES

### Metoda 1: Ctrl+C în fiecare terminal

### Metoda 2: Kill All Processes
```powershell
# Find all RentHub processes
Get-Process | Where-Object {$_.ProcessName -like "*php*" -or $_.ProcessName -like "*node*"}

# Kill specific ports
netstat -ano | findstr ":8000" | ForEach-Object {
    $pid = ($_ -split '\s+')[-1]
    taskkill /PID $pid /F
}
```

---

## ✅ CHECKLIST

După pornire, verifică:

- [ ] Backend: `http://localhost:8000` → ✅ Laravel welcome
- [ ] API: `http://localhost:8000/api/v1/properties` → ✅ JSON response
- [ ] WebSocket: Port 8080 LISTENING
- [ ] Frontend: `http://localhost:3000` → ✅ Homepage
- [ ] Console: Zero erori critice
- [ ] Network tab: API requests succeed (200 OK)

---

**Status:** 🟢 READY pentru development!

**Timp total pornire:** ~30 secunde  
**Services active:** 3/3 ✅
