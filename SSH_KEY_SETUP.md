# 🔑 SSH KEY SETUP FOR FORGE

**Problem**: Cannot connect to Forge server (Permission denied)  
**Solution**: Add your SSH key to Forge

---

## 📋 QUICK STEPS:

### 1. **Get Your SSH Public Key**

Windows PowerShell:
```powershell
cat $env:USERPROFILE\.ssh\id_rsa.pub
```

If key doesn't exist:
```powershell
ssh-keygen -t rsa -b 4096 -C "renthub-forge"
# Press Enter 3 times (accept defaults)
cat $env:USERPROFILE\.ssh\id_rsa.pub
```

---

### 2. **Add Key to Forge**

1. Copy your public key (starts with `ssh-rsa`)
2. Go to: https://forge.laravel.com/servers/979577
3. Click **"SSH Keys"** tab
4. Click **"Add SSH Key"**
5. Paste your key
6. Click **"Add Key"**

---

### 3. **Test Connection**

```bash
ssh forge@178.128.135.24
```

Should connect without password!

---

## 🔄 ALTERNATIVE: Use Forge's Web Terminal

**Easier option** - No SSH key needed!

1. Go to: https://forge.laravel.com/servers/979577
2. Click **"Terminal"** tab or **"Console"** button
3. Opens web-based terminal
4. Run commands directly:

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
tail -100 storage/logs/laravel.log
```

---

## 📋 MANUAL FIX VIA WEB TERMINAL:

If using Forge web terminal, run these commands:

```bash
# Navigate to project
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Find artisan
find . -name "artisan" -type f 2>/dev/null | head -5

# Go to actual project root (usually current/)
cd current/backend  # or wherever artisan is

# Fix permissions
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear

# Setup database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Run migrations
php artisan migrate --force

# Cache config
php artisan config:cache

# Test API
curl http://localhost/api/v1/properties
```

---

## 🎯 RECOMMENDED PATH:

**USE FORGE WEB TERMINAL** (easiest!)

1. Login: https://forge.laravel.com
2. Go to your server
3. Click "Terminal" or "Console"
4. Copy-paste commands above
5. Done! ✅

---

## 📞 AFTER FIX:

Test API:
```bash
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties
```

Should return JSON (even if empty array)!

---

**Choose your path and let me know what you see!** 🚀
