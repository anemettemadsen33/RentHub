import { useEffect, useRef, useState, useCallback } from 'react';
import { io, Socket } from 'socket.io-client';
import type { Message } from '@/types/extended';

interface UseChatOptions {
  userId?: number;
  activeConversationId?: number | null;
  onMessage?: (message: Message) => void;
  onBackgroundMessage?: (message: Message) => void;
  onTyping?: (conversationId: number, userId: number) => void;
  onMessageRead?: (messageId: number) => void;
  onUsersOnline?: (userIds: number[]) => void;
}

interface UseChatReturn {
  emitTyping: () => void;
  emitMessageRead: (messageId: number) => void;
  emitSendMessage: (conversationId: number, message: Message) => void;
  onlineUsers: Set<number>;
  socketConnected: boolean;
  disconnect: () => void;
  isConnected: boolean; // alias for socketConnected for ergonomic API
}

/**
 * Light abstraction for Socket.IO chat events.
 * Phase 1: keeps raw socket.io; Phase 2 will allow swap to Laravel Echo/Pusher seamlessly.
 */
export function useChat(options: UseChatOptions): UseChatReturn {
  const { userId, activeConversationId, onMessage, onBackgroundMessage, onTyping, onMessageRead, onUsersOnline } = options;
  const socketRef = useRef<Socket | null>(null);
  const [onlineUsers, setOnlineUsers] = useState<Set<number>>(new Set());
  const [socketConnected, setSocketConnected] = useState(false);
  const typingThrottleRef = useRef<number | null>(null);

  useEffect(() => {
    if (!userId) return; // Wait for auth
    if (socketRef.current) return; // Already connected

    const socket = io(process.env.NEXT_PUBLIC_WEBSOCKET_URL || 'http://localhost:6001', {
      auth: { token: typeof window !== 'undefined' ? localStorage.getItem('token') : undefined },
      reconnectionAttempts: 5,
    });
    socketRef.current = socket;

    socket.on('connect', () => setSocketConnected(true));
    socket.on('disconnect', () => setSocketConnected(false));
    socket.on('connect_error', (err) => console.warn('[chat] connect_error', err.message));

    socket.on('new-message', (message: Message) => {
      if (activeConversationId && message.conversation_id === activeConversationId) {
        onMessage?.(message);
      } else {
        onBackgroundMessage?.(message);
      }
    });

    socket.on('user-typing', ({ conversationId, userId: remoteUserId }: { conversationId: number; userId: number }) => {
      onTyping?.(conversationId, remoteUserId);
    });

    socket.on('message-read', ({ messageId }: { messageId: number }) => {
      onMessageRead?.(messageId);
    });

    socket.on('users-online', (userIds: number[]) => {
      const setVal = new Set(userIds);
      setOnlineUsers(setVal);
      onUsersOnline?.(userIds);
    });

    return () => {
      socket.disconnect();
      socketRef.current = null;
      setSocketConnected(false);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userId]);

  // Update active conversation id reference without re-binding socket listeners
  const emitTyping = useCallback(() => {
    const now = Date.now();
    if (typingThrottleRef.current && now - typingThrottleRef.current < 1200) return;
    typingThrottleRef.current = now;
    if (socketRef.current && activeConversationId && userId) {
      socketRef.current.emit('typing', { conversationId: activeConversationId, userId });
    }
  }, [activeConversationId, userId]);

  const emitMessageRead = useCallback((messageId: number) => {
    if (socketRef.current) socketRef.current.emit('message-read', { messageId });
  }, []);

  const emitSendMessage = useCallback((conversationId: number, message: Message) => {
    if (socketRef.current) socketRef.current.emit('send-message', { conversationId, message });
  }, []);

  const disconnect = useCallback(() => {
    if (socketRef.current) {
      socketRef.current.disconnect();
      socketRef.current = null;
      setSocketConnected(false);
    }
  }, []);

  return { emitTyping, emitMessageRead, emitSendMessage, onlineUsers, socketConnected, disconnect, isConnected: socketConnected };
}
