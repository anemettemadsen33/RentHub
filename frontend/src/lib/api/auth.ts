import { apiClient } from './client';

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  role: 'owner' | 'tenant';
}

export interface LoginData {
  email: string;
  password: string;
  remember?: boolean;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data?: {
    user: User;
    token: string;
  };
  requires_2fa?: boolean;
  code?: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: string;
  email_verified_at?: string;
  phone_verified_at?: string;
  avatar?: string;
  bio?: string;
  date_of_birth?: string;
  gender?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
  profile_completed_at?: string;
  two_factor_enabled?: boolean;
}

export interface ProfileCompletionStatus {
  success: boolean;
  data: {
    percentage: number;
    completed_steps: number;
    total_steps: number;
    steps: {
      [key: string]: {
        label: string;
        fields: string[];
        completed: boolean;
      };
    };
    is_complete: boolean;
    missing_fields: string[];
    profile_completed_at?: string;
  };
}

// Authentication
export const authApi = {
  register: (data: RegisterData) => 
    apiClient.post<AuthResponse>('/register', data),

  login: (data: LoginData) => 
    apiClient.post<AuthResponse>('/login', data),

  logout: () => 
    apiClient.post('/logout'),

  me: () => 
    apiClient.get<{ success: boolean; data: User }>('/me'),

  // Email Verification
  resendVerification: () => 
    apiClient.post('/resend-verification'),

  // Phone Verification
  sendPhoneVerification: (phone: string) => 
    apiClient.post('/send-phone-verification', { phone }),

  verifyPhone: (code: string) => 
    apiClient.post('/verify-phone', { code }),

  // Password Reset
  forgotPassword: (email: string) => 
    apiClient.post('/forgot-password', { email }),

  resetPassword: (data: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => apiClient.post('/reset-password', data),

  // Two-Factor Authentication
  sendTwoFactorCode: (email: string) => 
    apiClient.post('/2fa/send-code', { email }),

  verifyTwoFactorCode: (email: string, code: string) => 
    apiClient.post<AuthResponse>('/2fa/verify', { email, code }),

  verifyRecoveryCode: (email: string, recovery_code: string) => 
    apiClient.post<AuthResponse>('/2fa/verify-recovery', { email, recovery_code }),

  enableTwoFactor: () => 
    apiClient.post('/2fa/enable'),

  disableTwoFactor: (password: string) => 
    apiClient.post('/2fa/disable', { password }),

  // Profile Completion
  getProfileCompletionStatus: () => 
    apiClient.get<ProfileCompletionStatus>('/profile/completion-status'),

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

  // Social Login
  getSocialLoginUrl: (provider: 'google' | 'facebook') => 
    `${process.env.NEXT_PUBLIC_API_URL}/api/v1/auth/${provider}`,
};
