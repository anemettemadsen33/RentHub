import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import {
  AuditLogListSchema,
  AnomalyListSchema,
  type AuditLog,
  type Anomaly,
} from '@/lib/schemas/security';

export const getAuditLogs = async (filters?: {
  event?: string;
  startDate?: string;
  endDate?: string;
}): Promise<AuditLog[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/security/audit-logs', { params: filters });
  const result = await parse(AuditLogListSchema, res.data);
  return result as AuditLog[];
};

export const getAnomalies = async (): Promise<Anomaly[]> => {
  await ensureCsrfCookie();
  const res = await apiClient.get('/security/anomalies');
  const result = await parse(AnomalyListSchema, res.data);
  return result as Anomaly[];
};

export const logSecurityEvent = async (eventData: {
  event: string;
  description?: string;
}): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.post('/security/log', eventData);
};

export const cleanupOldLogs = async (daysOld: number): Promise<void> => {
  await ensureCsrfCookie();
  await apiClient.delete('/security/cleanup', { params: { days: daysOld } });
};
