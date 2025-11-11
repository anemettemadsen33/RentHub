/**
 * Zod Validation Schemas for RentHub
 * 
 * Centralized validation schemas using Zod for type-safe form validation
 * Used with React Hook Form for robust form handling
 */

import { z } from 'zod';

// ============================================================================
// AUTHENTICATION SCHEMAS
// ============================================================================

export const loginSchema = z.object({
  email: z
    .string()
    .min(1, 'Email is required')
    .email('Invalid email address'),
  password: z
    .string()
    .min(1, 'Password is required')
    .min(8, 'Password must be at least 8 characters'),
});

export const registerSchema = z.object({
  name: z
    .string()
    .min(1, 'Name is required')
    .min(2, 'Name must be at least 2 characters')
    .max(100, 'Name must be less than 100 characters'),
  email: z
    .string()
    .min(1, 'Email is required')
    .email('Invalid email address'),
  password: z
    .string()
    .min(1, 'Password is required')
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
    .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
    .regex(/[0-9]/, 'Password must contain at least one number'),
  passwordConfirmation: z
    .string()
    .min(1, 'Please confirm your password'),
  role: z.enum(['tenant', 'owner']).optional(),
}).refine((data) => data.password === data.passwordConfirmation, {
  message: "Passwords don't match",
  path: ['passwordConfirmation'],
});

export const forgotPasswordSchema = z.object({
  email: z
    .string()
    .min(1, 'Email is required')
    .email('Invalid email address'),
});

export const resetPasswordSchema = z.object({
  password: z
    .string()
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
    .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
    .regex(/[0-9]/, 'Password must contain at least one number'),
  passwordConfirmation: z
    .string()
    .min(1, 'Please confirm your password'),
}).refine((data) => data.password === data.passwordConfirmation, {
  message: "Passwords don't match",
  path: ['passwordConfirmation'],
});

// ============================================================================
// PROFILE SCHEMAS
// ============================================================================

export const profileBasicInfoSchema = z.object({
  name: z
    .string()
    .min(2, 'Name must be at least 2 characters')
    .max(100, 'Name must be less than 100 characters'),
  email: z
    .string()
    .email('Invalid email address'),
  phone: z
    .string()
    .regex(/^\+?[1-9]\d{1,14}$/, 'Invalid phone number')
    .optional()
    .or(z.literal('')),
  bio: z
    .string()
    .max(500, 'Bio must be less than 500 characters')
    .optional(),
});

export const profileAddressSchema = z.object({
  address: z.string().min(5, 'Address must be at least 5 characters'),
  city: z.string().min(2, 'City must be at least 2 characters'),
  state: z.string().min(2, 'State must be at least 2 characters'),
  country: z.string().min(2, 'Country must be at least 2 characters'),
  zipCode: z.string().min(3, 'ZIP code must be at least 3 characters'),
});

export const profilePreferencesSchema = z.object({
  language: z.enum(['en', 'ro', 'fr', 'de', 'es']),
  currency: z.enum(['USD', 'EUR', 'GBP', 'RON']),
  timezone: z.string().min(1, 'Timezone is required'),
  emailNotifications: z.boolean(),
  smsNotifications: z.boolean(),
  pushNotifications: z.boolean(),
});

// ============================================================================
// PROPERTY SCHEMAS
// ============================================================================

export const propertyBasicSchema = z.object({
  title: z
    .string()
    .min(10, 'Title must be at least 10 characters')
    .max(100, 'Title must be less than 100 characters'),
  description: z
    .string()
    .min(50, 'Description must be at least 50 characters')
    .max(2000, 'Description must be less than 2000 characters'),
  propertyType: z.enum(['apartment', 'house', 'villa', 'studio', 'condo', 'townhouse']),
  maxGuests: z
    .number()
    .min(1, 'Must accommodate at least 1 guest')
    .max(50, 'Maximum 50 guests'),
  bedrooms: z
    .number()
    .min(0, 'Cannot be negative')
    .max(20, 'Maximum 20 bedrooms'),
  bathrooms: z
    .number()
    .min(1, 'Must have at least 1 bathroom')
    .max(20, 'Maximum 20 bathrooms'),
  pricePerNight: z
    .number()
    .min(10, 'Price must be at least $10')
    .max(10000, 'Price must be less than $10,000'),
});

export const propertyLocationSchema = z.object({
  address: z.string().min(5, 'Address is required'),
  city: z.string().min(2, 'City is required'),
  state: z.string().min(2, 'State is required'),
  country: z.string().min(2, 'Country is required'),
  zipCode: z.string().min(3, 'ZIP code is required'),
  latitude: z
    .number()
    .min(-90, 'Invalid latitude')
    .max(90, 'Invalid latitude')
    .optional(),
  longitude: z
    .number()
    .min(-180, 'Invalid longitude')
    .max(180, 'Invalid longitude')
    .optional(),
});

export const propertyAmenitiesSchema = z.object({
  amenities: z
    .array(z.string())
    .min(1, 'Select at least one amenity'),
  houseRules: z
    .string()
    .max(1000, 'House rules must be less than 1000 characters')
    .optional(),
  checkInTime: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/, 'Invalid time format'),
  checkOutTime: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/, 'Invalid time format'),
});

