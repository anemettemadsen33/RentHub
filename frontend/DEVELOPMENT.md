# 🚀 RentHub - Development Guide

## ⚠️ Backend Configuration

### Option 1: Use Mock Data (Current Setup)
Frontend-ul va funcționa fără backend folosind **mock data** automat.

**Features disponibile cu mock data:**
- ✅ Browse properties (6 properties demo)
- ✅ Search & filters
- ✅ Favorites/Wishlist (localStorage)
- ✅ View modes (Grid/List/Map)
- ✅ Sort options
- ✅ All UI components

**Limitări:**
- ❌ Nu poți face login/register
- ❌ Nu poți crea bookings
- ❌ Nu poți adăuga properties
- ❌ Nu poți lăsa reviews

---

### Option 2: Start Laravel Backend

#### 1. Pornește Laravel Backend
```bash
cd C:\laragon\www\RentHub\backend
php artisan serve
```

Backend va rula pe: `http://localhost:8000`

#### 2. Configurare Database (Dacă este prima dată)
```bash
# Copiază .env.example
cp .env.example .env

# Generează app key
php artisan key:generate

# Rulează migrații
php artisan migrate

# (Opțional) Seed database cu date demo
php artisan db:seed
```

#### 3. API Endpoints Disponibile
```
POST   /api/v1/register       - Înregistrare user
POST   /api/v1/login          - Login user
POST   /api/v1/logout         - Logout user
GET    /api/v1/user           - User curent
GET    /api/v1/properties     - Lista properties
GET    /api/v1/properties/:id - Detalii property
POST   /api/v1/bookings       - Creare booking
GET    /api/v1/bookings       - Lista bookings
...
```

---

## 🔧 Frontend Configuration

### Environment Variables
Fișierul `.env.local` este deja configurat:
```bash
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

### Pornire Frontend
```bash
cd C:\laragon\www\RentHub\frontend
npm run dev
```

Frontend va rula pe: `http://localhost:3000`

---

## 🎯 Current Status

### ✅ Funcționează FĂRĂ Backend:
- Homepage
- Properties page (cu 6 mock properties)
- Favorites/Wishlist
- All filters & search
- Property cards
- UI components

### ⚠️ Necesită Backend:
- Login/Register
- Create bookings
- Add properties
- Reviews system
- User profile updates
- Messages

---

## 📝 Development Workflow

### Pentru UI Development (Fără Backend)
1. Pornește doar frontend: `npm run dev`
2. Mock data va fi folosit automat
3. Testează UI, filters, search, favorites

### Pentru Full-Stack Development (Cu Backend)
1. Pornește backend: `cd backend && php artisan serve`
2. Pornește frontend: `cd frontend && npm run dev`
3. Login/Register va funcționa
4. Toate features vor fi disponibile

---

## 🐛 Troubleshooting

### Error: "Network Error" în Console
**Cauză:** Backend-ul nu rulează
**Soluție:** 
- Frontend va folosi mock data automat
- SAU pornește backend-ul cu `php artisan serve`

### Error: "CORS"
**Soluție:** Adaugă în backend `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
```

### Error: "401 Unauthorized"
**Soluție:** Token expirat - logout și login din nou

---

## 📊 Mock Data Info

**6 Properties Demo:**
1. Luxury Downtown Apartment - $150/night (New York)
2. Cozy Beach House - $280/night (Miami)
3. Modern Studio - $89/night (San Francisco)
4. Spacious Villa with Pool - $450/night (Los Angeles)
5. Charming City Loft - $195/night (Chicago)
6. Mountain Cabin Retreat - $175/night (Aspen)

**Features:**
- Different property types
- Various price ranges
- Multiple amenities
- Different ratings
- Real Unsplash images

---

## 🚀 Next Steps

1. **Pentru testing UI:** Continuă cu mock data
2. **Pentru testing API:** Pornește backend Laravel
3. **Pentru production:** Deploy backend pe Laravel Forge, frontend pe Vercel

---

Creat: ${new Date().toLocaleDateString()}
