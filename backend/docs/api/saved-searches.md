# Saved Searches API Documentation

## Overview
The Saved Searches API allows users to save their property search criteria and receive alerts when new matching properties become available.

## Base URL
```
/api/v1/saved-searches
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header.

---

## Endpoints

### 1. Get All Saved Searches
**GET** `/api/v1/saved-searches`

Get all saved searches for the authenticated user.

**Query Parameters:**
- `is_active` (boolean, optional) - Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "name": "Summer Vacation in Bucharest",
      "location": "Bucharest, Romania",
      "latitude": 44.4268,
      "longitude": 26.1025,
      "radius_km": 10,
      "min_price": 50,
      "max_price": 200,
      "min_bedrooms": 2,
      "property_type": "apartment",
      "amenities": [1, 3, 5],
      "check_in": "2024-07-01",
      "check_out": "2024-07-15",
      "enable_alerts": true,
      "alert_frequency": "daily",
      "is_active": true,
      "search_count": 12,
      "last_searched_at": "2024-01-15T14:20:00Z"
    }
  ]
}
```

### 2. Create Saved Search
**POST** `/api/v1/saved-searches`

**Request Body:**
```json
{
  "name": "Summer Vacation in Bucharest",
  "location": "Bucharest, Romania",
  "latitude": 44.4268,
  "longitude": 26.1025,
  "radius_km": 10,
  "min_price": 50,
  "max_price": 200,
  "min_bedrooms": 2,
  "amenities": [1, 3, 5],
  "enable_alerts": true,
  "alert_frequency": "daily"
}
```

### 3. Execute Saved Search
**POST** `/api/v1/saved-searches/{id}/execute`

Execute a saved search and return matching properties.

### 4. Toggle Alerts
**POST** `/api/v1/saved-searches/{id}/toggle-alerts`

Enable/disable alerts for a saved search.

---

## Alert Frequencies
- **instant**: Hourly alerts
- **daily**: Once per day
- **weekly**: Once per week
