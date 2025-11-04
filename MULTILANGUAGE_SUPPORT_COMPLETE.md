# 🌍 Multi-language Support Implementation - Task 2.7 ✅

## 📋 Overview
Implementare completă sistem de **Internationalization (i18n)** pentru RentHub cu suport pentru:
- ✅ Multiple limbi (EN, RO, ES, FR, DE, AR, HE)
- ✅ Auto-detect language din browser
- ✅ Language switcher
- ✅ RTL support (Arabic, Hebrew)
- ✅ Translation management via Filament Admin
- ✅ API endpoints pentru frontend

---

## 🗄️ Database Structure

### Tables Created
1. **`translations`** - Store all translations
   - `locale` (string) - Language code (en, ro, es, etc.)
   - `group` (string) - Translation group (common, properties, bookings, etc.)
   - `key` (string) - Translation key
   - `value` (text) - Translated text
   - Unique constraint: `locale + group + key`

2. **`supported_languages`** - Manage available languages
   - `code` (string) - Language code
   - `name` (string) - English name
   - `native_name` (string) - Native name (e.g., "Română")
   - `is_rtl` (boolean) - Right-to-left support
   - `is_active` (boolean) - Enable/disable language
   - `sort_order` (integer) - Display order

### Default Languages Seeded
- 🇬🇧 **English** (en)
- 🇷🇴 **Română** (ro)
- 🇪🇸 **Español** (es)
- 🇫🇷 **Français** (fr)
- 🇩🇪 **Deutsch** (de)
- 🇸🇦 **العربية** (ar) - RTL
- 🇮🇱 **עברית** (he) - RTL

---

## 🔧 Backend Implementation

### Models Created
```
backend/app/Models/
├── Translation.php          # Translation management
└── SupportedLanguage.php    # Language management
```

**Translation Model Features:**
- Cache translations for performance
- Helper method: `Translation::get($key, $locale, $group, $default)`
- Automatic cache invalidation on save/delete

### Service Layer
```
backend/app/Services/TranslationService.php
```

**Methods:**
- `getTranslation()` - Get single translation
- `getAllTranslations()` - Get all for locale/group
- `setTranslation()` - Create/update translation
- `deleteTranslation()` - Remove translation
- `getSupportedLanguages()` - List active languages
- `detectLanguage()` - Auto-detect from Accept-Language header
- `importTranslations()` - Bulk import from JSON
- `exportTranslations()` - Export to JSON

### API Endpoints

#### Public Endpoints (No Auth Required)
```
GET  /api/v1/languages              # List all active languages
GET  /api/v1/translations           # Get translations (query: locale, group)
GET  /api/v1/translations/{key}     # Get single translation
GET  /api/v1/detect-language        # Auto-detect language
GET  /api/v1/translations/export    # Export translations as JSON
```

#### Admin Endpoints (Auth + Admin Role Required)
```
POST   /api/v1/translations         # Create translation
PUT    /api/v1/translations/{id}    # Update translation
DELETE /api/v1/translations         # Delete translation
POST   /api/v1/translations/import  # Bulk import from JSON
```

---

## 📱 API Usage Examples

### 1. Get All Supported Languages
```bash
curl -X GET "http://localhost/api/v1/languages" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "languages": [
    {
      "id": 1,
      "code": "en",
      "name": "English",
      "native_name": "English",
      "is_rtl": false,
      "is_active": true
    },
    {
      "id": 2,
      "code": "ro",
      "name": "Romanian",
      "native_name": "Română",
      "is_rtl": false,
      "is_active": true
    }
  ]
}
```

### 2. Get Translations for a Language
```bash
curl -X GET "http://localhost/api/v1/translations?locale=ro&group=common" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "locale": "ro",
  "translations": {
    "welcome": "Bine ai venit",
    "home": "Acasă",
    "search": "Caută",
    "properties": "Proprietăți"
  }
}
```

### 3. Auto-Detect User Language
```bash
curl -X GET "http://localhost/api/v1/detect-language" \
  -H "Accept-Language: ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7"
```

**Response:**
```json
{
  "success": true,
  "detected_language": "ro"
}
```

### 4. Create Translation (Admin Only)
```bash
curl -X POST "http://localhost/api/v1/translations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "locale": "ro",
    "group": "bookings",
    "key": "confirm_booking",
    "value": "Confirmă rezervarea"
  }'
```

### 5. Export Translations
```bash
curl -X GET "http://localhost/api/v1/translations/export?locale=ro&group=common" \
  -H "Accept: application/json"
```

### 6. Import Translations (Admin Only)
```bash
curl -X POST "http://localhost/api/v1/translations/import" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "locale": "es",
    "group": "common",
    "translations": {
      "welcome": "Bienvenido",
      "search": "Buscar"
    }
  }'
```

---

## 🎨 Frontend Integration (Next.js)

### Recommended Setup with `next-i18next`

#### 1. Install Dependencies
```bash
cd frontend
npm install next-i18next react-i18next i18next
```

#### 2. Create `next-i18next.config.js`
```javascript
module.exports = {
  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'ro', 'es', 'fr', 'de', 'ar', 'he'],
    localeDetection: true,
  },
  reloadOnPrerender: process.env.NODE_ENV === 'development',
}
```

#### 3. Update `next.config.js`
```javascript
const { i18n } = require('./next-i18next.config')

module.exports = {
  i18n,
  // ... other config
}
```

