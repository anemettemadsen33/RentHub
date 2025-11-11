# Ghid Sincronizare Frontend-Backend - RentHub

## 📋 Prezentare Generală

Acest document descrie modul în care setările din admin panel (Filament) se sincronizează cu aplicația frontend Next.js.

## 🔧 Configurare Setări

### Accesare Admin Panel

Accesați: `http://localhost:8000/admin/settings`

### Taburi Disponibile

#### 1. **Frontend** 🖥️
Configurări generale pentru aplicația frontend:

**Setări Generale:**
- `site_name` - Nume site (afișat în header, title)
- `site_description` - Descriere site (meta description)
- `site_keywords` - Cuvinte cheie SEO
- `frontend_url` - URL complet frontend (ex: `http://localhost:3000`)
- `items_per_page` - Număr elemente per pagină (listings)
- `default_meta_image` - Imagine pentru social sharing

**API & WebSockets:**
- `api_url` - URL backend API (ex: `http://localhost:8000`)
- `api_base_url` - URL bază API (ex: `http://localhost:8000/api/v1`)
- `websocket_url` - URL pentru WebSocket
- `use_reverb` - Folosește Reverb (recomandat: DA)
- `reverb_host` - Host Reverb (ex: `localhost`)
- `reverb_port` - Port Reverb (ex: `8080`)
- `reverb_scheme` - Protocol (ws/wss)

**Funcționalități:**
- `enable_registrations` - Permite înregistrări noi
- `require_email_verification` - Verificare email obligatorie
- `enable_reviews` - Activează sistem recenzii
- `enable_messaging` - Activează mesagerie
- `enable_wishlist` - Activează liste favorite
- `auto_approve_properties` - Aprobare automată proprietăți
- `maintenance_mode` - Mod mentenanță
- `maintenance_message` - Mesaj afișat în modul mentenanță

**Autentificare Socială:**
- Google Login (Client ID + Secret)
- Facebook Login (App ID + Secret)

**SEO:**
- `robots_txt_enabled` - Activează robots.txt
- `sitemap_enabled` - Activează sitemap XML

#### 2. **Companie** 🏢
Informații despre companie:
- `company_name` - Nume companie
- `company_email` - Email principal
- `company_phone` - Telefon
- `company_address` - Adresă completă
- `support_email` - Email suport
- `support_phone` - Telefon suport

#### 3. **Email** ✉️
Configurare SMTP:
- `mail_mailer` - Driver (smtp/sendmail/mailgun/ses)
- `mail_host` - Host SMTP (ex: smtp.gmail.com)
- `mail_port` - Port (587 pentru TLS, 465 pentru SSL)
- `mail_username` - Username SMTP
- `mail_password` - Parolă SMTP
- `mail_encryption` - Criptare (tls/ssl)
- `mail_from_address` - Email expeditor
- `mail_from_name` - Nume expeditor

#### 4. **Plăți** 💳
Configurare Stripe:
- `stripe_enabled` - Activează Stripe
- `stripe_public_key` - Cheie publică Stripe
- `stripe_secret_key` - Cheie secretă Stripe
- `currency` - Monedă (RON/EUR/USD/GBP)
- `currency_symbol` - Simbol monedă
- `commission_percentage` - Comision platformă (%)

#### 5. **SMS** 📱
Configurare Twilio:
- `twilio_enabled` - Activează Twilio
- `twilio_sid` - Account SID
- `twilio_auth_token` - Auth Token
- `twilio_phone_number` - Număr telefon Twilio

#### 6. **Hărți & Localizare** 🗺️
**Mapbox:**
- `mapbox_token` - Mapbox Access Token

**Google Maps:**
- `google_maps_api_key` - Google Maps API Key

**Geolocalizare:**
- `ipstack_api_key` - IPStack API Key
- `default_map_center_lat` - Latitudine centru hartă (București: 44.4268)
- `default_map_center_lng` - Longitudine centru hartă (București: 26.1025)

#### 7. **Analytics** 📊
- `enable_analytics` - Activează analytics
- `google_analytics_id` - Google Analytics ID (G-XXXXXXXXXX)
- `facebook_pixel_id` - Facebook Pixel ID

