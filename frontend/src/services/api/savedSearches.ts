import { apiClient } from './client';
import { SavedSearch, SavedSearchFormData, SavedSearchStatistics } from '@/types/saved-search';
import { Property } from '@/types';

interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
}

interface ExecuteSearchResponse {
  saved_search: SavedSearch;
  properties: Property[];
  count: number;
}

interface NewListingsResponse {
  saved_search: SavedSearch;
  new_properties: Property[];
  count: number;
  since: string;
}

export const savedSearchesApi = {
  // Get all saved searches
  getAll: async (isActive?: boolean): Promise<SavedSearch[]> => {
    const params = isActive !== undefined ? { is_active: isActive } : {};
    const response = await apiClient.get<ApiResponse<SavedSearch[]>>('/saved-searches', { params });
    return response.data.data;
  },

  // Get single saved search
  getOne: async (id: number): Promise<SavedSearch> => {
    const response = await apiClient.get<ApiResponse<SavedSearch>>(`/saved-searches/${id}`);
    return response.data.data;
  },

  // Create saved search
  create: async (data: SavedSearchFormData): Promise<SavedSearch> => {
    const response = await apiClient.post<ApiResponse<SavedSearch>>('/saved-searches', data);
    return response.data.data;
  },

  // Update saved search
  update: async (id: number, data: Partial<SavedSearchFormData>): Promise<SavedSearch> => {
    const response = await apiClient.put<ApiResponse<SavedSearch>>(`/saved-searches/${id}`, data);
    return response.data.data;
  },

  // Delete saved search
  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/saved-searches/${id}`);
  },

  // Execute saved search
  execute: async (id: number): Promise<ExecuteSearchResponse> => {
    const response = await apiClient.post<ApiResponse<ExecuteSearchResponse>>(
      `/saved-searches/${id}/execute`
    );
    return response.data.data;
  },

  // Check new listings
  checkNewListings: async (id: number): Promise<NewListingsResponse> => {
    const response = await apiClient.get<ApiResponse<NewListingsResponse>>(
      `/saved-searches/${id}/new-listings`
    );
    return response.data.data;
  },

  // Toggle alerts
  toggleAlerts: async (id: number): Promise<SavedSearch> => {
    const response = await apiClient.post<ApiResponse<SavedSearch>>(
      `/saved-searches/${id}/toggle-alerts`
    );
    return response.data.data;
  },

  // Get statistics
  getStatistics: async (): Promise<SavedSearchStatistics> => {
    const response = await apiClient.get<ApiResponse<SavedSearchStatistics>>(
      '/saved-searches/statistics'
    );
    return response.data.data;
  },
};
