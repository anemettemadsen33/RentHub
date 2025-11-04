# Guest Screening System - Quick Start Guide 🚀

**Feature:** Task 3.10 - Guest Screening  
**Status:** ✅ Complete  
**Date:** November 3, 2025

---

## 🎯 What You Can Do

Screen potential tenants with:
- ✅ Identity verification (passport, ID, driver's license)
- ✅ Credit checks with scoring
- ✅ Reference verification (landlords, employers)
- ✅ Automated trustworthiness scoring (0-100)
- ✅ Risk assessment (Low/Medium/High)

---

## ⚡ Quick Test (5 Minutes)

### 1. Create Screening
```bash
POST http://localhost/api/v1/guest-screenings
{
  "user_id": 5,
  "booking_id": 12
}
```

### 2. Verify Identity
```bash
POST http://localhost/api/v1/guest-screenings/1/verify-identity
{
  "method": "passport",
  "verified_by": 2
}
```

### 3. Simulate Credit Check
```bash
POST http://localhost/api/v1/credit-checks
{
  "guest_screening_id": 1,
  "user_id": 5,
  "requested_by": 2
}

POST http://localhost/api/v1/credit-checks/1/simulate
{
  "credit_score": 720
}
```

### 4. Add Reference
```bash
POST http://localhost/api/v1/guest-references
{
  "guest_screening_id": 1,
  "user_id": 5,
  "reference_name": "John Smith",
  "reference_email": "john@example.com",
  "relationship": "previous_landlord"
}
```

### 5. Calculate Score
```bash
POST http://localhost/api/v1/guest-screenings/1/calculate-score
```

**Result:** Score 87/100 = **Low Risk** ✅

---

## 📊 Scoring System

| Check | Points | Notes |
|-------|--------|-------|
| Identity | 20 | Passport/ID verified |
| Phone | 10 | SMS verification |
| Email | 10 | Email confirmed |
| Credit | 0-25 | Excellent: 25, Good: 20, Fair: 15 |
| Background | 15 | Clean record |
| References | 0-20 | 7 pts per verified ref (max 3) |
| **Total** | **100** | |

**Risk Levels:**
- 80-100 = Low Risk (Approve) ✅
- 60-79 = Medium Risk (Review) ⚠️
- 0-59 = High Risk (Reject) ❌

---

## 🔑 Key Endpoints

```http
# Screenings
GET    /api/v1/guest-screenings
POST   /api/v1/guest-screenings
GET    /api/v1/guest-screenings/{id}
POST   /api/v1/guest-screenings/{id}/calculate-score
GET    /api/v1/guest-screenings/user/{userId}/latest

# Credit Checks
POST   /api/v1/credit-checks
POST   /api/v1/credit-checks/{id}/simulate
GET    /api/v1/credit-checks/user/{userId}/latest

# References
POST   /api/v1/guest-references
POST   /api/v1/guest-references/{id}/send-request
GET    /api/v1/guest-references/verify/{code} (public)
POST   /api/v1/guest-references/verify/{code} (public)
```

---

## 💡 Pro Tips

1. **Auto-Verification:** Email/phone auto-verified from user profile
2. **Testing:** Use `/simulate` endpoint for credit checks
3. **Max Points:** Add 3 references for maximum 20 points
4. **Expiry:** Screenings expire in 30 days, references in 14 days
5. **Admin Override:** Mark references verified without waiting

---

## 🎨 Frontend Example

```tsx
// Fetch screening
const screening = await fetch(`/api/v1/guest-screenings/user/${userId}/latest`)

// Display risk
<span className={`badge ${screening.risk_level === 'low' ? 'green' : 'red'}`}>
  {screening.risk_level.toUpperCase()} RISK
</span>

// Show score
<progress value={screening.screening_score} max="100" />
```

---

## 🔧 Admin Panel

Create Filament resources:
```bash
php artisan make:filament-resource GuestScreening --generate
php artisan make:filament-resource CreditCheck --generate
php artisan make:filament-resource GuestReference --generate
```

Access at: `http://localhost/admin/guest-screenings`

---

## 📚 Full Documentation

- **Complete Guide:** [TASK_3.10_GUEST_SCREENING_COMPLETE.md](TASK_3.10_GUEST_SCREENING_COMPLETE.md)
- **API Docs:** [API_ENDPOINTS.md](API_ENDPOINTS.md)

---

## ✅ What's Done

- [x] Database schema (4 tables)
- [x] Models with relationships
- [x] API controllers & routes
- [x] Scoring algorithm
- [x] Credit check simulation
- [x] Reference verification system
- [x] Public reference submission
- [x] Statistics endpoint

---

**Test it now!** Use the curl commands above or import into Postman.

🚀 **Next:** Continue with Task 4.2 (AI & Machine Learning) or Task 4.6 (Loyalty Program)
