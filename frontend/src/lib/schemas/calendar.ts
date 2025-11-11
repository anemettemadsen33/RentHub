import { z } from 'zod';

export const CalendarDaySchema = z.object({
  date: z.string(),
  available: z.boolean().optional(),
  booked: z.boolean().optional(),
  blocked: z.boolean().optional(),
  price: z.number().optional(),
  reason: z.string().optional(),
});
export type CalendarDay = z.infer<typeof CalendarDaySchema>;

export const BlockedDateSchema = z.object({
  id: z.number(),
  propertyId: z.number().optional(),
  startDate: z.string(),
  endDate: z.string(),
  reason: z.string().optional(),
  createdAt: z.string().optional(),
});
export type BlockedDate = z.infer<typeof BlockedDateSchema>;

export const PricingEntrySchema = z.object({
  date: z.string(),
  price: z.number(),
  isWeekend: z.boolean().optional(),
  isSpecial: z.boolean().optional(),
});
export type PricingEntry = z.infer<typeof PricingEntrySchema>;

export const CalendarDataSchema = z
  .union([
    z.object({ data: z.array(CalendarDaySchema) }),
    z.array(CalendarDaySchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const BlockedDatesListSchema = z
  .union([
    z.object({ data: z.array(BlockedDateSchema) }),
    z.array(BlockedDateSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));

export const PricingListSchema = z
  .union([
    z.object({ data: z.array(PricingEntrySchema) }),
    z.array(PricingEntrySchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.data));
