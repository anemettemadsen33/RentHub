import apiClient, { ensureCsrfCookie } from './api-client';
import { API_ENDPOINTS } from './api-endpoints';

/**
 * Type-safe API service layer
 * Centralized API calls with proper error handling
 */

// Types
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  phone?: string;
  phone_verified_at?: string | null;
  avatar?: string;
  role?: string;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  message?: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role?: 'tenant' | 'owner';
}

export interface Property {
  id: number;
  title: string;
  description?: string;
  address: string;
  city: string;
  state?: string;
  country: string;
  price_per_night: number;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  images?: string[];
  main_image?: string;
  amenities?: string[];
  status: 'draft' | 'published' | 'unlisted';
  user_id: number;
  created_at: string;
  updated_at: string;
}

export interface Booking {
  id: number;
  property_id: number;
  user_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  total_price: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  property?: Property;
  created_at: string;
  updated_at: string;
}

export interface Notification {
  id: string;
  type: string;
  data: any;
  read_at: string | null;
  created_at: string;
}

export interface NotificationPreferences {
  preferences: Array<{
    notification_type: 'booking' | 'payment' | 'review' | 'account' | 'system';
    channel_email: boolean;
    channel_database: boolean;
    channel_sms: boolean;
    channel_push: boolean;
  }>;
}

/**
 * Authentication Service
 */
export const authService = {
  async register(data: RegisterData): Promise<AuthResponse> {
    console.log('[authService] Register request:', { 
      url: API_ENDPOINTS.auth.register, 
      data: { ...data, password: '***', password_confirmation: '***' } 
    });
    try {
      // Ensure CSRF cookie to satisfy Sanctum's CSRF middleware (419 otherwise)
      await ensureCsrfCookie();
      console.log('[authService] CSRF ensured, sending POST /register');
      const response = await apiClient.post(API_ENDPOINTS.auth.register, data);
      console.log('[authService] Register success:', response.status, response.data);
      // Store token and user like login does
      if (response.data.token) {
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        console.log('[authService] Token and user stored in localStorage');
      }
      return response.data;
    } catch (error: any) {
      console.error('[authService] Register failed:', {
        status: error?.response?.status,
        statusText: error?.response?.statusText,
        data: error?.response?.data,
        message: error?.message,
        code: error?.code,
        config: { url: error?.config?.url, method: error?.config?.method },
      });
      throw error;
    }
  },

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    await ensureCsrfCookie();
    console.log('[authService] CSRF ensured, sending POST /login');
    const response = await apiClient.post(API_ENDPOINTS.auth.login, credentials);
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }
    return response.data;
  },

  async logout(): Promise<void> {
    try {
      await apiClient.post(API_ENDPOINTS.auth.logout);
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  },

  async me(): Promise<User> {
    const response = await apiClient.get(API_ENDPOINTS.auth.me);
    return response.data;
  },

  async changePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }): Promise<any> {
    const response = await apiClient.put(API_ENDPOINTS.auth.changePassword, data);
    return response.data;
  },
};

/**
 * Profile Service
 */
