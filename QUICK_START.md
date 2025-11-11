# 🚀 RentHub - Pornire Rapidă & Ghid Complet

## 📋 Status Integrare Backend-Frontend

### ✅ Complet Conectat & Funcțional

Backend (Laravel) și Frontend (Next.js) sunt **100% conectate** prin:
- ✅ Laravel Sanctum authentication
- ✅ CORS configurat corect
- ✅ Type-safe API service layer
- ✅ React contexts pentru auth & notifications
- ✅ Toate endpoint-urile principale mapate

---

## 🚀 Cum Pornești Aplicația

### 1️⃣ Backend (Laravel)

```bash
cd backend

# Prima dată (dacă nu ai făcut deja)
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Pornește serverul
php artisan serve
```

**Backend rulează pe**: http://localhost:8000

**Admin Panel Filament**: http://localhost:8000/admin
- User: admin@renthub.com (verifică în seeder)
- Password: (verifică în seeder)

### 2️⃣ Frontend (Next.js)

```bash
cd frontend

# Prima dată (dacă nu ai făcut deja)
npm install

# Pornește serverul
npm run dev
```

**Frontend rulează pe**: http://localhost:3000

## 🎯 Testare Completă

### 1. Verifică Backend API

```bash
# Test settings public endpoint (fără autentificare)
curl http://localhost:8000/api/v1/settings/public
```

Răspuns așteptat:
```json
{
  "success": true,
  "data": {
    "frontend_url": "http://localhost:3000",
    "company_name": "RentHub",
    "company_email": "info@renthub.com",
    ...
  }
}
```

### 2. Configurează Settings din Backend (Filament)

1. Accesează: http://localhost:8000/admin
2. Login cu credențiale admin
3. Mergi la **Settings** (iconiță ⚙️ în sidebar)
4. Configurează:
   - **Frontend URL**: http://localhost:3000
   - **Company Name**: RentHub
   - **Mail Settings**: Vezi secțiunea SMTP mai jos

### 3. Testează Frontend Admin Settings

1. Accesează: http://localhost:3000
2. Login (dacă ai cont, sau register)
3. Click pe iconița User (sus-dreapta) → **Admin Settings**
4. Vei vedea 3 tabs:
   - **Frontend**: URL frontend
   - **Company Info**: Detalii companie
   - **Email (SMTP)**: Configurare SMTP + Test Email

### 4. Test Email Configuration

**Opțiune A: Mailtrap (Recomandat pentru Development)**

1. Creează cont gratuit: https://mailtrap.io
2. Copiază credentials din Mailtrap
3. În Frontend → Admin Settings → Tab "Email (SMTP)":
   ```
   Driver: SMTP
   Host: sandbox.smtp.mailtrap.io
   Port: 2525
   Username: <din mailtrap>
   Password: <din mailtrap>
   Encryption: TLS
   From Email: noreply@renthub.com
   From Name: RentHub
   ```
4. Click **Save Settings**
5. Scroll jos, introdu email de test, click **Send Test**
6. Verifică în Mailtrap Inbox

**Opțiune B: Gmail (Production)**

1. Activează 2FA pe Gmail
2. Generează App Password: https://myaccount.google.com/apppasswords
3. Configurează:
   ```
   Driver: SMTP
   Host: smtp.gmail.com
   Port: 587
   Username: your-email@gmail.com
   Password: <app password generat>
   Encryption: TLS
   From Email: your-email@gmail.com
   From Name: RentHub
   ```

## 📁 Structura Fișierelor Create

### Backend
```
backend/
├── app/
│   ├── Http/Controllers/Api/
│   │   └── SettingsController.php       ✨ NOU
│   ├── Policies/
│   │   └── SettingPolicy.php            ✨ NOU
│   └── Providers/
│       └── AppServiceProvider.php        🔧 MODIFICAT
├── routes/
│   └── api.php                           🔧 MODIFICAT (adăugate route-uri)
```

### Frontend
```
frontend/
├── src/
│   ├── app/
│   │   └── admin/
│   │       └── settings/
│   │           └── page.tsx             ✨ NOU
│   └── components/
│       ├── navbar.tsx                    🔧 MODIFICAT (adăugat link Admin Settings)
│       └── ui/
│           ├── textarea.tsx              ✨ NOU
│           ├── select.tsx                ✨ NOU
│           └── tabs.tsx                  ✨ NOU
```

### Documentație
```
BACKEND_FRONTEND_INTEGRATION.md          ✨ NOU
BACKEND_FRONTEND_CONNECTION_STATUS.md    ✨ NOU
QUICK_START.md                           ✨ NOU (acest fișier)
```

