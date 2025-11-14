"use client";

import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { Property } from '@/types';

export interface UsePropertiesOptions {
  search?: string;
  filters?: {
    priceRange?: [number, number];
    bedrooms?: number | null;
    bathrooms?: number | null;
    propertyType?: string[];
    amenities?: string[];
    guests?: number | null;
    instantBook?: boolean;
  };
  sortBy?: 'newest' | 'price-asc' | 'price-desc' | 'rating';
  bounds?: [number, number, number, number]; // [minLng, minLat, maxLng, maxLat]
  zoom?: number;
}

export function useProperties(options: UsePropertiesOptions = {}) {
  return useQuery({
    queryKey: ['properties', options],
    queryFn: async () => {
      const params = new URLSearchParams();
      
      if (options.search) {
        params.append('search', options.search);
      }
      
      if (options.filters) {
        if (options.filters.priceRange) {
          params.append('min_price', options.filters.priceRange[0].toString());
          params.append('max_price', options.filters.priceRange[1].toString());
        }
        if (options.filters.bedrooms) {
          params.append('bedrooms', options.filters.bedrooms.toString());
        }
        if (options.filters.bathrooms) {
          params.append('bathrooms', options.filters.bathrooms.toString());
        }
        if (options.filters.propertyType?.length) {
          options.filters.propertyType.forEach(type => {
            params.append('property_type[]', type);
          });
        }
        if (options.filters.amenities?.length) {
          options.filters.amenities.forEach(amenity => {
            params.append('amenities[]', amenity);
          });
        }
        if (options.filters.guests) {
          params.append('max_guests', options.filters.guests.toString());
        }
        if (options.filters.instantBook) {
          params.append('instant_book', '1');
        }
      }
      
      if (options.sortBy) {
        params.append('sort', options.sortBy);
      }
      
      if (options.bounds) {
        const [minLng, minLat, maxLng, maxLat] = options.bounds;
        params.append('min_lng', minLng.toString());
        params.append('min_lat', minLat.toString());
        params.append('max_lng', maxLng.toString());
        params.append('max_lat', maxLat.toString());
      }
      
      if (options.zoom) {
        params.append('zoom', options.zoom.toString());
      }
      
      const response = await apiClient.get<Property[]>(`${API_ENDPOINTS.properties.list}?${params.toString()}`);
      return response.data || [];
    },
    staleTime: 60_000, // 1 minute
    refetchOnWindowFocus: false,
  });
}

export function useProperty(id: number) {
  return useQuery({
    queryKey: ['properties', id],
    queryFn: async () => {
      const response = await apiClient.get<{ success: boolean; data: Property }>(`${API_ENDPOINTS.properties.show(id)}`);
      return response.data?.data || null;
    },
    staleTime: 60_000,
    refetchOnWindowFocus: false,
  });
}
