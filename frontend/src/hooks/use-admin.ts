"use client";

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import apiClient from '@/lib/api-client';

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: 'active' | 'inactive' | 'suspended';
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  last_login_at: string | null;
  permissions: string[];
}

export interface Property {
  id: number;
  title: string;
  status: 'draft' | 'published' | 'suspended' | 'rejected';
  user_id: number;
  owner_name: string;
  owner_email: string;
  created_at: string;
  updated_at: string;
  price_per_night: number;
  city: string;
  country: string;
  rating: number;
  bookings_count: number;
}

export interface SystemStats {
  total_users: number;
  active_users: number;
  total_properties: number;
  active_properties: number;
  total_bookings: number;
  completed_bookings: number;
  total_revenue: number;
  monthly_revenue: number;
  system_health: 'healthy' | 'degraded' | 'critical';
  server_uptime: number;
  database_uptime: number;
}

export function useUsers(options: { page?: number; limit?: number; search?: string; role?: string } = {}) {
  return useQuery({
    queryKey: ['admin', 'users', options],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (options.page) params.append('page', options.page.toString());
      if (options.limit) params.append('limit', options.limit.toString());
      if (options.search) params.append('search', options.search);
      if (options.role) params.append('role', options.role);
      
      const response = await apiClient.get(`/admin/users?${params.toString()}`);
      return response.data;
    },
    staleTime: 60_000, // 1 minute
    refetchOnWindowFocus: false,
  });
}

export function usePropertiesAdmin(options: { 
  page?: number; 
  limit?: number; 
  status?: string; 
  search?: string;
  sortBy?: string;
} = {}) {
  return useQuery({
    queryKey: ['admin', 'properties', options],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (options.page) params.append('page', options.page.toString());
      if (options.limit) params.append('limit', options.limit.toString());
      if (options.status) params.append('status', options.status);
      if (options.search) params.append('search', options.search);
      if (options.sortBy) params.append('sort_by', options.sortBy);
      
      const response = await apiClient.get(`/admin/properties?${params.toString()}`);
      return response.data;
    },
    staleTime: 60_000,
    refetchOnWindowFocus: false,
  });
}

export function useSystemStats() {
  return useQuery({
    queryKey: ['admin', 'stats'],
    queryFn: async () => {
      const response = await apiClient.get('/admin/stats');
      return response.data as SystemStats;
    },
    staleTime: 30_000, // 30 seconds
    refetchOnWindowFocus: false,
  });
}

export function useUpdateUserStatus() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async ({ userId, status }: { userId: number; status: 'active' | 'inactive' | 'suspended' }) => {
      const response = await apiClient.put(`/admin/users/${userId}/status`, { status });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'users'] });
    },
  });
}

export function useUpdateUserRole() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async ({ userId, role }: { userId: number; role: string }) => {
      const response = await apiClient.put(`/admin/users/${userId}/role`, { role });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'users'] });
    },
  });
}

export function useUpdatePropertyStatus() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async ({ propertyId, status }: { propertyId: number; status: string }) => {
      const response = await apiClient.put(`/admin/properties/${propertyId}/status`, { status });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'properties'] });
    },
  });
}

export function useDeleteProperty() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async (propertyId: number) => {
      const response = await apiClient.delete(`/admin/properties/${propertyId}`);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'properties'] });
    },
  });
}

export function useSystemHealth() {
  return useQuery({
    queryKey: ['admin', 'system-health'],
    queryFn: async () => {
      const response = await apiClient.get('/admin/system-health');
      return response.data;
    },
    staleTime: 30_000,
    refetchOnWindowFocus: false,
  });
}