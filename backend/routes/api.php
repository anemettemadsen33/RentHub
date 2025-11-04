<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CreditCheckController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ExternalCalendarController;
use App\Http\Controllers\Api\GDPRController;
// GoogleCalendarController temporarily not imported to avoid Google Calendar dependency at boot
// use App\Http\Controllers\Api\GoogleCalendarController;
use App\Http\Controllers\Api\GuestReferenceController;
use App\Http\Controllers\Api\GuestScreeningController;
use App\Http\Controllers\Api\GuestVerificationController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MapSearchController;
use App\Http\Controllers\Api\OAuth2Controller;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyVerificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SecurityAuditController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\UserVerificationController;
use App\Http\Controllers\Api\V1\OwnerDashboardController;
use App\Http\Controllers\Api\V1\PropertyComparisonController;
use App\Http\Controllers\Api\V1\TenantDashboardController;
use App\Http\Controllers\Api\V1\TranslationController;
use Illuminate\Support\Facades\Route;

// Health Check Routes (outside versioned routes)
Route::get('/health', [HealthCheckController::class, 'index']);
Route::get('/health/liveness', [HealthCheckController::class, 'liveness']);
Route::get('/health/readiness', [HealthCheckController::class, 'readiness']);
Route::get('/metrics', [HealthCheckController::class, 'metrics']);

