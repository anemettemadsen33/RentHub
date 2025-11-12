# ============================================
# FORGE COMPLETE SETUP GUIDE
# ============================================

## STEP 1: Environment Configuration (.env)
## Location: Forge → Site → Environment

Copy this ENTIRE block:

```env
APP_NAME=RentHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://YOUR-SITE-URL.on-forge.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=YOUR_DB_PASSWORD_HERE

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@renthub.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Frontend URL (Vercel)
FRONTEND_URL=https://rent-hub-git-master-madsens-projects.vercel.app
SANCTUM_STATEFUL_DOMAINS=rent-hub-git-master-madsens-projects.vercel.app,localhost:3000
SESSION_DOMAIN=.on-forge.com

# CORS
CORS_ALLOWED_ORIGINS=https://rent-hub-git-master-madsens-projects.vercel.app,http://localhost:3000
```

## STEP 2: Get Database Password
## Location: Forge → Site → Database

1. Click "Database" tab
2. Find database: `forge`
3. Copy the password
4. Go back to Environment
5. Paste password in: `DB_PASSWORD=`
6. Click "Update"

## STEP 3: Update Deployment Script
## Location: Forge → Site → Deployment

Replace with this script:

```bash
cd /home/forge/YOUR-SITE-FOLDER

git pull origin master

cd backend

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

php artisan key:generate --force --no-interaction || true
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan migrate --force
php artisan db:seed --force

chmod -R 775 storage bootstrap/cache

echo "✅ Backend deployment complete!"
```

## STEP 4: Enable Quick Deploy
## Location: Forge → Site → Apps → Quick Deploy

Toggle "Quick Deploy" to ON
This auto-deploys on every GitHub push!

## STEP 5: Deploy Now

Click "Deploy Now" button

Expected output:
```
✓ git pull origin master
✓ composer install
✓ php artisan migrate
✓ php artisan db:seed
✅ Backend deployment complete!
```

## STEP 6: Enable SSL
## Location: Forge → Site → SSL

1. Click "SSL" tab
2. Choose "LetsEncrypt"
3. Click "Obtain Certificate"
4. Wait 1-2 minutes

## STEP 7: Test Backend

Open in browser:
- https://YOUR-SITE.on-forge.com/api/health
- https://YOUR-SITE.on-forge.com/admin

Expected:
```json
{
  "status": "ok",
  "timestamp": "2025-11-11T20:45:00Z"
}
```

## STEP 8: Update Frontend .env (Vercel)

Vercel → Project → Settings → Environment Variables

Add/Update:
```
NEXT_PUBLIC_API_URL=https://YOUR-SITE.on-forge.com
```

Redeploy frontend after this change.

## TROUBLESHOOTING

### 500 Error:
- Check: Forge → Site → Logs
- Check: Environment has APP_KEY set
- Run: Deploy Now again

### Database Error:
- Verify DB_PASSWORD is correct
- Check: Forge → Database tab for credentials

### CORS Error:
- Update CORS_ALLOWED_ORIGINS in .env
- Include your Vercel URL
- Deploy again

## Quick Commands (if needed via SSH)

```bash
# Navigate to site
cd /home/forge/YOUR-SITE-FOLDER/backend

# Check status
php artisan about

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# View logs
tail -50 storage/logs/laravel.log
```

## SUCCESS CHECKLIST

- [ ] .env configured with database password
- [ ] APP_KEY generated
- [ ] Deployment script updated
- [ ] Quick Deploy enabled
- [ ] SSL certificate obtained
- [ ] /api/health returns 200 OK
- [ ] /admin loads Filament dashboard
- [ ] Frontend can connect to backend
- [ ] CORS configured for Vercel domain

## DONE! 🎉

Your backend is now deployed and ready!

Next: Update frontend environment variables to point to new backend URL.
