import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  PaymentListSchema,
  PaymentSchema,
  type Payment,
} from '@/lib/schemas/payment';

export const listPayments = async (): Promise<Payment[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/payments');
  const result = await parse(PaymentListSchema, res.data);
  return result as Payment[];
};

export const getPayment = async (id: number): Promise<Payment> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/payments/${id}`);
  return parse(PaymentSchema, res.data);
};

export const updatePaymentStatus = async (
  id: number,
  status: 'completed' | 'failed' | 'refunded' | 'cancelled'
): Promise<Payment> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/payments/${id}/status`, { status });
  return parse(PaymentSchema, res.data);
};

export const confirmPayment = async (
  id: number,
  transactionId?: string
): Promise<Payment> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/payments/${id}/confirm`, {
    transaction_id: transactionId,
  });
  return parse(PaymentSchema, res.data);
};

export const refundPayment = async (id: number): Promise<Payment> => {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/payments/${id}/refund`);
  return parse(PaymentSchema, res.data);
};