export const profileService = {
  async getProfile(): Promise<User> {
    const response = await apiClient.get(API_ENDPOINTS.profile.get);
    return response.data;
  },

  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await apiClient.put(API_ENDPOINTS.profile.update, data);
    return response.data;
  },

  async uploadAvatar(file: File): Promise<User> {
    const formData = new FormData();
    formData.append('avatar', file);
    const response = await apiClient.post(API_ENDPOINTS.profile.uploadAvatar, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async deleteAvatar(): Promise<void> {
    await apiClient.delete(API_ENDPOINTS.profile.deleteAvatar);
  },
};

/**
 * Properties Service
 */
export const propertiesService = {
  async list(params?: any): Promise<{ data: Property[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.properties.list, { params });
    return response.data;
  },

  async featured(): Promise<Property[]> {
    const response = await apiClient.get(API_ENDPOINTS.properties.featured);
    return response.data;
  },

  async search(params: any): Promise<{ data: Property[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.properties.search, { params });
    return response.data;
  },

  async show(id: string | number): Promise<Property> {
    const response = await apiClient.get(API_ENDPOINTS.properties.show(id));
    return response.data;
  },

  async myProperties(): Promise<Property[]> {
    const response = await apiClient.get(API_ENDPOINTS.properties.myProperties);
    return response.data;
  },

  async create(data: Partial<Property>): Promise<Property> {
    const response = await apiClient.post(API_ENDPOINTS.properties.create, data);
    return response.data;
  },

  async update(id: string | number, data: Partial<Property>): Promise<Property> {
    const response = await apiClient.put(API_ENDPOINTS.properties.update(id), data);
    return response.data;
  },

  async delete(id: string | number): Promise<void> {
    await apiClient.delete(API_ENDPOINTS.properties.delete(id));
  },
};

/**
 * Bookings Service
 */
export const bookingsService = {
  async list(params?: any): Promise<{ data: Booking[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.bookings.list, { params });
    return response.data;
  },

  async myBookings(): Promise<Booking[]> {
    const response = await apiClient.get(API_ENDPOINTS.bookings.myBookings);
    return response.data;
  },

  async show(id: string | number): Promise<Booking> {
    const response = await apiClient.get(API_ENDPOINTS.bookings.show(id));
    return response.data;
  },

  async create(data: Partial<Booking>): Promise<Booking> {
    const response = await apiClient.post(API_ENDPOINTS.bookings.create, data);
    return response.data;
  },

  async checkAvailability(data: { property_id: number; check_in: string; check_out: string }): Promise<{ available: boolean }> {
    const response = await apiClient.post(API_ENDPOINTS.bookings.checkAvailability, data);
    return response.data;
  },

  async cancel(id: string | number): Promise<Booking> {
    const response = await apiClient.post(API_ENDPOINTS.bookings.cancel(id));
    return response.data;
  },

  async getInvoices(id: string | number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.bookings.getInvoices(id));
    return response.data;
  },
};

/**
 * Payments Service
 */
export const paymentsService = {
  async list(params?: any): Promise<{ data: any[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.payments.list, { params });
    return response.data;
  },

  async create(data: any): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.payments.create, data);
    return response.data;
  },

  async show(id: string | number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.payments.show(id));
    return response.data;
  },
};

/**
 * Invoices Service
 */
export const invoicesService = {
  async list(params?: any): Promise<{ data: any[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.invoices.list, { params });
    return response.data;
  },

  async show(id: string | number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.invoices.show(id));
    return response.data;
  },

  async download(id: string | number): Promise<Blob> {
    const response = await apiClient.get(API_ENDPOINTS.invoices.download(id), { responseType: 'blob' });
    return response.data as Blob;
  },
};

/**
 * Loyalty Service
 */
export const loyaltyService = {
  async summary(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.index);
    return response.data;
  },
  async tiers(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.tiers);
    return response.data;
  },
  async transactions(params?: any): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.transactions, { params });
    return response.data;
  },
  async redeem(data: { reward_code?: string; points?: number }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.loyalty.redeem, data);
    return response.data;
  },
  async calculateDiscount(data: { booking_total: number }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.loyalty.calculateDiscount, data);
    return response.data;
  },
  async leaderboard(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.leaderboard);
    return response.data;
  },
  async claimBirthday(): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.loyalty.claimBirthday, {});
    return response.data;
  },
  async expiringPoints(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.expiringPoints);
    return response.data;
  },
  async tierBenefits(tierId: number | string): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.loyalty.tierBenefits(tierId));
    return response.data;
  },
};

/**
 * Referral Service
 */
export const referralService = {
  async list(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.referrals.list);
    return response.data;
  },
  async code(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.referrals.code);
    return response.data;
  },
  async regenerate(): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.referrals.regenerate, {});
    return response.data;
  },
  async create(data: { email: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.referrals.create, data);
    return response.data;
  },
  async discount(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.referrals.discount);
    return response.data;
  },
  async applyDiscount(data: { code: string; booking_total: number }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.referrals.applyDiscount, data);
    return response.data;
  },
  async leaderboard(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.referrals.leaderboard);
    return response.data;
  },
  async validate(data: { code: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.referrals.validate, data);
    return response.data;
  },
  async programInfo(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.referrals.programInfo);
    return response.data;
  },
};

