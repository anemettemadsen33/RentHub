# 🤖 AI & Machine Learning - Quick Start Guide

**Status:** ✅ Production Ready  
**Version:** 1.0.0  
**Last Updated:** November 3, 2025

---

## 🚀 Quick Start (5 Minutes)

### 1. Access Admin Panel

```
URL: http://localhost/admin
Login with admin credentials
Navigate to: AI & Security → Fraud Alerts
```

### 2. Test Recommendations API

```bash
# Get personalized recommendations
curl -X GET http://localhost/api/v1/ai/recommendations \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get similar properties
curl -X GET http://localhost/api/v1/ai/properties/1/similar \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Price Optimization (Owner)

```bash
# Get price optimization
curl -X GET http://localhost/api/v1/ai/price/1/optimization \
  -H "Authorization: Bearer OWNER_TOKEN"

# Get price predictions
curl -X GET "http://localhost/api/v1/ai/price/1/predictions?start_date=2025-12-01&end_date=2025-12-31" \
  -H "Authorization: Bearer OWNER_TOKEN"
```

### 4. Test Fraud Detection (Admin)

```bash
# Check user for fraud
curl -X POST http://localhost/api/v1/ai/fraud/check/user/1 \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Get fraud statistics
curl -X GET http://localhost/api/v1/ai/fraud/stats \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Run fraud scan
curl -X POST http://localhost/api/v1/ai/fraud/scan \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -d '{"scan_type":"all"}'
```

---

## 📋 Key Features

### 🎯 Smart Recommendations
- **Personalized suggestions** based on user behavior
- **Similar properties** using content-based filtering
- **Performance tracking** (CTR, conversions)
- **Real-time learning** from user interactions

### 💰 Price Optimization
- **Dynamic pricing** with ML predictions
- **Revenue maximization** algorithms
- **Competitor analysis** automation
- **Seasonal insights** and trends
- **One-click price application**

### 🛡️ Fraud Detection
- **Automated screening** for users, properties, bookings, payments
- **Real-time scoring** (0-100 fraud risk)
- **Alert management** with resolution workflow
- **Bot detection** and prevention
- **Bulk scanning** capabilities

---

## 🎨 Admin Panel Quick Guide

### Fraud Alerts Dashboard

**Location:** `http://localhost/admin/fraud-alerts`

**Features:**
1. **View Alerts** - Filter by severity, status, type
2. **Sort** - By fraud score, date, status
3. **Quick Actions:**
   - ✅ Resolve Alert
   - ⚠️ Mark False Positive
   - 👁️ View Details
   - 🗑️ Delete

**Severity Levels:**
- 🔴 **Critical** (85-100) - Immediate action required
- 🟠 **High** (70-84) - Review within 24h
- 🟡 **Medium** (50-69) - Review within week
- ⚪ **Low** (0-49) - Monitor

---

## 📊 API Endpoints Overview

### Recommendations (All Users)
```
GET    /api/v1/ai/recommendations                 - Get personalized recommendations
GET    /api/v1/ai/recommendations/{id}/track      - Track interaction
GET    /api/v1/ai/properties/{id}/similar         - Get similar properties
GET    /api/v1/ai/recommendations/stats           - Get stats (admin)
```

### Price Optimization (Owners & Admins)
```
GET    /api/v1/ai/price/{propertyId}/prediction        - Single date prediction
GET    /api/v1/ai/price/{propertyId}/predictions       - Date range predictions
GET    /api/v1/ai/price/{propertyId}/optimization      - Get optimization suggestions
POST   /api/v1/ai/price/{propertyId}/apply             - Apply price suggestion
GET    /api/v1/ai/price/{propertyId}/revenue-report    - Revenue optimization report
```

### ML Model (Admins Only)
```
GET    /api/v1/ai/model/metrics                   - Get model performance
POST   /api/v1/ai/model/train                     - Update ML model
```

