import { z } from 'zod';

export const ComparisonPropertySchema = z.object({
  id: z.number(),
  title: z.string(),
  pricePerNight: z.number(),
  location: z.string().optional(),
});
export type ComparisonProperty = z.infer<typeof ComparisonPropertySchema>;
