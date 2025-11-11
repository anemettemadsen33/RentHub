import { z } from 'zod';

export const ApiErrorSchema = z.object({
  message: z.string(),
  code: z.string().optional(),
  errors: z.record(z.any()).optional(),
});
export type ApiError = z.infer<typeof ApiErrorSchema>;