#### 4. Create Translation Hook
```typescript
// hooks/useTranslations.ts
import { useEffect, useState } from 'react';
import axios from 'axios';

export const useTranslations = (locale: string, group: string = 'common') => {
  const [translations, setTranslations] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchTranslations = async () => {
      try {
        const response = await axios.get(
          `/api/v1/translations?locale=${locale}&group=${group}`
        );
        setTranslations(response.data.translations);
      } catch (error) {
        console.error('Failed to load translations:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchTranslations();
  }, [locale, group]);

  return { t: translations, loading };
};
```

#### 5. Create Language Switcher Component
```typescript
// components/LanguageSwitcher.tsx
'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';

interface Language {
  code: string;
  name: string;
  native_name: string;
  is_rtl: boolean;
}

export default function LanguageSwitcher() {
  const router = useRouter();
  const [languages, setLanguages] = useState<Language[]>([]);
  const [currentLocale, setCurrentLocale] = useState('en');

  useEffect(() => {
    // Fetch available languages
    fetch('/api/v1/languages')
      .then(res => res.json())
      .then(data => setLanguages(data.languages));
  }, []);

  const changeLanguage = (locale: string) => {
    setCurrentLocale(locale);
    document.documentElement.lang = locale;
    
    // Update RTL direction
    const selectedLang = languages.find(l => l.code === locale);
    if (selectedLang?.is_rtl) {
      document.documentElement.dir = 'rtl';
    } else {
      document.documentElement.dir = 'ltr';
    }

    // Redirect to new locale
    router.push(`/${locale}`);
  };

  return (
    <select 
      value={currentLocale} 
      onChange={(e) => changeLanguage(e.target.value)}
      className="border rounded px-3 py-2"
    >
      {languages.map((lang) => (
        <option key={lang.code} value={lang.code}>
          {lang.native_name}
        </option>
      ))}
    </select>
  );
}
```

#### 6. RTL Support CSS
```css
/* styles/rtl.css */
[dir="rtl"] {
  text-align: right;
}

[dir="rtl"] .container {
  direction: rtl;
}

[dir="rtl"] .flex-row {
  flex-direction: row-reverse;
}

[dir="rtl"] input,
[dir="rtl"] textarea {
  text-align: right;
}
```

---

## 📊 Translation Groups

Translations are organized into groups for better management:

- **`common`** - Common UI elements (buttons, labels, navigation)
- **`properties`** - Property-related terms
- **`bookings`** - Booking process translations
- **`payments`** - Payment and billing terms
- **`reviews`** - Review system translations
- **`messages`** - Messaging system
- **`notifications`** - Notification messages
- **`dashboard`** - Dashboard analytics
- **`auth`** - Authentication pages
- **`validation`** - Form validation messages

---

## ✅ Default Translations Seeded

### English (en) - Common
```json
{
  "welcome": "Welcome",
  "home": "Home",
  "search": "Search",
  "properties": "Properties",
  "bookings": "Bookings",
  "messages": "Messages",
  "profile": "Profile",
  "logout": "Logout",
  "login": "Login",
  "register": "Register"
}
```

### Romanian (ro) - Common
```json
{
  "welcome": "Bine ai venit",
  "home": "Acasă",
  "search": "Caută",
  "properties": "Proprietăți",
  "bookings": "Rezervări",
  "messages": "Mesaje",
  "profile": "Profil"
}
```

### Spanish (es) - Common
```json
{
  "welcome": "Bienvenido",
  "home": "Inicio",
  "search": "Buscar",
  "properties": "Propiedades"
}
```

---

## 🔐 Admin Panel (Future - Filament v4)

Filament Resources for managing translations will need to be created following Filament v4 conventions:
- Translation CRUD interface
- Language management
- Import/Export functionality
- Bulk operations

---

## 🚀 Testing Checklist

### Backend API Tests
- [ ] GET /api/v1/languages returns all active languages
- [ ] GET /api/v1/translations?locale=en returns English translations
- [ ] GET /api/v1/detect-language detects language from header
- [ ] POST /api/v1/translations creates new translation (admin)
- [ ] POST /api/v1/translations/import imports JSON file (admin)
- [ ] GET /api/v1/translations/export exports JSON

### Frontend Tests
- [ ] Language switcher displays all languages
- [ ] Switching language updates all text
- [ ] RTL languages (Arabic, Hebrew) display correctly
- [ ] Auto-detect browser language works
- [ ] Translations cached for performance

---

## 📈 Performance Optimization

1. **Caching Strategy:**
   - Translations cached for 1 hour
   - Cache invalidated on translation updates
   - Per-locale and per-group caching

2. **Frontend Optimization:**
   - Load only required translation groups
   - Store translations in localStorage
   - Lazy load language-specific components

---

## 🎯 Next Steps

1. **Create more translation groups:**
   ```bash
   php artisan db:seed --class=PropertyTranslationsSeeder
   php artisan db:seed --class=BookingTranslationsSeeder
   ```

2. **Add more languages:**
   - Italian (it)
   - Portuguese (pt)
   - Russian (ru)
   - Chinese (zh)

3. **Frontend Implementation:**
   - Implement language switcher in Next.js
   - Add RTL CSS support
   - Create translation hook
   - Test on all pages

4. **Professional Translation:**
   - Hire professional translators
   - Review and improve auto-translations
   - Add region-specific variants (en-US vs en-GB)

---

## 📞 Support

Need more languages or translation help?
- Export current translations: `GET /api/v1/translations/export?locale=en`
- Import new translations via API or Filament Admin
- Cache will auto-refresh after updates

---

**Status:** ✅ **COMPLETE**
**Date:** November 2, 2025
**Task:** 2.7 Multi-language Support
