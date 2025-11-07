# RentHub - Server Start Guide

## Problema Actuală

Backend-ul Laravel are probleme cu Composer dependencies. Aici sunt soluțiile:

## Soluție 1: Folosirea Laragon (RECOMANDAT)

### Pasul 1: Pornește Laragon
1. Deschide Laragon
2. Click pe "Start All"
3. Apache și MySQL vor porni automat

### Pasul 2: Configurează Frontend
Actualizează `frontend/.env.local`:
```env
NEXT_PUBLIC_API_URL=http://localhost/RentHub/backend/public
```

### Pasul 3: Pornește Frontend
```powershell
cd frontend
npm run dev
```

Frontend va rula pe: **http://localhost:3000**
Backend va rula pe: **http://localhost/RentHub/backend/public**

## Soluție 2: Standalone Servers

### Pasul 1: Fix Backend Dependencies

Rulează în terminal în directorul `backend`:
```powershell
cd backend

# Remove vendor if problematic
Remove-Item vendor -Recurse -Force -ErrorAction SilentlyContinue

# Install dependencies
composer install --ignore-platform-reqs

# Generate APP_KEY
php artisan key:generate

# Run migrations
php artisan migrate --seed
```

### Pasul 2: Pornește Backend
```powershell
# Option A: Laravel Artisan (prefer this)
php artisan serve --port=8001

# Option B: PHP Built-in server
php -S localhost:8001 -t public
```

### Pasul 3: Configurează Frontend
Actualizează `frontend/.env.local`:
```env
NEXT_PUBLIC_API_URL=http://localhost:8001
```

### Pasul 4: Pornește Frontend
```powershell
cd frontend
npm run dev
```

## Soluție 3: Docker (Cel Mai Ușor)

```powershell
# Build și pornește toate serviciile
docker-compose up -d

# Verifică statusul
docker-compose ps
```

Frontend: **http://localhost:3000**
Backend: **http://localhost:8000**

## Troubleshooting

### Backend returnează 500 Error

**Cauză**: `APP_KEY` lipsește sau Composer dependencies nu sunt instalate

**Soluție**:
```powershell
cd backend

# Check .env file
cat .env | Select-String "APP_KEY"

# If APP_KEY is empty, generate it
php artisan key:generate

# Or manually set it
$key = "base64:" + [Convert]::ToBase64String([byte[]]@(1..32 | ForEach-Object { Get-Random -Maximum 256 }))
(Get-Content .env) -replace "APP_KEY=.*", "APP_KEY=$key" | Set-Content .env
```

### Composer Hangs/Freezes

**Soluție**:
```powershell
# Kill all composer processes
Stop-Process -Name "composer" -Force -ErrorAction SilentlyContinue

# Clear Composer cache
composer clear-cache

# Try install again with timeout
composer install --prefer-dist --no-dev --optimize-autoloader
```

### Frontend Can't Connect to Backend

**Check**:
1. Backend este pornit și răspunde la requests
2. `.env.local` are URL-ul corect
3. CORS este configurat în backend

**Test Backend**:
```powershell
# Test cu curl
curl http://localhost:8001/api/properties

# Sau cu PowerShell
Invoke-WebRequest -Uri "http://localhost:8001/api/properties" -UseBasicParsing
```

### Port Already in Use

**Check porturile**:
```powershell
# Check port 3000 (Frontend)
netstat -ano | findstr ":3000"

# Check port 8001 (Backend)
netstat -ano | findstr ":8001"

# Kill process by PID
Stop-Process -Id <PID> -Force
```

## Current Configuration

### Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=http://localhost/RentHub/backend/public
NEXT_PUBLIC_SITE_URL=http://localhost:3000
NODE_ENV=development
```

### Backend (.env)
```env
APP_NAME=RentHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001
DB_CONNECTION=sqlite
```

## Recommended Setup for Development

1. **Use Laragon** pentru backend (Apache + MySQL)
   - Mai stabil
   - Nu necesită pornire manuală
   - Auto-reload când salvezi fișiere

2. **Use npm run dev** pentru frontend
   - Hot reload
   - Fast refresh
   - Better debugging

3. **Configure CORS** în backend (`config/cors.php`):
```php
'allowed_origins' => ['http://localhost:3000'],
```

## Quick Commands

### Start Everything (Cu Laragon)
```powershell
# 1. Start Laragon (GUI)
# 2. Start Frontend
cd frontend && npm run dev
```

### Start Everything (Standalone)
```powershell
# Terminal 1 - Backend
cd backend && php artisan serve --port=8001

# Terminal 2 - Frontend  
cd frontend && npm run dev
```

### Stop Everything
```powershell
# Press Ctrl+C in each terminal

# Or kill all node/php processes
Stop-Process -Name "node" -Force
Stop-Process -Name "php" -Force
```

## Next Steps

1. ✅ Fix Composer autoload issue
2. ✅ Set APP_KEY in .env
3. ✅ Configure frontend .env.local
4. ⏳ Start backend server
5. ⏳ Test API endpoints
6. ⏳ Start frontend
7. ⏳ Test full application

## Need Help?

- Check Laravel logs: `backend/storage/logs/laravel.log`
- Check Frontend console: Browser DevTools > Console
- Check Network tab: Browser DevTools > Network
- Backend API docs: http://localhost:8001/api/documentation