// Public routes
Route::prefix('v1')->group(function () {
    // Languages
    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/languages/default', [LanguageController::class, 'getDefault']);
    Route::get('/languages/{code}', [LanguageController::class, 'show']);

    // Currencies
    Route::get('/currencies', [CurrencyController::class, 'index']);
    Route::get('/currencies/default', [CurrencyController::class, 'getDefault']);
    Route::get('/currencies/{code}', [CurrencyController::class, 'show']);
    Route::post('/currencies/convert', [CurrencyController::class, 'convert']);

    // Authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Two-Factor Authentication (Login)
    Route::post('/2fa/send-code', [AuthController::class, 'sendTwoFactorCode']);
    Route::post('/2fa/verify', [AuthController::class, 'verifyTwoFactorCode']);
    Route::post('/2fa/verify-recovery', [AuthController::class, 'verifyRecoveryCode']);

    // Social Login
    Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

    // Referral validation (public)
    Route::post('/referrals/validate', [\App\Http\Controllers\Api\ReferralController::class, 'validate']);
    Route::get('/referrals/program-info', [\App\Http\Controllers\Api\ReferralController::class, 'programInfo']);

    // Public properties
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/featured', [PropertyController::class, 'featured']);
    Route::get('/properties/search', [PropertyController::class, 'search']);
    Route::get('/properties/{property}', [PropertyController::class, 'show']);

    // Map Search
    Route::post('/map/search-radius', [MapSearchController::class, 'searchRadius']);
    Route::post('/map/search-bounds', [MapSearchController::class, 'searchBounds']);
    Route::get('/map/property/{id}', [MapSearchController::class, 'getPropertyMapData']);
    Route::post('/map/geocode', [MapSearchController::class, 'geocode']);

    // Property Comparison (Public - guests can compare too)
    Route::get('/property-comparison', [PropertyComparisonController::class, 'index']);
    Route::post('/property-comparison/compare', [PropertyComparisonController::class, 'compare']);

    // Public reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::get('/properties/{property}/rating', [ReviewController::class, 'propertyRating']);

    // SEO endpoints
    Route::get('/seo/locations', [SeoController::class, 'locations']);
    Route::get('/seo/property-urls', [SeoController::class, 'propertyUrls']);
    Route::get('/seo/popular-searches', [SeoController::class, 'popularSearches']);
    Route::get('/seo/properties/{id}/metadata', [SeoController::class, 'propertyMetadata']);
    Route::get('/seo/organization', [SeoController::class, 'organizationData']);

    // Performance Analytics endpoints (public for beacon API)
    Route::post('/analytics/web-vitals', [PerformanceController::class, 'storeWebVitals']);
    Route::get('/performance/recommendations', [PerformanceController::class, 'getRecommendations']);

    // OAuth 2.0 Public Endpoints
    Route::post('/oauth/token', [OAuth2Controller::class, 'token']);

    // GDPR Public Endpoints
    Route::get('/gdpr/data-protection', [GDPRController::class, 'dataProtection']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/send-phone-verification', [AuthController::class, 'sendPhoneVerification']);
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);

    // Two-Factor Authentication (Settings)
    Route::post('/2fa/enable', [AuthController::class, 'enableTwoFactor']);
    Route::post('/2fa/disable', [AuthController::class, 'disableTwoFactor']);

    // OAuth 2.0 Protected Endpoints
    Route::post('/oauth/authorize', [OAuth2Controller::class, 'authorize']);
    Route::post('/oauth/revoke', [OAuth2Controller::class, 'revoke']);
    Route::post('/oauth/introspect', [OAuth2Controller::class, 'introspect']);

    // GDPR User Endpoints
    Route::post('/gdpr/export', [GDPRController::class, 'export']);
    Route::delete('/gdpr/forget-me', [GDPRController::class, 'forgetMe']);
    Route::get('/gdpr/consent', [GDPRController::class, 'getConsent']);
    Route::put('/gdpr/consent', [GDPRController::class, 'updateConsent']);

    // Security Audit (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/security/audit-logs', [SecurityAuditController::class, 'index']);
        Route::get('/security/anomalies', [SecurityAuditController::class, 'detectAnomalies']);
        Route::post('/security/log', [SecurityAuditController::class, 'logEvent']);
        Route::delete('/security/cleanup', [SecurityAuditController::class, 'cleanup']);
        Route::get('/gdpr/compliance-report', [GDPRController::class, 'complianceReport']);
    });

    // Profile Wizard
    Route::get('/profile/completion-status', [\App\Http\Controllers\Api\ProfileController::class, 'getCompletionStatus']);
    Route::post('/profile/basic-info', [\App\Http\Controllers\Api\ProfileController::class, 'updateBasicInfo']);
    Route::post('/profile/contact-info', [\App\Http\Controllers\Api\ProfileController::class, 'updateContactInfo']);
    Route::post('/profile/details', [\App\Http\Controllers\Api\ProfileController::class, 'updateProfileDetails']);
    Route::post('/profile/complete', [\App\Http\Controllers\Api\ProfileController::class, 'completeWizard']);

    // User Profile
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'getProfile']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'updateProfile']);
    Route::post('/profile/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'deleteAvatar']);

    // User Settings
    Route::put('/settings', [\App\Http\Controllers\Api\ProfileController::class, 'updateSettings']);
    Route::put('/privacy', [\App\Http\Controllers\Api\ProfileController::class, 'updatePrivacySettings']);

    // Verification Status
    Route::get('/verification-status', [\App\Http\Controllers\Api\ProfileController::class, 'getVerificationStatus']);

    // ID Verification
    Route::post('/verification/government-id', [\App\Http\Controllers\Api\VerificationController::class, 'uploadGovernmentId']);
    Route::get('/verification/status', [\App\Http\Controllers\Api\VerificationController::class, 'getStatus']);

    // Admin: ID Verification Management
    Route::post('/admin/verification/{userId}/approve', [\App\Http\Controllers\Api\VerificationController::class, 'approveGovernmentId']);
    Route::post('/admin/verification/{userId}/reject', [\App\Http\Controllers\Api\VerificationController::class, 'rejectGovernmentId']);

    // Roles & Permissions
    Route::get('/roles', [\App\Http\Controllers\Api\RoleController::class, 'getRoles']);
    Route::get('/my-role', [\App\Http\Controllers\Api\RoleController::class, 'getMyRole']);
    Route::post('/check-permission', [\App\Http\Controllers\Api\RoleController::class, 'checkPermission']);

    // Admin: Role Management
    Route::get('/admin/users-by-role', [\App\Http\Controllers\Api\RoleController::class, 'getUsersByRole'])->middleware('role:admin');
    Route::put('/admin/users/{userId}/role', [\App\Http\Controllers\Api\RoleController::class, 'changeUserRole'])->middleware('role:admin');

    // Properties Management (Owner & Admin only)
    Route::get('/my-properties', [PropertyController::class, 'myProperties'])->middleware('role:owner,admin');
    Route::post('/properties', [PropertyController::class, 'store'])->middleware('role:owner,admin');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->middleware('role:owner,admin');

    // Property Status Management
    Route::post('/properties/{property}/publish', [PropertyController::class, 'publish'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/unpublish', [PropertyController::class, 'unpublish'])->middleware('role:owner,admin');

    // Property Calendar Management (legacy endpoints)
    Route::post('/properties/{property}/block-dates', [PropertyController::class, 'blockDates'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/unblock-dates', [PropertyController::class, 'unblockDates'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/custom-pricing', [PropertyController::class, 'setCustomPricing'])->middleware('role:owner,admin');

    // Enhanced Calendar Management
    Route::get('/properties/{property}/calendar', [CalendarController::class, 'getAvailability']);
    Route::get('/properties/{property}/calendar/pricing', [CalendarController::class, 'getPricingCalendar']);
    Route::get('/properties/{property}/calendar/blocked-dates', [CalendarController::class, 'getBlockedDates']);
    Route::post('/properties/{property}/calendar/bulk-block', [CalendarController::class, 'bulkBlockDates'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/calendar/bulk-unblock', [CalendarController::class, 'bulkUnblockDates'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/calendar/bulk-pricing', [CalendarController::class, 'bulkSetPricing'])->middleware('role:owner,admin');
    Route::delete('/properties/{property}/calendar/bulk-pricing', [CalendarController::class, 'bulkRemovePricing'])->middleware('role:owner,admin');

    // External Calendar Management (iCal, Airbnb, Booking.com sync)
    Route::get('/properties/{property}/external-calendars', [ExternalCalendarController::class, 'index'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/external-calendars', [ExternalCalendarController::class, 'store'])->middleware('role:owner,admin');
    Route::put('/properties/{property}/external-calendars/{externalCalendar}', [ExternalCalendarController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/properties/{property}/external-calendars/{externalCalendar}', [ExternalCalendarController::class, 'destroy'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/external-calendars/{externalCalendar}/sync', [ExternalCalendarController::class, 'sync'])->middleware('role:owner,admin');
    Route::get('/properties/{property}/external-calendars/{externalCalendar}/logs', [ExternalCalendarController::class, 'syncLogs'])->middleware('role:owner,admin');
    Route::get('/properties/{property}/ical-url', [ExternalCalendarController::class, 'getICalUrl'])->middleware('role:owner,admin');

    // Property Images
    Route::post('/properties/{property}/images', [PropertyController::class, 'uploadImages'])->middleware('role:owner,admin');
    Route::delete('/properties/{property}/images/{imageIndex}', [PropertyController::class, 'deleteImage'])->middleware('role:owner,admin');
    Route::post('/properties/{property}/main-image', [PropertyController::class, 'setMainImage'])->middleware('role:owner,admin');

    // Bookings - Tenant can create, Owner/Admin can manage
    Route::apiResource('bookings', BookingController::class)->middleware('role:tenant,owner,admin');
    Route::get('/my-bookings', [BookingController::class, 'userBookings']);
    Route::post('/check-availability', [BookingController::class, 'checkAvailability']);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->middleware('role:owner,admin');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('role:tenant,owner,admin');
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->middleware('role:owner,admin');
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->middleware('role:owner,admin');
    Route::post('/bookings/{booking}/generate-invoice', [BookingController::class, 'generateInvoice'])->middleware('role:owner,admin');
    Route::get('/bookings/{booking}/invoices', [BookingController::class, 'getInvoices']);

    // Reviews - Tenant can write, Owner can respond, Admin can delete
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    Route::post('/reviews', [ReviewController::class, 'store'])->middleware('role:tenant,owner,admin');
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Review responses
    Route::post('/reviews/{id}/response', [ReviewController::class, 'addResponse'])->middleware('role:owner,admin');

    // Review helpful votes
    Route::post('/reviews/{id}/vote', [ReviewController::class, 'vote']);

    // Payments
    Route::get('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'index']);
    Route::post('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store'])->middleware('role:tenant,owner,admin');
    Route::get('/payments/{payment}', [\App\Http\Controllers\Api\PaymentController::class, 'show']);
    Route::post('/payments/{payment}/status', [\App\Http\Controllers\Api\PaymentController::class, 'updateStatus']);

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']);
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Api\InvoiceController::class, 'download']);
    Route::post('/invoices/{invoice}/resend', [\App\Http\Controllers\Api\InvoiceController::class, 'resend']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);

    // Notification Preferences
    Route::get('/notifications/preferences', [\App\Http\Controllers\Api\NotificationController::class, 'getPreferences']);
    Route::put('/notifications/preferences', [\App\Http\Controllers\Api\NotificationController::class, 'updatePreferences']);

    // Test notification (dev only)
    Route::post('/notifications/test', [\App\Http\Controllers\Api\NotificationController::class, 'testNotification']);

    // Conversations & Messages
    Route::get('/conversations', [\App\Http\Controllers\Api\ConversationController::class, 'index']);
    Route::post('/conversations', [\App\Http\Controllers\Api\ConversationController::class, 'store']);
    Route::get('/conversations/{id}', [\App\Http\Controllers\Api\ConversationController::class, 'show']);
    Route::patch('/conversations/{id}/archive', [\App\Http\Controllers\Api\ConversationController::class, 'archive']);
    Route::patch('/conversations/{id}/unarchive', [\App\Http\Controllers\Api\ConversationController::class, 'unarchive']);
    Route::delete('/conversations/{id}', [\App\Http\Controllers\Api\ConversationController::class, 'destroy']);
    Route::post('/conversations/{id}/mark-all-read', [\App\Http\Controllers\Api\ConversationController::class, 'markAllAsRead']);

    // Messages
    Route::get('/conversations/{conversationId}/messages', [\App\Http\Controllers\Api\MessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [\App\Http\Controllers\Api\MessageController::class, 'store']);
    Route::patch('/messages/{id}', [\App\Http\Controllers\Api\MessageController::class, 'update']);
    Route::delete('/messages/{id}', [\App\Http\Controllers\Api\MessageController::class, 'destroy']);
    Route::post('/messages/{id}/read', [\App\Http\Controllers\Api\MessageController::class, 'markAsRead']);
    Route::post('/messages/upload-attachment', [\App\Http\Controllers\Api\MessageController::class, 'uploadAttachment']);

    // Wishlists
    Route::get('/wishlists', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/wishlists', [\App\Http\Controllers\Api\WishlistController::class, 'store']);
    Route::get('/wishlists/{id}', [\App\Http\Controllers\Api\WishlistController::class, 'show']);
    Route::put('/wishlists/{id}', [\App\Http\Controllers\Api\WishlistController::class, 'update']);
    Route::delete('/wishlists/{id}', [\App\Http\Controllers\Api\WishlistController::class, 'destroy']);

    // Wishlist Items
    Route::post('/wishlists/{id}/properties', [\App\Http\Controllers\Api\WishlistController::class, 'addProperty']);
    Route::delete('/wishlists/{wishlistId}/items/{itemId}', [\App\Http\Controllers\Api\WishlistController::class, 'removeProperty']);
    Route::put('/wishlists/{wishlistId}/items/{itemId}', [\App\Http\Controllers\Api\WishlistController::class, 'updateItem']);

    // Quick toggle property in default wishlist
    Route::post('/wishlists/toggle-property', [\App\Http\Controllers\Api\WishlistController::class, 'toggleProperty']);
    Route::get('/wishlists/check/{propertyId}', [\App\Http\Controllers\Api\WishlistController::class, 'checkProperty']);

    // Property Comparison (Authenticated)
    Route::post('/property-comparison/add', [PropertyComparisonController::class, 'add']);
    Route::delete('/property-comparison/remove/{propertyId}', [PropertyComparisonController::class, 'remove']);
    Route::delete('/property-comparison/clear', [PropertyComparisonController::class, 'clear']);

    // Saved Searches
    Route::get('/saved-searches', [\App\Http\Controllers\Api\SavedSearchController::class, 'index']);
    Route::post('/saved-searches', [\App\Http\Controllers\Api\SavedSearchController::class, 'store']);
    Route::get('/saved-searches/statistics', [\App\Http\Controllers\Api\SavedSearchController::class, 'statistics']);
    Route::get('/saved-searches/{id}', [\App\Http\Controllers\Api\SavedSearchController::class, 'show']);
    Route::put('/saved-searches/{id}', [\App\Http\Controllers\Api\SavedSearchController::class, 'update']);
    Route::delete('/saved-searches/{id}', [\App\Http\Controllers\Api\SavedSearchController::class, 'destroy']);
    Route::post('/saved-searches/{id}/execute', [\App\Http\Controllers\Api\SavedSearchController::class, 'execute']);
    Route::get('/saved-searches/{id}/new-listings', [\App\Http\Controllers\Api\SavedSearchController::class, 'checkNewListings']);
    Route::post('/saved-searches/{id}/toggle-alerts', [\App\Http\Controllers\Api\SavedSearchController::class, 'toggleAlerts']);

    // User Verifications
    Route::get('/user-verifications', [UserVerificationController::class, 'index']);
    Route::get('/user-verifications/{id}', [UserVerificationController::class, 'show']);
    Route::get('/my-verification', [UserVerificationController::class, 'getMyVerification']);
    Route::post('/user-verifications/id', [UserVerificationController::class, 'submitIdVerification']);
    Route::post('/user-verifications/phone/send', [UserVerificationController::class, 'submitPhoneVerification']);
    Route::post('/user-verifications/phone/verify', [UserVerificationController::class, 'verifyPhoneCode']);
    Route::post('/user-verifications/address', [UserVerificationController::class, 'submitAddressVerification']);
    Route::post('/user-verifications/background-check', [UserVerificationController::class, 'requestBackgroundCheck']);
    Route::get('/user-verifications/statistics', [UserVerificationController::class, 'getStatistics']);

    // Admin: User Verification Management
    Route::post('/admin/user-verifications/{id}/approve-id', [UserVerificationController::class, 'approveId'])->middleware('role:admin');
    Route::post('/admin/user-verifications/{id}/reject-id', [UserVerificationController::class, 'rejectId'])->middleware('role:admin');
    Route::post('/admin/user-verifications/{id}/approve-address', [UserVerificationController::class, 'approveAddress'])->middleware('role:admin');
    Route::post('/admin/user-verifications/{id}/reject-address', [UserVerificationController::class, 'rejectAddress'])->middleware('role:admin');
    Route::post('/admin/user-verifications/{id}/background-check', [UserVerificationController::class, 'completeBackgroundCheck'])->middleware('role:admin');

    // Insurance Management
    Route::post('/insurance/plans/available', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'getAvailablePlans']);
    Route::post('/insurance/add-to-booking', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'addToBooking']);
    Route::get('/insurance/booking/{bookingId}', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'getBookingInsurances']);
    Route::post('/insurance/{insuranceId}/activate', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'activateInsurance']);
    Route::post('/insurance/{insuranceId}/cancel', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'cancelInsurance']);

    // Insurance Claims
    Route::post('/insurance/claims', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'submitClaim']);
    Route::get('/insurance/claims', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'getUserClaims']);
    Route::get('/insurance/claims/{claimId}', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'getClaimDetails']);

    // Smart Locks & Access Codes
    Route::prefix('properties/{propertyId}')->group(function () {
        Route::get('/smart-locks', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'index']);
        Route::post('/smart-locks', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'store']);
        Route::get('/smart-locks/{lockId}', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'show']);
        Route::put('/smart-locks/{lockId}', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'update']);
        Route::delete('/smart-locks/{lockId}', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'destroy']);
        Route::get('/smart-locks/{lockId}/status', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'status']);
        Route::post('/smart-locks/{lockId}/lock', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'lock']);
        Route::post('/smart-locks/{lockId}/unlock', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'unlock']);
        Route::get('/smart-locks/{lockId}/activities', [\App\Http\Controllers\Api\V1\SmartLockController::class, 'activities']);

        // Access Codes
        Route::get('/smart-locks/{lockId}/access-codes', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'index']);
        Route::post('/smart-locks/{lockId}/access-codes', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'store']);
        Route::get('/smart-locks/{lockId}/access-codes/{codeId}', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'show']);
        Route::put('/smart-locks/{lockId}/access-codes/{codeId}', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'update']);
        Route::delete('/smart-locks/{lockId}/access-codes/{codeId}', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'destroy']);
    });

    // Guest access to their booking code
    Route::get('/bookings/{bookingId}/access-code', [\App\Http\Controllers\Api\V1\AccessCodeController::class, 'myCode']);

    // Property Verifications
    Route::get('/property-verifications', [PropertyVerificationController::class, 'index']);
    Route::get('/property-verifications/{id}', [PropertyVerificationController::class, 'show']);
    Route::get('/properties/{propertyId}/verification', [PropertyVerificationController::class, 'getPropertyVerification']);
    Route::post('/properties/{propertyId}/verification/ownership', [PropertyVerificationController::class, 'submitOwnershipDocuments']);
    Route::post('/properties/{propertyId}/verification/legal-documents', [PropertyVerificationController::class, 'submitLegalDocuments']);
    Route::post('/properties/{propertyId}/verification/request-inspection', [PropertyVerificationController::class, 'requestInspection']);
    Route::get('/property-verifications/statistics', [PropertyVerificationController::class, 'getStatistics']);

    // Admin: Property Verification Management
    Route::post('/admin/property-verifications/{id}/approve-ownership', [PropertyVerificationController::class, 'approveOwnership'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/reject-ownership', [PropertyVerificationController::class, 'rejectOwnership'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/approve-photos', [PropertyVerificationController::class, 'approvePhotos'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/reject-photos', [PropertyVerificationController::class, 'rejectPhotos'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/approve-details', [PropertyVerificationController::class, 'approveDetails'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/reject-details', [PropertyVerificationController::class, 'rejectDetails'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/schedule-inspection', [PropertyVerificationController::class, 'scheduleInspection'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/complete-inspection', [PropertyVerificationController::class, 'completeInspection'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/grant-badge', [PropertyVerificationController::class, 'grantVerifiedBadge'])->middleware('role:admin');
    Route::post('/admin/property-verifications/{id}/revoke-badge', [PropertyVerificationController::class, 'revokeVerifiedBadge'])->middleware('role:admin');

    // Guest Verification (Screening)
    Route::get('/guest-verification', [GuestVerificationController::class, 'show']);
    Route::post('/guest-verification/identity', [GuestVerificationController::class, 'submitIdentity']);
    Route::post('/guest-verification/references', [GuestVerificationController::class, 'addReference']);
    Route::post('/guest-verification/credit-check', [GuestVerificationController::class, 'requestCreditCheck']);
    Route::get('/guest-verification/statistics', [GuestVerificationController::class, 'statistics']);

    // Owner Dashboard Analytics
    Route::prefix('owner/dashboard')->group(function () {
        Route::get('/overview', [OwnerDashboardController::class, 'getOverview']);
        Route::get('/booking-statistics', [OwnerDashboardController::class, 'getBookingStatistics']);
        Route::get('/revenue-reports', [OwnerDashboardController::class, 'getRevenueReports']);
        Route::get('/occupancy-rate', [OwnerDashboardController::class, 'getOccupancyRate']);
        Route::get('/property-performance', [OwnerDashboardController::class, 'getPropertyPerformance']);
        Route::get('/guest-demographics', [OwnerDashboardController::class, 'getGuestDemographics']);
    });

    // Tenant Dashboard Analytics
    Route::prefix('tenant/dashboard')->group(function () {
        Route::get('/overview', [TenantDashboardController::class, 'getOverview']);
        Route::get('/booking-history', [TenantDashboardController::class, 'getBookingHistory']);
        Route::get('/spending-reports', [TenantDashboardController::class, 'getSpendingReports']);
        Route::get('/saved-properties', [TenantDashboardController::class, 'getSavedProperties']);
        Route::get('/review-history', [TenantDashboardController::class, 'getReviewHistory']);
        Route::get('/upcoming-trips', [TenantDashboardController::class, 'getUpcomingTrips']);
    });

    // Smart Pricing System
    Route::prefix('properties/{propertyId}')->group(function () {
        // Pricing Rules
        Route::get('/pricing-rules', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'index']);
        Route::post('/pricing-rules', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'store']);
        Route::get('/pricing-rules/{ruleId}', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'show']);
        Route::put('/pricing-rules/{ruleId}', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'update']);
        Route::delete('/pricing-rules/{ruleId}', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'destroy']);
        Route::post('/pricing-rules/{ruleId}/toggle', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'toggle']);

        // Price Calculation
        Route::post('/calculate-price', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'calculatePrice']);
        Route::get('/pricing-calendar', [\App\Http\Controllers\Api\V1\PricingRuleController::class, 'calendar']);

        // Price Suggestions (AI-powered)
        Route::get('/price-suggestions', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'index']);
        Route::post('/price-suggestions', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'store']);
        Route::get('/price-suggestions/{suggestionId}', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'show']);
        Route::post('/price-suggestions/{suggestionId}/accept', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'accept']);
        Route::post('/price-suggestions/{suggestionId}/reject', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'reject']);

        // Market Analysis & Optimization
        Route::get('/market-analysis', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'marketAnalysis']);
        Route::post('/pricing-optimize', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'optimize']);
        Route::post('/price-suggestions/batch-accept', [\App\Http\Controllers\Api\V1\PriceSuggestionController::class, 'batchAccept']);
    });

    // Currency Management (Admin Only)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates']);
    });

    // Translation Management (Admin Only)
    Route::middleware(['role:admin'])->prefix('translations')->group(function () {
        Route::post('/', [TranslationController::class, 'store']);
        Route::put('/{id}', [TranslationController::class, 'update']);
        Route::delete('/', [TranslationController::class, 'destroy']);
        Route::post('/import', [TranslationController::class, 'import']);
        Route::get('/travel-statistics', [TenantDashboardController::class, 'getTravelStatistics']);
    });
    Route::post('/property/{propertyId}/verification/inspection', [\App\Http\Controllers\Api\V1\PropertyVerificationController::class, 'requestInspection']);

    // Google Calendar Integration
    Route::prefix('google-calendar')->group(function () {
        Route::get('/authorize', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'authorize'])->middleware('role:owner,admin');
        Route::post('/callback', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'callback'])->middleware('role:owner,admin');
        Route::get('/', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'index'])->middleware('role:owner,admin');
        Route::get('/{googleCalendarToken}', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'show'])->middleware('role:owner,admin');
        Route::patch('/{googleCalendarToken}/toggle-sync', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'toggleSync'])->middleware('role:owner,admin');
        Route::post('/{googleCalendarToken}/import', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'import'])->middleware('role:owner,admin');
        Route::post('/{googleCalendarToken}/refresh-webhook', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'refreshWebhook'])->middleware('role:owner,admin');
        Route::delete('/{googleCalendarToken}', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'disconnect'])->middleware('role:owner,admin');
    });
});

