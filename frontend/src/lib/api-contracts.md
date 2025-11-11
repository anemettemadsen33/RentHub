# API Contracts (Draft)

This document sketches JSON contracts for new / enhanced endpoints to align frontend & backend.

## 1. Dashboard Stats
GET /api/v1/dashboard/stats
Response 200:
{
  "properties": 3,
  "bookingsUpcoming": 5,
  "revenueLast30": 4350.75,
  "guestsUnique": 12
}
Errors: 401, 500

## 2. Comparison Listing (Public/Authenticated)
GET /api/v1/property-comparison
Response 200 (variant A):
{ "items": [ {"id": 12, "title": "Loft", "pricePerNight": 120, "location": "Cluj" } ] }
Response 200 (variant B - legacy):
[ {"id": 12, "title": "Loft", "pricePerNight": 120, "location": "Cluj" } ]

## 3. Saved Searches CRUD
GET /api/v1/saved-searches
Response:
[ {"id": 14, "name": "Budget Flats", "params": {"city": "Bucharest","minPrice":300}, "alerts": true, "createdAt": "2025-11-01T10:00:00Z" } ]
POST /api/v1/saved-searches
Request:
{ "name": "String <= 60", "params": { ... arbitrary filter object ... }, "alerts": true }
Response 201:
{ "id": 15, "name": "...", "params": {...}, "alerts": true, "createdAt": "ISO", "updatedAt": "ISO" }
PUT /api/v1/saved-searches/{id} similar to POST (all fields optional)
DELETE /api/v1/saved-searches/{id} -> 204

## 4. Notification Preferences (already partially exists)
PUT /api/v1/notifications/preferences
Request:
{ "email": {"booking": true, "message": true}, "push": {"booking": true, "message": false} }
Response 200 mirrors stored object.

## 5. Messaging Threads Summary
GET /api/v1/conversations?summary=1
Response:
[ {"id": 44, "lastMessageExcerpt": "See you soon", "unreadCount": 2, "participants": [ {"id":7,"name":"Ana"} ], "updatedAt": "ISO" } ]

## 6. Insurance Plans
POST /api/v1/insurance/plans/available
Request:
{ "bookingId": 101 }
Response:
[ { "id": "basic", "name": "Basic Coverage", "price": 19.99, "currency": "EUR", "coverage": ["cancellation","damage"], "recommended": true } ]

## 7. Insurance Claims Submission
POST /api/v1/insurance/claims
Request:
{ "bookingId": 101, "reason": "damage", "description": "Broken lamp", "amount": 45.00 }
Response 201:
{ "id": 77, "bookingId": 101, "status": "submitted", "submittedAt": "ISO" }

## 8. Smart Lock Activity
GET /api/v1/properties/{propertyId}/smart-locks/{lockId}/activities
Response:
[ { "timestamp": "ISO", "event": "unlock", "source": "code", "codeId": 501, "userId": 7 } ]

## 9. Access Code Create
POST /api/v1/properties/{propertyId}/smart-locks/{lockId}/access-codes
Request:
{ "type": "guest" | "temporary", "validFrom": "ISO?", "validTo": "ISO?" }
Response 201:
{ "id": 501, "code": "173920", "type": "guest", "validFrom": "ISO", "validTo": "ISO" }

## 10. Calendar Bulk Pricing
POST /api/v1/properties/{propertyId}/calendar/bulk-pricing
Request:
{ "dates": ["2025-11-10","2025-11-11"], "price": 120.00, "currency": "EUR" }
Response 200:
{ "updated": 2 }

## 11. Standard Error Shape (Recommendation)
{
  "message": "Human readable",
  "code": "BOOKING_NOT_FOUND",
  "errors": { "fieldName": ["Validation detail..."] }
}

## 12. Rate Limit Exceeded (Auth Bucket)
HTTP 429
{ "message": "Too many authentication attempts. Please try again later.", "retryAfter": 60 }

## 13. Dashboard Upcoming Bookings Endpoint (Optional Split)
GET /api/v1/dashboard/bookings/upcoming
Response:
[ { "id": 101, "propertyId": 22, "property": "Luxury Apartment", "checkIn": "2025-11-15", "checkOut": "2025-11-18", "status": "confirmed" } ]

---
Next Steps:
1. Backend implement missing endpoints & unify error shape.
2. Generate Zod schemas per section into `src/lib/schemas/` & replace ad-hoc types.
3. Add React Query integration for caching (keys: `dashboard:stats`, `comparison:list`).
4. Add feature tests for new endpoints (backend) + contract tests (frontend -> mock server or MSW).
