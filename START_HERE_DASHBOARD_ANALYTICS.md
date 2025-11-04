# 🚀 Quick Start: Dashboard Analytics

Get started with RentHub Dashboard Analytics in 5 minutes!

---

## 📋 What's Included?

✅ **Owner Dashboard** - 6 analytical endpoints for property owners
✅ **Tenant Dashboard** - 7 analytical endpoints for tenants
✅ **Real-time Statistics** - Bookings, revenue, occupancy, performance
✅ **Flexible Time Periods** - Daily, weekly, monthly grouping
✅ **Comprehensive Reports** - Revenue, spending, demographics

---

## 🎯 Quick Test

### 1. Start Your Server
```bash
cd backend
php artisan serve
```

### 2. Get Your Auth Token
```bash
# Login to get token
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@example.com",
    "password": "password"
  }'
```

### 3. Test Owner Dashboard
```bash
# Get overview
curl -X GET "http://localhost:8000/api/v1/owner/dashboard/overview?period=30" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get revenue reports
curl -X GET "http://localhost:8000/api/v1/owner/dashboard/revenue-reports?period=90" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Test Tenant Dashboard
```bash
# Get overview
curl -X GET "http://localhost:8000/api/v1/tenant/dashboard/overview?period=30" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get booking history
curl -X GET "http://localhost:8000/api/v1/tenant/dashboard/booking-history" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Frontend Integration

### Next.js Example:

```typescript
// lib/api/dashboard.ts
export async function getOwnerOverview(token: string, period: number = 30) {
  const response = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/api/v1/owner/dashboard/overview?period=${period}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    }
  )
  
  return response.json()
}

// pages/owner/dashboard.tsx
import { useEffect, useState } from 'react'
import { getOwnerOverview } from '@/lib/api/dashboard'

export default function OwnerDashboard() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadData() {
      const token = localStorage.getItem('token')
      const data = await getOwnerOverview(token, 30)
      setStats(data.data)
      setLoading(false)
    }
    loadData()
  }, [])

  if (loading) return <div>Loading...</div>

  return (
    <div className="dashboard">
      <h1>Owner Dashboard</h1>
      <div className="stats-grid">
        <StatCard title="Total Properties" value={stats.total_properties} />
        <StatCard title="Active Bookings" value={stats.active_bookings} />
        <StatCard title="Total Revenue" value={`$${stats.total_revenue}`} />
        <StatCard title="Average Rating" value={stats.average_rating} />
      </div>
    </div>
  )
}
```

### With SWR (Recommended):

```typescript
// hooks/useDashboard.ts
import useSWR from 'swr'

const fetcher = (url: string) => 
  fetch(url, {
    headers: {
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    }
  }).then(r => r.json())

export function useOwnerDashboard(period: number = 30) {
  const { data, error, isLoading } = useSWR(
    `/api/v1/owner/dashboard/overview?period=${period}`,
    fetcher,
    {
      refreshInterval: 300000, // Refresh every 5 minutes
      revalidateOnFocus: false
    }
  )
  
  return {
    stats: data?.data,
    isLoading,
    error
  }
}

// Usage in component
function Dashboard() {
  const { stats, isLoading, error } = useOwnerDashboard(30)
  
  if (isLoading) return <Skeleton />
  if (error) return <Error />
  
  return <DashboardView stats={stats} />
}
```

---

## 📊 Available Endpoints

### Owner Endpoints:
1. `/api/v1/owner/dashboard/overview` - Key metrics
2. `/api/v1/owner/dashboard/booking-statistics` - Booking trends
3. `/api/v1/owner/dashboard/revenue-reports` - Revenue analysis
4. `/api/v1/owner/dashboard/occupancy-rate` - Property occupancy
5. `/api/v1/owner/dashboard/property-performance` - Individual property stats
6. `/api/v1/owner/dashboard/guest-demographics` - Guest insights

### Tenant Endpoints:
1. `/api/v1/tenant/dashboard/overview` - Key metrics
2. `/api/v1/tenant/dashboard/booking-history` - Past bookings
3. `/api/v1/tenant/dashboard/spending-reports` - Spending analysis
4. `/api/v1/tenant/dashboard/saved-properties` - Wishlist
5. `/api/v1/tenant/dashboard/review-history` - Reviews given
6. `/api/v1/tenant/dashboard/upcoming-trips` - Future bookings
7. `/api/v1/tenant/dashboard/travel-statistics` - Travel stats

---

## 🎨 UI Components to Build

