# Frontend Examples - Multi-Language & Multi-Currency

## 📁 Structura Fișierelor pentru Next.js

```
frontend/
├── app/
│   └── layout.tsx                    # Wrap with providers
├── components/
│   ├── LanguageSwitcher.tsx          # Language dropdown
│   ├── CurrencySwitcher.tsx          # Currency dropdown
│   ├── PriceDisplay.tsx              # Auto-converting price display
│   └── Header.tsx                    # Header with switchers
├── contexts/
│   ├── LanguageContext.tsx           # Language state management
│   └── CurrencyContext.tsx           # Currency state management
├── hooks/
│   └── useTranslations.ts            # Translation hook
└── styles/
    └── rtl.css                       # RTL support styles
```

## 🚀 Quick Start

### 1. Copiază Contexts

Creează fișierele:
- `contexts/LanguageContext.tsx`
- `contexts/CurrencyContext.tsx`

Copiază codul din `i18n-currency-examples.tsx`

### 2. Adaugă Providers în Layout

```tsx
// app/layout.tsx
import { LanguageProvider } from '@/contexts/LanguageContext';
import { CurrencyProvider } from '@/contexts/CurrencyContext';

export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        <LanguageProvider>
          <CurrencyProvider>
            {children}
          </CurrencyProvider>
        </LanguageProvider>
      </body>
    </html>
  );
}
```

### 3. Folosește Componentele

```tsx
import LanguageSwitcher from '@/components/LanguageSwitcher';
import CurrencySwitcher from '@/components/CurrencySwitcher';
import PriceDisplay from '@/components/PriceDisplay';

export default function MyPage() {
  return (
    <div>
      <LanguageSwitcher />
      <CurrencySwitcher />
      
      <PriceDisplay 
        amount={100} 
        baseCurrency="RON"
      />
    </div>
  );
}
```

## 📚 API Endpoints Used

- `GET /api/v1/languages` - Get all languages
- `GET /api/v1/currencies` - Get all currencies
- `POST /api/v1/currencies/convert` - Convert currency

## ✨ Features

✅ Auto-detect user language  
✅ Save preferences in localStorage  
✅ Automatic price conversion  
✅ RTL support (Arabic, Hebrew)  
✅ Format prices correctly  
✅ Easy to integrate  

## 🎨 Styling

Componentele folosesc Tailwind CSS. Adaptează clasele după designul tău.

## 🔧 Configuration

### API URL
Schimbă `http://localhost:8000` cu URL-ul tău în:
- `LanguageContext.tsx`
- `CurrencyContext.tsx`

### Default Values
- Limba default: English (en)
- Valuta default: RON (lei)

Poți schimba în admin panel sau din seeders.
