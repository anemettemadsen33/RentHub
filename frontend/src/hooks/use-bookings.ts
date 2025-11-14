"use client";

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Booking } from '@/types';
import apiClient, { ensureCsrfCookie } from '@/lib/api-client';

export interface UseBookingsOptions {
  filter?: 'all' | 'upcoming' | 'past' | 'cancelled';
  page?: number;
  limit?: number;
}

export function useBookings(options: UseBookingsOptions = {}) {
  return useQuery({
    queryKey: ['bookings', options],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (options.filter) {
        params.append('filter', options.filter);
      }
      if (options.page) {
        params.append('page', options.page.toString());
      }
      if (options.limit) {
        params.append('limit', options.limit.toString());
      }
      
      const response = await apiClient.get(`/bookings?${params.toString()}`);
      return response.data;
    },
    staleTime: 30_000, // 30 seconds
    refetchOnWindowFocus: false,
  });
}

export function useBooking(bookingId: number) {
  return useQuery({
    queryKey: ['bookings', bookingId],
    queryFn: async () => {
      const response = await apiClient.get(`/bookings/${bookingId}`);
      return response.data;
    },
    staleTime: 30_000,
    refetchOnWindowFocus: false,
    enabled: !!bookingId,
  });
}

export function useCancelBooking() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async (bookingId: number) => {
      await ensureCsrfCookie();
      const response = await apiClient.post(`/bookings/${bookingId}/cancel`);
      return response.data;
    },
    onSuccess: () => {
      // Invalidate bookings queries to refresh the list
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });
}

export function useExportBookings() {
  return useMutation({
    mutationFn: async (format: 'pdf' | 'csv' | 'excel' = 'pdf') => {
      const response = await apiClient.get(`/bookings/export?format=${format}`, {
        responseType: 'blob',
      });
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `bookings.${format}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
      
      return response.data;
    },
  });
}

export function useBookingStats() {
  return useQuery({
    queryKey: ['bookings', 'stats'],
    queryFn: async () => {
      const response = await apiClient.get('/bookings/stats');
      return response.data;
    },
    staleTime: 60_000, // 1 minute
    refetchOnWindowFocus: false,
  });
}