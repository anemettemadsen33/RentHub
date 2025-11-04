# 🧪 Concierge Services API Testing Guide

## 📋 Complete API Testing Examples

Use these examples to test all API endpoints with **Postman**, **Thunder Client**, or **curl**.

---

## 🔓 Public Endpoints (No Authentication)

### 1. Get All Services

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services" \
  -H "Accept: application/json"
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
        "name": "Airport Transfer - Standard",
        "description": "Comfortable sedan transfer...",
        "service_type": "airport_pickup",
        "base_price": 150.00,
        "price_unit": "per trip",
        "duration_minutes": 60,
        "max_guests": 3,
        "is_available": true,
        "service_provider": {
          "name": "Michael Anderson",
          "average_rating": 4.8
        }
      }
    ],
    "per_page": 15,
    "total": 10
  }
}
```

---

### 2. Filter Services by Type

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services?service_type=airport_pickup
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services?service_type=airport_pickup" \
  -H "Accept: application/json"
```

---

### 3. Filter by Max Price

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services?max_price=200
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services?max_price=200" \
  -H "Accept: application/json"
```

---

### 4. Filter by Guest Capacity

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services?max_guests=4
Accept: application/json
```

---

### 5. Get Service Types

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services/types
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services/types" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "airport_pickup",
      "label": "Airport Pickup",
      "icon": "✈️",
      "description": "Professional airport transfer service"
    },
    {
      "value": "grocery_delivery",
      "label": "Grocery Delivery",
      "icon": "🛒",
      "description": "Fresh groceries delivered to your door"
    }
  ]
}
```

---

### 6. Get Featured Services

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services/featured
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services/featured" \
  -H "Accept: application/json"
```

---

### 7. Get Single Service

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-services/1
Accept: application/json
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-services/1" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_provider_id": 1,
    "name": "Airport Transfer - Standard",
    "description": "Comfortable sedan transfer from/to Bucharest airport...",
    "service_type": "airport_pickup",
    "base_price": 150.00,
    "price_unit": "per trip",
    "duration_minutes": 60,
    "max_guests": 3,
    "pricing_extras": [
      {
        "name": "Extra luggage (4+ bags)",
        "price": 20
      },
      {
        "name": "Child seat",
        "price": 15
      }
    ],
    "requirements": [
      "Flight number required",
      "Arrival time must be provided"
    ],
    "images": [
      "https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=800"
    ],
    "is_available": true,
    "advance_booking_hours": 12,
    "service_provider": {
      "id": 1,
      "name": "Michael Anderson",
      "company_name": "Elite Transport Services",
      "average_rating": 4.8,
      "verified": true
    }
  }
}
```

---

## 🔐 Authenticated Endpoints

**Note:** Replace `YOUR_AUTH_TOKEN` with actual token from login.

### 8. Get My Bookings

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-bookings
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
```

**cURL:**
```bash
curl -X GET "http://renthub.test/api/v1/concierge-bookings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN"
```

---

### 9. Get Upcoming Bookings

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-bookings?upcoming=true
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
```

---

### 10. Get Booking Statistics

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-bookings/stats
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_bookings": 15,
    "upcoming_bookings": 3,
    "completed_bookings": 10,
    "total_spent": 2450.00,
    "favorite_service": {
      "id": 1,
      "name": "Airport Transfer - Standard"
    }
  }
}
```

---

### 11. Create New Booking

**Request:**
```bash
POST http://renthub.test/api/v1/concierge-bookings
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
Content-Type: application/json

{
  "concierge_service_id": 1,
  "property_id": 5,
  "booking_id": null,
  "service_date": "2024-12-25",
  "service_time": "14:00",
  "guests_count": 2,
  "special_requests": "Please call when arriving",
  "contact_details": {
    "name": "John Doe",
    "phone": "+40721234567",
    "email": "john@example.com"
  }
}
```

**cURL:**
```bash
curl -X POST "http://renthub.test/api/v1/concierge-bookings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "concierge_service_id": 1,
    "service_date": "2024-12-25",
    "service_time": "14:00",
    "guests_count": 2,
    "contact_details": {
      "name": "John Doe",
      "phone": "+40721234567",
      "email": "john@example.com"
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 1,
    "booking_reference": "CONC-ABCD123456",
    "user_id": 1,
    "concierge_service_id": 1,
    "service_date": "2024-12-25",
    "service_time": "2024-12-25 14:00:00",
    "guests_count": 2,
    "special_requests": "Please call when arriving",
    "base_price": 150.00,
    "extras_price": 0.00,
    "total_price": 150.00,
    "currency": "RON",
    "status": "pending",
    "payment_status": "pending",
    "contact_details": {
      "name": "John Doe",
      "phone": "+40721234567",
      "email": "john@example.com"
    },
    "created_at": "2024-11-03T12:00:00.000000Z"
  }
}
```

**Error Response (Validation Failed):**
```json
{
  "success": false,
  "errors": {
    "service_date": [
      "The service date field is required."
    ],
    "contact_details.email": [
      "The contact_details.email field is required."
    ]
  }
}
```

