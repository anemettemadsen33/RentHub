"use client";

import { useEffect, useState } from 'react';
import { usePusher } from '@/hooks/use-pusher';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { ToastAction } from '@/components/ui/toast';
import { Bell } from 'lucide-react';

interface Notification {
  id: string;
  type: string;
  title: string;
  message: string;
  data?: any;
  read: boolean;
  created_at: string;
}

export function NotificationProvider({ children }: { children: React.ReactNode }) {
  const { user } = useAuth();
  const { subscribeToUserChannel } = usePusher();
  const { toast } = useToast();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    if (!user) return;

    const unsubscribe = subscribeToUserChannel({
      'booking.new': (data: any) => {
        const notification: Notification = {
          id: `booking-${data.id}`,
          type: 'booking',
          title: 'New Booking',
          message: `${data.guest_name} booked ${data.property_title}`,
          data,
          read: false,
          created_at: data.created_at,
        };

        setNotifications(prev => [notification, ...prev]);
        setUnreadCount(prev => prev + 1);

        toast({
          title: notification.title,
          description: notification.message,
          action: (
            <ToastAction altText="View booking" onClick={() => (window.location.href = `/bookings/${data.id}`)}>
              View
            </ToastAction>
          ),
        });

        // Play notification sound
        if (typeof Audio !== 'undefined') {
          const audio = new Audio('/notification.mp3');
          audio.play().catch(() => {});
        }
      },

      'property.match': (data: any) => {
        const notification: Notification = {
          id: `match-${Date.now()}`,
          type: 'property_match',
          title: 'New Property Match',
          message: `${data.property_count} new properties match "${data.search_name}"`,
          data,
          read: false,
          created_at: new Date().toISOString(),
        };

        setNotifications(prev => [notification, ...prev]);
        setUnreadCount(prev => prev + 1);

        toast({
          title: notification.title,
          description: notification.message,
          action: (
            <ToastAction altText="View matches" onClick={() => (window.location.href = '/saved-searches')}>
              View
            </ToastAction>
          ),
        });
      },

      'message.new': (data: any) => {
        const notification: Notification = {
          id: `message-${data.id}`,
          type: 'message',
          title: 'New Message',
          message: `${data.sender_name}: ${data.preview}`,
          data,
          read: false,
          created_at: data.created_at,
        };

        setNotifications(prev => [notification, ...prev]);
        setUnreadCount(prev => prev + 1);

        toast({
          title: notification.title,
          description: notification.message,
        });
      },
    });

    return () => {
      unsubscribe();
    };
  }, [user, subscribeToUserChannel, toast]);

  return <>{children}</>;
}
