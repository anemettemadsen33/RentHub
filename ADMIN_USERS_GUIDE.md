# 🔐 Admin Users - Quick Reference

## Default Admin Accounts

### 1. Main Admin (Already exists)
```
📧 Email:    admin@renthub.com
🔑 Password: Admin@123456
🎯 Role:     Administrator
```

### 2. Filament Admin (New)
```
📧 Email:    filament@renthub.com
🔑 Password: FilamentAdmin123
🎯 Role:     Administrator
```

---

## 🌐 Access Points

### Local Development
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **Filament Admin**: http://localhost:8000/admin

### Production
- **Frontend**: https://rent-hub-beta.vercel.app
- **Backend API**: https://renthub-tbj7yxj7.on-forge.com/api/v1
- **Filament Admin**: https://renthub-tbj7yxj7.on-forge.com/admin

---

## 🚀 Creating New Admin Users

### Method 1: Interactive (Recommended)
```bash
php artisan admin:create
```

Then follow the prompts.

### Method 2: Direct Command
```bash
php artisan admin:create [email] [password] [name]
```

**Example**:
```bash
php artisan admin:create john@renthub.com SecurePass123 "John Doe"
```

### Method 3: Run Seeder
```bash
php artisan db:seed --class=AdminSeeder
```

Creates default admin: `admin@renthub.com` / `Admin@123456`

---

## 🔧 On Forge Server

### Create Admin via SSH
```bash
ssh forge@renthub-tbj7yxj7.on-forge.com
cd ~/renthub-tbj7yxj7.on-forge.com
php artisan admin:create
```

### Quick One-Liner
```bash
ssh forge@renthub-tbj7yxj7.on-forge.com "cd renthub-tbj7yxj7.on-forge.com && php artisan admin:create filament@renthub.com FilamentAdmin123 'Filament Admin'"
```

---

## 📋 Update Existing User to Admin

```bash
# If user exists, the command will offer to update
php artisan admin:create existing@email.com

# Or force update
php artisan admin:create existing@email.com NewPassword123 --force
```

---

## 🧪 Test Admin Access

### Test Login API
```bash
curl -X POST https://renthub-tbj7yxj7.on-forge.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@renthub.com","password":"Admin@123456"}'
```

### PowerShell Test
```powershell
$body = @{
    email = "admin@renthub.com"
    password = "Admin@123456"
} | ConvertTo-Json

Invoke-RestMethod -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/login" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"
```

---

## 🔒 Security Best Practices

1. ✅ **Change default passwords** immediately after first login
2. ✅ **Use strong passwords** (min 12 characters)
3. ✅ **Enable 2FA** if available
4. ✅ **Don't share admin credentials**
5. ✅ **Rotate passwords** regularly
6. ✅ **Monitor admin access logs**

---

## 🐛 Troubleshooting

### Can't Login to Filament
1. Check user has `role = 'admin'`
2. Check email is verified (`email_verified_at` not null)
3. Clear cache: `php artisan cache:clear`
4. Check Filament config: `config/filament.php`

### Password Not Working
```bash
# Reset password for existing user
php artisan admin:create user@email.com NewPassword123
# Choose "yes" to update
```

### User Already Exists Error
```bash
# Update existing user
php artisan admin:create existing@email.com --force
```

---

## 📊 List All Admins

```bash
php artisan tinker
>>> User::where('role', 'admin')->get(['id', 'name', 'email']);
```

Or create a custom command:
```bash
php artisan make:command ListAdmins
```

---

**Last Updated**: 2025-11-12  
**Admin Count**: 2 (admin@renthub.com, filament@renthub.com)

