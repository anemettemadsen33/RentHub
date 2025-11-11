import apiClient from '@/lib/api-client-enhanced';
import { logger } from '@/lib/logger';

function urlBase64ToUint8Array(base64String: string) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export async function subscribeToPush(options?: { vapidPublicKey?: string }) {
  if (typeof window === 'undefined') return null;
  if (!('Notification' in window) || !('serviceWorker' in navigator)) {
    logger.warn('Push not supported in this browser');
    return null;
  }

  let permission = Notification.permission;
  if (permission === 'default') {
    permission = await Notification.requestPermission();
  }
  if (permission !== 'granted') {
    logger.warn('Push permission not granted');
    return null;
  }

  const registration = await navigator.serviceWorker.ready;
  const existing = await registration.pushManager.getSubscription();
  if (existing) return existing;

  const publicKey = options?.vapidPublicKey || process.env.NEXT_PUBLIC_VAPID_PUBLIC_KEY;
  if (!publicKey) {
    logger.error('Missing VAPID public key: set NEXT_PUBLIC_VAPID_PUBLIC_KEY');
    return null;
  }

  const subscription = await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(publicKey),
  });

  try {
    await apiClient.post('/push/subscribe', subscription);
  } catch (e) {
    logger.error('Failed to persist push subscription', e as Error);
  }

  return subscription;
}

export async function unsubscribeFromPush() {
  if (typeof window === 'undefined' || !('serviceWorker' in navigator)) return;
  const registration = await navigator.serviceWorker.ready;
  const sub = await registration.pushManager.getSubscription();
  if (sub) {
    try {
      await apiClient.post('/push/unsubscribe', await sub.toJSON());
    } catch (e) {
      logger.error('Failed to remove push subscription on server', e as Error);
    }
    await sub.unsubscribe();
  }
}
