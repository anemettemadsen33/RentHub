# 🔐 Smart Locks - Quick Reference Card

## 🚀 Quick Start (30 seconds)

### 1. Add a Smart Lock
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/smart-locks \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "mock",
    "lock_id": "LOCK001",
    "name": "Front Door",
    "auto_generate_codes": true
  }'
```

### 2. Create Access Code
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/smart-locks/1/access-codes \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "temporary",
    "valid_from": "2025-11-15T14:00:00Z",
    "valid_until": "2025-11-20T12:00:00Z"
  }'
```

### 3. Remote Unlock
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/smart-locks/1/unlock \
  -H "Authorization: Bearer TOKEN"
```

---

## 📋 Artisan Commands

```bash
# Sync all locks and expire old codes
php artisan smartlocks:sync

# Schedule it hourly in app/Console/Kernel.php
$schedule->command('smartlocks:sync')->hourly();
```

---

## 🗂️ Database Tables

### smart_locks
```sql
- id, property_id, provider, lock_id
- name, location, status
- auto_generate_codes, battery_level
- credentials (encrypted), settings
```

### access_codes
```sql
- id, smart_lock_id, booking_id, user_id
- code, type (temporary/permanent/one_time)
- valid_from, valid_until, status
- max_uses, uses_count
```

### lock_activities
```sql
- id, smart_lock_id, access_code_id
- event_type (unlock/lock/code_used/error)
- access_method, description
- event_at, user_id
```

---

## 🔌 API Endpoints (19)

### Smart Lock Management
```
GET    /api/v1/properties/{id}/smart-locks                      - List locks
POST   /api/v1/properties/{id}/smart-locks                      - Add lock
GET    /api/v1/properties/{id}/smart-locks/{lockId}             - Get lock
PUT    /api/v1/properties/{id}/smart-locks/{lockId}             - Update lock
DELETE /api/v1/properties/{id}/smart-locks/{lockId}             - Delete lock
GET    /api/v1/properties/{id}/smart-locks/{lockId}/status      - Get status
POST   /api/v1/properties/{id}/smart-locks/{lockId}/lock        - Lock
POST   /api/v1/properties/{id}/smart-locks/{lockId}/unlock      - Unlock
GET    /api/v1/properties/{id}/smart-locks/{lockId}/activities  - View logs
```

### Access Codes
```
GET    /api/v1/properties/{id}/smart-locks/{lockId}/access-codes          - List codes
POST   /api/v1/properties/{id}/smart-locks/{lockId}/access-codes          - Create code
GET    /api/v1/properties/{id}/smart-locks/{lockId}/access-codes/{codeId} - Get code
PUT    /api/v1/properties/{id}/smart-locks/{lockId}/access-codes/{codeId} - Update code
DELETE /api/v1/properties/{id}/smart-locks/{lockId}/access-codes/{codeId} - Revoke code
```

### Guest Access
```
GET    /api/v1/bookings/{bookingId}/access-code - Get my code
```

---

## 🔄 Automatic Code Generation Flow

```
Booking created → status: confirmed
    ↓
BookingObserver triggered
    ↓
SmartLockService::createAccessCodeForBooking()
    ↓
6-digit code generated
    ↓
Code sent to provider API
    ↓
Email sent to guest
    ↓
Code active (2h before check-in to 2h after checkout)
```

---

## 🎯 Provider Types

### Mock (Testing)
```json
{
  "provider": "mock",
  "lock_id": "MOCK001"
}
```

### Generic (REST API)
```json
{
  "provider": "generic",
  "lock_id": "DEVICE123",
  "credentials": {
    "base_url": "https://api.provider.com",
    "api_key": "your_api_key"
  }
}
```

### Add Custom Provider
```php
// 1. Create: app/Services/SmartLock/Providers/AugustProvider.php
class AugustProvider implements SmartLockProviderInterface { ... }

// 2. Register: app/Providers/AppServiceProvider.php
$service->registerProvider('august', new AugustProvider());

// 3. Use:
{ "provider": "august", "lock_id": "AUG123" }
```

---

## 📧 Email Notification

Guest receives:
```
Subject: Your Access Code for [Property]

Access Code: 123456
Valid From: Nov 15, 2025 12:00
Valid Until: Nov 20, 2025 14:00

Lock: Front Door (Main entrance)
```

---

## 🔒 Security Features

- ✅ Encrypted credentials
- ✅ Masked codes in API
- ✅ Time-limited access
- ✅ Activity logging
- ✅ Authorization gates
- ✅ Auto-expiration

---

## 🧪 Quick Test

### 1. Test with Postman
Import collection from: `POSTMAN_SMART_LOCKS_TESTS.md`

### 2. Test Auto-generation
```bash
# Create booking
POST /api/v1/bookings { property_id: 1, ... }

# Confirm booking (triggers code generation)
PATCH /api/v1/bookings/1 { status: "confirmed" }

# Check code was created
GET /api/v1/properties/1/smart-locks/1/access-codes
```

### 3. Test Guest Access
```bash
# As guest user
GET /api/v1/bookings/1/access-code
```

---

## 🎨 Frontend Components

Available in `frontend-examples/smart-locks-examples.tsx`:

1. **SmartLockCard** - Lock status + remote control
2. **AccessCodeList** - Code management
3. **LockActivityTimeline** - Activity logs
4. **GuestAccessCodeCard** - Guest view
5. **CreateAccessCodeModal** - Code creation form
6. **SmartLocksOwnerDashboard** - Complete UI

---

## 📁 Important Files

### Backend
```
app/Models/SmartLock.php
app/Models/AccessCode.php
app/Models/LockActivity.php
app/Services/SmartLock/SmartLockService.php
app/Http/Controllers/Api/V1/SmartLockController.php
app/Http/Controllers/Api/V1/AccessCodeController.php
app/Notifications/AccessCodeCreatedNotification.php
app/Console/Commands/SyncSmartLocksCommand.php
```

### Documentation
```
START_HERE_SMART_LOCKS.md           - Quick start
SMART_LOCKS_API_GUIDE.md            - Complete API docs
POSTMAN_SMART_LOCKS_TESTS.md        - Test scenarios
TASK_3.7_SMART_LOCKS_COMPLETE.md    - Implementation summary
```

---

## 🐛 Troubleshooting

### Code not generated automatically?
1. Check lock has `auto_generate_codes: true`
2. Check booking status is `confirmed`
3. Check logs: `storage/logs/laravel.log`

### Provider connection failed?
1. Test credentials: `SmartLockService::testConnection()`
2. Check provider is registered in `AppServiceProvider`
3. Use Mock provider for testing

### Guest can't see code?
1. Verify booking belongs to user
2. Check code status is `active`
3. Check code validity dates

---

## 📞 Support

- 📖 Full docs: `SMART_LOCKS_API_GUIDE.md`
- 🧪 Tests: `POSTMAN_SMART_LOCKS_TESTS.md`
- 🚀 Start: `START_HERE_SMART_LOCKS.md`
- 📝 Logs: `storage/logs/laravel.log`

---

## 🎉 Done!

Your smart locks system is ready! 🔐✨

**Next:** Integrate with Next.js frontend using the provided components.
