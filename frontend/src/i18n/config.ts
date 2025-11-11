// Pure i18n configuration (no server-only imports) for safe use in client components
export const locales = ['en', 'ro', 'es', 'fr', 'de'] as const;
export type Locale = (typeof locales)[number];
export const defaultLocale: Locale = 'en';
