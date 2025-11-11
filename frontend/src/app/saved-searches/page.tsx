"use client";

import { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Skeleton } from '@/components/ui/skeleton';
import { notify } from '@/lib/notify';
import { useTranslations } from 'next-intl';
import apiClient from '@/lib/api-client';
import { Bell, BellOff, Trash2, Search, Clock, Calendar } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface SavedSearch {
  id: number;
  name: string;
  criteria: any;
  email_notifications: boolean;
  frequency: 'instant' | 'daily' | 'weekly';
  is_active: boolean;
  last_notified_at: string | null;
  notification_count: number;
  matches_count?: number;
  unnotified_matches_count?: number;
  created_at: string;
}

export default function SavedSearchesPage() {
  const router = useRouter();
  const [searches, setSearches] = useState<SavedSearch[]>([]);
  const tNotify = useTranslations('notify');
  const t = useTranslations('savedSearches');
  const [loading, setLoading] = useState(true);

  const fetchSavedSearches = useCallback(async () => {
    try {
      const { data } = await apiClient.get('/saved-searches');
      setSearches(data.data || []);
    } catch (error) {
      notify.error({
        title: tNotify('errorLoadSavedSearches'),
      });
    } finally {
      setLoading(false);
    }
  }, [tNotify]);

  useEffect(() => {
    fetchSavedSearches();
  }, [fetchSavedSearches]);

  const toggleActive = useCallback(async (id: number, isActive: boolean) => {
    try {
      await apiClient.put(`/saved-searches/${id}`, { is_active: !isActive });
      setSearches(prev => prev.map(s => 
        s.id === id ? { ...s, is_active: !isActive } : s
      ));
      notify.success({
        title: !isActive ? tNotify('searchActivated') : tNotify('searchPaused'),
      });
    } catch (error) {
      notify.error({
        title: tNotify('errorUpdateSearch'),
      });
    }
  }, [tNotify]);

  const deleteSearch = useCallback(async (id: number) => {
    if (!confirm(t('confirmDelete'))) {
      return;
    }

    try {
      await apiClient.delete(`/saved-searches/${id}`);
      setSearches(prev => prev.filter(s => s.id !== id));
      notify.success({
        title: tNotify('searchDeleted'),
      });
    } catch (error) {
      notify.error({
        title: tNotify('errorDeleteSearch'),
      });
    }
  }, [tNotify]);

  const viewMatches = useCallback((id: number) => {
    router.push(`/saved-searches/${id}/matches`);
  }, [router]);

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <Skeleton className="h-8 w-64 mb-6" />
          <div className="grid gap-4">
            {[1, 2, 3].map(i => (
              <Skeleton key={i} className="h-48 w-full" />
            ))}
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-3xl font-bold mb-2">{t('title')}</h1>
            <p className="text-muted-foreground">{t('subtitle')}</p>
          </div>
          <Button asChild>
            <Link href="/properties">
              <Search className="mr-2 h-4 w-4" />
              {t('newSearch')}
            </Link>
          </Button>
        </div>

        {searches.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-16">
              <Bell className="h-16 w-16 text-muted-foreground mb-4" />
              <h2 className="text-2xl font-semibold mb-2">{t('emptyTitle')}</h2>
              <p className="text-muted-foreground mb-6 text-center max-w-md">{t('emptyDesc')}</p>
              <Button asChild>
                <Link href="/properties">{t('browseProperties')}</Link>
              </Button>
            </CardContent>
          </Card>
        ) : (
          <div className="grid gap-4">
            {searches.map(search => (
              <Card key={search.id} className={!search.is_active ? 'opacity-60' : ''}>
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <CardTitle>{search.name}</CardTitle>
                        {!search.is_active && (
                          <Badge variant="secondary">{t('paused')}</Badge>
                        )}
                        {(search.unnotified_matches_count || 0) > 0 && (
                          <Badge variant="default">
                            {t('newMatches', { count: search.unnotified_matches_count || 0 })}
                          </Badge>
                        )}
                      </div>
                      <CardDescription>
                        {t('created', { date: formatDate(search.created_at) })}
                      </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                      <Switch
                        checked={search.is_active}
                        onCheckedChange={() => toggleActive(search.id, search.is_active)}
                      />
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => deleteSearch(search.id)}
                        aria-label={t('confirmDelete')}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {/* Criteria Summary */}
                    <div>
                      <p className="text-sm font-medium mb-2">{t('criteria')}</p>
                      <div className="flex flex-wrap gap-2">
                        {search.criteria.priceRange && (
                          <Badge variant="outline">
                            ${search.criteria.priceRange[0]} - ${search.criteria.priceRange[1]}
                          </Badge>
                        )}
                        {search.criteria.bedrooms && (
                          <Badge variant="outline">{search.criteria.bedrooms}+ beds</Badge>
                        )}
                        {search.criteria.bathrooms && (
                          <Badge variant="outline">{search.criteria.bathrooms}+ baths</Badge>
                        )}
                        {search.criteria.guests && (
                          <Badge variant="outline">{search.criteria.guests}+ guests</Badge>
                        )}
                        {search.criteria.propertyType?.map((type: string) => (
                          <Badge key={type} variant="outline" className="capitalize">
                            {type}
                          </Badge>
                        ))}
                      </div>
                    </div>

                    {/* Notification Settings */}
                    <div className="flex items-center gap-6 text-sm text-muted-foreground">
                      <div className="flex items-center gap-2">
                        {search.email_notifications ? (
                          <>
                            <Bell className="h-4 w-4" />
                            <span className="capitalize">{search.frequency} notifications</span>
                          </>
                        ) : (
                          <>
                            <BellOff className="h-4 w-4" />
                            <span>{t('notificationsOff')}</span>
                          </>
                        )}
                      </div>
                      {search.last_notified_at && (
                        <div className="flex items-center gap-2">
                          <Clock className="h-4 w-4" />
                          <span>{t('lastNotified', { date: formatDate(search.last_notified_at) })}</span>
                        </div>
                      )}
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4" />
                        <span>{t('totalMatches', { count: search.matches_count || 0 })}</span>
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-2 pt-2">
                      <Button
                        variant="default"
                        size="sm"
                        onClick={() => viewMatches(search.id)}
                      >
                        {t('viewMatches')}
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        asChild
                      >
                        <Link href={`/properties?${buildSearchUrl(search.criteria)}`}>
                          {t('runSearch')}
                        </Link>
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </MainLayout>
  );
}

function buildSearchUrl(criteria: any): string {
  const params = new URLSearchParams();
  
  if (criteria.priceRange) {
    params.set('minPrice', criteria.priceRange[0].toString());
    params.set('maxPrice', criteria.priceRange[1].toString());
  }
  if (criteria.bedrooms) params.set('bedrooms', criteria.bedrooms.toString());
  if (criteria.bathrooms) params.set('bathrooms', criteria.bathrooms.toString());
  if (criteria.guests) params.set('guests', criteria.guests.toString());
  if (criteria.propertyType?.length) params.set('type', criteria.propertyType.join(','));
  if (criteria.amenities?.length) params.set('amenities', criteria.amenities.join(','));
  if (criteria.instantBook) params.set('instantBook', 'true');
  
  return params.toString();
}