// Public wishlist sharing
Route::prefix('v1')->group(function () {
    Route::get('/wishlists/shared/{token}', [\App\Http\Controllers\Api\WishlistController::class, 'getShared']);

    // Public reference verification (no auth required)
    Route::post('/guest-verification/references/{token}/verify', [GuestVerificationController::class, 'verifyReference']);
});

// Translations & Languages (Public)
Route::prefix('v1')->group(function () {
    Route::get('/translations', [TranslationController::class, 'index']);
    Route::get('/translations/{key}', [TranslationController::class, 'show']);
    Route::get('/languages', [TranslationController::class, 'languages']);
    Route::get('/detect-language', [TranslationController::class, 'detectLanguage']);
    Route::get('/translations/export', [TranslationController::class, 'export']);
});

// Long-term Rentals (Authenticated routes)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Long-term Rentals
    Route::get('/long-term-rentals', [\App\Http\Controllers\Api\LongTermRentalController::class, 'index']);
    Route::post('/long-term-rentals', [\App\Http\Controllers\Api\LongTermRentalController::class, 'store'])->middleware('role:tenant,owner,admin');
    Route::get('/long-term-rentals/statistics', [\App\Http\Controllers\Api\LongTermRentalController::class, 'statistics']);
    Route::get('/long-term-rentals/{id}', [\App\Http\Controllers\Api\LongTermRentalController::class, 'show']);
    Route::put('/long-term-rentals/{id}', [\App\Http\Controllers\Api\LongTermRentalController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/long-term-rentals/{id}', [\App\Http\Controllers\Api\LongTermRentalController::class, 'destroy'])->middleware('role:owner,admin');
    Route::post('/long-term-rentals/{id}/activate', [\App\Http\Controllers\Api\LongTermRentalController::class, 'activate'])->middleware('role:owner,admin');
    Route::post('/long-term-rentals/{id}/request-renewal', [\App\Http\Controllers\Api\LongTermRentalController::class, 'requestRenewal'])->middleware('role:tenant,owner');
    Route::post('/long-term-rentals/{id}/approve-renewal', [\App\Http\Controllers\Api\LongTermRentalController::class, 'approveRenewal'])->middleware('role:owner,admin');
    Route::post('/long-term-rentals/{id}/cancel', [\App\Http\Controllers\Api\LongTermRentalController::class, 'cancel'])->middleware('role:tenant,owner,admin');

    // Rent Payments
    Route::get('/rent-payments', [\App\Http\Controllers\Api\RentPaymentController::class, 'index']);
    Route::get('/rent-payments/{id}', [\App\Http\Controllers\Api\RentPaymentController::class, 'show']);
    Route::post('/rent-payments/{id}/mark-as-paid', [\App\Http\Controllers\Api\RentPaymentController::class, 'markAsPaid'])->middleware('role:owner,admin');

    // AI & Machine Learning Features
    // AI Recommendations
    Route::prefix('ai')->group(function () {
        // Smart Recommendations
        Route::get('/recommendations', [\App\Http\Controllers\Api\AiRecommendationController::class, 'getRecommendations']);
        Route::get('/recommendations/{recommendationId}/track', [\App\Http\Controllers\Api\AiRecommendationController::class, 'trackInteraction']);
        Route::get('/properties/{propertyId}/similar', [\App\Http\Controllers\Api\AiRecommendationController::class, 'getSimilarProperties']);
        Route::get('/recommendations/stats', [\App\Http\Controllers\Api\AiRecommendationController::class, 'getRecommendationStats'])->middleware('role:admin');

        // Price Optimization (Owner only)
        Route::middleware('role:owner,admin')->group(function () {
            Route::get('/price/{propertyId}/prediction', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'getPrediction']);
            Route::get('/price/{propertyId}/predictions', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'getPredictionRange']);
            Route::get('/price/{propertyId}/optimization', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'getOptimization']);
            Route::post('/price/{propertyId}/apply', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'applyPriceSuggestion']);
            Route::get('/price/{propertyId}/revenue-report', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'getRevenueReport']);
        });

        // ML Model Management (Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/model/metrics', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'getModelMetrics']);
            Route::post('/model/train', [\App\Http\Controllers\Api\PriceOptimizationController::class, 'trainModel']);
        });

        // Fraud Detection (Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/fraud/alerts', [\App\Http\Controllers\Api\FraudDetectionController::class, 'getAlerts']);
            Route::get('/fraud/alerts/{alertId}', [\App\Http\Controllers\Api\FraudDetectionController::class, 'getAlertDetails']);
            Route::post('/fraud/check/user/{userId}', [\App\Http\Controllers\Api\FraudDetectionController::class, 'checkUser']);
            Route::post('/fraud/check/property/{propertyId}', [\App\Http\Controllers\Api\FraudDetectionController::class, 'checkProperty']);
            Route::post('/fraud/check/booking/{bookingId}', [\App\Http\Controllers\Api\FraudDetectionController::class, 'checkBooking']);
            Route::post('/fraud/check/payment/{paymentId}', [\App\Http\Controllers\Api\FraudDetectionController::class, 'checkPayment']);
            Route::post('/fraud/alerts/{alertId}/resolve', [\App\Http\Controllers\Api\FraudDetectionController::class, 'resolveAlert']);
            Route::post('/fraud/alerts/{alertId}/false-positive', [\App\Http\Controllers\Api\FraudDetectionController::class, 'markFalsePositive']);
            Route::get('/fraud/stats', [\App\Http\Controllers\Api\FraudDetectionController::class, 'getStats']);
            Route::post('/fraud/scan', [\App\Http\Controllers\Api\FraudDetectionController::class, 'runScan']);
        });
    });

    Route::post('/rent-payments/update-overdue', [\App\Http\Controllers\Api\RentPaymentController::class, 'updateOverdue'])->middleware('role:admin');
    Route::post('/rent-payments/{id}/send-reminder', [\App\Http\Controllers\Api\RentPaymentController::class, 'sendReminder'])->middleware('role:owner,admin');

    // Maintenance Requests
    Route::get('/maintenance-requests', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'index']);
    Route::post('/maintenance-requests', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'store'])->middleware('role:tenant,owner');
    Route::get('/maintenance-requests/{id}', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'show']);
    Route::put('/maintenance-requests/{id}', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'update'])->middleware('role:owner,admin');
    Route::post('/maintenance-requests/{id}/assign', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'assign'])->middleware('role:owner,admin');
    Route::post('/maintenance-requests/{id}/assign-service-provider', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'assignServiceProvider'])->middleware('role:owner,admin');
    Route::post('/maintenance-requests/{id}/complete', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'complete'])->middleware('role:owner,admin');
    Route::delete('/maintenance-requests/{id}', [\App\Http\Controllers\Api\MaintenanceRequestController::class, 'destroy'])->middleware('role:owner,admin');

    // Service Providers
    Route::get('/service-providers', [\App\Http\Controllers\Api\ServiceProviderController::class, 'index']);
    Route::post('/service-providers', [\App\Http\Controllers\Api\ServiceProviderController::class, 'store'])->middleware('role:owner,admin');
    Route::get('/service-providers/{serviceProvider}', [\App\Http\Controllers\Api\ServiceProviderController::class, 'show']);
    Route::put('/service-providers/{serviceProvider}', [\App\Http\Controllers\Api\ServiceProviderController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/service-providers/{serviceProvider}', [\App\Http\Controllers\Api\ServiceProviderController::class, 'destroy'])->middleware('role:admin');
    Route::post('/service-providers/{serviceProvider}/verify', [\App\Http\Controllers\Api\ServiceProviderController::class, 'verify'])->middleware('role:admin');
    Route::post('/service-providers/{serviceProvider}/check-availability', [\App\Http\Controllers\Api\ServiceProviderController::class, 'checkAvailability']);
    Route::get('/service-providers/{serviceProvider}/stats', [\App\Http\Controllers\Api\ServiceProviderController::class, 'stats']);

    // Cleaning Services
    Route::get('/cleaning-services', [\App\Http\Controllers\Api\CleaningServiceController::class, 'index']);
    Route::post('/cleaning-services', [\App\Http\Controllers\Api\CleaningServiceController::class, 'store'])->middleware('role:owner,admin');
    Route::get('/cleaning-services/{cleaningService}', [\App\Http\Controllers\Api\CleaningServiceController::class, 'show']);
    Route::put('/cleaning-services/{cleaningService}', [\App\Http\Controllers\Api\CleaningServiceController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/cleaning-services/{cleaningService}', [\App\Http\Controllers\Api\CleaningServiceController::class, 'destroy'])->middleware('role:owner,admin');
    Route::post('/cleaning-services/{cleaningService}/start', [\App\Http\Controllers\Api\CleaningServiceController::class, 'start']);
    Route::post('/cleaning-services/{cleaningService}/complete', [\App\Http\Controllers\Api\CleaningServiceController::class, 'complete']);
    Route::post('/cleaning-services/{cleaningService}/cancel', [\App\Http\Controllers\Api\CleaningServiceController::class, 'cancel']);
    Route::post('/cleaning-services/{cleaningService}/rate', [\App\Http\Controllers\Api\CleaningServiceController::class, 'rate']);
    Route::get('/properties/{property}/cleaning-history', [\App\Http\Controllers\Api\CleaningServiceController::class, 'history']);

    // IoT Device Routes
    Route::get('/properties/{property}/iot-devices', [\App\Http\Controllers\Api\IoTDeviceController::class, 'propertyDevices']);
    Route::get('/iot-devices/{device}', [\App\Http\Controllers\Api\IoTDeviceController::class, 'show']);
    Route::post('/iot-devices/{device}/command', [\App\Http\Controllers\Api\IoTDeviceController::class, 'sendCommand']);
    Route::get('/iot-devices/{device}/history', [\App\Http\Controllers\Api\IoTDeviceController::class, 'deviceHistory']);
    Route::get('/iot-devices/{device}/commands', [\App\Http\Controllers\Api\IoTDeviceController::class, 'commandHistory']);
    Route::post('/iot-devices/{device}/thermostat', [\App\Http\Controllers\Api\IoTDeviceController::class, 'controlThermostat']);
    Route::post('/iot-devices/{device}/light', [\App\Http\Controllers\Api\IoTDeviceController::class, 'controlLight']);
    Route::get('/iot-devices/{device}/camera/stream', [\App\Http\Controllers\Api\IoTDeviceController::class, 'getCameraStream']);

    // Concierge Services
    Route::get('/concierge-services', [\App\Http\Controllers\Api\V1\ConciergeServiceController::class, 'index']);
    Route::get('/concierge-services/types', [\App\Http\Controllers\Api\V1\ConciergeServiceController::class, 'types']);
    Route::get('/concierge-services/featured', [\App\Http\Controllers\Api\V1\ConciergeServiceController::class, 'featured']);
    Route::get('/concierge-services/{service}', [\App\Http\Controllers\Api\V1\ConciergeServiceController::class, 'show']);

    // Concierge Bookings
    Route::get('/concierge-bookings', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'index']);
    Route::post('/concierge-bookings', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'store']);
    Route::get('/concierge-bookings/stats', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'stats']);
    Route::get('/concierge-bookings/{booking}', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'show']);
    Route::put('/concierge-bookings/{booking}', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'update']);
    Route::post('/concierge-bookings/{booking}/cancel', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'cancel']);
    Route::post('/concierge-bookings/{booking}/review', [\App\Http\Controllers\Api\V1\ConciergeBookingController::class, 'addReview']);

    // Guest Screening
    Route::get('/guest-screenings', [GuestScreeningController::class, 'index']);
    Route::post('/guest-screenings', [GuestScreeningController::class, 'store'])->middleware('role:owner,admin');
    Route::get('/guest-screenings/{id}', [GuestScreeningController::class, 'show']);
    Route::put('/guest-screenings/{id}', [GuestScreeningController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/guest-screenings/{id}', [GuestScreeningController::class, 'destroy'])->middleware('role:admin');
    Route::post('/guest-screenings/{id}/verify-identity', [GuestScreeningController::class, 'verifyIdentity'])->middleware('role:admin');
    Route::post('/guest-screenings/{id}/verify-phone', [GuestScreeningController::class, 'verifyPhone'])->middleware('role:admin');
    Route::post('/guest-screenings/{id}/calculate-score', [GuestScreeningController::class, 'calculateScore'])->middleware('role:owner,admin');
    Route::get('/guest-screenings/statistics/all', [GuestScreeningController::class, 'statistics'])->middleware('role:admin');
    Route::get('/guest-screenings/user/{userId}', [GuestScreeningController::class, 'getUserScreenings']);
    Route::get('/guest-screenings/user/{userId}/latest', [GuestScreeningController::class, 'getLatestScreening']);

    // Credit Checks
    Route::get('/credit-checks', [CreditCheckController::class, 'index']);
    Route::post('/credit-checks', [CreditCheckController::class, 'store'])->middleware('role:owner,admin');
    Route::get('/credit-checks/{id}', [CreditCheckController::class, 'show']);
    Route::put('/credit-checks/{id}', [CreditCheckController::class, 'update'])->middleware('role:admin');
    Route::delete('/credit-checks/{id}', [CreditCheckController::class, 'destroy'])->middleware('role:admin');
    Route::post('/credit-checks/{id}/simulate', [CreditCheckController::class, 'simulateCheck'])->middleware('role:admin');
    Route::get('/credit-checks/user/{userId}', [CreditCheckController::class, 'getUserCreditChecks']);
    Route::get('/credit-checks/user/{userId}/latest', [CreditCheckController::class, 'getLatestCreditCheck']);

    // Guest References
    Route::get('/guest-references', [GuestReferenceController::class, 'index']);
    Route::post('/guest-references', [GuestReferenceController::class, 'store'])->middleware('role:tenant,owner,admin');
    Route::get('/guest-references/{id}', [GuestReferenceController::class, 'show']);
    Route::put('/guest-references/{id}', [GuestReferenceController::class, 'update'])->middleware('role:owner,admin');
    Route::delete('/guest-references/{id}', [GuestReferenceController::class, 'destroy'])->middleware('role:owner,admin');

    // Loyalty Program
    Route::get('/loyalty', [\App\Http\Controllers\Api\LoyaltyController::class, 'index']);
    Route::get('/loyalty/tiers', [\App\Http\Controllers\Api\LoyaltyController::class, 'tiers']);
    Route::get('/loyalty/transactions', [\App\Http\Controllers\Api\LoyaltyController::class, 'transactions']);
    Route::post('/loyalty/redeem', [\App\Http\Controllers\Api\LoyaltyController::class, 'redeem']);
    Route::post('/loyalty/calculate-discount', [\App\Http\Controllers\Api\LoyaltyController::class, 'calculateDiscount']);
    Route::get('/loyalty/leaderboard', [\App\Http\Controllers\Api\LoyaltyController::class, 'leaderboard']);
    Route::post('/loyalty/claim-birthday', [\App\Http\Controllers\Api\LoyaltyController::class, 'claimBirthdayBonus']);
    Route::get('/loyalty/expiring-points', [\App\Http\Controllers\Api\LoyaltyController::class, 'expiringPoints']);
    Route::get('/loyalty/tiers/{tierId}/benefits', [\App\Http\Controllers\Api\LoyaltyController::class, 'tierBenefits']);

    // Referral Program
    Route::get('/referrals', [\App\Http\Controllers\Api\ReferralController::class, 'index']);
    Route::get('/referrals/code', [\App\Http\Controllers\Api\ReferralController::class, 'getCode']);
    Route::post('/referrals/regenerate', [\App\Http\Controllers\Api\ReferralController::class, 'regenerateCode']);
    Route::post('/referrals/create', [\App\Http\Controllers\Api\ReferralController::class, 'create']);
    Route::get('/referrals/discount', [\App\Http\Controllers\Api\ReferralController::class, 'getDiscount']);
    Route::post('/referrals/apply-discount', [\App\Http\Controllers\Api\ReferralController::class, 'applyDiscount']);
    Route::get('/referrals/leaderboard', [\App\Http\Controllers\Api\ReferralController::class, 'leaderboard']);

    // Automated Messaging
    Route::prefix('messaging')->group(function () {
        // Message Templates
        Route::get('/templates', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'getTemplates']);
        Route::get('/templates/defaults', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'getDefaultTemplates']);
        Route::post('/templates', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'createTemplate']);
        Route::put('/templates/{template}', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'updateTemplate']);
        Route::delete('/templates/{template}', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'deleteTemplate']);
        Route::post('/templates/{template}/preview', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'previewTemplate']);

        // Scheduled Messages
        Route::get('/scheduled', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'getScheduledMessages']);
        Route::post('/scheduled', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'createScheduledMessage']);
        Route::post('/scheduled/{message}/cancel', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'cancelScheduledMessage']);

        // Auto-Responses
        Route::get('/auto-responses', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'getAutoResponses']);
        Route::post('/auto-responses', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'createAutoResponse']);
        Route::put('/auto-responses/{autoResponse}', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'updateAutoResponse']);
        Route::delete('/auto-responses/{autoResponse}', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'deleteAutoResponse']);

        // Smart Replies
        Route::get('/messages/{message}/suggestions', [\App\Http\Controllers\Api\AutomatedMessagingController::class, 'getSuggestedReplies']);
    });
    Route::post('/guest-references/{id}/send-request', [GuestReferenceController::class, 'sendRequest'])->middleware('role:owner,admin');
    Route::post('/guest-references/{id}/resend-request', [GuestReferenceController::class, 'resendRequest'])->middleware('role:owner,admin');
    Route::post('/guest-references/{id}/mark-verified', [GuestReferenceController::class, 'markAsVerified'])->middleware('role:admin');
    Route::get('/guest-references/screening/{screeningId}', [GuestReferenceController::class, 'getScreeningReferences']);
});

