import { z } from 'zod';

export const InsurancePlanSchema = z.object({
  id: z.number(),
  name: z.string(),
  type: z.enum(['basic', 'premium', 'comprehensive']).optional(),
  price: z.number(),
  currency: z.string().optional(),
  description: z.string().optional(),
  coverages: z.array(z.string()).optional(),
});
export type InsurancePlan = z.infer<typeof InsurancePlanSchema>;

export const InsuranceSchema = z.object({
  id: z.number(),
  bookingId: z.number().optional(),
  planId: z.number().optional(),
  planName: z.string().optional(),
  status: z.enum(['active', 'pending', 'cancelled', 'expired']).optional(),
  startDate: z.string().optional(),
  endDate: z.string().optional(),
  price: z.number().optional(),
  currency: z.string().optional(),
  createdAt: z.string().optional(),
});
export type Insurance = z.infer<typeof InsuranceSchema>;

export const ClaimSchema = z.object({
  id: z.number(),
  insuranceId: z.number().optional(),
  status: z.enum(['pending', 'approved', 'rejected']).optional(),
  description: z.string().optional(),
  amount: z.number().optional(),
  submittedAt: z.string().optional(),
  resolvedAt: z.string().optional(),
});
export type Claim = z.infer<typeof ClaimSchema>;

export const InsuranceListSchema = z
  .union([
    z.object({ data: z.array(InsuranceSchema) }),
    z.array(InsuranceSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const PlanListSchema = z
  .union([
    z.object({ data: z.array(InsurancePlanSchema) }),
    z.array(InsurancePlanSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const ClaimListSchema = z
  .union([
    z.object({ data: z.array(ClaimSchema) }),
    z.array(ClaimSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
