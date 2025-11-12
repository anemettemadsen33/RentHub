'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Skeleton } from '@/components/ui/skeleton';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { notify } from '@/lib/notify';
import { useAuth } from '@/contexts/auth-context';
import { useChat } from '@/hooks/use-chat';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import type { Conversation, Message } from '@/types/extended';
import { ArrowLeft, Download, Image as ImageIcon, Paperclip, Send, X } from 'lucide-react';
import { useTranslations } from '@/lib/i18n-temp';

export default function MessageThreadPage() {
  const router = useRouter();
  const params = useParams();
  
  const { user } = useAuth();
  const t = useTranslations('chatThread');

  const threadId = useMemo(() => {
    try { return parseInt(params.id as string, 10); } catch { return NaN; }
  }, [params.id]);

  const [conversation, setConversation] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const [sending, setSending] = useState(false);
  const [loading, setLoading] = useState(true);
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Socket connection via useChat
  const chat = useChat({
    userId: user?.id,
    activeConversationId: conversation?.id ?? null,
    onMessage: (message) => {
      setMessages((prev) => [...prev, { ...message, status: message.read ? 'read' : 'sent' }]);
      scrollToBottom();
    },
    onTyping: () => {},
    onMessageRead: (messageId) => {
      setMessages((prev) => prev.map((m) => (m.id === messageId ? { ...m, read: true, status: 'read' } : m)));
    },
    onBackgroundMessage: () => {},
    onUsersOnline: () => {},
  });

  useEffect(() => {
    if (!user) return;
    if (!Number.isFinite(threadId)) {
      router.push('/messages');
      return;
    }
    loadConversationAndMessages(threadId);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user, threadId]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const loadConversationAndMessages = async (id: number) => {
    setLoading(true);
    try {
      // Try to read conversation via list endpoint and pick one
      const convRes = await apiClient.get('/conversations');
      const conv: Conversation | undefined = (convRes.data.data || []).find((c: Conversation) => c.id === id);
      if (conv) setConversation(conv);

      // Load messages
      const listRes = await apiClient.get(API_ENDPOINTS.messages.list(id));
      const incoming: Message[] = (listRes.data.data || []).map((m: Message) => ({ ...m, status: m.read ? 'read' : 'sent' }));
      setMessages(incoming);
      // Mark all as read
      await apiClient.post(API_ENDPOINTS.conversations.markAllAsRead(id));
    } catch (e: any) {
      notify.error({ title: 'Error', description: 'Failed to load thread' });
    } finally {
      setLoading(false);
    }
  };

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!conversation || !user) return;
    if (!newMessage.trim() && selectedFiles.length === 0) return;

    const client_id = `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    const optimistic: Message = {
      id: -1,
      client_id,
      status: 'pending',
      conversation_id: conversation.id,
      sender_id: user.id,
      recipient_id: conversation.other_user?.id || 0,
      message: newMessage,
      read: false,
      created_at: new Date().toISOString(),
    } as any;

    setMessages((prev) => [...prev, optimistic]);
    setNewMessage('');
    setSending(true);

    const formData = new FormData();
    if (optimistic.message?.trim()) formData.append('content', optimistic.message.trim());
    for (const f of selectedFiles) formData.append('attachments[]', f);
    setSelectedFiles([]);

    try {
      const response = await apiClient.post(
        API_ENDPOINTS.messages.create(conversation.id),
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const saved: Message = { ...response.data.data, status: 'sent' };
      setMessages((prev) => prev.map((m) => (m.client_id === client_id ? saved : m)));
      chat.emitSendMessage(conversation.id, saved);
    } catch (err) {
      setMessages((prev) => prev.map((m) => (m.client_id === client_id ? { ...m, status: 'failed' } : m)));
      notify.error({ title: t('failed'), description: t('offlineCached') });
    } finally {
      setSending(false);
    }
  };

  const handleRetry = async (failed: Message) => {
    if (!conversation) return;
    const formData = new FormData();
    if (failed.message?.trim()) formData.append('content', failed.message.trim());
    try {
      const response = await apiClient.post(
        API_ENDPOINTS.messages.create(conversation.id),
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const saved: Message = { ...response.data.data, status: 'sent' };
      setMessages((prev) => prev.map((m) => (m.client_id === failed.client_id ? saved : m)));
      chat.emitSendMessage(conversation.id, saved);
    } catch {}
  };

  const onFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const files = Array.from(e.target.files);
      setSelectedFiles((prev) => [...prev, ...files]);
    }
  };

  const removeFile = (idx: number) => setSelectedFiles((prev) => prev.filter((_, i) => i !== idx));

  if (!user) return null;

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="mb-4 flex items-center gap-2">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Button variant="ghost" size="icon" onClick={() => router.push('/messages')} aria-label="Back">
                  <ArrowLeft className="h-5 w-5" />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Back</TooltipContent>
            </Tooltip>
          </TooltipProvider>
          <h1 className="text-2xl font-bold">{conversation?.other_user?.name || 'Chat'}</h1>
        </div>

        <Card className="overflow-hidden">
          {/* Messages */}
          <div className="h-[60vh] overflow-y-auto p-4 space-y-4" role="log" aria-live="polite" aria-busy={loading ? 'true' : undefined}>
            {loading ? (
              <div className="space-y-3">
                {[...Array(5)].map((_, i) => (
                  <div key={i} className={`flex ${i % 2 ? 'justify-end' : 'justify-start'} animate-fade-in-up`} style={{ animationDelay: `${i * 70}ms` }}>
                    <div className="h-6 w-48 bg-gray-200 rounded-2xl" />
                  </div>
                ))}
              </div>
            ) : messages.length === 0 ? (
              <div className="text-center text-sm text-gray-500">{t('offlineCached')}</div>
            ) : (
              messages.map((message, idx) => {
                const isOwn = message.sender_id === user.id;
                return (
                  <div key={(message.client_id as any) || message.id} className={`flex ${isOwn ? 'justify-end' : 'justify-start'} animate-fade-in-up`} style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                    <div className={`max-w-[70%] rounded-2xl px-4 py-2 ${isOwn ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900'}`}>
                      {message.message && <p className="text-sm">{message.message}</p>}
                      {message.attachments && message.attachments.length > 0 && (
                        <div className="mt-2 space-y-2">
                          {message.attachments.map((a) => (
                            <a key={a.id} href={a.file_url} target="_blank" rel="noopener noreferrer" download className={`flex items-center gap-2 p-2 rounded ${isOwn ? 'bg-blue-700 hover:bg-blue-800' : 'bg-white hover:bg-gray-50'}`}>
                              {a.file_type.startsWith('image/') ? <ImageIcon className="h-4 w-4" /> : <Download className="h-4 w-4" />}
                              <span className="text-xs truncate">{a.filename}</span>
                            </a>
                          ))}
                        </div>
                      )}
                    </div>
                    <div className={`ml-2 flex items-center gap-2 text-xs text-gray-500 ${isOwn ? '' : 'opacity-80'}`}>
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
                              <Button size="sm" variant="ghost" className="h-6 px-2" onClick={() => handleRetry(message)}>
                                Retry
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Try sending again</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                      )}
                    </div>
                  </div>
                );
              })
            )}
            <div ref={messagesEndRef} />
          </div>

          {/* Composer */}
          <CardHeader className="border-t">
            <CardTitle className="sr-only">Composer</CardTitle>
            <CardContent className="p-0">
              {selectedFiles.length > 0 && (
                <div className="px-4 pt-4 flex flex-wrap gap-2">
                  {selectedFiles.map((file, idx) => (
                    <div key={idx} className="flex items-center gap-2 bg-gray-100 rounded-lg p-2 pr-3">
                      {file.type.startsWith('image/') ? (
                        <ImageIcon className="h-4 w-4 text-gray-600" />
                      ) : (
                        <Download className="h-4 w-4 text-gray-600" />
                      )}
                      <span className="text-sm truncate max-w-44">{file.name}</span>
                      <button type="button" onClick={() => removeFile(idx)} aria-label={`Remove ${file.name}`}>
                        <X className="h-4 w-4" />
                      </button>
                    </div>
                  ))}
                </div>
              )}

              <form onSubmit={handleSend} className="p-4 flex items-end gap-2" aria-label="Send message">
                <input ref={fileInputRef} type="file" className="hidden" onChange={onFileSelect} multiple accept="image/*,application/pdf,.doc,.docx" />
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button type="button" variant="ghost" size="icon" className="flex-shrink-0" onClick={() => fileInputRef.current?.click()} aria-label={t('attachments')}>
                        <Paperclip className="h-5 w-5" />
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Attach files</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <Textarea
                  placeholder={t('placeholder')}
                  value={newMessage}
                  onChange={(e) => setNewMessage(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                      e.preventDefault();
                      handleSend(e);
                    }
                  }}
                  className="flex-1 min-h-[44px] max-h-32 resize-none"
                  rows={1}
                  disabled={sending}
                />
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button type="submit" disabled={(!newMessage.trim() && selectedFiles.length === 0) || sending} className="flex-shrink-0">
                        <Send className="h-5 w-5" />
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Send</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
              </form>
            </CardContent>
          </CardHeader>
        </Card>
      </div>
    </MainLayout>
  );
}
