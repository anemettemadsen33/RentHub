import api from './client';

export interface Wishlist {
  id: number;
  user_id: number;
  name: string;
  description?: string;
  is_public: boolean;
  share_token?: string;
  items_count?: number;
  created_at: string;
  updated_at: string;
  items?: WishlistItem[];
}

export interface WishlistItem {
  id: number;
  wishlist_id: number;
  property_id: number;
  notes?: string;
  price_alert?: number;
  notify_availability: boolean;
  created_at: string;
  updated_at: string;
  property?: any;
}

export interface CreateWishlistData {
  name: string;
  description?: string;
  is_public?: boolean;
}

export interface UpdateWishlistData {
  name?: string;
  description?: string;
  is_public?: boolean;
}

export interface AddPropertyData {
  property_id: number;
  notes?: string;
  price_alert?: number;
  notify_availability?: boolean;
}

export interface UpdateItemData {
  notes?: string;
  price_alert?: number;
  notify_availability?: boolean;
}

// Get all wishlists for the authenticated user
export const getWishlists = async (): Promise<Wishlist[]> => {
  const response = await api.get('/wishlists');
  return response.data.data;
};

// Get a specific wishlist
export const getWishlist = async (id: number): Promise<Wishlist> => {
  const response = await api.get(`/wishlists/${id}`);
  return response.data.data;
};

// Create a new wishlist
export const createWishlist = async (data: CreateWishlistData): Promise<Wishlist> => {
  const response = await api.post('/wishlists', data);
  return response.data.data;
};

// Update a wishlist
export const updateWishlist = async (id: number, data: UpdateWishlistData): Promise<Wishlist> => {
  const response = await api.put(`/wishlists/${id}`, data);
  return response.data.data;
};

// Delete a wishlist
export const deleteWishlist = async (id: number): Promise<void> => {
  await api.delete(`/wishlists/${id}`);
};

// Add property to wishlist
export const addPropertyToWishlist = async (
  wishlistId: number,
  data: AddPropertyData
): Promise<WishlistItem> => {
  const response = await api.post(`/wishlists/${wishlistId}/properties`, data);
  return response.data.data;
};

// Remove property from wishlist
export const removePropertyFromWishlist = async (
  wishlistId: number,
  itemId: number
): Promise<void> => {
  await api.delete(`/wishlists/${wishlistId}/items/${itemId}`);
};

// Update wishlist item
export const updateWishlistItem = async (
  wishlistId: number,
  itemId: number,
  data: UpdateItemData
): Promise<WishlistItem> => {
  const response = await api.put(`/wishlists/${wishlistId}/items/${itemId}`, data);
  return response.data.data;
};

// Toggle property in default wishlist (quick add/remove)
export const togglePropertyInWishlist = async (
  propertyId: number,
  wishlistId?: number
): Promise<{ action: 'added' | 'removed'; data?: WishlistItem }> => {
  const response = await api.post('/wishlists/toggle-property', {
    property_id: propertyId,
    wishlist_id: wishlistId,
  });
  return {
    action: response.data.action,
    data: response.data.data,
  };
};

// Check if property is in any wishlist
export const checkPropertyInWishlist = async (
  propertyId: number
): Promise<{ in_wishlist: boolean; wishlists: Wishlist[] }> => {
  const response = await api.get(`/wishlists/check/${propertyId}`);
  return {
    in_wishlist: response.data.in_wishlist,
    wishlists: response.data.wishlists,
  };
};

// Get shared wishlist
export const getSharedWishlist = async (token: string): Promise<Wishlist> => {
  const response = await api.get(`/wishlists/shared/${token}`);
  return response.data.data;
};
