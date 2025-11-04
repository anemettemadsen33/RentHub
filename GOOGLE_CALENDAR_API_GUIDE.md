# 🗓️ Google Calendar API Integration Guide

## 📋 Quick Reference

Acest ghid conține toate detaliile necesare pentru a utiliza Google Calendar OAuth integration în RentHub.

## 🔧 Setup Inițial

### 1. Google Cloud Console Configuration

#### Step 1: Create Project
1. Mergi la [Google Cloud Console](https://console.cloud.google.com/)
2. Creează un proiect nou sau selectează unul existent
3. Notează Project ID-ul

#### Step 2: Enable APIs
1. În Cloud Console, mergi la **APIs & Services** > **Library**
2. Caută și activează:
   - **Google Calendar API**

#### Step 3: Create OAuth 2.0 Credentials
1. Mergi la **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **OAuth client ID**
3. Configurează:
   - **Application type**: Web application
   - **Name**: RentHub Calendar Integration
   - **Authorized redirect URIs**: 
     - Development: `http://localhost:8000/api/v1/google-calendar/callback`
     - Production: `https://your-domain.com/api/v1/google-calendar/callback`
4. Salvează **Client ID** și **Client Secret**

#### Step 4: Configure OAuth Consent Screen
1. Mergi la **APIs & Services** > **OAuth consent screen**
2. Completează informațiile aplicației:
   - App name: RentHub
   - User support email: your-email@domain.com
   - Developer contact: your-email@domain.com
3. Adaugă scopes:
   - `https://www.googleapis.com/auth/calendar`
   - `https://www.googleapis.com/auth/calendar.events`
4. Adaugă test users (pentru development)

### 2. Laravel Configuration

#### Update .env
```env
# Google Calendar OAuth
GOOGLE_CALENDAR_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=your-client-secret-here
GOOGLE_CALENDAR_REDIRECT_URI=http://localhost:8000/api/v1/google-calendar/callback

# App URL (important pentru webhooks în production)
APP_URL=https://your-domain.com
```

#### Run Migrations
```bash
php artisan migrate
```

#### Setup Cron Job (Production)
```bash
# Add to crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📡 API Endpoints

### Base URL
```
Development: http://localhost:8000/api/v1
Production: https://your-domain.com/api/v1
```

### Authentication
Toate endpoint-urile (exceptând webhook-ul) necesită authentication via Sanctum token:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## 🔐 1. Get Authorization URL

Obține URL-ul pentru a autoriza aplicația să acceseze Google Calendar.

### Request
```http
GET /google-calendar/authorize?property_id=1
```

### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| property_id | integer | No | ID-ul proprietății pentru care se conectează calendarul |

### Response
```json
{
  "authorization_url": "https://accounts.google.com/o/oauth2/auth?client_id=..."
}
```

### cURL Example
```bash
curl -X GET "http://localhost:8000/api/v1/google-calendar/authorize?property_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Frontend Integration
```javascript
// Step 1: Get authorization URL
const getAuthUrl = async (propertyId = null) => {
  const url = propertyId 
    ? `/api/v1/google-calendar/authorize?property_id=${propertyId}`
    : '/api/v1/google-calendar/authorize';
    
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.authorization_url;
};

// Step 2: Redirect user
const connectGoogleCalendar = async (propertyId) => {
  const authUrl = await getAuthUrl(propertyId);
  window.location.href = authUrl;
};
```

---

## ✅ 2. Handle OAuth Callback

Acest endpoint este apelat automat de Google după ce utilizatorul autorizează aplicația.

### Request
```http
POST /google-calendar/callback
```

### Body Parameters
```json
{
  "code": "4/0AY0e-g7X...",
  "state": "base64_encoded_state"
}
```

### Response
```json
{
  "message": "Google Calendar connected successfully",
  "data": {
    "id": 1,
    "calendar_id": "primary",
    "calendar_name": "John's Calendar",
    "sync_enabled": true
  }
}
```

### Frontend Integration
```javascript
// În pagina de callback (e.g., /google-callback)
const handleGoogleCallback = async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const code = urlParams.get('code');
  const state = urlParams.get('state');
  
  if (!code || !state) {
    console.error('Missing OAuth parameters');
    return;
  }
  
  try {
    const response = await fetch('/api/v1/google-calendar/callback', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ code, state })
    });
    
    const result = await response.json();
    console.log('Connected:', result);
    
    // Redirect to dashboard
    window.location.href = '/dashboard';
  } catch (error) {
    console.error('Connection failed:', error);
  }
};

// Call on page load
handleGoogleCallback();
```

---

## 📋 3. List Connected Calendars

Listează toate calendarele Google conectate pentru utilizatorul curent.

### Request
```http
GET /google-calendar/
```

### Response
```json
{
  "data": [
    {
      "id": 1,
      "calendar_id": "primary",
      "calendar_name": "John's Calendar",
      "property": {
        "id": 1,
        "title": "Luxury Apartment Downtown"
      },
      "sync_enabled": true,
      "last_sync_at": "2025-11-02T18:30:00.000000Z",
      "webhook_expires_at": "2025-11-09T18:30:00.000000Z",
      "has_errors": false
    }
  ]
}
```

### cURL Example
```bash
curl -X GET "http://localhost:8000/api/v1/google-calendar/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Frontend Integration
```javascript
const getConnectedCalendars = async () => {
  const response = await fetch('/api/v1/google-calendar/', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const { data } = await response.json();
  return data;
};

// Usage
const calendars = await getConnectedCalendars();
console.log('Connected calendars:', calendars);
```

---

## 🔍 4. Get Calendar Details

Obține detaliile unui calendar conectat specific.

### Request
```http
GET /google-calendar/{id}
```

### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID-ul token-ului Google Calendar |

### Response
```json
{
  "data": {
    "id": 1,
    "calendar_id": "primary",
    "calendar_name": "John's Calendar",
    "property": {
      "id": 1,
      "title": "Luxury Apartment Downtown"
    },
    "sync_enabled": true,
    "last_sync_at": "2025-11-02T18:30:00.000000Z",
    "webhook_expires_at": "2025-11-09T18:30:00.000000Z",
    "sync_errors": []
  }
}
```

### cURL Example
```bash
curl -X GET "http://localhost:8000/api/v1/google-calendar/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔄 5. Toggle Sync

Activează sau dezactivează sincronizarea pentru un calendar.

### Request
```http
PATCH /google-calendar/{id}/toggle-sync
```

### Response
```json
{
  "message": "Sync enabled",
  "data": {
    "sync_enabled": true
  }
}
```

### cURL Example
```bash
curl -X PATCH "http://localhost:8000/api/v1/google-calendar/1/toggle-sync" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Frontend Integration
```javascript
const toggleSync = async (calendarId) => {
  const response = await fetch(`/api/v1/google-calendar/${calendarId}/toggle-sync`, {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const result = await response.json();
  console.log(result.message);
  return result.data.sync_enabled;
};
```

---

## 📥 6. Import Events

Importă evenimente din Google Calendar ca blocked dates.

### Request
```http
POST /google-calendar/{id}/import
```

### Response
```json
{
  "message": "Events imported successfully",
  "data": {
    "imported_count": 5
  }
}
```

### cURL Example
```bash
curl -X POST "http://localhost:8000/api/v1/google-calendar/1/import" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Frontend Integration
```javascript
const importEvents = async (calendarId) => {
  const response = await fetch(`/api/v1/google-calendar/${calendarId}/import`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const result = await response.json();
  console.log(`Imported ${result.data.imported_count} events`);
  return result.data.imported_count;
};
```

---

## 🔄 7. Refresh Webhook

Reînnoiește webhook-ul pentru un calendar (util când este pe cale să expire).

### Request
```http
POST /google-calendar/{id}/refresh-webhook
```

### Response
```json
{
  "message": "Webhook refreshed successfully",
  "data": {
    "webhook_expires_at": "2025-11-09T18:30:00.000000Z"
  }
}
```

### cURL Example
```bash
curl -X POST "http://localhost:8000/api/v1/google-calendar/1/refresh-webhook" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🗑️ 8. Disconnect Calendar

Deconectează un calendar Google și șterge toate datele asociate.

### Request
```http
DELETE /google-calendar/{id}
```

### Response
```json
{
  "message": "Google Calendar disconnected successfully"
}
```

### cURL Example
```bash
curl -X DELETE "http://localhost:8000/api/v1/google-calendar/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Frontend Integration
```javascript
const disconnectCalendar = async (calendarId) => {
  if (!confirm('Are you sure you want to disconnect this calendar?')) {
    return;
  }
  
  const response = await fetch(`/api/v1/google-calendar/${calendarId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const result = await response.json();
  console.log(result.message);
};
```

---

## 🔔 9. Webhook Endpoint

Endpoint pentru notificările push de la Google Calendar (nu necesită autentificare).

### Request
```http
POST /google-calendar/webhook
```

### Headers
```
X-Goog-Channel-ID: unique-channel-id
X-Goog-Resource-ID: resource-id
X-Goog-Resource-State: exists | sync
```

### Response
```json
{
  "message": "Webhook processed"
}
```

**Note**: Acest endpoint este apelat automat de Google când apar modificări în calendar.

---

## 🔄 Automatic Sync Behavior

### When Creating a Booking

```php
// În cod - automatic prin BookingObserver
$booking = Booking::create([
    'property_id' => 1,
    'check_in' => '2025-11-10',
    'check_out' => '2025-11-15',
    // ... other fields
]);

// Sistemul automat:
// 1. Verifică dacă există Google Calendar tokens pentru property
// 2. Creează eveniment în Google Calendar
// 3. Salvează google_event_id în booking
```

### When Creating Blocked Dates

```php
// În cod - automatic prin BlockedDateObserver
$blockedDate = BlockedDate::create([
    'property_id' => 1,
    'start_date' => '2025-12-20',
    'end_date' => '2025-12-27',
    'reason' => 'Maintenance'
]);

// Sistemul automat sincronizează cu Google Calendar
```

---

## 🧪 Testing Flow

### Complete Integration Test

```javascript
// 1. Connect Google Calendar
const authUrl = await getAuthUrl(propertyId);
window.location.href = authUrl;

// 2. After callback, list calendars
const calendars = await getConnectedCalendars();
console.log('Connected:', calendars);

// 3. Import existing events
const imported = await importEvents(calendars[0].id);
console.log(`Imported ${imported} events`);

// 4. Create a booking (will auto-sync)
const booking = await createBooking({
  property_id: propertyId,
  check_in: '2025-11-20',
  check_out: '2025-11-25',
  // ... other fields
});

// 5. Check Google Calendar - booking should appear

// 6. Toggle sync off
await toggleSync(calendars[0].id);

// 7. Disconnect when done
await disconnectCalendar(calendars[0].id);
```

---

## ⚠️ Error Handling

### Common Errors

#### 1. Invalid Credentials
```json
{
  "message": "Failed to generate authorization URL",
  "error": "Invalid client credentials"
}
```
**Solution**: Verifică GOOGLE_CALENDAR_CLIENT_ID și CLIENT_SECRET în .env

#### 2. Token Expired
```json
{
  "message": "Failed to sync booking to Google Calendar",
  "error": "Token has been expired or revoked"
}
```
**Solution**: Tokenul se refresh-uiește automat. Dacă persistă, reconectează calendarul.

#### 3. Webhook Expired
```json
{
  "message": "Webhook expired"
}
```
**Solution**: Rulează `php artisan google-calendar:renew-webhooks` sau așteaptă task-ul scheduled.

#### 4. Rate Limit
```json
{
  "message": "Rate limit exceeded",
  "error": "Quota exceeded for quota metric 'Queries' and limit 'Queries per day'"
}
```
**Solution**: Așteaptă sau request quota increase în Google Cloud Console.

---

## 📊 Monitoring

### Check Sync Status

```bash
# View logs
tail -f storage/logs/laravel.log | grep "Google Calendar"

# List all connected calendars
php artisan tinker
>>> App\Models\GoogleCalendarToken::with('property', 'user')->get();

# Check webhooks
>>> App\Models\GoogleCalendarToken::whereNotNull('webhook_id')->get();
```

### Scheduled Tasks

```bash
# List scheduled tasks
php artisan schedule:list

# Run webhook renewal manually
php artisan google-calendar:renew-webhooks

# Test schedule
php artisan schedule:run
```

---

## 🚀 Production Checklist

- [ ] Google Calendar API enabled în Cloud Console
- [ ] OAuth credentials created și configurate
- [ ] Redirect URI adăugat în Google Console
- [ ] `.env` actualizat cu credentials
- [ ] Migrations rulate
- [ ] Cron job configurat pentru scheduled tasks
- [ ] HTTPS enabled (required pentru webhooks)
- [ ] Webhook URL public și accesibil
- [ ] Test OAuth flow completat
- [ ] Test import/sync completat
- [ ] Monitoring setup (logs, errors)

---

## 📚 Additional Resources

- [Google Calendar API v3 Reference](https://developers.google.com/calendar/api/v3/reference)
- [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)
- [Push Notifications](https://developers.google.com/calendar/api/guides/push)
- [API Quotas](https://developers.google.com/calendar/api/guides/quota)

---

**Last Updated**: 2025-11-02
