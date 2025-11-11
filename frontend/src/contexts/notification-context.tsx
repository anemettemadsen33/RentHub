"use client";
import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { notificationsService } from '@/lib/api-service';
import { io, Socket } from 'socket.io-client';
import { createLogger } from '@/lib/logger';

const notificationLogger = createLogger('NotificationContext');

interface NotificationContextValue {
  unreadCount: number;
  loading: boolean;
  refresh: () => Promise<void>;
  socket?: Socket;
}

const NotificationContext = createContext<NotificationContextValue | undefined>(undefined);

export function NotificationProvider({ children }: { children: ReactNode }) {
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [socket, setSocket] = useState<Socket | undefined>();

  const fetchUnread = async () => {
    setLoading(true);
    try {
      const data = await notificationsService.unreadCount();
      setUnreadCount(data.count || 0);
      notificationLogger.debug('Fetched unread count', { count: data.count });
    } catch (e) {
      // Silent fail in dev - user might not be authenticated
      notificationLogger.debug('Failed to fetch unread count (user may not be authenticated)', { error: e });
      setUnreadCount(0);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    fetchUnread();
    const interval = setInterval(fetchUnread, 60_000);

    // Init socket for live unread updates
    const s = io(process.env.NEXT_PUBLIC_WEBSOCKET_URL || 'http://localhost:6001', {
      auth: { token },
    });
    setSocket(s);
    s.on('notification', () => {
      // Increment locally rather than refetch for speed
      setUnreadCount((c) => c + 1);
    });
    s.on('notification-read', fetchUnread);
    s.on('notification-refresh', fetchUnread);

    return () => {
      clearInterval(interval);
      s.disconnect();
    };
  }, []);

  return (
    <NotificationContext.Provider value={{ unreadCount, loading, refresh: fetchUnread, socket }}>
      {children}
    </NotificationContext.Provider>
  );
}

export function useNotifications() {
  const ctx = useContext(NotificationContext);
  if (!ctx) throw new Error('useNotifications must be used within NotificationProvider');
  return ctx;
}
