'use client';

import { useEffect, useState, useRef, useCallback } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { notify } from '@/lib/notify';
import { MainLayout } from '@/components/layouts/main-layout';
import { MessageListSkeleton, ConversationSkeleton } from '@/components/skeletons';
import { Skeleton } from '@/components/ui/skeleton';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { ScrollArea } from '@/components/ui/scroll-area';
import { EmptyState } from '@/components/empty-state';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
  TooltipProvider,
} from '@/components/ui/tooltip';
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog';
import {
  Search,
  Send,
  Image as ImageIcon,
  Paperclip,
  MoreVertical,
  Phone,
  Video,
  Info,
  ArrowLeft,
  MessageSquare,
  X,
  File,
  Archive,
  Download,
} from 'lucide-react';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import type { Conversation, Message } from '@/types/extended';
import { io, Socket } from 'socket.io-client';
// TODO: Phase 2: Replace raw socket.io usage with Laravel Echo (Pusher) when backend broadcasting
// channels for conversations are finalized. We'll keep a lightweight abstraction for now.
import { usePushNotifications } from '@/hooks/use-push-notifications';
import { useChat } from '@/hooks/use-chat';
import { announceToScreenReader } from '@/lib/a11y-utils';
import { useTranslations } from 'next-intl';

export default function MessagesPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { user } = useAuth();
  const t = useTranslations('chat');
  const tMsg = useTranslations('messages');
  const tNotify = useTranslations('notify');
  
  const { showNotification, requestPermission, permission } = usePushNotifications();
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [selectedConversation, setSelectedConversation] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [loading, setLoading] = useState(true);
  const [messagesLoading, setMessagesLoading] = useState(false);
  const [sending, setSending] = useState(false);
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
  const [typing, setTyping] = useState(false);
  const [onlineUsers, setOnlineUsers] = useState<Set<number>>(new Set());
  const fileInputRef = useRef<HTMLInputElement>(null);
  const socketRef = useRef<Socket | null>(null);

  // Define all callback functions before useEffects
  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, []);

  const loadConversations = useCallback(async () => {
    try {
      const response = await apiClient.get('/conversations');
      const data = response.data.data || [];
      setConversations(data);
      // Cache conversations for offline use
      if (typeof window !== 'undefined' && data.length > 0) {
        localStorage.setItem('chat_conversations_cache', JSON.stringify(data));
      }
    } catch (error: any) {
      console.warn('Failed to load conversations from API, trying cache', error.message);
      // Try cache first
      const cached = typeof window !== 'undefined' ? localStorage.getItem('chat_conversations_cache') : null;
      if (cached) {
        try {
          const parsed = JSON.parse(cached);
          setConversations(parsed);
          notify.info({
            title: tNotify('offlineModeTitle'),
            description: tNotify('offlineModeDesc'),
          });
        } catch {}
      } else {
        // No cache, show empty state
        setConversations([]);
      }
    } finally {
      setLoading(false);
    }
  }, [tNotify]);

  const loadConversation = useCallback(async (id: number) => {
    const conversation = conversations.find((c) => c.id === id);
    if (conversation) {
      setSelectedConversation(conversation);
    }
  }, [conversations]);

  const loadMessages = useCallback(async (conversationId: number) => {
    setMessagesLoading(true);
    const cacheKey = `chat_messages_${conversationId}`;
    try {
      const response = await apiClient.get(API_ENDPOINTS.messages.list(conversationId));
      const incoming: Message[] = (response.data.data || []).map((m: Message) => ({ ...m, status: m.read ? 'read' : 'sent' }));
      setMessages(incoming);
      // Cache for offline
      if (typeof window !== 'undefined' && incoming.length > 0) {
        localStorage.setItem(cacheKey, JSON.stringify(incoming));
      }
      // Mark as read
      await apiClient.post(API_ENDPOINTS.conversations.markAllAsRead(conversationId));
      loadConversations(); // Update unread count
    } catch (error: any) {
      console.warn('Failed to load messages from API, trying cache', error.message);
      const cached = typeof window !== 'undefined' ? localStorage.getItem(cacheKey) : null;
      if (cached) {
        try {
          const parsed: Message[] = JSON.parse(cached);
          setMessages(parsed);
        } catch {}
      } else {
        // No cache, empty messages
        setMessages([]);
      }
    }
    finally {
      setMessagesLoading(false);
    }
  }, [loadConversations]);

  // Use chat hook to manage socket events
  const chat = useChat({
    userId: user?.id,
    activeConversationId: selectedConversation?.id ?? null,
    onMessage: (message) => {
      setMessages((prev) => {
        const updated = [...prev, message];
        // Cache updated messages
        if (selectedConversation && typeof window !== 'undefined') {
          const cacheKey = `chat_messages_${selectedConversation.id}`;
          localStorage.setItem(cacheKey, JSON.stringify(updated));
        }
        return updated;
      });
      markMessageAsRead(message.id);
      scrollToBottom();
      
      // Announce new message to screen readers
      if (message.sender_id !== user?.id) {
        const sender = selectedConversation?.other_user?.name || 'Someone';
        announceToScreenReader(`New message from ${sender}: ${message.message}`, 'polite');
      }
    },
    onBackgroundMessage: (message) => {
      const conversation = conversations.find((c) => c.id === message.conversation_id);
      if (conversation && permission === 'granted') {
        showNotification({
          title: `New message from ${conversation.other_user?.name}`,
          body: message.message || 'Sent an attachment',
          icon: conversation.other_user?.avatar_url,
          tag: `message-${message.id}`,
          onClick: () => router.push(`/messages?conversation=${conversation.id}`),
        });
      }
      loadConversations();
    },
    onTyping: (conversationId, remoteUserId) => {
      if (selectedConversation && conversationId === selectedConversation.id && remoteUserId !== user?.id) {
        setTyping(true);
        setTimeout(() => setTyping(false), 2500);
      }
    },
    onMessageRead: (messageId) => {
      setMessages((prev) => prev.map((m) => (m.id === messageId ? { ...m, read: true } : m)));
    },
    onUsersOnline: (userIds) => setOnlineUsers(new Set(userIds)),
  });

  const flushOutbox = useCallback(async (conversationId: number) => {
    try {
      const raw = localStorage.getItem('chat_outbox');
      const outbox: Array<{ conversationId: number; content: string; client_id: string }> = raw ? JSON.parse(raw) : [];
      const remaining: typeof outbox = [];
      for (const item of outbox) {
        if (item.conversationId !== conversationId) {
          remaining.push(item);
          continue;
        }
        const optimistic = messages.find((m) => m.client_id === item.client_id);
        if (optimistic && optimistic.status === 'pending') {
          // attempt resend
          const formData = new FormData();
          formData.append('content', item.content);
          try {
            const response = await apiClient.post(
              API_ENDPOINTS.messages.create(conversationId),
              formData,
              { headers: { 'Content-Type': 'multipart/form-data' } }
            );
            const saved: Message = { ...response.data.data, status: 'sent' };
            setMessages((prev) => prev.map((m) => (m.client_id === item.client_id ? saved : m)));
            chat.emitSendMessage(conversationId, saved);
          } catch (e) {
            remaining.push(item);
          }
        }
      }
      localStorage.setItem('chat_outbox', JSON.stringify(remaining));
    } catch {}
  }, [messages, chat]);

  const markMessageAsRead = useCallback(async (messageId: number) => {
    try {
      await apiClient.post(API_ENDPOINTS.messages.markAsRead(messageId));
      chat.emitMessageRead(messageId);
    } catch (error) {
      console.error('Failed to mark message as read');
    }
  }, [chat]);

  const retrySend = useCallback(async (failed: Message) => {
    if (!selectedConversation || !user || failed.status !== 'failed') return;
    const content = failed.message;
    const client_id = failed.client_id || `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    setMessages((prev) => prev.map((m) => (m.client_id === failed.client_id ? { ...m, status: 'pending' } : m)));
    const formData = new FormData();
    formData.append('content', content);
    try {
      const response = await apiClient.post(
        API_ENDPOINTS.messages.create(selectedConversation.id),
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const saved: Message = { ...response.data.data, status: 'sent' };
      setMessages((prev) => prev.map((m) => (m.client_id === failed.client_id ? saved : m)));
      chat.emitSendMessage(selectedConversation.id, saved);
    } catch (err) {
      setMessages((prev) => prev.map((m) => (m.client_id === failed.client_id ? { ...m, status: 'failed' } : m)));
    }
  }, [selectedConversation, user, chat]);

  const handleSendMessage = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    if ((!newMessage.trim() && selectedFiles.length === 0) || !selectedConversation || !user) return;

    const client_id = `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    const optimistic: Message = {
      id: -1, // temporary placeholder
      client_id,
      status: 'pending',
      conversation_id: selectedConversation.id,
      sender_id: user.id,
      recipient_id: selectedConversation.other_user?.id || 0,
      message: newMessage,
      read: false,
      created_at: new Date().toISOString(),
    };

    setMessages((prev) => [...prev, optimistic]);
    const originalMessage = newMessage;
    setNewMessage('');
    setSelectedFiles([]);
    setSending(true);

    const formData = new FormData();
    if (originalMessage.trim()) formData.append('content', originalMessage.trim());
    for (const file of selectedFiles) formData.append('attachments[]', file);

    try {
      const response = await apiClient.post(
        API_ENDPOINTS.messages.create(selectedConversation.id),
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const saved: Message = { ...response.data.data, status: 'sent' };
      setMessages((prev) => prev.map((m) => (m.client_id === client_id ? saved : m)));
      chat.emitSendMessage(selectedConversation.id, saved);
      loadConversations();
      // Update cache
      if (typeof window !== 'undefined') {
        const cacheKey = `chat_messages_${selectedConversation.id}`;
        const current = messages.map((m) => (m.client_id === client_id ? saved : m));
        localStorage.setItem(cacheKey, JSON.stringify(current));
      }
    } catch (error) {
      // Mark optimistic as failed and allow retry
      setMessages((prev) => prev.map((m) => (m.client_id === client_id ? { ...m, status: 'failed' } : m)));
  notify.error({ title: tNotify('sendFailedTitle'), description: tNotify('sendFailedQueued') });
      // Persist to localStorage outbox
      try {
        const outbox = JSON.parse(localStorage.getItem('chat_outbox') || '[]');
        outbox.push({ conversationId: selectedConversation.id, content: originalMessage, client_id });
        localStorage.setItem('chat_outbox', JSON.stringify(outbox));
      } catch {}
    } finally {
      setSending(false);
    }
  }, [newMessage, selectedFiles, selectedConversation, user, chat, messages, loadConversations, tNotify]);

  const handleFileSelect = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const files = Array.from(e.target.files);
      setSelectedFiles((prev) => [...prev, ...files]);
    }
  }, []);

  const removeFile = useCallback((index: number) => {
    setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
  }, []);

  const handleTyping = useCallback(() => {
    if (selectedConversation) {
      chat.emitTyping();
    }
  }, [selectedConversation, chat]);

  const handleArchiveConversation = useCallback(async (conversationId: number) => {
    try {
      await apiClient.post(API_ENDPOINTS.conversations.archive(conversationId));
      notify.success({
        title: tNotify('success'),
        description: tNotify('conversationArchived'),
      });
      loadConversations();
      setSelectedConversation(null);
    } catch (error) {
      notify.error({
        title: tNotify('error'),
        description: tNotify('failedArchiveConversation'),
      });
    }
  }, [loadConversations, tNotify]);

  const getFileIcon = useCallback((fileType: string) => {
    if (fileType.startsWith('image/')) return <ImageIcon className="h-4 w-4" />;
    return <File className="h-4 w-4" />;
  }, []);

  const formatFileSize = useCallback((bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }, []);

  const formatTime = useCallback((dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
      return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (days === 1) {
      return 'Yesterday';
    } else if (days < 7) {
      return date.toLocaleDateString('en-US', { weekday: 'short' });
    } else {
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
  }, []);

  // Now useEffects can safely reference the functions above
  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    loadConversations();
    if (permission === 'default') requestPermission();
  }, [user, router, permission, requestPermission, loadConversations]);

  // Listen to searchParams changes separately (avoid re-binding socket handlers)
  useEffect(() => {
    const conversationParam = searchParams.get('conversation');
    if (conversationParam) loadConversation(parseInt(conversationParam));
  }, [searchParams, loadConversation]);

  useEffect(() => {
    if (selectedConversation) {
      loadMessages(selectedConversation.id);
      if (typeof window !== 'undefined' && navigator.onLine) {
        flushOutbox(selectedConversation.id);
      }
    }
  }, [selectedConversation, loadMessages, flushOutbox]);

  useEffect(() => {
    scrollToBottom();
  }, [messages, scrollToBottom]);

  const filteredConversations = conversations.filter((conv) => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    return (
      conv.other_user?.name.toLowerCase().includes(query) ||
      (conv.property?.title && conv.property.title.toLowerCase().includes(query))
    );
  });

  if (!user) return null;

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-12rem)]">
            <div className="lg:col-span-1 border rounded-lg p-4">
              <Skeleton className="h-10 w-full mb-4" />
              <MessageListSkeleton items={6} />
            </div>
            <div className="lg:col-span-2 border rounded-lg overflow-hidden">
              <ConversationSkeleton />
            </div>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2">{tMsg('title')}</h1>
          <p className="text-gray-600">{t('selectConversation')}</p>
          {/* Live region announcing conversation and unread counts */}
          <p className="sr-only" aria-live="polite">
            {(() => {
              const total = conversations.length;
              const unread = conversations.reduce((acc, c) => acc + (c.unread_count || 0), 0);
              return `${total} conversations, ${unread} unread`;
            })()}
          </p>
        </div>

        <div className="grid lg:grid-cols-3 gap-6 h-[calc(100vh-250px)]">
          {/* Conversations List */}
          <Card className="lg:col-span-1 overflow-hidden flex flex-col">
            <div className="p-4 border-b">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                <Input
                  placeholder={tMsg('searchMessages')}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto">
              {loading ? (
                <div className="p-4 space-y-4">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="animate-pulse">
                      <div className="flex items-center gap-3">
                        <div className="w-12 h-12 bg-gray-200 rounded-full" />
                        <div className="flex-1">
                          <div className="bg-gray-200 h-4 rounded w-3/4 mb-2" />
                          <div className="bg-gray-200 h-3 rounded w-1/2" />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : filteredConversations.length === 0 ? (
                <div className="flex items-center justify-center h-full p-6">
                  <EmptyState
                    icon={MessageSquare}
                    title={t('noConversations')}
                    description={t('selectConversation')}
                  />
                </div>
              ) : (
                filteredConversations.map((conversation, idx) => (
                  <div
                    key={conversation.id}
                    onClick={() => setSelectedConversation(conversation)}
                    className={`p-4 border-b cursor-pointer hover:bg-gray-50 transition-colors animate-fade-in-up ${
                      selectedConversation?.id === conversation.id ? 'bg-blue-50' : ''
                    }`}
                    style={{ animationDelay: `${Math.min(idx, 8) * 60}ms` }}
                    role="button"
                    tabIndex={0}
                    aria-label={`Conversation with ${conversation.other_user?.name}${conversation.unread_count > 0 ? `, ${conversation.unread_count} unread messages` : ''}`}
                    aria-current={selectedConversation?.id === conversation.id ? 'true' : undefined}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setSelectedConversation(conversation);
                      }
                    }}
                  >
                    <div className="flex items-start gap-3">
                      <div className="relative">
                        <Avatar className="w-12 h-12">
                          <AvatarImage src={conversation.other_user?.avatar_url} />
                          <AvatarFallback className="bg-gradient-to-br from-blue-400 to-blue-600 text-white">
                            {conversation.other_user?.name
                              .split(' ')
                              .map((n) => n[0])
                              .join('')}
                          </AvatarFallback>
                        </Avatar>
                        {onlineUsers.has(conversation.other_user?.id || 0) && (
                          <span className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between mb-1">
                          <h3 className="font-semibold truncate">
                            {conversation.other_user?.name}
                          </h3>
                          {conversation.last_message && (
                            <span className="text-xs text-gray-500 ml-2 flex-shrink-0">
                              {formatTime(conversation.last_message.created_at)}
                            </span>
                          )}
                        </div>
                        <p className="text-sm text-gray-600 truncate mb-1">
                          {conversation.property?.title}
                        </p>
                        {conversation.last_message && (
                          <p className="text-sm text-gray-600 truncate">
                            {conversation.last_message.message}
                          </p>
                        )}
                        {conversation.unread_count > 0 && (
                          <Badge className="mt-2" variant="default">
                            {conversation.unread_count} new
                          </Badge>
                        )}
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </Card>

          {/* Chat Area */}
          <Card className="lg:col-span-2 overflow-hidden flex flex-col">
            {selectedConversation ? (
              <>
                {/* Chat Header */}
                <div className="p-4 border-b flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button
                            variant="ghost"
                            size="icon"
                            className="lg:hidden"
                            onClick={() => setSelectedConversation(null)}
                            aria-label="Back to conversations"
                          >
                            <ArrowLeft className="h-5 w-5" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Back</TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                    <div className="relative">
                      <Avatar className="w-10 h-10">
                        <AvatarImage src={selectedConversation.other_user?.avatar_url} />
                        <AvatarFallback className="bg-gradient-to-br from-blue-400 to-blue-600 text-white">
                          {selectedConversation.other_user?.name
                            .split(' ')
                            .map((n) => n[0])
                            .join('')}
                        </AvatarFallback>
                      </Avatar>
                      {onlineUsers.has(selectedConversation.other_user?.id || 0) && (
                        <span className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                      )}
                    </div>
                    <div>
                      <h3 className="font-semibold">
                        {selectedConversation.other_user?.name}
                      </h3>
                      <p className="text-xs text-gray-500" aria-live="polite">
                        {typing ? (
                          <span className="text-primary">{t('typing')}</span>
                        ) : onlineUsers.has(selectedConversation.other_user?.id || 0) ? (
                          t('online')
                        ) : (
                          selectedConversation.property?.title
                        )}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button variant="ghost" size="icon" aria-label="Start voice call">
                            <Phone className="h-5 w-5" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Voice call</TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button variant="ghost" size="icon" aria-label="Start video call">
                            <Video className="h-5 w-5" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Video call</TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                    <AlertDialog>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <AlertDialogTrigger asChild>
                              <Button 
                                variant="ghost" 
                                size="icon"
                                aria-label="Archive conversation"
                              >
                                <Archive className="h-5 w-5" />
                              </Button>
                            </AlertDialogTrigger>
                          </TooltipTrigger>
                          <TooltipContent>Archive</TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Archive conversation?</AlertDialogTitle>
                          <AlertDialogDescription>
                            This conversation will be moved to your archive. You can restore it later. Continue?
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction onClick={() => handleArchiveConversation(selectedConversation.id)}>
                            Archive
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </div>

                {/* Messages */}
                <div 
                  className="flex-1 overflow-y-auto p-4 space-y-4" 
                  role="log" 
                  aria-live="polite" 
                  aria-label="Chat messages"
                  aria-busy={messagesLoading ? 'true' : undefined}
                >
                  {messagesLoading && (
                    <div className="space-y-3">
                      {[...Array(4)].map((_, i) => (
                        <div key={i} className={`flex ${i % 2 ? 'justify-end' : 'justify-start'} animate-fade-in-up`} style={{ animationDelay: `${i * 80}ms` }}>
                          <div className="h-6 w-40 bg-gray-200 rounded-2xl" />
                        </div>
                      ))}
                    </div>
                  )}
                  {!messagesLoading && messages.map((message, index) => {
                    const isOwn = message.sender_id === user.id;
                    const showAvatar =
                      index === 0 ||
                      messages[index - 1].sender_id !== message.sender_id;

                    return (
                      <div
                        key={message.id}
                        className={`flex ${isOwn ? 'justify-end' : 'justify-start'} animate-fade-in-up`}
                        style={{ animationDelay: `${Math.min(index, 8) * 40}ms` }}
                      >
                        <div
                          className={`flex gap-2 max-w-[70%] ${
                            isOwn ? 'flex-row-reverse' : 'flex-row'
                          }`}
                        >
                          {!isOwn && showAvatar && (
                            <Avatar className="w-8 h-8 flex-shrink-0">
                              <AvatarImage src={selectedConversation.other_user?.avatar_url} />
                              <AvatarFallback className="bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs">
                                {selectedConversation.other_user?.name
                                  .split(' ')
                                  .map((n) => n[0])
                                  .join('')}
                              </AvatarFallback>
                            </Avatar>
                          )}
                          {!isOwn && !showAvatar && <div className="w-8" />}
                          <div>
                            <div
                              className={`rounded-2xl px-4 py-2 ${
                                isOwn
                                  ? 'bg-blue-600 text-white'
                                  : 'bg-gray-100 text-gray-900'
                              }`}
                            >
                              {message.message && <p className="text-sm">{message.message}</p>}
                              
                              {/* Attachments */}
                              {message.attachments && message.attachments.length > 0 && (
                                <div className="mt-2 space-y-2">
                                  {message.attachments.map((attachment) => (
                                    <a
                                      key={attachment.id}
                                      href={attachment.file_url}
                                      target="_blank"
                                      rel="noopener noreferrer"
                                      download
                                      className={`flex items-center space-x-2 p-2 rounded ${
                                        isOwn ? 'bg-blue-700 hover:bg-blue-800' : 'bg-white hover:bg-gray-50'
                                      } transition-colors`}
                                    >
                                      {getFileIcon(attachment.file_type)}
                                      <div className="flex-1 min-w-0">
                                        <p className="text-xs truncate">
                                          {attachment.filename}
                                        </p>
                                        <p className={`text-xs ${isOwn ? 'opacity-75' : 'text-gray-500'}`}>
                                          {formatFileSize(attachment.file_size)}
                                        </p>
                                      </div>
                                      <Download className="h-3 w-3 flex-shrink-0" />
                                    </a>
                                  ))}
                                </div>
                              )}
                            </div>
                            <div className={`flex items-center gap-2 text-xs text-gray-500 mt-1 ${isOwn ? 'justify-end' : 'justify-start'}`}>
                              <span>
                                {new Date(message.created_at).toLocaleTimeString('en-US', {
                                  hour: '2-digit',
                                  minute: '2-digit',
                                })}
                              </span>
                              {isOwn && (
                                <span>
                                  {message.status === 'failed' && '⚠️'}
                                  {message.status === 'pending' && '…'}
                                  {!message.status && (message.read ? '✓✓' : '✓')}
                                  {message.status === 'sent' && (message.read ? '✓✓' : '✓')}
                                  {message.status === 'read' && '✓✓'}
                                </span>
                              )}
                              {isOwn && message.status === 'failed' && (
                                <TooltipProvider>
                                  <Tooltip>
                                    <TooltipTrigger asChild>
                                      <Button size="sm" variant="ghost" className="h-6 px-2" onClick={() => retrySend(message)}>
                                        Retry
                                      </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>Try sending again</TooltipContent>
                                  </Tooltip>
                                </TooltipProvider>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                  <div ref={messagesEndRef} />
                </div>

                {/* Message Input */}
                <form onSubmit={handleSendMessage} className="p-4 border-t" aria-label="Send message">
                  {/* Selected Files Preview */}
                  {selectedFiles.length > 0 && (
                    <div className="mb-3 flex flex-wrap gap-2" role="list" aria-label="Attached files">
                      {selectedFiles.map((file, index) => (
                        <div
                          key={index}
                          className="flex items-center space-x-2 bg-gray-100 rounded-lg p-2 pr-3"
                          role="listitem"
                        >
                          {file.type.startsWith('image/') ? (
                            <ImageIcon className="h-4 w-4 text-gray-600" />
                          ) : (
                            <File className="h-4 w-4 text-gray-600" />
                          )}
                          <span className="text-sm truncate max-w-32">{file.name}</span>
                          <span className="text-xs text-gray-500">{formatFileSize(file.size)}</span>
                          <button
                            type="button"
                            onClick={() => removeFile(index)}
                            className="text-gray-500 hover:text-gray-700"
                            aria-label={`Remove ${file.name}`}
                          >
                            <X className="h-4 w-4" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}

                  <div className="flex items-end gap-2">
                    <input
                      type="file"
                      ref={fileInputRef}
                      onChange={handleFileSelect}
                      multiple
                      accept="image/*,application/pdf,.doc,.docx"
                      className="hidden"
                    />
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="flex-shrink-0"
                            onClick={() => fileInputRef.current?.click()}
                            disabled={sending}
                            aria-label="Attach file"
                          >
                            <Paperclip className="h-5 w-5" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Attach files</TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                    <Textarea
                      placeholder={tMsg('typeMessage')}
                      aria-label="Message input"
                      value={newMessage}
                      onChange={(e) => {
                        setNewMessage(e.target.value);
                        handleTyping();
                      }}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                          e.preventDefault();
                          handleSendMessage(e);
                        }
                      }}
                      className="flex-1 min-h-[44px] max-h-32 resize-none"
                      rows={1}
                      disabled={sending}
                    />
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button
                            type="submit"
                            disabled={(!newMessage.trim() && selectedFiles.length === 0) || sending}
                            aria-label="Send message"
                            className="flex-shrink-0"
                          >
                            <Send className="h-5 w-5" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Send</TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                  </div>
                  <p className="text-xs text-gray-500 mt-2">
                    Press Enter to send, Shift+Enter for new line
                  </p>
                </form>
              </>
            ) : (
              <div className="flex-1 flex items-center justify-center text-center p-6">
                <div>
                  <MessageSquare className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                  <h3 className="text-xl font-semibold mb-2">{t('selectConversation')}</h3>
                  <p className="text-gray-600">
                    {t('noConversations')}
                  </p>
                </div>
              </div>
            )}
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
