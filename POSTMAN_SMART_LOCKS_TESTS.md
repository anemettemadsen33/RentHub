# 🔐 Smart Locks - Postman Testing Guide

## Prerequisites

1. Start Laravel backend: `php artisan serve`
2. Have a valid Bearer token (login first)
3. Have at least one property created
4. Have Postman installed

## Environment Variables (Postman)

```
BASE_URL = http://localhost:8000/api/v1
TOKEN = your_bearer_token_here
PROPERTY_ID = 1
LOCK_ID = 1
CODE_ID = 1
```

---

## Test Sequence

### 1️⃣ Add Smart Lock to Property

**Request:**
```http
POST {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks
Authorization: Bearer {{TOKEN}}
Content-Type: application/json
```

**Body:**
```json
{
  "provider": "mock",
  "lock_id": "MOCK_LOCK_001",
  "name": "Front Door",
  "location": "Main entrance at street level",
  "auto_generate_codes": true,
  "settings": {
    "auto_lock_delay": 30,
    "notifications_enabled": true
  }
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Smart lock added successfully",
  "data": {
    "id": 1,
    "property_id": 1,
    "provider": "mock",
    "lock_id": "MOCK_LOCK_001",
    "name": "Front Door",
    "location": "Main entrance at street level",
    "status": "active",
    "auto_generate_codes": true,
    "battery_level": null,
    "created_at": "2025-11-02T15:00:00.000000Z"
  }
}
```

**Save to variable:**
```javascript
// In Postman Tests tab
const response = pm.response.json();
pm.environment.set("LOCK_ID", response.data.id);
```

---

### 2️⃣ List Property Smart Locks

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property_id": 1,
      "name": "Front Door",
      "status": "active",
      "battery_level": null,
      "access_codes": []
    }
  ]
}
```

---

### 3️⃣ Get Lock Status

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/status
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "status": "active",
    "battery_level": "85",
    "is_online": true,
    "needs_battery_replacement": false,
    "last_synced_at": "2025-11-02T15:30:00.000000Z",
    "error_message": null
  }
}
```

---

### 4️⃣ Create Manual Access Code

**Request:**
```http
POST {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes
Authorization: Bearer {{TOKEN}}
Content-Type: application/json
```

**Body:**
```json
{
  "type": "temporary",
  "valid_from": "2025-11-15T14:00:00Z",
  "valid_until": "2025-11-20T12:00:00Z",
  "notes": "Test code for maintenance crew"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Access code created successfully",
  "data": {
    "id": 1,
    "smart_lock_id": 1,
    "code": "123456",
    "type": "temporary",
    "valid_from": "2025-11-15T14:00:00Z",
    "valid_until": "2025-11-20T12:00:00Z",
    "status": "active",
    "notified": false,
    "notes": "Test code for maintenance crew"
  }
}
```

**Save to variable:**
```javascript
const response = pm.response.json();
pm.environment.set("CODE_ID", response.data.id);
```

---

### 5️⃣ List Access Codes

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "smart_lock_id": 1,
        "code": "123456",
        "type": "temporary",
        "valid_from": "2025-11-15T14:00:00Z",
        "valid_until": "2025-11-20T12:00:00Z",
        "status": "active",
        "notified": false
      }
    ],
    "total": 1
  }
}
```

---

### 6️⃣ Get Access Code Details

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes/{{CODE_ID}}
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "smart_lock_id": 1,
    "code": "123456",
    "type": "temporary",
    "valid_from": "2025-11-15T14:00:00Z",
    "valid_until": "2025-11-20T12:00:00Z",
    "status": "active",
    "max_uses": null,
    "uses_count": 0,
    "activities": []
  }
}
```

---

### 7️⃣ Update Access Code

**Request:**
```http
PUT {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes/{{CODE_ID}}
Authorization: Bearer {{TOKEN}}
Content-Type: application/json
```

**Body:**
```json
{
  "valid_until": "2025-11-25T12:00:00Z",
  "notes": "Extended access for additional day"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Access code updated successfully",
  "data": {
    "id": 1,
    "code": "123456",
    "valid_until": "2025-11-25T12:00:00Z",
    "notes": "Extended access for additional day"
  }
}
```

---

### 8️⃣ Remote Unlock Test

**Request:**
```http
POST {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/unlock
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Lock opened successfully"
}
```

---

### 9️⃣ Remote Lock Test

**Request:**
```http
POST {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/lock
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Lock secured successfully"
}
```

---

### 🔟 Get Lock Activity History

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/activities?per_page=10
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "event_type": "unlock",
        "access_method": "remote",
        "description": "Lock opened remotely",
        "event_at": "2025-11-02T15:45:00Z",
        "user": {
          "id": 1,
          "name": "Property Owner"
        }
      },
      {
        "id": 2,
        "event_type": "lock",
        "access_method": "remote",
        "description": "Lock secured remotely",
        "event_at": "2025-11-02T15:40:00Z"
      },
      {
        "id": 1,
        "event_type": "code_created",
        "description": "Access code created",
        "event_at": "2025-11-02T15:35:00Z"
      }
    ],
    "total": 3
  }
}
```

---

### 1️⃣1️⃣ Filter Activities by Event Type

**Request:**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/activities?event_type=unlock&per_page=5
Authorization: Bearer {{TOKEN}}
```

