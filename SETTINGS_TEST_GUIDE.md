# Test Script - Sincronizare Frontend-Backend Settings

## Quick Test Commands

### 1. Test API Endpoint (PowerShell)

```powershell
# Test public settings endpoint
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/settings/public" -Method GET | ConvertTo-Json -Depth 10

# Test specific setting
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/settings/site_name" -Method GET | ConvertTo-Json
```

### 2. Test cu cURL (Git Bash/WSL)

```bash
# Full public settings
curl http://localhost:8000/api/v1/settings/public | jq

# Specific setting
curl http://localhost:8000/api/v1/settings/site_name | jq
```

### 3. Verificare în Browser

Deschide:
- http://localhost:8000/api/v1/settings/public

### 4. Test din Frontend (JavaScript/Browser Console)

```javascript
// Fetch toate setările
fetch('http://localhost:8000/api/v1/settings/public')
  .then(response => response.json())
  .then(data => console.log(data));

// Test specific
fetch('http://localhost:8000/api/v1/settings/site_name')
  .then(response => response.json())
  .then(data => console.log(data));
```

## Expected Response Structure

### `/api/v1/settings/public`

```json
{
  "success": true,
  "data": {
    "site_name": "RentHub",
    "site_description": "Platformă de închirieri",
    "site_keywords": "închirieri, proprietăți, cazare",
    "items_per_page": 12,
    "api_url": "http://localhost:8000",
    "api_base_url": "http://localhost:8000/api/v1",
    "websocket_url": "http://localhost:6001",
    "reverb": {
      "enabled": true,
      "host": "localhost",
      "port": 8080,
      "scheme": "ws",
      "key": "renthub-key"
    },
    "features": {
      "registrations_enabled": true,
      "email_verification_required": true,
      "reviews_enabled": true,
      "messaging_enabled": true,
      "wishlist_enabled": true
    },
    "maintenance_mode": false,
    "social_login": {
      "google_enabled": false,
      "google_client_id": "",
      "facebook_enabled": false,
      "facebook_client_id": ""
    },
    "payment": {
      "stripe_enabled": false,
      "stripe_public_key": "",
      "currency": "RON",
      "currency_symbol": "RON"
    },
    "maps": {
      "mapbox_token": "",
      "google_maps_api_key": "",
      "default_center": {
        "lat": 44.4268,
        "lng": 26.1025
      }
    },
    "analytics": {
      "enabled": false,
      "google_analytics_id": "",
      "facebook_pixel_id": ""
    },
    "notifications": {
      "push_enabled": false,
      "pusher_beams_instance_id": ""
    },
    "company": {
      "name": "RentHub",
      "email": "info@renthub.ro",
      "phone": "+40 XXX XXX XXX",
      "support_email": "support@renthub.ro",
      "support_phone": "+40 XXX XXX XXX"
    },
    "seo": {
      "default_meta_image": "",
      "robots_enabled": true,
      "sitemap_enabled": true
    }
  }
}
```

## Admin Panel Tabs Checklist

### ✅ Tab 1: Frontend
- [ ] Site Name
- [ ] Site Description  
- [ ] Site Keywords
- [ ] Frontend URL
- [ ] Items Per Page
- [ ] Meta Image
- [ ] API URLs (api_url, api_base_url, websocket_url)
- [ ] Reverb Settings (host, port, scheme)
- [ ] Features (registrations, reviews, messaging, wishlist)
- [ ] Maintenance Mode
- [ ] Social Login (Google, Facebook)
- [ ] SEO (robots, sitemap)

### ✅ Tab 2: Companie
- [ ] Company Name
- [ ] Company Email
- [ ] Company Phone
- [ ] Company Address
- [ ] Support Email
- [ ] Support Phone

### ✅ Tab 3: Email
- [ ] Mail Driver
- [ ] SMTP Host
- [ ] SMTP Port
- [ ] SMTP Username
- [ ] SMTP Password
- [ ] Encryption
- [ ] From Address
- [ ] From Name

### ✅ Tab 4: Plăți
- [ ] Stripe Enabled
- [ ] Stripe Public Key
- [ ] Stripe Secret Key
- [ ] Currency
- [ ] Currency Symbol
- [ ] Commission Percentage

### ✅ Tab 5: SMS
- [ ] Twilio Enabled
- [ ] Twilio SID
- [ ] Twilio Auth Token
- [ ] Twilio Phone Number

### ✅ Tab 6: Hărți & Localizare
- [ ] Mapbox Token
- [ ] Google Maps API Key
- [ ] IPStack API Key
- [ ] Default Map Center (Lat/Lng)

### ✅ Tab 7: Analytics
- [ ] Analytics Enabled
- [ ] Google Analytics ID
- [ ] Facebook Pixel ID

### ✅ Tab 8: Notificări
- [ ] Email Notifications
- [ ] SMS Notifications
- [ ] Push Notifications
- [ ] Pusher Beams Instance ID

## Validation Steps

1. **Backend Check:**
```bash
php artisan tinker
>>> App\Models\Setting::count()
>>> App\Models\Setting::where('group', 'frontend')->get()
```

2. **Database Check:**
```sql
SELECT * FROM settings WHERE `group` = 'frontend';
SELECT * FROM settings WHERE `group` = 'payment';
```

3. **Config Check:**
```bash
php artisan config:show mail
php artisan config:show cors
```

4. **Route Check:**
```bash
php artisan route:list --path=api/v1/settings
```

## Common Issues & Solutions

### Issue: Settings not updating
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: CORS errors from frontend
- Check `frontend_url` in admin settings
- Verify it matches your Next.js dev server URL
- Clear Laravel config cache

### Issue: API returns empty data
- Verify settings table has data
- Check DynamicConfigServiceProvider is registered
- Check bootstrap/providers.php includes DynamicConfigServiceProvider

### Issue: Stripe/Maps not working
- Verify API keys are set in admin panel
- Check they're being returned in `/api/v1/settings/public`
- Verify frontend is using correct keys

## Testing Workflow

1. **Configurare în Admin:**
   - Accesați http://localhost:8000/admin/settings
   - Completați toate câmpurile necesare
   - Salvați

2. **Verificare Backend:**
   ```bash
   curl http://localhost:8000/api/v1/settings/public | jq
   ```

3. **Verificare Frontend:**
   - Deschideți aplicația Next.js
   - Verificați console pentru date settings
   - Verificați că valorile se afișează corect

4. **Test Modificare:**
   - Modificați o setare în admin (ex: site_name)
   - Salvați
   - Reload frontend
   - Verificați că noua valoare apare

## Success Indicators

✅ API endpoint returnează toate setările  
✅ Frontend primește date corect  
✅ Modificările în admin se reflectă instant în API  
✅ CORS funcționează între frontend și backend  
✅ Stripe/Maps API keys funcționează  
✅ Maintenance mode funcționează  
✅ Social login buttons apar/dispar corect  

---

**Quick Start Command:**

```bash
# Start backend
cd backend && php artisan serve --port=8000

# In alt terminal - test API
curl http://localhost:8000/api/v1/settings/public | jq

# Start frontend  
cd frontend && npm run dev

# Open browser
# http://localhost:3000
```
