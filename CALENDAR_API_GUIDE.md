# Calendar Management API Guide

## Overview
Complete calendar management system supporting:
- ✅ Availability calendar queries
- ✅ Bulk date blocking/unblocking
- ✅ Custom pricing for date ranges
- ✅ iCal export/import
- ✅ External calendar sync (Airbnb, Booking.com, VRBO, Google Calendar)
- ✅ Automated sync with cron

## Base URL
```
http://localhost/api/v1
```

## Authentication
All protected endpoints require:
```
Authorization: Bearer {token}
```

---

## 📅 Calendar Availability Endpoints

### 1. Get Availability Calendar
Get availability and pricing for a date range.

**Endpoint:** `GET /properties/{property_id}/calendar`

**Query Parameters:**
- `start_date` (required): YYYY-MM-DD format
- `end_date` (required): YYYY-MM-DD format
- `months` (optional): Alternative to end_date - get N months from start

**Example Request:**
```bash
curl -X GET "http://localhost/api/v1/properties/1/calendar?start_date=2025-11-01&end_date=2025-11-30" \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "property_id": 1,
    "start_date": "2025-11-01",
    "end_date": "2025-11-30",
    "base_price": 100.00,
    "calendar": [
      {
        "date": "2025-11-01",
        "available": true,
        "blocked": false,
        "booked": false,
        "price": 100.00,
        "is_custom_price": false
      },
      {
        "date": "2025-11-15",
        "available": false,
        "blocked": true,
        "booked": false,
        "price": 100.00,
        "is_custom_price": false
      },
      {
        "date": "2025-11-25",
        "available": true,
        "blocked": false,
        "booked": false,
        "price": 150.00,
        "is_custom_price": true
      }
    ]
  }
}
```

### 2. Get Pricing Calendar
Get custom pricing overview.

**Endpoint:** `GET /properties/{property_id}/calendar/pricing`

**Query Parameters:**
- `start_date` (required)
- `end_date` (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "property_id": 1,
    "base_price": 100.00,
    "custom_pricing": {
      "2025-11-25": {
        "price": 150.00,
        "is_custom": true
      },
      "2025-12-24": {
        "price": 200.00,
        "is_custom": true
      }
    }
  }
}
```

### 3. Get Blocked Dates
Get list of all blocked dates.

**Endpoint:** `GET /properties/{property_id}/calendar/blocked-dates`

**Response:**
```json
{
  "success": true,
  "data": {
    "property_id": 1,
    "blocked_dates": ["2025-11-15", "2025-12-25"],
    "count": 2
  }
}
```

---

## 🚫 Bulk Date Management

### 4. Bulk Block Dates
Block a date range at once.

**Endpoint:** `POST /properties/{property_id}/calendar/bulk-block`

**Body:**
```json
{
  "start_date": "2025-12-24",
  "end_date": "2025-12-31",
  "reason": "Holiday closure"
}
```

**Response:**
```json
{
  "success": true,
  "message": "7 dates blocked successfully",
  "data": {
    "blocked_count": 7,
    "start_date": "2025-12-24",
    "end_date": "2025-12-31"
  }
}
```

### 5. Bulk Unblock Dates
Unblock a date range.

**Endpoint:** `POST /properties/{property_id}/calendar/bulk-unblock`

**Body:**
```json
{
  "start_date": "2025-12-24",
  "end_date": "2025-12-31"
}
```

### 6. Bulk Set Pricing
Set custom price for date range.

**Endpoint:** `POST /properties/{property_id}/calendar/bulk-pricing`

**Body:**
```json
{
  "start_date": "2025-12-20",
  "end_date": "2026-01-05",
  "price": 150.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "Custom pricing set for 16 dates",
  "data": {
    "updated_count": 16,
    "start_date": "2025-12-20",
    "end_date": "2026-01-05",
    "price": 150.00
  }
}
```

### 7. Remove Custom Pricing
Remove custom pricing for date range (revert to base price).

**Endpoint:** `DELETE /properties/{property_id}/calendar/bulk-pricing`

**Body:**
```json
{
  "start_date": "2025-12-20",
  "end_date": "2026-01-05"
}
```

---

## 📥 iCal Export/Import

### 8. Export Property Calendar as iCal
Export property availability as .ics file.

**Endpoint:** `GET /properties/{property_id}/ical` (Public)

**Example:**
```bash
curl "http://localhost/api/v1/properties/1/ical" -o property-calendar.ics
```

**Use Case:** Import this URL into:
- Airbnb calendar settings
- Booking.com extranet
- Google Calendar
- VRBO

### 9. Get iCal URL
Get the public iCal URL for sharing.

**Endpoint:** `GET /properties/{property_id}/ical-url`

**Response:**
```json
{
  "success": true,
  "data": {
    "property_id": 1,
    "ical_url": "http://localhost/api/v1/properties/1/ical",
    "instructions": "Copy this URL and add it to your external calendar application"
  }
}
```

---

## 🔗 External Calendar Management

### 10. List External Calendars
Get all connected external calendars.

**Endpoint:** `GET /properties/{property_id}/external-calendars`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property_id": 1,
      "platform": "airbnb",
      "name": "Airbnb Calendar",
      "url": "https://airbnb.com/calendar/ical/...",
      "sync_enabled": true,
      "last_synced_at": "2025-11-02T10:30:00Z",
      "sync_error": null,
      "latest_sync_log": {
        "status": "success",
        "dates_added": 5,
        "synced_at": "2025-11-02T10:30:00Z"
      }
    }
  ]
}
```

### 11. Add External Calendar
Connect external calendar (Airbnb, Booking.com, etc.).

**Endpoint:** `POST /properties/{property_id}/external-calendars`

