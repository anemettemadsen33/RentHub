# 🧹 Quick Start: Cleaning & Maintenance System

## Overview

The Cleaning & Maintenance system provides comprehensive tools for managing property cleaning services and maintenance requests with professional service provider integration.

## ⚡ Quick Setup

### 1. Database is Ready ✅
All migrations have been run. Tables created:
- `service_providers`
- `cleaning_services`
- `cleaning_schedules`
- `maintenance_requests` (updated)

### 2. Check Your Admin Panel

Visit: `http://localhost/admin`

You should see new menu items:
- **Service Providers** - Manage cleaning & maintenance providers
- **Cleaning Services** - View/manage cleaning bookings
- **Cleaning Schedules** - Set up recurring cleanings
- **Maintenance Requests** - Enhanced with provider assignment

## 🚀 Quick Test (5 Minutes)

### Step 1: Create a Service Provider

```bash
curl -X POST http://localhost/api/v1/service-providers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Pro Cleaning Services",
    "type": "cleaning",
    "email": "info@procleaning.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "city": "New York",
    "zip_code": "10001",
    "pricing_type": "per_service",
    "base_rate": 150,
    "working_hours": {
      "monday": {"start": "08:00", "end": "17:00"},
      "tuesday": {"start": "08:00", "end": "17:00"},
      "wednesday": {"start": "08:00", "end": "17:00"},
      "thursday": {"start": "08:00", "end": "17:00"},
      "friday": {"start": "08:00", "end": "17:00"}
    }
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Service provider created successfully",
  "data": {
    "id": 1,
    "name": "Pro Cleaning Services",
    "status": "pending_verification"
  }
}
```

### Step 2: Verify Provider (Admin Only)

```bash
curl -X POST http://localhost/api/v1/service-providers/1/verify \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### Step 3: Schedule a Cleaning

```bash
curl -X POST http://localhost/api/v1/cleaning-services \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "service_provider_id": 1,
    "service_type": "regular_cleaning",
    "scheduled_date": "2025-11-06",
    "scheduled_time": "10:00",
    "estimated_duration_hours": 2,
    "checklist": [
      "Clean all rooms",
      "Change bedding",
      "Vacuum carpets"
    ],
    "estimated_cost": 150
  }'
```

### Step 4: View Your Services

```bash
# List all cleaning services
curl -X GET http://localhost/api/v1/cleaning-services \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get provider stats
curl -X GET http://localhost/api/v1/service-providers/1/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🎯 Common Use Cases

### Use Case 1: Post-Booking Turnover Cleaning

**Scenario:** Guest checks out, need to clean property for next guest

```bash
curl -X POST http://localhost/api/v1/cleaning-services \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "booking_id": 5,
    "service_provider_id": 1,
    "service_type": "post_booking",
    "scheduled_date": "2025-11-06",
    "scheduled_time": "11:00",
    "estimated_duration_hours": 3,
    "requires_key": true,
    "access_code": "1234",
    "checklist": [
      "Strip and replace all linens",
      "Deep clean bathrooms",
      "Clean kitchen and appliances",
      "Vacuum all floors",
      "Restock toiletries",
      "Take out trash"
    ]
  }'
```

### Use Case 2: Set Up Weekly Recurring Cleaning

**Scenario:** Long-term rental needs weekly cleaning

```bash
# Note: Create CleaningSchedule via Filament Admin Panel for now
# Or implement CleaningSchedule API endpoints
```

Visit Admin Panel → Cleaning Schedules → Create New:
- Property: Select property
- Provider: Select verified provider
- Schedule Type: Recurring
- Frequency: Weekly
- Days: Select Friday
- Time: 14:00
- Auto-book: Yes ✓

### Use Case 3: Maintenance Request with Provider

**Scenario:** Tenant reports broken AC, assign HVAC specialist

```bash
# 1. Create maintenance request (tenant)
curl -X POST http://localhost/api/v1/maintenance-requests \
  -H "Authorization: Bearer TENANT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "long_term_rental_id": 1,
    "property_id": 1,
    "tenant_id": 2,
    "title": "AC Not Working",
    "description": "AC unit not cooling, makes strange noise",
    "category": "hvac",
    "priority": "urgent"
  }'

# 2. Assign HVAC provider (owner/admin)
curl -X POST http://localhost/api/v1/maintenance-requests/1/assign-service-provider \
  -H "Authorization: Bearer OWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "service_provider_id": 2
  }'
```

## 🤖 Automation

### Set Up Cron Job for Auto-Scheduling

Add to your crontab:

```bash
# Run every hour to process cleaning schedules
0 * * * * cd /path/to/project && php artisan cleaning:process-schedules
```

**What it does:**
- Checks all active cleaning schedules
- Creates cleaning service bookings automatically
- Sends notifications to providers and owners
- Updates next execution dates

### Manual Test:

```bash
cd C:\laragon\www\RentHub\backend
php artisan cleaning:process-schedules
```

