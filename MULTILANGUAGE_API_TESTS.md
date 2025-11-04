# 🧪 Multi-language API Testing Guide

## 🚀 Quick Test with Postman/Curl

### Pre-requisites
```bash
# Start Laravel server
cd C:\laragon\www\RentHub\backend
php artisan serve

# Server runs at: http://127.0.0.1:8000
```

---

## 📋 Test Collection

### 1. GET /api/v1/languages
**Description:** Get all supported languages

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/languages" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "languages": [
    {
      "id": 1,
      "code": "en",
      "name": "English",
      "native_name": "English",
      "is_rtl": false,
      "is_active": true,
      "sort_order": 1
    },
    {
      "id": 2,
      "code": "ro",
      "name": "Romanian",
      "native_name": "Română",
      "is_rtl": false,
      "is_active": true,
      "sort_order": 2
    },
    ...
  ]
}
```

---

### 2. GET /api/v1/translations (All English Common)
**Description:** Get all common translations for English

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations?locale=en&group=common" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "locale": "en",
  "translations": {
    "welcome": "Welcome",
    "home": "Home",
    "search": "Search",
    "properties": "Properties",
    "bookings": "Bookings",
    "messages": "Messages",
    "profile": "Profile",
    "logout": "Logout",
    "login": "Login",
    "register": "Register",
    "save": "Save",
    "cancel": "Cancel",
    "delete": "Delete",
    "edit": "Edit",
    "view": "View"
  }
}
```

---

### 3. GET /api/v1/translations (Romanian Common)
**Description:** Get Romanian translations

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations?locale=ro&group=common" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "locale": "ro",
  "translations": {
    "welcome": "Bine ai venit",
    "home": "Acasă",
    "search": "Caută",
    "properties": "Proprietăți",
    "bookings": "Rezervări",
    "messages": "Mesaje",
    "profile": "Profil",
    "logout": "Deconectare",
    "login": "Autentificare",
    "register": "Înregistrare"
  }
}
```

---

### 4. GET /api/v1/translations (Spanish Common)
**Description:** Get Spanish translations

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations?locale=es&group=common" \
  -H "Accept: application/json"
```

---

### 5. GET /api/v1/translations (Properties Group)
**Description:** Get property-related translations

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations?locale=en&group=properties" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "locale": "en",
  "translations": {
    "title": "Property Title",
    "description": "Description",
    "price_per_night": "Price per night",
    "bedrooms": "Bedrooms",
    "bathrooms": "Bathrooms",
    "guests": "Guests",
    "amenities": "Amenities",
    "location": "Location",
    "availability": "Availability",
    "book_now": "Book Now"
  }
}
```

---

### 6. GET /api/v1/translations/{key}
**Description:** Get single translation by key

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations/welcome?locale=ro&group=common" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "key": "welcome",
  "value": "Bine ai venit"
}
```

---

### 7. GET /api/v1/detect-language
**Description:** Auto-detect language from Accept-Language header

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/detect-language" \
  -H "Accept-Language: ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7"
```

**Expected Response:**
```json
{
  "success": true,
  "detected_language": "ro"
}
```

**Test with different headers:**
```bash
# German
curl -X GET "http://127.0.0.1:8000/api/v1/detect-language" \
  -H "Accept-Language: de-DE,de;q=0.9"

# Spanish
curl -X GET "http://127.0.0.1:8000/api/v1/detect-language" \
  -H "Accept-Language: es-ES,es;q=0.9"

# Arabic
curl -X GET "http://127.0.0.1:8000/api/v1/detect-language" \
  -H "Accept-Language: ar-SA,ar;q=0.9"
```

---

### 8. GET /api/v1/translations/export
**Description:** Export translations as JSON

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations/export?locale=ro&group=common" \
  -H "Accept: application/json"
```

**Export all translations for a language:**
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/translations/export?locale=ro" \
  -H "Accept: application/json"
