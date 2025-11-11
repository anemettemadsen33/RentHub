import { useState, useCallback, useEffect } from 'react';
import { notify } from '@/lib/notify';
import { useAuth } from '@/contexts/auth-context';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { usePushNotifications } from '@/hooks/use-push-notifications';

/**
 * Optimistic favorites hook with backend sync, localStorage fallback, and Beams price-change notifications.
 */
export function useFavorites(initialFavorites: number[] = []) {
  const { user } = useAuth();
  const { subscribeToInterest, unsubscribeFromInterest } = usePushNotifications();
  const [favorites, setFavorites] = useState<number[]>(() => {
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem('favorites');
      return stored ? JSON.parse(stored) : initialFavorites;
    }
    return initialFavorites;
  });
  const [optimisticIds, setOptimisticIds] = useState<Set<number>>(new Set());
  const [loading, setLoading] = useState(true);
  

  // Load favorites from backend on mount and subscribe to Beams for each
  useEffect(() => {
    if (user) {
      loadFavorites();
    } else {
      setLoading(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user]);

  const loadFavorites = async () => {
    try {
      const response = await apiClient.get(API_ENDPOINTS.wishlists.list);
      const items = response.data.data || [];
      const propertyIds = items.flatMap((list: any) =>
        (list.items || []).map((item: any) => item.property_id)
      );
      const uniqueIds: number[] = Array.from(new Set(propertyIds));
      setFavorites(uniqueIds);

      // Subscribe to price-change notifications for each favorited property
      for (const id of uniqueIds) {
        subscribeToInterest(`property-${id}-price-change`);
      }

      // Sync to localStorage for offline
      if (typeof window !== 'undefined') {
        localStorage.setItem('favorites', JSON.stringify(uniqueIds));
      }
    } catch (error) {
      console.warn('Failed to load favorites from backend, using localStorage', error);
      // Already initialized from localStorage in useState
    } finally {
      setLoading(false);
    }
  };

  const toggleFavorite = useCallback(async (propertyId: number) => {
    const isFavorited = favorites.includes(propertyId);
    
    // Optimistic update
    const newFavorites = isFavorited
      ? favorites.filter(id => id !== propertyId)
      : [...favorites, propertyId];
    setFavorites(newFavorites);
    setOptimisticIds(prev => new Set(prev).add(propertyId));

    // Sync localStorage immediately
    if (typeof window !== 'undefined') {
      localStorage.setItem('favorites', JSON.stringify(newFavorites));
    }

    // Subscribe or unsubscribe from Beams price-change notifications
    if (isFavorited) {
      unsubscribeFromInterest(`property-${propertyId}-price-change`);
    } else {
      subscribeToInterest(`property-${propertyId}-price-change`);
    }

    // Sync with backend
    try {
      await apiClient.post(API_ENDPOINTS.wishlists.toggleProperty, {
        property_id: propertyId,
      });
      
      notify.success({
        title: isFavorited ? 'Removed from favorites' : 'Added to favorites',
        description: isFavorited 
          ? 'Property removed from your favorites'
          : 'Property added to your favorites. You\'ll receive notifications for price changes.',
      });
    } catch (error) {
      console.error('Failed to sync favorite with backend', error);
      // Rollback optimistic change
      setFavorites(favorites);
      if (typeof window !== 'undefined') {
        localStorage.setItem('favorites', JSON.stringify(favorites));
      }
      // Revert Beams subscription
      if (isFavorited) {
        subscribeToInterest(`property-${propertyId}-price-change`);
      } else {
        unsubscribeFromInterest(`property-${propertyId}-price-change`);
      }
      notify.error({
        title: 'Error',
        description: 'Failed to update favorites',
      });
    } finally {
      setOptimisticIds(prev => {
        const newSet = new Set(prev);
        newSet.delete(propertyId);
        return newSet;
      });
    }
  }, [favorites, subscribeToInterest, unsubscribeFromInterest]);

  const isFavorite = useCallback((propertyId: number) => {
    return favorites.includes(propertyId);
  }, [favorites]);

  const isOptimistic = useCallback((propertyId: number) => {
    return optimisticIds.has(propertyId);
  }, [optimisticIds]);

  return {
    favorites,
    toggleFavorite,
    isFavorite,
    isOptimistic,
    loading,
  };
}
