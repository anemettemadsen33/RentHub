import { z } from 'zod';

export const VerificationStepSchema = z.object({
  type: z.enum(['identity', 'address', 'selfie', 'background', 'phone']),
  status: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  submittedAt: z.string().optional(),
  approvedAt: z.string().optional(),
  rejectedAt: z.string().optional(),
  rejectionReason: z.string().optional(),
});
export type VerificationStep = z.infer<typeof VerificationStepSchema>;

export const UserVerificationSchema = z.object({
  id: z.number(),
  userId: z.number().optional(),
  identityStatus: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  addressStatus: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  phoneStatus: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  backgroundCheckStatus: z.enum(['pending', 'completed', 'not_started']).optional(),
  trustScore: z.number().optional(),
  steps: z.array(VerificationStepSchema).optional(),
  createdAt: z.string().optional(),
  updatedAt: z.string().optional(),
});
export type UserVerification = z.infer<typeof UserVerificationSchema>;

export const VerificationStatusSchema = z.object({
  overall: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  identity: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  address: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  phone: z.enum(['pending', 'verified', 'rejected', 'not_started']).optional(),
  background: z.enum(['pending', 'completed', 'not_started']).optional(),
  trustScore: z.number().optional(),
});
export type VerificationStatus = z.infer<typeof VerificationStatusSchema>;
