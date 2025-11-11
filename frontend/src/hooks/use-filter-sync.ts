"use client";

import { useCallback } from 'react';
import { useRouter, useSearchParams, usePathname } from 'next/navigation';
import { FilterOptions } from '@/components/filter-panel';

/**
 * Hook to sync filter state with URL query parameters
 * Enables bookmarkable/shareable search URLs with back/forward navigation support
 */
export function useFilterSync() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();

  const parseFiltersFromURL = useCallback((): Partial<FilterOptions> => {
    const filters: Partial<FilterOptions> = {};

    // Price range
    const minPrice = searchParams.get('minPrice');
    const maxPrice = searchParams.get('maxPrice');
    if (minPrice && maxPrice) {
      filters.priceRange = [parseInt(minPrice), parseInt(maxPrice)];
    }

    // Bedrooms
    const bedrooms = searchParams.get('bedrooms');
    if (bedrooms) {
      filters.bedrooms = parseInt(bedrooms);
    }

    // Bathrooms
    const bathrooms = searchParams.get('bathrooms');
    if (bathrooms) {
      filters.bathrooms = parseInt(bathrooms);
    }

    // Guests
    const guests = searchParams.get('guests');
    if (guests) {
      filters.guests = parseInt(guests);
    }

    // Property types (comma-separated)
    const propertyType = searchParams.get('type');
    if (propertyType) {
      filters.propertyType = propertyType.split(',');
    }

    // Amenities (comma-separated)
    const amenities = searchParams.get('amenities');
    if (amenities) {
      filters.amenities = amenities.split(',');
    }

    // Instant book
    const instantBook = searchParams.get('instantBook');
    if (instantBook) {
      filters.instantBook = instantBook === 'true';
    }

    return filters;
  }, [searchParams]);

  const syncFiltersToURL = useCallback((filters: FilterOptions) => {
    const params = new URLSearchParams();

    // Price range
    if (filters.priceRange && (filters.priceRange[0] > 0 || filters.priceRange[1] < 1000)) {
      params.set('minPrice', filters.priceRange[0].toString());
      params.set('maxPrice', filters.priceRange[1].toString());
    }

    // Bedrooms
    if (filters.bedrooms) {
      params.set('bedrooms', filters.bedrooms.toString());
    }

    // Bathrooms
    if (filters.bathrooms) {
      params.set('bathrooms', filters.bathrooms.toString());
    }

    // Guests
    if (filters.guests) {
      params.set('guests', filters.guests.toString());
    }

    // Property types
    if (filters.propertyType && filters.propertyType.length > 0) {
      params.set('type', filters.propertyType.join(','));
    }

    // Amenities
    if (filters.amenities && filters.amenities.length > 0) {
      params.set('amenities', filters.amenities.join(','));
    }

    // Instant book
    if (filters.instantBook) {
      params.set('instantBook', 'true');
    }

    // Update URL without triggering a full page reload
    const queryString = params.toString();
    const newURL = queryString ? `${pathname}?${queryString}` : pathname;
    
    router.replace(newURL, { scroll: false });
  }, [router, pathname]);

  const clearFilters = useCallback(() => {
    router.replace(pathname, { scroll: false });
  }, [router, pathname]);

  const getShareableURL = useCallback((filters: FilterOptions): string => {
    const params = new URLSearchParams();

    if (filters.priceRange) {
      params.set('minPrice', filters.priceRange[0].toString());
      params.set('maxPrice', filters.priceRange[1].toString());
    }
    if (filters.bedrooms) params.set('bedrooms', filters.bedrooms.toString());
    if (filters.bathrooms) params.set('bathrooms', filters.bathrooms.toString());
    if (filters.guests) params.set('guests', filters.guests.toString());
    if (filters.propertyType?.length) params.set('type', filters.propertyType.join(','));
    if (filters.amenities?.length) params.set('amenities', filters.amenities.join(','));
    if (filters.instantBook) params.set('instantBook', 'true');

    const queryString = params.toString();
    return queryString ? `${window.location.origin}${pathname}?${queryString}` : `${window.location.origin}${pathname}`;
  }, [pathname]);

  return {
    parseFiltersFromURL,
    syncFiltersToURL,
    clearFilters,
    getShareableURL,
  };
}
