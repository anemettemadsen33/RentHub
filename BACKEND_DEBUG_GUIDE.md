# 🔍 BACKEND DEBUG COMMANDS

**Status**: Connected to server ✅  
**Issue**: API returns 500 Server Error  
**Location**: `/home/forge/renthub-tbj7yxj7.on-forge.com/current/backend`

---

## 📋 RUN THESE COMMANDS IN SSH:

### 1. **Check Laravel Logs**
```bash
tail -100 storage/logs/laravel.log
```

Look for recent errors with timestamps.

---

### 2. **Check .env File**
```bash
cat .env | grep -E "APP_|DB_"
```

Verify:
- `APP_KEY` is set
- `DB_CONNECTION` is configured
- `APP_DEBUG=false` or `true`

---

### 3. **Test Database Connection**
```bash
php artisan migrate:status
```

Should show list of migrations, not errors.

---

### 4. **Check if Database File Exists**
```bash
ls -la database/database.sqlite
```

If doesn't exist:
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate --force
```

---

### 5. **Clear All Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

---

### 6. **Generate APP_KEY (if missing)**
```bash
php artisan key:generate --force
```

---

### 7. **Fix Permissions**
```bash
chmod -R 775 storage bootstrap/cache
chown -R forge:www-data storage bootstrap/cache
```

---

### 8. **Cache Config**
```bash
php artisan config:cache
php artisan route:cache
```

---

### 9. **Test Routes**
```bash
php artisan route:list | grep properties
```

Should show API routes.

---

### 10. **Test API Again**
```bash
curl http://localhost/api/v1/properties
```

---

## 🎯 PRIORITY ORDER:

**START WITH #1** (check logs) - this will tell us exactly what's wrong!

Then fix based on error message.

---

## 📞 SEND ME:

1. **Output from tail -100 storage/logs/laravel.log**
2. **Output from cat .env | grep -E "APP_|DB_"**

These will tell me exactly what's wrong!

---

**RUN THESE NOW IN YOUR SSH SESSION!** 🚀