/**
 * Notifications Service
 */
export const notificationsService = {
  async list(params?: any): Promise<{ data: Notification[]; meta?: any }> {
    const response = await apiClient.get(API_ENDPOINTS.notifications.list, { params });
    return response.data;
  },

  async unreadCount(): Promise<{ count: number }> {
    const response = await apiClient.get(API_ENDPOINTS.notifications.unreadCount);
    return response.data;
  },

  async markAllAsRead(): Promise<void> {
    await apiClient.post(API_ENDPOINTS.notifications.markAllAsRead);
  },

  async markAsRead(id: string): Promise<void> {
    await apiClient.post(API_ENDPOINTS.notifications.markAsRead(id));
  },

  async delete(id: string): Promise<void> {
    await apiClient.delete(API_ENDPOINTS.notifications.delete(id));
  },

  async getPreferences(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.notifications.getPreferences);
    return response.data;
  },

  async updatePreferences(data: NotificationPreferences): Promise<any> {
    const response = await apiClient.put(API_ENDPOINTS.notifications.updatePreferences, data);
    return response.data;
  },
};

/**
 * Settings Service (Admin)
 */
export const settingsService = {
  async list(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.adminSettings.list);
    return response.data;
  },

  async update(data: any): Promise<any> {
    const response = await apiClient.put(API_ENDPOINTS.adminSettings.update, data);
    return response.data;
  },

  async public(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.adminSettings.public);
    return response.data;
  },
};

/**
 * KYC & Verification Service
 */
export const verificationService = {
  async getStatus(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.verification.getStatus);
    return response.data;
  },

  async uploadGovernmentId(data: FormData): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.verification.uploadGovernmentId, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async getVerificationStatus(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.verification.getVerificationStatus);
    return response.data;
  },

  // Guest Verification
  guestVerification: {
    async getStatus(): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.verification.guestVerification.getStatus);
      return response.data;
    },

    async submitIdentity(data: FormData): Promise<any> {
      const response = await apiClient.post(
        API_ENDPOINTS.verification.guestVerification.submitIdentity,
        data,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      return response.data;
    },

    async uploadSelfie(data: FormData): Promise<any> {
      const response = await apiClient.post(
        API_ENDPOINTS.verification.guestVerification.uploadSelfie,
        data,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      return response.data;
    },

    async requestCreditCheck(): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.verification.guestVerification.requestCreditCheck);
      return response.data;
    },

    async getReferences(): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.verification.guestVerification.getReferences);
      return response.data;
    },
  },

  // User Verification
  userVerification: {
    async getStatus(): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.verification.userVerification.getStatus);
      return response.data;
    },

    async uploadId(data: FormData): Promise<any> {
      const response = await apiClient.post(
        API_ENDPOINTS.verification.userVerification.uploadId,
        data,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      return response.data;
    },

    async uploadAddress(data: FormData): Promise<any> {
      const response = await apiClient.post(
        API_ENDPOINTS.verification.userVerification.uploadAddress,
        data,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      return response.data;
    },

    async verifyPhone(data: { code: string }): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.verification.userVerification.verifyPhone, data);
      return response.data;
    },

    async sendPhoneVerification(data: { phone: string }): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.verification.userVerification.sendPhoneVerification, data);
      return response.data;
    },

    async requestBackgroundCheck(): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.verification.userVerification.backgroundCheck);
      return response.data;
    },
  },
};

/**
 * Smart Locks & Access Codes Service
 */
