"use client";
import { useCallback, useRef } from 'react';
import { trackConversion } from '@/lib/analytics-client';
import { useAuth } from '@/contexts/auth-context';

/**
 * useConversionTracking
 * Lightweight abstraction for firing standardized conversion events.
 * Provides guard against duplicate rapid submissions and ensures
 * consistent payload shapes.
 *
 * Events emitted:
 *  - booking_submitted
 *  - wishlist_toggled (added: true/false)
 */
export function useConversionTracking() {
  const { user } = useAuth();
  const lastBookingRef = useRef<number>(0);
  const lastSearchRef = useRef<number>(0);

  const trackBookingSubmit = useCallback((data: {
    propertyId: string | number;
    checkIn?: string;
    checkOut?: string;
    guests?: number;
    total?: number;
    nights?: number;
    discounts?: { referral?: number; loyalty?: number; total?: number };
  }) => {
    const now = Date.now();
    // Debounce duplicate form double-click within 1s
    if (now - lastBookingRef.current < 1000) return;
    lastBookingRef.current = now;
    trackConversion('booking_submitted', {
      ...data,
      userId: user?.id,
      role: (user as any)?.role,
    });
  }, [user]);

  const trackWishlistToggle = useCallback((propertyId: string | number, added: boolean) => {
    trackConversion('wishlist_toggled', {
      propertyId,
      added,
      userId: user?.id,
      role: (user as any)?.role,
    });
  }, [user]);

  return {
    trackBookingSubmit,
    trackWishlistToggle,
    trackSearchPerformed: (data: { query: string; results: number; filtersCount: number }) => {
      const now = Date.now();
      // debounced at 1s to avoid spamming on every keystroke (debouncedSearch already helps)
      if (now - lastSearchRef.current < 1000) return;
      lastSearchRef.current = now;
      if (!data.query) return; // ignore empty searches
      trackConversion('search_performed', {
        ...data,
        userId: user?.id,
        role: (user as any)?.role,
      });
    },
    trackFiltersApplied: (changed: Record<string, any>) => {
      // Shallow record of changed filter keys/values
      trackConversion('filters_applied', {
        changed,
        userId: user?.id,
        role: (user as any)?.role,
      });
    },
  };
}
