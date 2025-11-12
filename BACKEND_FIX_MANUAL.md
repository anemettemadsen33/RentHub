# 🚀 BACKEND FIX - MANUAL COMMANDS

**SSH Key**: ✅ Added to Forge  
**Connection**: Should work now!

---

## 📋 RUN THESE COMMANDS IN ORDER:

### 1. **Test SSH Connection**
```bash
ssh forge@178.128.135.24
```
Should connect without password! ✅

---

### 2. **Navigate to Project**
```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
pwd
ls -la
```

---

### 3. **Find Laravel Root**
```bash
# Search for artisan
find . -name "artisan" -type f 2>/dev/null | head -5

# Usually it's in one of these:
# - ./artisan
# - ./current/artisan  
# - ./current/backend/artisan
```

---

### 4. **Go to Laravel Root** 
(Based on where you found artisan)
```bash
# Example - adjust based on step 3:
cd current/backend

# Verify
ls -la artisan
```

---

### 5. **Check .env File**
```bash
cat .env | head -20

# Look for:
# - APP_KEY=base64:...
# - DB_CONNECTION=sqlite
# - APP_ENV=production
```

---

### 6. **Fix Permissions**
```bash
chmod -R 775 storage bootstrap/cache
chown -R forge:www-data storage bootstrap/cache
```

---

### 7. **Clear Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### 8. **Setup Database**
```bash
# Create SQLite database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Verify
ls -la database/database.sqlite
```

---

### 9. **Run Migrations**
```bash
php artisan migrate:status
php artisan migrate --force
```

If you see errors, send me the output!

---

### 10. **Cache Config**
```bash
php artisan config:cache
php artisan route:cache
```

---

### 11. **Test API Locally**
```bash
curl http://localhost/api/v1/properties
```

Should return JSON (even if empty array)!

---

### 12. **Check Logs**
```bash
tail -50 storage/logs/laravel.log
```

Look for any errors.

---

## 🎯 EXPECTED RESULTS:

After all steps:
- ✅ No permission errors
- ✅ Database exists
- ✅ Migrations ran
- ✅ API returns JSON
- ✅ No errors in logs

---

## 🧪 TEST FROM YOUR PC:

Exit SSH and run:
```powershell
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties
```

Should see JSON response!

---

## 📞 REPORT BACK:

Send me:
1. **Output from step 3** (where is artisan?)
2. **Output from step 11** (curl localhost test)
3. **Any errors from step 12** (logs)

---

**START NOW! SSH to the server and run the commands!** 🚀

```bash
ssh forge@178.128.135.24
```