// ============================================================================
// BOOKING SCHEMAS
// ============================================================================

export const bookingSchema = z.object({
  propertyId: z.number().positive('Property ID is required'),
  checkIn: z.string().min(1, 'Check-in date is required'),
  checkOut: z.string().min(1, 'Check-out date is required'),
  guests: z
    .number()
    .min(1, 'At least 1 guest required')
    .max(50, 'Maximum 50 guests'),
  specialRequests: z
    .string()
    .max(500, 'Special requests must be less than 500 characters')
    .optional(),
}).refine((data) => new Date(data.checkOut) > new Date(data.checkIn), {
  message: 'Check-out must be after check-in',
  path: ['checkOut'],
});

// ============================================================================
// REVIEW SCHEMAS
// ============================================================================

export const reviewSchema = z.object({
  propertyId: z.number().positive('Property ID is required'),
  rating: z
    .number()
    .min(1, 'Rating must be at least 1 star')
    .max(5, 'Rating must be at most 5 stars'),
  title: z
    .string()
    .min(5, 'Title must be at least 5 characters')
    .max(100, 'Title must be less than 100 characters'),
  comment: z
    .string()
    .min(20, 'Comment must be at least 20 characters')
    .max(1000, 'Comment must be less than 1000 characters'),
  cleanlinessRating: z.number().min(1).max(5).optional(),
  accuracyRating: z.number().min(1).max(5).optional(),
  communicationRating: z.number().min(1).max(5).optional(),
  locationRating: z.number().min(1).max(5).optional(),
  valueRating: z.number().min(1).max(5).optional(),
});

// ============================================================================
// SETTINGS SCHEMAS
// ============================================================================

export const changePasswordSchema = z.object({
  currentPassword: z.string().min(1, 'Current password is required'),
  newPassword: z
    .string()
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Must contain uppercase letter')
    .regex(/[a-z]/, 'Must contain lowercase letter')
    .regex(/[0-9]/, 'Must contain number'),
  confirmPassword: z.string().min(1, 'Please confirm your password'),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: "Passwords don't match",
  path: ['confirmPassword'],
}).refine((data) => data.newPassword !== data.currentPassword, {
  message: "New password must be different from current password",
  path: ['newPassword'],
});

export const twoFactorSetupSchema = z.object({
  code: z
    .string()
    .length(6, 'Code must be 6 digits')
    .regex(/^\d+$/, 'Code must contain only numbers'),
});

export const notificationSettingsSchema = z.object({
  emailNotifications: z.boolean(),
  smsNotifications: z.boolean(),
  pushNotifications: z.boolean(),
  bookingUpdates: z.boolean(),
  messageNotifications: z.boolean(),
  promotionalEmails: z.boolean(),
  weeklyDigest: z.boolean(),
});

// ============================================================================
// PAYMENT SCHEMAS
// ============================================================================

export const paymentMethodSchema = z.object({
  cardNumber: z
    .string()
    .regex(/^\d{16}$/, 'Card number must be 16 digits'),
  cardholderName: z
    .string()
    .min(3, 'Cardholder name is required'),
  expiryMonth: z
    .number()
    .min(1, 'Invalid month')
    .max(12, 'Invalid month'),
  expiryYear: z
    .number()
    .min(new Date().getFullYear(), 'Card is expired'),
  cvv: z
    .string()
    .regex(/^\d{3,4}$/, 'CVV must be 3 or 4 digits'),
  billingZip: z
    .string()
    .min(3, 'Billing ZIP code is required'),
});

// ============================================================================
// TYPE EXPORTS
// ============================================================================

export type LoginFormData = z.infer<typeof loginSchema>;
export type RegisterFormData = z.infer<typeof registerSchema>;
export type ForgotPasswordFormData = z.infer<typeof forgotPasswordSchema>;
export type ResetPasswordFormData = z.infer<typeof resetPasswordSchema>;

export type ProfileBasicInfoFormData = z.infer<typeof profileBasicInfoSchema>;
export type ProfileAddressFormData = z.infer<typeof profileAddressSchema>;
export type ProfilePreferencesFormData = z.infer<typeof profilePreferencesSchema>;

export type PropertyBasicFormData = z.infer<typeof propertyBasicSchema>;
export type PropertyLocationFormData = z.infer<typeof propertyLocationSchema>;
export type PropertyAmenitiesFormData = z.infer<typeof propertyAmenitiesSchema>;

export type BookingFormData = z.infer<typeof bookingSchema>;
export type ReviewFormData = z.infer<typeof reviewSchema>;

export type ChangePasswordFormData = z.infer<typeof changePasswordSchema>;
export type TwoFactorSetupFormData = z.infer<typeof twoFactorSetupSchema>;
export type NotificationSettingsFormData = z.infer<typeof notificationSettingsSchema>;

export type PaymentMethodFormData = z.infer<typeof paymentMethodSchema>;
