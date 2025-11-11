'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';

/**
 * Shows a notification when locale is auto-detected from IP
 */
export function LocaleDetectionNotification() {
  const router = useRouter();
  const [shown, setShown] = useState(false);

  useEffect(() => {
    // Only show once per session
    if (shown || sessionStorage.getItem('locale_notification_shown')) {
      return;
    }

    // Check if locale was just auto-detected
    const autoDetected = sessionStorage.getItem('locale_auto_detected');
    const detectedLocale = sessionStorage.getItem('detected_locale');

    if (autoDetected === 'true' && detectedLocale) {
      const languageNames: Record<string, string> = {
        en: 'English',
        ro: 'Română',
      };

      toast.info(
        `Language set to ${languageNames[detectedLocale]} based on your location`,
        {
          duration: 5000,
          action: {
            label: 'Change',
            onClick: () => {
              // Scroll to top where language switcher is located
              window.scrollTo({ top: 0, behavior: 'smooth' });
            },
          },
        }
      );

      sessionStorage.setItem('locale_notification_shown', 'true');
      sessionStorage.removeItem('locale_auto_detected');
      setShown(true);
    }
  }, [shown, router]);

  return null;
}
