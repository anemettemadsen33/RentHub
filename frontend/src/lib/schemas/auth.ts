import { z } from 'zod';

export const AuthUserSchema = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string().email(),
  role: z.string().optional(),
});
export type AuthUser = z.infer<typeof AuthUserSchema>;
