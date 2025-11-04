import { apiClient } from './client';

export interface Property {
  id: number;
  title: string;
  description: string;
  type: string;
  furnishing_status?: string;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  min_nights?: number;
  max_nights?: number;
  price_per_night: number;
  price_per_week?: number;
  price_per_month?: number;
  cleaning_fee?: number;
  security_deposit?: number;
  street_address: string;
  city: string;
  state?: string;
  country: string;
  postal_code?: string;
  latitude?: number;
  longitude?: number;
  area_sqm?: number;
  square_footage?: number;
  built_year?: number;
  floor_number?: number;
  parking_available?: boolean;
  parking_spaces?: number;
  is_active: boolean;
  is_featured: boolean;
  status: 'draft' | 'published' | 'inactive';
  available_from?: string;
  available_until?: string;
  blocked_dates?: string[];
  custom_pricing?: { [key: string]: number };
  rules?: string[];
  images?: string[];
  main_image?: string;
  user_id: number;
  created_at: string;
  updated_at: string;
  amenities?: Amenity[];
  user?: {
    id: number;
    name: string;
    email: string;
    avatar?: string;
  };
  average_rating?: number;
  total_reviews?: number;
  reviews_count?: number;
}

export interface Amenity {
  id: number;
  name: string;
  icon?: string;
  category?: string;
}

export interface PropertyFilters {
  search?: string;
  city?: string;
  country?: string;
  min_price?: number;
  max_price?: number;
  guests?: number;
  bedrooms?: number;
  bathrooms?: number;
  check_in?: string;
  check_out?: string;
  amenities?: string;
  sort_by?: 'created_at' | 'price' | 'rating';
  sort_order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}

export interface PropertyFormData {
  title: string;
  description: string;
  type: string;
  furnishing_status?: string;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  min_nights?: number;
  max_nights?: number;
  price_per_night: number;
  price_per_week?: number;
  price_per_month?: number;
  cleaning_fee?: number;
  security_deposit?: number;
  street_address: string;
  city: string;
  state?: string;
  country: string;
  postal_code?: string;
  latitude?: number;
  longitude?: number;
  area_sqm?: number;
  square_footage?: number;
  built_year?: number;
  floor_number?: number;
  parking_available?: boolean;
  parking_spaces?: number;
  status?: 'draft' | 'published';
  available_from?: string;
  available_until?: string;
  rules?: string[];
  amenities?: number[];
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
}

// Properties API
export const propertiesApi = {
  // Get all properties (public)
  getAll: (filters?: PropertyFilters) => 
    apiClient.get<PaginatedResponse<Property>>('/properties', { params: filters }),

  // Get single property (public)
  getById: (id: number) => 
    apiClient.get<ApiResponse<Property>>(`/properties/${id}`),

  // Get my properties (owner)
  getMy: (filters?: PropertyFilters) => 
    apiClient.get<PaginatedResponse<Property>>('/my-properties', { params: filters }),

  // Create property (owner)
  create: (data: PropertyFormData) => 
    apiClient.post<ApiResponse<Property>>('/properties', data),

  // Update property (owner)
  update: (id: number, data: Partial<PropertyFormData>) => 
    apiClient.put<ApiResponse<Property>>(`/properties/${id}`, data),

  // Delete property (owner)
  delete: (id: number) => 
    apiClient.delete<ApiResponse<null>>(`/properties/${id}`),

  // Publish property
  publish: (id: number) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/publish`),

  // Unpublish property
  unpublish: (id: number) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/unpublish`),

  // Upload images
  uploadImages: (id: number, files: FileList | File[]) => {
    const formData = new FormData();
    Array.from(files).forEach((file, index) => {
      formData.append(`images[${index}]`, file);
    });
    return apiClient.post<ApiResponse<Property>>(`/properties/${id}/images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  // Delete image
  deleteImage: (id: number, imageIndex: number) => 
    apiClient.delete<ApiResponse<Property>>(`/properties/${id}/images/${imageIndex}`),

  // Set main image
  setMainImage: (id: number, imageIndex: number) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/main-image`, { image_index: imageIndex }),

  // Block dates
  blockDates: (id: number, dates: string[]) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/block-dates`, { dates }),

  // Unblock dates
  unblockDates: (id: number, dates: string[]) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/unblock-dates`, { dates }),

  // Set custom pricing
  setCustomPricing: (id: number, pricing: { [key: string]: number }) => 
    apiClient.post<ApiResponse<Property>>(`/properties/${id}/custom-pricing`, { pricing }),

  // Get featured properties
  getFeatured: () => 
    apiClient.get<ApiResponse<Property[]>>('/properties/featured'),

  // Search properties
  search: (filters: PropertyFilters) => 
    apiClient.get<PaginatedResponse<Property>>('/properties/search', { params: filters }),

  // Get amenities
  getAmenities: () => 
    apiClient.get<ApiResponse<Amenity[]>>('/amenities'),
};
