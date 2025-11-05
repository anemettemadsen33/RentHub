'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';

export type Currency = 'USD' | 'EUR' | 'GBP' | 'RON';

interface CurrencyContextType {
  currency: Currency;
  setCurrency: (currency: Currency) => void;
  exchangeRates: Record<Currency, number>;
  convertPrice: (amount: number, fromCurrency?: Currency) => number;
  formatPrice: (amount: number, currencyCode?: Currency) => string;
  isLoading: boolean;
}

const CurrencyContext = createContext<CurrencyContextType | undefined>(undefined);

export const currencies = {
  USD: { symbol: '$', name: 'US Dollar', flag: '🇺🇸' },
  EUR: { symbol: '€', name: 'Euro', flag: '🇪🇺' },
  GBP: { symbol: '£', name: 'British Pound', flag: '🇬🇧' },
  RON: { symbol: 'RON', name: 'Romanian Leu', flag: '🇷🇴' },
};

interface CurrencyProviderProps {
  children: ReactNode;
}

export function CurrencyProvider({ children }: CurrencyProviderProps) {
  const [currency, setCurrencyState] = useState<Currency>('USD');
  const [exchangeRates, setExchangeRates] = useState<Record<Currency, number>>({
    USD: 1,
    EUR: 0.92,
    GBP: 0.79,
    RON: 4.55,
  });
  const [isLoading, setIsLoading] = useState(false);

  // Load saved currency from localStorage
  useEffect(() => {
    const savedCurrency = localStorage.getItem('currency') as Currency;
    if (savedCurrency && currencies[savedCurrency]) {
      setCurrencyState(savedCurrency);
    }
  }, []);

  // Fetch exchange rates from API
  useEffect(() => {
    const fetchExchangeRates = async () => {
      setIsLoading(true);
      try {
        const response = await fetch(
          `${process.env.NEXT_PUBLIC_API_BASE_URL}/exchange-rates`
        );
        
        if (response.ok) {
          const data = await response.json();
          setExchangeRates(data.rates);
        }
      } catch (error) {
        console.error('Failed to fetch exchange rates:', error);
        // Keep default rates on error
      } finally {
        setIsLoading(false);
      }
    };

    fetchExchangeRates();
    
    // Refresh rates every hour
    const interval = setInterval(fetchExchangeRates, 60 * 60 * 1000);
    
    return () => clearInterval(interval);
  }, []);

  const setCurrency = (newCurrency: Currency) => {
    setCurrencyState(newCurrency);
    localStorage.setItem('currency', newCurrency);
  };

  const convertPrice = (amount: number, fromCurrency: Currency = 'USD'): number => {
    // Convert from base currency to USD first
    const amountInUSD = amount / exchangeRates[fromCurrency];
    
    // Then convert from USD to target currency
    return amountInUSD * exchangeRates[currency];
  };

  const formatPrice = (amount: number, currencyCode: Currency = currency): string => {
    const currencyInfo = currencies[currencyCode];
    
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currencyCode,
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    }).format(amount);
  };

  const value: CurrencyContextType = {
    currency,
    setCurrency,
    exchangeRates,
    convertPrice,
    formatPrice,
    isLoading,
  };

  return (
    <CurrencyContext.Provider value={value}>
      {children}
    </CurrencyContext.Provider>
  );
}

export function useCurrency(): CurrencyContextType {
  const context = useContext(CurrencyContext);
  
  if (context === undefined) {
    throw new Error('useCurrency must be used within a CurrencyProvider');
  }
  
  return context;
}

export default CurrencyContext;
