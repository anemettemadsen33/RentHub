'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';

/**
 * Auto-detect locale on first visit using IP geolocation
 * Only runs once if no locale preference is set
 */
export function LocaleAutoDetect() {
  const router = useRouter();
  const [hasDetected, setHasDetected] = useState(false);

  useEffect(() => {
    // Only run once
    if (hasDetected) return;

    // Check if user already has a locale preference
    const existingLocale = document.cookie
      .split('; ')
      .find(row => row.startsWith('NEXT_LOCALE='));
    
    if (existingLocale) {
      setHasDetected(true);
      return;
    }

    // Check if we've already attempted detection in this session
    const detectionAttempted = sessionStorage.getItem('locale_detection_attempted');
    if (detectionAttempted) {
      setHasDetected(true);
      return;
    }

    // Perform IP-based locale detection
    const detectAndSetLocale = async () => {
      try {
        const apiKey = process.env.NEXT_PUBLIC_IPSTACK_API_KEY;
        if (!apiKey) return;

        const response = await fetch(
          `http://api.ipstack.com/check?access_key=${apiKey}&fields=country_code,location.languages`,
          { cache: 'force-cache' }
        );

        if (!response.ok) return;

        const data = await response.json();
        
        // Map country code to locale
        const countryToLocale: Record<string, string> = {
          RO: 'ro',
          MD: 'ro',
        };

        let detectedLocale = countryToLocale[data.country_code];
        
        // Fallback to language detection
        if (!detectedLocale && data.location?.languages?.[0]) {
          const langCode = data.location.languages[0].code.toLowerCase().split('-')[0];
          if (['en', 'ro'].includes(langCode)) {
            detectedLocale = langCode;
          }
        }

        // Set locale if detected and different from default
        if (detectedLocale && detectedLocale !== 'en') {
          document.cookie = `NEXT_LOCALE=${detectedLocale}; path=/; max-age=${60*60*24*365}`;
          
          // Mark for notification
          sessionStorage.setItem('locale_auto_detected', 'true');
          sessionStorage.setItem('detected_locale', detectedLocale);
          
          // Refresh to apply new locale
          router.refresh();
        }

        // Mark detection as attempted
        sessionStorage.setItem('locale_detection_attempted', 'true');
        setHasDetected(true);
      } catch (error) {
        console.error('Locale auto-detection failed:', error);
        sessionStorage.setItem('locale_detection_attempted', 'true');
        setHasDetected(true);
      }
    };

    detectAndSetLocale();
  }, [hasDetected, router]);

  return null; // This component doesn't render anything
}
