# RentHub - Complete Routes & Forms Inventory

**Generated**: 2025-11-14  
**Purpose**: Comprehensive documentation of all routes, pages, API endpoints, and forms in the RentHub application.

---

## Table of Contents

1. [Frontend Routes](#frontend-routes)
2. [Frontend Forms](#frontend-forms)
3. [Backend API Routes](#backend-api-routes)
4. [Backend Web Routes](#backend-web-routes)
5. [Forms Validation Rules](#forms-validation-rules)

---

## Frontend Routes

### Public Pages

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/` | `app/page.tsx` | Homepage | Property search | No |
| `/about` | `app/about/page.tsx` | About page | - | No |
| `/contact` | `app/contact/page.tsx` | Contact form | Contact form | No |
| `/faq` | `app/faq/page.tsx` | FAQ page | - | No |
| `/help` | `app/help/page.tsx` | Help center | - | No |
| `/press` | `app/press/page.tsx` | Press kit | - | No |
| `/careers` | `app/careers/page.tsx` | Careers | - | No |
| `/privacy` | `app/privacy/page.tsx` | Privacy policy | - | No |
| `/terms` | `app/terms/page.tsx` | Terms of service | - | No |
| `/cookies` | `app/cookies/page.tsx` | Cookie policy | - | No |

### Authentication Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/auth/login` | `app/auth/login/page.tsx` | User login | Login form | No |
| `/auth/register` | `app/auth/register/page.tsx` | User registration | Registration form | No |
| `/auth/forgot-password` | `app/auth/forgot-password/page.tsx` | Password reset request | Email form | No |
| `/auth/reset-password` | `app/auth/reset-password/page.tsx` | Password reset | New password form | No |
| `/auth/callback` | `app/auth/callback/page.tsx` | OAuth callback | - | No |

### Property Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/properties` | `app/properties/page.tsx` | Property listing | Search & filters | No |
| `/properties/[id]` | `app/properties/[id]/page.tsx` | Property details | Booking request | No |
| `/properties/[id]/reviews` | `app/properties/[id]/reviews/page.tsx` | Property reviews | Review submission | Yes |
| `/properties/[id]/calendar` | `app/properties/[id]/calendar/page.tsx` | Availability calendar | Date blocking | Yes (Owner) |
| `/properties/[id]/analytics` | `app/properties/[id]/analytics/page.tsx` | Property analytics | - | Yes (Owner) |
| `/properties/[id]/maintenance` | `app/properties/[id]/maintenance/page.tsx` | Maintenance requests | Maintenance form | Yes (Owner) |
| `/properties/[id]/smart-locks` | `app/properties/[id]/smart-locks/page.tsx` | Smart lock management | Lock controls | Yes (Owner) |
| `/properties/[id]/access` | `app/properties/[id]/access/page.tsx` | Access codes | Code management | Yes (Owner) |

### Dashboard Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/dashboard` | `app/dashboard/page.tsx` | Main dashboard | - | Yes |
| `/dashboard/owner` | `app/dashboard/owner/page.tsx` | Owner dashboard | - | Yes (Owner) |
| `/dashboard/properties` | `app/dashboard/properties/page.tsx` | User properties list | - | Yes |
| `/dashboard/properties/new` | `app/dashboard/properties/new/page.tsx` | Create property | Property form | Yes |
| `/dashboard/properties/[id]` | `app/dashboard/properties/[id]/page.tsx` | Edit property | Property form | Yes (Owner) |
| `/dashboard/settings` | `app/dashboard/settings/page.tsx` | User settings | Settings form | Yes |

### Booking Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/bookings` | `app/bookings/page.tsx` | Bookings list | Filters | Yes |
| `/bookings/[id]` | `app/bookings/[id]/page.tsx` | Booking details | - | Yes |
| `/bookings/[id]/payment` | `app/bookings/[id]/payment/page.tsx` | Payment page | Payment form | Yes |

### Host Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/host` | `app/host/page.tsx` | Host landing | - | No |
| `/host/properties` | `app/host/properties/page.tsx` | Host properties | - | Yes |
| `/host/properties/new` | `app/host/properties/new/page.tsx` | Create listing | Property form | Yes |
| `/host/ratings` | `app/host/ratings/page.tsx` | Host ratings | - | Yes |

### Messaging Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/messages` | `app/messages/page.tsx` | Messages inbox | - | Yes |
| `/messages/[id]` | `app/messages/[id]/page.tsx` | Conversation | Message form | Yes |

### User Profile Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/profile` | `app/profile/page.tsx` | User profile | Profile form | Yes |
| `/profile/verification` | `app/profile/verification/page.tsx` | Identity verification | Verification form | Yes |
| `/verification` | `app/verification/page.tsx` | Verification center | Document upload | Yes |

### Payment & Financial Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/payments` | `app/payments/page.tsx` | Payments overview | - | Yes |
| `/payments/history` | `app/payments/history/page.tsx` | Payment history | Filters | Yes |
| `/invoices` | `app/invoices/page.tsx` | Invoices list | - | Yes |

### Feature Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/favorites` | `app/favorites/page.tsx` | Favorite properties | - | Yes |
| `/wishlists` | `app/wishlists/page.tsx` | Wishlists | Wishlist management | Yes |
| `/saved-searches` | `app/saved-searches/page.tsx` | Saved searches | Search criteria | Yes |
| `/property-comparison` | `app/property-comparison/page.tsx` | Compare properties | - | No |
| `/notifications` | `app/notifications/page.tsx` | Notifications | Settings | Yes |
| `/referrals` | `app/referrals/page.tsx` | Referral program | - | Yes |
| `/loyalty` | `app/loyalty/page.tsx` | Loyalty program | - | Yes |
| `/insurance` | `app/insurance/page.tsx` | Insurance info | Quote request | No |
| `/screening` | `app/screening/page.tsx` | Guest screening | Screening form | Yes |
| `/security` | `app/security/page.tsx` | Security center | Security settings | Yes |
| `/security/audit` | `app/security/audit/page.tsx` | Security audit log | - | Yes |

### Integration Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/integrations` | `app/integrations/page.tsx` | Integrations hub | - | Yes |
| `/integrations/google-calendar` | `app/integrations/google-calendar/page.tsx` | Google Calendar | OAuth flow | Yes |
| `/integrations/stripe` | `app/integrations/stripe/page.tsx` | Stripe | Payment setup | Yes |
| `/integrations/realtime` | `app/integrations/realtime/page.tsx` | Real-time demo | - | Yes |
| `/calendar-sync` | `app/calendar-sync/page.tsx` | Calendar sync | Calendar settings | Yes |

### Admin Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/admin` | `app/admin/page.tsx` | Admin panel | - | Yes (Admin) |
| `/admin/settings` | `app/admin/settings/page.tsx` | Admin settings | Settings form | Yes (Admin) |
| `/analytics` | `app/analytics/page.tsx` | Analytics | - | Yes (Admin/Owner) |

### Demo & Utility Routes

| Route | File | Purpose | Forms | Auth Required |
|-------|------|---------|-------|---------------|
| `/demo` | `app/demo/page.tsx` | Demo hub | - | No |
| `/demo/accessibility` | `app/demo/accessibility/page.tsx` | Accessibility demo | - | No |
| `/demo/form-validation` | `app/demo/form-validation/page.tsx` | Form validation demo | Demo form | No |
| `/demo/i18n` | `app/demo/i18n/page.tsx` | Internationalization | - | No |
| `/demo/image-optimization` | `app/demo/image-optimization/page.tsx` | Image optimization | - | No |
| `/demo/logger` | `app/demo/logger/page.tsx` | Logger demo | - | No |
| `/demo/optimistic-ui` | `app/demo/optimistic-ui/page.tsx` | Optimistic UI demo | - | No |
| `/demo/performance` | `app/demo/performance/page.tsx` | Performance demo | - | No |
| `/offline` | `app/offline/page.tsx` | Offline page (PWA) | - | No |
| `/settings` | `app/settings/page.tsx` | Settings | Settings form | Yes |

### API Routes (Frontend)

| Route | File | Purpose | Method |
|-------|------|---------|--------|
| `/api/locale/detect` | `app/api/locale/detect/route.ts` | Detect user locale | GET |
| `/api/manifest` | `app/api/manifest/route.ts` | PWA manifest | GET |

---

## Frontend Forms

### 1. Property Search Form (Homepage)
**Location**: `/` (app/page.tsx)

**Fields**:
- Location (text input with autocomplete)
- Check-in date (date picker)
- Check-out date (date picker)
- Guests (number input)
- Property type (select dropdown)
- Price range (range slider)

**Validation**:
- Location: Optional
- Check-in: Must be today or future
- Check-out: Must be after check-in
- Guests: Min 1, max 20
- Price: Min 0

### 2. Login Form
**Location**: `/auth/login`

**Fields**:
- Email (email input)
- Password (password input)
- Remember me (checkbox)

**Validation**:
- Email: Required, valid email format
- Password: Required, min 8 characters

### 3. Registration Form
**Location**: `/auth/register`

**Fields**:
- First name (text input)
- Last name (text input)
- Email (email input)
- Password (password input)
- Confirm password (password input)
- Phone (tel input)
- Terms acceptance (checkbox)

**Validation**:
- First name: Required, min 2 chars
- Last name: Required, min 2 chars
- Email: Required, valid email, unique
- Password: Required, min 8 chars, must include uppercase, lowercase, number
- Confirm password: Must match password
- Phone: Optional, valid phone format
- Terms: Required (must be checked)

### 4. Property Creation/Edit Form
**Location**: `/dashboard/properties/new`, `/dashboard/properties/[id]`

**Fields**:
- Title (text input)
- Description (textarea)
- Property type (select)
- Address (text input)
- City (text input)
- State/Province (text input)
- Postal code (text input)
- Country (select)
- Price per night (number input)
- Bedrooms (number input)
- Bathrooms (number input)
- Max guests (number input)
- Amenities (multi-select checkboxes)
- Images (file upload, multiple)
- House rules (textarea)
- Cancellation policy (select)

**Validation**:
- Title: Required, max 100 chars
- Description: Required, max 1000 chars
- Property type: Required
- Address: Required
- City: Required
- Country: Required
- Price: Required, min 1
- Bedrooms: Required, min 1
- Bathrooms: Required, min 1
- Max guests: Required, min 1
- Images: At least 1 required, max 10, each max 5MB

### 5. Booking Request Form
**Location**: `/properties/[id]`

**Fields**:
- Check-in date (date picker)
- Check-out date (date picker)
- Guests (number input)
- Special requests (textarea)

**Validation**:
- Check-in: Required, today or future
- Check-out: Required, after check-in
- Guests: Required, max property capacity
- Special requests: Optional, max 500 chars

### 6. Payment Form
**Location**: `/bookings/[id]/payment`

**Fields**:
- Cardholder name (text input)
- Card number (card input, Stripe Element)
- Expiry date (card expiry, Stripe Element)
- CVC (card CVC, Stripe Element)
- Billing address (address input)

**Validation**:
- All fields required
- Stripe handles card validation

### 7. Review Form
**Location**: `/properties/[id]/reviews`

**Fields**:
- Rating (star rating, 1-5)
- Title (text input)
- Review text (textarea)
- Cleanliness rating (star rating)
- Communication rating (star rating)
- Check-in rating (star rating)
- Accuracy rating (star rating)
- Location rating (star rating)
- Value rating (star rating)

**Validation**:
- Rating: Required, 1-5
- Title: Required, max 100 chars
- Review text: Required, min 50 chars, max 1000 chars
- Category ratings: Optional, 1-5

### 8. Message Form
**Location**: `/messages/[id]`

**Fields**:
- Message text (textarea)
- Attachments (file upload, optional)

**Validation**:
- Message text: Required, max 2000 chars
- Attachments: Optional, max 3 files, each max 10MB

### 9. Profile Update Form
**Location**: `/profile`

**Fields**:
- Profile photo (file upload)
- First name (text input)
- Last name (text input)
- Phone (tel input)
- Bio (textarea)
- Language preference (select)
- Currency preference (select)
- Timezone (select)

**Validation**:
- First name: Required, min 2 chars
- Last name: Required, min 2 chars
- Phone: Optional, valid format
- Bio: Optional, max 500 chars

### 10. Contact Form
**Location**: `/contact`

**Fields**:
- Name (text input)
- Email (email input)
- Subject (text input)
- Message (textarea)
- Category (select)

**Validation**:
- Name: Required
- Email: Required, valid email
- Subject: Required, max 100 chars
- Message: Required, min 20 chars, max 1000 chars
- Category: Required

---

## Backend API Routes

### Health & Monitoring

```
GET  /api/health
GET  /api/health/liveness
GET  /api/health/readiness
GET  /api/metrics
GET  /api/metrics/prometheus
GET  /api/health/production
GET  /api/health/production/logs
GET  /api/health/status
```

### Authentication (v1)

```
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout (auth)
POST /api/v1/auth/refresh (auth)
POST /api/v1/auth/forgot-password
POST /api/v1/auth/reset-password
GET  /api/v1/auth/user (auth)
POST /api/v1/auth/verify-email (auth)
POST /api/v1/auth/resend-verification (auth)
GET  /api/v1/auth/social/{provider}
GET  /api/v1/auth/social/{provider}/callback
```

### Properties

```
GET    /api/v1/properties
GET    /api/v1/properties/{id}
POST   /api/v1/properties (auth)
PUT    /api/v1/properties/{id} (auth, owner)
DELETE /api/v1/properties/{id} (auth, owner)
GET    /api/v1/properties/{id}/availability
POST   /api/v1/properties/{id}/verify (auth)
GET    /api/v1/properties/search
GET    /api/v1/properties/map-search
GET    /api/properties (with X-API-Version: v1 header)
GET    /api/v{version}/properties (versioned)
```

### Bookings

```
GET    /api/v1/bookings (auth)
GET    /api/v1/bookings/{id} (auth)
POST   /api/v1/bookings (auth)
PUT    /api/v1/bookings/{id} (auth)
DELETE /api/v1/bookings/{id} (auth)
POST   /api/v1/bookings/{id}/cancel (auth)
POST   /api/v1/bookings/{id}/confirm (auth, owner)
```

### Payments

```
POST   /api/v1/payments (auth)
GET    /api/v1/payments/{id} (auth)
POST   /api/v1/payments/{id}/refund (auth, admin)
POST   /api/v1/payment-proofs (auth)
GET    /api/v1/payment-proofs/{id} (auth)
```

### Reviews

```
GET    /api/v1/properties/{id}/reviews
POST   /api/v1/bookings/{id}/reviews (auth)
PUT    /api/v1/reviews/{id} (auth)
DELETE /api/v1/reviews/{id} (auth)
POST   /api/v1/reviews/{id}/helpful (auth)
GET    /api/v1/reviews/{id}/responses (auth)
POST   /api/v1/reviews/{id}/responses (auth, owner)
```

### Messages

```
GET    /api/v1/conversations (auth)
GET    /api/v1/conversations/{id} (auth)
POST   /api/v1/conversations (auth)
POST   /api/v1/conversations/{id}/messages (auth)
PUT    /api/v1/messages/{id} (auth)
DELETE /api/v1/messages/{id} (auth)
POST   /api/v1/messages/{id}/read (auth)
```

### User & Profile

```
GET    /api/v1/user (auth)
PUT    /api/v1/user (auth)
POST   /api/v1/user/verify (auth)
GET    /api/v1/user/dashboard (auth)
GET    /api/v1/user/bookings (auth)
GET    /api/v1/user/properties (auth)
```

### Favorites

```
GET    /api/v1/favorites (auth)
POST   /api/v1/favorites (auth)
DELETE /api/v1/favorites/{id} (auth)
GET    /api/v1/favorites/check/{propertyId} (auth)
```

### Wishlists

```
GET    /api/v1/wishlists (auth)
POST   /api/v1/wishlists (auth)
PUT    /api/v1/wishlists/{id} (auth)
DELETE /api/v1/wishlists/{id} (auth)
POST   /api/v1/wishlists/{id}/items (auth)
DELETE /api/v1/wishlists/{id}/items/{itemId} (auth)
```

### Calendar & Integrations

```
GET    /api/v1/calendars (auth)
POST   /api/v1/calendars (auth)
DELETE /api/v1/calendars/{id} (auth)
POST   /api/v1/calendars/sync (auth)
GET    /api/v1/integrations (auth)
POST   /api/v1/integrations/{provider} (auth)
DELETE /api/v1/integrations/{provider} (auth)
```

### Settings

```
GET    /api/v1/settings/public
GET    /api/v1/settings (auth)
PUT    /api/v1/settings (auth, admin)
```

### Languages & Localization

```
GET    /api/v1/languages
GET    /api/v1/languages/default
GET    /api/v1/languages/{code}
GET    /api/v1/translations/{lang}
```

### Currencies

```
GET    /api/v1/currencies
GET    /api/v1/currencies/rates
GET    /api/v1/currencies/convert
```

### Amenities

```
GET    /api/v1/amenities
GET    /api/v1/amenities/{id}
```

### Admin Routes

```
GET    /api/admin/queues (auth, admin)
POST   /api/admin/queues/failed/{id}/retry (auth, admin)
DELETE /api/admin/queues/failed (auth, admin)
```

---

## Backend Web Routes

### Public Routes

```
GET  /
```

### Admin Routes

```
GET  /admin/login
POST /admin/login
GET  /admin/dashboard (auth)
GET  /admin (auth)
POST /admin/logout (auth)
GET  /admin/users (auth)
GET  /admin/properties (auth)
GET  /admin/bookings (auth)
GET  /admin/settings (auth)
```

### Filament Routes

Filament auto-generates extensive admin panel routes under `/admin/*` including:
- Resource CRUD operations
- Relation managers
- Custom pages
- Dashboard widgets
- Notifications
- Profile management

---

## Forms Validation Rules

### Common Validation Patterns

**Email**:
- Required: `required`
- Format: `email`
- Unique: `unique:users,email`
- Max length: `max:255`

**Password**:
- Required: `required`
- Min length: `min:8`
- Confirmed: `confirmed`
- Complexity: `regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/`

**Phone**:
- Format: `regex:/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/`

**Price**:
- Required: `required`
- Numeric: `numeric`
- Min: `min:0`
- Max: `max:999999`

**Date**:
- Required: `required`
- Format: `date`
- After: `after:today`
- Before: `before:check_out_date`

**File Upload**:
- Required: `required`
- Type: `mimes:jpeg,png,jpg`
- Max size: `max:5120` (5MB in KB)

**Text**:
- Required: `required`
- String: `string`
- Min length: `min:2`
- Max length: `max:255`

---

**Last Updated**: 2025-11-14  
**Maintained by**: Development Team
