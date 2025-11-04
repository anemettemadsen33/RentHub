# Multi-Language & Multi-Currency Support

## Overview
RentHub acum suportă multiple limbi și valute, permițând utilizatorilor să navigheze platforma în limba lor preferată și să vadă prețurile în moneda dorită.

## Features

### 1. Multi-Language Support (i18n)

#### Limbi Disponibile:
- **Engleza** (🇬🇧) - Limba default
- **Română** (🇷🇴)
- **Franceză** (🇫🇷)
- **Germană** (🇩🇪)
- **Spaniolă** (🇪🇸)
- **Italiană** (🇮🇹) - Inactivă (poate fi activată din admin)
- **Arabă** (🇸🇦) - RTL Support - Inactivă
- **Ebraică** (🇮🇱) - RTL Support - Inactivă

#### Caracteristici:
- ✅ Detectare automată a limbii din browser
- ✅ Language switcher în UI
- ✅ Suport RTL pentru limbi ca Arabă și Ebraică
- ✅ Traduceri complete pentru toate componentele
- ✅ Flag emoji pentru fiecare limbă

### 2. Multi-Currency Support

#### Valute Disponibile:
- **USD** ($) - US Dollar
- **EUR** (€) - Euro
- **RON** (lei) - Romanian Leu (default)
- **GBP** (£) - British Pound
- **CHF** - Swiss Franc (inactivă)

#### Caracteristici:
- ✅ Conversie automată între valute
- ✅ Rate de schimb actualizate zilnic
- ✅ Currency switcher în UI
- ✅ Formatare corectă a sumelor (separatori, zecimale)
- ✅ Poziționare corectă a simbolului (înainte/după)

## API Endpoints

### Languages

#### Get All Active Languages
```bash
GET /api/v1/languages
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "en",
      "name": "English",
      "native_name": "English",
      "flag_emoji": "🇬🇧",
      "is_rtl": false,
      "is_active": true,
      "is_default": true,
      "sort_order": 1
    },
    {
      "id": 2,
      "code": "ro",
      "name": "Romanian",
      "native_name": "Română",
      "flag_emoji": "🇷🇴",
      "is_rtl": false,
      "is_active": true,
      "is_default": false,
      "sort_order": 2
    }
  ]
}
```

#### Get Default Language
```bash
GET /api/v1/languages/default
```

#### Get Language by Code
```bash
GET /api/v1/languages/{code}
```

### Currencies

#### Get All Active Currencies
```bash
GET /api/v1/currencies
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "USD",
      "name": "US Dollar",
      "symbol": "$",
      "symbol_position": "before",
      "decimal_places": 2,
      "thousand_separator": ",",
      "decimal_separator": ".",
      "is_active": true,
      "is_default": false,
      "sort_order": 1
    }
  ]
}
```

#### Get Default Currency
```bash
GET /api/v1/currencies/default
```

#### Get Currency by Code
```bash
GET /api/v1/currencies/{code}
```

#### Convert Currency
```bash
POST /api/v1/currencies/convert
Content-Type: application/json

{
  "from": "USD",
  "to": "EUR",
  "amount": 100
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "from": {
      "currency": "USD",
      "amount": 100,
      "formatted": "$100.00"
    },
    "to": {
      "currency": "EUR",
      "amount": 92,
      "formatted": "92,00€"
    },
    "rate": 0.92
  }
}
```

#### Update Exchange Rates (Admin Only)
```bash
POST /api/v1/admin/currencies/update-rates
Authorization: Bearer {token}
```

## Artisan Commands

### Update Exchange Rates
Actualizează ratele de schimb valutar din API extern (exchangerate-api.com):

```bash
php artisan exchange-rates:update
```

Acest command rulează automat zilnic prin scheduler (vezi `routes/console.php`).

## Filament Admin Panel

### Language Management
Accesează **Languages** din admin panel pentru:
- Adăugare limbi noi
- Activare/dezactivare limbi
- Setare limbă default
- Configurare RTL support
- Reordonare limbi

### Currency Management
Accesează **Currencies** din admin panel pentru:
- Adăugare valute noi
- Activare/dezactivare valute
- Setare valută default
- Configurare formatare (separatori, zecimale)
- Setare poziție simbol

### Exchange Rate Management
Accesează **Exchange Rates** din admin panel pentru:
- Vizualizare rate curente
- Actualizare manuală rate
- Monitorizare sursă rate (API/manual)
- Istoric actualizări

## Frontend Integration (Next.js)

### Language Switcher Component

```typescript
// components/LanguageSwitcher.tsx
'use client';

import { useState, useEffect } from 'react';

interface Language {
  code: string;
  name: string;
  native_name: string;
  flag_emoji: string;
  is_rtl: boolean;
}

export default function LanguageSwitcher() {
  const [languages, setLanguages] = useState<Language[]>([]);
  const [currentLang, setCurrentLang] = useState('en');

  useEffect(() => {
    fetch('http://localhost:8000/api/v1/languages')
      .then(res => res.json())
      .then(data => setLanguages(data.data));
  }, []);

  const handleChange = (code: string) => {
    setCurrentLang(code);
    // Set in localStorage
    localStorage.setItem('language', code);
    // Update HTML lang attribute
    document.documentElement.lang = code;
    // Update dir attribute for RTL
    const lang = languages.find(l => l.code === code);
    if (lang?.is_rtl) {
      document.documentElement.dir = 'rtl';
    } else {
      document.documentElement.dir = 'ltr';
    }
  };

  return (
    <select value={currentLang} onChange={(e) => handleChange(e.target.value)}>
      {languages.map((lang) => (
        <option key={lang.code} value={lang.code}>
          {lang.flag_emoji} {lang.native_name}
        </option>
      ))}
    </select>
  );
}
```

