// ===========================================
// EXEMPLE COMPLETE PENTRU NEXT.JS FRONTEND
// Multi-Language & Multi-Currency Support
// ===========================================

// ===========================================
// 1. LANGUAGE CONTEXT PROVIDER
// ===========================================
// File: contexts/LanguageContext.tsx

'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';

interface Language {
  id: number;
  code: string;
  name: string;
  native_name: string;
  flag_emoji: string;
  is_rtl: boolean;
  is_active: boolean;
  is_default: boolean;
}

interface LanguageContextType {
  currentLanguage: Language | null;
  languages: Language[];
  setLanguage: (code: string) => void;
  isLoading: boolean;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [languages, setLanguages] = useState<Language[]>([]);
  const [currentLanguage, setCurrentLanguage] = useState<Language | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadLanguages();
  }, []);

  const loadLanguages = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/v1/languages');
      const data = await response.json();
      
      if (data.success) {
        setLanguages(data.data);
        
        // Get saved language or default
        const savedLang = localStorage.getItem('language');
        const defaultLang = data.data.find((l: Language) => l.is_default);
        const selected = savedLang 
          ? data.data.find((l: Language) => l.code === savedLang)
          : defaultLang;
        
        setCurrentLanguage(selected || data.data[0]);
        updateHTMLAttributes(selected || data.data[0]);
      }
    } catch (error) {
      console.error('Failed to load languages:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const setLanguage = (code: string) => {
    const language = languages.find(l => l.code === code);
    if (language) {
      setCurrentLanguage(language);
      localStorage.setItem('language', code);
      updateHTMLAttributes(language);
    }
  };

  const updateHTMLAttributes = (language: Language) => {
    document.documentElement.lang = language.code;
    document.documentElement.dir = language.is_rtl ? 'rtl' : 'ltr';
  };

  return (
    <LanguageContext.Provider value={{ currentLanguage, languages, setLanguage, isLoading }}>
      {children}
    </LanguageContext.Provider>
  );
}

export function useLanguage() {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within LanguageProvider');
  }
  return context;
}

// ===========================================
// 2. CURRENCY CONTEXT PROVIDER
// ===========================================
// File: contexts/CurrencyContext.tsx

'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';

interface Currency {
  id: number;
  code: string;
  name: string;
  symbol: string;
  symbol_position: 'before' | 'after';
  decimal_places: number;
  thousand_separator: string;
  decimal_separator: string;
  is_active: boolean;
  is_default: boolean;
}

interface CurrencyContextType {
  currentCurrency: Currency | null;
  currencies: Currency[];
  setCurrency: (code: string) => void;
  formatPrice: (amount: number, currency?: Currency) => string;
  convertPrice: (amount: number, fromCode: string, toCode: string) => Promise<number>;
  isLoading: boolean;
}

const CurrencyContext = createContext<CurrencyContextType | undefined>(undefined);

export function CurrencyProvider({ children }: { children: ReactNode }) {
  const [currencies, setCurrencies] = useState<Currency[]>([]);
  const [currentCurrency, setCurrentCurrency] = useState<Currency | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadCurrencies();
  }, []);

  const loadCurrencies = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/v1/currencies');
      const data = await response.json();
      
      if (data.success) {
        setCurrencies(data.data);
        
        // Get saved currency or default
        const savedCurr = localStorage.getItem('currency');
        const defaultCurr = data.data.find((c: Currency) => c.is_default);
        const selected = savedCurr 
          ? data.data.find((c: Currency) => c.code === savedCurr)
          : defaultCurr;
        
        setCurrentCurrency(selected || data.data[0]);
      }
    } catch (error) {
      console.error('Failed to load currencies:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const setCurrency = (code: string) => {
    const currency = currencies.find(c => c.code === code);
    if (currency) {
      setCurrentCurrency(currency);
      localStorage.setItem('currency', code);
    }
  };

  const formatPrice = (amount: number, currency?: Currency): string => {
    const curr = currency || currentCurrency;
    if (!curr) return amount.toString();

    const formatted = amount.toLocaleString('en-US', {
      minimumFractionDigits: curr.decimal_places,
      maximumFractionDigits: curr.decimal_places,
    }).replace(/,/g, curr.thousand_separator)
      .replace(/\./g, curr.decimal_separator);

    return curr.symbol_position === 'before'
      ? `${curr.symbol}${formatted}`
      : `${formatted}${curr.symbol}`;
  };

  const convertPrice = async (amount: number, fromCode: string, toCode: string): Promise<number> => {
    try {
      const response = await fetch('http://localhost:8000/api/v1/currencies/convert', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ from: fromCode, to: toCode, amount }),
      });
      const data = await response.json();
      
      if (data.success) {
        return data.data.to.amount;
      }
    } catch (error) {
      console.error('Failed to convert price:', error);
    }
    return amount;
  };

  return (
    <CurrencyContext.Provider value={{ 
      currentCurrency, 
      currencies, 
      setCurrency, 
      formatPrice,
      convertPrice,
      isLoading 
    }}>
      {children}
    </CurrencyContext.Provider>
  );
}