**Error Response (Advance Booking Required):**
```json
{
  "success": false,
  "message": "This service requires booking at least 24 hours in advance."
}
```

---

### 12. Get Single Booking

**Request:**
```bash
GET http://renthub.test/api/v1/concierge-bookings/1
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
```

---

### 13. Update Booking

**Request:**
```bash
PUT http://renthub.test/api/v1/concierge-bookings/1
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
Content-Type: application/json

{
  "service_date": "2024-12-26",
  "service_time": "15:00",
  "guests_count": 3,
  "special_requests": "Updated: Please call 30 minutes before"
}
```

**cURL:**
```bash
curl -X PUT "http://renthub.test/api/v1/concierge-bookings/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "service_date": "2024-12-26",
    "service_time": "15:00",
    "guests_count": 3
  }'
```

---

### 14. Cancel Booking

**Request:**
```bash
POST http://renthub.test/api/v1/concierge-bookings/1/cancel
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
Content-Type: application/json

{
  "reason": "Change of plans - leaving earlier"
}
```

**cURL:**
```bash
curl -X POST "http://renthub.test/api/v1/concierge-bookings/1/cancel" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Change of plans"}'
```

**Response:**
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled",
    "cancelled_at": "2024-11-03T12:30:00.000000Z",
    "cancellation_reason": "Change of plans - leaving earlier"
  }
}
```

---

### 15. Add Review to Completed Booking

**Request:**
```bash
POST http://renthub.test/api/v1/concierge-bookings/1/review
Accept: application/json
Authorization: Bearer YOUR_AUTH_TOKEN
Content-Type: application/json

{
  "rating": 5,
  "review": "Excellent service! Driver was professional and car was very clean."
}
```

**cURL:**
```bash
curl -X POST "http://renthub.test/api/v1/concierge-bookings/1/review" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rating": 5,
    "review": "Excellent service!"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Review added successfully",
  "data": {
    "id": 1,
    "rating": 5,
    "review": "Excellent service! Driver was professional and car was very clean.",
    "reviewed_at": "2024-11-03T13:00:00.000000Z"
  }
}
```

---

## 🧪 Testing Scenarios

### Scenario 1: Browse & Book a Service

1. Get all services → `/concierge-services`
2. Filter by type → `/concierge-services?service_type=airport_pickup`
3. View service details → `/concierge-services/1`
4. Create booking → `POST /concierge-bookings`
5. View booking → `/concierge-bookings/1`

### Scenario 2: Manage Bookings

1. Get my bookings → `/concierge-bookings`
2. Get upcoming only → `/concierge-bookings?upcoming=true`
3. Update booking → `PUT /concierge-bookings/1`
4. Cancel booking → `POST /concierge-bookings/1/cancel`

### Scenario 3: Complete & Review

1. Wait for booking status to be "completed" (admin changes)
2. Add review → `POST /concierge-bookings/1/review`
3. View statistics → `/concierge-bookings/stats`

---

## 📝 Postman Collection

### Import this collection:

```json
{
  "info": {
    "name": "RentHub - Concierge Services",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://renthub.test/api/v1"
    },
    {
      "key": "auth_token",
      "value": "YOUR_AUTH_TOKEN_HERE"
    }
  ],
  "item": [
    {
      "name": "Get All Services",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/concierge-services"
      }
    },
    {
      "name": "Create Booking",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{auth_token}}"
          }
        ],
        "url": "{{base_url}}/concierge-bookings",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"concierge_service_id\": 1,\n  \"service_date\": \"2024-12-25\",\n  \"service_time\": \"14:00\",\n  \"guests_count\": 2,\n  \"contact_details\": {\n    \"name\": \"John Doe\",\n    \"phone\": \"+40721234567\",\n    \"email\": \"john@example.com\"\n  }\n}"
        }
      }
    }
  ]
}
```

---

## ✅ Testing Checklist

- [ ] Can browse all services
- [ ] Filters work (type, price, guests)
- [ ] Can view service details with images
- [ ] Service types endpoint returns all types
- [ ] Featured services show random selection
- [ ] Can create booking with valid data
- [ ] Booking validation works (date, contact info)
- [ ] Advance booking hours check works
- [ ] Max guests validation works
- [ ] Can view own bookings only
- [ ] Can update pending/confirmed bookings
- [ ] Can cancel bookings with reason
- [ ] Can add reviews to completed bookings
- [ ] Cannot review twice
- [ ] Statistics endpoint works
- [ ] Unauthorized users get 401/403

---

## 🔍 Common Error Codes

| Code | Meaning | Solution |
|------|---------|----------|
| 401 | Unauthorized | Add valid Authorization header |
| 403 | Forbidden | User doesn't own this resource |
| 404 | Not Found | Check service/booking ID |
| 422 | Validation Error | Check request body format |
| 500 | Server Error | Check Laravel logs |

---

## 📊 Expected Response Times

- List services: < 200ms
- Single service: < 100ms
- Create booking: < 300ms
- Update booking: < 200ms
- Get statistics: < 250ms

---

Happy Testing! 🎉
