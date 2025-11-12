# 🚀 Ghid Rapid: Adăugare Date de Test pe Forge

## Metoda 1: Manual via SSH (Recomandată)

```bash
# 1. Conectează-te la Forge via SSH
ssh forge@renthub-tbj7yxj7.on-forge.com

# 2. Navighează la directorul site-ului
cd renthub-tbj7yxj7.on-forge.com

# 3. Uploadează seederul (din local, nu din SSH)
# Pe mașina locală:
scp c:\laragon\www\RentHub\backend\database\seeders\TestPropertiesSeeder.php forge@renthub-tbj7yxj7.on-forge.com:renthub-tbj7yxj7.on-forge.com/database/seeders/

# 4. Rulează seederul (din SSH)
php artisan db:seed --class=TestPropertiesSeeder

# 5. Verifică
php artisan tinker
>>> App\Models\Property::count();
>>> App\Models\Property::first();
```

## Metoda 2: Via GitHub Deploy

```bash
# 1. Commit seederul
git add backend/database/seeders/TestPropertiesSeeder.php
git add backend/database/seeders/DatabaseSeeder.php
git commit -m "Add TestPropertiesSeeder with 5 sample properties"
git push origin master

# 2. Așteaptă auto-deploy pe Forge (sau trigger manual)

# 3. SSH și rulează seeder
ssh forge@renthub-tbj7yxj7.on-forge.com
cd renthub-tbj7yxj7.on-forge.com
php artisan db:seed --class=TestPropertiesSeeder
```

## Metoda 3: Via Forge UI

1. Mergi pe https://forge.laravel.com
2. Selectează site-ul `renthub-tbj7yxj7.on-forge.com`
3. Click "SSH" → deschide terminal
4. Rulează:
```bash
cd renthub-tbj7yxj7.on-forge.com
php artisan db:seed --class=TestPropertiesSeeder
```

## Verificare După Seed

```bash
# Test API endpoint
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

# Sau PowerShell
Invoke-RestMethod -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties" -Method GET | ConvertTo-Json -Depth 2
```

## Ce Date Se Vor Adăuga

✅ **5 Proprietăți de Test**:
1. Luxury Downtown Apartment - New York, NY ($250/night)
2. Cozy Suburban Family Home - Los Angeles, CA ($320/night)
3. Beachfront Luxury Villa - Miami, FL ($850/night)
4. Modern Downtown Studio - Chicago, IL ($150/night)
5. Penthouse with Skyline Views - New York, NY ($1200/night)

✅ **1 User de Test**:
- Email: owner@renthub.test
- Password: password123

## Troubleshooting

### Problema: "Class TestPropertiesSeeder not found"
**Soluție**:
```bash
composer dump-autoload
php artisan db:seed --class=TestPropertiesSeeder
```

### Problema: Seederul nu creează date
**Verifică**:
```bash
# Verifică dacă seederul există
ls -la database/seeders/TestPropertiesSeeder.php

# Verifică sintaxa PHP
php -l database/seeders/TestPropertiesSeeder.php

# Rulează cu verbose output
php artisan db:seed --class=TestPropertiesSeeder -vvv
```

### Problema: "SQLSTATE[23000]: Integrity constraint violation"
**Soluție**: Probabil proprietățile există deja
```bash
# Verifică în database
php artisan tinker
>>> App\Models\Property::where('title', 'Luxury Downtown Apartment')->exists();
```

## După Success

✅ Testează frontend-ul:
- https://rent-hub-beta.vercel.app/properties (ar trebui să arate 5 proprietăți)
- https://rent-hub-beta.vercel.app/properties/1 (ar trebui să funcționeze acum!)
- https://rent-hub-beta.vercel.app/dashboard/owner (ar trebui să arate 5 proprietăți)