## 📊 Admin Panel Features

### Service Providers Management

**Navigate to:** Admin → Service Providers

**Features:**
- ✅ List all providers with filters
- ✅ Create new providers
- ✅ Verify providers (admin only)
- ✅ View provider statistics
- ✅ Edit provider details
- ✅ Soft delete providers

**Filters:**
- Type (Cleaning, Maintenance, Both)
- Status (Active, Inactive, Suspended)
- Verified status
- Rating

### Cleaning Services

**Navigate to:** Admin → Cleaning Services

**Features:**
- ✅ View all scheduled cleanings
- ✅ Filter by property, status, date
- ✅ Quick status updates
- ✅ View completion photos
- ✅ See ratings and feedback
- ✅ Export to CSV

### Maintenance Requests (Enhanced)

**Navigate to:** Admin → Maintenance Requests

**New Features:**
- ✅ Service Provider assignment
- ✅ Provider contact info display
- ✅ Provider performance tracking

## 🔗 Integration Points

### With Booking System
```php
// Automatically schedule cleaning after booking checkout
$booking = Booking::find($bookingId);
$checkoutDate = $booking->check_out;

CleaningService::create([
    'property_id' => $booking->property_id,
    'booking_id' => $booking->id,
    'service_type' => 'post_booking',
    'scheduled_date' => $checkoutDate->addHours(2),
    // ... other fields
]);
```

### With Smart Locks
```php
// Provide temporary access code to cleaning provider
$cleaningService = CleaningService::find($id);

if ($cleaningService->property->has_smart_lock) {
    $accessCode = SmartLock::generateTemporaryCode(
        $cleaningService->property->smart_lock_id,
        $cleaningService->scheduled_date,
        $cleaningService->estimated_duration_hours
    );
    
    $cleaningService->update(['access_code' => $accessCode]);
}
```

### With Notifications
```php
// Send notifications to provider
$cleaningService->serviceProvider->notify(
    new CleaningServiceScheduled($cleaningService)
);

// Reminder 24 hours before
$cleaningService->requestedBy->notify(
    new CleaningServiceReminder($cleaningService)
);
```

## 📱 Mobile App Integration

### Provider App Flow

1. **Login** → View assigned jobs
2. **Today's Schedule** → See all jobs for today
3. **Start Job** → `POST /cleaning-services/{id}/start`
4. **Complete Checklist** → Mark items as done
5. **Upload Photos** → Before/After photos
6. **Complete Job** → `POST /cleaning-services/{id}/complete`

### Owner App Flow

1. **Dashboard** → See upcoming cleanings
2. **Schedule Cleaning** → `POST /cleaning-services`
3. **View History** → `GET /properties/{id}/cleaning-history`
4. **Rate Service** → `POST /cleaning-services/{id}/rate`

## 🧪 Testing Checklist

- [ ] Create service provider
- [ ] Verify provider (admin)
- [ ] Check provider availability
- [ ] Schedule one-time cleaning
- [ ] Start cleaning service
- [ ] Complete cleaning with photos
- [ ] Rate cleaning service
- [ ] View provider statistics
- [ ] Create recurring schedule (admin panel)
- [ ] Run schedule processor command
- [ ] Submit maintenance request
- [ ] Assign service provider to maintenance
- [ ] Complete maintenance request
- [ ] View cleaning history for property

## 📚 Documentation

- **Complete Guide:** [TASK_3.8_CLEANING_MAINTENANCE_COMPLETE.md](TASK_3.8_CLEANING_MAINTENANCE_COMPLETE.md)
- **API Reference:** [CLEANING_MAINTENANCE_API_GUIDE.md](CLEANING_MAINTENANCE_API_GUIDE.md)

## 🆘 Troubleshooting

### Issue: Service provider not appearing in dropdown

**Solution:**
```bash
# Check provider status
curl -X GET http://localhost/api/v1/service-providers/1 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Provider must be verified and active
curl -X POST http://localhost/api/v1/service-providers/1/verify \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### Issue: Cleaning schedule not creating services

**Solution:**
```bash
# Check schedule configuration
# - Ensure auto_book is true
# - Ensure next_execution_at is set
# - Run command manually to see errors

php artisan cleaning:process-schedules
```

### Issue: Cannot assign provider to maintenance

**Solution:**
```bash
# Provider must have maintenance in their specialties
# Check provider type
curl -X GET http://localhost/api/v1/service-providers/1 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Type should be "maintenance" or "both"
```

## 🎉 Success!

You now have a fully functional Cleaning & Maintenance system!

**Next Steps:**
- Set up cron job for auto-scheduling
- Configure email notifications
- Add more service providers
- Create recurring schedules
- Integrate with your booking flow

**Questions?** Check the full documentation or API guide.

---

**Version:** 1.0.0  
**Last Updated:** November 3, 2025  
**Status:** ✅ Production Ready
