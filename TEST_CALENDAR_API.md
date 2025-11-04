# Calendar API - Quick Test Guide

## Prerequisites
1. Start Laravel server: `php artisan serve`
2. Have a valid auth token
3. Have at least one property created

## Quick Tests

### 1. Get Calendar Availability
```bash
curl "http://localhost/api/v1/properties/1/calendar?start_date=2025-11-01&end_date=2025-11-30"
```

**Expected:** JSON with calendar array showing 30 days of availability

### 2. Block Date Range
```bash
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-block" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-24",
    "end_date": "2025-12-26"
  }'
```

**Expected:** 
```json
{
  "success": true,
  "message": "3 dates blocked successfully"
}
```

### 3. Set Custom Pricing
```bash
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-pricing" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-20",
    "end_date": "2025-12-25",
    "price": 150.00
  }'
```

**Expected:**
```json
{
  "success": true,
  "message": "Custom pricing set for 6 dates"
}
```

### 4. Get Blocked Dates
```bash
curl "http://localhost/api/v1/properties/1/calendar/blocked-dates" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:**
```json
{
  "success": true,
  "data": {
    "blocked_dates": ["2025-12-24", "2025-12-25", "2025-12-26"],
    "count": 3
  }
}
```

### 5. Export iCal
```bash
curl "http://localhost/api/v1/properties/1/ical" -o property-calendar.ics
```

**Expected:** Downloads .ics file with calendar events

### 6. Get iCal URL
```bash
curl "http://localhost/api/v1/properties/1/ical-url" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:**
```json
{
  "success": true,
  "data": {
    "ical_url": "http://localhost/api/v1/properties/1/ical",
    "instructions": "Copy this URL..."
  }
}
```

### 7. Add External Calendar
```bash
curl -X POST "http://localhost/api/v1/properties/1/external-calendars" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "airbnb",
    "url": "https://www.airbnb.com/calendar/ical/123456.ics?s=abc",
    "name": "Test Airbnb Calendar"
  }'
```

**Expected:**
```json
{
  "success": true,
  "message": "External calendar added successfully",
  "data": {
    "id": 1,
    "platform": "airbnb",
    "name": "Test Airbnb Calendar"
  }
}
```

### 8. List External Calendars
```bash
curl "http://localhost/api/v1/properties/1/external-calendars" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:** Array of external calendars

### 9. Manual Sync
```bash
curl -X POST "http://localhost/api/v1/properties/1/external-calendars/1/sync" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:**
```json
{
  "success": true,
  "message": "Calendar synced successfully",
  "data": {
    "sync_result": {
      "dates_added": 5,
      "total_events": 10
    }
  }
}
```

### 10. Get Sync Logs
```bash
curl "http://localhost/api/v1/properties/1/external-calendars/1/logs" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:** Paginated list of sync logs

### 11. Unblock Dates
```bash
curl -X POST "http://localhost/api/v1/properties/1/calendar/bulk-unblock" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-24",
    "end_date": "2025-12-26"
  }'
```

**Expected:**
```json
{
  "success": true,
  "message": "3 dates unblocked successfully"
}
```

### 12. Remove Custom Pricing
```bash
curl -X DELETE "http://localhost/api/v1/properties/1/calendar/bulk-pricing" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-12-20",
    "end_date": "2025-12-25"
  }'
```

**Expected:**
```json
{
  "success": true,
  "message": "Custom pricing removed for 6 dates"
}
```

## Artisan Commands

### Sync All Calendars
```bash
php artisan calendar:sync
```

### Sync Specific Property
```bash
php artisan calendar:sync --property=1
```

### Force Sync
```bash
php artisan calendar:sync --force
```

### List Calendar Routes
```bash
php artisan route:list --path=calendar
```

## Testing Workflow

### Complete Test Scenario

1. **Setup Property**
   - Create property via API
   - Note the property ID

2. **Block Dates**
   - Block Christmas week (Dec 24-31)
   - Verify in calendar endpoint

3. **Set Pricing**
   - Set custom pricing for summer (Jun-Aug)
   - Verify in pricing calendar endpoint

4. **Export Calendar**
   - Get iCal URL
   - Download .ics file
   - Verify it opens in calendar app

5. **Add External Calendar**
   - Add test Airbnb calendar
   - Trigger manual sync
   - Check sync logs

6. **Verify Availability**
   - Query calendar for next 3 months
   - Verify blocked dates show as unavailable
   - Verify custom prices are applied

7. **Cleanup**
   - Unblock dates
   - Remove custom pricing
   - Delete external calendar

## Expected Results

All endpoints should return:
- ✅ Status 200 for successful operations
- ✅ Status 201 for create operations
- ✅ Status 403 for unauthorized access
- ✅ Status 422 for validation errors
- ✅ Consistent JSON structure with "success" field

## Common Issues

### 401 Unauthorized
**Problem:** Missing or invalid token  
**Solution:** Get fresh token from /api/v1/login

### 403 Forbidden
**Problem:** Property doesn't belong to user  
**Solution:** Use your own property or admin account

### 422 Validation Error
**Problem:** Invalid date format or range  
**Solution:** Use YYYY-MM-DD format, max 365 days

### 500 Sync Failed
**Problem:** External calendar URL unreachable  
**Solution:** Verify URL is correct and accessible

## Success Indicators

✅ All 15 routes return valid responses  
✅ Dates are blocked/unblocked correctly  
✅ Custom pricing is saved and retrieved  
✅ iCal export contains valid format  
✅ External calendar sync works  
✅ Sync logs are created  
✅ Command runs without errors

## Next Steps

After verifying all tests pass:
1. Create frontend components
2. Add Filament admin UI
3. Setup production cron job
4. Monitor sync performance
5. Add Google Calendar OAuth
