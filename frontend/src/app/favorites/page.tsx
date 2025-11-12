'use client';

import { useEffect, useState, useCallback } from 'react';
import { useConversionTracking } from '@/hooks/use-conversion-tracking';
import { useFavorites } from '@/hooks/use-favorites';
import apiClient from '@/lib/api-client';
import { Property } from '@/types';
import { useAuth } from '@/contexts/auth-context';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { PropertyCard } from '@/components/property-card';
// (skeletons/empty states available but not used here)
import { Heart, Trash2, Share2 } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { useTranslations } from '@/lib/i18n-temp';
import { notify } from '@/lib/notify';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';

export default function FavoritesPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { favorites, toggleFavorite, isFavorite, loading: favLoading } = useFavorites();
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const { trackWishlistToggle } = useConversionTracking();
  const t = useTranslations('favoritesPage');
  const tCommon = useTranslations('common');

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    if (!favLoading) {
      fetchFavoriteProperties(favorites);
    }
  }, [user, router, favorites, favLoading]);

  const fetchFavoriteProperties = async (ids: number[]) => {
    if (ids.length === 0) {
      setLoading(false);
      setProperties([]);
      return;
    }

    try {
      const promises = ids.map(id => apiClient.get(`/properties/${id}`));
      const responses = await Promise.all(promises);
      const props = responses.map(res => res.data.data).filter(Boolean);
      setProperties(props);
    } catch (error) {
      console.error('Failed to fetch favorite properties:', error);
    } finally {
      setLoading(false);
    }
  };

  const removeFavorite = (id: number) => {
    toggleFavorite(id);
    trackWishlistToggle(id, false);
  };

  const clearAllFavorites = async () => {
    for (const id of favorites) {
      await toggleFavorite(id);
    }
    setProperties([]);
  };

  const shareWishlist = async () => {
    const url = window.location.origin + '/favorites';
    try {
      await navigator.clipboard.writeText(url);
      notify.success({ title: tCommon('share'), description: t('copied') });
    } catch (e) {
      notify.error({ title: tCommon('share'), description: t('copied') });
    }
  };

  if (!user) {
    return null;
  }

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="animate-pulse">
            <div className="h-8 bg-gray-200 rounded w-48 mb-6"></div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {[1, 2, 3].map((i) => (
                <div key={i} className="h-96 bg-gray-200 rounded"></div>
              ))}
            </div>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold tracking-tight mb-2 flex items-center gap-2">
              <Heart className="h-8 w-8 text-red-500 fill-red-500" />
              {t('title')}
            </h1>
            <p className="text-muted-foreground">
              {t('count', { count: properties.length })}
            </p>
            <span className="sr-only" aria-live="polite">{properties.length} favorites</span>
          </div>
          
          {properties.length > 0 && (
            <TooltipProvider>
              <div className="flex gap-2">
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" onClick={shareWishlist} className="gap-2">
                      <Share2 className="h-4 w-4" />
                      {t('share')}
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Copy wishlist link</TooltipContent>
                </Tooltip>
                <AlertDialog>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <AlertDialogTrigger asChild>
                        <Button variant="destructive" className="gap-2">
                          <Trash2 className="h-4 w-4" />
                          {t('clearAll')}
                        </Button>
                      </AlertDialogTrigger>
                    </TooltipTrigger>
                    <TooltipContent>Remove all favorites</TooltipContent>
                  </Tooltip>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>{t('confirmClearAll')}</AlertDialogTitle>
                      <AlertDialogDescription>
                        {t('confirmClearAll')}
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>{t('cancel') || 'Cancel'}</AlertDialogCancel>
                      <AlertDialogAction onClick={clearAllFavorites} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">{t('clearAll')}</AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </TooltipProvider>
          )}
        </div>

        {properties.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-16">
              <Heart className="h-16 w-16 text-gray-300 mb-4" />
              <h3 className="text-xl font-semibold mb-2">{t('emptyTitle')}</h3>
              <p className="text-gray-600 mb-6 text-center">{t('emptyDesc')}</p>
              <Button onClick={() => router.push('/properties')}>
                {t('browse')}
              </Button>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {properties.map((property, idx) => (
              <div key={property.id} className="animate-fade-in-up" style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                <PropertyCard
                  property={property}
                  onFavorite={removeFavorite}
                  isFavorite={true}
                />
              </div>
            ))}
          </div>
        )}
      </div>
    </MainLayout>
  );
}
