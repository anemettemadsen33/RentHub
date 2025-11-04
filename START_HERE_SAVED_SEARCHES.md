# 🚀 Quick Start - Saved Searches

## 📋 Ce este Saved Searches?
Sistem care permite utilizatorilor să salveze criteriile de căutare și să primească alerte automate când apar proprietăți noi care se potrivesc.

---

## ⚡ Quick Setup

### 1. Database Migration
```bash
cd backend
php artisan migrate
```

### 2. Schedule Setup
Adaugă în cron (sau rulează scheduler-ul Laravel):
```bash
* * * * * cd /path-to-project/backend && php artisan schedule:run >> /dev/null 2>&1
```

Sau pentru testing local:
```bash
php artisan schedule:work
```

---

## 🧪 Test API

### 1. Get Auth Token
```bash
# Login
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'

# Copy the token from response
```

### 2. Create Saved Search
```bash
curl -X POST http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My First Search",
    "location": "Bucharest",
    "latitude": 44.4268,
    "longitude": 26.1025,
    "radius_km": 10,
    "min_price": 50,
    "max_price": 150,
    "min_bedrooms": 2,
    "enable_alerts": true,
    "alert_frequency": "daily"
  }'
```

### 3. Get All Saved Searches
```bash
curl http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Execute a Search
```bash
curl -X POST http://localhost/api/v1/saved-searches/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. Check New Listings
```bash
curl http://localhost/api/v1/saved-searches/1/new-listings \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Admin Panel

### Access
```
http://localhost/admin/saved-searches
```

### Features
- View all saved searches
- Create/Edit/Delete searches
- Execute search directly
- View statistics

---

## 🔔 Test Alerts

### Manual Alert Sending
```bash
# Send daily alerts
php artisan saved-searches:send-alerts daily

# Send instant alerts
php artisan saved-searches:send-alerts instant

# Send weekly alerts
php artisan saved-searches:send-alerts weekly
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📱 Frontend Integration

### 1. Create Save Search Button
```typescript
// components/SaveSearchButton.tsx
import { useState } from 'react';

export function SaveSearchButton({ searchParams }) {
  const [name, setName] = useState('');
  const [open, setOpen] = useState(false);

  const handleSave = async () => {
    const response = await fetch('/api/v1/saved-searches', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name,
        ...searchParams,
        enable_alerts: true,
        alert_frequency: 'daily'
      })
    });

    if (response.ok) {
      alert('Search saved!');
      setOpen(false);
    }
  };

  return (
    <>
      <button onClick={() => setOpen(true)}>
        Save Search
      </button>
      
      {open && (
        <dialog open>
          <h2>Save This Search</h2>
          <input 
            type="text" 
            placeholder="Name..." 
            value={name}
            onChange={(e) => setName(e.target.value)}
          />
          <button onClick={handleSave}>Save</button>
          <button onClick={() => setOpen(false)}>Cancel</button>
        </dialog>
      )}
    </>
  );
}
```

### 2. List Saved Searches
```typescript
// app/saved-searches/page.tsx
'use client';

import { useEffect, useState } from 'react';

export default function SavedSearchesPage() {
  const [searches, setSearches] = useState([]);

  useEffect(() => {
    fetch('/api/v1/saved-searches', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then(res => res.json())
    .then(data => setSearches(data.data));
  }, []);

  const executeSearch = async (id) => {
    const response = await fetch(`/api/v1/saved-searches/${id}/execute`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    const data = await response.json();
    console.log(`Found ${data.data.count} properties`);
    // Redirect to results page
  };

  return (
    <div>
      <h1>My Saved Searches</h1>
      {searches.map(search => (
        <div key={search.id}>
          <h3>{search.name}</h3>
          <p>{search.location}</p>
          <p>Price: €{search.min_price} - €{search.max_price}</p>
          <p>Alerts: {search.enable_alerts ? 'ON' : 'OFF'}</p>
          <button onClick={() => executeSearch(search.id)}>
            Execute Search
          </button>
        </div>
      ))}
    </div>
  );
}
```

---

## 🔧 Configuration

### Email Settings
Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@renthub.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Settings (Optional but Recommended)
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

---

## 📊 Common Use Cases

### 1. "Summer Vacation in Brasov"
```json
{
  "name": "Summer Vacation in Brasov",
  "location": "Brasov, Romania",
  "latitude": 45.6534,
  "longitude": 25.6086,
  "radius_km": 20,
  "min_price": 80,
  "max_price": 200,
  "min_bedrooms": 2,
  "min_guests": 4,
  "check_in": "2025-07-01",
  "check_out": "2025-07-15",
  "amenities": [1, 5, 8],
  "enable_alerts": true,
  "alert_frequency": "weekly"
}
```

### 2. "Business Trips to Bucharest"
```json
{
  "name": "Business Trips to Bucharest",
  "location": "Bucharest City Center",
  "latitude": 44.4268,
  "longitude": 26.1025,
  "radius_km": 5,
  "min_price": 40,
  "max_price": 100,
  "min_bedrooms": 1,
  "property_type": "apartment",
  "amenities": [1, 5],
  "enable_alerts": true,
  "alert_frequency": "instant"
}
```

### 3. "Family Weekend Getaway"
```json
{
  "name": "Family Weekend Getaway",
  "location": "Sinaia",
  "latitude": 45.3500,
  "longitude": 25.5500,
  "radius_km": 10,
  "min_price": 100,
  "max_price": 300,
  "min_bedrooms": 3,
  "min_guests": 6,
  "amenities": [1, 5, 8, 12],
  "enable_alerts": true,
  "alert_frequency": "daily"
}
```

---

## 🐛 Troubleshooting

### Issue: Alerts not sending
**Solution:**
1. Check scheduler is running: `php artisan schedule:work`
2. Check queue worker (if using queues): `php artisan queue:work`
3. Check mail configuration in `.env`
4. Check logs: `tail -f storage/logs/laravel.log`

### Issue: "Unauthorized" error
**Solution:**
- Ensure you're sending `Authorization: Bearer TOKEN` header
- Check token is valid: `curl /api/v1/me -H "Authorization: Bearer TOKEN"`

### Issue: No results when executing search
**Solution:**
- Check if properties exist in database
- Verify search criteria aren't too restrictive
- Check if properties are published: `is_published = 1`

---

## 📚 Full Documentation
- [Complete API Guide](./SAVED_SEARCHES_API_GUIDE.md)
- [Implementation Details](./TASK_2.4_SAVED_SEARCHES_COMPLETE.md)

---

## ✅ Quick Checklist

Before going to production:

- [ ] Database migrated
- [ ] Cron job scheduled
- [ ] Email configured
- [ ] Queue worker running (optional but recommended)
- [ ] Frontend components created
- [ ] API tested
- [ ] Alerts tested
- [ ] Admin panel accessible

---

**🎉 You're ready to use Saved Searches!**

For support or questions, refer to the full documentation.
