"use client";

import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { logPWAEvent } from '@/lib/pwa-analytics';

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
}

export function PWAInstallPrompt() {
  const [deferred, setDeferred] = useState<BeforeInstallPromptEvent | null>(null);

  useEffect(() => {
    const handler = (e: Event) => {
      e.preventDefault();
  setDeferred(e as BeforeInstallPromptEvent);
  logPWAEvent('install_prompt_shown');
      toast(
        'Install RentHub for a better experience',
        {
          action: {
            label: 'Install',
            onClick: async () => {
              const dp = deferred || (e as BeforeInstallPromptEvent);
              try {
                await dp.prompt();
                const choice = await dp.userChoice;
                if (choice.outcome === 'accepted') {
                  toast.success('Installing...');
                  logPWAEvent('install_prompt_accepted');
                } else {
                  logPWAEvent('install_prompt_dismissed');
                }
              } catch {}
              setDeferred(null);
            },
          },
        }
      );
    };

    window.addEventListener('beforeinstallprompt', handler);
    return () => window.removeEventListener('beforeinstallprompt', handler);
  }, [deferred]);

  return null;
}
