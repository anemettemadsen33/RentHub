import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  InvoiceListSchema,
  InvoiceSchema,
  type Invoice,
} from '@/lib/schemas/invoice';

export const listInvoices = async (): Promise<Invoice[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/invoices');
  const result = await parse(InvoiceListSchema, res.data);
  return result as Invoice[];
};

export const getInvoice = async (id: number): Promise<Invoice> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/invoices/${id}`);
  return await parse(InvoiceSchema, res.data);
};

export const downloadInvoice = async (id: number): Promise<Blob> => {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/invoices/${id}/download`, {
    responseType: 'blob',
  });
  return res.data;
};

export const resendInvoice = async (id: number): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post(`/invoices/${id}/resend`);
};
