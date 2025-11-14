// Translation hook that uses static English translations
import enMessages from '../../messages/en.json';

export function useTranslations(namespace?: string) {
  return (key: string, params?: Record<string, any>) => {
    const fullKey = namespace ? `${namespace}.${key}` : key;
    
    // Navigate through nested object
    const keys = fullKey.split('.');
    let value: any = enMessages;
    
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        // Fallback to key if not found
        return key;
      }
    }
    
    // If we found a string, replace params if any
    if (typeof value === 'string' && params) {
      return value.replace(/\{\{(\w+)\}\}/g, (match, paramKey) => {
        return params[paramKey]?.toString() || match;
      });
    }
    
    return typeof value === 'string' ? value : key;
  };
}

export function useLocale() {
  return 'en';
}