// Public Guest Reference Response (no auth)
Route::prefix('v1')->group(function () {
    Route::get('/guest-references/verify/{code}', [GuestReferenceController::class, 'getByCode']);
    Route::post('/guest-references/verify/{code}', [GuestReferenceController::class, 'submitResponse']);
});

// Public iCal export (no auth required)
Route::get('/v1/properties/{property}/ical', [ExternalCalendarController::class, 'exportICal'])->name('ical.property');

// Google Calendar webhook (no auth required - verified by channel ID)
Route::post('/v1/google-calendar/webhook', [\App\Http\Controllers\Api\GoogleCalendarController::class, 'webhook'])->name('api.google-calendar.webhook');

// Loyalty Program routes (Task 4.6)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Loyalty Program - User Endpoints
    Route::prefix('loyalty')->group(function () {
        Route::get('/points', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getPoints']);
        Route::get('/points/history', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getPointsHistory']);
        Route::post('/points/redeem', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'redeemPoints']);
        Route::get('/points/expiring', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getExpiringPoints']);
        Route::post('/points/calculate', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'calculateValue']);
        Route::get('/tiers', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getTiers']);
        Route::get('/tiers/{slug}', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getTierDetails']);
    });

    // Loyalty Program - Admin Endpoints
    Route::prefix('admin/loyalty')->middleware('role:admin')->group(function () {
        Route::post('/award-points', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'awardPoints']);
        Route::post('/adjust-points', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'adjustPoints']);
        Route::get('/leaderboard', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'getLeaderboard']);
        Route::get('/statistics', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'getStatistics']);
        Route::get('/users/{userId}', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'getUserLoyalty']);
        Route::post('/expire-points', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'expirePoints']);
        Route::get('/expiring-soon', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'getUsersWithExpiringPoints']);
        Route::post('/birthday-bonuses', [\App\Http\Controllers\Api\V1\Admin\LoyaltyAdminController::class, 'awardBirthdayBonuses']);
    });
});

