# 🔐 Smart Locks Integration - API Guide

## Overview

The Smart Locks Integration allows property owners to manage keyless entry for their properties using various smart lock providers (August, Yale, Schlage, Nuki, etc.). Access codes are automatically generated for confirmed bookings and sent to guests.

## Features

- ✅ Multi-provider support (Mock, Generic, August, Yale, etc.)
- ✅ Automatic access code generation for bookings
- ✅ Time-limited access codes
- ✅ Remote lock/unlock control
- ✅ Activity logging and monitoring
- ✅ Battery status tracking
- ✅ Guest notifications
- ✅ Manual code management

## Database Schema

### Smart Locks Table
- property_id (foreign key)
- provider (string: mock, august, yale, schlage, nuki, generic)
- lock_id (unique external ID)
- name (e.g., "Front Door")
- location (description)
- credentials (encrypted JSON)
- settings (JSON)
- status (active, inactive, offline, error)
- auto_generate_codes (boolean)
- battery_level (string/percentage)
- last_synced_at (timestamp)

### Access Codes Table
- smart_lock_id (foreign key)
- booking_id (nullable foreign key)
- user_id (nullable foreign key)
- code (PIN/access code)
- external_code_id (provider's code ID)
- type (temporary, permanent, one_time)
- valid_from (timestamp)
- valid_until (timestamp)
- status (pending, active, expired, revoked)
- max_uses (integer, for one-time codes)
- uses_count (integer)
- notified (boolean)

### Lock Activities Table
- smart_lock_id (foreign key)
- access_code_id (nullable)
- user_id (nullable)
- event_type (unlock, lock, code_used, code_created, code_deleted, battery_low, error)
- code_used (string)
- access_method (code, app, key, remote, auto)
- metadata (JSON)
- event_at (timestamp)

## API Endpoints

### Smart Lock Management (Property Owners)

#### 1. List Property Smart Locks
```http
GET /api/v1/properties/{propertyId}/smart-locks
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property_id": 5,
      "provider": "mock",
      "lock_id": "LOCK123ABC",
      "name": "Front Door",
      "location": "Main entrance",
      "status": "active",
      "auto_generate_codes": true,
      "battery_level": "85",
      "last_synced_at": "2025-11-02T15:30:00Z",
      "access_codes": [...]
    }
  ]
}
```

#### 2. Add Smart Lock to Property
```http
POST /api/v1/properties/{propertyId}/smart-locks
Authorization: Bearer {token}
Content-Type: application/json

{
  "provider": "mock",
  "lock_id": "LOCK123ABC",
  "name": "Front Door",
  "location": "Main entrance",
  "auto_generate_codes": true,
  "credentials": {
    "api_key": "your_api_key_here",
    "base_url": "https://api.provider.com"
  },
  "settings": {
    "auto_lock_delay": 30,
    "notifications_enabled": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Smart lock added successfully",
  "data": {
    "id": 1,
    "property_id": 5,
    "provider": "mock",
    "lock_id": "LOCK123ABC",
    "name": "Front Door",
    "status": "active"
  }
}
```

#### 3. Get Lock Status
```http
GET /api/v1/properties/{propertyId}/smart-locks/{lockId}/status
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "active",
    "battery_level": "85",
    "is_online": true,
    "needs_battery_replacement": false,
    "last_synced_at": "2025-11-02T15:30:00Z",
    "error_message": null
  }
}
```

#### 4. Remote Lock Control
```http
POST /api/v1/properties/{propertyId}/smart-locks/{lockId}/lock
Authorization: Bearer {token}
```

```http
POST /api/v1/properties/{propertyId}/smart-locks/{lockId}/unlock
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Lock secured successfully"
}
```

#### 5. Get Lock Activity History
```http
GET /api/v1/properties/{propertyId}/smart-locks/{lockId}/activities
Authorization: Bearer {token}
Parameters:
  - event_type (optional): unlock, lock, code_used, error
  - from_date (optional): YYYY-MM-DD
  - to_date (optional): YYYY-MM-DD
  - per_page (optional): 20 (default)
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "event_type": "unlock",
        "code_used": "******34",
        "access_method": "code",
        "description": "Door unlocked by guest",
        "event_at": "2025-11-02T14:30:00Z",
        "user": {
          "id": 45,
          "name": "John Doe"
        }
      }
    ],
    "total": 50,
    "per_page": 20
  }
}
```

### Access Code Management

#### 6. List Access Codes for Lock
```http
GET /api/v1/properties/{propertyId}/smart-locks/{lockId}/access-codes
Authorization: Bearer {token}
Parameters:
  - status (optional): active, pending, expired, revoked
  - type (optional): temporary, permanent, one_time
```

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "smart_lock_id": 1,
        "booking_id": 42,
        "code": "123456",
        "type": "temporary",
        "valid_from": "2025-11-15T14:00:00Z",
        "valid_until": "2025-11-20T12:00:00Z",
        "status": "active",
        "notified": true,
        "booking": {
          "id": 42,
          "check_in": "2025-11-15",
          "check_out": "2025-11-20"
        }
      }
    ]
  }
}
```

#### 7. Create Manual Access Code
```http
POST /api/v1/properties/{propertyId}/smart-locks/{lockId}/access-codes
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "temporary",
  "valid_from": "2025-11-15T14:00:00Z",
  "valid_until": "2025-11-20T12:00:00Z",
  "notes": "Code for maintenance crew",
  "code": "654321" // Optional, auto-generated if not provided
}
```

**Response:**
```json
{
  "success": true,
  "message": "Access code created successfully",
  "data": {
    "id": 2,
    "code": "654321",
    "type": "temporary",
    "valid_from": "2025-11-15T14:00:00Z",
    "valid_until": "2025-11-20T12:00:00Z",
    "status": "active"
  }
}
```

#### 8. Update Access Code
```http
PUT /api/v1/properties/{propertyId}/smart-locks/{lockId}/access-codes/{codeId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "valid_until": "2025-11-25T12:00:00Z",
  "notes": "Extended for extra day"
}
```

#### 9. Revoke Access Code
```http
DELETE /api/v1/properties/{propertyId}/smart-locks/{lockId}/access-codes/{codeId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Access code revoked successfully"
}
```

### Guest Access

#### 10. Get My Access Code for Booking
```http
GET /api/v1/bookings/{bookingId}/access-code
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "123456",
    "type": "temporary",
    "valid_from": "2025-11-15T14:00:00Z",
    "valid_until": "2025-11-20T12:00:00Z",
    "smart_lock": {
      "name": "Front Door",
      "location": "Main entrance"
    },
    "booking": {
      "id": 42,
      "property": {
        "title": "Beautiful Beach House",
        "street_address": "123 Ocean Drive"
      }
    }
  }
}
```

## Automatic Code Generation

Access codes are **automatically generated** when:
1. A booking is confirmed (status changes to "confirmed")
2. The property has an active smart lock
3. The smart lock has `auto_generate_codes` enabled

**Code Details:**
- 6-digit PIN
- Valid from 2 hours before check-in
- Valid until 2 hours after check-out
- Automatically sent to guest via email and in-app notification

## Provider Support

### Mock Provider (Development)
Used for testing and development. Always returns successful responses.

```php
'provider' => 'mock'
```

### Generic Webhook Provider
Generic REST API integration for most smart lock systems.

```php
'provider' => 'generic',
'credentials' => [
    'base_url' => 'https://api.smartlock.com',
    'api_key' => 'your_api_key'
]
```

### Custom Providers
Implement `SmartLockProviderInterface` for specific providers:
- August Home
- Yale Access
- Schlage Encode
- Nuki Smart Lock
- Wyze Lock

## Artisan Commands

### Sync Smart Locks
Syncs all lock statuses and expires old codes:
```bash
php artisan smartlocks:sync
```

This command should be scheduled to run periodically (e.g., every hour):
```php
// In app/Console/Kernel.php
$schedule->command('smartlocks:sync')->hourly();
```

## Email Notifications

Guests automatically receive an email with their access code when:
- Booking is confirmed
- Access code is successfully created

**Email includes:**
- Property name and address
- Check-in/check-out dates
- Lock location (e.g., "Front Door")
- 6-digit access code
- Valid time range
- Security reminder

## Security Features

1. **Encrypted Credentials**: Lock provider credentials are encrypted in database
2. **Masked Codes**: Codes are hidden in API responses unless authorized
3. **Time-Limited Access**: All codes have expiration dates
4. **Activity Logging**: All lock events are logged
5. **Automatic Expiration**: Old codes are automatically expired
6. **Status Monitoring**: Lock connectivity and battery status tracked

## Webhook Integration (Optional)

Smart lock providers can send webhooks for real-time events:

```http
POST /api/webhooks/smartlocks/{provider}
Content-Type: application/json