### Currency Switcher Component

```typescript
// components/CurrencySwitcher.tsx
'use client';

import { useState, useEffect } from 'react';

interface Currency {
  code: string;
  name: string;
  symbol: string;
}

export default function CurrencySwitcher() {
  const [currencies, setCurrencies] = useState<Currency[]>([]);
  const [currentCurrency, setCurrentCurrency] = useState('RON');

  useEffect(() => {
    fetch('http://localhost:8000/api/v1/currencies')
      .then(res => res.json())
      .then(data => setCurrencies(data.data));
  }, []);

  const handleChange = (code: string) => {
    setCurrentCurrency(code);
    localStorage.setItem('currency', code);
  };

  return (
    <select value={currentCurrency} onChange={(e) => handleChange(e.target.value)}>
      {currencies.map((curr) => (
        <option key={curr.code} value={curr.code}>
          {curr.symbol} {curr.code}
        </option>
      ))}
    </select>
  );
}
```

### Price Display with Currency Conversion

```typescript
// components/PriceDisplay.tsx
'use client';

import { useState, useEffect } from 'react';

interface PriceDisplayProps {
  amount: number;
  baseCurrency?: string;
}

export default function PriceDisplay({ amount, baseCurrency = 'RON' }: PriceDisplayProps) {
  const [displayPrice, setDisplayPrice] = useState<string>('');
  const [selectedCurrency, setSelectedCurrency] = useState('RON');

  useEffect(() => {
    const currency = localStorage.getItem('currency') || 'RON';
    setSelectedCurrency(currency);

    if (currency === baseCurrency) {
      // No conversion needed
      formatPrice(amount, currency);
    } else {
      // Convert price
      fetch('http://localhost:8000/api/v1/currencies/convert', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          from: baseCurrency,
          to: currency,
          amount: amount
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            setDisplayPrice(data.data.to.formatted);
          }
        });
    }
  }, [amount, baseCurrency, selectedCurrency]);

  const formatPrice = async (amount: number, currency: string) => {
    const res = await fetch(`http://localhost:8000/api/v1/currencies/${currency}`);
    const data = await res.json();
    if (data.success) {
      const curr = data.data;
      const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: curr.decimal_places,
        maximumFractionDigits: curr.decimal_places,
      }).format(amount);
      
      if (curr.symbol_position === 'before') {
        setDisplayPrice(`${curr.symbol}${formatted}`);
      } else {
        setDisplayPrice(`${formatted}${curr.symbol}`);
      }
    }
  };

  return <span className="font-bold text-lg">{displayPrice}</span>;
}
```

## Database Schema

### Languages Table
```sql
- id
- code (en, ro, fr, etc.)
- name (English, Romanian, etc.)
- native_name (English, Română, etc.)
- flag_emoji (🇬🇧, 🇷🇴, etc.)
- is_rtl (boolean)
- is_active (boolean)
- is_default (boolean)
- sort_order (integer)
- timestamps
```

### Currencies Table
```sql
- id
- code (USD, EUR, RON, etc.)
- name (US Dollar, Euro, etc.)
- symbol ($, €, lei, etc.)
- symbol_position (before/after)
- decimal_places (integer)
- thousand_separator (string)
- decimal_separator (string)
- is_active (boolean)
- is_default (boolean)
- sort_order (integer)
- timestamps
```

### Exchange Rates Table
```sql
- id
- from_currency_id (foreign key)
- to_currency_id (foreign key)
- rate (decimal 20,8)
- fetched_at (timestamp)
- source (string: manual, api, exchangerate-api.com)
- timestamps
- UNIQUE(from_currency_id, to_currency_id)
```

## Best Practices

### 1. Cache Management
Pentru performanță optimă, cache-ează lista de limbi și valute:

```php
// In a service provider or controller
Cache::remember('active_languages', 86400, function () {
    return Language::getActive();
});

Cache::remember('active_currencies', 86400, function () {
    return Currency::getActive();
});
```

### 2. Exchange Rate Updates
- Ratele se actualizează automat zilnic prin scheduler
- Pentru actualizări manuale, folosește command-ul artisan
- API-ul gratuit (exchangerate-api.com) oferă 1500 requests/lună

### 3. Frontend Caching
- Salvează limba și valuta selectată în localStorage
- Cache-ază lista de limbi/valute pentru a reduce API calls
- Implementează loading states pentru conversii

## Testing

### Test API Endpoints

```bash
# Test languages endpoint
curl http://localhost:8000/api/v1/languages

# Test currencies endpoint
curl http://localhost:8000/api/v1/currencies

# Test currency conversion
curl -X POST http://localhost:8000/api/v1/currencies/convert \
  -H "Content-Type: application/json" \
  -d '{"from":"USD","to":"EUR","amount":100}'

# Test exchange rate update (needs auth)
curl -X POST http://localhost:8000/api/v1/admin/currencies/update-rates \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Next Steps

1. **Frontend Implementation**
   - [ ] Implementează Language Switcher în Next.js
   - [ ] Implementează Currency Switcher în Next.js
   - [ ] Adaugă conversie automată prețuri
   - [ ] Implementează RTL support în CSS

2. **Translations**
   - [ ] Creează fișiere de traduceri pentru fiecare limbă
   - [ ] Implementează translation management în admin
   - [ ] Adaugă suport pentru traduceri dinamice din DB

3. **Advanced Features**
   - [ ] Detectare automată locație utilizator
   - [ ] Suggerare limbă/valută bazată pe IP
   - [ ] A/B testing pentru conversii
   - [ ] Analytics pentru preferințe utilizatori

## Support

Pentru întrebări sau probleme, contactează echipa de development.
