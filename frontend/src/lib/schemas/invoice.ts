import { z } from 'zod';

export const InvoiceSchema = z.object({
  id: z.number(),
  invoiceNumber: z.string().optional(),
  bookingId: z.number().optional(),
  propertyTitle: z.string().optional(),
  amount: z.number(),
  currency: z.string().optional(),
  status: z.enum(['paid', 'pending', 'overdue', 'cancelled']).optional(),
  issuedAt: z.string().optional(),
  dueAt: z.string().optional(),
  paidAt: z.string().optional(),
  downloadUrl: z.string().optional(),
  createdAt: z.string().optional(),
  updatedAt: z.string().optional(),
});
export type Invoice = z.infer<typeof InvoiceSchema>;

export const InvoiceListSchema = z
  .union([
    z.object({ data: z.array(InvoiceSchema) }),
    z.array(InvoiceSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
export type InvoiceList = z.infer<typeof InvoiceListSchema>;
