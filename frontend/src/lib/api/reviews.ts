import { apiClient } from './client';

export interface Review {
  id: number;
  property_id: number;
  user_id: number;
  booking_id?: number;
  rating: number;
  comment?: string;
  cleanliness_rating?: number;
  communication_rating?: number;
  check_in_rating?: number;
  accuracy_rating?: number;
  location_rating?: number;
  value_rating?: number;
  is_approved: boolean;
  owner_response?: string;
  owner_response_at?: string;
  property?: any;
  user?: any;
  created_at: string;
  updated_at: string;
}

export interface CreateReviewData {
  property_id: number;
  booking_id?: number;
  rating: number;
  comment?: string;
  cleanliness_rating?: number;
  communication_rating?: number;
  check_in_rating?: number;
  accuracy_rating?: number;
  location_rating?: number;
  value_rating?: number;
}

export interface ReviewResponse {
  content: string;
}

export const reviewsApi = {
  // Get all reviews (public)
  getAll: (params?: {
    property_id?: number;
    user_id?: number;
    page?: number;
    per_page?: number;
  }) => apiClient.get<{ data: Review[] }>('/reviews', { params }),

  // Get single review
  getById: (id: number) => 
    apiClient.get<{ data: Review }>(`/reviews/${id}`),

  // Get my reviews (authenticated)
  getMyReviews: () => 
    apiClient.get<{ data: Review[] }>('/my-reviews'),

  // Get property rating
  getPropertyRating: (propertyId: number) => 
    apiClient.get<{ 
      data: {
        average_rating: number;
        total_reviews: number;
        rating_breakdown: {
          5: number;
          4: number;
          3: number;
          2: number;
          1: number;
        };
      }
    }>(`/properties/${propertyId}/rating`),

  // Create review (authenticated)
  create: (data: CreateReviewData) => 
    apiClient.post<{ data: Review }>('/reviews', data),

  // Update review (authenticated)
  update: (id: number, data: Partial<CreateReviewData>) => 
    apiClient.put<{ data: Review }>(`/reviews/${id}`, data),

  // Delete review (authenticated)
  delete: (id: number) => 
    apiClient.delete(`/reviews/${id}`),

  // Add response to review (owner/admin only)
  addResponse: (id: number, response: string) => 
    apiClient.post<{ data: Review }>(`/reviews/${id}/response`, { response }),

  // Vote on review helpfulness (authenticated)
  vote: (id: number, helpful: boolean) => 
    apiClient.post(`/reviews/${id}/vote`, { helpful }),
};