### Owner Dashboard:
```
┌─────────────────────────────────────────────┐
│  Owner Dashboard                             │
├─────────────────────────────────────────────┤
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐   │
│  │ 12   │  │ 8    │  │$125K │  │ 4.7  │   │
│  │Props │  │Active│  │Revenue│ │Rating│   │
│  └──────┘  └──────┘  └──────┘  └──────┘   │
│                                             │
│  📈 Revenue Chart                           │
│  ┌─────────────────────────────────────┐   │
│  │ [Revenue over time line chart]      │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  🏠 Property Performance                    │
│  ┌─────────────────────────────────────┐   │
│  │ Property 1    | 25 bookings | $8k   │   │
│  │ Property 2    | 18 bookings | $6k   │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

### Tenant Dashboard:
```
┌─────────────────────────────────────────────┐
│  My Trips Dashboard                          │
├─────────────────────────────────────────────┤
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐   │
│  │ 25   │  │ 2    │  │$35K  │  │ 12   │   │
│  │Trips │  │Coming│  │Spent │  │Saved │   │
│  └──────┘  └──────┘  └──────┘  └──────┘   │
│                                             │
│  ✈️ Upcoming Trips                          │
│  ┌─────────────────────────────────────┐   │
│  │ Beach House | Feb 15-20 | Confirmed │   │
│  │ Mountain    | Mar 1-5   | Confirmed │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  📊 Spending Chart                          │
│  ┌─────────────────────────────────────┐   │
│  │ [Spending over time bar chart]      │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

---

## 📦 Recommended Libraries

### Charting:
- **Recharts** - Best for React/Next.js
- **Chart.js** - Simple and versatile
- **ApexCharts** - Beautiful interactive charts

### Data Fetching:
- **SWR** - React hooks for data fetching (recommended)
- **React Query** - Powerful data synchronization
- **Axios** - Promise-based HTTP client

### UI Components:
- **Tailwind CSS** - Utility-first CSS
- **Shadcn/ui** - Beautiful React components
- **Headless UI** - Unstyled accessible components

---

## 🎯 Implementation Checklist

### Phase 1: Basic Dashboard (Day 1-2)
- [ ] Create dashboard layout
- [ ] Implement overview statistics cards
- [ ] Add loading states
- [ ] Add error handling

### Phase 2: Charts & Visualization (Day 3-4)
- [ ] Revenue timeline chart
- [ ] Booking statistics chart
- [ ] Occupancy rate gauge
- [ ] Performance comparison bars

### Phase 3: Advanced Features (Day 5-7)
- [ ] Date range picker
- [ ] Period selector (30/60/90 days)
- [ ] Export to CSV/PDF
- [ ] Real-time updates
- [ ] Mobile responsive design

### Phase 4: Tenant Dashboard (Day 8-10)
- [ ] Tenant overview
- [ ] Booking history table
- [ ] Spending charts
- [ ] Upcoming trips cards
- [ ] Travel statistics

---

## 💡 Pro Tips

### Performance:
```typescript
// Cache dashboard data for 5 minutes
const { data } = useSWR(url, fetcher, {
  refreshInterval: 300000,
  revalidateOnFocus: false
})
```

### Date Range:
```typescript
// Add a period selector
const periods = [
  { label: '7 days', value: 7 },
  { label: '30 days', value: 30 },
  { label: '90 days', value: 90 },
  { label: '1 year', value: 365 }
]
```

### Loading States:
```typescript
// Use skeleton loaders
{isLoading ? (
  <div className="grid gap-4">
    <Skeleton className="h-20" />
    <Skeleton className="h-20" />
  </div>
) : (
  <StatsCards data={stats} />
)}
```

---

## 🐛 Troubleshooting

### Issue: 401 Unauthorized
**Solution:** Check your authentication token
```typescript
// Verify token is valid
console.log('Token:', localStorage.getItem('token'))
```

### Issue: Empty Data
**Solution:** Make sure you have bookings/properties in database
```bash
# Run seeders
php artisan db:seed --class=BookingSeeder
```

### Issue: CORS Error
**Solution:** Configure CORS in Laravel
```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'],
```

---

## 📚 Additional Resources

- [Full API Documentation](./DASHBOARD_ANALYTICS_API_GUIDE.md)
- [Complete Implementation Guide](./TASK_2.5_2.6_COMPLETE.md)
- [Next.js Integration Guide](./NEXTJS_INTEGRATION_GUIDE.md)

---

## 🚀 Ready to Build!

You now have everything you need to build beautiful, functional dashboards for your RentHub application!

**Start with the Owner Dashboard overview, then expand to charts and advanced features.**

Need help? Check the API guide or reach out! 💪

---

**Happy Coding! 🎉**
