# Laravel Forge Setup Guide for RentHub Backend

This guide will help you set up and deploy the RentHub backend on Laravel Forge.

## Quick Setup Steps

### 1. Create Site in Forge

- **Site Name**: `api.renthub.com` (or your domain)
- **Web Directory**: `/public`
- **PHP Version**: `8.2` or higher
- **Project Type**: Laravel

### 2. Install Repository

```
Repository: anemettemadsen33/RentHub
Branch: main
Directory: /home/forge/api.renthub.com
```

Check: ✓ Install Composer Dependencies

### 3. Environment Variables

Update the `.env` file in Forge with these critical variables:

```env
APP_NAME=RentHub
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.renthub.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=forge
DB_PASSWORD=your_secure_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@api.renthub.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Deployment Script

The deployment script is located at `backend/forge-deploy.sh`.

**Update Forge Deployment Script** (in Forge UI):

```bash
cd /home/forge/api.renthub.com
bash backend/forge-deploy.sh
```

If the backend is at the repository root, use:

```bash
cd /home/forge/api.renthub.com
bash forge-deploy.sh
```

### 5. Initial Commands

After first deployment, run these commands via SSH or Forge terminal:

```bash
cd /home/forge/api.renthub.com
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan db:seed --force  # Optional, only if you want sample data
```

### 6. Queue Workers

Create a queue worker in Forge:
- **Connection**: `redis`
- **Queue**: `default`
- **Processes**: `1`
- **Max Tries**: `3`
- **Sleep**: `3`
- **Timeout**: `60`

### 7. Scheduled Jobs

Verify the scheduler is configured (Forge sets this up automatically):

```bash
* * * * * cd /home/forge/api.renthub.com && php artisan schedule:run >> /dev/null 2>&1
```

### 8. SSL Certificate

1. Go to SSL tab in Forge
2. Click "LetsEncrypt"
3. Enter your domain: `api.renthub.com`
4. Click "Obtain Certificate"

### 9. Enable Quick Deploy

1. Go to "Apps" tab
2. Toggle "Quick Deploy" to automatically deploy on git push

## File Permissions

Ensure correct permissions (Forge usually handles this):

```bash
sudo chown -R forge:forge /home/forge/api.renthub.com
sudo chmod -R 775 /home/forge/api.renthub.com/storage
sudo chmod -R 775 /home/forge/api.renthub.com/bootstrap/cache
```

## Testing Deployment

After deployment, test these endpoints:

1. **Health Check**: `https://api.renthub.com/api/health`
2. **API Status**: `https://api.renthub.com/api/status`

## Common Issues

### Issue: 500 Error After Deployment

**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Storage Link Not Working

**Solution:**
```bash
php artisan storage:link
```

### Issue: Queue Not Processing

**Solution:**
1. Check queue worker in Forge is running
2. Restart queue: `php artisan queue:restart`
3. Check logs: `tail -f storage/logs/laravel.log`

### Issue: Database Connection Error

**Solution:**
1. Verify database credentials in `.env`
2. Ensure database exists in Forge
3. Check database user has proper permissions

## Rollback

If deployment fails, rollback to previous version:

```bash
cd /home/forge/api.renthub.com
git reset --hard HEAD~1
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan up
```

## Monitoring

- **Application Logs**: `/home/forge/api.renthub.com/storage/logs/`
- **Forge Logs**: Available in Forge UI under "Logs"
- **Server Logs**: `/var/log/nginx/` for web server logs

## Security Checklist

- [x] APP_DEBUG=false in production
- [x] Strong APP_KEY generated
- [x] SSL certificate installed
- [x] Firewall configured
- [x] Database password is strong
- [x] Redis password set (if exposed)
- [x] File permissions properly set

## Support

For issues specific to this deployment:
- Check Laravel logs: `storage/logs/laravel.log`
- Check Nginx logs: `/var/log/nginx/error.log`
- Forge support: https://forge.laravel.com/support

For application issues:
- GitHub Issues: https://github.com/anemettemadsen33/RentHub/issues
