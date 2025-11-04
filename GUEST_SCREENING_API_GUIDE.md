# Guest Screening API Guide

Complete API reference for the Guest Screening System.

---

## 📚 Table of Contents

1. [Guest Screenings](#guest-screenings)
2. [Credit Checks](#credit-checks)
3. [Guest References](#guest-references)
4. [Screening Documents](#screening-documents)
5. [Examples](#examples)

---

## Guest Screenings

### List All Screenings

```http
GET /api/v1/guest-screenings
```

**Query Parameters:**
- `user_id` (optional) - Filter by user
- `status` (optional) - pending, in_progress, approved, rejected, expired
- `risk_level` (optional) - low, medium, high, unknown
- `active_only` (optional boolean) - Only active (non-expired)
- `per_page` (optional) - Pagination

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "status": "in_progress",
      "risk_level": "low",
      "screening_score": 85,
      "identity_verified": true,
      "phone_verified": true,
      "email_verified": true,
      "credit_check_completed": true,
      "credit_rating": "good",
      "references_count": 2,
      "references_verified": 1
    }
  ],
  "meta": { "current_page": 1, "total": 45 }
}
```

---

### Create Screening

```http
POST /api/v1/guest-screenings
```

**Body:**
```json
{
  "user_id": 5,
  "booking_id": 12
}
```

**Response:** 201 Created
```json
{
  "message": "Guest screening initiated successfully",
  "screening": {
    "id": 1,
    "user_id": 5,
    "booking_id": 12,
    "status": "pending",
    "risk_level": "unknown",
    "expires_at": "2025-12-03T09:25:00.000Z"
  }
}
```

---

### Get Screening

```http
GET /api/v1/guest-screenings/{id}
```

**Response:**
```json
{
  "id": 1,
  "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
  "booking": { "id": 12, "property_id": 3 },
  "status": "in_progress",
  "risk_level": "low",
  "screening_score": 85,
  "identity_verified": true,
  "identity_verified_at": "2025-11-03T10:00:00Z",
  "identity_verification_method": "passport",
  "credit_check": {
    "id": 1,
    "credit_score": 720,
    "credit_rating": "good"
  },
  "references": [
    {
      "id": 1,
      "reference_name": "Jane Smith",
      "relationship": "previous_landlord",
      "status": "verified",
      "rating": 5
    }
  ]
}
```

---

### Update Screening

```http
PUT /api/v1/guest-screenings/{id}
```

**Body:**
```json
{
  "status": "approved",
  "admin_notes": "Excellent background, approved for booking"
}
```

---

### Verify Identity

```http
POST /api/v1/guest-screenings/{id}/verify-identity
```

**Body:**
```json
{
  "method": "passport",
  "verified_by": 2
}
```

**Response:**
```json
{
  "message": "Identity verified successfully",
  "screening": {
    "id": 1,
    "identity_verified": true,
    "identity_verified_at": "2025-11-03T10:00:00Z",
    "screening_score": 75
  }
}
```

---

### Verify Phone

```http
POST /api/v1/guest-screenings/{id}/verify-phone
```

**Response:**
```json
{
  "message": "Phone verified successfully",
  "screening": {
    "id": 1,
    "phone_verified": true,
    "phone_verified_at": "2025-11-03T10:05:00Z",
    "screening_score": 85
  }
}
```

---

### Calculate Score

```http
POST /api/v1/guest-screenings/{id}/calculate-score
```

**Response:**
```json
{
  "screening_id": 1,
  "score": 87,
  "risk_level": "low",
  "screening": {
    "id": 1,
    "screening_score": 87,
    "risk_level": "low"
  }
}
```

---

### Get Statistics

```http
GET /api/v1/guest-screenings/statistics/all
```

**Response:**
```json
{
  "total_screenings": 45,
  "pending": 12,
  "in_progress": 8,
  "approved": 20,
  "rejected": 5,
  "by_risk_level": {
    "low": 18,
    "medium": 15,
    "high": 7
  },
  "avg_screening_score": 72.5,
  "identity_verified": 35,
  "phone_verified": 30,
  "email_verified": 40,
  "credit_checks_completed": 25
}
```

---

### Get User Screenings

```http
GET /api/v1/guest-screenings/user/{userId}
```

**Response:**
```json
[
  {
    "id": 1,
    "status": "approved",
    "screening_score": 87,
    "created_at": "2025-11-01T10:00:00Z"
  },
  {
    "id": 2,
    "status": "pending",
    "screening_score": null,
    "created_at": "2025-11-03T09:00:00Z"
  }
]
```

---

### Get Latest Screening

```http
GET /api/v1/guest-screenings/user/{userId}/latest
```

**Response:**
```json
{
  "id": 2,
  "user_id": 5,
  "status": "in_progress",
  "screening_score": 75,
  "expires_at": "2025-12-03T09:00:00Z"
}
```

---

## Credit Checks

### Create Credit Check

```http
POST /api/v1/credit-checks
```

**Body:**
```json
{
  "guest_screening_id": 1,
  "user_id": 5,
  "requested_by": 2,
  "provider": "equifax"
}
```

**Response:** 201 Created
```json
{
  "message": "Credit check initiated successfully",
  "credit_check": {
    "id": 1,
    "guest_screening_id": 1,
    "status": "pending",
    "requested_at": "2025-11-03T10:00:00Z"
  }
}
```

---

### Update Credit Check

```http
PUT /api/v1/credit-checks/{id}
```

**Body:**
```json
{
  "status": "completed",
  "credit_score": 720,
  "credit_rating": "good",
  "total_accounts": 8,
  "open_accounts": 6,
  "on_time_payments": 95,
  "late_payments": 2,
  "passed": true
}
```

---

### Simulate Credit Check (Testing)

```http
POST /api/v1/credit-checks/{id}/simulate
```

**Body:**
```json
{
  "credit_score": 720
}
```

**Response:**
```json
{
  "message": "Credit check simulated successfully",
  "credit_check": {
    "id": 1,
    "credit_score": 720,
    "max_score": 850,
    "credit_rating": "good",
    "total_accounts": 8,
    "open_accounts": 6,
    "credit_utilization": 35,
    "on_time_payments": 95,
    "late_payments": 2,
    "missed_payments": 0,
    "defaults": 0,
    "bankruptcies": 0,
    "status": "completed",
    "passed": true
  }
}
```

---

### Get User Credit Checks

```http
GET /api/v1/credit-checks/user/{userId}
```

---

### Get Latest Credit Check

```http
GET /api/v1/credit-checks/user/{userId}/latest
```

---

## Guest References

### Create Reference

```http
POST /api/v1/guest-references
```

**Body:**
```json
{
  "guest_screening_id": 1,
  "user_id": 5,
  "reference_name": "John Smith",
  "reference_email": "john.smith@example.com",
  "reference_phone": "+1234567890",
  "relationship": "previous_landlord",
  "relationship_description": "Landlord for 2 years at previous address"
}
```

**Response:** 201 Created
```json
{
  "message": "Guest reference added successfully",
  "reference": {
    "id": 1,
    "guest_screening_id": 1,
    "reference_name": "John Smith",
    "verification_code": "abc123xyz456...",
    "status": "pending",
    "expires_at": "2025-11-17T10:00:00Z"
  }
}
```

---

### Send Verification Request

```http
POST /api/v1/guest-references/{id}/send-request
```

**Response:**
```json
{
  "message": "Verification request sent successfully",
  "reference": {
    "id": 1,
    "status": "contacted",
    "contact_attempts": 1,
    "last_contact_at": "2025-11-03T10:00:00Z"
  }
}
```

---

### Resend Request

```http
POST /api/v1/guest-references/{id}/resend-request
```

---

### Get Reference by Code (Public)

```http
GET /api/v1/guest-references/verify/{code}
```

**No authentication required**

**Response:**
```json
{
  "reference": {
    "id": 1,
    "reference_name": "John Smith",
    "relationship": "previous_landlord"
  },
  "guest_name": "Jane Doe",
  "expired": false,
  "responded": false
}
```

---

### Submit Reference Response (Public)

```http
POST /api/v1/guest-references/verify/{code}
```

**No authentication required**

**Body:**
```json
{
  "rating": 5,
  "comments": "Excellent tenant, always paid on time",
  "would_rent_again": true,
  "reliable_tenant": true,
  "damages_caused": false,
  "payment_issues": false,
  "strengths": "Very responsible, maintained property well",
  "concerns": "None"
}
```

**Response:**
```json
{
  "message": "Reference response submitted successfully",
  "reference": {
    "id": 1,
    "responded": true,
    "responded_at": "2025-11-03T11:00:00Z",
    "rating": 5,
    "status": "verified"
  }
}
```

---

### Mark as Verified (Admin)

```http
POST /api/v1/guest-references/{id}/mark-verified
```

**Body:**
```json
{
  "rating": 5,
  "comments": "Verified via phone call",
  "verification_notes": "Called landlord, confirmed excellent tenant"
}
```

---

### Get Screening References

```http
GET /api/v1/guest-references/screening/{screeningId}
```

**Response:**
```json
{
  "references": [
    {
      "id": 1,
      "reference_name": "John Smith",
      "relationship": "previous_landlord",
      "status": "verified",
      "responded": true,
      "rating": 5
    }
  ],
  "statistics": {
    "total": 2,
    "pending": 0,
    "contacted": 1,
    "verified": 1,
    "responded": 1,
    "average_rating": 5.0
  }
}
```

---

## Examples

### Complete Screening Workflow

```bash
# 1. Create screening
curl -X POST http://localhost/api/v1/guest-screenings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"user_id": 5, "booking_id": 12}'

# 2. Verify identity
curl -X POST http://localhost/api/v1/guest-screenings/1/verify-identity \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"method": "passport", "verified_by": 2}'

# 3. Verify phone
curl -X POST http://localhost/api/v1/guest-screenings/1/verify-phone \
  -H "Authorization: Bearer $TOKEN"

# 4. Create & simulate credit check
curl -X POST http://localhost/api/v1/credit-checks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"guest_screening_id": 1, "user_id": 5, "requested_by": 2}'

curl -X POST http://localhost/api/v1/credit-checks/1/simulate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"credit_score": 720}'

# 5. Add reference
curl -X POST http://localhost/api/v1/guest-references \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "guest_screening_id": 1,
    "user_id": 5,
    "reference_name": "John Smith",
    "reference_email": "john@example.com",
    "relationship": "previous_landlord"
  }'

# 6. Send reference request
curl -X POST http://localhost/api/v1/guest-references/1/send-request \
  -H "Authorization: Bearer $TOKEN"

# 7. Calculate final score
curl -X POST http://localhost/api/v1/guest-screenings/1/calculate-score \
  -H "Authorization: Bearer $TOKEN"

# 8. Approve screening
curl -X PUT http://localhost/api/v1/guest-screenings/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status": "approved"}'
```

---

### Reference Responds (Public - No Auth)

```bash
# Get reference details
curl http://localhost/api/v1/guest-references/verify/abc123xyz

# Submit response
curl -X POST http://localhost/api/v1/guest-references/verify/abc123xyz \
  -H "Content-Type: application/json" \
  -d '{
    "rating": 5,
    "comments": "Great tenant",
    "would_rent_again": true,
    "reliable_tenant": true,
    "damages_caused": false,
    "payment_issues": false,
    "strengths": "Always paid on time",
    "concerns": "None"
  }'
```

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 409 | Conflict (e.g., already responded) |
| 410 | Gone (e.g., expired) |
| 422 | Validation Error |

---

## Notes

- All timestamps are in ISO 8601 format (UTC)
- Screenings expire after 30 days
- References expire after 14 days
- Maximum 3 references per screening (for scoring)
- Credit scores range from 300-850
- Screening scores range from 0-100

---

**More Info:** [TASK_3.10_GUEST_SCREENING_COMPLETE.md](TASK_3.10_GUEST_SCREENING_COMPLETE.md)
