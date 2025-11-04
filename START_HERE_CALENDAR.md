# 🗓️ Calendar Management - Quick Start Guide

## Overview
Complete calendar management system with external platform synchronization (Airbnb, Booking.com, VRBO, Google Calendar).

## ✨ Features
- 📅 Availability calendar queries
- 🚫 Bulk date blocking/unblocking
- 💰 Custom pricing for date ranges
- 📤 iCal export (share with other platforms)
- 📥 iCal import (sync from other platforms)
- 🔄 Automated sync every 6 hours
- 📊 Sync history and logs

## 🚀 Quick Start

### 1. Basic Calendar Operations

#### Get Calendar Availability
```bash
curl "http://localhost/api/v1/properties/1/calendar?start_date=2025-11-01&months=3"
```

#### Block Date Range
```bash
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-block" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-24",
    "end_date": "2025-12-31"
  }'
```

#### Set Custom Pricing
```bash
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-pricing" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-06-01",
    "end_date": "2025-08-31",
    "price": 200.00
  }'
```

### 2. External Calendar Sync

#### Export Your Calendar
```bash
# Get iCal URL for your property
curl "http://localhost/api/v1/properties/1/ical-url" \
  -H "Authorization: Bearer {token}"

# Response: { "ical_url": "http://localhost/api/v1/properties/1/ical" }
```

**Add this URL to:**
- **Airbnb:** Listing → Availability → Import calendar
- **Booking.com:** Extranet → Calendar → Import calendar  
- **VRBO:** Property → Calendar → Import calendar

#### Import External Calendar
```bash
curl -X POST "http://localhost/api/v1/properties/1/external-calendars" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "airbnb",
    "url": "https://www.airbnb.com/calendar/ical/xxxxx.ics",
    "name": "My Airbnb Listing"
  }'
```

#### Sync Calendar Manually
```bash
curl -X POST "http://localhost/api/v1/properties/1/external-calendars/1/sync" \
  -H "Authorization: Bearer {token}"
```

### 3. Automated Sync

#### Run Manual Sync
```bash
# Sync all calendars
php artisan calendar:sync

# Sync specific property
php artisan calendar:sync --property=1

# Force sync (ignore last sync time)
php artisan calendar:sync --force
```

#### Automatic Sync
Calendars sync automatically every 6 hours via cron.

**Setup Cron (Linux/Mac):**
```bash
* * * * * cd /path/to/renthub/backend && php artisan schedule:run >> /dev/null 2>&1
```

**Setup Task Scheduler (Windows):**
```
Program: C:\path\to\php.exe
Arguments: C:\path\to\renthub\backend\artisan schedule:run
Trigger: Every 1 minute
```

## 📋 Common Use Cases

### Use Case 1: Property Owner with Multiple Platforms
**Scenario:** You list on Airbnb and Booking.com, want to sync all bookings to RentHub.

**Solution:**
1. Get your iCal export URLs from Airbnb and Booking.com
2. Add them to RentHub:
   ```bash
   POST /properties/1/external-calendars
   { "platform": "airbnb", "url": "..." }
   
   POST /properties/1/external-calendars
   { "platform": "booking_com", "url": "..." }
   ```
3. Share your RentHub iCal URL back to Airbnb/Booking.com
4. Automatic sync every 6 hours keeps everything in sync

### Use Case 2: Block Maintenance Period
**Scenario:** Property needs maintenance for 2 weeks.

**Solution:**
```bash
POST /properties/1/calendar/bulk-block
{
  "start_date": "2026-01-10",
  "end_date": "2026-01-24",
  "reason": "Maintenance"
}
```

### Use Case 3: Seasonal Pricing
**Scenario:** Higher prices during summer.

**Solution:**
```bash
# Set summer pricing
POST /properties/1/calendar/bulk-pricing
{
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "price": 250.00
}

# Set holiday pricing
POST /properties/1/calendar/bulk-pricing
{
  "start_date": "2025-12-20",
  "end_date": "2026-01-05",
  "price": 300.00
}
```

### Use Case 4: Check Availability Before Booking
**Scenario:** Tenant wants to see which dates are available.

**Solution:**
```bash
GET /properties/1/calendar?start_date=2025-12-01&months=3
```

Returns daily breakdown:
```json
{
  "calendar": [
    {
      "date": "2025-12-01",
      "available": true,
      "blocked": false,
      "booked": false,
      "price": 100.00
    },
    {
      "date": "2025-12-25",
      "available": false,
      "blocked": true,
      "booked": false,
      "price": 100.00
    }
  ]
}
```

