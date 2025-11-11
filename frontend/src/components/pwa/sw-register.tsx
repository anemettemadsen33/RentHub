"use client";
import { useEffect } from 'react';
import { logger } from '@/lib/logger';

export function ServiceWorkerRegister() {
  useEffect(() => {
    if (typeof window === 'undefined') return;
    if ('serviceWorker' in navigator) {
      const registerSW = async () => {
        try {
          const reg = await navigator.serviceWorker.register('/sw.js');
          logger.info('Service worker registered', { scope: reg.scope });
        } catch (err) {
          logger.error('Service worker registration failed', err as Error);
        }
      };
      registerSW();
    }
  }, []);
  return null;
}
