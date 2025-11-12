# 🚀 Forge SSH Commands - Quick Reference

## You are currently connected to Forge SSH! ✅

### ⚠️ ERRORS YOU SAW:
```bash
# ❌ WRONG: pwsh quick-deploy-test-data.ps1
# That's a PowerShell script - doesn't work in Linux bash!

# ❌ WRONG: hp artisan db:seed
# Typo - should be "php" not "hp"
```

---

## ✅ CORRECT COMMANDS:

### Option 1: Single Line (Recommended)
Copy and paste this **ONE LINE**:

```bash
cd ~/renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=TestPropertiesSeeder
```

### Option 2: Step by Step

```bash
# 1. Navigate to site directory
cd ~/renthub-tbj7yxj7.on-forge.com

# 2. Run seeder
php artisan db:seed --class=TestPropertiesSeeder

# 3. Check it worked
php artisan tinker --execute="echo App\Models\Property::count();"
```

---

## 🎯 EXPECTED OUTPUT:

```
   INFO  Seeding database.  

✅ Created 5 test properties
📧 Test owner email: owner@renthub.test
🔑 Test owner password: password123
```

---

## 🔍 AFTER RUNNING - VERIFY:

```bash
# Check if properties exist (in SSH)
curl -s http://localhost/api/v1/properties | grep -o "title" | wc -l
# Should output: 5

# Or just check the response
curl http://localhost/api/v1/properties
```

---

## 🌐 TEST FROM LOCAL MACHINE:

After exiting SSH (`exit`), run this from PowerShell:

```powershell
Invoke-RestMethod -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
```

Then test these pages:
- https://rent-hub-beta.vercel.app/properties
- https://rent-hub-beta.vercel.app/properties/1
- https://rent-hub-beta.vercel.app/dashboard/owner

---

## 🐛 TROUBLESHOOTING:

### If you see "Class not found":
```bash
cd ~/renthub-tbj7yxj7.on-forge.com
composer dump-autoload
php artisan db:seed --class=TestPropertiesSeeder
```

### If you see "Permission denied":
```bash
cd ~/renthub-tbj7yxj7.on-forge.com
ls -la database/seeders/TestPropertiesSeeder.php
# File should exist - if not, run deployment first
```

### Check current directory:
```bash
pwd
# Should show: /home/forge/renthub-tbj7yxj7.on-forge.com
```

---

## 📋 QUICK COPY/PASTE (Just this one line):

```bash
cd ~/renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=TestPropertiesSeeder && echo "✅ Done! Properties seeded."
```

**That's it! Just copy the line above and paste in your SSH terminal.**