export const smartLocksService = {
  async list(propertyId: number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.smartLocks.list(propertyId));
    return response.data;
  },

  async create(propertyId: number, data: any): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.smartLocks.create(propertyId), data);
    return response.data;
  },

  async update(propertyId: number, lockId: number, data: any): Promise<any> {
    const response = await apiClient.put(API_ENDPOINTS.smartLocks.update(propertyId, lockId), data);
    return response.data;
  },

  async delete(propertyId: number, lockId: number): Promise<void> {
    await apiClient.delete(API_ENDPOINTS.smartLocks.delete(propertyId, lockId));
  },

  async sync(propertyId: number, lockId: number): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.smartLocks.sync(propertyId, lockId));
    return response.data;
  },

  // Access Codes
  accessCodes: {
    async list(lockId: number): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.smartLocks.accessCodes.list(lockId));
      return response.data;
    },

    async create(lockId: number, data: any): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.smartLocks.accessCodes.create(lockId), data);
      return response.data;
    },

    async get(lockId: number, codeId: number): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.smartLocks.accessCodes.get(lockId, codeId));
      return response.data;
    },

    async delete(lockId: number, codeId: number): Promise<void> {
      await apiClient.delete(API_ENDPOINTS.smartLocks.accessCodes.delete(lockId, codeId));
    },

    async generateForBooking(bookingId: number): Promise<any> {
      const response = await apiClient.post(API_ENDPOINTS.smartLocks.accessCodes.generate(bookingId));
      return response.data;
    },

    async getByBooking(bookingId: number): Promise<any> {
      const response = await apiClient.get(API_ENDPOINTS.smartLocks.accessCodes.getByBooking(bookingId));
      return response.data;
    },
  },
};

/**
 * Two-Factor Authentication Service
 */
export const twoFactorService = {
  async getStatus(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.twoFactor.status);
    return response.data;
  },

  async enable(): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.enable);
    return response.data;
  },

  async confirm(data: { code: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.confirm, data);
    return response.data;
  },

  async disable(data?: { password?: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.disable, data);
    return response.data;
  },

  async getRecoveryCodes(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.twoFactor.recoveryCodes);
    return response.data;
  },

  async sendCode(data: { email: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.sendCode, data);
    return response.data;
  },

  async verify(data: { email: string; code: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.verify, data);
    return response.data;
  },

  async verifyRecovery(data: { email: string; recovery_code: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.twoFactor.verifyRecovery, data);
    return response.data;
  },
};

/**
 * GDPR & Privacy Service
 */
export const gdprService = {
  async exportData(): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.gdpr.export);
    return response.data;
  },

  async forgetMe(): Promise<any> {
    const response = await apiClient.delete(API_ENDPOINTS.gdpr.forgetMe);
    return response.data;
  },

  async getConsent(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.gdpr.getConsent);
    return response.data;
  },

  async updateConsent(data: any): Promise<any> {
    const response = await apiClient.put(API_ENDPOINTS.gdpr.updateConsent, data);
    return response.data;
  },

  async getDataProtection(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.gdpr.dataProtection);
    return response.data;
  },
};

/**
 * Credit Check Service
 */
export const creditCheckService = {
  async request(): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.creditCheck.request);
    return response.data;
  },

  async getStatus(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.creditCheck.getStatus);
    return response.data;
  },

  async getReport(id: number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.creditCheck.getReport(id));
    return response.data;
  },
};

/**
 * Guest Screening Service
 */
export const guestScreeningService = {
  async list(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.guestScreening.list);
    return response.data;
  },

  async submit(data: any): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.guestScreening.submit, data);
    return response.data;
  },

  async getStatus(): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.guestScreening.getStatus);
    return response.data;
  },

  async getReport(id: number): Promise<any> {
    const response = await apiClient.get(API_ENDPOINTS.guestScreening.getReport(id));
    return response.data;
  },

  async approve(id: number, data: { notes?: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.guestScreening.approve(id), data);
    return response.data;
  },

  async reject(id: number, data: { notes?: string }): Promise<any> {
    const response = await apiClient.post(API_ENDPOINTS.guestScreening.reject(id), data);
    return response.data;
  },
};

// Export all services as a single object for convenience
export const api = {
  auth: authService,
  profile: profileService,
  properties: propertiesService,
  bookings: bookingsService,
  payments: paymentsService,
  invoices: invoicesService,
  notifications: notificationsService,
  settings: settingsService,
  verification: verificationService,
  smartLocks: smartLocksService,
  twoFactor: twoFactorService,
  gdpr: gdprService,
  creditCheck: creditCheckService,
  guestScreening: guestScreeningService,
};

export default api;
