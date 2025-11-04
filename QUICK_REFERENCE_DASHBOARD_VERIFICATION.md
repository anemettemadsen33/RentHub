# 🚀 Quick Reference - Dashboard & Verification

**One-page cheat sheet for Tasks 2.5 & 2.6**

---

## 📌 Quick Links

- **Admin Panel:** http://localhost:8000/admin
- **API Base:** http://localhost:8000/api/v1
- **Documentation:** [START_HERE_DASHBOARD_ANALYTICS.md](./START_HERE_DASHBOARD_ANALYTICS.md)

---

## 🔐 Task 2.5: Verification System

### User Verification Score
```
ID Verification (30) + Phone (20) + Email (20) + Address (20) + Background (10) = 100 pts
```

**Status:**
- 0 pts = unverified
- 1-69 pts = partially_verified
- 70+ pts = fully_verified ✅

### Property Verification Score
```
Ownership (30) + Inspection (25) + Photos (15) + Details (15) + Legal (15) = 100 pts
```

**Status:**
- 0-49 pts = unverified
- 50-79 pts = under_review
- 80+ pts = verified ⭐

---

## 📊 Task 2.6: Dashboard Analytics

### Owner Dashboard Endpoints (6)
```bash
GET /api/v1/owner/dashboard/overview                 # Key stats
GET /api/v1/owner/dashboard/booking-statistics       # Bookings over time
GET /api/v1/owner/dashboard/revenue-reports          # Revenue analysis
GET /api/v1/owner/dashboard/occupancy-rate           # Occupancy %
GET /api/v1/owner/dashboard/property-performance     # Per-property stats
GET /api/v1/owner/dashboard/guest-demographics       # Guest data
```

### Tenant Dashboard Endpoints (7)
```bash
GET /api/v1/tenant/dashboard/overview                # Key stats
GET /api/v1/tenant/dashboard/booking-history         # Past bookings
GET /api/v1/tenant/dashboard/spending-reports        # Spending analysis
GET /api/v1/tenant/dashboard/saved-properties        # Wishlists
GET /api/v1/tenant/dashboard/review-history          # Reviews given
GET /api/v1/tenant/dashboard/upcoming-trips          # Future bookings
GET /api/v1/tenant/dashboard/travel-statistics       # Travel stats
```

---

## 🧪 Quick Test Commands

### Login
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@example.com","password":"password"}'
```

### Test Owner Dashboard
```bash
TOKEN="your_token_here"

# Overview
curl -X GET "http://localhost:8000/api/v1/owner/dashboard/overview?period=30" \
  -H "Authorization: Bearer $TOKEN"

# Revenue
curl -X GET "http://localhost:8000/api/v1/owner/dashboard/revenue-reports?period=90" \
  -H "Authorization: Bearer $TOKEN"
```

### Test Tenant Dashboard
```bash
# Overview
curl -X GET "http://localhost:8000/api/v1/tenant/dashboard/overview?period=30" \
  -H "Authorization: Bearer $TOKEN"

# Upcoming Trips
curl -X GET "http://localhost:8000/api/v1/tenant/dashboard/upcoming-trips" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🎨 Frontend Integration (Next.js)

### Hook Example
```typescript
// hooks/useDashboard.ts
import useSWR from 'swr'

export function useOwnerDashboard(period = 30) {
  const { data, error, isLoading } = useSWR(
    `/api/v1/owner/dashboard/overview?period=${period}`,
    fetcher
  )
  return { stats: data?.data, isLoading, error }
}
```

### Component Example
```typescript
// pages/owner/dashboard.tsx
function OwnerDashboard() {
  const { stats, isLoading } = useOwnerDashboard(30)
  
  if (isLoading) return <Skeleton />
  
  return (
    <div>
      <StatCard title="Total Revenue" value={stats.total_revenue} />
      <StatCard title="Active Bookings" value={stats.active_bookings} />
    </div>
  )
}
```

---

## 📋 Query Parameters

### Common Parameters
```
period      = 7 | 30 | 90 | 365 (days)
group_by    = day | week | month
page        = 1, 2, 3... (pagination)
per_page    = 10 (results per page)
status      = confirmed | cancelled | completed
start_date  = YYYY-MM-DD
end_date    = YYYY-MM-DD
property_id = numeric ID
```

### Examples
```bash
?period=30                                    # Last 30 days
?period=90&group_by=month                     # Last 90 days, monthly
?start_date=2025-01-01&end_date=2025-01-31   # Custom range
?status=confirmed&page=2&per_page=20          # Filtered, paginated
```

