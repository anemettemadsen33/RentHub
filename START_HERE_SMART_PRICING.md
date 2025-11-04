# 🎯 Smart Pricing System - Quick Start Guide

## 📚 What is Smart Pricing?

Smart Pricing este un sistem avansat de management și optimizare a prețurilor pentru proprietăți, oferind:

✅ **Dynamic Pricing Rules** - Reguli automate bazate pe sezon, weekend, sărbători, cerere
✅ **AI Price Suggestions** - Recomandări inteligente de prețuri bazate pe date de piață
✅ **Market Analysis** - Analiză competitivă și poziționare pe piață
✅ **Pricing Calendar** - Vizualizare prețuri pe calendar
✅ **Optimization Engine** - Algoritm de optimizare pentru maximizarea veniturilor

---

## 🚀 Quick Start (5 minute setup)

### Step 1: Verify Installation
```bash
cd C:\laragon\www\RentHub\backend

# Check tables exist
php artisan tinker
>>> \App\Models\PricingRule::count()
>>> \App\Models\PriceSuggestion::count()
>>> exit
```

### Step 2: Test API Endpoints
```bash
# Get your auth token first
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","password":"password"}'

# Save the token
$TOKEN = "your_token_here"
```

### Step 3: Create Your First Pricing Rule
```bash
# Weekend pricing (+20%)
curl -X POST http://localhost/api/v1/properties/1/pricing-rules \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "weekend",
    "name": "Weekend Premium",
    "days_of_week": [5, 6],
    "adjustment_type": "percentage",
    "adjustment_value": 20,
    "priority": 10,
    "is_active": true
  }'
```

### Step 4: Generate AI Price Suggestion
```bash
curl -X POST http://localhost/api/v1/properties/1/price-suggestions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-01",
    "end_date": "2025-12-31"
  }'
```

### Step 5: Get Market Analysis
```bash
curl -X GET http://localhost/api/v1/properties/1/market-analysis \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📖 Documentation Files

1. **TASK_3.1_SMART_PRICING_COMPLETE.md** - Complete implementation details
2. **SMART_PRICING_API_GUIDE.md** - API reference & examples
3. **SMART_PRICING_TESTS.md** - Testing scenarios
4. **START_HERE_SMART_PRICING.md** - This file (Quick start)

---

## 🎯 Common Use Cases

### Use Case 1: Setup Weekend Pricing
```json
{
  "type": "weekend",
  "name": "Weekend Premium",
  "days_of_week": [5, 6],  // Friday & Saturday
  "adjustment_type": "percentage",
  "adjustment_value": 20,
  "priority": 10
}
```

### Use Case 2: Summer Season Pricing
```json
{
  "type": "seasonal",
  "name": "Summer Peak",
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "adjustment_type": "percentage",
  "adjustment_value": 35,
  "priority": 5
}
```

### Use Case 3: Last-Minute Discounts
```json
{
  "type": "last_minute",
  "name": "Last Minute Deal",
  "last_minute_days": 7,
  "adjustment_type": "percentage",
  "adjustment_value": -15,
  "priority": 15
}
```

### Use Case 4: Holiday Premium
```json
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

### Use Case 5: Weekly Stay Discount
```json
{
  "type": "weekly",
  "name": "Weekly Discount",
  "min_nights": 7,
  "adjustment_type": "percentage",
  "adjustment_value": -15,
  "priority": 8
}
```

---

## 🔢 How Pricing Works

### Base Calculation
```
1. Start with property base price: 100€
2. Apply rules by priority (highest first)
3. Each rule modifies the price
4. Final price = adjusted price + cleaning fee
```

### Example with Multiple Rules
```
Base price: 100€
├─ Summer season (+35%): 100€ → 135€
├─ Weekend (+20%): 135€ → 162€
└─ Last-minute (-15%): 162€ → 137.70€

Final price: 137.70€ per night
```

### Priority System
```
Priority 20: Last-minute rules (highest)
Priority 15: Holiday specials
Priority 10: Weekend rules
Priority 5: Seasonal rules
Priority 0: Base adjustments (lowest)
```

---

## 🤖 AI Price Suggestions

### How It Works
1. **Market Analysis** - Compares with similar properties
2. **Demand Scoring** - Analyzes season, weekends, bookings
3. **Historical Data** - Uses past performance
4. **Confidence Score** - Calculates reliability (0-100%)
5. **Price Recommendation** - Suggests optimal price

### Confidence Levels
- **90-100%** 🟢 Very confident - Auto-accept safe
- **80-89%** 🟡 Confident - Review and accept
- **70-79%** 🟠 Moderate - Review carefully
- **<70%** 🔴 Low - Manual analysis needed

### Factors Considered
✅ Similar properties in 5km radius
✅ Market average price
✅ Area occupancy rate
✅ Seasonal demand
✅ Weekend vs weekday
✅ Advance booking time
✅ Historical performance
✅ Competitor count

---

## 📊 API Endpoints Overview

### Pricing Rules
```
GET    /properties/{id}/pricing-rules          - List all rules
POST   /properties/{id}/pricing-rules          - Create rule
GET    /properties/{id}/pricing-rules/{ruleId} - Get rule
PUT    /properties/{id}/pricing-rules/{ruleId} - Update rule
DELETE /properties/{id}/pricing-rules/{ruleId} - Delete rule
POST   /properties/{id}/pricing-rules/{ruleId}/toggle - Toggle active
```