```

---

## 🔐 Admin Endpoints (Requires Authentication)

### 9. POST /api/v1/translations
**Description:** Create new translation

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/translations" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "locale": "ro",
    "group": "bookings",
    "key": "confirm_booking",
    "value": "Confirmă rezervarea"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Translation created successfully",
  "translation": {
    "id": 123,
    "locale": "ro",
    "group": "bookings",
    "key": "confirm_booking",
    "value": "Confirmă rezervarea",
    "created_at": "2025-11-02T20:00:00.000000Z",
    "updated_at": "2025-11-02T20:00:00.000000Z"
  }
}
```

---

### 10. PUT /api/v1/translations/{id}
**Description:** Update existing translation

```bash
curl -X PUT "http://127.0.0.1:8000/api/v1/translations/123" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "value": "Confirmă noua rezervare"
  }'
```

---

### 11. DELETE /api/v1/translations
**Description:** Delete translation

```bash
curl -X DELETE "http://127.0.0.1:8000/api/v1/translations" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "locale": "ro",
    "group": "bookings",
    "key": "confirm_booking"
  }'
```

---

### 12. POST /api/v1/translations/import
**Description:** Bulk import translations

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/translations/import" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "locale": "ro",
    "group": "notifications",
    "translations": {
      "new_message": "Mesaj nou",
      "new_booking": "Rezervare nouă",
      "payment_received": "Plată primită",
      "booking_confirmed": "Rezervare confirmată"
    }
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "4 translations imported successfully",
  "count": 4
}
```

---

## 📊 Postman Collection Import

### Create Collection JSON
Save as `RentHub_Multilanguage.postman_collection.json`:

```json
{
  "info": {
    "name": "RentHub Multi-language API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get All Languages",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{baseUrl}}/api/v1/languages",
          "host": ["{{baseUrl}}"],
          "path": ["api", "v1", "languages"]
        }
      }
    },
    {
      "name": "Get Translations (EN Common)",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{baseUrl}}/api/v1/translations?locale=en&group=common",
          "host": ["{{baseUrl}}"],
          "path": ["api", "v1", "translations"],
          "query": [
            {"key": "locale", "value": "en"},
            {"key": "group", "value": "common"}
          ]
        }
      }
    },
    {
      "name": "Detect Language",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept-Language",
            "value": "ro-RO,ro;q=0.9,en-US;q=0.8"
          }
        ],
        "url": {
          "raw": "{{baseUrl}}/api/v1/detect-language",
          "host": ["{{baseUrl}}"],
          "path": ["api", "v1", "detect-language"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://127.0.0.1:8000"
    }
  ]
}
```

---

## ✅ Test Checklist

### Public Endpoints
- [ ] GET /languages returns 7 languages
- [ ] GET /translations?locale=en returns English translations
- [ ] GET /translations?locale=ro returns Romanian translations
- [ ] GET /translations?locale=es returns Spanish translations
- [ ] GET /translations?locale=ar returns Arabic (RTL)
- [ ] GET /translations?locale=he returns Hebrew (RTL)
- [ ] GET /translations/{key} returns specific translation
- [ ] GET /detect-language detects from header
- [ ] GET /export exports JSON file

### Admin Endpoints (Auth Required)
- [ ] POST /translations creates new translation
- [ ] PUT /translations/{id} updates translation
- [ ] DELETE /translations deletes translation
- [ ] POST /import bulk imports translations

### Cache Tests
- [ ] First request slower (cache miss)
- [ ] Second request faster (cache hit)
- [ ] Cache clears after update
- [ ] Cache respects locale + group

---

## 🐛 Troubleshooting

### Issue: 404 Not Found
**Solution:**
```bash
# Check if server is running
php artisan serve

# Check routes
php artisan route:list | grep translation
```

### Issue: Empty translations
**Solution:**
```bash
# Re-run seeder
php artisan db:seed --class=DefaultTranslationsSeeder

# Check database
php artisan tinker
>>> \App\Models\Translation::count()
```

### Issue: Cache not clearing
**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
```

---

## 📝 Notes

- All public endpoints work without authentication
- Admin endpoints require valid Bearer token
- Translations are cached for 1 hour
- Cache auto-invalidates on update
- RTL languages (ar, he) have `is_rtl: true`

**Happy Testing! 🚀**
