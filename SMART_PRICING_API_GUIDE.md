# 🎯 Smart Pricing API - Quick Reference Guide

## 📌 Base URL
```
http://localhost/api/v1/properties/{propertyId}
```

## 🔐 Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 🏷️ Pricing Rules Endpoints

### 1. List All Rules
```http
GET /pricing-rules
```
Lists all pricing rules for a property.

### 2. Create New Rule
```http
POST /pricing-rules
Content-Type: application/json

{
  "type": "weekend|seasonal|holiday|demand|last_minute|early_bird|weekly|monthly",
  "name": "Rule Name",
  "description": "Optional description",
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "days_of_week": [5, 6],  // 0=Sunday, 6=Saturday
  "adjustment_type": "percentage|fixed",
  "adjustment_value": 20,
  "priority": 10,
  "is_active": true
}
```

### 3. Update Rule
```http
PUT /pricing-rules/{ruleId}
Content-Type: application/json

{
  "adjustment_value": 25,
  "is_active": false
}
```

### 4. Delete Rule
```http
DELETE /pricing-rules/{ruleId}
```

### 5. Toggle Rule Status
```http
POST /pricing-rules/{ruleId}/toggle
```

### 6. Calculate Price
```http
POST /calculate-price
Content-Type: application/json

{
  "check_in": "2025-07-15",
  "check_out": "2025-07-22"
}
```

**Response:**
```json
{
  "base_price": 100,
  "daily_prices": { "2025-07-15": 130, ... },
  "subtotal": 950,
  "cleaning_fee": 50,
  "total_price": 1000,
  "nights": 7,
  "average_price_per_night": 135.71
}
```

### 7. Get Pricing Calendar
```http
GET /pricing-calendar?start_date=2025-07-01&end_date=2025-07-31
```

---

## 🤖 AI Price Suggestions Endpoints

### 1. List Suggestions
```http
GET /price-suggestions?status=pending
```
**Status values:** pending, accepted, rejected, expired

### 2. Generate New Suggestion
```http
POST /price-suggestions
Content-Type: application/json

{
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}
```

### 3. Accept Suggestion
```http
POST /price-suggestions/{suggestionId}/accept
```
Applies the suggested price to the property.

### 4. Reject Suggestion
```http
POST /price-suggestions/{suggestionId}/reject
Content-Type: application/json

{
  "reason": "Optional rejection reason"
}
```

### 5. Market Analysis
```http
GET /market-analysis
```

**Response:**
```json
{
  "property_price": 100,
  "competitor_count": 15,
  "market_average": 125,
  "market_min": 80,
  "market_max": 200,
  "competitors": [...]
}
```

### 6. Optimize Pricing (90 days)
```http
POST /pricing-optimize
```
Generates AI suggestions for the next 90 days.

### 7. Batch Accept High-Confidence
```http
POST /price-suggestions/batch-accept
Content-Type: application/json

{
  "min_confidence": 80
}
```

---

## 📊 Pricing Rule Types

| Type | Description | Example |
|------|-------------|---------|
| `seasonal` | Seasonal pricing adjustments | Summer +30%, Winter -10% |
| `weekend` | Weekend vs weekday pricing | Friday-Saturday +20% |
| `holiday` | Special holidays | Christmas +50% |
| `demand` | High-demand periods | Local events +40% |
| `last_minute` | Last-minute bookings | < 7 days -15% |
| `early_bird` | Early bookings | > 60 days -10% |
| `weekly` | Weekly stay discounts | 7+ nights -15% |
| `monthly` | Monthly stay discounts | 30+ nights -25% |

---

## 🎯 Adjustment Types

### Percentage
```json
{
  "adjustment_type": "percentage",
  "adjustment_value": 20  // +20%
}
```
Adds/subtracts percentage from base price.

### Fixed Amount
```json
{
  "adjustment_type": "fixed",
  "adjustment_value": 50  // +50 EUR/USD
}
```
Adds/subtracts fixed amount from base price.

---

## 🔢 Priority System

Rules with **higher priority** override rules with lower priority:
- Priority 20: Last-minute, urgent rules
- Priority 10: Weekend/holiday rules
- Priority 5: Seasonal rules
- Priority 0: Default rules

**Example:**
```json
[
  { "type": "last_minute", "priority": 20, "adjustment": -15% },
  { "type": "weekend", "priority": 10, "adjustment": +20% },
  { "type": "seasonal", "priority": 5, "adjustment": +30% }
]
```

If all apply to same date: Last-minute rule wins (priority 20).

---

## 💡 AI Suggestion Fields

