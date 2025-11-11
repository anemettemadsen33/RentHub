/**
 * React Hook for Laravel Echo
 * 
 * Provides convenient React integration for real-time channels
 */

import { useEffect, useRef, useState } from 'react';
import { getEcho, disconnectEcho, echoChannels } from '@/lib/echo';
import type Echo from 'laravel-echo';

interface UseEchoOptions {
  authToken?: string;
  enabled?: boolean;
}

/**
 * Hook to access the Echo instance
 */
export function useEcho(options: UseEchoOptions = {}) {
  const { authToken, enabled = true } = options;
  // Echo generic param expects channel type; we use any to avoid strict generic requirement
  const [echo, setEcho] = useState<Echo<any> | null>(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    if (!enabled) return;

    const echoInstance = getEcho(authToken);
    setEcho(echoInstance);

    // Listen for connection events
    const pusher = echoInstance.connector.pusher;
    
    const handleConnected = () => setIsConnected(true);
    const handleDisconnected = () => setIsConnected(false);

    pusher.connection.bind('connected', handleConnected);
    pusher.connection.bind('disconnected', handleDisconnected);

    // Set initial state
    if (pusher.connection.state === 'connected') {
      setIsConnected(true);
    }

    return () => {
      pusher.connection.unbind('connected', handleConnected);
      pusher.connection.unbind('disconnected', handleDisconnected);
    };
  }, [authToken, enabled]);

  return { echo, isConnected };
}

/**
 * Hook to subscribe to a channel
 */
export function useChannel(
  channelName: string,
  options: UseEchoOptions = {}
) {
  const { authToken, enabled = true } = options;
  const [channel, setChannel] = useState<any>(null);
  const echoRef = useRef(getEcho(authToken));

  useEffect(() => {
    if (!enabled || !channelName) return;

    const echo = echoRef.current;
    const ch = echo.channel(channelName);
    setChannel(ch);

    return () => {
      echo.leave(channelName);
    };
  }, [channelName, enabled]);

  return channel;
}

/**
 * Hook to subscribe to a private channel
 */
export function usePrivateChannel(
  channelName: string,
  authToken: string,
  enabled: boolean = true
) {
  const [channel, setChannel] = useState<any>(null);
  const echoRef = useRef(getEcho(authToken));

  useEffect(() => {
    if (!enabled || !channelName || !authToken) return;

    const echo = echoRef.current;
    const ch = echo.private(channelName);
    setChannel(ch);

    return () => {
      echo.leave(channelName);
    };
  }, [channelName, authToken, enabled]);

  return channel;
}

/**
 * Hook to subscribe to a presence channel
 */
export function usePresenceChannel(
  channelName: string,
  authToken: string,
  enabled: boolean = true
) {
  const [channel, setChannel] = useState<any>(null);
  const [members, setMembers] = useState<any[]>([]);
  const echoRef = useRef(getEcho(authToken));

  useEffect(() => {
    if (!enabled || !channelName || !authToken) return;

    const echo = echoRef.current;
    const ch = echo.join(channelName);
    setChannel(ch);

    // Track members
    ch.here((users: any[]) => {
      setMembers(users);
    });

    ch.joining((user: any) => {
      setMembers(prev => [...prev, user]);
    });

    ch.leaving((user: any) => {
      setMembers(prev => prev.filter(m => m.id !== user.id));
    });

    return () => {
      echo.leave(channelName);
    };
  }, [channelName, authToken, enabled]);

  return { channel, members };
}

/**
 * Hook to listen for a specific event on a channel
 */
export function useChannelEvent<T = any>(
  channelName: string,
  eventName: string,
  callback: (data: T) => void,
  options: UseEchoOptions = {}
) {
  const { authToken, enabled = true } = options;
  const callbackRef = useRef(callback);
  const echoRef = useRef(getEcho(authToken));

  // Update callback ref when it changes
  useEffect(() => {
    callbackRef.current = callback;
  }, [callback]);

  useEffect(() => {
    if (!enabled || !channelName || !eventName) return;

    const echo = echoRef.current;
    const channel = echo.channel(channelName);

    const handler = (data: T) => {
      callbackRef.current(data);
    };

    channel.listen(eventName, handler);

    return () => {
      channel.stopListening(eventName, handler);
      echo.leave(channelName);
    };
  }, [channelName, eventName, enabled]);
}

/**
 * Hook for user notifications channel
 */
export function useUserNotifications(
  userId: number | null,
  authToken: string | null,
  onNotification: (notification: any) => void
) {
  const [channel, setChannel] = useState<any>(null);

  useEffect(() => {
    if (!userId || !authToken) return;

    const ch = echoChannels.userNotifications(userId, authToken);
    setChannel(ch);

    // Listen for notification events
    ch.notification(onNotification);

    return () => {
      ch.stopListening('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated');
    };
  }, [userId, authToken, onNotification]);

  return channel;
}

/**
 * Hook for conversation messages
 */
export function useConversationMessages(
  conversationId: number | null,
  authToken: string | null,
  onMessage: (message: any) => void,
  onTyping?: (data: { user_id: number; is_typing: boolean }) => void
) {
  const [channel, setChannel] = useState<any>(null);

  useEffect(() => {
    if (!conversationId || !authToken) return;

    const ch = echoChannels.conversation(conversationId, authToken);
    setChannel(ch);

    // Listen for new messages
    ch.listen('MessageSent', onMessage);

    // Listen for typing indicators
    if (onTyping) {
      ch.listenForWhisper('typing', onTyping);
    }

    return () => {
      ch.stopListening('MessageSent');
      if (onTyping) {
        ch.stopListeningForWhisper('typing');
      }
    };
  }, [conversationId, authToken, onMessage, onTyping]);

  const whisperTyping = (isTyping: boolean) => {
    if (channel) {
      channel.whisper('typing', { is_typing: isTyping });
    }
  };

  return { channel, whisperTyping };
}

/**
 * Cleanup on app unmount
 */
export function useEchoCleanup() {
  useEffect(() => {
    return () => {
      disconnectEcho();
    };
  }, []);
}
