# 📋 Saved Searches API Guide

## Overview
API pentru salvarea criteriilor de căutare și primirea de alerte automate când apar proprietăți noi.

---

## 🔗 API Endpoints

### Base URL
```
/api/v1/saved-searches
```

---

## 📌 Endpoints

### 1. **Get All Saved Searches**
```http
GET /api/v1/saved-searches
```

**Query Parameters:**
- `is_active` (optional): Filter by active status (true/false)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "name": "Summer Vacation in Bucharest",
      "location": "Bucharest, Romania",
      "latitude": 44.4268,
      "longitude": 26.1025,
      "radius_km": 10,
      "min_price": 50,
      "max_price": 150,
      "min_bedrooms": 2,
      "max_bedrooms": null,
      "min_bathrooms": 1,
      "max_bathrooms": null,
      "min_guests": 4,
      "property_type": "apartment",
      "amenities": [1, 3, 5],
      "check_in": "2025-07-01",
      "check_out": "2025-07-15",
      "enable_alerts": true,
      "alert_frequency": "daily",
      "last_alert_sent_at": "2025-11-01T10:00:00Z",
      "new_listings_count": 3,
      "is_active": true,
      "search_count": 15,
      "last_searched_at": "2025-11-02T14:30:00Z",
      "created_at": "2025-10-15T08:00:00Z",
      "updated_at": "2025-11-02T14:30:00Z"
    }
  ]
}
```

---

### 2. **Create Saved Search**
```http
POST /api/v1/saved-searches
```

**Request Body:**
```json
{
  "name": "Summer Vacation in Bucharest",
  "location": "Bucharest, Romania",
  "latitude": 44.4268,
  "longitude": 26.1025,
  "radius_km": 10,
  "min_price": 50,
  "max_price": 150,
  "min_bedrooms": 2,
  "min_bathrooms": 1,
  "min_guests": 4,
  "property_type": "apartment",
  "amenities": [1, 3, 5],
  "check_in": "2025-07-01",
  "check_out": "2025-07-15",
  "enable_alerts": true,
  "alert_frequency": "daily"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Saved search created successfully",
  "data": {
    "id": 1,
    "user_id": 123,
    "name": "Summer Vacation in Bucharest",
    ...
  }
}
```

---

### 3. **Get Specific Saved Search**
```http
GET /api/v1/saved-searches/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Summer Vacation in Bucharest",
    ...
  }
}
```

---

### 4. **Update Saved Search**
```http
PUT /api/v1/saved-searches/{id}
```

**Request Body:** (all fields optional)
```json
{
  "name": "Updated Search Name",
  "min_price": 60,
  "max_price": 180,
  "enable_alerts": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Saved search updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Search Name",
    ...
  }
}
```

---

### 5. **Delete Saved Search**
```http
DELETE /api/v1/saved-searches/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Saved search deleted successfully"
}
```

---

### 6. **Execute Saved Search**
```http
POST /api/v1/saved-searches/{id}/execute
```

**Response:**
```json
{
  "success": true,
  "data": {
    "saved_search": {
      "id": 1,
      "name": "Summer Vacation in Bucharest",
      "search_count": 16,
      "last_searched_at": "2025-11-02T15:00:00Z"
    },
    "properties": [
      {
        "id": 45,
        "title": "Cozy Apartment in City Center",
        "price_per_night": 75,
        "bedrooms": 2,
        "bathrooms": 1,
        ...
      }
    ],
    "count": 1
  }
}
```

---

### 7. **Check New Listings**
```http
GET /api/v1/saved-searches/{id}/new-listings
```

**Response:**
```json
{
  "success": true,
  "data": {
    "saved_search": {
      "id": 1,
      "name": "Summer Vacation in Bucharest"
    },
    "new_properties": [
      {
        "id": 47,
        "title": "Modern Apartment",
        "price_per_night": 85,
        "created_at": "2025-11-02T10:00:00Z"
      }
    ],
    "count": 1,
    "since": "2025-11-01T10:00:00Z"
  }
}
```

---

### 8. **Toggle Alerts**
```http
POST /api/v1/saved-searches/{id}/toggle-alerts
```

**Response:**
```json
{
  "success": true,
  "message": "Alerts enabled successfully",
  "data": {
    "id": 1,
    "enable_alerts": true
  }
}
```

---

### 9. **Get Statistics**
```http
GET /api/v1/saved-searches/statistics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_searches": 5,
    "active_searches": 4,
    "with_alerts": 3,
    "most_used": [
      {
        "id": 1,
        "name": "Summer Vacation",
        "search_count": 25
      }
    ],
    "recent": [
      {
        "id": 2,
        "name": "Business Trips",
        "last_searched_at": "2025-11-02T14:30:00Z"
      }
    ]
  }
}
```

---

## 📝 Search Criteria Fields

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Name for this saved search |
| `location` | string | Location description |
| `latitude` | decimal | Latitude for radius search |
| `longitude` | decimal | Longitude for radius search |
| `radius_km` | integer | Search radius in kilometers (1-100) |
| `min_price` | decimal | Minimum price per night |
| `max_price` | decimal | Maximum price per night |
| `min_bedrooms` | integer | Minimum number of bedrooms |
| `max_bedrooms` | integer | Maximum number of bedrooms |
| `min_bathrooms` | integer | Minimum number of bathrooms |
| `max_bathrooms` | integer | Maximum number of bathrooms |
| `min_guests` | integer | Minimum guest capacity |
| `property_type` | string | Property type filter |
| `amenities` | array | Array of amenity IDs |
| `check_in` | date | Check-in date |
| `check_out` | date | Check-out date |
| `enable_alerts` | boolean | Enable email alerts |
| `alert_frequency` | enum | `instant`, `daily`, or `weekly` |
| `is_active` | boolean | Search is active |

---

## 🔔 Alert System

### Alert Frequencies
- **Instant**: Max once per hour (to avoid spam)
- **Daily**: Once every 24 hours
- **Weekly**: Once every 7 days

### Alert Triggers
Alerts are sent when:
1. New properties matching all criteria are published
2. Sufficient time has passed since last alert (based on frequency)
3. Alerts are enabled for the saved search

### Email Notifications
Users receive:
- Subject: "🔔 X New Properties Match Your Search: [Search Name]"
- First 5 matching properties with details
- Link to view all results
- Option to disable alerts

---

## ⚙️ Automated Jobs

### Schedule in `app/Console/Kernel.php`:
```php
$schedule->command('saved-searches:send-alerts instant')->hourly();
$schedule->command('saved-searches:send-alerts daily')->daily();
$schedule->command('saved-searches:send-alerts weekly')->weekly();
```

### Manual Execution:
```bash
# Send instant alerts
php artisan saved-searches:send-alerts instant

