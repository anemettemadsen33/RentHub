/**
 * IPStack Geolocation Service
 * Detects user location from IP address and determines appropriate locale
 */

interface IPStackResponse {
  country_code: string;
  country_name: string;
  region_code: string;
  region_name: string;
  city: string;
  zip: string;
  latitude: number;
  longitude: number;
  location: {
    languages: Array<{
      code: string;
      name: string;
      native: string;
    }>;
  };
}

// Map country codes to supported locales
const COUNTRY_TO_LOCALE: Record<string, string> = {
  RO: 'ro', // Romania
  MD: 'ro', // Moldova (Romanian speaking)
  // Add more mappings as needed
};

const DEFAULT_LOCALE = 'en';

/**
 * Detect user's locale based on IP geolocation
 */
export async function detectLocaleFromIP(): Promise<string> {
  const apiKey = process.env.NEXT_PUBLIC_IPSTACK_API_KEY;
  
  if (!apiKey) {
    console.warn('IPStack API key not configured');
    return DEFAULT_LOCALE;
  }

  try {
    // Use IPStack's "check" endpoint to get user's IP info
    const response = await fetch(
      `http://api.ipstack.com/check?access_key=${apiKey}&fields=country_code,location.languages`,
      {
        // Cache for 24 hours since location rarely changes
        next: { revalidate: 86400 }
      }
    );

    if (!response.ok) {
      console.error('IPStack API error:', response.status);
      return DEFAULT_LOCALE;
    }

    const data: IPStackResponse = await response.json();
    
    // First, try country-based mapping
    const countryLocale = COUNTRY_TO_LOCALE[data.country_code];
    if (countryLocale) {
      return countryLocale;
    }

    // Fallback to language detection from IPStack
    const primaryLanguage = data.location?.languages?.[0]?.code;
    if (primaryLanguage) {
      // IPStack returns ISO 639-1 codes (e.g., "ro", "en")
      const locale = primaryLanguage.toLowerCase().split('-')[0];
      // Validate it's a supported locale
      if (['en', 'ro'].includes(locale)) {
        return locale;
      }
    }

    return DEFAULT_LOCALE;
  } catch (error) {
    console.error('Error detecting locale from IP:', error);
    return DEFAULT_LOCALE;
  }
}

/**
 * Get full geolocation data for user's IP
 */
export async function getGeolocationData(): Promise<IPStackResponse | null> {
  const apiKey = process.env.NEXT_PUBLIC_IPSTACK_API_KEY;
  
  if (!apiKey) {
    return null;
  }

  try {
    const response = await fetch(
      `http://api.ipstack.com/check?access_key=${apiKey}`,
      {
        next: { revalidate: 86400 }
      }
    );

    if (!response.ok) {
      return null;
    }

    return await response.json();
  } catch (error) {
    console.error('Error fetching geolocation data:', error);
    return null;
  }
}

/**
 * Client-side locale detection with fallback chain
 */
export async function detectClientLocale(): Promise<string> {
  // 1. Check cookie first (user preference takes priority)
  const cookieLocale = document.cookie
    .split('; ')
    .find(row => row.startsWith('NEXT_LOCALE='))
    ?.split('=')[1];
  
  if (cookieLocale && ['en', 'ro'].includes(cookieLocale)) {
    return cookieLocale;
  }

  // 2. Try IP-based detection
  const ipLocale = await detectLocaleFromIP();
  if (ipLocale !== DEFAULT_LOCALE) {
    return ipLocale;
  }

  // 3. Check browser language
  const browserLang = navigator.language.split('-')[0].toLowerCase();
  if (['en', 'ro'].includes(browserLang)) {
    return browserLang;
  }

  // 4. Default
  return DEFAULT_LOCALE;
}
