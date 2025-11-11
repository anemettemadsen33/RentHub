import { getRequestConfig } from 'next-intl/server';
import { cookies, headers } from 'next/headers';

// Supported locales (expanded with es, fr, de)
export const locales = ['en', 'ro', 'es', 'fr', 'de'] as const;
export type Locale = (typeof locales)[number];
export const defaultLocale: Locale = 'en';

export default getRequestConfig(async ({ requestLocale }) => {
  const resolvedRequestLocale = await requestLocale;
  let locale: Locale = locales.includes(resolvedRequestLocale as Locale)
    ? (resolvedRequestLocale as Locale)
    : defaultLocale;

  // Cookie first
  try {
    const cookieStore = await cookies();
    const cookieLocale = cookieStore.get('NEXT_LOCALE')?.value as Locale | undefined;
    if (cookieLocale && locales.includes(cookieLocale)) {
      locale = cookieLocale;
    }
  } catch {}

  // Header fallback
  if (!locales.includes(locale)) {
    try {
      const headersList = await headers();
      const acceptLanguage = headersList.get('accept-language');
      const browserLocale = acceptLanguage?.split(',')[0]?.split('-')[0];
      if (browserLocale && locales.includes(browserLocale as Locale)) {
        locale = browserLocale as Locale;
      } else {
        locale = defaultLocale;
      }
    } catch {
      locale = defaultLocale;
    }
  }

  // Safe messages import with fallback (shared messages located in frontend/messages)
  let messages: any = {};
  try {
    messages = (await import(`../../messages/${locale}.json`)).default;
  } catch {
    messages = (await import(`../../messages/en.json`)).default;
    locale = 'en';
  }

  return { locale, messages };
});
