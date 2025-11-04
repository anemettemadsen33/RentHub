# Cleaning & Maintenance API Guide

## 📋 Table of Contents

- [Service Providers](#service-providers)
- [Cleaning Services](#cleaning-services)
- [Maintenance Requests](#maintenance-requests)
- [Testing Guide](#testing-guide)

---

## 🏢 Service Providers

### List Service Providers

```http
GET /api/v1/service-providers
```

**Query Parameters:**
- `type` - Filter by type: `cleaning`, `maintenance`, `both`
- `status` - Filter by status: `active`, `inactive`, `suspended`, `pending_verification`
- `verified` - Filter verified providers: `1` or `0`
- `city` - Filter by city name
- `min_rating` - Minimum rating (e.g., `4.5`)
- `service_type` - Either `cleaning` or `maintenance`
- `search` - Search by name, company, or city
- `sort_by` - Sort field (default: `average_rating`)
- `sort_order` - `asc` or `desc` (default: `desc`)
- `per_page` - Results per page (default: 15)

**Example:**
```bash
curl -X GET "http://localhost/api/v1/service-providers?type=cleaning&verified=1&min_rating=4" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "company_name": "SparkleClean LLC",
        "type": "cleaning",
        "email": "john@sparkleclean.com",
        "phone": "+1234567890",
        "city": "New York",
        "average_rating": 4.8,
        "total_jobs": 150,
        "completed_jobs": 145,
        "verified": true,
        "status": "active"
      }
    ],
    "total": 50
  }
}
```

---

### Create Service Provider

```http
POST /api/v1/service-providers
```

**Required:** `role:owner` or `role:admin`

**Body:**
```json
{
  "name": "John Cleaning Services",
  "company_name": "SparkleClean LLC",
  "type": "cleaning",
  "email": "john@sparkleclean.com",
  "phone": "+1234567890",
  "secondary_phone": "+0987654321",
  "address": "123 Main Street",
  "city": "New York",
  "state": "NY",
  "zip_code": "10001",
  "business_license": "BL123456",
  "insurance_policy": "INS789012",
  "insurance_expiry": "2026-12-31",
  "certifications": ["IICRC Certified", "Green Clean Certified"],
  "service_areas": ["New York", "Brooklyn", "Queens"],
  "services_offered": ["regular_cleaning", "deep_cleaning", "move_out", "post_booking"],
  "maintenance_specialties": ["plumbing", "electrical"],
  "hourly_rate": 50.00,
  "base_rate": 150.00,
  "pricing_type": "per_service",
  "working_hours": {
    "monday": {"start": "08:00", "end": "17:00"},
    "tuesday": {"start": "08:00", "end": "17:00"},
    "wednesday": {"start": "08:00", "end": "17:00"},
    "thursday": {"start": "08:00", "end": "17:00"},
    "friday": {"start": "08:00", "end": "17:00"}
  },
  "holidays": ["2025-12-25", "2025-01-01"],
  "emergency_available": true,
  "bio": "Professional cleaning service with 10+ years experience"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Service provider created successfully",
  "data": {
    "id": 1,
    "name": "John Cleaning Services",
    "status": "pending_verification",
    "created_at": "2025-11-03T07:00:00Z"
  }
}
```

---

### Get Service Provider Details

```http
GET /api/v1/service-providers/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Cleaning Services",
    "company_name": "SparkleClean LLC",
    "average_rating": 4.8,
    "total_jobs": 150,
    "completed_jobs": 145,
    "cleaning_services": [],
    "maintenance_requests": []
  }
}
```

---

### Update Service Provider

```http
PUT /api/v1/service-providers/{id}
```

**Required:** `role:owner` or `role:admin`

**Body:** Same as create, all fields optional

---

### Verify Service Provider

```http
POST /api/v1/service-providers/{id}/verify
```

**Required:** `role:admin`

**Response:**
```json
{
  "success": true,
  "message": "Service provider verified successfully",
  "data": {
    "id": 1,
    "verified": true,
    "verified_at": "2025-11-03T07:30:00Z",
    "status": "active"
  }
}
```

---

### Check Availability

```http
POST /api/v1/service-providers/{id}/check-availability
```

**Body:**
```json
{
  "date": "2025-11-05",
  "time": "14:00"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "available": true,
    "provider": {
      "id": 1,
      "name": "John Cleaning Services",
      "company_name": "SparkleClean LLC"
    },
    "requested_date": "2025-11-05",
    "requested_time": "14:00"
  }
}
```

---

### Get Provider Statistics

```http
GET /api/v1/service-providers/{id}/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_jobs": 150,
    "completed_jobs": 145,
    "cancelled_jobs": 5,
    "completion_rate": 96.67,
    "average_rating": 4.8,
    "response_time_hours": 2.5,
    "cleaning_services_count": 120,
    "maintenance_requests_count": 30,
    "recent_ratings": [
      {
        "rating": 5,
        "feedback": "Excellent service!",
        "rated_at": "2025-11-02T10:00:00Z"
      }
    ]
  }
}
```

---

## 🧹 Cleaning Services

### List Cleaning Services

```http
GET /api/v1/cleaning-services
```

**Query Parameters:**
- `property_id` - Filter by property
- `status` - Filter by status: `scheduled`, `confirmed`, `in_progress`, `completed`, `cancelled`
- `service_type` - Filter by type
- `service_provider_id` - Filter by provider
- `from_date` - Start date filter (YYYY-MM-DD)
- `to_date` - End date filter (YYYY-MM-DD)
- `sort_by` - Sort field (default: `scheduled_date`)
- `sort_order` - `asc` or `desc`
- `per_page` - Results per page

**Example:**
```bash
curl -X GET "http://localhost/api/v1/cleaning-services?property_id=1&status=upcoming" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### Schedule Cleaning Service

```http
POST /api/v1/cleaning-services
```

**Required:** `role:owner` or `role:admin`

**Body:**
```json
{
  "property_id": 1,
  "booking_id": null,
  "long_term_rental_id": null,
  "service_provider_id": 1,
  "service_type": "post_booking",
  "description": "Post-checkout cleaning",
  "checklist": [
    "Clean all rooms",
    "Change bedding",
    "Stock toiletries",
    "Check appliances",
    "Vacuum carpets",
    "Mop floors"
  ],
  "special_instructions": "Please use eco-friendly products. Extra attention to kitchen.",
  "scheduled_date": "2025-11-05",
  "scheduled_time": "10:00",
  "estimated_duration_hours": 3,
  "requires_key": true,
  "access_instructions": "Key in lockbox, code 1234",
  "access_code": "5678",
  "estimated_cost": 150.00,
  "provider_brings_supplies": true,
  "supplies_needed": []
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cleaning service scheduled successfully",
  "data": {
    "id": 1,
    "property_id": 1,
    "service_provider_id": 1,
    "service_type": "post_booking",
    "scheduled_date": "2025-11-05T10:00:00Z",
    "status": "confirmed",
    "created_at": "2025-11-03T07:00:00Z"
  }
}
```

---

### Start Cleaning Service

```http
POST /api/v1/cleaning-services/{id}/start
```

**Response:**
```json
{
  "success": true,
  "message": "Cleaning service started",
  "data": {
    "id": 1,
    "status": "in_progress",
    "started_at": "2025-11-05T10:05:00Z"
  }
}
```

---

### Complete Cleaning Service

```http
POST /api/v1/cleaning-services/{id}/complete
```

**Body:**
```json
{
  "completed_checklist": [
    "Clean all rooms - ✓",
    "Change bedding - ✓",
    "Stock toiletries - ✓",
    "Check appliances - ✓",
    "Vacuum carpets - ✓",
    "Mop floors - ✓"
  ],
  "after_photos": [
    "/storage/cleaning/service-1/after-1.jpg",
    "/storage/cleaning/service-1/after-2.jpg",
    "/storage/cleaning/service-1/after-3.jpg"
  ],
  "completion_notes": "All tasks completed successfully. Property is ready for next guest.",
  "issues_found": [
    {
      "issue": "Bathroom light flickering",
      "location": "Master Bathroom",
      "severity": "low",
      "action_needed": "Replace light bulb"
    }
  ],
  "actual_cost": 150.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cleaning service marked as completed",
  "data": {
    "id": 1,
    "status": "completed",
    "completed_at": "2025-11-05T13:00:00Z"
  }
}
```

---

### Cancel Cleaning Service

```http
POST /api/v1/cleaning-services/{id}/cancel
```

**Body:**
```json
{
  "reason": "Guest cancelled booking"
}
```

---

### Rate Cleaning Service

```http
POST /api/v1/cleaning-services/{id}/rate
```

**Body:**
```json
{
  "rating": 5,
  "feedback": "Excellent service! Very thorough and professional. Property was spotless."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Rating submitted successfully",
  "data": {
    "id": 1,
    "rating": 5,
    "feedback": "Excellent service!",
    "rated_at": "2025-11-05T14:00:00Z"
  }
}
```

---

### Get Property Cleaning History

```http
GET /api/v1/properties/{id}/cleaning-history
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "service_type": "post_booking",
        "scheduled_date": "2025-11-05T10:00:00Z",
        "status": "completed",
        "rating": 5,
        "service_provider": {
          "id": 1,
          "name": "John Cleaning Services"
        }
      }
    ]
  }
}
```

---

## 🔧 Maintenance Requests (Enhanced)

### Assign Service Provider to Maintenance Request

```http
POST /api/v1/maintenance-requests/{id}/assign-service-provider
```

**Required:** `role:owner` or `role:admin`

**Body:**
```json
{
  "service_provider_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Service provider assigned successfully",
  "request": {
    "id": 1,
    "title": "Leaking Faucet",
    "status": "acknowledged",
    "service_provider": {
      "id": 2,
      "name": "Mike's Plumbing Services",
      "phone": "+1234567890"
    }
  }
}
```

---

## 🧪 Testing Guide

### Postman Collection

#### Environment Variables
```json
{
  "base_url": "http://localhost/api/v1",
  "auth_token": "YOUR_BEARER_TOKEN",
  "provider_id": "1",
  "cleaning_service_id": "1",
  "property_id": "1"
}
```

### Test Scenarios

#### Scenario 1: Complete Cleaning Service Workflow

1. **Create Service Provider**
```bash
curl -X POST http://localhost/api/v1/service-providers \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Cleaning Co",
    "type": "cleaning",
    "email": "test@cleaning.com",
    "phone": "+1234567890",
    "address": "123 Test St",
    "city": "Test City",
    "zip_code": "12345",
    "pricing_type": "per_service",
    "base_rate": 100
  }'
