import { z } from 'zod';

export const SmartLockSchema = z.object({
  id: z.number(),
  propertyId: z.number().optional(),
  name: z.string(),
  status: z.enum(['online', 'offline']).optional(),
  batteryLevel: z.number().optional(),
  isLocked: z.boolean().optional(),
  lastSyncAt: z.string().optional(),
  createdAt: z.string().optional(),
});
export type SmartLock = z.infer<typeof SmartLockSchema>;

export const AccessCodeSchema = z.object({
  id: z.number(),
  lockId: z.number().optional(),
  code: z.string(),
  type: z.enum(['one_time', 'recurring']).optional(),
  status: z.enum(['active', 'expired', 'revoked']).optional(),
  validFrom: z.string().optional(),
  validUntil: z.string().optional(),
  createdAt: z.string().optional(),
});
export type AccessCode = z.infer<typeof AccessCodeSchema>;

export const LockActivitySchema = z.object({
  id: z.number(),
  lockId: z.number().optional(),
  action: z.enum(['locked', 'unlocked', 'code_used', 'battery_low', 'offline']).optional(),
  timestamp: z.string().optional(),
  userId: z.number().optional(),
  codeId: z.number().optional(),
  description: z.string().optional(),
});
export type LockActivity = z.infer<typeof LockActivitySchema>;

export const SmartLockListSchema = z
  .union([
    z.object({ data: z.array(SmartLockSchema) }),
    z.array(SmartLockSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const AccessCodeListSchema = z
  .union([
    z.object({ data: z.array(AccessCodeSchema) }),
    z.array(AccessCodeSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const ActivityListSchema = z
  .union([
    z.object({ data: z.array(LockActivitySchema) }),
    z.array(LockActivitySchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
