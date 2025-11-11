import { z } from 'zod';

export const PaymentSchema = z.object({
  id: z.number(),
  amount: z.number(),
  currency: z.string().optional(),
  status: z.enum(['pending', 'completed', 'failed', 'refunded', 'cancelled']).optional(),
  method: z.string().optional(), // e.g., 'stripe', 'paypal', 'card'
  bookingId: z.number().optional(),
  userId: z.number().optional(),
  transactionId: z.string().optional(),
  description: z.string().optional(),
  createdAt: z.string().optional(),
  updatedAt: z.string().optional(),
});
export type Payment = z.infer<typeof PaymentSchema>;

export const PaymentListSchema = z
  .union([
    z.object({ data: z.array(PaymentSchema) }),
    z.array(PaymentSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
export type PaymentList = z.infer<typeof PaymentListSchema>;