### Fraud Detection (Admins Only)
```
GET    /api/v1/ai/fraud/alerts                    - List fraud alerts
GET    /api/v1/ai/fraud/alerts/{id}               - Alert details
POST   /api/v1/ai/fraud/check/user/{userId}       - Check user
POST   /api/v1/ai/fraud/check/property/{id}       - Check property
POST   /api/v1/ai/fraud/check/booking/{id}        - Check booking
POST   /api/v1/ai/fraud/check/payment/{id}        - Check payment
POST   /api/v1/ai/fraud/alerts/{id}/resolve       - Resolve alert
POST   /api/v1/ai/fraud/alerts/{id}/false-positive - Mark false positive
GET    /api/v1/ai/fraud/stats                     - Get statistics
POST   /api/v1/ai/fraud/scan                      - Run fraud scan
```

---

## 🧪 Testing Scenarios

### Scenario 1: New User Gets Recommendations

```bash
# 1. Login as new user
TOKEN=$(curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"newuser@example.com","password":"password"}' \
  | jq -r '.token')

# 2. View some properties (creates behavior data)
curl -X GET http://localhost/api/v1/properties \
  -H "Authorization: Bearer $TOKEN"

# 3. Get personalized recommendations
curl -X GET http://localhost/api/v1/ai/recommendations \
  -H "Authorization: Bearer $TOKEN" | jq

# Expected: 10 recommended properties based on views
```

### Scenario 2: Owner Optimizes Pricing

```bash
# 1. Login as property owner
OWNER_TOKEN=$(curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Get price optimization
curl -X GET http://localhost/api/v1/ai/price/1/optimization \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq

# 3. Apply suggested price
curl -X POST http://localhost/api/v1/ai/price/1/apply \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"apply_type":"immediate"}' | jq

# Expected: Price updated, revenue forecast provided
```

### Scenario 3: Admin Detects Fraud

```bash
# 1. Login as admin
ADMIN_TOKEN=$(curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Run fraud detection scan
curl -X POST http://localhost/api/v1/ai/fraud/scan \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"scan_type":"all"}' | jq

# 3. View pending alerts
curl -X GET "http://localhost/api/v1/ai/fraud/alerts?status=pending" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq

# 4. Resolve an alert
curl -X POST http://localhost/api/v1/ai/fraud/alerts/1/resolve \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "resolution_notes":"Verified and resolved",
    "action_type":"no_action"
  }' | jq

# Expected: Alert resolved, stats updated
```

---

## 🔧 Configuration

### Recommendation Settings

Edit in config or database:
- `recommendation_validity_days` - How long recommendations are valid (default: 7)
- `min_recommendation_score` - Minimum score to show (default: 60)
- `max_recommendations` - Max to generate per user (default: 50)

### Price Optimization Settings

- `prediction_confidence_threshold` - Min confidence (default: 50%)
- `max_price_change_percent` - Max price change allowed (default: 30%)
- `seasonal_factors` - Monthly demand multipliers
- `competitor_weight` - Weight of competitor prices (default: 0.3)

### Fraud Detection Settings

- `fraud_score_threshold` - Score to trigger alert (default: 70)
- `auto_action_threshold` - Score for automatic actions (default: 90)
- `scan_frequency` - How often to run scans (default: daily)
- `alert_retention_days` - How long to keep alerts (default: 90)

---

## 📈 Monitoring

### Key Metrics to Track

**Recommendations:**
- Click-Through Rate (CTR) - Target: 25-35%
- Conversion Rate - Target: 3-8%
- Average Score - Target: 75+

**Price Optimization:**
- Prediction Accuracy - Target: 85%+
- Revenue Increase - Target: 15-25%
- Occupancy Improvement - Target: 10-20%

**Fraud Detection:**
- Detection Rate - Target: 80%+
- False Positive Rate - Target: <10%
- Resolution Time - Target: <24h

### View Metrics

