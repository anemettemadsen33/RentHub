import { z } from 'zod';

export const WishlistItemSchema = z.object({
  id: z.number(),
  propertyId: z.number(),
  title: z.string().optional(),
  imageUrl: z.string().url().optional(),
  addedAt: z.string().optional(),
});
export type WishlistItem = z.infer<typeof WishlistItemSchema>;

export const WishlistSchema = z.object({
  id: z.number(),
  name: z.string(),
  // Use transform to guarantee an array type (never undefined)
  items: z
    .array(WishlistItemSchema)
    .optional()
    .transform((val) => (Array.isArray(val) ? val : [])),
  createdAt: z.string().optional(),
  updatedAt: z.string().optional(),
}).transform((obj) => ({ ...obj, items: Array.isArray(obj.items) ? obj.items : [] }));
export type Wishlist = z.infer<typeof WishlistSchema>;

export const WishlistListSchema = z
  .union([
    z.object({ items: z.array(WishlistSchema) }),
    z.array(WishlistSchema),
  ])
  .transform((val) => (Array.isArray(val) ? val : val.items));
export type WishlistList = z.infer<typeof WishlistListSchema>;
