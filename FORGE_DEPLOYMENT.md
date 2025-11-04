# Laravel Forge Deployment Guide

This repository uses a monorepo structure with the Laravel backend in the `backend/` directory. This guide explains how to properly configure Laravel Forge to deploy the backend application.

## Repository Structure

```
RentHub/
├── backend/           # Laravel application
│   ├── app/
│   ├── config/
│   ├── composer.json
│   └── ...
├── frontend/          # Frontend application
├── composer.json      # Root composer.json (deployment wrapper)
├── forge-deploy.sh    # Forge deployment script
└── ...
```

## Quick Setup in Laravel Forge

### 1. Create New Site

In Laravel Forge, create a new site with these settings:

- **Root Domain**: Your domain (e.g., `api.renthub.com` or `renthub-dji696t0.on-forge.com`)
- **Project Type**: Laravel
- **Web Directory**: `/backend/public` (Important!)
- **PHP Version**: 8.2 or higher

### 2. Install Repository

Connect your GitHub repository:

- **Repository**: `anemettemadsen33/RentHub`
- **Branch**: `main`
- **Install Composer Dependencies**: ✓ Yes

### 3. Update Deployment Script

In Forge, go to your site's "Apps" tab and update the deployment script to:

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

# Run the deployment script
bash forge-deploy.sh
```

**Important**: The `forge-deploy.sh` script at the root handles:
- Navigation to the `backend/` directory
- Installing Composer dependencies in the backend
- Running Laravel artisan commands
- Cache clearing and optimization

### 4. Environment Variables

Update the `.env` file in Forge (under "Environment" tab) with your production settings:

```env
APP_NAME=RentHub
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key-here
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=forge
DB_PASSWORD=your_secure_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### 5. Initial Deployment Commands

After the first deployment, SSH into your server and run:

```bash
cd /home/forge/your-site-name/backend
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

### 6. Setup Queue Worker

In Forge, add a Daemon (under "Daemons" tab):

- **Command**: `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`
- **Directory**: `/home/forge/your-site-name/backend`
- **User**: `forge`

### 7. Scheduler

Forge automatically sets up the Laravel scheduler. Verify in the "Scheduler" tab that this cron job exists:

```
* * * * * cd /home/forge/your-site-name/backend && php artisan schedule:run >> /dev/null 2>&1
```

### 8. SSL Certificate

1. Go to the "SSL" tab
2. Click "Let's Encrypt"
3. Enter your domain
4. Click "Obtain Certificate"

## Understanding the Deployment Process

### Root composer.json

The `composer.json` at the repository root is a minimal wrapper that satisfies Forge's requirement. The actual Laravel application and its dependencies are defined in `backend/composer.json`.

### Deployment Script

The `forge-deploy.sh` script:

1. Identifies the deployment directory
2. Navigates to the `backend/` subdirectory
3. Runs all Laravel-specific commands (composer, artisan, etc.)
4. Handles maintenance mode, migrations, and cache optimization

### Web Directory

Setting the Web Directory to `/backend/public` in Forge ensures that:
- Nginx serves files from the correct public directory
- Laravel's routing works correctly
- Security is maintained (code outside public directory is not accessible)

## Troubleshooting

### "composer.json not found" Error

**Cause**: This happens when Forge tries to run composer at the root level.

**Solution**: 
1. Ensure the root `composer.json` exists (it should be in the repo)
2. Verify the deployment script uses `cd $BACKEND_DIR` before running composer
3. Check that Web Directory is set to `/backend/public`

### 500 Internal Server Error

**Solutions**:
```bash
cd /home/forge/your-site-name/backend
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

### Storage/Permission Issues

```bash
cd /home/forge/your-site-name/backend
chmod -R 775 storage bootstrap/cache
```

### Queue Not Processing

1. Check the daemon is running in Forge
2. Restart the queue: `php artisan queue:restart`
3. Check logs: `tail -f storage/logs/laravel.log`

## Testing the Deployment

After deployment, test these endpoints:

1. **Homepage**: `https://your-domain.com`
2. **Health Check**: `https://your-domain.com/api/health` (if available)

## Additional Resources

- [Laravel Forge Documentation](https://forge.laravel.com/docs)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- Backend-specific setup: See `backend/FORGE_SETUP.md`

## Support

For deployment issues:
1. Check Forge deployment logs in the Forge UI
2. Check Laravel logs: `backend/storage/logs/laravel.log`
3. Check Nginx logs: `/var/log/nginx/error.log`
4. Create an issue on GitHub: https://github.com/anemettemadsen33/RentHub/issues