// Public Loyalty Tiers (no auth required)
Route::prefix('v1')->group(function () {
    Route::get('/loyalty/tiers', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getTiers']);
    Route::get('/loyalty/tiers/{slug}', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'getTierDetails']);
});

// Dashboard Analytics Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner/dashboard')->group(function () {
        Route::get('/stats', [App\Http\Controllers\Api\V1\OwnerDashboardController::class, 'stats']);
        Route::get('/revenue', [App\Http\Controllers\Api\V1\OwnerDashboardController::class, 'revenue']);
        Route::get('/properties', [App\Http\Controllers\Api\V1\OwnerDashboardController::class, 'properties']);
    });

    Route::prefix('tenant/dashboard')->group(function () {
        Route::get('/stats', [App\Http\Controllers\Api\V1\TenantDashboardController::class, 'stats']);
    });
});

// Social Authentication Routes
Route::prefix('auth/social')->group(function () {
    Route::get('{provider}/redirect', [App\Http\Controllers\Api\SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [App\Http\Controllers\Api\SocialAuthController::class, 'callback']);
    Route::post('{provider}/link', [App\Http\Controllers\Api\SocialAuthController::class, 'link'])->middleware('auth:sanctum');
    Route::delete('{provider}/unlink', [App\Http\Controllers\Api\SocialAuthController::class, 'unlink'])->middleware('auth:sanctum');
});

// Dashboard Routes
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\DashboardController::class, 'index']);
    Route::get('/revenue', [App\Http\Controllers\Api\DashboardController::class, 'revenue']);
    Route::get('/bookings', [App\Http\Controllers\Api\DashboardController::class, 'bookings']);
    Route::get('/properties', [App\Http\Controllers\Api\DashboardController::class, 'properties']);
});

// Multi-Currency Routes
Route::prefix('currency')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\MultiCurrencyController::class, 'index']);
    Route::get('/rates', [App\Http\Controllers\Api\MultiCurrencyController::class, 'rates']);
    Route::post('/convert', [App\Http\Controllers\Api\MultiCurrencyController::class, 'convert']);
});
