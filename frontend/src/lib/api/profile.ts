import { apiClient } from './client';

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: string;
  bio?: string;
  avatar?: string;
  date_of_birth?: string;
  gender?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
  email_verified_at?: string;
  phone_verified_at?: string;
  profile_completed_at?: string;
  two_factor_enabled?: boolean;
  created_at: string;
  updated_at: string;
}

export interface UpdateProfileData {
  name?: string;
  phone?: string;
  bio?: string;
  date_of_birth?: string;
  gender?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
}

export interface UserSettings {
  language?: string;
  currency?: string;
  timezone?: string;
  email_notifications?: boolean;
  push_notifications?: boolean;
  sms_notifications?: boolean;
}

export interface PrivacySettings {
  show_profile?: boolean;
  show_email?: boolean;
  show_phone?: boolean;
  allow_messages?: boolean;
}

export interface VerificationStatus {
  email_verified: boolean;
  phone_verified: boolean;
  id_verified: boolean;
  address_verified: boolean;
  background_check_completed: boolean;
  verification_level: 'none' | 'basic' | 'intermediate' | 'advanced';
}

export const profileApi = {
  // Get current user profile
  getProfile: () => 
    apiClient.get<{ data: UserProfile }>('/profile'),

  // Update profile
  updateProfile: (data: UpdateProfileData) => 
    apiClient.put<{ data: UserProfile }>('/profile', data),

  // Upload avatar
  uploadAvatar: (file: File) => {
    const formData = new FormData();
    formData.append('avatar', file);
    return apiClient.post<{ data: { avatar_url: string } }>('/profile/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  // Delete avatar
  deleteAvatar: () => 
    apiClient.delete('/profile/avatar'),

  // Get user settings
  getSettings: () => 
    apiClient.get<{ data: UserSettings }>('/settings'),

  // Update settings
  updateSettings: (settings: Partial<UserSettings>) => 
    apiClient.put<{ data: UserSettings }>('/settings', settings),

  // Get privacy settings
  getPrivacySettings: () => 
    apiClient.get<{ data: PrivacySettings }>('/privacy'),

  // Update privacy settings
  updatePrivacySettings: (settings: Partial<PrivacySettings>) => 
    apiClient.put<{ data: PrivacySettings }>('/privacy', settings),

  // Get verification status
  getVerificationStatus: () => 
    apiClient.get<{ data: VerificationStatus }>('/verification-status'),

  // Profile completion wizard
  getCompletionStatus: () => 
    apiClient.get('/profile/completion-status'),

  updateBasicInfo: (data: {
    name: string;
    phone?: string;
    date_of_birth?: string;
    gender?: string;
  }) => apiClient.post('/profile/basic-info', data),

  updateContactInfo: (data: {
    phone: string;
  }) => apiClient.post('/profile/contact-info', data),

  updateProfileDetails: (data: {
    bio?: string;
    address?: string;
    city?: string;
    state?: string;
    country?: string;
    zip_code?: string;
  }) => apiClient.post('/profile/details', data),

  completeWizard: () => 
    apiClient.post('/profile/complete'),
};
