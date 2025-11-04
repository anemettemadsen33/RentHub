export type IdentityStatus = 'pending' | 'verified' | 'rejected' | 'expired';
export type CreditStatus = 'not_requested' | 'pending' | 'approved' | 'rejected';
export type BackgroundStatus = 'pending' | 'clear' | 'flagged';
export type ReferenceStatus = 'pending' | 'contacted' | 'verified' | 'failed';
export type ReferenceType = 'previous_landlord' | 'employer' | 'personal' | 'other';
export type DocumentType = 'passport' | 'drivers_license' | 'id_card' | 'national_id';

export interface GuestVerification {
  id: number;
  user_id: number;
  
  // Identity
  identity_status: IdentityStatus;
  document_type?: DocumentType;
  document_number?: string;
  document_front?: string;
  document_back?: string;
  selfie_photo?: string;
  document_expiry_date?: string;
  identity_verified_at?: string;
  identity_rejection_reason?: string;
  
  // Credit
  credit_check_enabled: boolean;
  credit_status: CreditStatus;
  credit_score?: number;
  credit_checked_at?: string;
  
  // Background
  background_status: BackgroundStatus;
  background_notes?: string;
  background_checked_at?: string;
  
  // References
  references?: any[];
  references_verified: number;
  
  // Trust Score
  trust_score: number;
  completed_bookings: number;
  cancelled_bookings: number;
  positive_reviews: number;
  negative_reviews: number;
  
  // Meta
  verified_by?: number;
  admin_notes?: string;
  created_at: string;
  updated_at: string;
}

export interface GuestReference {
  id: number;
  guest_verification_id: number;
  reference_name: string;
  reference_email: string;
  reference_phone?: string;
  reference_type: ReferenceType;
  relationship?: string;
  status: ReferenceStatus;
  rating?: number;
  comments?: string;
  verification_token?: string;
  contacted_at?: string;
  verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface VerificationStatistics {
  trust_score: number;
  completed_bookings: number;
  cancelled_bookings: number;
  positive_reviews: number;
  negative_reviews: number;
  verification_level: 'none' | 'basic' | 'verified' | 'full';
  identity_verified: boolean;
  background_clear: boolean;
  credit_approved: boolean;
  references_count: number;
}

export interface IdentityVerificationForm {
  document_type: DocumentType;
  document_number: string;
  document_front: File;
  document_back?: File;
  selfie_photo: File;
  document_expiry_date: string;
}

export interface ReferenceForm {
  reference_name: string;
  reference_email: string;
  reference_phone?: string;
  reference_type: ReferenceType;
  relationship?: string;
}

export interface ReferenceVerificationForm {
  rating: number;
  comments?: string;
}
