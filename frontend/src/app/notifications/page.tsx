'use client';

import { useEffect, useState, useMemo, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import { useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { ListSkeleton } from '@/components/skeletons';
import { Skeleton } from '@/components/ui/skeleton';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
  Bell,
  Check,
  X,
  Filter,
  Search,
  Loader2,
  Trash2,
  MailOpen,
  Mail,
  CheckCheck,
} from 'lucide-react';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { AppNotification, NotificationType } from '@/types/extended';
import { useNotifications } from '@/contexts/notification-context';
import { io, Socket } from 'socket.io-client';
import { usePushNotifications } from '@/hooks/use-push-notifications';

interface UnreadCountResponse { count: number }

// TYPE_LABELS now resolved via i18n keys
const TYPE_LABELS: Record<NotificationType, string> = {
  booking_created: 'notificationsPage.types.booking_created',
  booking_confirmed: 'notificationsPage.types.booking_confirmed',
  booking_cancelled: 'notificationsPage.types.booking_cancelled',
  payment_received: 'notificationsPage.types.payment_received',
  maintenance: 'notificationsPage.types.maintenance',
  message: 'notificationsPage.types.message',
  system: 'notificationsPage.types.system',
};

export default function NotificationsPage() {
  const { user } = useAuth();
  const t = useTranslations('notificationsPage');
  const tNotify = useTranslations('notify');
  const router = useRouter();
  const { toast } = useToast();
  const { showNotification, permission, requestPermission } = usePushNotifications();
  const { refresh } = useNotifications();

  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'all' | NotificationType>('all');
  const [search, setSearch] = useState('');
  const [selectedIds, setSelectedIds] = useState<Set<string | number>>(new Set());
  const [socket, setSocket] = useState<Socket | null>(null);
  const [markingAll, setMarkingAll] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [preferences, setPreferences] = useState<any | null>(null);
  const [savingPrefs, setSavingPrefs] = useState(false);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
  loadNotifications();
  loadPreferences();
    if (permission === 'default') requestPermission();

    const s = io(process.env.NEXT_PUBLIC_WEBSOCKET_URL || 'http://localhost:6001', {
      auth: { token: typeof window !== 'undefined' ? localStorage.getItem('token') : undefined },
    });
    setSocket(s);

    s.on('notification', (notification: AppNotification) => {
      setNotifications(prev => [notification, ...prev]);
      if (permission === 'granted') {
        showNotification({
          title: notification.title,
          body: notification.body,
          tag: `notif-${notification.id}`,
          onClick: () => {
            router.push('/notifications');
          },
        });
      }
      refresh();
    });
  s.on('booking_created', (payload: any) => toast({ title: t('toasts.newBooking'), description: `#${payload?.reference || payload?.id}` }));
  s.on('payment_received', (payload: any) => toast({ title: t('toasts.paymentReceived'), description: payload?.amount ? `$${payload.amount}` : '' }));

    return () => { s.disconnect(); };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user, permission, showNotification, router, refresh, toast]);

  const loadNotifications = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await apiClient.get(API_ENDPOINTS.notifications.list);
      setNotifications(data.data || []);
    } catch (e) {
      // fallback demo data
      setNotifications([
        {
          id: '1',
          type: 'booking_created',
            title: t('demo.title'),
            body: 'Booking #RB-2024-1001 was created', // keep body example unlocalized placeholder
            data: { booking_id: 1001 },
            is_read: false,
            created_at: new Date().toISOString(),
        },
        {
          id: '2',
          type: 'payment_received',
          title: t('types.payment_received'),
          body: 'You received $450.00 for booking #RB-2024-0999',
          is_read: true,
          created_at: new Date(Date.now() - 3600_000).toISOString(),
        },
        {
          id: '3',
          type: 'maintenance',
          title: t('types.maintenance'),
          body: 'New urgent maintenance request submitted',
          is_read: false,
          created_at: new Date(Date.now() - 7200_000).toISOString(),
        },
      ]);
      toast({ title: t('demo.title'), description: t('demo.description') });
    } finally { setLoading(false); }
  }, [toast, t]);

  const loadPreferences = async () => {
    try {
      const { data } = await apiClient.get(API_ENDPOINTS.notifications.getPreferences);
      setPreferences(data.data || {});
    } catch {
      setPreferences({
        booking_created: true,
        booking_confirmed: true,
        booking_cancelled: true,
        payment_received: true,
        maintenance: true,
        message: true,
        system: true,
        push_enabled: true,
      });
    }
  };

  const updatePreference = (key: string, value: boolean) => {
    setPreferences((prev: any) => ({ ...prev, [key]: value }));
  };

  const savePreferences = async () => {
    if (!preferences) return;
    setSavingPrefs(true);
    try {
      await apiClient.post(API_ENDPOINTS.notifications.updatePreferences, preferences);
      toast({ title: t('toasts.preferencesSaved') });
    } catch {
      toast({ title: tNotify('error'), description: t('toasts.preferencesSaveFailed'), variant: 'destructive' });
    } finally { setSavingPrefs(false); }
  };

  const filtered = useMemo(() => notifications.filter(n => {
    if (activeTab !== 'all' && n.type !== activeTab) return false;
    if (search && !(`${n.title} ${n.body}`.toLowerCase().includes(search.toLowerCase()))) return false;
    return true;
  }), [notifications, activeTab, search]);

  const unreadCount = useMemo(() => filtered.filter(n => !n.is_read).length, [filtered]);

  const toggleSelect = (id: string | number) => {
    setSelectedIds(prev => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });
  };

  const markAsRead = async (id: string | number, read = true) => {
    setNotifications(prev => prev.map(n => n.id === id ? { ...n, is_read: read } : n));
    try {
      if (read) {
        await apiClient.post(API_ENDPOINTS.notifications.markAsRead(String(id)));
      } else {
        await apiClient.post(API_ENDPOINTS.notifications.markAsUnread(String(id)));
      }
    } catch (error) {
      console.error('Failed to mark notification:', error);
    }
  };

  const markSelected = async (read: boolean) => {
    const ids = Array.from(selectedIds);
    setNotifications(prev => prev.map(n => ids.includes(n.id) ? { ...n, is_read: read } : n));
    for (const id of ids) {
      await markAsRead(id, read);
    }
    setSelectedIds(new Set());
  };

  const markAll = async () => {
    setMarkingAll(true);
    try {
      await apiClient.post(API_ENDPOINTS.notifications.markAllAsRead);
      setNotifications(prev => prev.map(n => ({ ...n, is_read: true })));
      toast({ title: t('toasts.markAllRead') });
    } catch (e) {
      toast({ title: tNotify('error'), description: t('toasts.markAllRead'), variant: 'destructive' });
    } finally { setMarkingAll(false); }
  };

  const deleteSelected = async () => {
    const ids = Array.from(selectedIds);
    setDeleting(true);
    try {
      setNotifications(prev => prev.filter(n => !ids.includes(n.id)));
      for (const id of ids) {
        try { await apiClient.delete(API_ENDPOINTS.notifications.delete(String(id))); } catch {}
      }
      setSelectedIds(new Set());
      toast({ title: tNotify('success'), description: t('toasts.deleted', { count: ids.length }) });
    } finally { setDeleting(false); }
  };

  const formatTime = (iso: string) => {
    const date = new Date(iso);
    return date.toLocaleString(undefined, { hour: '2-digit', minute: '2-digit', month: 'short', day: 'numeric' });
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="flex items-start justify-between mb-6 gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 flex items-center gap-2"><Bell className="h-8 w-8 text-primary" />{t('title')}</h1>
            <p className="text-gray-600">{t('subtitle')}</p>
            <span className="sr-only" aria-live="polite">{t('list.unreadBadge', { count: unreadCount })}</span>
          </div>
          <TooltipProvider>
            <div className="flex gap-2">
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="sm" onClick={markAll} disabled={markingAll} aria-live="polite">
                    {markingAll && <Loader2 className="h-4 w-4 mr-1 animate-spin" />}{t('actions.markAllRead')}
                  </Button>
                </TooltipTrigger>
                <TooltipContent>{t('tooltips.markAll')}</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="sm" onClick={() => markSelected(true)} disabled={!selectedIds.size}>{t('actions.markRead')}</Button>
                </TooltipTrigger>
                <TooltipContent>{t('tooltips.markSelectedRead')}</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="sm" onClick={() => markSelected(false)} disabled={!selectedIds.size}>{t('actions.markUnread')}</Button>
                </TooltipTrigger>
                <TooltipContent>{t('tooltips.markSelectedUnread')}</TooltipContent>
              </Tooltip>
              <AlertDialog>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <AlertDialogTrigger asChild>
                      <Button variant="destructive" size="sm" disabled={!selectedIds.size || deleting} aria-disabled={!selectedIds.size || deleting}>
                        {deleting && <Loader2 className="h-4 w-4 mr-1 animate-spin" />}{t('actions.delete')}
                      </Button>
                    </AlertDialogTrigger>
                  </TooltipTrigger>
                  <TooltipContent>{t('tooltips.deleteSelected')}</TooltipContent>
                </Tooltip>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>{t('confirm.delete.title')}</AlertDialogTitle>
                    <AlertDialogDescription>{t('confirm.delete.description', { count: selectedIds.size })}</AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>{t('confirm.cancel')}</AlertDialogCancel>
                    <AlertDialogAction onClick={deleteSelected} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                      {deleting && <Loader2 className="h-4 w-4 mr-1 animate-spin" />}{t('confirm.delete.action')}
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </div>
          </TooltipProvider>
        </div>

        <Card className="mb-4">
          <CardHeader className="pb-3">
            <CardTitle className="text-base">{t('filters.title')}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-col md:flex-row md:items-center gap-4">
              <div className="relative md:w-72">
                <Search className="h-4 w-4 absolute left-3 top-3 text-gray-400" />
                <Input placeholder={t('filters.searchPlaceholder')} className="pl-9" value={search} onChange={(e) => setSearch(e.target.value)} />
              </div>
              <Tabs value={activeTab} onValueChange={(v: any) => setActiveTab(v)} className="w-full md:flex-1">
                <TabsList className="flex flex-wrap gap-1">
                  <TabsTrigger value="all">{t('filters.all')}</TabsTrigger>
                  {Object.entries(TYPE_LABELS).map(([value, label]) => (
                    <TabsTrigger key={value} value={value}>{t(label as any)}</TabsTrigger>
                  ))}
                </TabsList>
              </Tabs>
            </div>
          </CardContent>
        </Card>

        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="text-base">{t('preferences.title')}</CardTitle>
          </CardHeader>
          <CardContent>
            {!preferences ? (
              <p className="text-sm text-muted-foreground">{t('preferences.loading')}</p>
            ) : (
              <div className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  {Object.entries(TYPE_LABELS).map(([key,label]) => (
                    <div key={key} className="flex items-center justify-between rounded border p-3">
                      <Label className="text-sm font-medium">{t(label as any)}</Label>
                      <Switch checked={!!preferences[key]} onCheckedChange={(val) => updatePreference(key, !!val)} />
                    </div>
                  ))}
                  <div className="flex items-center justify-between rounded border p-3 md:col-span-3">
                    <Label className="text-sm font-medium">{t('preferences.enablePush')}</Label>
                    <Switch checked={!!preferences.push_enabled} onCheckedChange={(val) => updatePreference('push_enabled', !!val)} />
                  </div>
                </div>
                <Button onClick={savePreferences} disabled={savingPrefs}>
                  {savingPrefs && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}{t('preferences.save')}
                </Button>
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-base flex items-center gap-2">{t('list.title')} <Badge variant="secondary">{t('list.unreadBadge', { count: unreadCount })}</Badge></CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <ScrollArea className="h-[60vh]">
              {loading ? (
                <div className="p-4"><ListSkeleton items={8} /></div>
              ) : filtered.length === 0 ? (
                <div className="p-10 text-center text-gray-500">{t('list.empty')}</div>
              ) : (
                <ul className="divide-y">
                  {filtered.map((notification, idx) => {
                    const selected = selectedIds.has(notification.id);
                    return (
                      <li
                        key={notification.id}
                        className={`flex items-start gap-4 p-4 hover:bg-muted/40 transition cursor-pointer animate-fade-in-up ${!notification.is_read ? 'bg-primary/5' : ''}`}
                        style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}
                        onClick={() => markAsRead(notification.id, true)}
                        role="button"
                        tabIndex={0}
                        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); markAsRead(notification.id, true); } }}
                      >
                        <div className="pt-1">
                          <Checkbox checked={selected} onCheckedChange={() => toggleSelect(notification.id)} aria-label={t('item.aria.select')} />
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2 flex-wrap">
                            <Badge variant={notification.is_read ? 'outline' : 'default'}>{t(TYPE_LABELS[notification.type] as any)}</Badge>
                            <span className="font-medium">{notification.title}</span>
                            {!notification.is_read && <span className="inline-block w-2 h-2 rounded-full bg-primary" />}
                            <span className="ml-auto text-xs text-gray-500">{formatTime(notification.created_at)}</span>
                          </div>
                          <p className="text-sm text-gray-600 mt-1 line-clamp-2">{notification.body}</p>
                          {notification.data && (
                            <div className="mt-2 text-xs text-gray-500 flex flex-wrap gap-2">
                              {Object.entries(notification.data).slice(0,4).map(([k,v]) => (
                                <span key={k} className="bg-muted px-2 py-0.5 rounded">{k}: {String(v)}</span>
                              ))}
                            </div>
                          )}
                          <div className="mt-2 flex gap-2">
                            {notification.is_read ? (
                              <Button size="sm" variant="ghost" onClick={(e) => { e.stopPropagation(); markAsRead(notification.id, false); }}>{t('item.markUnread')}</Button>
                            ) : (
                              <Button size="sm" variant="ghost" onClick={(e) => { e.stopPropagation(); markAsRead(notification.id, true); }}>{t('item.markRead')}</Button>
                            )}
                            <Button size="sm" variant="ghost" onClick={(e) => { e.stopPropagation(); toggleSelect(notification.id); }}>
                              {selected ? t('item.unselect') : t('item.select')}
                            </Button>
                          </div>
                        </div>
                      </li>
                    );
                  })}
                </ul>
              )}
            </ScrollArea>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