```

2. **Verify Provider (Admin Only)**
```bash
curl -X POST http://localhost/api/v1/service-providers/1/verify \
  -H "Authorization: Bearer {admin_token}"
```

3. **Schedule Cleaning**
```bash
curl -X POST http://localhost/api/v1/cleaning-services \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "service_provider_id": 1,
    "service_type": "regular_cleaning",
    "scheduled_date": "2025-11-06",
    "scheduled_time": "10:00",
    "estimated_duration_hours": 2,
    "estimated_cost": 100
  }'
```

4. **Start Service**
```bash
curl -X POST http://localhost/api/v1/cleaning-services/1/start \
  -H "Authorization: Bearer {token}"
```

5. **Complete Service**
```bash
curl -X POST http://localhost/api/v1/cleaning-services/1/complete \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "completion_notes": "Service completed",
    "actual_cost": 100
  }'
```

6. **Rate Service**
```bash
curl -X POST http://localhost/api/v1/cleaning-services/1/rate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "rating": 5,
    "feedback": "Great job!"
  }'
```

7. **Check Provider Stats**
```bash
curl -X GET http://localhost/api/v1/service-providers/1/stats \
  -H "Authorization: Bearer {token}"
```

---

#### Scenario 2: Maintenance with Service Provider

1. **Submit Maintenance Request**
```bash
curl -X POST http://localhost/api/v1/maintenance-requests \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "long_term_rental_id": 1,
    "property_id": 1,
    "tenant_id": 2,
    "title": "Broken AC",
    "description": "AC not cooling",
    "category": "hvac",
    "priority": "high"
  }'
