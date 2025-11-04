# Task 1.7 - Notification System Implementation Plan

## 📋 Overview

**Obiectiv**: Implementare sistem complet de notificări pentru RentHub
**Tehnologii**: Laravel Notifications + Database + Email + (optional) SMS & Push
**Status**: 🚧 În Implementare

---

## 🎯 Feature Requirements

### 1.7.1 Notification Types

#### A. Email Notifications ✉️
- [ ] **Booking Notifications**
  - New booking request (→ Owner)
  - Booking confirmed (→ Tenant)
  - Booking rejected (→ Tenant)
  - Booking cancelled (→ Owner & Tenant)
  - Booking check-in reminder (→ Tenant)
  - Booking check-out reminder (→ Tenant)
  
- [ ] **Payment Notifications**
  - Payment received (→ Owner & Tenant)
  - Payment failed (→ Tenant)
  - Invoice generated (→ Tenant) ✅ DEJA IMPLEMENTAT
  - Receipt sent (→ Tenant)
  - Payout processed (→ Owner)
  
- [ ] **Review Notifications**
  - New review received (→ Owner)
  - Owner responded to review (→ Tenant)
  - Review reminder after checkout (→ Tenant)
  
- [ ] **Account Notifications**
  - Welcome email (→ New User)
  - Email verification (→ New User)
  - Password reset (→ User)
  - Profile updated (→ User)
  - Account verified (→ Owner)

#### B. In-App Notifications 🔔
- [ ] Real-time notification bell icon
- [ ] Unread notification counter
- [ ] Notification list with pagination
- [ ] Mark as read/unread
- [ ] Delete notifications
- [ ] Notification preferences

#### C. SMS Notifications 📱 (Optional - Phase 2)
- [ ] Booking confirmation SMS
- [ ] Check-in reminder SMS
- [ ] Payment confirmation SMS
- [ ] Emergency notifications

#### D. Push Notifications 📲 (Optional - Phase 2)
- [ ] Browser push notifications
- [ ] Mobile app push notifications
- [ ] Real-time updates

---

## 🏗️ Architecture

### Database Tables

#### 1. `notifications` table (Laravel default)
```sql
- id (uuid)
- type (string) - notification class name
- notifiable_type (string) - User model
- notifiable_id (bigint) - user_id
- data (json) - notification data
- read_at (timestamp nullable)
- created_at, updated_at
```

#### 2. `notification_preferences` table (custom)
```sql
- id
- user_id (FK)
- notification_type (string) - booking, payment, review, account
- channel_email (boolean) - default true
- channel_database (boolean) - default true
- channel_sms (boolean) - default false
- channel_push (boolean) - default false
- created_at, updated_at
```

### Laravel Notifications Structure

```
app/Notifications/
├── Booking/
│   ├── BookingRequestNotification.php
│   ├── BookingConfirmedNotification.php
│   ├── BookingRejectedNotification.php
│   ├── BookingCancelledNotification.php
│   ├── CheckInReminderNotification.php
│   └── CheckOutReminderNotification.php
├── Payment/
│   ├── PaymentReceivedNotification.php
│   ├── PaymentFailedNotification.php
│   ├── InvoiceGeneratedNotification.php ✅ EXISTS
│   ├── ReceiptSentNotification.php
│   └── PayoutProcessedNotification.php
├── Review/
│   ├── NewReviewNotification.php
│   ├── ReviewResponseNotification.php
│   └── ReviewReminderNotification.php
└── Account/
    ├── WelcomeNotification.php
    ├── EmailVerificationNotification.php
    ├── PasswordResetNotification.php
    └── AccountVerifiedNotification.php
```

---

## 📝 Implementation Steps

### Phase 1: Core Setup (1-2 ore)

#### Step 1.1: Database & Models
```bash
# Create notifications table (Laravel default)
php artisan notifications:table
php artisan migrate

# Create notification preferences
php artisan make:migration create_notification_preferences_table
php artisan make:model NotificationPreference -m
```

#### Step 1.2: Base Notification Classes
```bash
# Create base booking notifications
php artisan make:notification Booking/BookingRequestNotification
php artisan make:notification Booking/BookingConfirmedNotification
php artisan make:notification Booking/BookingRejectedNotification
php artisan make:notification Booking/BookingCancelledNotification

# Create payment notifications
php artisan make:notification Payment/PaymentReceivedNotification
php artisan make:notification Payment/PaymentFailedNotification

# Create review notifications
php artisan make:notification Review/NewReviewNotification
php artisan make:notification Review/ReviewResponseNotification

# Create account notifications
php artisan make:notification Account/WelcomeNotification
```

