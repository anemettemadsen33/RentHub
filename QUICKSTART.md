# 🚀 Quick Start Guide - RentHub

## ⚡ Pornire Rapidă (5 minute)

### 1️⃣ Backend (Laravel)

```bash
# Navighează în directorul backend
cd backend

# Instalează dependențele
composer install

# Configurează environment
cp .env.example .env

# Generează cheia aplicației
php artisan key:generate

# Rulează migrațiile
php artisan migrate --seed

# Link storage
php artisan storage:link

# Pornește serverul
php artisan serve
```

✅ Backend disponibil la: `http://localhost:8000`
✅ Admin panel (Filament): `http://localhost:8000/admin`

**Credențiale admin** (dacă ai seeder):
- Email: `admin@renthub.com`
- Password: `password`

---

### 2️⃣ Frontend (Next.js)

**Windows (PowerShell):**
```powershell
cd frontend
.\setup.ps1
npm run dev
```

**Linux/Mac/Manual:**
```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

✅ Frontend disponibil la: `http://localhost:3000`

---

## 🔧 Configurare `.env.local` (Frontend)

Editează `frontend/.env.local`:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

---

## 🎯 Testare Rapidă

### Testează Backend API:
```bash
curl http://localhost:8000/api/v1/properties
```

### Testează Frontend:
1. Deschide `http://localhost:3000`
2. Click pe "Sign Up" → Creează cont
3. Login
4. Navighează prin aplicație

---

## 🐛 Probleme Comune

### Backend nu pornește?
```bash
# Verifică dacă portul 8000 e ocupat
# Windows:
netstat -ano | findstr :8000

# Linux/Mac:
lsof -i :8000

# Folosește alt port:
php artisan serve --port=8001
```

### Frontend nu pornește?
```bash
# Șterge node_modules și reinstalează
rm -rf node_modules
npm install

# Șterge cache Next.js
rm -rf .next
npm run dev
```

### CORS Errors?
Backend `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
'supports_credentials' => true,
```

---

## 📱 Structura Aplicației

```
http://localhost:3000/              → Homepage
http://localhost:3000/properties    → Lista proprietăți
http://localhost:3000/auth/login    → Login
http://localhost:3000/auth/register → Register
http://localhost:3000/dashboard     → Dashboard (autentificat)

http://localhost:8000/api/v1/       → API Backend
http://localhost:8000/admin         → Filament Admin
```

---

## ✅ Checklist Pornire

- [ ] PHP 8.2+ instalat
- [ ] Composer instalat
- [ ] Node.js 18+ instalat
- [ ] Database (MySQL/PostgreSQL) pornit
- [ ] Redis pornit (opțional)
- [ ] Backend `.env` configurat
- [ ] Frontend `.env.local` configurat
- [ ] Backend rulează pe port 8000
- [ ] Frontend rulează pe port 3000

---

## 🎓 Următorii Pași

1. **Explorează aplicația**
   - Creează un cont
   - Navighează prin proprietăți
   - Testează dashboard-ul

2. **Citește documentația**
   - `frontend/SETUP_COMPLETE.md` - Setup complet frontend
   - `frontend/DEPLOYMENT.md` - Deployment guide
   - `backend/openapi.yaml` - API documentation

3. **Personalizează**
   - Adaugă propriile proprietăți
   - Modifică stilurile
   - Extinde funcționalitatea

---

## 🚀 Deploy în Producție

### Frontend → Vercel
```bash
cd frontend
vercel
```

### Backend → Laravel Forge
Vezi `frontend/DEPLOYMENT.md` pentru ghid complet.

---

## 💡 Tips & Tricks

- **Hot reload**: Ambele servere au hot reload activat
- **Debug mode**: Verifică console-ul browser-ului pentru erori
- **API Testing**: Folosește Postman sau Thunder Client
- **Database**: Folosește TablePlus, DBeaver sau phpMyAdmin

---

## 📞 Need Help?

- 📖 Vezi `README.md` în directorul principal
- 📖 Vezi `frontend/SETUP_COMPLETE.md` pentru detalii frontend
- 📖 Vezi `backend/DEPLOYMENT.md` pentru detalii backend
- 🐛 Verifică [GitHub Issues](https://github.com/yourusername/renthub/issues)

---

**Succes! 🎉**