---

## 📊 Response Formats

### Overview Response
```json
{
  "success": true,
  "data": {
    "total_properties": 12,
    "active_bookings": 8,
    "total_revenue": 125000.00,
    "average_rating": 4.7
  }
}
```

### Timeline Response
```json
{
  "success": true,
  "data": [
    {
      "period": "2025-01",
      "total_revenue": 15000.00,
      "total_bookings": 12
    }
  ]
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  }
}
```

---

## 🗂️ File Locations

### Controllers
```
backend/app/Http/Controllers/Api/V1/
├── OwnerDashboardController.php
└── TenantDashboardController.php
```

### Models
```
backend/app/Models/
├── UserVerification.php
├── PropertyVerification.php
└── VerificationDocument.php
```

### Routes
```
backend/routes/api.php
└── Lines 275-297 (Dashboard routes)
```

### Documentation
```
/TASK_2.5_2.6_COMPLETE.md
/DASHBOARD_ANALYTICS_API_GUIDE.md
/START_HERE_DASHBOARD_ANALYTICS.md
/SESSION_SUMMARY_DASHBOARD_VERIFICATION.md
/PROJECT_ROADMAP_2025.md
/README_TASKS_2.5_2.6.md
/VISUAL_SUMMARY_TASKS_2.5_2.6.md
```

---

## 🎯 Key Features

### Verification System
- ✅ Automatic scoring (0-100)
- ✅ Multiple verification types
- ✅ Badge awarding
- ✅ Admin approval workflows
- ✅ Document management

### Dashboard Analytics
- ✅ Real-time statistics
- ✅ Flexible time periods
- ✅ Multiple groupings
- ✅ Per-property analysis
- ✅ Pagination support

---

## 🔒 Security Checklist

- ✅ Authentication required (Sanctum)
- ✅ User-scoped queries
- ✅ Admin-only actions
- ✅ Input validation
- ✅ No sensitive data exposed

---

## 🎨 UI Components to Build

### Owner Dashboard
```
□ Statistics Cards (4)
□ Revenue Line Chart
□ Booking Bar Chart
□ Occupancy Gauge
□ Property Performance Table
□ Demographics Pie Chart
□ Period Selector
□ Date Range Picker
```

### Tenant Dashboard
```
□ Statistics Cards (4)
□ Upcoming Trips Cards
□ Booking History Table
□ Spending Line Chart
□ Travel Statistics
□ Review History List
```

---

## 📈 Performance Tips

### Caching
```typescript
// Cache for 5 minutes
const { data } = useSWR(url, fetcher, {
  refreshInterval: 300000
})
```

### Debouncing
```typescript
// Debounce period changes
const debouncedPeriod = useDebounce(period, 500)
```

### Skeleton Loading
```typescript
{isLoading ? <Skeleton /> : <DashboardView />}
```

---

## 🐛 Common Issues

### Issue: 401 Error
**Fix:** Check token in Authorization header

### Issue: Empty Data
**Fix:** Run seeders, create test data

### Issue: CORS Error
**Fix:** Update config/cors.php with frontend URL

### Issue: Slow Queries
**Fix:** Check database indexes, use eager loading

---

## 📚 Recommended Stack

### Frontend
- Next.js 14
- TypeScript
- SWR / React Query
- Recharts / ApexCharts
- Tailwind CSS
- Shadcn/ui

### Backend (Already Done!)
- Laravel 11 ✅
- Filament v4 ✅
- MySQL ✅
- Sanctum ✅

---

## 🎉 Status Summary

```
Backend:  ████████████████████ 100% ✅
Frontend: ░░░░░░░░░░░░░░░░░░░░   0% 🎯
Docs:     ████████████████████ 100% ✅
```

**Next Step:** Build Owner Dashboard UI! 🚀

---

## 📞 Quick Help

- Documentation: [README_TASKS_2.5_2.6.md](./README_TASKS_2.5_2.6.md)
- API Guide: [DASHBOARD_ANALYTICS_API_GUIDE.md](./DASHBOARD_ANALYTICS_API_GUIDE.md)
- Quick Start: [START_HERE_DASHBOARD_ANALYTICS.md](./START_HERE_DASHBOARD_ANALYTICS.md)
- Visual Guide: [VISUAL_SUMMARY_TASKS_2.5_2.6.md](./VISUAL_SUMMARY_TASKS_2.5_2.6.md)

---

**🎊 Everything is ready for frontend development! Let's build! 🎊**
