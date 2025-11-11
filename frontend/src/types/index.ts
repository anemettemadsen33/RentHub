export interface AmenityObject {
  id: number;
  name: string;
  icon?: string;
  category?: string;
  // pivot data from backend many-to-many relation; keep loose typing
  pivot?: Record<string, any>;
}

export interface Property {
  id: number;
  title: string;
  description: string;
  price: number;
  price_per_night: number;
  currency: string;
  type: 'apartment' | 'house' | 'villa' | 'studio';
  property_type?: string;
  bedrooms: number;
  bathrooms: number;
  max_guests: number;
  address: string;
  city: string;
  country: string;
  latitude?: number;
  longitude?: number;
  status: 'available' | 'booked' | 'maintenance';
  rating?: number;
  review_count?: number;
  image_url?: string;
  images?: string[];
  // Backend may return either an array of strings or objects with {id,name,icon,category,pivot}
  amenities?: (string | AmenityObject)[];
  house_rules?: string[];
  cancellation_policy?: string;
  check_in_time?: string;
  check_out_time?: string;
  minimum_stay?: number;
  maximum_stay?: number;
  instant_book?: boolean;
  created_at: string;
  updated_at: string;
}

export interface Booking {
  id: number;
  property_id: number;
  user_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  total_price: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  property?: Property;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  role?: string;
  phone?: string;
  address?: string;
  avatar_url?: string;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
