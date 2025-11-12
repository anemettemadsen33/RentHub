// Temporary wrapper to replace next-intl until we configure it properly
// This allows us to keep the translation keys but return English text

export function useTranslations(namespace?: string) {
  return (key: string, params?: Record<string, any>) => {
    // Return the key as fallback text (better than crashing)
    // You can add specific translations here if needed
    const translations: Record<string, string> = {
      'properties.title': 'Browse Properties',
      'properties.searchPlaceholder': 'Search properties...',
      'properties.filters': 'Filters',
      'properties.sortBy': 'Sort by',
      'properties.viewMode': 'View',
      'navigation.home': 'Home',
      'navigation.properties': 'Properties',
      'comparison.add': 'Add to Compare',
      'comparison.remove': 'Remove from Compare',
      // Add more as needed
    };

    const fullKey = namespace ? `${namespace}.${key}` : key;
    return translations[fullKey] || key;
  };
}

export function useLocale() {
  return 'en';
}
