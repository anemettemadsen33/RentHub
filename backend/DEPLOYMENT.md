# RentHub Backend - Forge Deployment Instructions

## Server Information
- **Public IP**: 178.128.135.24
- **VPC Host**: rental-platform.private.on-forge.com
- **VPC Name**: Laravel Managed
- **Frontend URL**: https://rent-hub-six.vercel.app

## Step 1: SSH Connection to Forge Server

Connect to your Forge server:
```bash
ssh forge@178.128.135.24
```

## Step 2: Site Setup in Forge Dashboard

1. Log in to Laravel Forge
2. Navigate to your server
3. Create a new site with these settings:
   - **Root Domain**: `rental-platform.private.on-forge.com`
   - **Project Type**: Laravel
   - **Web Directory**: `/public`
   - **PHP Version**: 8.2 or higher

## Step 3: Install Repository

In Forge Dashboard → Apps → Git Repository:

```
Repository: anemettemadsen33/RentHub
Branch: main (or master)
```

✅ Check: "Install Composer Dependencies"

Click "Install Repository"

## Step 4: Configure Environment Variables

In Forge Dashboard → Environment:

Copy the contents from `backend/.env.forge` and update these critical values:

```env
APP_KEY=                          # Generate with: php artisan key:generate
DB_PASSWORD=                      # Your database password from Forge
MAIL_HOST=                        # Your SMTP host
MAIL_PORT=                        # Your SMTP port
MAIL_USERNAME=                    # Your SMTP username
MAIL_PASSWORD=                    # Your SMTP password
```

After updating, click "Save" button.

## Step 5: Update Deployment Script

In Forge Dashboard → Apps → Deploy Script:

Replace the default script with:

```bash
cd /home/forge/rental-platform.private.on-forge.com
git pull origin main
bash backend/forge-deploy.sh
```

**Note**: If your repository structure has backend at root (not in a subdirectory), use:
```bash
cd /home/forge/rental-platform.private.on-forge.com
bash forge-deploy.sh
```

## Step 6: Initial Deployment Commands

After the repository is installed, SSH into the server and run:

```bash
cd /home/forge/rental-platform.private.on-forge.com

# If backend is in a subdirectory
cd backend

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optional: Seed database with sample data
# php artisan db:seed --force
```

## Step 7: Configure Queue Worker

In Forge Dashboard → Queue:

Create a new worker:
- **Connection**: `redis`
- **Queue**: `default`
- **Processes**: `1`
- **Max Tries**: `3`
- **Sleep**: `3`
- **Timeout**: `60`

## Step 8: SSL Certificate

In Forge Dashboard → SSL:

1. Select "LetsEncrypt"
2. Enter domains: `rental-platform.private.on-forge.com`
3. Click "Obtain Certificate"

## Step 9: Configure Scheduler (Auto-configured by Forge)

Verify the scheduler cron is set up:
```bash
* * * * * cd /home/forge/rental-platform.private.on-forge.com && php artisan schedule:run >> /dev/null 2>&1
```

## Step 10: Enable Quick Deploy (Optional)

In Forge Dashboard → Apps:

Toggle "Quick Deploy" ON to automatically deploy on git push to main branch.

## Step 11: Deploy Application

Click the "Deploy Now" button in Forge Dashboard.

## Step 12: Verify Deployment

Test these endpoints:

1. **API Health**: 
   ```bash
   curl https://rental-platform.private.on-forge.com/api/health
   ```

2. **API Root**:
   ```bash
   curl https://rental-platform.private.on-forge.com/api
   ```

3. **CORS Test** (from frontend):
   Access https://rent-hub-six.vercel.app and test API calls

## Database Configuration

Forge automatically creates a MySQL database. Verify in Forge Dashboard → Database:

- Database name: `forge`
- Database user: `forge`
- Password: (shown in Forge)

Update `.env` with these credentials.

## File Permissions

If you encounter permission issues:

```bash
sudo chown -R forge:forge /home/forge/rental-platform.private.on-forge.com
sudo chmod -R 775 /home/forge/rental-platform.private.on-forge.com/storage
sudo chmod -R 775 /home/forge/rental-platform.private.on-forge.com/bootstrap/cache
```

## Troubleshooting

### Check Logs

```bash
# Laravel logs
tail -f /home/forge/rental-platform.private.on-forge.com/storage/logs/laravel.log

# Nginx error logs
sudo tail -f /var/log/nginx/rental-platform.private.on-forge.com-error.log

# Nginx access logs
sudo tail -f /var/log/nginx/rental-platform.private.on-forge.com-access.log
```

### Common Issues

**500 Internal Server Error**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Queue Not Processing**:
```bash
php artisan queue:restart
```

**Storage Issues**:
```bash
php artisan storage:link
sudo chmod -R 775 storage
```

## CORS Configuration

The backend is already configured to accept requests from:
- `https://rent-hub-six.vercel.app`
- Any `*.vercel.app` domain
- Any `*.on-forge.com` domain

This is configured in `backend/config/cors.php`

## Environment-Specific Notes

### Production Optimizations
- `APP_DEBUG=false` - Disabled for security
- `LOG_LEVEL=error` - Only log errors
- Opcache enabled via Forge
- Redis for cache and sessions
- Queue workers running

### Security Checklist
- ✅ SSL certificate installed
- ✅ APP_DEBUG disabled
- ✅ Strong APP_KEY generated
- ✅ Database password secured
- ✅ File permissions set correctly
- ✅ Firewall configured by Forge

## Maintenance Commands

### Update Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Clear All Caches
```bash
php artisan optimize:clear
```

### Rebuild Caches
```bash
php artisan optimize
```

### Restart Queue Workers
```bash
php artisan queue:restart
```

## Rollback Procedure

If deployment fails:

```bash
cd /home/forge/rental-platform.private.on-forge.com
git reset --hard HEAD~1
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan up
```

## Support

- **Forge Documentation**: https://forge.laravel.com/docs
- **Laravel Documentation**: https://laravel.com/docs
- **Server IP**: 178.128.135.24
- **Server Access**: SSH via `forge@178.128.135.24`

---

**Last Updated**: 2025-11-07
**Deployment Status**: Ready for production deployment
