# 🚀 Saved Searches - Quick Start Guide

## Backend Setup (Already Complete ✅)

### 1. Database
```bash
cd backend
php artisan migrate  # Already migrated
```

### 2. Test API Endpoints

#### Create a Saved Search
```bash
curl -X POST http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Beach House Summer 2024",
    "location": "Constanta, Romania",
    "latitude": 44.1598,
    "longitude": 28.6348,
    "radius_km": 20,
    "min_price": 100,
    "max_price": 300,
    "min_bedrooms": 2,
    "property_type": "house",
    "enable_alerts": true,
    "alert_frequency": "daily"
  }'
```

#### Get All Saved Searches
```bash
curl http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Execute a Search
```bash
curl -X POST http://localhost/api/v1/saved-searches/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Toggle Alerts
```bash
curl -X POST http://localhost/api/v1/saved-searches/1/toggle-alerts \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Alert System

#### Run Manually
```bash
cd backend
php artisan saved-searches:send-alerts
```

#### Check Scheduled Tasks
```bash
php artisan schedule:list | grep "saved-searches"
```

Output should show:
- `saved-searches:send-alerts instant` - Hourly
- `saved-searches:send-alerts daily` - Daily at midnight
- `saved-searches:send-alerts weekly` - Weekly on Sundays

---

## Frontend Integration

### 1. Install Required Package (if not installed)
```bash
cd frontend
npm install axios
# or
yarn add axios
```

### 2. Create Saved Searches Page

**File**: `frontend/src/app/saved-searches/page.tsx`

```tsx
import { SavedSearchesList } from '@/components/SavedSearches';

export default function SavedSearchesPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <SavedSearchesList />
    </div>
  );
}
```

### 3. Add to Navigation

Add to your main navigation:
```tsx
<Link href="/saved-searches">
  🔍 Saved Searches
</Link>
```

### 4. Add "Save Search" Button to Search Results

**File**: `frontend/src/app/search/page.tsx`

```tsx
import { savedSearchesApi } from '@/services/api/savedSearches';
import { useState } from 'react';

function SearchResults() {
  const [searchParams, setSearchParams] = useState({...});

  const handleSaveSearch = async () => {
    const name = prompt('Name your search:');
    if (!name) return;

    try {
      await savedSearchesApi.create({
        name,
        ...searchParams, // Your current search filters
        enable_alerts: true,
        alert_frequency: 'daily',
      });
      alert('Search saved successfully! 🎉');
    } catch (error) {
      alert('Failed to save search');
    }
  };

  return (
    <div>
      <button onClick={handleSaveSearch}>
        💾 Save This Search
      </button>
      {/* ... rest of search results */}
    </div>
  );
}
```

---

## Testing Checklist

### Backend ✅
- [x] Model & Migration created
- [x] API endpoints working
- [x] Notification class created
- [x] Command working
- [x] Scheduled tasks configured
- [x] Filament resource accessible

### Frontend 🔄
- [ ] Install on page: `/saved-searches`
- [ ] Test create/edit/delete
- [ ] Test execute search
- [ ] Test toggle alerts
- [ ] Add "Save Search" button on search page

### Email Notifications 📧
- [ ] Configure mail settings in `.env`:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=your_username
  MAIL_PASSWORD=your_password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=noreply@renthub.com
  MAIL_FROM_NAME="RentHub"
  ```
- [ ] Test email sending:
  ```bash
  php artisan saved-searches:send-alerts
  ```

---

## API Endpoints Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/saved-searches` | List all searches |
| `POST` | `/api/v1/saved-searches` | Create new search |
| `GET` | `/api/v1/saved-searches/{id}` | Get one search |
| `PUT` | `/api/v1/saved-searches/{id}` | Update search |
| `DELETE` | `/api/v1/saved-searches/{id}` | Delete search |
| `POST` | `/api/v1/saved-searches/{id}/execute` | Run search |
| `GET` | `/api/v1/saved-searches/{id}/new-listings` | Check new properties |
| `POST` | `/api/v1/saved-searches/{id}/toggle-alerts` | Toggle alerts |
| `GET` | `/api/v1/saved-searches/statistics` | Get user stats |

---

## Alert Frequencies

- **instant**: Checks every hour for new listings
- **daily**: Checks once per day at midnight
- **weekly**: Checks once per week on Sunday

---

## Common Issues & Solutions

### Issue: Emails not sending
**Solution**: Check mail configuration in `.env` and test with:
```bash
php artisan tinker
Mail::raw('Test', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

### Issue: Cron not running
**Solution**: Add to crontab:
```bash
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

### Issue: Frontend API calls failing
**Solution**: Check CORS settings in `backend/config/cors.php` and API URL in frontend `.env`:
```env
NEXT_PUBLIC_API_URL=http://localhost/api/v1
```

---

## Next Steps

1. ✅ **Test Backend API** using Postman or curl
2. 🔄 **Integrate Frontend** - Create the saved-searches page
3. 📧 **Configure Email** - Set up mail server
4. 🎨 **Customize UI** - Adjust styles to match your theme
5. 🧪 **Test Alerts** - Create test searches and run command
6. 🚀 **Deploy** - Push to production

---

## Support

For issues or questions:
- Check API docs: `backend/docs/api/saved-searches.md`
- Review implementation: `SAVED_SEARCHES_IMPLEMENTATION.md`
- Check Laravel logs: `backend/storage/logs/laravel.log`

---

**Status**: ✅ Backend Complete | 🔄 Frontend Ready for Integration

**Estimated Integration Time**: 1-2 hours
