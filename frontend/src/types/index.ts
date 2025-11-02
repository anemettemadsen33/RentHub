export interface User {
  id: number
  name: string
  email: string
  role: 'guest' | 'owner' | 'admin'
  phone?: string
  bio?: string
  avatar?: string
  is_verified: boolean
  verified_at?: string
  created_at: string
  updated_at: string
}

export interface Amenity {
  id: number
  name: string
  slug: string
  description?: string
  icon?: string
  category: string
  is_popular: boolean
  sort_order: number
}

export interface Property {
  id: number
  title: string
  description: string
  type: string
  bedrooms: number
  bathrooms: number
  guests: number
  price_per_night: number
  cleaning_fee?: number
  security_deposit?: number
  street_address: string
  city: string
  state: string
  country: string
  postal_code: string
  latitude?: number
  longitude?: number
  area_sqm?: number
  built_year?: number
  is_active: boolean
  is_featured: boolean
  available_from?: string
  available_until?: string
  images?: string[]
  main_image?: string
  user_id: number
  user?: User
  amenities?: Amenity[]
  reviews_count?: number
  average_rating?: number
  created_at: string
  updated_at: string
}

export interface Booking {
  id: number
  property_id: number
  user_id: number
  check_in: string
  check_out: string
  guests: number
  nights: number
  price_per_night: number
  subtotal: number
  cleaning_fee: number
  security_deposit: number
  taxes: number
  total_amount: number
  status: 'pending' | 'confirmed' | 'checked_in' | 'checked_out' | 'cancelled' | 'completed'
  guest_name: string
  guest_email: string
  guest_phone?: string
  special_requests?: string
  payment_status: string
  payment_method?: string
  payment_transaction_id?: string
  paid_at?: string
  confirmed_at?: string
  cancelled_at?: string
  property?: Property
  user?: User
  created_at: string
  updated_at: string
}

export interface Review {
  id: number
  property_id: number
  user_id: number
  booking_id?: number
  rating: number
  comment?: string
  cleanliness_rating?: number
  communication_rating?: number
  check_in_rating?: number
  accuracy_rating?: number
  location_rating?: number
  value_rating?: number
  is_approved: boolean
  admin_notes?: string
  owner_response?: string
  owner_response_at?: string
  property?: Property
  user?: User
  booking?: Booking
  created_at: string
  updated_at: string
}

export interface SearchParams {
  location?: string
  check_in?: string
  check_out?: string
  guests?: number
  min_price?: number
  max_price?: number
  bedrooms?: number
  bathrooms?: number
  amenities?: number[]
  type?: string
  sort_by?: 'price' | 'rating' | 'created_at'
  sort_order?: 'asc' | 'desc'
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface ApiResponse<T> {
  success: boolean
  message?: string
  data: T
}