**Body:**
```json
{
  "platform": "airbnb",
  "url": "https://airbnb.com/calendar/ical/...",
  "name": "My Airbnb Listing",
  "sync_enabled": true
}
```

**Platforms:**
- `airbnb`
- `booking_com`
- `vrbo`
- `ical` (generic)
- `google`

**How to get iCal URLs:**
- **Airbnb:** Listing > Availability > Export calendar
- **Booking.com:** Extranet > Calendar > Sync calendars
- **VRBO:** Property dashboard > Calendar > Export

### 12. Update External Calendar
Update calendar settings.

**Endpoint:** `PUT /properties/{property_id}/external-calendars/{calendar_id}`

**Body:**
```json
{
  "name": "Updated Name",
  "sync_enabled": false
}
```

### 13. Delete External Calendar
Remove external calendar connection.

**Endpoint:** `DELETE /properties/{property_id}/external-calendars/{calendar_id}`

### 14. Manual Sync Trigger
Trigger immediate sync for a calendar.

**Endpoint:** `POST /properties/{property_id}/external-calendars/{calendar_id}/sync`

**Response:**
```json
{
  "success": true,
  "message": "Calendar synced successfully",
  "data": {
    "sync_result": {
      "success": true,
      "dates_added": 3,
      "dates_removed": 0,
      "total_events": 15,
      "total_blocked_dates": 18
    },
    "sync_log": {
      "id": 123,
      "status": "success",
      "dates_added": 3,
      "synced_at": "2025-11-02T12:00:00Z"
    }
  }
}
```

### 15. Get Sync Logs
View sync history for a calendar.

**Endpoint:** `GET /properties/{property_id}/external-calendars/{calendar_id}/logs`

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 123,
        "status": "success",
        "dates_added": 3,
        "dates_removed": 0,
        "dates_updated": 0,
        "error_message": null,
        "synced_at": "2025-11-02T12:00:00Z"
      }
    ],
    "current_page": 1,
    "per_page": 20
  }
}
```

---

## 🤖 Automated Sync

### Artisan Command
Sync all external calendars:

```bash
# Sync all calendars
php artisan calendar:sync

# Sync only for specific property
php artisan calendar:sync --property=1

# Force sync even if recently synced
php artisan calendar:sync --force
```

### Cron Schedule
Automatically syncs every 6 hours:

```php
// routes/console.php
Schedule::command('calendar:sync')->everySixHours();
```

**Setup Cron (Linux):**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Setup Task Scheduler (Windows):**
Run every minute:
```
C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe C:\laragon\www\RentHub\backend\artisan schedule:run
```

---

## 📖 Usage Examples

### Complete Calendar Workflow

#### 1. Setup: Export your calendar
```bash
# Get your iCal URL
GET /properties/1/ical-url

# Share this URL with Airbnb/Booking.com
```

#### 2. Import external calendars
```bash
# Add Airbnb calendar
POST /properties/1/external-calendars
{
  "platform": "airbnb",
  "url": "https://airbnb.com/calendar/ical/xxxxx",
  "name": "Airbnb Main Listing"
}

# Add Booking.com calendar
POST /properties/1/external-calendars
{
  "platform": "booking_com",
  "url": "https://admin.booking.com/hotel/hoteladmin/ical.html?...",
  "name": "Booking.com Property"
}
```

#### 3. Manual sync
```bash
POST /properties/1/external-calendars/1/sync
```

#### 4. Block special dates
```bash
# Block Christmas week
POST /properties/1/calendar/bulk-block
{
  "start_date": "2025-12-24",
  "end_date": "2025-12-31",
  "reason": "Holiday closure"
}
```

#### 5. Set seasonal pricing
```bash
# Set higher price for summer
POST /properties/1/calendar/bulk-pricing
{
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "price": 200.00
}
```

#### 6. Check availability
```bash
GET /properties/1/calendar?start_date=2025-12-01&months=3
```

---

## Error Handling

### Common Errors

**401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**422 Validation Error**
```json
{
  "success": false,
  "message": "Date range cannot exceed 365 days",
  "errors": {
    "end_date": ["The end date must be within 365 days"]
  }
}
```

**500 Sync Failed**
```json
{
  "success": false,
  "message": "Sync failed",
  "data": {
    "error": "Failed to fetch iCal feed: 404"
  }
}
```

---

## Testing

### Test Calendar Features

```bash
# 1. Get availability
curl "http://localhost/api/v1/properties/1/calendar?start_date=2025-11-01&end_date=2025-11-30"

# 2. Block dates
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-block" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"start_date":"2025-12-24","end_date":"2025-12-31"}'

# 3. Set custom pricing
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-pricing" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"start_date":"2025-12-01","end_date":"2025-12-31","price":150}'

# 4. Export iCal
curl "http://localhost/api/v1/properties/1/ical" -o calendar.ics

# 5. Add external calendar
curl -X POST "http://localhost/api/v1/properties/1/external-calendars" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"platform":"airbnb","url":"https://airbnb.com/ical/xxx","name":"Test"}'
```

---

## Next Steps

1. ✅ Phase 1: Enhanced Calendar APIs - COMPLETED
2. ✅ Phase 2: iCal Support - COMPLETED
3. ⏳ Phase 3: Google Calendar OAuth (optional)
4. ⏳ Phase 4: Filament Admin UI
5. ✅ Phase 5: Automated Sync - COMPLETED

## Notes

- iCal format is universal - works with all major platforms
- Sync runs every 6 hours automatically
- Manual sync available anytime via API
- Maximum 365 days per bulk operation
- All dates in YYYY-MM-DD format (UTC)
- Blocked dates from external sources won't be auto-removed
