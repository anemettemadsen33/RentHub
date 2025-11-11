"use client";
import { useQuery } from '@tanstack/react-query';
import { getDashboardStats } from '@/lib/api-client';

export function useDashboardStats() {
  return useQuery({
    queryKey: ['dashboard', 'stats'],
    queryFn: () => getDashboardStats(),
    staleTime: 60_000,
    refetchOnWindowFocus: false,
  });
}