---

### 1️⃣2️⃣ Revoke Access Code

**Request:**
```http
DELETE {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes/{{CODE_ID}}
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Access code revoked successfully"
}
```

---

### 1️⃣3️⃣ Update Smart Lock Settings

**Request:**
```http
PUT {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}
Authorization: Bearer {{TOKEN}}
Content-Type: application/json
```

**Body:**
```json
{
  "name": "Main Entrance Lock",
  "location": "Front door - updated location",
  "auto_generate_codes": false
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Smart lock updated successfully",
  "data": {
    "id": 1,
    "name": "Main Entrance Lock",
    "location": "Front door - updated location",
    "auto_generate_codes": false
  }
}
```

---

### 1️⃣4️⃣ Delete Smart Lock

**Request:**
```http
DELETE {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}
Authorization: Bearer {{TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Smart lock deleted successfully"
}
```

---

## Guest Endpoints

### 1️⃣5️⃣ Guest: Get My Access Code

**Scenario:** Guest retrieves access code for their confirmed booking

**Request:**
```http
GET {{BASE_URL}}/bookings/{{BOOKING_ID}}/access-code
Authorization: Bearer {{GUEST_TOKEN}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "123456",
    "type": "temporary",
    "valid_from": "2025-11-15T12:00:00Z",
    "valid_until": "2025-11-20T14:00:00Z",
    "smart_lock": {
      "id": 1,
      "name": "Front Door",
      "location": "Main entrance"
    },
    "booking": {
      "id": 42,
      "check_in": "2025-11-15",
      "check_out": "2025-11-20",
      "property": {
        "id": 1,
        "title": "Beautiful Beach House",
        "street_address": "123 Ocean Drive"
      }
    }
  }
}
```

---

## Testing Automatic Code Generation

### Scenario: Create and Confirm Booking

**Step 1: Create Booking**
```http
POST {{BASE_URL}}/bookings
Authorization: Bearer {{TOKEN}}
Content-Type: application/json

{
  "property_id": {{PROPERTY_ID}},
  "check_in": "2025-11-15",
  "check_out": "2025-11-20",
  "guests": 2,
  "total_price": 500
}
```

**Step 2: Confirm Booking** (triggers automatic code generation)
```http
PATCH {{BASE_URL}}/bookings/{{BOOKING_ID}}
Authorization: Bearer {{TOKEN}}
Content-Type: application/json

{
  "status": "confirmed"
}
```

**Step 3: Verify Code Was Created**
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes
Authorization: Bearer {{TOKEN}}
```

Should show a new code linked to the booking!

---

## Error Scenarios

### ❌ Unauthorized Access
```http
GET {{BASE_URL}}/properties/999/smart-locks
Authorization: Bearer {{TOKEN}}
```

**Response (403):**
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

### ❌ Lock Not Found
```http
GET {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/999/status
Authorization: Bearer {{TOKEN}}
```

**Response (404):**
```json
{
  "success": false,
  "message": "No query results for model [SmartLock] 999"
}
```

### ❌ Invalid Code Format
```http
POST {{BASE_URL}}/properties/{{PROPERTY_ID}}/smart-locks/{{LOCK_ID}}/access-codes
Authorization: Bearer {{TOKEN}}
Content-Type: application/json

{
  "type": "invalid_type",
  "valid_from": "2025-11-15T14:00:00Z"
}
```

**Response (422):**
```json
{
  "message": "The selected type is invalid.",
  "errors": {
    "type": ["The selected type is invalid."]
  }
}
```

---

## Collection Import (JSON)

Create a Postman collection with this JSON:

```json
{
  "info": {
    "name": "RentHub - Smart Locks",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "BASE_URL",
      "value": "http://localhost:8000/api/v1"
    },
    {
      "key": "TOKEN",
      "value": "your_token_here"
    },
    {
      "key": "PROPERTY_ID",
      "value": "1"
    },
    {
      "key": "LOCK_ID",
      "value": "1"
    },
    {
      "key": "CODE_ID",
      "value": "1"
    }
  ]
}
```

---

## Quick Test Checklist

- [ ] Add smart lock to property
- [ ] List property locks
- [ ] Get lock status
- [ ] Create manual access code
- [ ] List access codes
- [ ] Update access code
- [ ] Remote unlock
- [ ] Remote lock
- [ ] View activity history
- [ ] Revoke access code
- [ ] Test automatic code generation on booking confirmation
- [ ] Guest retrieves their access code
- [ ] Delete smart lock

---

## Tips

1. **Mock Provider**: Use for testing without real hardware
2. **Activity Logs**: Check after each lock/unlock operation
3. **Battery Status**: Will be populated after first sync
4. **Automatic Codes**: Require `auto_generate_codes: true` on lock
5. **Time Zones**: All timestamps in UTC
6. **Code Format**: 6 digits, auto-generated if not provided

## Next: Frontend Integration

After API testing is complete, integrate with Next.js frontend:
- Owner dashboard UI
- Access code management interface
- Guest access code display
- Real-time activity feed
- Lock status widget
