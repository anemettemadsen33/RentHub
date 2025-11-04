import { apiClient } from './client';
import type {
  GuestVerification,
  IdentityVerificationForm,
  ReferenceForm,
  ReferenceVerificationForm,
  VerificationStatistics,
} from '@/types/guest-verification';

export const guestVerificationApi = {
  // Get current user's verification status
  async getStatus() {
    const response = await apiClient.get<{
      verification?: GuestVerification;
      can_book: boolean;
      is_fully_verified: boolean;
      status?: string;
      message?: string;
    }>('/guest-verification');
    return response.data;
  },

  // Submit identity verification documents
  async submitIdentity(data: IdentityVerificationForm) {
    const formData = new FormData();
    formData.append('document_type', data.document_type);
    formData.append('document_number', data.document_number);
    formData.append('document_front', data.document_front);
    if (data.document_back) {
      formData.append('document_back', data.document_back);
    }
    formData.append('selfie_photo', data.selfie_photo);
    formData.append('document_expiry_date', data.document_expiry_date);

    const response = await apiClient.post<{
      message: string;
      verification: GuestVerification;
    }>('/guest-verification/identity', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  // Add a reference
  async addReference(data: ReferenceForm) {
    const response = await apiClient.post<{
      message: string;
      reference: unknown;
    }>('/guest-verification/references', data);
    return response.data;
  },

  // Request credit check
  async requestCreditCheck() {
    const response = await apiClient.post<{
      message: string;
      verification: GuestVerification;
    }>('/guest-verification/credit-check');
    return response.data;
  },

  // Get verification statistics
  async getStatistics() {
    const response = await apiClient.get<VerificationStatistics>('/guest-verification/statistics');
    return response.data;
  },

  // Verify reference (public - uses token)
  async verifyReference(token: string, data: ReferenceVerificationForm) {
    const response = await apiClient.post<{
      message: string;
      reference: unknown;
    }>(`/guest-verification/references/${token}/verify`, data);
    return response.data;
  },
};