### Request
```json
{
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}
```

### Response
```json
{
  "id": 1,
  "current_price": 100,
  "suggested_price": 135,
  "min_recommended_price": 120,
  "max_recommended_price": 150,
  "confidence_score": 85,  // 0-100%
  "market_average_price": 130,
  "competitor_count": 12,
  "demand_score": 75,
  "price_difference": 35,  // %
  "factors": {
    "market_analysis": {...},
    "demand_analysis": {...}
  },
  "status": "pending",
  "expires_at": "2025-11-09T21:00:00Z"
}
```

---

## 🧮 Price Calculation Algorithm

### Step 1: Base Price
```
base_price = property.price_per_night
```

### Step 2: Apply Rules (by priority)
```
For each applicable rule (highest priority first):
  if rule.adjustment_type == 'percentage':
    price = price + (price × rule.adjustment_value / 100)
  else:
    price = price + rule.adjustment_value
```

### Step 3: Daily Breakdown
```
For each day in range:
  daily_prices[date] = calculate_price_for_date(date)
```

### Step 4: Add Fees
```
subtotal = sum(daily_prices)
total = subtotal + cleaning_fee
```

---

## 🎨 Example Workflows

### Workflow 1: Setup Weekend Pricing
```bash
# 1. Create weekend rule
POST /properties/1/pricing-rules
{
  "type": "weekend",
  "name": "Weekend Premium",
  "days_of_week": [5, 6],
  "adjustment_type": "percentage",
  "adjustment_value": 20,
  "priority": 10
}

# 2. Test calculation
POST /properties/1/calculate-price
{
  "check_in": "2025-07-18",
  "check_out": "2025-07-21"
}

# 3. View calendar
GET /properties/1/pricing-calendar?start_date=2025-07-01&end_date=2025-07-31
```

### Workflow 2: AI Price Optimization
```bash
# 1. Generate market analysis
GET /properties/1/market-analysis

# 2. Generate AI suggestion
POST /properties/1/price-suggestions
{
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}

# 3. Review suggestion
GET /properties/1/price-suggestions?status=pending

# 4. Accept if confident
POST /properties/1/price-suggestions/5/accept
```

### Workflow 3: Seasonal Strategy
```bash
# 1. Summer season
POST /properties/1/pricing-rules
{
  "type": "seasonal",
  "name": "Summer Peak",
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "adjustment_type": "percentage",
  "adjustment_value": 35,
  "priority": 5
}

# 2. Winter season
POST /properties/1/pricing-rules
{
  "type": "seasonal",
  "name": "Winter Low Season",
  "start_date": "2025-12-01",
  "end_date": "2026-02-28",
  "adjustment_type": "percentage",
  "adjustment_value": -15,
  "priority": 5
}

# 3. Holiday premium
POST /properties/1/pricing-rules
{
  "type": "holiday",
  "name": "Christmas & New Year",
  "start_date": "2025-12-20",
  "end_date": "2026-01-05",
  "adjustment_type": "percentage",
  "adjustment_value": 50,
  "priority": 15
}
```

---

## 🚨 Error Responses

### 401 Unauthorized
```json
{
  "error": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "error": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "error": "Property not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "errors": {
    "adjustment_value": ["The adjustment value field is required."]
  }
}
```

### 400 Bad Request
```json
{
  "success": false,
  "error": "This suggestion has expired"
}
```

---

## 📈 Best Practices

### 1. Rule Priority Strategy
- **Priority 15-20:** Urgent, time-sensitive rules (last-minute, specific events)
- **Priority 10-14:** Weekend and holiday rules
- **Priority 5-9:** Seasonal rules
- **Priority 0-4:** Default base adjustments

### 2. Confidence Thresholds
- **90-100%:** Auto-accept safe
- **80-89%:** Review and likely accept
- **70-79%:** Review carefully
- **<70%:** Requires manual analysis

### 3. Suggestion Expiry
- Suggestions expire after 7 days
- Market conditions change quickly
- Regenerate for fresh analysis

### 4. Testing
- Always test price calculations before going live
- Use pricing calendar to visualize
- Compare with market analysis

---

## 🔄 Status Values

### Pricing Rules
- `is_active: true` - Rule is applied
- `is_active: false` - Rule is disabled

### Price Suggestions
- `pending` - Awaiting decision
- `accepted` - Applied to property
- `rejected` - Declined by owner
- `expired` - No longer valid

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- API documentation: `/docs`
- GitHub Issues: Repository issues page

---

**Last Updated:** 2025-11-02
**Version:** 1.0
**Status:** Production Ready ✅
