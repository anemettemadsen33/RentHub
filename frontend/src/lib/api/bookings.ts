import { apiClient } from './client';
import { Property } from './properties';

export interface Booking {
  id: number;
  property_id: number;
  user_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  nights: number;
  price_per_night: number;
  subtotal: number;
  cleaning_fee: number;
  security_deposit: number;
  taxes: number;
  total_amount: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  guest_name: string;
  guest_email: string;
  guest_phone?: string;
  special_requests?: string;
  payment_status: 'unpaid' | 'paid' | 'refunded';
  payment_method?: string;
  payment_transaction_id?: string;
  paid_at?: string;
  confirmed_at?: string;
  cancelled_at?: string;
  created_at: string;
  updated_at: string;
  property?: Property;
  user?: {
    id: number;
    name: string;
    email: string;
    avatar?: string;
  };
}

export interface BookingFormData {
  property_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  guest_name: string;
  guest_email: string;
  guest_phone?: string;
  special_requests?: string;
}

export interface BookingCalculation {
  nights: number;
  price_per_night: number;
  subtotal: number;
  cleaning_fee: number;
  security_deposit: number;
  taxes: number;
  total_amount: number;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
}

// Bookings API
export const bookingsApi = {
  // Get all bookings (user's own bookings)
  getAll: (params?: { per_page?: number; page?: number; status?: string }) =>
    apiClient.get<PaginatedResponse<Booking>>('/bookings', { params }),

  // Get single booking
  getById: (id: number) =>
    apiClient.get<ApiResponse<Booking>>(`/bookings/${id}`),

  // Calculate booking price
  calculate: (data: {
    property_id: number;
    check_in: string;
    check_out: string;
    guests: number;
  }) =>
    apiClient.post<ApiResponse<BookingCalculation>>('/bookings/calculate', data),

  // Create booking
  create: (data: BookingFormData) =>
    apiClient.post<ApiResponse<Booking>>('/bookings', data),

  // Update booking
  update: (id: number, data: Partial<BookingFormData>) =>
    apiClient.put<ApiResponse<Booking>>(`/bookings/${id}`, data),

  // Cancel booking
  cancel: (id: number, reason?: string) =>
    apiClient.post<ApiResponse<Booking>>(`/bookings/${id}/cancel`, { reason }),

  // Confirm booking (owner)
  confirm: (id: number) =>
    apiClient.post<ApiResponse<Booking>>(`/bookings/${id}/confirm`),

  // Check property availability
  checkAvailability: (propertyId: number, checkIn: string, checkOut: string) =>
    apiClient.get<ApiResponse<{ available: boolean; blocked_dates?: string[] }>>(
      `/properties/${propertyId}/availability`,
      { params: { check_in: checkIn, check_out: checkOut } }
    ),

  // Get my bookings (tenant)
  getMy: (params?: { per_page?: number; page?: number; status?: string }) =>
    apiClient.get<PaginatedResponse<Booking>>('/my-bookings', { params }),

  // Get property bookings (owner)
  getPropertyBookings: (
    propertyId: number,
    params?: { per_page?: number; page?: number; status?: string }
  ) =>
    apiClient.get<PaginatedResponse<Booking>>(`/properties/${propertyId}/bookings`, { params }),
};