## 📖 API Endpoints

### Calendar Availability
- `GET /properties/{id}/calendar` - Get availability
- `GET /properties/{id}/calendar/pricing` - Get pricing
- `GET /properties/{id}/calendar/blocked-dates` - Get blocked dates

### Bulk Operations
- `POST /properties/{id}/calendar/bulk-block` - Block dates
- `POST /properties/{id}/calendar/bulk-unblock` - Unblock dates
- `POST /properties/{id}/calendar/bulk-pricing` - Set pricing
- `DELETE /properties/{id}/calendar/bulk-pricing` - Remove pricing

### External Calendars
- `GET /properties/{id}/external-calendars` - List calendars
- `POST /properties/{id}/external-calendars` - Add calendar
- `PUT /properties/{id}/external-calendars/{calId}` - Update calendar
- `DELETE /properties/{id}/external-calendars/{calId}` - Delete calendar
- `POST /properties/{id}/external-calendars/{calId}/sync` - Manual sync
- `GET /properties/{id}/external-calendars/{calId}/logs` - Sync logs

### iCal Export
- `GET /properties/{id}/ical` - Export iCal (public)
- `GET /properties/{id}/ical-url` - Get iCal URL

## 🔍 How to Get iCal URLs

### From Airbnb
1. Go to your listing
2. Click **Availability**
3. Click **Export calendar**
4. Copy the iCal URL

### From Booking.com
1. Log into Extranet
2. Go to **Calendar**
3. Click **Sync calendars**
4. Copy the export URL

### From VRBO
1. Go to property dashboard
2. Click **Calendar**
3. Click **Export**
4. Copy the iCal URL

### From Google Calendar
1. Open Google Calendar
2. Settings → Calendar name → Integrate calendar
3. Copy the **Secret address in iCal format**

## 🐛 Troubleshooting

### Sync Not Working
**Problem:** External calendar not syncing

**Solutions:**
1. Check URL is correct
2. Verify calendar is enabled: `sync_enabled: true`
3. Check sync logs: `GET /properties/1/external-calendars/1/logs`
4. Try manual sync: `POST /properties/1/external-calendars/1/sync`

### Dates Not Blocking
**Problem:** Blocked dates still showing as available

**Solution:**
1. Check if dates are in `blocked_dates` array
2. Verify date format is YYYY-MM-DD
3. Check if bookings override blocked dates

### iCal Export Empty
**Problem:** Downloaded .ics file has no events

**Solution:**
1. Make sure property has blocked dates or bookings
2. Verify property exists and is accessible
3. Check blocked_dates field is not null

## 📚 Documentation

- **Full API Guide:** See `CALENDAR_API_GUIDE.md`
- **Testing Guide:** See `TEST_CALENDAR_API.md`
- **Implementation Details:** See `TASK_2.3_COMPLETE.md`

## 🎯 Next Steps

1. ✅ Test basic calendar operations
2. ✅ Add external calendar
3. ✅ Test sync
4. ⏳ Build frontend calendar UI
5. ⏳ Add Filament admin widget
6. ⏳ Setup production cron

## 💡 Tips

1. **Sync Frequency:** Don't sync more than once per hour per calendar (API rate limits)
2. **Date Format:** Always use YYYY-MM-DD format
3. **Bulk Operations:** Use bulk endpoints instead of looping individual dates
4. **Caching:** Consider caching calendar data for popular properties
5. **Timezones:** All dates are stored in UTC

## ✅ Quick Test

Test if everything works:

```bash
# 1. Get calendar
curl "http://localhost/api/v1/properties/1/calendar?start_date=2025-11-01&months=1"

# 2. Block tomorrow
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-block" \
  -H "Authorization: Bearer {token}" \
  -d '{"start_date":"2025-11-03","end_date":"2025-11-03"}'

# 3. Verify it's blocked
curl "http://localhost/api/v1/properties/1/calendar/blocked-dates" \
  -H "Authorization: Bearer {token}"

# 4. Export calendar
curl "http://localhost/api/v1/properties/1/ical" -o test.ics

# 5. Run sync command
php artisan calendar:sync
```

If all commands work, you're ready to go! 🎉

## 🆘 Need Help?

- Check `CALENDAR_API_GUIDE.md` for detailed API docs
- Check `TEST_CALENDAR_API.md` for more test examples
- Check sync logs in database: `calendar_sync_logs` table
- Check Laravel logs: `storage/logs/laravel.log`

---

**Happy Calendar Management! 📅✨**
