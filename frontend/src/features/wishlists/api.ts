import apiClient, { ensureCsrfCookie, parse } from '@/lib/api-client';
import { z } from 'zod';
import { WishlistListSchema, WishlistSchema, type Wishlist } from '@/lib/schemas';

export async function listWishlists(): Promise<Wishlist[]> {
  await ensureCsrfCookie();
  const res = await apiClient.get('/wishlists');
  const items = await parse(WishlistListSchema, res.data);
  return items as Wishlist[];
}

export async function createWishlist(name: string): Promise<Wishlist> {
  await ensureCsrfCookie();
  const res = await apiClient.post('/wishlists', { name });
  const data = await parse(WishlistSchema, res.data) as any;
  return { ...data, items: Array.isArray(data.items) ? data.items : [] } as Wishlist;
}

export async function renameWishlist(id: number, name: string): Promise<Wishlist> {
  await ensureCsrfCookie();
  const res = await apiClient.put(`/wishlists/${id}`, { name });
  const data = await parse(WishlistSchema, res.data) as any;
  return { ...data, items: Array.isArray(data.items) ? data.items : [] } as Wishlist;
}

export async function deleteWishlist(id: number): Promise<void> {
  await ensureCsrfCookie();
  await apiClient.delete(`/wishlists/${id}`);
}

export async function addPropertyToWishlist(id: number, propertyId: number): Promise<Wishlist> {
  await ensureCsrfCookie();
  const res = await apiClient.post(`/wishlists/${id}/items`, { propertyId });
  const data = await parse(WishlistSchema, res.data) as any;
  return { ...data, items: Array.isArray(data.items) ? data.items : [] } as Wishlist;
}

export async function removePropertyFromWishlist(wishlistId: number, itemId: number): Promise<Wishlist> {
  await ensureCsrfCookie();
  const res = await apiClient.delete(`/wishlists/${wishlistId}/items/${itemId}`);
  const data = await parse(WishlistSchema, res.data) as any;
  return { ...data, items: Array.isArray(data.items) ? data.items : [] } as Wishlist;
}

export async function getWishlist(id: number): Promise<Wishlist> {
  await ensureCsrfCookie();
  const res = await apiClient.get(`/wishlists/${id}`);
  const data = await parse(WishlistSchema, res.data) as any;
  return { ...data, items: Array.isArray(data.items) ? data.items : [] } as Wishlist;
}
