# 🧪 Testing Guide - Multi-Language & Multi-Currency

## ✅ Prerequisites

1. Backend running: `http://localhost:8000`
2. Database migrated with seeders
3. API accessible

## 📋 Backend Testing

### 1. Verifică Migrațiile

```bash
cd C:\laragon\www\RentHub\backend
php artisan migrate:status
```

Ar trebui să vezi:
- ✅ `2025_11_02_204955_create_languages_table`
- ✅ `2025_11_02_205005_create_currencies_table`
- ✅ `2025_11_02_205006_create_exchange_rates_table`

### 2. Verifică Datele în Database

```bash
php artisan tinker
```

Apoi rulează:
```php
\App\Models\Language::count();  // Ar trebui să returneze 8
\App\Models\Currency::count();  // Ar trebui să returneze 5
\App\Models\ExchangeRate::count();  // Ar trebui să returneze 12

// Verifică limba default
\App\Models\Language::getDefault()->code;  // Ar trebui "en"

// Verifică valuta default
\App\Models\Currency::getDefault()->code;  // Ar trebui "RON"
```

### 3. Test API Endpoints

#### Test Languages

```bash
# Get all languages
curl http://localhost:8000/api/v1/languages

# Expected response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "en",
      "name": "English",
      "native_name": "English",
      "flag_emoji": "🇬🇧",
      "is_rtl": false,
      "is_active": true,
      "is_default": true
    },
    ...
  ]
}
```

```bash
# Get default language
curl http://localhost:8000/api/v1/languages/default

# Get specific language
curl http://localhost:8000/api/v1/languages/ro
```

#### Test Currencies

```bash
# Get all currencies
curl http://localhost:8000/api/v1/currencies

# Expected response:
{
  "success": true,
  "data": [
    {
      "id": 3,
      "code": "RON",
      "name": "Romanian Leu",
      "symbol": "lei",
      "symbol_position": "after",
      "decimal_places": 2,
      "is_default": true
    },
    ...
  ]
}
```

```bash
# Test currency conversion
curl -X POST http://localhost:8000/api/v1/currencies/convert \
  -H "Content-Type: application/json" \
  -d "{\"from\":\"USD\",\"to\":\"EUR\",\"amount\":100}"

# Expected response:
{
  "success": true,
  "data": {
    "from": {
      "currency": "USD",
      "amount": 100,
      "formatted": "$100.00"
    },
    "to": {
      "currency": "EUR",
      "amount": 92,
      "formatted": "92,00€"
    },
    "rate": 0.92
  }
}
```

### 4. Test Artisan Command

```bash
php artisan exchange-rates:update
```

Ar trebui să vezi:
```
Updating exchange rates...
✓ Exchange rates updated successfully!
```

Verifică în database:
```php
php artisan tinker

\App\Models\ExchangeRate::latest()->first()->source;  // Ar trebui "exchangerate-api.com"
\App\Models\ExchangeRate::latest()->first()->fetched_at;  // Timestamp recent
```

### 5. Test Scheduler

```bash
# Test manual
php artisan schedule:run

# Verifică log-urile
tail -f storage/logs/laravel.log
```

## 🎛️ Admin Panel Testing

### 1. Accesează Admin Panel

URL: `http://localhost:8000/admin`

### 2. Test Language Management

1. Go to **Languages** menu
2. Verifică că vezi 8 limbi
3. Încearcă să:
   - Editezi o limbă (schimbă sort_order)
   - Activezi o limbă inactivă (Italiano)
   - Dezactivezi o limbă activă
   - Adaugi o limbă nouă
   - Schimbi limba default

### 3. Test Currency Management

1. Go to **Currencies** menu
2. Verifică că vezi 5 valute
3. Încearcă să:
   - Editezi o valută (schimbă symbol)
   - Activezi CHF
   - Adaugi o valută nouă (JPY, CAD, etc.)
   - Schimbi valuta default
   - Modifici formatarea (separatori)

### 4. Test Exchange Rates

1. Go to **Exchange Rates** menu
2. Verifică că vezi rate de schimb
3. Verifică coloana "source" (ar trebui "seeder" sau "exchangerate-api.com")
4. Verifică "fetched_at" timestamp
5. Editează manual un rate

## 🔍 Common Issues & Solutions

### Issue 1: API returns 404

**Solution:**
```bash
php artisan route:list | grep languages
php artisan route:list | grep currencies
```

Verifică că rutele există. Dacă nu, run:
```bash
php artisan route:cache
php artisan config:cache
```

### Issue 2: Exchange rates not updating

**Solution:**
```bash
# Verifică API connection
curl https://api.exchangerate-api.com/v4/latest/RON

# Verifică logs
tail -f storage/logs/laravel.log

# Update manual
php artisan exchange-rates:update
```

### Issue 3: Seeders not working

**Solution:**
```bash
# Fresh migrate with seeders
php artisan migrate:fresh
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=CurrencySeeder
```

### Issue 4: CORS errors in frontend

**Solution:**
```bash
# În backend/.env
FRONTEND_URL=http://localhost:3000

# În backend/config/cors.php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
```

## 📊 Test Checklist

### Backend
- [ ] Migrations ran successfully
- [ ] Seeders created 8 languages
- [ ] Seeders created 5 currencies
- [ ] Seeders created 12 exchange rates
- [ ] API endpoint `/languages` works
- [ ] API endpoint `/currencies` works
- [ ] API endpoint `/currencies/convert` works
- [ ] Command `exchange-rates:update` works
- [ ] Scheduler is configured

### Admin Panel
- [ ] Languages resource accessible
- [ ] Can create/edit/delete languages
- [ ] Can activate/deactivate languages
- [ ] Can set default language
- [ ] Currencies resource accessible
- [ ] Can create/edit/delete currencies
- [ ] Exchange rates resource accessible
- [ ] Can view exchange rates history

### API Response Format
- [ ] All responses have `success` field
- [ ] All responses have `data` field
- [ ] Error responses have `message` field
- [ ] Proper HTTP status codes

## 🎯 Next Steps

După ce ai verificat că backend-ul funcționează:

1. **Frontend Integration**
   - Copiază exemple din `frontend-examples/`
   - Creează contexts
   - Adaugă switchers în header
   - Testează conversie prețuri

2. **Translation System**
   - Creează fișiere de traduceri
   - Implementează hook pentru translations
   - Adaugă traduceri în toate paginile

3. **Advanced Features**
   - Cache-uiește languages/currencies
   - Adaugă analytics pentru preferințe
   - Implementează auto-detect bazat pe IP
   - Adaugă A/B testing

## 📞 Support

Dacă întâmpini probleme, verifică:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console pentru erori CORS
3. Network tab pentru API responses
4. Database pentru date corecte

## ✨ Success Criteria

✅ Toate API endpoints returnează date corecte  
✅ Admin panel funcționează fără erori  
✅ Exchange rates se actualizează automat  
✅ Conversiile valutare sunt corecte  
✅ Formatul prețurilor este corect  
✅ RTL funcționează pentru Arabic/Hebrew  

Când toate criteriile sunt îndeplinite, poți trece la implementarea frontend-ului! 🎉
