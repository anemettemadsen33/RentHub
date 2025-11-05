import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

// Translation resources
import enCommon from '../../public/locales/en/common.json';
import roCommon from '../../public/locales/ro/common.json';
import esCommon from '../../public/locales/es/common.json';
import frCommon from '../../public/locales/fr/common.json';
import deCommon from '../../public/locales/de/common.json';

export const defaultLocale = 'en';
export const locales = ['en', 'ro', 'es', 'fr', 'de'] as const;
export type Locale = typeof locales[number];

export const languages = {
  en: { name: 'English', flag: '🇬🇧' },
  ro: { name: 'Română', flag: '🇷🇴' },
  es: { name: 'Español', flag: '🇪🇸' },
  fr: { name: 'Français', flag: '🇫🇷' },
  de: { name: 'Deutsch', flag: '🇩🇪' },
};

const resources = {
  en: { common: enCommon },
  ro: { common: roCommon },
  es: { common: esCommon },
  fr: { common: frCommon },
  de: { common: deCommon },
};

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    defaultNS: 'common',
    fallbackLng: defaultLocale,
    supportedLngs: locales,
    
    detection: {
      order: ['localStorage', 'navigator', 'htmlTag'],
      caches: ['localStorage'],
      lookupLocalStorage: 'i18nextLng',
    },

    interpolation: {
      escapeValue: false, // React already escapes
    },

    react: {
      useSuspense: false,
    },
  });

export default i18n;

// Helper functions
export function getLocaleName(locale: Locale): string {
  return languages[locale]?.name || locale;
}

export function getLocaleFlag(locale: Locale): string {
  return languages[locale]?.flag || '🌐';
}

export function isValidLocale(locale: string): locale is Locale {
  return locales.includes(locale as Locale);
}