```

2. **Create Maintenance Provider**
```bash
curl -X POST http://localhost/api/v1/service-providers \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "AC Repair Pro",
    "type": "maintenance",
    "email": "ac@repair.com",
    "phone": "+1234567890",
    "address": "456 Service Ave",
    "city": "Test City",
    "zip_code": "12345",
    "pricing_type": "hourly",
    "hourly_rate": 75,
    "maintenance_specialties": ["hvac", "electrical"]
  }'
```

3. **Assign Provider to Request**
```bash
curl -X POST http://localhost/api/v1/maintenance-requests/1/assign-service-provider \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "service_provider_id": 2
  }'
```

4. **Complete Maintenance**
```bash
curl -X POST http://localhost/api/v1/maintenance-requests/1/complete \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "resolution_notes": "Replaced compressor",
    "actual_cost": 300
  }'
```

---

### Quick Test Commands

```bash
# List all cleaning providers
curl -X GET "http://localhost/api/v1/service-providers?type=cleaning" \
  -H "Authorization: Bearer {token}"

# List upcoming cleaning services
curl -X GET "http://localhost/api/v1/cleaning-services?status=upcoming" \
  -H "Authorization: Bearer {token}"

# Check provider availability
curl -X POST http://localhost/api/v1/service-providers/1/check-availability \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"date": "2025-11-06", "time": "14:00"}'

# Get cleaning history for property
curl -X GET http://localhost/api/v1/properties/1/cleaning-history \
  -H "Authorization: Bearer {token}"
```

---

## 📝 Notes

- All timestamps are in UTC
- File uploads for photos should use `multipart/form-data`
- Rating must be between 1-5
- Only owners and admins can create/manage service providers
- Service providers can be assigned to multiple properties
- Cleaning schedules can be recurring or one-time
- Use the console command `php artisan cleaning:process-schedules` for automation

---

## 🔗 Related Endpoints

- [Property Management](API_ENDPOINTS.md#properties)
- [Booking System](API_ENDPOINTS.md#bookings)
- [Long-term Rentals](LONG_TERM_RENTALS_API_GUIDE.md)
- [Smart Locks](SMART_LOCKS_API_GUIDE.md)

---

**Version:** 1.0.0  
**Last Updated:** November 3, 2025
