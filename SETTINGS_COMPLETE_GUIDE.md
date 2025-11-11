# 🛠️ Ghid Complet Setări RentHub

## 📋 Cuprins
- [Panoul Filament Admin](#panoul-filament-admin)
- [API Endpoints pentru Setări](#api-endpoints-pentru-setări)
- [Configurare Email](#configurare-email)
- [Integrare Frontend-Backend](#integrare-frontend-backend)
- [Testare Setări](#testare-setări)

---

## 🎛️ Panoul Filament Admin

### Acces
```
URL: http://localhost:8000/admin/settings
Autentificare: Admin user required
```

### Secțiuni Disponibile

#### 1️⃣ **General**
- **Nume Site**: Numele afișat al aplicației
- **Descriere Site**: Descrierea scurtă pentru SEO
- **Logo Site**: Upload logo (max 2MB)
- **Favicon**: Upload favicon (max 512KB)

#### 2️⃣ **Frontend & API**
Setări pentru integrare frontend-backend:

**URL Configuration:**
- **Frontend URL**: URL-ul aplicației React (ex: `http://localhost:3000`)
- **Backend API URL**: URL-ul backend Laravel (ex: `http://localhost:8000`)

**CORS & Sanctum:**
- **Sanctum Stateful Domains**: Domenii permise pentru autentificare
  - Local: `localhost:3000,127.0.0.1:3000`
  - Production: `app.renthub.ro`
- **CORS Allowed Origins**: Origini permise pentru CORS
  - Local: `http://localhost:3000`
  - Production: `https://app.renthub.ro`

#### 3️⃣ **Email Configuration**
Configurare completă SMTP:

**SMTP Settings:**
- **Mail Driver**: smtp, sendmail, mailgun, ses, postmark, log
- **SMTP Host**: Server SMTP (ex: `smtp.gmail.com`, `smtp.mailtrap.io`)
- **SMTP Port**: 
  - 587 (TLS - recomandat)
  - 465 (SSL)
  - 2525 (Mailtrap)
- **Encryption**: TLS, SSL, None
- **Username**: Email sau username SMTP
- **Password**: Parola SMTP sau App Password

**From Configuration:**
- **From Address**: Email-ul expeditorului
- **From Name**: Numele afișat ca expeditor

#### 4️⃣ **Companie**
- **Nume Companie**
- **Email Companie**
- **Telefon Companie**
- **Adresă Companie**

#### 5️⃣ **Plăți**
**Stripe:**
- Toggle activare Stripe
- Public Key (`pk_test_...` sau `pk_live_...`)
- Secret Key (`sk_test_...` sau `sk_live_...`)

**PayPal:**
- Toggle activare PayPal
- Client ID
- Mode (Sandbox/Live)

#### 6️⃣ **SEO**
- **Meta Title**: Max 60 caractere
- **Meta Description**: Max 160 caractere
- **Meta Keywords**: Separat prin virgulă

#### 7️⃣ **Social Auth**
**Google OAuth:**
- Toggle activare Google Login
- Google Client ID

**Facebook OAuth:**
- Toggle activare Facebook Login
- Facebook Client ID

#### 8️⃣ **Funcționalități**
- **Mod Mentenanță**: Dezactivează accesul utilizatorilor
- **Înregistrare Activată**: Permite înregistrări noi
- **Verificare Email Obligatorie**: Necesită verificare email

---

## 🔌 API Endpoints pentru Setări

### 1. Obține Setări Publice
```http
GET /api/v1/settings/public
```

**Response:**
```json
{
  "success": true,
  "data": {
    "site_name": "RentHub",
    "site_description": "...",
    "frontend_url": "http://localhost:3000",
    "company": {
      "name": "RentHub",
      "email": "info@renthub.ro",
      "phone": "+40 XXX XXX XXX"
    },
    "features": {
      "registrations_enabled": true,
      "email_verification_required": true
    },
    "payment": {
      "stripe_enabled": true,
      "stripe_public_key": "pk_test_..."
    }
  }
}
```

### 2. Obține Toate Setările (Admin)
```http
GET /api/v1/settings
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "site_name": "RentHub",
    "frontend_url": "http://localhost:3000",
    "mail_host": "smtp.gmail.com",
    "mail_port": "587",
    "stripe_public_key": "pk_test_...",
    "stripe_secret_key": "sk_test_..."
  }
}
```

### 3. Actualizează Setări (Admin)
```http
PUT /api/v1/settings
Authorization: Bearer {token}
Content-Type: application/json

{
  "site_name": "RentHub Romania",
  "frontend_url": "https://app.renthub.ro",
  "mail_host": "smtp.gmail.com",
  "mail_port": 587,
  "mail_encryption": "tls"
}
```

### 4. Testează Configurare Email (Admin)
```http
POST /api/v1/settings/test-email
Authorization: Bearer {token}
Content-Type: application/json

{
  "email": "test@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Test email sent successfully to test@example.com"
}
```

---

## 📧 Configurare Email

### Gmail Setup

1. **Activează 2-Step Verification** în contul Google
2. **Generează App Password:**
   - Mergi la: https://myaccount.google.com/apppasswords
   - Selectează "Mail" și device-ul tău
   - Copiază parola generată (16 caractere)

3. **Setări în Filament Admin:**
```
Mail Driver: smtp
SMTP Host: smtp.gmail.com
SMTP Port: 587
Encryption: TLS
Username: your-email@gmail.com
Password: [App Password de 16 caractere]
From Address: your-email@gmail.com
From Name: RentHub
```

### Mailtrap Setup (Development)

1. **Creează cont gratuit:** https://mailtrap.io
2. **Obține credențiale** din inbox-ul creat

3. **Setări în Filament Admin:**
```
Mail Driver: smtp
SMTP Host: smtp.mailtrap.io
SMTP Port: 2525
Encryption: TLS
Username: [din Mailtrap]
Password: [din Mailtrap]
From Address: noreply@renthub.test
From Name: RentHub Dev
```

### SendGrid Setup (Production)

1. **Creează cont SendGrid:** https://sendgrid.com
2. **Creează API Key:**
   - Settings → API Keys → Create API Key
   - Full Access

3. **Setări în Filament Admin:**
```
Mail Driver: smtp
SMTP Host: smtp.sendgrid.net
SMTP Port: 587
Encryption: TLS
Username: apikey
Password: [SendGrid API Key]
From Address: noreply@renthub.ro
From Name: RentHub
```

### Testare Email

**Din Filament Admin:**
1. Mergi la Settings → Email
2. Configurează setările SMTP
3. Salvează
4. Click pe butonul "Test Email"
5. Introdu email-ul de test
6. Verifică inbox-ul

**Via API:**
```bash
curl -X POST http://localhost:8000/api/v1/settings/test-email \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```

---

## 🔗 Integrare Frontend-Backend

### 1. Configurare CORS

**În Filament Settings:**
```
Frontend URL: http://localhost:3000
CORS Allowed Origins: http://localhost:3000
```

**Verificare în `.env`:**
```env
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000
```

### 2. Configurare Sanctum

**Backend (`config/sanctum.php`):**
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:3000')),
```

**Frontend (React):**
```javascript
// src/config/api.js
const API_CONFIG = {
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000',
  withCredentials: true, // Important pentru Sanctum
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
};
```

### 3. Fetch Settings în Frontend

**React Hook pentru Settings:**
```javascript
// src/hooks/useSettings.js
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useSettings = () => {
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const response = await axios.get('/api/v1/settings/public');
        setSettings(response.data.data);
      } catch (error) {
        console.error('Error fetching settings:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchSettings();
  }, []);

  return { settings, loading };
};
```

**Usage în componente:**
```javascript
import { useSettings } from './hooks/useSettings';

function App() {
  const { settings, loading } = useSettings();

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      <h1>{settings.site_name}</h1>
      <p>{settings.site_description}</p>
      {settings.payment.stripe_enabled && (
        <StripeProvider apiKey={settings.payment.stripe_public_key}>
          {/* Stripe components */}
        </StripeProvider>
      )}
    </div>
  );
}
```

### 4. Environment Variables Sync

**Backend `.env`:**
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@renthub.ro
MAIL_FROM_NAME="RentHub"

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

**Frontend `.env`:**
```env
REACT_APP_API_URL=http://localhost:8000
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1
REACT_APP_ENABLE_PWA=true
```

---

## 🧪 Testare Setări

### 1. Verificare Database

```bash
cd backend
php artisan tinker
```

```php
// Verifică toate setările
\App\Models\Setting::all();

// Obține o setare specifică
\App\Models\Setting::get('site_name');

// Setează o valoare
\App\Models\Setting::set('site_name', 'RentHub Romania');

// Verifică cache
Cache::get('app_settings');
```

### 2. Testare API

**Test Public Settings:**
```bash
curl http://localhost:8000/api/v1/settings/public | jq
```

**Test Admin Settings (cu autentificare):**
```bash
# 1. Login
TOKEN=$(curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@renthub.ro","password":"password"}' \
  | jq -r '.data.token')

# 2. Get Settings
curl http://localhost:8000/api/v1/settings \
  -H "Authorization: Bearer $TOKEN" | jq

# 3. Update Settings
curl -X PUT http://localhost:8000/api/v1/settings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"site_name":"RentHub Updated"}' | jq
```

### 3. Testare Email

**Test din Laravel:**
```bash
php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')
            ->subject('Test');
});
```

**Test API Endpoint:**
```bash
curl -X POST http://localhost:8000/api/v1/settings/test-email \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

### 4. Verificare CORS

**Test din Browser Console:**
```javascript
fetch('http://localhost:8000/api/v1/settings/public', {
  credentials: 'include',
  headers: {
    'Accept': 'application/json'
  }
})
.then(r => r.json())
.then(console.log)
.catch(console.error);
```

---

## 📝 Checklist Configurare Completă

### Development Setup
- [ ] Configurare URLs în Filament Settings
  - [ ] Frontend URL: `http://localhost:3000`
  - [ ] API URL: `http://localhost:8000`
- [ ] Configurare CORS & Sanctum
  - [ ] SANCTUM_STATEFUL_DOMAINS în .env
  - [ ] CORS Allowed Origins în Settings
- [ ] Configurare Email (Mailtrap)
  - [ ] Credențiale SMTP
  - [ ] Test email trimis cu succes
- [ ] Test API Endpoints
  - [ ] GET /settings/public funcționează
  - [ ] GET /settings (admin) funcționează
  - [ ] PUT /settings (admin) funcționează

### Production Setup
- [ ] URLs de producție
  - [ ] Frontend URL: `https://app.renthub.ro`
  - [ ] API URL: `https://api.renthub.ro`
- [ ] Email Provider (SendGrid/Gmail)
  - [ ] API Keys configurate
  - [ ] Domain verification
  - [ ] Test email production
- [ ] Payment Gateways
  - [ ] Stripe Live Keys
  - [ ] PayPal Live Credentials
- [ ] Social Auth
  - [ ] Google OAuth production credentials
  - [ ] Facebook OAuth production credentials
- [ ] SSL Certificates
  - [ ] Backend SSL configurate
  - [ ] Frontend SSL configurate
- [ ] Environment Variables
  - [ ] Backend .env production
  - [ ] Frontend .env.production

---

## 🚨 Troubleshooting

### Email nu se trimite
1. Verifică credențialele SMTP în Settings
2. Testează conexiunea SMTP
3. Verifică log-urile: `storage/logs/laravel.log`
4. Pentru Gmail, asigură-te că folosești App Password

### CORS Errors
1. Verifică FRONTEND_URL în .env
2. Verifică SANCTUM_STATEFUL_DOMAINS
3. Clear cache: `php artisan config:clear`
4. Restart server

### Settings nu se salvează
1. Verifică permisiuni admin
2. Verifică database migration: `settings` table exists
3. Clear cache: `php artisan cache:clear`
4. Verifică log-uri

### Frontend nu primește settings
1. Verifică URL: `/api/v1/settings/public`
2. Verifică CORS headers
3. Verifică network tab în browser
4. Test cu curl direct

---

## 📚 Resurse Utile

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Filament Documentation](https://filamentphp.com/docs)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Stripe Documentation](https://stripe.com/docs)
- [SendGrid Documentation](https://docs.sendgrid.com/)

---

**Ultima actualizare:** 2025-11-11
**Versiune:** 1.0.0
