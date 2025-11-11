"use client";

import { useEffect, useState, useCallback } from 'react';
import Pusher from 'pusher-js';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';

interface NotificationData {
  id: string;
  type: string;
  message: string;
  data: any;
  created_at: string;
}

export function usePusher() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [pusher, setPusher] = useState<Pusher | null>(null);
  const [connected, setConnected] = useState(false);

  useEffect(() => {
    if (!user) {
      if (pusher) {
        pusher.disconnect();
        setPusher(null);
        setConnected(false);
      }
      return;
    }

    // Initialize Pusher
    const pusherInstance = new Pusher(process.env.NEXT_PUBLIC_PUSHER_KEY!, {
      cluster: process.env.NEXT_PUBLIC_PUSHER_CLUSTER!,
      authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
      auth: {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      },
    });

    pusherInstance.connection.bind('connected', () => {
      console.log('Pusher connected');
      setConnected(true);
    });

    pusherInstance.connection.bind('disconnected', () => {
      console.log('Pusher disconnected');
      setConnected(false);
    });

    pusherInstance.connection.bind('error', (err: any) => {
      console.error('Pusher error:', err);
    });

    setPusher(pusherInstance);

    return () => {
      pusherInstance.disconnect();
    };
  }, [user, pusher]);

  const subscribe = useCallback((channelName: string, eventName: string, callback: (data: any) => void) => {
    if (!pusher) return () => {};

    const channel = pusher.subscribe(channelName);
    channel.bind(eventName, callback);

    return () => {
      channel.unbind(eventName, callback);
      pusher.unsubscribe(channelName);
    };
  }, [pusher]);

  const subscribeToUserChannel = useCallback((eventHandlers: Record<string, (data: any) => void>) => {
    if (!user || !pusher) return () => {};

    const channelName = `private-user.${user.id}`;
    const channel = pusher.subscribe(channelName);

    Object.entries(eventHandlers).forEach(([eventName, handler]) => {
      channel.bind(eventName, handler);
    });

    return () => {
      Object.keys(eventHandlers).forEach(eventName => {
        channel.unbind(eventName);
      });
      pusher.unsubscribe(channelName);
    };
  }, [user, pusher]);

  return {
    pusher,
    connected,
    subscribe,
    subscribeToUserChannel,
  };
}