#### Step 1.3: Notification Service
```php
app/Services/NotificationService.php
- sendBookingNotification()
- sendPaymentNotification()
- sendReviewNotification()
- getUserPreferences()
- respectPreferences()
```

---

### Phase 2: API Integration (1 ora)

#### Step 2.1: Notification API Controller
```bash
php artisan make:controller Api/NotificationController
```

**API Endpoints**:
```
GET    /api/v1/notifications              - List user notifications
GET    /api/v1/notifications/unread-count - Get unread count
POST   /api/v1/notifications/{id}/read    - Mark as read
POST   /api/v1/notifications/mark-all-read - Mark all as read
DELETE /api/v1/notifications/{id}         - Delete notification
GET    /api/v1/notifications/preferences  - Get preferences
PUT    /api/v1/notifications/preferences  - Update preferences
```

#### Step 2.2: Real-time (Optional)
```php
# Laravel Broadcasting
php artisan make:event NewNotification
# Configure Pusher/Laravel Echo
```

---

### Phase 3: Email Templates (1-2 ore)

#### Step 3.1: Blade Email Templates
```
resources/views/emails/
├── booking/
│   ├── request.blade.php
│   ├── confirmed.blade.php
│   ├── rejected.blade.php
│   ├── cancelled.blade.php
│   ├── check-in-reminder.blade.php
│   └── check-out-reminder.blade.php
├── payment/
│   ├── received.blade.php
│   ├── failed.blade.php
│   └── receipt.blade.php
├── review/
│   ├── new-review.blade.php
│   ├── review-response.blade.php
│   └── review-reminder.blade.php
└── account/
    ├── welcome.blade.php
    └── account-verified.blade.php
```

#### Step 3.2: Email Design
- Professional branded design
- Responsive HTML
- Clear call-to-action buttons
- Unsubscribe links
- Company branding

---

### Phase 4: Filament Admin (30 min)

#### Step 4.1: Notification Admin Panel
```bash
php artisan make:filament-resource Notification --view
```

**Features**:
- View all notifications
- Filter by user, type, status
- Search functionality
- Resend notifications
- Delete notifications
- Notification analytics

---

### Phase 5: Automation (30 min)

#### Step 5.1: Event Listeners
```php
app/Listeners/
├── SendBookingNotifications.php
├── SendPaymentNotifications.php
└── SendReviewNotifications.php
```

#### Step 5.2: Scheduled Notifications
```php
app/Console/Commands/
├── SendCheckInReminders.php
├── SendCheckOutReminders.php
└── SendReviewReminders.php
```

Register in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Send check-in reminders 24h before
    $schedule->command('notifications:check-in-reminders')->daily();
    
    // Send check-out reminders on checkout day
    $schedule->command('notifications:check-out-reminders')->daily();
    
    // Send review reminders 2 days after checkout
    $schedule->command('notifications:review-reminders')->daily();
}
```

---

## 🎨 Frontend Integration

### Next.js Components

#### Component 1: NotificationBell
```typescript
// components/NotificationBell.tsx
- Display bell icon with unread count
- Dropdown with recent notifications
- "View All" link
```

#### Component 2: NotificationList
```typescript
// components/NotificationList.tsx
- Paginated list of notifications
- Mark as read/unread
- Delete action
- Filter by type
```

#### Component 3: NotificationPreferences
```typescript
// components/NotificationPreferences.tsx
- Toggle email notifications
- Toggle in-app notifications
- Toggle SMS (if enabled)
- Save preferences
```

---

## 📊 Notification Priority System

### Priority Levels
1. **🔴 Critical** (Immediate)
   - Payment failed
   - Booking cancelled
   - Account security alerts

2. **🟡 High** (Within 1 hour)
   - New booking request
   - Booking confirmed
   - Payment received

3. **🟢 Medium** (Within 24 hours)
   - New review
   - Review response
   - Check-in reminder

4. **🔵 Low** (Batch/Scheduled)
   - Marketing emails
   - Monthly summaries
   - Tips & guides

---

## 🔐 Security & Privacy

### GDPR Compliance
- [ ] User consent for notifications
- [ ] Easy unsubscribe mechanism
- [ ] Data retention policy
- [ ] Privacy policy updated
- [ ] Terms of service updated

### Best Practices
- [ ] Rate limiting on notification sending
- [ ] Prevent spam/notification flooding
- [ ] Respect user preferences
- [ ] Secure notification data
- [ ] Audit logging

---

## 🧪 Testing Checklist

### Unit Tests
- [ ] Notification class tests
- [ ] Preference model tests
- [ ] Service method tests

### Integration Tests
- [ ] Email delivery tests
- [ ] Database notification tests
- [ ] API endpoint tests
- [ ] Event listener tests

### Manual Tests
- [ ] Send test notifications
- [ ] Check email delivery
- [ ] Verify in-app notifications
- [ ] Test preferences
- [ ] Test unsubscribe

---

## 📈 Analytics & Monitoring

### Metrics to Track
- Notification delivery rate
- Open rate (emails)
- Click-through rate
- Unsubscribe rate
- Failed deliveries
- Average response time

### Monitoring Tools
- Laravel Telescope (development)
- Laravel Horizon (queue monitoring)
- Email service provider dashboard
- Custom analytics dashboard

---

## 🚀 Deployment Steps

### 1. Environment Setup
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@renthub.com
MAIL_FROM_NAME="${APP_NAME}"

# Optional: SMS (Twilio)
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_FROM=+1234567890

# Optional: Push (Pusher)
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
```

