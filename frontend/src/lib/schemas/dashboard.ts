import { z } from 'zod';

export const DashboardStatsSchema = z.object({
  properties: z.number(),
  bookingsUpcoming: z.number(),
  revenueLast30: z.number(),
  guestsUnique: z.number(),
});
export type DashboardStats = z.infer<typeof DashboardStatsSchema>;
