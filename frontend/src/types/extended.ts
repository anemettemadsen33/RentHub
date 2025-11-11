import { Property, User } from './index';

export interface Review {
  id: number;
  property_id: number;
  user_id: number;
  booking_id: number;
  rating: number;
  cleanliness_rating: number;
  communication_rating: number;
  accuracy_rating: number;
  location_rating: number;
  value_rating: number;
  comment: string;
  images?: string[];
  user?: {
    id: number;
    name: string;
    avatar_url?: string;
  };
  host_response?: string;
  host_response_date?: string;
  helpful_count: number;
  created_at: string;
  updated_at: string;
}

export interface ReviewImage {
  id: number;
  review_id: number;
  url: string;
  mime_type?: string;
  created_at: string;
}

export interface ReviewResponse {
  id: number;
  review_id: number;
  host_id: number;
  body: string;
  created_at: string;
  updated_at: string;
}

export interface HostRatingSummary {
  host_id: number;
  overall: number;
  communication: number;
  cleanliness: number;
  accuracy: number;
  value: number;
  response_rate?: number;
  total_reviews: number;
}

export interface Message {
  id: number;
  conversation_id: number;
  sender_id: number;
  recipient_id: number;
  message: string;
  read: boolean;
  attachments?: Array<{
    id: number;
    filename: string;
    file_url: string;
    file_type: string;
    file_size: number;
  }>;
  created_at: string;
  // Frontend-only, optional fields for delivery state management
  client_id?: string; // temporary id for optimistic messages
  status?: 'pending' | 'sent' | 'read' | 'failed';
}

export interface Conversation {
  id: number;
  property_id: number;
  host_id: number;
  guest_id: number;
  last_message?: Message;
  unread_count: number;
  property?: Partial<Property>;
  other_user?: Partial<User> & { name: string; email: string };
  created_at: string;
  updated_at: string;
}

export type NotificationType =
  | 'booking_created'
  | 'booking_confirmed'
  | 'booking_cancelled'
  | 'payment_received'
  | 'maintenance'
  | 'message'
  | 'system';

export interface AppNotification {
  id: string | number;
  type: NotificationType;
  title: string;
  body: string;
  data?: Record<string, any>;
  is_read: boolean;
  created_at: string;
}

export interface Favorite {
  id: number;
  user_id: number;
  property_id: number;
  property?: Property;
  created_at: string;
}

// Loyalty Program Types
export interface LoyaltySummary {
  user_id: number;
  points_balance: number;
  lifetime_points: number;
  tier_id: number;
  tier_name: string;
  tier_points_required: number;
  next_tier_points_required?: number;
  progress_to_next_tier_percent?: number;
  birthday_claimed?: boolean;
  expiring_points?: Array<{ amount: number; expires_at: string }>;
}

export interface LoyaltyTier {
  id: number;
  name: string;
  description?: string;
  points_required: number;
  multiplier?: number; // e.g. earning multiplier
  benefits?: LoyaltyBenefit[];
}

export interface LoyaltyTransaction {
  id: number;
  user_id: number;
  type: 'earn' | 'redeem' | 'adjustment' | 'bonus';
  points: number;
  description?: string;
  metadata?: Record<string, any>;
  created_at: string;
}

export interface LoyaltyBenefit {
  id: number;
  tier_id: number;
  label: string;
  description?: string;
  created_at: string;
}

// Referral Program Types
export interface ReferralRecord {
  id: number;
  referrer_id: number;
  referred_user_id?: number;
  referral_code: string;
  status: 'pending' | 'converted' | 'expired';
  reward_points?: number;
  created_at: string;
}

export interface ReferralCodeInfo {
  code: string;
  uses: number;
  max_uses?: number;
  created_at: string;
}

export interface ReferralLeaderboardEntry {
  user_id: number;
  user_name?: string;
  total_referrals: number;
  converted_referrals: number;
  points_earned: number;
  rank: number;
}