#### 8. **Notificări** 🔔
**Canale:**
- `enable_email_notifications` - Notificări email
- `enable_sms_notifications` - Notificări SMS
- `enable_push_notifications` - Notificări push

**Pusher Beams:**
- `pusher_beams_instance_id` - Instance ID pentru push notifications

---

## 🔌 API Endpoint pentru Frontend

### Obținere Toate Setările Publice

**Endpoint:** `GET /api/v1/settings/public`

**Response:**
```json
{
  "success": true,
  "data": {
    "site_name": "RentHub",
    "site_description": "Platformă de închirieri",
    "items_per_page": 12,
    "api_url": "http://localhost:8000",
    "api_base_url": "http://localhost:8000/api/v1",
    "reverb": {
      "enabled": true,
      "host": "localhost",
      "port": 8080,
      "scheme": "ws",
      "key": "renthub-key"
    },
    "features": {
      "registrations_enabled": true,
      "email_verification_required": true,
      "reviews_enabled": true,
      "messaging_enabled": true,
      "wishlist_enabled": true
    },
    "maintenance_mode": false,
    "social_login": {
      "google_enabled": false,
      "google_client_id": "",
      "facebook_enabled": false,
      "facebook_client_id": ""
    },
    "payment": {
      "stripe_enabled": false,
      "stripe_public_key": "",
      "currency": "RON",
      "currency_symbol": "RON"
    },
    "maps": {
      "mapbox_token": "",
      "google_maps_api_key": "",
      "default_center": {
        "lat": 44.4268,
        "lng": 26.1025
      }
    },
    "analytics": {
      "enabled": false,
      "google_analytics_id": "",
      "facebook_pixel_id": ""
    },
    "company": {
      "name": "RentHub",
      "email": "info@renthub.ro",
      "phone": "+40 XXX XXX XXX"
    }
  }
}
```

### Obținere Setare Specifică

**Endpoint:** `GET /api/v1/settings/{key}`

**Exemplu:** `GET /api/v1/settings/site_name`

**Response:**
```json
{
  "success": true,
  "data": {
    "key": "site_name",
    "value": "RentHub"
  }
}
```

---

## 🚀 Utilizare în Frontend (Next.js)

### 1. Creați un Hook pentru Settings

```typescript
// hooks/useSettings.ts
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

interface PublicSettings {
  site_name: string;
  site_description: string;
  items_per_page: number;
  api_url: string;
  api_base_url: string;
  reverb: {
    enabled: boolean;
    host: string;
    port: number;
    scheme: string;
    key: string;
  };
  features: {
    registrations_enabled: boolean;
    email_verification_required: boolean;
    reviews_enabled: boolean;
    messaging_enabled: boolean;
    wishlist_enabled: boolean;
  };
  maintenance_mode: boolean;
  maintenance_message?: string;
  payment: {
    stripe_enabled: boolean;
    stripe_public_key: string;
    currency: string;
    currency_symbol: string;
  };
  maps: {
    mapbox_token: string;
    google_maps_api_key: string;
    default_center: {
      lat: number;
      lng: number;
    };
  };
  analytics: {
    enabled: boolean;
    google_analytics_id: string;
    facebook_pixel_id: string;
  };
  company: {
    name: string;
    email: string;
    phone: string;
  };
}

export const usePublicSettings = () => {
  return useQuery<PublicSettings>({
    queryKey: ['settings', 'public'],
    queryFn: async () => {
      const { data } = await axios.get(
        `${process.env.NEXT_PUBLIC_API_BASE_URL}/settings/public`
      );
      return data.data;
    },
    staleTime: 5 * 60 * 1000, // 5 minute cache
  });
};
```

### 2. Folosiți Settings în Componente

```typescript
// components/Header.tsx
import { usePublicSettings } from '@/hooks/useSettings';

export default function Header() {
  const { data: settings, isLoading } = usePublicSettings();
  
  if (isLoading) return <div>Loading...</div>;
  
  return (
    <header>
      <h1>{settings?.site_name}</h1>
      <p>{settings?.company.email}</p>
    </header>
  );
}
```

### 3. Context pentru Settings (Opțional)