### 2. Queue Configuration
```bash
# Start queue worker
php artisan queue:work --tries=3

# Or use Supervisor (production)
sudo supervisorctl start laravel-worker:*
```

### 3. Cron Jobs (Scheduled Notifications)
```bash
# Add to crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📦 Dependencies

### Required
```json
{
  "laravel/framework": "^11.0",
  "guzzlehttp/guzzle": "^7.0" // for HTTP notifications
}
```

### Optional (SMS)
```bash
composer require twilio/sdk
```

### Optional (Push)
```bash
composer require laravel/echo-server
npm install --save laravel-echo pusher-js
```

---

## 💰 Cost Estimation

### Email Service (Production)
- **SendGrid**: Free tier (100 emails/day), Paid from $15/month
- **Mailgun**: Free tier (10,000 emails/month), Paid from $15/month
- **AWS SES**: $0.10 per 1,000 emails

### SMS Service (Optional)
- **Twilio**: $0.0075 per SMS

### Push Notifications (Optional)
- **Pusher**: Free tier (100 connections), Paid from $49/month
- **OneSignal**: Free tier (unlimited), Paid for advanced features

---

## 🎯 Success Criteria

### Must Have (MVP)
- [x] Email notifications working
- [x] In-app notifications stored in DB
- [x] User notification preferences
- [x] API endpoints for frontend
- [x] Admin panel for management

### Should Have
- [ ] Real-time updates (polling or websockets)
- [ ] Email templates beautifully designed
- [ ] Scheduled notification commands
- [ ] Notification analytics

### Could Have (Future)
- [ ] SMS notifications
- [ ] Push notifications
- [ ] Multi-language support
- [ ] Advanced personalization

---

## 📚 References

- [Laravel Notifications Docs](https://laravel.com/docs/11.x/notifications)
- [Laravel Queue Docs](https://laravel.com/docs/11.x/queues)
- [Laravel Broadcasting Docs](https://laravel.com/docs/11.x/broadcasting)
- [SendGrid API Docs](https://docs.sendgrid.com/)
- [Twilio API Docs](https://www.twilio.com/docs)

---

## ⏱️ Time Estimation

### Development
- Phase 1 (Core Setup): 1-2 ore
- Phase 2 (API Integration): 1 ora
- Phase 3 (Email Templates): 1-2 ore
- Phase 4 (Filament Admin): 30 min
- Phase 5 (Automation): 30 min

**Total Development**: 4-6 ore

### Testing
- Unit tests: 1 ora
- Integration tests: 1 ora
- Manual testing: 1 ora

**Total Testing**: 3 ore

### Documentation
- API docs: 30 min
- User guide: 30 min
- Admin guide: 30 min

**Total Documentation**: 1.5 ore

---

**TOTAL TIME**: 8.5 - 10.5 ore

---

## 🎊 Ready to Start Implementation?

Următorii pași:
1. ✅ Review acest plan
2. 🚀 Start implementare Phase 1
3. 📝 Test și documentare
4. 🎉 Deploy în producție

**Să începem?** 🚀
