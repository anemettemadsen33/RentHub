import { useEffect, useState } from 'react';

export function useOnlineStatus(): boolean {
  const [isOnline, setIsOnline] = useState(
    typeof navigator !== 'undefined' ? navigator.onLine : true
  );

  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  return isOnline;
}

export async function cacheProperty(propertyId: string): Promise<void> {
  if ('caches' in window) {
    const cache = await caches.open('property-cache');
    await cache.add(`/api/properties/${propertyId}`);
  }
}

export async function getCachedProperty(propertyId: string): Promise<unknown> {
  if ('caches' in window) {
    const cache = await caches.open('property-cache');
    const response = await cache.match(`/api/properties/${propertyId}`);
    if (response) return await response.json();
  }
  return null;
}
