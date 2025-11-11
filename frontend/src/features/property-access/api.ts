import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  SmartLockListSchema,
  AccessCodeListSchema,
  ActivityListSchema,
  SmartLockSchema,
  AccessCodeSchema,
  type SmartLock,
  type AccessCode,
  type LockActivity,
} from '@/lib/schemas/smart-lock';

// Property-scoped Smart Locks (within properties/{id} group)
export const getPropertySmartLocks = async (
  propertyId: number
): Promise<SmartLock[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/properties/${propertyId}/smart-locks`);
  const result = await parse(SmartLockListSchema, res.data);
  return result as SmartLock[];
};

export const createSmartLock = async (
  propertyId: number,
  data: { name: string }
): Promise<SmartLock> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/properties/${propertyId}/smart-locks`, data);
  return await parse(SmartLockSchema, res.data);
};

export const getSmartLock = async (
  propertyId: number,
  lockId: number
): Promise<SmartLock> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/properties/${propertyId}/smart-locks/${lockId}`);
  return await parse(SmartLockSchema, res.data);
};

export const updateSmartLock = async (
  propertyId: number,
  lockId: number,
  data: Partial<SmartLock>
): Promise<SmartLock> => {
  await ensureCsrfCookie();
  const res = await apiClient.put(
    `/properties/${propertyId}/smart-locks/${lockId}`,
    data
  );
  return await parse(SmartLockSchema, res.data);
};

export const deleteSmartLock = async (
  propertyId: number,
  lockId: number
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.delete(`/properties/${propertyId}/smart-locks/${lockId}`);
};

export const lockDoor = async (propertyId: number, lockId: number): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/smart-locks/${lockId}/lock`);
};

export const unlockDoor = async (
  propertyId: number,
  lockId: number
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/smart-locks/${lockId}/unlock`);
};

export const getLockActivities = async (
  propertyId: number,
  lockId: number
): Promise<LockActivity[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(
    `/properties/${propertyId}/smart-locks/${lockId}/activities`
  );
  const result = await parse(ActivityListSchema, res.data);
  return result as LockActivity[];
};

// Access Codes
export const getAccessCodes = async (
  propertyId: number,
  lockId: number
): Promise<AccessCode[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(
    `/properties/${propertyId}/smart-locks/${lockId}/access-codes`
  );
  const result = await parse(AccessCodeListSchema, res.data);
  return result as AccessCode[];
};

export const createAccessCode = async (
  propertyId: number,
  lockId: number,
  data: {
    code: string;
    type?: 'one_time' | 'recurring';
    validFrom?: string;
    validUntil?: string;
  }
): Promise<AccessCode> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(
    `/properties/${propertyId}/smart-locks/${lockId}/access-codes`,
    data
  );
  return await parse(AccessCodeSchema, res.data);
};

export const revokeAccessCode = async (
  propertyId: number,
  lockId: number,
  codeId: number
): Promise<void> => {
  await ensureCsrfCookie();
  // Assuming DELETE or PATCH to revoke
  await apiClient.delete(
    `/properties/${propertyId}/smart-locks/${lockId}/access-codes/${codeId}`
  );
};
