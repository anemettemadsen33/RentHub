"use client";
import { useEffect, useState } from 'react';
import { Workbox } from 'workbox-window';
import { toast } from 'sonner';

export function SWUpdatePrompt() {
  const [wb, setWb] = useState<Workbox | null>(null);

  useEffect(() => {
    if ('serviceWorker' in navigator) {
      const workbox = new Workbox('/sw.js');
      setWb(workbox);

      workbox.addEventListener('waiting', () => {
        toast(
          'A new version is available',
          {
            action: {
              label: 'Refresh',
              onClick: () => {
                workbox.messageSkipWaiting();
              },
            },
            description: 'Reload to update to the latest version.',
          }
        );
      });

      workbox.addEventListener('controlling', () => {
        window.location.reload();
      });

      workbox.register();
    }
  }, []);

  return null;
}
