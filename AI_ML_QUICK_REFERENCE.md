# 🤖 AI & ML Quick Reference Card

**Task 4.2 - COMPLETE** ✅

---

## 📱 Quick Access

### Admin Panel
```
URL: http://localhost/admin
Navigate to: Security → Fraud Alerts
```

### API Base
```
Base URL: http://localhost/api/v1/ai/
Auth: Bearer token required
```

---

## 🔑 Key Endpoints

### Recommendations (Users)
```bash
# Get recommendations
GET /recommendations

# Track click
GET /recommendations/{id}/track?action=clicked

# Similar properties
GET /properties/{id}/similar
```

### Price Optimization (Owners)
```bash
# Get optimization
GET /price/{propertyId}/optimization

# Apply price
POST /price/{propertyId}/apply
```

### Fraud Detection (Admins)
```bash
# Get alerts
GET /fraud/alerts?status=pending

# Check user
POST /fraud/check/user/{userId}

# Run scan
POST /fraud/scan
```

---

## 📊 Quick Stats Commands

```bash
# Recommendation stats
curl http://localhost/api/v1/ai/recommendations/stats \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Fraud stats
curl http://localhost/api/v1/ai/fraud/stats \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Model metrics
curl http://localhost/api/v1/ai/model/metrics \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

---

## 🎯 Fraud Score Thresholds

| Score | Risk Level | Action |
|-------|-----------|---------|
| 0-49 | 🟢 Low | Monitor |
| 50-69 | 🟡 Medium | Review weekly |
| 70-84 | 🟠 High | Review in 24h |
| 85-100 | 🔴 Critical | Immediate action |

---

## 📈 Performance Targets

| Metric | Target |
|--------|---------|
| Recommendation CTR | 25-35% |
| Conversion Rate | 3-8% |
| Price Accuracy | 85-90% |
| Fraud Detection | 80-85% |
| Response Time | <500ms |

---

## 🛠️ Troubleshooting

### No recommendations?
```bash
# Check user behavior
php artisan tinker
>>> App\Models\UserBehavior::where('user_id', 1)->count();

# Generate manually
>>> $controller = new App\Http\Controllers\Api\AiRecommendationController();
>>> $controller->generateRecommendationsForUser(1);
```

### Price predictions off?
```bash
# Retrain model
curl -X POST http://localhost/api/v1/ai/model/train \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

### Too many alerts?
```bash
# Check false positive rate
curl http://localhost/api/v1/ai/fraud/stats \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  | jq '.stats.false_positives'
```

---

## 📚 Documentation

- 📖 Full Docs: `TASK_4.2_AI_ML_COMPLETE.md`
- 🚀 Quick Start: `START_HERE_AI_ML.md`
- 📋 Summary: `AI_ML_IMPLEMENTATION_SUMMARY.md`

---

## ✅ Implementation Checklist

- [x] Smart Recommendations (620 lines)
- [x] Price Optimization (920 lines)
- [x] Fraud Detection (850 lines)
- [x] 23 API endpoints
- [x] Admin panel integration
- [x] Full documentation
- [ ] Test all endpoints
- [ ] Clear caches
- [ ] Deploy to production

---

*Version: 1.0.0 | Updated: Nov 3, 2025*
