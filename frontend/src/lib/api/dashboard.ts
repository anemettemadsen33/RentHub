import { apiClient } from './client';

export interface DashboardStats {
  total_bookings?: number;
  active_bookings?: number;
  total_revenue?: number;
  total_properties?: number;
  active_properties?: number;
  average_rating?: number;
  occupancy_rate?: number;
  pending_bookings?: number;
}

export interface RevenueData {
  period: string;
  revenue: number;
  bookings: number;
}

export interface BookingData {
  id: number;
  property_id: number;
  property_title: string;
  check_in: string;
  check_out: string;
  status: string;
  total_amount: number;
}

export interface PropertyPerformance {
  property_id: number;
  property_title: string;
  bookings: number;
  revenue: number;
  occupancy_rate: number;
  average_rating: number;
}

// Owner Dashboard APIs
export const ownerDashboardApi = {
  // Get overview stats
  getOverview: (params?: { period?: 'week' | 'month' | 'year' }) => 
    apiClient.get<{ data: DashboardStats }>('/owner/dashboard/overview', { params }),

  // Get booking statistics
  getBookingStatistics: (params?: { 
    start_date?: string;
    end_date?: string;
  }) => apiClient.get('/owner/dashboard/booking-statistics', { params }),

  // Get revenue reports
  getRevenueReports: (params?: {
    period?: 'daily' | 'weekly' | 'monthly' | 'yearly';
    start_date?: string;
    end_date?: string;
  }) => apiClient.get<{ data: RevenueData[] }>('/owner/dashboard/revenue-reports', { params }),

  // Get occupancy rate
  getOccupancyRate: (params?: {
    property_id?: number;
    start_date?: string;
    end_date?: string;
  }) => apiClient.get('/owner/dashboard/occupancy-rate', { params }),

  // Get property performance
  getPropertyPerformance: (params?: {
    sort_by?: 'revenue' | 'bookings' | 'rating';
    limit?: number;
  }) => apiClient.get<{ data: PropertyPerformance[] }>('/owner/dashboard/property-performance', { params }),

  // Get guest demographics
  getGuestDemographics: () => 
    apiClient.get('/owner/dashboard/guest-demographics'),

  // Legacy endpoints (for backward compatibility)
  getStats: () => 
    apiClient.get<{ data: DashboardStats }>('/owner/dashboard/stats'),

  getRevenue: (params?: { period?: string }) => 
    apiClient.get('/owner/dashboard/revenue', { params }),

  getProperties: () => 
    apiClient.get('/owner/dashboard/properties'),
};

// Tenant Dashboard APIs
export const tenantDashboardApi = {
  // Get overview stats
  getOverview: () => 
    apiClient.get<{ data: DashboardStats }>('/tenant/dashboard/overview'),

  // Get booking history
  getBookingHistory: (params?: {
    status?: string;
    page?: number;
    per_page?: number;
  }) => apiClient.get<{ data: BookingData[] }>('/tenant/dashboard/booking-history', { params }),

  // Get spending reports
  getSpendingReports: (params?: {
    period?: 'monthly' | 'yearly';
    year?: number;
  }) => apiClient.get('/tenant/dashboard/spending-reports', { params }),

  // Get saved properties
  getSavedProperties: () => 
    apiClient.get('/tenant/dashboard/saved-properties'),

  // Get review history
  getReviewHistory: () => 
    apiClient.get('/tenant/dashboard/review-history'),

  // Get upcoming trips
  getUpcomingTrips: () => 
    apiClient.get('/tenant/dashboard/upcoming-trips'),

  // Get travel statistics
  getTravelStatistics: () => 
    apiClient.get('/tenant/dashboard/travel-statistics'),

  // Legacy endpoint
  getStats: () => 
    apiClient.get<{ data: DashboardStats }>('/tenant/dashboard/stats'),
};

// Generic Dashboard API (role-agnostic)
export const dashboardApi = {
  getOverview: () => 
    apiClient.get<{ data: DashboardStats }>('/dashboard'),

  getRevenue: (params?: { period?: string }) => 
    apiClient.get('/dashboard/revenue', { params }),

  getBookings: (params?: {
    status?: string;
    page?: number;
  }) => apiClient.get('/dashboard/bookings', { params }),

  getProperties: () => 
    apiClient.get('/dashboard/properties'),
};
