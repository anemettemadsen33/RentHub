/**
 * API Endpoints Configuration
 * Centralized endpoint definitions for type-safe API calls
 */

export const API_ENDPOINTS = {
  // Authentication
  auth: {
    register: '/register',
    login: '/login',
    logout: '/logout',
    me: '/me',
    verifyEmail: (id: string, hash: string) => `/verify-email/${id}/${hash}`,
    resendVerification: '/resend-verification',
    forgotPassword: '/forgot-password',
    resetPassword: '/reset-password',
    changePassword: '/profile/password',
    sendPhoneVerification: '/send-phone-verification',
    verifyPhone: '/verify-phone',
    enable2FA: '/2fa/enable',
    disable2FA: '/2fa/disable',
  },

  // Profile
  profile: {
    get: '/profile',
    update: '/profile',
    uploadAvatar: '/profile/avatar',
    deleteAvatar: '/profile/avatar',
    completionStatus: '/profile/completion-status',
    updateBasicInfo: '/profile/basic-info',
    updateContactInfo: '/profile/contact-info',
    updateDetails: '/profile/details',
    complete: '/profile/complete',
  },

  // Settings & Privacy
  settings: {
    update: '/settings',
    updatePrivacy: '/privacy',
  },

  // Properties
  properties: {
    list: '/properties',
    featured: '/properties/featured',
    search: '/properties/search',
    show: (id: string | number) => `/properties/${id}`,
    myProperties: '/my-properties',
    create: '/properties',
    update: (id: string | number) => `/properties/${id}`,
    delete: (id: string | number) => `/properties/${id}`,
    publish: (id: string | number) => `/properties/${id}/publish`,
    unpublish: (id: string | number) => `/properties/${id}/unpublish`,
    uploadImages: (id: string | number) => `/properties/${id}/images`,
    deleteImage: (id: string | number, imageIndex: number) => `/properties/${id}/images/${imageIndex}`,
    setMainImage: (id: string | number) => `/properties/${id}/main-image`,
    blockedDates: (id: string | number) => `/properties/${id}/blocked-dates`,
    calendar: (id: string | number) => `/properties/${id}/calendar`,
    calendarPricing: (id: string | number) => `/properties/${id}/calendar/pricing`,
    
    // Analytics
    analytics: (id: string | number) => `/properties/${id}/analytics`,
    revenue: (id: string | number) => `/properties/${id}/analytics/revenue`,
    occupancy: (id: string | number) => `/properties/${id}/analytics/occupancy`,
    bookingTrends: (id: string | number) => `/properties/${id}/analytics/booking-trends`,
    guestDemographics: (id: string | number) => `/properties/${id}/analytics/demographics`,
    
    // Reports
    reports: {
      revenue: (id: string | number) => `/properties/${id}/reports/revenue`,
      bookings: (id: string | number) => `/properties/${id}/reports/bookings`,
      expenses: (id: string | number) => `/properties/${id}/reports/expenses`,
      download: (id: string | number, type: string) => `/properties/${id}/reports/${type}/download`,
    },
    
    // Maintenance
    maintenance: {
      list: (propertyId: string | number) => `/properties/${propertyId}/maintenance`,
      create: (propertyId: string | number) => `/properties/${propertyId}/maintenance`,
      show: (propertyId: string | number, id: number) => `/properties/${propertyId}/maintenance/${id}`,
      update: (propertyId: string | number, id: number) => `/properties/${propertyId}/maintenance/${id}`,
      delete: (propertyId: string | number, id: number) => `/properties/${propertyId}/maintenance/${id}`,
      updateStatus: (propertyId: string | number, id: number) => `/properties/${propertyId}/maintenance/${id}/status`,
      assign: (propertyId: string | number, id: number) => `/properties/${propertyId}/maintenance/${id}/assign`,
    },
    
    // Bulk Operations
    bulk: {
      activate: '/properties/bulk/activate',
      deactivate: '/properties/bulk/deactivate',
      delete: '/properties/bulk/delete',
      updatePrices: '/properties/bulk/update-prices',
      updateAvailability: '/properties/bulk/update-availability',
    },
  },

  // Global Analytics (aggregate across all properties for the current host)
  analytics: {
    // Consolidated summary (returns nested data: revenue, occupancy, bookings, demographics)
    summary: (range: number | string = 30) => `/analytics/summary?range=${range}`,
    revenue: (range: number | string = 30) => `/analytics/revenue?range=${range}`,
    occupancy: (range: number | string = 30) => `/analytics/occupancy?range=${range}`,
    bookingTrends: (range: number | string = 30) => `/analytics/booking-trends?range=${range}`,
    guestDemographics: (range: number | string = 30) => `/analytics/demographics?range=${range}`,
    bookingStatus: (range: number | string = 30) => `/analytics/booking-status?range=${range}`,
  },

  // Bookings
  bookings: {
    list: '/bookings',
    myBookings: '/my-bookings',
    show: (id: string | number) => `/bookings/${id}`,
    create: '/bookings',
    update: (id: string | number) => `/bookings/${id}`,
    delete: (id: string | number) => `/bookings/${id}`,
    checkAvailability: '/check-availability',
    confirm: (id: string | number) => `/bookings/${id}/confirm`,
    cancel: (id: string | number) => `/bookings/${id}/cancel`,
    checkIn: (id: string | number) => `/bookings/${id}/check-in`,
    checkOut: (id: string | number) => `/bookings/${id}/check-out`,
    generateInvoice: (id: string | number) => `/bookings/${id}/generate-invoice`,
    getInvoices: (id: string | number) => `/bookings/${id}/invoices`,
    accessCode: (id: string | number) => `/bookings/${id}/access-code`,
  },

  // Payments
  payments: {
    list: '/payments',
    create: '/payments',
    show: (id: string | number) => `/payments/${id}`,
    updateStatus: (id: string | number) => `/payments/${id}/status`,
  },

  // Invoices
  invoices: {
    list: '/invoices',
    show: (id: string | number) => `/invoices/${id}`,
    download: (id: string | number) => `/invoices/${id}/download`,
    resend: (id: string | number) => `/invoices/${id}/resend`,
  },

  // Notifications
  notifications: {
    list: '/notifications',
    unreadCount: '/notifications/unread-count',
    markAllAsRead: '/notifications/mark-all-read',
    markAsRead: (id: string) => `/notifications/${id}/read`,
    markAsUnread: (id: string) => `/notifications/${id}/unread`,
    delete: (id: string) => `/notifications/${id}`,
    getPreferences: '/notifications/preferences',
    updatePreferences: '/notifications/preferences',
    test: '/notifications/test',
  },

  // Reviews
  reviews: {
    list: '/reviews',
    myReviews: '/my-reviews',
    show: (id: string | number) => `/reviews/${id}`,
    create: '/reviews',
    update: (id: string | number) => `/reviews/${id}`,
    delete: (id: string | number) => `/reviews/${id}`,
    addResponse: (id: string | number) => `/reviews/${id}/response`,
    vote: (id: string | number) => `/reviews/${id}/vote`,
    propertyRating: (propertyId: string | number) => `/properties/${propertyId}/rating`,
  },

  // Loyalty Program
  loyalty: {
    index: '/loyalty',
    tiers: '/loyalty/tiers',
    transactions: '/loyalty/transactions',
    redeem: '/loyalty/redeem',
    calculateDiscount: '/loyalty/calculate-discount',
    leaderboard: '/loyalty/leaderboard',
    claimBirthday: '/loyalty/claim-birthday',
    expiringPoints: '/loyalty/expiring-points',
    tierBenefits: (tierId: number | string) => `/loyalty/tiers/${tierId}/benefits`,
  },

  // Referral Program
  referrals: {
    list: '/referrals',
    code: '/referrals/code',
    regenerate: '/referrals/regenerate',
    create: '/referrals/create',
    discount: '/referrals/discount',
    applyDiscount: '/referrals/apply-discount',
    leaderboard: '/referrals/leaderboard',
    validate: '/referrals/validate',
    programInfo: '/referrals/program-info',
  },

  // Conversations & Messages
  conversations: {
    list: '/conversations',
    create: '/conversations',
    show: (id: string | number) => `/conversations/${id}`,
    archive: (id: string | number) => `/conversations/${id}/archive`,
    unarchive: (id: string | number) => `/conversations/${id}/unarchive`,
    delete: (id: string | number) => `/conversations/${id}`,
    markAllAsRead: (id: string | number) => `/conversations/${id}/mark-all-read`,
  },

  messages: {
    list: (conversationId: string | number) => `/conversations/${conversationId}/messages`,
    create: (conversationId: string | number) => `/conversations/${conversationId}/messages`,
    update: (id: string | number) => `/messages/${id}`,
    delete: (id: string | number) => `/messages/${id}`,
    markAsRead: (id: string | number) => `/messages/${id}/read`,
    uploadAttachment: '/messages/upload-attachment',
  },

  // Wishlists
  wishlists: {
    list: '/wishlists',
    create: '/wishlists',
    show: (id: string | number) => `/wishlists/${id}`,
    update: (id: string | number) => `/wishlists/${id}`,
    delete: (id: string | number) => `/wishlists/${id}`,
    addProperty: (id: string | number) => `/wishlists/${id}/properties`,
    removeProperty: (wishlistId: string | number, itemId: string | number) => `/wishlists/${wishlistId}/items/${itemId}`,
    updateItem: (wishlistId: string | number, itemId: string | number) => `/wishlists/${wishlistId}/items/${itemId}`,
    toggleProperty: '/wishlists/toggle-property',
    checkProperty: (propertyId: string | number) => `/wishlists/check/${propertyId}`,
  },

  // Settings (Admin)
  adminSettings: {
    list: '/settings',
    update: '/settings',
    public: '/settings/public',
    testEmail: '/settings/test-email',
  },

  // Dashboards
  dashboards: {
    tenant: '/dashboards/tenant',
    owner: '/dashboards/owner',
  },

  // Languages & Currencies
  languages: {
    list: '/languages',
    default: '/languages/default',
    show: (code: string) => `/languages/${code}`,
  },

  currencies: {
    list: '/currencies',
    default: '/currencies/default',
    show: (code: string) => `/currencies/${code}`,
    convert: '/currencies/convert',
  },

  // KYC & Verification
  verification: {
    getStatus: '/verification/status',
    uploadGovernmentId: '/verification/government-id',
    getVerificationStatus: '/verification-status',
    
    // Guest Verification
    guestVerification: {
      getStatus: '/guest-verification',
      submitIdentity: '/guest-verification/identity',
      uploadSelfie: '/guest-verification/selfie',
      requestCreditCheck: '/guest-verification/credit-check',
      getReferences: '/guest-verification/references',
    },

    // User Verification
    userVerification: {
      // Align with backend routes in backend/routes/api.php
      // GET /my-verification returns current user's verification record
      getStatus: '/my-verification',
      // Submission endpoints
      uploadId: '/user-verifications/id',
      uploadAddress: '/user-verifications/address',
      // Phone verification flows for user verification
      verifyPhone: '/user-verifications/phone/verify',
      sendPhoneVerification: '/user-verifications/phone/send',
      backgroundCheck: '/user-verifications/background-check',
    },

    // Admin Verification Management
    admin: {
      approve: (userId: number) => `/admin/verification/${userId}/approve`,
      reject: (userId: number) => `/admin/verification/${userId}/reject`,
    },
  },

  // Smart Locks & Access Codes
  smartLocks: {
    list: (propertyId: number) => `/properties/${propertyId}/smart-locks`,
    create: (propertyId: number) => `/properties/${propertyId}/smart-locks`,
    update: (propertyId: number, lockId: number) => `/properties/${propertyId}/smart-locks/${lockId}`,
    delete: (propertyId: number, lockId: number) => `/properties/${propertyId}/smart-locks/${lockId}`,
    sync: (propertyId: number, lockId: number) => `/properties/${propertyId}/smart-locks/${lockId}/sync`,
    
    // Access Codes
    accessCodes: {
      list: (lockId: number) => `/smart-locks/${lockId}/access-codes`,
      create: (lockId: number) => `/smart-locks/${lockId}/access-codes`,
      get: (lockId: number, codeId: number) => `/smart-locks/${lockId}/access-codes/${codeId}`,
      delete: (lockId: number, codeId: number) => `/smart-locks/${lockId}/access-codes/${codeId}`,
      generate: (bookingId: number) => `/bookings/${bookingId}/generate-access-code`,
      getByBooking: (bookingId: number) => `/bookings/${bookingId}/access-codes`,
    },
  },

  // Two-Factor Authentication
  twoFactor: {
    enable: '/2fa/enable',
    confirm: '/2fa/confirm',
    disable: '/2fa/disable',
    status: '/2fa/status',
    sendCode: '/2fa/send-code',
    verify: '/2fa/verify',
    verifyRecovery: '/2fa/verify-recovery',
    recoveryCodes: '/2fa/recovery-codes',
  },

  // GDPR & Privacy
  gdpr: {
    export: '/gdpr/export',
    forgetMe: '/gdpr/forget-me',
    getConsent: '/gdpr/consent',
    updateConsent: '/gdpr/consent',
    dataProtection: '/gdpr/data-protection',
  },

  // Credit Check
  creditCheck: {
    request: '/credit-check/request',
    getStatus: '/credit-check/status',
    getReport: (id: number) => `/credit-check/${id}`,
  },

  // Guest Screening
  guestScreening: {
    list: '/guest-screening',
    submit: '/guest-screening',
    getStatus: '/guest-screening/status',
    getReport: (id: number) => `/guest-screening/${id}`,
    approve: (id: number) => `/guest-screening/${id}/approve`,
    reject: (id: number) => `/guest-screening/${id}/reject`,
  },
} as const;

export type ApiEndpoint = typeof API_ENDPOINTS;