```typescript
// contexts/SettingsContext.tsx
'use client';

import { createContext, useContext, ReactNode } from 'react';
import { usePublicSettings } from '@/hooks/useSettings';

const SettingsContext = createContext<any>(null);

export const SettingsProvider = ({ children }: { children: ReactNode }) => {
  const { data: settings, isLoading } = usePublicSettings();
  
  return (
    <SettingsContext.Provider value={{ settings, isLoading }}>
      {children}
    </SettingsContext.Provider>
  );
};

export const useSettings = () => {
  const context = useContext(SettingsContext);
  if (!context) {
    throw new Error('useSettings must be used within SettingsProvider');
  }
  return context;
};
```

### 4. Wrappați App cu Provider

```typescript
// app/layout.tsx
import { SettingsProvider } from '@/contexts/SettingsContext';

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html>
      <body>
        <SettingsProvider>
          {children}
        </SettingsProvider>
      </body>
    </html>
  );
}
```

---

## ⚙️ Configurare Automată

### Backend (Laravel)

Când salvați setări în admin panel:

1. **Setările sunt salvate în DB** (`settings` table)
2. **DynamicConfigServiceProvider se reîncarcă** automat
3. **Config-ul Laravel este actualizat** cu noile valori
4. **Cache-ul este cleared** pentru aplicarea imediată

### Frontend (Next.js)

Pentru sincronizare automată:

```typescript
// hooks/useSettings.ts
export const usePublicSettings = () => {
  return useQuery({
    queryKey: ['settings', 'public'],
    queryFn: fetchSettings,
    staleTime: 5 * 60 * 1000, // Cache 5 minute
    refetchOnWindowFocus: true, // Reîncarcă la focus
    refetchInterval: 10 * 60 * 1000, // Reîncarcă la 10 minute
  });
};
```

---

## 🔄 Flux de Sincronizare

```
Admin Panel (Filament)
        ↓
    Settings Table (MySQL)
        ↓
DynamicConfigServiceProvider (Boot)
        ↓
    Laravel Config
        ↓
API Endpoint (/api/v1/settings/public)
        ↓
    Frontend Request
        ↓
React Query Cache
        ↓
    Components
```

---

## ✅ Checklist Configurare Inițială

### Backend:
- [ ] Rulați migrările: `php artisan migrate`
- [ ] Accesați `/admin/settings`
- [ ] Configurați **Frontend URL**
- [ ] Configurați **API URLs**
- [ ] Setați **Email SMTP**
- [ ] Configurați **Stripe** (dacă e cazul)
- [ ] Adăugați **Mapbox Token**
- [ ] Salvați setările

### Frontend:
- [ ] Actualizați `.env.local` cu `NEXT_PUBLIC_API_BASE_URL`
- [ ] Instalați dependencies: `npm install @tanstack/react-query`
- [ ] Creați `usePublicSettings` hook
- [ ] Testați endpoint: `curl http://localhost:8000/api/v1/settings/public`
- [ ] Verificați sincronizarea în browser

---

## 🛠️ Debugging

### Backend

```bash
# Verifică setările din DB
php artisan tinker
>>> Setting::all()

# Clearează cache-ul
php artisan config:clear
php artisan cache:clear

# Testează endpoint
curl http://localhost:8000/api/v1/settings/public
```

### Frontend

```javascript
// Console browser
fetch('http://localhost:8000/api/v1/settings/public')
  .then(r => r.json())
  .then(console.log);
```

---

## 📝 Note Importante

1. **Nu stocați secrete în frontend** - folosiți doar `public` endpoint
2. **Cache-ul setărilor** - React Query cache 5-10 minute
3. **Maintenance Mode** - verificați `maintenance_mode` înainte de render
4. **CORS** - setarea `frontend_url` configurează automat CORS
5. **Environment Variables** - unele setări pot fi override de .env

---

## 🔐 Securitate

- Endpoint-ul `/settings/public` returnează **doar** setări publice
- Secretele (passwords, tokens) **NU** sunt expuse
- Frontend primește doar `stripe_public_key`, nu `secret_key`
- Filtrare strictă în `SettingsController::publicSettings()`

---

## 📞 Suport

Pentru probleme de sincronizare:
1. Verificați că backend rulează pe portul corect
2. Verificați CORS settings
3. Verificați că setările sunt salvate în DB
4. Clearați cache-urile (backend + frontend)

---

**Versiune:** 1.0  
**Ultima actualizare:** Noiembrie 2025