### Price Calculation
```
POST /properties/{id}/calculate-price   - Calculate price for dates
GET  /properties/{id}/pricing-calendar  - Get pricing calendar
```

### AI Suggestions
```
GET  /properties/{id}/price-suggestions - List suggestions
POST /properties/{id}/price-suggestions - Generate suggestion
POST /properties/{id}/price-suggestions/{id}/accept - Accept
POST /properties/{id}/price-suggestions/{id}/reject - Reject
```

### Analytics
```
GET  /properties/{id}/market-analysis   - Market data
POST /properties/{id}/pricing-optimize  - 90-day optimization
POST /properties/{id}/price-suggestions/batch-accept - Batch accept
```

---

## 🎨 Admin Panel (Filament)

Access: `http://localhost/admin`

### Manage Pricing Rules
1. Navigate to **Pricing Rules**
2. Filter by type, property, active status
3. Create/Edit/Delete rules
4. View applied rules per property

### View Price Suggestions
1. Navigate to **Price Suggestions**
2. See confidence scores
3. Review market analysis
4. Accept/Reject suggestions
5. View historical decisions

---

## 🧪 Testing

### Quick Test Script
```bash
# 1. Create weekend rule
POST /properties/1/pricing-rules
{ "type": "weekend", ... }

# 2. Calculate price
POST /properties/1/calculate-price
{ "check_in": "2025-07-18", "check_out": "2025-07-21" }

# 3. Check calendar
GET /properties/1/pricing-calendar?start_date=2025-07-01&end_date=2025-07-31

# 4. Generate AI suggestion
POST /properties/1/price-suggestions
{ "start_date": "2025-12-01", "end_date": "2025-12-31" }

# 5. Market analysis
GET /properties/1/market-analysis
```

### Run All Tests
```bash
# See SMART_PRICING_TESTS.md for complete test suite
```

---

## 💡 Best Practices

### 1. Rule Setup Strategy
```
✅ Start with seasonal rules (low priority)
✅ Add weekend rules (medium priority)
✅ Setup holiday premiums (high priority)
✅ Last-minute rules (highest priority)
```

### 2. AI Suggestions
```
✅ Generate suggestions monthly
✅ Review before accepting
✅ Check confidence score
✅ Compare with market analysis
✅ Test on calendar before applying
```

### 3. Price Optimization
```
✅ Monitor competitor prices regularly
✅ Adjust based on occupancy rate
✅ Use AI suggestions as guidance
✅ Don't change prices too frequently
✅ Keep some price stability
```

### 4. Testing
```
✅ Test price calculations before going live
✅ Verify calendar shows correct prices
✅ Check multiple date ranges
✅ Test rule priority combinations
```

---

## 🔧 Troubleshooting

### Issue: Prices not updating
**Solution:** Check if rules are active (`is_active: true`)

### Issue: Wrong price calculated
**Solution:** Check rule priorities - higher priority wins

### Issue: AI suggestions not generating
**Solution:** Ensure property has competitors in area

### Issue: Low confidence scores
**Solution:** Add more properties or historical bookings for better data

### Issue: Authorization errors
**Solution:** Verify user owns the property or is admin

---

## 📈 Next Steps

### Frontend Integration (Next.js)
1. **Pricing Rules UI**
   - Create/Edit form
   - List with filters
   - Calendar visualization
   - Toggle switches

2. **AI Dashboard**
   - Suggestion cards
   - Accept/Reject buttons
   - Market comparison charts
   - Confidence indicators

3. **Calendar Component**
   - Interactive date picker
   - Price per day display
   - Color coding
   - Availability overlay

### Future Enhancements
- [ ] Machine Learning model
- [ ] Event-based pricing
- [ ] Weather-based pricing
- [ ] Email notifications
- [ ] Auto-accept high confidence
- [ ] A/B testing
- [ ] Airbnb/Booking.com sync

---

## 📞 Support & Resources

### Documentation
- Full API docs: `SMART_PRICING_API_GUIDE.md`
- Complete implementation: `TASK_3.1_SMART_PRICING_COMPLETE.md`
- Test suite: `SMART_PRICING_TESTS.md`

### Database Schema
```sql
pricing_rules (id, property_id, type, name, adjustment_value, priority, ...)
price_suggestions (id, property_id, suggested_price, confidence_score, ...)
```

### Key Models
- `App\Models\PricingRule`
- `App\Models\PriceSuggestion`
- `App\Services\PricingService`

### Controllers
- `Api\V1\PricingRuleController`
- `Api\V1\PriceSuggestionController`

---

## ✅ Verification Checklist

- [ ] Migrations ran successfully
- [ ] Can create pricing rules
- [ ] Price calculation works
- [ ] Calendar displays prices
- [ ] AI suggestions generate
- [ ] Market analysis returns data
- [ ] Accept/Reject works
- [ ] Authorization enforced
- [ ] Validation prevents errors
- [ ] Admin panel accessible

---

## 🎉 You're Ready!

Smart Pricing System este complet funcțional și gata de utilizare!

**Need help?** Check the documentation files sau rulează testele din `SMART_PRICING_TESTS.md`

**Next Task:** Frontend implementation (Next.js UI components)

---

**Created:** 2025-11-02
**Version:** 1.0
**Status:** ✅ Production Ready