export function useCurrency() {
  const context = useContext(CurrencyContext);
  if (!context) {
    throw new Error('useCurrency must be used within CurrencyProvider');
  }
  return context;
}

// ===========================================
// 3. LANGUAGE SWITCHER COMPONENT
// ===========================================
// File: components/LanguageSwitcher.tsx

'use client';

import { useLanguage } from '@/contexts/LanguageContext';

export default function LanguageSwitcher() {
  const { currentLanguage, languages, setLanguage, isLoading } = useLanguage();

  if (isLoading) {
    return <div className="w-32 h-10 bg-gray-200 animate-pulse rounded"></div>;
  }

  return (
    <select
      value={currentLanguage?.code || ''}
      onChange={(e) => setLanguage(e.target.value)}
      className="bg-white border border-gray-300 rounded-lg px-4 py-2"
    >
      {languages.map((lang) => (
        <option key={lang.code} value={lang.code}>
          {lang.flag_emoji} {lang.native_name}
        </option>
      ))}
    </select>
  );
}

// ===========================================
// 4. CURRENCY SWITCHER COMPONENT
// ===========================================
// File: components/CurrencySwitcher.tsx

'use client';

import { useCurrency } from '@/contexts/CurrencyContext';

export default function CurrencySwitcher() {
  const { currentCurrency, currencies, setCurrency, isLoading } = useCurrency();

  if (isLoading) {
    return <div className="w-32 h-10 bg-gray-200 animate-pulse rounded"></div>;
  }

  return (
    <select
      value={currentCurrency?.code || ''}
      onChange={(e) => setCurrency(e.target.value)}
      className="bg-white border border-gray-300 rounded-lg px-4 py-2"
    >
      {currencies.map((curr) => (
        <option key={curr.code} value={curr.code}>
          {curr.symbol} {curr.code}
        </option>
      ))}
    </select>
  );
}

// ===========================================
// 5. PRICE DISPLAY COMPONENT
// ===========================================
// File: components/PriceDisplay.tsx

'use client';

import { useState, useEffect } from 'react';
import { useCurrency } from '@/contexts/CurrencyContext';

interface PriceDisplayProps {
  amount: number;
  baseCurrency?: string;
  className?: string;
}

export default function PriceDisplay({ 
  amount, 
  baseCurrency = 'RON',
  className = ''
}: PriceDisplayProps) {
  const { currentCurrency, formatPrice, convertPrice } = useCurrency();
  const [displayPrice, setDisplayPrice] = useState<string>('');

  useEffect(() => {
    if (!currentCurrency) return;

    const updatePrice = async () => {
      if (currentCurrency.code === baseCurrency) {
        setDisplayPrice(formatPrice(amount));
      } else {
        const converted = await convertPrice(amount, baseCurrency, currentCurrency.code);
        setDisplayPrice(formatPrice(converted));
      }
    };

    updatePrice();
  }, [amount, baseCurrency, currentCurrency]);

  return <span className={className}>{displayPrice}</span>;
}

// ===========================================
// 6. ROOT LAYOUT WITH PROVIDERS
// ===========================================
// File: app/layout.tsx

import { LanguageProvider } from '@/contexts/LanguageContext';
import { CurrencyProvider } from '@/contexts/CurrencyContext';

export default function RootLayout({ children }: { children: React.ReactNode }) {
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