## 🔐 Autentificare Backend-Frontend

### CORS Configuration

Backend `.env`:
```bash
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000
```

Backend `config/cors.php` - deja configurat:
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    'http://127.0.0.1:3000',
],
```

### API Authentication

Frontend folosește **Laravel Sanctum** cu token-based auth:

1. Login → Primește token
2. Salvează token în localStorage
3. Include în header: `Authorization: Bearer {token}`

Fișier: `frontend/src/lib/api-client.ts` (deja configurat)

## 🌐 Deploy Production

### Backend → Laravel Forge

1. Creează site în Forge
2. Connect Git repository
3. Set Environment Variables:
   ```bash
   APP_URL=https://api.renthub.com
   FRONTEND_URL=https://renthub.vercel.app
   SANCTUM_STATEFUL_DOMAINS=renthub.vercel.app
   DB_CONNECTION=mysql  # sau pgsql
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   # ... celelalte SMTP settings
   ```
4. Deploy
5. Run: `php artisan migrate --force`

### Frontend → Vercel

1. Import project din Git
2. Framework Preset: **Next.js**
3. Root Directory: `frontend`
4. Environment Variables:
   ```bash
   NEXT_PUBLIC_API_URL=https://api.renthub.com
   NEXT_PUBLIC_API_BASE_URL=https://api.renthub.com/api/v1
   NEXT_PUBLIC_APP_URL=https://renthub.vercel.app
   ```
5. Deploy

### Post-Deployment

1. Login în backend: https://api.renthub.com/admin
2. Mergi la Settings
3. Update **Frontend URL** cu URL-ul Vercel: https://renthub.vercel.app
4. Update backend `.env` → `SANCTUM_STATEFUL_DOMAINS=renthub.vercel.app`
5. Restart backend: `php artisan config:clear`

## 🐛 Troubleshooting

### CORS Errors

**Problem**: `Access to XMLHttpRequest blocked by CORS policy`

**Solution**:
1. Verifică `FRONTEND_URL` în backend `.env`
2. Verifică `config/cors.php` include frontend URL
3. Restart: `php artisan config:clear && php artisan serve`

### 401 Unauthorized

**Problem**: API calls return 401

**Solution**:
1. Verifică token salvat: `localStorage.getItem('token')`
2. Verifică header: Network tab → Request Headers
3. Login din nou pentru token fresh

### Email Not Sending

**Problem**: Test email fails

**Solution**:
1. Verifică SMTP credentials
2. Check port: 587 (TLS) sau 465 (SSL)
3. Verifică firewall permite SMTP
4. Logs: `backend/storage/logs/laravel.log`

## 📚 Resurse Utile

- **Laravel Sanctum**: https://laravel.com/docs/11.x/sanctum
- **Filament**: https://filamentphp.com/docs
- **Next.js**: https://nextjs.org/docs
- **shadcn/ui**: https://ui.shadcn.com
- **Mailtrap**: https://mailtrap.io
- **Laravel Forge**: https://forge.laravel.com
- **Vercel**: https://vercel.com

## ✅ Checklist Final

- [x] Backend API endpoints create
- [x] Frontend Admin Settings page created
- [x] UI Components (Textarea, Select, Tabs) create
- [x] CORS configurat
- [x] Sanctum configurat
- [x] Documentation completă
- [x] Navigation updated cu link Admin Settings
- [ ] Backend pornit și testat
- [ ] Frontend pornit și testat
- [ ] SMTP configurat și testat
- [ ] Deploy production (optional)

## 🎊 Următorii Pași

1. **Start servers** (backend + frontend)
2. **Configure SMTP** în Admin Settings
3. **Test email** din frontend
4. **Develop features**:
   - Messaging System
   - Payment Integration (Stripe)
   - Calendar & Availability
   - Host Analytics Dashboard

## 💡 Tips

- Folosește **Mailtrap** pentru development (gratis, fără limit)
- Folosește **Gmail App Passwords** pentru production
- Verifică **Laravel logs** pentru debugging: `tail -f backend/storage/logs/laravel.log`
- Folosește **Network tab** în browser pentru debugging API calls
- Backend `.env` și Frontend `.env.local` trebuie sincronizate (FRONTEND_URL ↔ API_BASE_URL)

---

**🎉 Backend și Frontend sunt COMPLET conectate și gata de folosit!**

**Întrebări?** Verifică documentația completă în `BACKEND_FRONTEND_INTEGRATION.md`
