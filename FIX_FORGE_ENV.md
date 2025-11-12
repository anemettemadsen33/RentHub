# Fix Forge .env - Run these commands in SSH

```bash
ssh forge@178.128.135.24
cd renthub-tbj7yxj7.on-forge.com

# Backup .env
cp .env .env.backup.$(date +%Y%m%d)

# Edit .env to set SANCTUM_STATEFUL_DOMAINS
# Option 1: Use sed to replace/add
sed -i '/^SANCTUM_STATEFUL_DOMAINS=/d' .env
echo "SANCTUM_STATEFUL_DOMAINS=rent-hub-beta.vercel.app,localhost:3000,localhost,127.0.0.1:3000" >> .env

# Set SESSION_DOMAIN if not exists
grep -q "^SESSION_DOMAIN=" .env || echo "SESSION_DOMAIN=.on-forge.com" >> .env

# Clear config cache
cd releases/000000/backend
php artisan config:clear
php artisan cache:clear

# Verify
cd ~/renthub-tbj7yxj7.on-forge.com
cat .env | grep -E "(SANCTUM|SESSION_DOMAIN)"
```

## Or use Forge UI:

1. Go to: https://forge.laravel.com
2. Select your server
3. Click **Environment** tab
4. Add/Update:
   ```
   SANCTUM_STATEFUL_DOMAINS=rent-hub-beta.vercel.app,localhost:3000,localhost,127.0.0.1:3000
   SESSION_DOMAIN=.on-forge.com
   ```
5. Click **Save**
6. SSH and run: `cd renthub-tbj7yxj7.on-forge.com/releases/000000/backend && php artisan config:clear`

## What this fixes:

- ✅ 419 Page Expired errors
- ✅ CSRF token issues
- ✅ Session cookie problems
- ✅ Cross-domain auth for Vercel → Forge
