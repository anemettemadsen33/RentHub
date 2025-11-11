import { z } from 'zod';

export const AuditLogSchema = z.object({
  id: z.number(),
  userId: z.number().optional(),
  event: z.string(),
  description: z.string().optional(),
  ipAddress: z.string().optional(),
  userAgent: z.string().optional(),
  location: z.string().optional(),
  device: z.string().optional(),
  status: z.enum(['success', 'failed', 'blocked']).optional(),
  isAnomaly: z.boolean().optional(),
  anomalyType: z.string().optional(),
  timestamp: z.string().optional(),
  createdAt: z.string().optional(),
});
export type AuditLog = z.infer<typeof AuditLogSchema>;

export const AnomalySchema = z.object({
  id: z.number(),
  type: z.string(),
  description: z.string(),
  severity: z.enum(['low', 'medium', 'high']).optional(),
  detectedAt: z.string().optional(),
});
export type Anomaly = z.infer<typeof AnomalySchema>;

export const AuditLogListSchema = z
  .union([
    z.object({ data: z.array(AuditLogSchema) }),
    z.array(AuditLogSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const AnomalyListSchema = z
  .union([
    z.object({ data: z.array(AnomalySchema) }),
    z.array(AnomalySchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
