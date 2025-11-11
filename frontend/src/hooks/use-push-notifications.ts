'use client';

import { useEffect, useState } from 'react';
import { notify } from '@/lib/notify';

interface PushNotificationOptions {
  title: string;
  body: string;
  icon?: string;
  badge?: string;
  tag?: string;
  data?: any;
  onClick?: () => void;
}

interface UsePushNotificationsDeps {
  notificationImpl?: typeof Notification | undefined;
}

export function usePushNotifications(deps?: UsePushNotificationsDeps) {
  // Allow injection of a mock Notification implementation for testing
  const NotificationAPI: typeof Notification | undefined = deps?.notificationImpl ??
    (typeof window !== 'undefined' ? (window as any).Notification : undefined);

  const [permission, setPermission] = useState<NotificationPermission>('default');
  const [isSupported, setIsSupported] = useState<boolean>(!!NotificationAPI);
  

  useEffect(() => {
    if (NotificationAPI) {
      setIsSupported(true);
      // In some browsers permission may be undefined; fall back to 'default'
      setPermission((NotificationAPI as any).permission ?? 'default');
    }
  }, [NotificationAPI]);

  const requestPermission = async (): Promise<boolean> => {
    if (!isSupported) {
      notify.error({
        title: 'Not Supported',
        description: 'Push notifications are not supported in this browser',
      });
      return false;
    }

    try {
      const result = await (NotificationAPI as any).requestPermission();
      setPermission(result);

      if (result === 'granted') {
        notify.success({
          title: 'Success',
          description: 'Push notifications enabled',
        });
        return true;
      } else if (result === 'denied') {
        notify.error({
          title: 'Permission Denied',
          description: 'You have denied push notifications',
        });
        return false;
      }

      return false;
    } catch (error) {
      console.error('Error requesting notification permission:', error);
      return false;
    }
  };

  const showNotification = async (options: PushNotificationOptions): Promise<void> => {
    if (!isSupported || !NotificationAPI) {
      console.warn('Notifications not supported');
      return;
    }

    if (permission !== 'granted') {
      const granted = await requestPermission();
      if (!granted) return;
    }

    try {
      const notification = new (NotificationAPI as any)(options.title, {
        body: options.body,
        icon: options.icon || '/logo.png',
        badge: options.badge || '/logo.png',
        tag: options.tag || 'renthub-notification',
        data: options.data,
        requireInteraction: false,
        silent: false,
      });

      if (options.onClick) {
        notification.onclick = (event: MouseEvent) => {
          event.preventDefault();
          window.focus();
          options.onClick?.();
          notification.close();
        };
      }

      // Auto close after 5 seconds
      setTimeout(() => {
        notification.close();
      }, 5000);
    } catch (error) {
      console.error('Error showing notification:', error);
    }
  };

  // Pusher Beams interest subscription stubs (will be used for favorites price-change notifications)
  const subscribeToInterest = async (interest: string): Promise<void> => {
    // TODO: Integrate with Pusher Beams SDK when backend triggers price-change events
    console.log('[Beams] Subscribe to interest:', interest);
  };

  const unsubscribeFromInterest = async (interest: string): Promise<void> => {
    console.log('[Beams] Unsubscribe from interest:', interest);
  };

  return {
    isSupported,
    permission,
    requestPermission,
    showNotification,
    subscribeToInterest,
    unsubscribeFromInterest,
  };
}
