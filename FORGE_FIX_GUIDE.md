# 🔧 RentHub Forge - Ghid Complet de Reparare

## ❌ Probleme Identificate

1. **API returnează 500 Server Error** pentru toate endpoint-urile
2. **Health check funcționează** - serverul rulează
3. **Răspunsurile sunt HTML** în loc de JSON - eroare critică Laravel

## 🎯 Soluții (Executați în ordine pe Forge SSH)

### 1. Conectați-vă la Forge SSH

```bash
ssh forge@renthub-tbj7yxj7.on-forge.com
cd /home/forge/renthub-tbj7yxj7.on-forge.com
```

### 2. Verificați Logs-urile Laravel

```bash
tail -100 storage/logs/laravel.log
```

**Ce să căutați:**
- Erori de database connection
- Missing APP_KEY
- Redis connection errors
- Missing dependencies

### 3. Verificați .env Variables

```bash
cat .env | grep -E '(APP_KEY|DB_|REDIS_)'
```

**IMPORTANT:** Verificați:
- `APP_KEY` - trebuie să existe și să înceapă cu `base64:`
- `DB_DATABASE=forge` (sau numele bazei de date MySQL)
- `DB_USERNAME=forge`
- `DB_PASSWORD` - trebuie să aibă parola MySQL
- `DB_HOST=127.0.0.1`
- `DB_CONNECTION=mysql`

### 4. Testați Conexiunea la Database

```bash
php artisan db:show
```

Dacă dă eroare:
```bash
# Verificați credențialele MySQL în Forge Dashboard
# Actualizați .env cu credențialele corecte
```

### 5. Clear All Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 6. Re-cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Verificați Status Migrații

```bash
php artisan migrate:status
```

Dacă nu sunt rulate:
```bash
php artisan migrate --force
```

### 8. Seed Database cu Date Test

```bash
# Dacă tabela properties este goală
php artisan db:seed --class=PropertySeeder --force

# SAU creați un admin user
php artisan db:seed --class=AdminUserSeeder --force
```

### 9. Testați API Local

```bash
# Test direct pe server
curl http://localhost/api/health
curl http://localhost/api/v1/properties
```

### 10. Verificați PHP-FPM și Nginx

```bash
# Restart PHP-FPM
sudo service php8.3-fpm restart

# Restart Nginx
sudo service nginx restart

# Check PHP errors
sudo tail -50 /var/log/php8.3-fpm.log
sudo tail -50 /var/log/nginx/error.log
```

### 11. Verificați Permissions

```bash
# Fix storage permissions
sudo chown -R forge:forge storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 12. Test Final

```bash
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties
```

---

## 🔍 Diagnostic Rapid

### Dacă APP_KEY lipsește:

```bash
php artisan key:generate --show
# Copiați output-ul și adăugați în .env:
# APP_KEY=base64:XXXXXXXXXXXXX
```

### Dacă Database connection failed:

```bash
# În Forge Dashboard → Database
# Notați:
# - Database Name
# - Database User
# - Database Password

# Actualizați .env:
vim .env
# Modificați:
DB_DATABASE=nume_db
DB_USERNAME=user_db  
DB_PASSWORD=parola_db
```

### Dacă Redis connection failed:

```bash
# Verificați Redis
redis-cli ping

# Dacă nu funcționează, în .env:
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

---

## ✅ Verificare Finală

După toate fix-urile, testați:

```bash
# 1. Test health
curl https://renthub-tbj7yxj7.on-forge.com/api/health

# 2. Test properties
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

# 3. Test settings
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/settings/public
```

---

## 📋 Checklist

- [ ] Logs verificate (storage/logs/laravel.log)
- [ ] .env corect configurat (APP_KEY, DB_*, REDIS_*)
- [ ] Database connection OK (php artisan db:show)
- [ ] Cache cleared (config, route, view)
- [ ] Migrații rulate (php artisan migrate:status)
- [ ] Permissions OK (storage 775)
- [ ] PHP-FPM & Nginx restarted
- [ ] API funcționează (curl test)

---

## 🆘 Dacă tot nu funcționează

Trimiteți output-ul acestor comenzi:

```bash
# Environment
cat .env | grep -v PASSWORD | grep -v SECRET

# Last 50 errors
tail -50 storage/logs/laravel.log

# PHP version
php -v

# Database test
php artisan db:show

# Route list
php artisan route:list | grep api/v1/properties
```
