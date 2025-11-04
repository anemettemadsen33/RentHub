# 🧪 Smart Pricing - Testing Guide

## 📋 Test Scenarios

### Prerequisites
1. Have a registered user with a property
2. Get authentication token
3. Note your property ID

---

## ✅ Test 1: Create Weekend Pricing Rule

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "weekend",
    "name": "Weekend Premium",
    "description": "20% increase for Friday and Saturday",
    "days_of_week": [5, 6],
    "adjustment_type": "percentage",
    "adjustment_value": 20,
    "priority": 10,
    "is_active": true
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing rule created successfully",
  "data": {
    "id": 1,
    "property_id": 1,
    "type": "weekend",
    "name": "Weekend Premium",
    "adjustment_value": "20.00",
    "is_active": true,
    "priority": 10
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 2: Create Seasonal Pricing Rule

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "seasonal",
    "name": "Summer Peak Season",
    "description": "High season pricing for summer",
    "start_date": "2025-06-01",
    "end_date": "2025-08-31",
    "adjustment_type": "percentage",
    "adjustment_value": 35,
    "priority": 5,
    "is_active": true
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing rule created successfully",
  "data": {
    "id": 2,
    "type": "seasonal",
    "name": "Summer Peak Season",
    "start_date": "2025-06-01",
    "end_date": "2025-08-31",
    "adjustment_value": "35.00"
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 3: Create Last-Minute Discount

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "last_minute",
    "name": "Last Minute Deal",
    "description": "15% discount for bookings within 7 days",
    "last_minute_days": 7,
    "adjustment_type": "percentage",
    "adjustment_value": -15,
    "priority": 15,
    "is_active": true
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing rule created successfully",
  "data": {
    "id": 3,
    "type": "last_minute",
    "name": "Last Minute Deal",
    "adjustment_value": "-15.00",
    "priority": 15
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 4: List All Pricing Rules

### Request
```bash
curl -X GET http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "type": "last_minute",
      "name": "Last Minute Deal",
      "priority": 15
    },
    {
      "id": 1,
      "type": "weekend",
      "name": "Weekend Premium",
      "priority": 10
    },
    {
      "id": 2,
      "type": "seasonal",
      "name": "Summer Peak Season",
      "priority": 5
    }
  ]
}
```

**Note:** Should be ordered by priority DESC

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 5: Calculate Price for Regular Weekday

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/calculate-price \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "check_in": "2025-07-07",
    "check_out": "2025-07-10"
  }'
```

### Expected Calculation
- Base price: 100€
- Monday-Wednesday (3 nights)
- Summer season: +35% = 135€ per night
- Total: 3 × 135 = 405€ + cleaning fee

### Expected Response
```json
{
  "success": true,
  "data": {
    "base_price": 100,
    "daily_prices": {
      "2025-07-07": 135,
      "2025-07-08": 135,
      "2025-07-09": 135
    },
    "subtotal": 405,
    "cleaning_fee": 50,
    "total_price": 455,
    "nights": 3,
    "average_price_per_night": 135
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 6: Calculate Price for Weekend

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/calculate-price \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "check_in": "2025-07-18",
    "check_out": "2025-07-21"
  }'
```

### Expected Calculation
- Base price: 100€
- Friday-Sunday (3 nights)
- Summer season: +35% = 135€
- Weekend (Fri, Sat): +20% on 135 = 162€
- Friday: 162€, Saturday: 162€, Sunday: 135€
- Total: 162 + 162 + 135 = 459€ + cleaning fee

### Expected Response
```json
{
  "success": true,
  "data": {
    "daily_prices": {
      "2025-07-18": 162,
      "2025-07-19": 162,
      "2025-07-20": 135
    },
    "subtotal": 459,
    "total_price": 509,
    "nights": 3
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 7: Get Pricing Calendar

### Request
```bash
curl -X GET "http://localhost/api/v1/properties/1/pricing-calendar?start_date=2025-07-01&end_date=2025-07-15" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "data": {
    "2025-07-01": {
      "date": "2025-07-01",
      "day_of_week": "Tuesday",
      "price": 135,
      "is_weekend": false,
      "is_available": true
    },
    "2025-07-04": {
      "date": "2025-07-04",
      "day_of_week": "Friday",
      "price": 162,
      "is_weekend": false,
      "is_available": true
    },
    "2025-07-05": {
      "date": "2025-07-05",
      "day_of_week": "Saturday",
      "price": 162,
      "is_weekend": true,
      "is_available": true
    }
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 8: Update Pricing Rule

### Request
```bash
curl -X PUT http://localhost/api/v1/properties/1/pricing-rules/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "adjustment_value": 25
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing rule updated successfully",
  "data": {
    "id": 1,
    "adjustment_value": "25.00"
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 9: Toggle Rule Active Status

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules/1/toggle \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "message": "Rule status updated",
  "data": {
    "id": 1,
    "is_active": false
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 10: Generate AI Price Suggestion

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/price-suggestions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-01",
    "end_date": "2025-12-31"
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Price suggestion generated successfully",
  "data": {
    "id": 1,
    "property_id": 1,
    "start_date": "2025-12-01",
    "end_date": "2025-12-31",
    "current_price": "100.00",
    "suggested_price": "145.00",
    "min_recommended_price": "120.00",
    "max_recommended_price": "150.00",
    "confidence_score": 85,
    "market_average_price": "140.00",
    "competitor_count": 12,
    "demand_score": 70,
    "status": "pending",
    "expires_at": "2025-11-09T21:00:00.000000Z"
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 11: List Price Suggestions

### Request
```bash
curl -X GET "http://localhost/api/v1/properties/1/price-suggestions?status=pending" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "suggested_price": "145.00",
      "confidence_score": 85,
      "status": "pending",
      "price_difference": 45
    }
  ]
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 12: Get Market Analysis

### Request
```bash
curl -X GET http://localhost/api/v1/properties/1/market-analysis \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "data": {
    "property_price": 100,
    "competitor_count": 15,
    "market_average": 125.50,
    "market_min": 80,
    "market_max": 200,
    "competitors": [
      {
        "id": 5,
        "title": "Luxury Apartment",
        "price": 150,
        "rating": 4.8,
        "reviews": 45,
        "city": "București"
      }
    ]
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 13: Accept Price Suggestion

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/price-suggestions/1/accept \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "message": "Price suggestion accepted and applied",
  "data": {
    "id": 1,
    "status": "accepted",
    "accepted_at": "2025-11-02T21:45:00.000000Z"
  }
}
```

**Note:** Property price should be updated to suggested_price

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 14: Reject Price Suggestion

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/price-suggestions/2/reject \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Price too high for current market"
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "Price suggestion rejected",
  "data": {
    "id": 2,
    "status": "rejected",
    "rejected_at": "2025-11-02T21:46:00.000000Z",
    "notes": "Price too high for current market"
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 15: Optimize Pricing (90 days)

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-optimize \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing optimization completed",
  "data": {
    "period": {
      "start": "2025-11-02",
      "end": "2026-01-31"
    },
    "suggestions": [
      {
        "id": 3,
        "start_date": "2025-11-02",
        "end_date": "2025-12-02",
        "suggested_price": "135.00",
        "confidence_score": 82
      },
      {
        "id": 4,
        "start_date": "2025-12-03",
        "end_date": "2026-01-02",
        "suggested_price": "165.00",
        "confidence_score": 88
      },
      {
        "id": 5,
        "start_date": "2026-01-03",
        "end_date": "2026-01-31",
        "suggested_price": "120.00",
        "confidence_score": 75
      }
    ]
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 16: Batch Accept High Confidence

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/price-suggestions/batch-accept \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "min_confidence": 80
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "2 high-confidence suggestions accepted",
  "data": {
    "accepted_count": 2,
    "min_confidence": 80
  }
}
```

### Status: ✅ Pass / ❌ Fail

---

## ✅ Test 17: Delete Pricing Rule

### Request
```bash
curl -X DELETE http://localhost/api/v1/properties/1/pricing-rules/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "success": true,
  "message": "Pricing rule deleted successfully"
}
```

### Status: ✅ Pass / ❌ Fail

---

## 🔒 Authorization Tests

### Test 18: Unauthorized Access (No Token)

### Request
```bash
curl -X GET http://localhost/api/v1/properties/1/pricing-rules
```

### Expected Response
```json
{
  "message": "Unauthenticated."
}
```

**Status Code:** 401

### Status: ✅ Pass / ❌ Fail

---

### Test 19: Access Other User's Property

### Request
```bash
curl -X GET http://localhost/api/v1/properties/999/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Expected Response
```json
{
  "error": "Unauthorized"
}
```

**Status Code:** 403

### Status: ✅ Pass / ❌ Fail

---

## ❌ Validation Error Tests

### Test 20: Create Rule Without Required Fields

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Rule"
  }'
```

### Expected Response
```json
{
  "success": false,
  "errors": {
    "type": ["The type field is required."],
    "adjustment_type": ["The adjustment type field is required."],
    "adjustment_value": ["The adjustment value field is required."]
  }
}
```

**Status Code:** 422

### Status: ✅ Pass / ❌ Fail

---

### Test 21: Invalid Date Range

### Request
```bash
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "seasonal",
    "name": "Test",
    "start_date": "2025-08-01",
    "end_date": "2025-07-01",
    "adjustment_type": "percentage",
    "adjustment_value": 20
  }'
```

### Expected Response
```json
{
  "success": false,
  "errors": {
    "end_date": ["The end date field must be a date after start date."]
  }
}
```

**Status Code:** 422

### Status: ✅ Pass / ❌ Fail

---

## 📊 Test Summary

| Test # | Test Name | Status | Notes |
|--------|-----------|--------|-------|
| 1 | Create Weekend Rule | ⬜ | |
| 2 | Create Seasonal Rule | ⬜ | |
| 3 | Create Last-Minute | ⬜ | |
| 4 | List All Rules | ⬜ | |
| 5 | Calculate Regular Price | ⬜ | |
| 6 | Calculate Weekend Price | ⬜ | |
| 7 | Get Pricing Calendar | ⬜ | |
| 8 | Update Rule | ⬜ | |
| 9 | Toggle Rule Status | ⬜ | |
| 10 | Generate AI Suggestion | ⬜ | |
| 11 | List Suggestions | ⬜ | |
| 12 | Market Analysis | ⬜ | |
| 13 | Accept Suggestion | ⬜ | |
| 14 | Reject Suggestion | ⬜ | |
| 15 | Optimize Pricing | ⬜ | |
| 16 | Batch Accept | ⬜ | |
| 17 | Delete Rule | ⬜ | |
| 18 | Unauthorized Access | ⬜ | |
| 19 | Access Other Property | ⬜ | |
| 20 | Invalid Fields | ⬜ | |
| 21 | Invalid Date Range | ⬜ | |

**Total Tests:** 21
**Passed:** 0
**Failed:** 0
**Pending:** 21

---

## 🎯 Testing Checklist

- [ ] All CRUD operations work
- [ ] Price calculations are accurate
- [ ] Priority system works correctly
- [ ] AI suggestions generate
- [ ] Market analysis returns data
- [ ] Authorization checks work
- [ ] Validation prevents bad data
- [ ] Calendar view displays correctly
- [ ] Multiple rules combine properly
- [ ] Date ranges are respected
- [ ] Days of week filter correctly

---

**Test Date:** _____________
**Tested By:** _____________
**Environment:** Development / Staging / Production
**Notes:** _____________________________________________