{
  "lock_id": "LOCK123ABC",
  "event": "unlocked",
  "code_used": "123456",
  "timestamp": "2025-11-02T14:30:00Z"
}
```

## Testing with Postman

### 1. Add Smart Lock
```bash
POST http://localhost/api/v1/properties/1/smart-locks
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "provider": "mock",
  "lock_id": "MOCK_LOCK_001",
  "name": "Front Door",
  "location": "Main entrance",
  "auto_generate_codes": true
}
```

### 2. Create Manual Code
```bash
POST http://localhost/api/v1/properties/1/smart-locks/1/access-codes
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "type": "temporary",
  "valid_from": "2025-11-15T14:00:00Z",
  "valid_until": "2025-11-20T12:00:00Z",
  "notes": "Test code"
}
```

### 3. Test Remote Unlock
```bash
POST http://localhost/api/v1/properties/1/smart-locks/1/unlock
Authorization: Bearer YOUR_TOKEN
```

### 4. Check Lock Status
```bash
GET http://localhost/api/v1/properties/1/smart-locks/1/status
Authorization: Bearer YOUR_TOKEN
```

### 5. View Activity Logs
```bash
GET http://localhost/api/v1/properties/1/smart-locks/1/activities?per_page=10
Authorization: Bearer YOUR_TOKEN
```

## Next Steps

✅ **Backend Complete:**
- Models and migrations
- Service layer with provider support
- API controllers and routes
- Automatic code generation
- Email notifications
- Activity logging

⏳ **Next Phase - Frontend:**
1. Owner dashboard for lock management
2. Access code UI
3. Activity history viewer
4. Guest access code display
5. Real-time status updates

## Support

For questions or issues:
- Check the main [README.md](./README.md)
- Review API examples above
- Test with Mock provider first
- Check logs: `storage/logs/laravel.log`