# Send daily alerts
php artisan saved-searches:send-alerts daily

# Send weekly alerts
php artisan saved-searches:send-alerts weekly
```

---

## 🎨 Filament Admin Panel

### Features:
- ✅ View all saved searches
- ✅ Create/Edit/Delete saved searches
- ✅ Execute search directly from admin
- ✅ Filter by active status, alert settings
- ✅ View statistics (search count, new listings)
- ✅ Manage alert settings

### Access:
```
/admin/saved-searches
```

---

## 📊 Usage Examples

### Example 1: Save a Search
```javascript
const response = await fetch('/api/v1/saved-searches', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Weekend Getaway',
    location: 'Brasov',
    latitude: 45.6534,
    longitude: 25.6086,
    radius_km: 20,
    min_price: 80,
    max_price: 200,
    min_bedrooms: 1,
    amenities: [1, 5, 8], // WiFi, Parking, Kitchen
    enable_alerts: true,
    alert_frequency: 'weekly'
  })
});
```

### Example 2: Execute Search
```javascript
const response = await fetch('/api/v1/saved-searches/1/execute', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
});

const data = await response.json();
console.log(`Found ${data.data.count} properties`);
```

### Example 3: Check New Listings
```javascript
const response = await fetch('/api/v1/saved-searches/1/new-listings', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
});

const data = await response.json();
console.log(`${data.data.count} new properties since last alert`);
```

---

## 🔒 Security

- ✅ All endpoints require authentication
- ✅ Users can only access their own saved searches
- ✅ Input validation on all fields
- ✅ Rate limiting on alert sending
- ✅ SQL injection protection via Eloquent

---

## 🧪 Testing with cURL

### Create Saved Search:
```bash
curl -X POST http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Search",
    "location": "Bucharest",
    "latitude": 44.4268,
    "longitude": 26.1025,
    "radius_km": 10,
    "min_price": 50,
    "max_price": 150,
    "enable_alerts": true,
    "alert_frequency": "daily"
  }'
```

### Execute Search:
```bash
curl -X POST http://localhost/api/v1/saved-searches/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Statistics:
```bash
curl http://localhost/api/v1/saved-searches/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📱 Frontend Integration (Next.js)

### Save Search Component:
```typescript
// components/SaveSearchDialog.tsx
'use client';

import { useState } from 'react';

interface SaveSearchProps {
  searchParams: any;
}

export function SaveSearchDialog({ searchParams }: SaveSearchProps) {
  const [name, setName] = useState('');
  const [enableAlerts, setEnableAlerts] = useState(true);
  const [frequency, setFrequency] = useState('daily');

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
        enable_alerts: enableAlerts,
        alert_frequency: frequency
      })
    });

    if (response.ok) {
      alert('Search saved successfully!');
    }
  };

  return (
    <dialog>
      <h2>Save This Search</h2>
      <input 
        type="text" 
        placeholder="Name this search..." 
        value={name}
        onChange={(e) => setName(e.target.value)}
      />
      <label>
        <input 
          type="checkbox" 
          checked={enableAlerts}
          onChange={(e) => setEnableAlerts(e.target.checked)}
        />
        Enable alerts
      </label>
      {enableAlerts && (
        <select value={frequency} onChange={(e) => setFrequency(e.target.value)}>
          <option value="instant">Instant</option>
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
        </select>
      )}
      <button onClick={handleSave}>Save Search</button>
    </dialog>
  );
}
```

---

## ✅ Task Completion

### ✨ Implemented Features:
- [x] Save search criteria
- [x] Multiple saved searches per user
- [x] Execute saved searches
- [x] Get alerts for new listings
- [x] Email notifications
- [x] Alert frequency settings (instant/daily/weekly)
- [x] Quick access to saved searches
- [x] Filament admin panel
- [x] API endpoints
- [x] Automated jobs for alerts

### 🎯 Benefits:
- Users don't need to re-enter search criteria
- Automatic notifications for new properties
- Better user engagement
- Increased conversions
- Time-saving for users

---

## 📚 Related Documentation
- [Map Search API](./MAP_SEARCH_API_GUIDE.md)
- [Property API](./API_ENDPOINTS.md)
- [Wishlist API](./WISHLIST_API_GUIDE.md)

---

**Task 2.4 - Saved Searches: Complete! ✅**
