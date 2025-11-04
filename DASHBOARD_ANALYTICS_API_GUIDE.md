# 📊 Dashboard Analytics API Guide

Complete API reference for Owner and Tenant Dashboard Analytics.

---

## 🏠 Owner Dashboard Endpoints

### 1. Get Overview Statistics
**Endpoint:** `GET /api/v1/owner/dashboard/overview`

**Query Parameters:**
- `period` (optional, default: 30) - Number of days to analyze

**Response:**
```json
{
  "success": true,
  "data": {
    "total_properties": 15,
    "active_properties": 12,
    "total_bookings": 145,
    "active_bookings": 8,
    "total_revenue": 125000.00,
    "period_revenue": 12500.00,
    "average_rating": 4.7,
    "total_reviews": 98
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/overview?period=30" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 2. Get Booking Statistics
**Endpoint:** `GET /api/v1/owner/dashboard/booking-statistics`

**Query Parameters:**
- `period` (optional, default: 30) - Number of days
- `group_by` (optional, default: 'day') - Grouping: 'day', 'week', 'month'

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "period": "2025-01-01",
      "total_bookings": 5,
      "confirmed": 4,
      "cancelled": 1,
      "completed": 0
    },
    {
      "period": "2025-01-02",
      "total_bookings": 3,
      "confirmed": 2,
      "cancelled": 0,
      "completed": 1
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/booking-statistics?period=30&group_by=day" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 3. Get Revenue Reports
**Endpoint:** `GET /api/v1/owner/dashboard/revenue-reports`

**Query Parameters:**
- `period` (optional, default: 30) - Number of days
- `group_by` (optional, default: 'day') - Grouping: 'day', 'week', 'month'

**Response:**
```json
{
  "success": true,
  "data": {
    "timeline": [
      {
        "period": "2025-01",
        "total_revenue": 15000.00,
        "total_transactions": 12,
        "average_transaction": 1250.00
      }
    ],
    "by_property": [
      {
        "id": 1,
        "title": "Luxury Villa in Bucharest",
        "total_revenue": 8000.00,
        "total_bookings": 8
      },
      {
        "id": 2,
        "title": "Cozy Apartment Downtown",
        "total_revenue": 7000.00,
        "total_bookings": 14
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/revenue-reports?period=90&group_by=month" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 4. Get Occupancy Rate
**Endpoint:** `GET /api/v1/owner/dashboard/occupancy-rate`

**Query Parameters:**
- `property_id` (optional) - Filter by specific property
- `start_date` (optional, default: start of current month) - Format: YYYY-MM-DD
- `end_date` (optional, default: end of current month) - Format: YYYY-MM-DD

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "property_id": 1,
      "property_title": "Luxury Villa",
      "total_days": 31,
      "booked_days": 25,
      "available_days": 6,
      "occupancy_rate": 80.65
    },
    {
      "property_id": 2,
      "property_title": "Cozy Apartment",
      "total_days": 31,
      "booked_days": 18,
      "available_days": 13,
      "occupancy_rate": 58.06
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/occupancy-rate?start_date=2025-01-01&end_date=2025-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Get Property Performance
**Endpoint:** `GET /api/v1/owner/dashboard/property-performance`

**Query Parameters:**
- `period` (optional, default: 90) - Number of days

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "property_id": 1,
      "title": "Luxury Villa",
      "total_bookings": 25,
      "confirmed_bookings": 20,
      "total_revenue": 32000.00,
      "average_rating": 4.8,
      "total_reviews": 18,
      "views": 1250,
      "conversion_rate": 2.0
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/property-performance?period=90" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 6. Get Guest Demographics
**Endpoint:** `GET /api/v1/owner/dashboard/guest-demographics`

**Query Parameters:**
- `period` (optional, default: 90) - Number of days

**Response:**
```json
{
  "success": true,
  "data": {
    "total_unique_guests": 85,
    "repeat_guests": 15,
    "by_location": {
      "Romania": 45,
      "Germany": 20,
      "France": 10,
      "UK": 7,
      "Unknown": 3
    },
    "average_booking_value": 1285.50
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/owner/dashboard/guest-demographics?period=90" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 👤 Tenant Dashboard Endpoints

### 1. Get Overview Statistics
**Endpoint:** `GET /api/v1/tenant/dashboard/overview`

**Query Parameters:**
- `period` (optional, default: 30) - Number of days

**Response:**
```json
{
  "success": true,
  "data": {
    "total_bookings": 25,
    "active_bookings": 1,
    "upcoming_bookings": 2,
    "completed_bookings": 22,
    "total_spent": 35000.00,
    "period_spent": 4500.00,
    "saved_properties": 12,
    "reviews_given": 18
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/overview?period=30" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 2. Get Booking History
**Endpoint:** `GET /api/v1/tenant/dashboard/booking-history`

**Query Parameters:**
- `status` (optional) - Filter: 'pending', 'confirmed', 'cancelled', 'completed'
- `page` (optional, default: 1)
- `per_page` (optional, default: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property": {
        "id": 5,
        "title": "Beach House",
        "city": "Constanta"
      },
      "check_in": "2025-01-15",
      "check_out": "2025-01-20",
      "status": "confirmed",
      "total_price": 1500.00,
      "payments": [
        {
          "id": 1,
          "amount": 1500.00,
          "status": "completed"
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/booking-history?status=confirmed&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 3. Get Spending Reports
**Endpoint:** `GET /api/v1/tenant/dashboard/spending-reports`

**Query Parameters:**
- `period` (optional, default: 365) - Number of days
- `group_by` (optional, default: 'month') - Grouping: 'day', 'week', 'month', 'year'

**Response:**
```json
{
  "success": true,
  "data": {
    "timeline": [
      {
        "period": "2025-01",
        "total_spent": 4500.00,
        "total_bookings": 3,
        "average_booking_cost": 1500.00
      }
    ],
    "by_property": [
      {
        "id": 5,
        "title": "Beach House",
        "total_spent": 6000.00,
        "total_bookings": 4
      }
    ],
    "total_spent": 35000.00,
    "average_booking_cost": 1400.00
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/spending-reports?period=365&group_by=month" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 4. Get Saved Properties
**Endpoint:** `GET /api/v1/tenant/dashboard/saved-properties`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Summer Vacation",
      "description": "Properties for summer 2025",
      "properties_count": 5,
      "properties": [
        {
          "id": 10,
          "title": "Beach Villa",
          "price": 200.00,
          "location": "Constanta, Romania",
          "added_at": "2025-01-15T10:30:00"
        }
      ]
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/saved-properties" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Get Review History
**Endpoint:** `GET /api/v1/tenant/dashboard/review-history`

**Query Parameters:**
- `page` (optional, default: 1)
- `per_page` (optional, default: 10)

**Response:**
```json
{
  "success": true,
  "data": {
    "reviews": [
      {
        "id": 1,
        "property": {
          "id": 5,
          "title": "Beach House"
        },
        "overall_rating": 5,
        "comment": "Amazing stay!",
        "created_at": "2025-01-20T14:30:00",
        "response": {
          "comment": "Thank you for your review!",
          "created_at": "2025-01-21T09:00:00"
        }
      }
    ],
    "stats": {
      "total_reviews": 18,
      "average_rating_given": 4.6,
      "reviews_with_response": 15
    }
  },
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 18
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/review-history?page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 6. Get Upcoming Trips
**Endpoint:** `GET /api/v1/tenant/dashboard/upcoming-trips`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 25,
      "property": {
        "id": 8,
        "title": "Mountain Cabin",
        "city": "Brasov",
        "address": "Strada Principala 10"
      },
      "check_in": "2025-02-15",
      "check_out": "2025-02-20",
      "guests": 4,
      "status": "confirmed",
      "total_price": 2000.00,
      "payments": [
        {
          "status": "completed",
          "amount": 2000.00
        }
      ]
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/upcoming-trips" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 7. Get Travel Statistics
**Endpoint:** `GET /api/v1/tenant/dashboard/travel-statistics`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_nights": 125,
    "cities_visited": 8,
    "countries_visited": 3,
    "favorite_city": "Bucharest",
    "total_trips": 22
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost/api/v1/tenant/dashboard/travel-statistics" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔐 Authentication

All endpoints require authentication using Laravel Sanctum:

```javascript
// JavaScript/TypeScript
const response = await fetch('http://localhost/api/v1/owner/dashboard/overview', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
```

---

## 🎨 Frontend Integration Examples

### React/Next.js Hook Example:

```typescript
// hooks/useOwnerDashboard.ts
import useSWR from 'swr'

const fetcher = (url: string) => 
  fetch(url, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json())

export function useOwnerOverview(period: number = 30) {
  const { data, error, isLoading } = useSWR(
    `/api/v1/owner/dashboard/overview?period=${period}`,
    fetcher
  )
  
  return {
    stats: data?.data,
    isLoading,
    error
  }
}

export function useRevenueReports(period: number = 90, groupBy: string = 'month') {
  const { data, error, isLoading } = useSWR(
    `/api/v1/owner/dashboard/revenue-reports?period=${period}&group_by=${groupBy}`,
    fetcher
  )
  
  return {
    reports: data?.data,
    isLoading,
    error
  }
}
```

### Vue.js Composable Example:

```typescript
// composables/useOwnerDashboard.ts
import { ref, watchEffect } from 'vue'

export function useOwnerOverview(period: number = 30) {
  const stats = ref(null)
  const loading = ref(true)
  const error = ref(null)

  watchEffect(async () => {
    try {
      const response = await fetch(
        `/api/v1/owner/dashboard/overview?period=${period}`,
        {
          headers: { 'Authorization': `Bearer ${token}` }
        }
      )
      const data = await response.json()
      stats.value = data.data
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  })

  return { stats, loading, error }
}
```

---

## 📊 Chart Library Recommendations

### For Revenue & Booking Charts:
- **Chart.js** - Simple and effective
- **Recharts** (React) - Native React charts
- **ApexCharts** - Beautiful, interactive charts
- **D3.js** - Maximum customization

### Example with Recharts:

```tsx
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip } from 'recharts'

function RevenueChart({ data }) {
  return (
    <LineChart width={600} height={300} data={data.timeline}>
      <CartesianGrid strokeDasharray="3 3" />
      <XAxis dataKey="period" />
      <YAxis />
      <Tooltip />
      <Line type="monotone" dataKey="total_revenue" stroke="#8884d8" />
    </LineChart>
  )
}
```

---

## 🎯 Performance Tips

1. **Use SWR or React Query** for automatic caching and revalidation
2. **Implement pagination** for large datasets
3. **Debounce period changes** to reduce API calls
4. **Cache dashboard data** for 5-10 minutes
5. **Use skeleton loaders** while data loads
6. **Implement error boundaries** for better UX

---

## 🐛 Error Handling

All endpoints return consistent error format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `401` - Unauthorized (token invalid/missing)
- `403` - Forbidden (insufficient permissions)
- `422` - Validation error
- `500` - Server error

---

## 📱 Mobile Optimization

All endpoints return data optimized for both desktop and mobile:
- Minimal payload size
- Pagination support
- Flexible grouping options
- Optional fields support

---

## 🚀 Next Steps

1. Implement frontend dashboards in Next.js
2. Add chart visualizations
3. Add export functionality (CSV, PDF)
4. Add custom date range pickers
5. Add real-time updates with WebSocket
6. Add email report scheduling

---

**Happy Coding! 🎉**
