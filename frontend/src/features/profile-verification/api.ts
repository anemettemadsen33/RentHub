import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  UserVerificationSchema,
  VerificationStatusSchema,
  type UserVerification,
  type VerificationStatus,
} from '@/lib/schemas/verification';

export const getMyVerification = async (): Promise<UserVerification> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/my-verification');
  return await parse(UserVerificationSchema, res.data);
};

export const getVerificationStatus = async (): Promise<VerificationStatus> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/verification-status');
  return await parse(VerificationStatusSchema, res.data);
};

export const submitIdVerification = async (formData: FormData): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/user-verifications/id', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
};

export const submitAddressVerification = async (
  formData: FormData
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/user-verifications/address', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
};

export const sendPhoneVerification = async (phone: string): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/user-verifications/phone/send', { phone });
};

export const verifyPhoneCode = async (code: string): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/user-verifications/phone/verify', { code });
};

export const requestBackgroundCheck = async (): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/user-verifications/background-check');
};
