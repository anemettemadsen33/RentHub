import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  PlanListSchema,
  ClaimListSchema,
  InsuranceSchema,
  ClaimSchema,
  type InsurancePlan,
  type Claim,
  type Insurance,
} from '@/lib/schemas/insurance';

export const getAvailablePlans = async (
  bookingId?: number
): Promise<InsurancePlan[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.post('/insurance/plans/available', {
    booking_id: bookingId,
  });
  const result = await parse(PlanListSchema, res.data);
  return result as InsurancePlan[];
};

export const addInsuranceToBooking = async (
  bookingId: number,
  planId: number
): Promise<Insurance> => {
  await ensureCsrfCookie();
  const res = await apiClient.post('/insurance/add-to-booking', {
    booking_id: bookingId,
    plan_id: planId,
  });
  return await parse(InsuranceSchema, res.data);
};

export const getBookingInsurances = async (
  bookingId: number
): Promise<Insurance[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/insurance/booking/${bookingId}`);
  return (await parse(
    PlanListSchema,
    Array.isArray(res.data) ? res.data : res.data.data || []
  )) as any;
};

export const activateInsurance = async (insuranceId: number): Promise<Insurance> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/insurance/${insuranceId}/activate`);
  return await parse(InsuranceSchema, res.data);
};

export const cancelInsurance = async (insuranceId: number): Promise<Insurance> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/insurance/${insuranceId}/cancel`);
  return await parse(InsuranceSchema, res.data);
};

export const submitClaim = async (claimData: {
  insuranceId: number;
  description: string;
  amount?: number;
}): Promise<Claim> => {
  await ensureCsrfCookie();
  const res = await apiClient.post('/insurance/claims', {
    insurance_id: claimData.insuranceId,
    description: claimData.description,
    amount: claimData.amount,
  });
  return await parse(ClaimSchema, res.data);
};

export const getUserClaims = async (): Promise<Claim[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/insurance/claims');
  const result = await parse(ClaimListSchema, res.data);
  return result as Claim[];
};
