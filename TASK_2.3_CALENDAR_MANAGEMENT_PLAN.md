# Task 2.3: Calendar Management - Implementation Plan

## Status: In Progress
**Started:** 2025-11-02

## Current State
✅ Basic calendar functionality exists:
- Block/unblock dates API endpoints
- Custom pricing per date
- Date range queries
- Bulk date selection (via array)

## To Implement

### 1. Enhanced Calendar APIs
- [ ] Get availability calendar for a property
- [ ] Get availability for date range with pricing
- [ ] Bulk operations with date ranges (not just arrays)
- [ ] Get blocked dates with reasons
- [ ] Get custom pricing calendar view

### 2. Calendar Sync Infrastructure
- [ ] Google Calendar Integration
  - OAuth2 setup
  - Sync blocked dates to Google Calendar
  - Import events from Google Calendar
  - Two-way sync capability
  
- [ ] iCal Format Support (pentru Airbnb, Booking.com)
  - Generate iCal feed (.ics) for property
  - Import from iCal URL
  - Periodic sync job for external calendars

### 3. External Platform Import
- [ ] iCal URL management
  - Add/remove external calendar URLs
  - Store platform source (Airbnb, Booking.com, etc.)
  - Manual sync trigger
  - Auto-sync with cron

### 4. Filament Admin Resources
- [ ] Calendar visual interface in property edit page
- [ ] Manage external calendar URLs
- [ ] View sync history
- [ ] Manual sync triggers

### 5. Database Structure
```sql
-- New table for external calendar sources
CREATE TABLE external_calendars (
    id BIGINT PRIMARY KEY,
    property_id BIGINT,
    platform VARCHAR(50), -- 'airbnb', 'booking_com', 'google', 'ical'
    url TEXT,
    name VARCHAR(255),
    last_synced_at TIMESTAMP,
    sync_enabled BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- New table for calendar sync logs
CREATE TABLE calendar_sync_logs (
    id BIGINT PRIMARY KEY,
    external_calendar_id BIGINT,
    status VARCHAR(50), -- 'success', 'failed'
    dates_added INT,
    dates_removed INT,
    error_message TEXT,
    synced_at TIMESTAMP
);
```

### 6. API Endpoints to Add
```
GET    /api/v1/properties/{property}/calendar              - Get availability calendar
GET    /api/v1/properties/{property}/calendar/pricing      - Get pricing calendar
POST   /api/v1/properties/{property}/calendar/bulk-block   - Block date range
POST   /api/v1/properties/{property}/calendar/bulk-unblock - Unblock date range
GET    /api/v1/properties/{property}/ical                  - Export iCal feed
POST   /api/v1/properties/{property}/external-calendars    - Add external calendar
GET    /api/v1/properties/{property}/external-calendars    - List external calendars
DELETE /api/v1/properties/{property}/external-calendars/{id} - Remove external calendar
POST   /api/v1/properties/{property}/external-calendars/{id}/sync - Manual sync
```

## Implementation Steps

### Phase 1: Enhanced Calendar APIs (1-2 hours)
1. Create CalendarController for dedicated calendar operations
2. Add calendar view endpoints
3. Add bulk range operations
4. Add tests

### Phase 2: iCal Support (2-3 hours)
1. Install iCal library (eluceo/ical or sabre/vobject)
2. Create iCal export service
3. Create iCal import service
4. Add external_calendars table
5. Add API endpoints for iCal management

### Phase 3: Google Calendar Integration (3-4 hours)
1. Setup Google OAuth2
2. Create Google Calendar service
3. Add sync methods
4. Add webhook for real-time updates (optional)

### Phase 4: Filament Admin UI (2-3 hours)
1. Add calendar widget to PropertyResource
2. Add ExternalCalendarResource
3. Add sync buttons and status indicators

### Phase 5: Automated Sync (1-2 hours)
1. Create sync command
2. Schedule cron job
3. Add error handling and notifications

## Total Estimated Time: 9-14 hours

## Dependencies
- `spatie/laravel-google-calendar` or `google/apiclient`
- `sabre/vobject` (for iCal parsing)
- Cron setup for periodic sync

## Files to Create/Modify

### New Files
- `app/Http/Controllers/Api/CalendarController.php`
- `app/Services/ICalService.php`
- `app/Services/GoogleCalendarService.php`
- `app/Models/ExternalCalendar.php`
- `app/Models/CalendarSyncLog.php`
- `app/Console/Commands/SyncExternalCalendars.php`
- `app/Filament/Resources/ExternalCalendarResource.php`
- `database/migrations/2025_11_02_XXXXXX_create_external_calendars_table.php`
- `database/migrations/2025_11_02_XXXXXX_create_calendar_sync_logs_table.php`

### Modified Files
- `routes/api.php`
- `app/Filament/Resources/PropertyResource.php`
- `config/services.php` (Google credentials)

## Testing Checklist
- [ ] Get availability calendar
- [ ] Bulk block/unblock date ranges
- [ ] Export iCal feed
- [ ] Import from iCal URL
- [ ] Google Calendar OAuth flow
- [ ] Sync from Google Calendar
- [ ] Automated sync command
- [ ] Filament UI for calendar management

## Notes
- iCal format is standard - works with Airbnb, Booking.com, VRBO
- Google Calendar requires OAuth2 consent
- Consider rate limits for external API calls
- Cache calendar data to reduce API calls