```bash
# Recommendation stats
curl -X GET http://localhost/api/v1/ai/recommendations/stats \
  -H "Authorization: Bearer ADMIN_TOKEN"

# ML model metrics
curl -X GET http://localhost/api/v1/ai/model/metrics \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Fraud stats
curl -X GET http://localhost/api/v1/ai/fraud/stats \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

---

## 🐛 Troubleshooting

### Issue: No Recommendations Generated

**Solution:**
1. Check if user has any activity (views, searches, bookings)
2. Ensure properties exist with status='active'
3. Check `property_recommendations` table
4. Verify `valid_until` is in future

```bash
# Check user behavior
php artisan tinker
>>> App\Models\UserBehavior::where('user_id', 1)->count();

# Generate recommendations manually
>>> $controller = new App\Http\Controllers\Api\AiRecommendationController();
>>> $controller->generateRecommendationsForUser(1);
```

### Issue: Price Predictions Not Accurate

**Solution:**
1. Ensure enough historical booking data
2. Update model with actual prices
3. Retrain model

```bash
# Update actual prices
php artisan tinker
>>> $predictions = App\Models\PricePrediction::whereNull('actual_price')->get();
>>> foreach($predictions as $p) {
...   $booking = App\Models\Booking::where('property_id', $p->property_id)
...     ->whereDate('check_in', $p->date)->first();
...   if($booking) $p->update(['actual_price' => $booking->total_price / $booking->nights]);
... }

# Retrain model
curl -X POST http://localhost/api/v1/ai/model/train \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### Issue: Too Many Fraud Alerts

**Solution:**
1. Review false positive rate
2. Adjust thresholds
3. Mark legitimate patterns as false positives

```bash
# Check stats
curl -X GET http://localhost/api/v1/ai/fraud/stats \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Mark pattern as false positive
curl -X POST http://localhost/api/v1/ai/fraud/alerts/{id}/false-positive \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"notes":"Legitimate business pattern"}'
```

---

## 📚 Additional Resources

- **Full Documentation:** [TASK_4.2_AI_ML_COMPLETE.md](TASK_4.2_AI_ML_COMPLETE.md)
- **API Reference:** [API_ENDPOINTS.md](API_ENDPOINTS.md)
- **Project Status:** [PROJECT_STATUS_2025_11_03.md](PROJECT_STATUS_2025_11_03.md)

---

## 💡 Tips & Best Practices

### For Recommendations
1. ✅ Track user behavior consistently
2. ✅ Update recommendations regularly (weekly)
3. ✅ A/B test different algorithms
4. ✅ Monitor CTR and conversions
5. ✅ Collect user feedback

### For Price Optimization
1. ✅ Start with suggestions, don't auto-apply
2. ✅ Review competitor data weekly
3. ✅ Adjust seasonal factors quarterly
4. ✅ Monitor occupancy vs revenue balance
5. ✅ Update actual prices for model learning

### For Fraud Detection
1. ✅ Review alerts daily
2. ✅ Mark false positives to improve accuracy
3. ✅ Run scans during off-peak hours
4. ✅ Document resolution patterns
5. ✅ Keep thresholds updated based on trends

---

## 🎯 Next Steps

1. **Integrate Frontend:**
   - Add recommendation widgets
   - Create price optimization dashboard for owners
   - Add fraud alert notifications for admins

2. **Improve Algorithms:**
   - Collect more training data
   - Fine-tune scoring weights
   - Add more features to ML models

3. **Monitor & Optimize:**
   - Track key metrics daily
   - A/B test improvements
   - Gather user feedback

---

**Need Help?**
- Check [TASK_4.2_AI_ML_COMPLETE.md](TASK_4.2_AI_ML_COMPLETE.md) for detailed documentation
- Review code in `app/Http/Controllers/Api/` directory
- Test with Postman using examples above

---

*Happy AI/ML Implementation! 🤖*

*Last Updated: November 3, 2025*
