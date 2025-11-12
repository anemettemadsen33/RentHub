import { getRequestConfig } from 'next-intl/server';
import { locales, defaultLocale } from './src/i18n/config';

export default getRequestConfig(async () => {
  // For now, always use the default locale
  const locale = defaultLocale;
  
  return {
    locale,
    messages: (await import(`./messages/${locale}.json`)).default
  };
});
