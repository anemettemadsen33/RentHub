import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  CalendarDataSchema,
  BlockedDatesListSchema,
  PricingListSchema,
  type CalendarDay,
  type BlockedDate,
  type PricingEntry,
} from '@/lib/schemas/calendar';

export const getCalendar = async (
  propertyId: number,
  startDate?: string,
  endDate?: string
): Promise<CalendarDay[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/properties/${propertyId}/calendar`, {
    params: { start_date: startDate, end_date: endDate },
  });
  const result = await parse(CalendarDataSchema, res.data);
  return result as CalendarDay[];
};

export const getPricingCalendar = async (
  propertyId: number,
  month?: string
): Promise<PricingEntry[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/properties/${propertyId}/calendar/pricing`, {
    params: { month },
  });
  const result = await parse(PricingListSchema, res.data);
  return result as PricingEntry[];
};

export const getBlockedDates = async (
  propertyId: number
): Promise<BlockedDate[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/properties/${propertyId}/calendar/blocked-dates`);
  const result = await parse(BlockedDatesListSchema, res.data);
  return result as BlockedDate[];
};

export const blockDates = async (
  propertyId: number,
  startDate: string,
  endDate: string,
  reason?: string
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/block-dates`, {
    start_date: startDate,
    end_date: endDate,
    reason,
  });
};

export const unblockDates = async (
  propertyId: number,
  startDate: string,
  endDate: string
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/unblock-dates`, {
    start_date: startDate,
    end_date: endDate,
  });
};

export const bulkBlockDates = async (
  propertyId: number,
  dates: { start_date: string; end_date: string; reason?: string }[]
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/calendar/bulk-block`, { dates });
};

export const bulkUnblockDates = async (
  propertyId: number,
  dates: { start_date: string; end_date: string }[]
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/calendar/bulk-unblock`, { dates });
};

export const setCustomPricing = async (
  propertyId: number,
  pricingData: { date: string; price: number }[]
): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/properties/${propertyId}/custom-pricing`, {
    pricing: pricingData,
  });
};
