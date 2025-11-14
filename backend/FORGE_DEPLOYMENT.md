# Laravel Forge Deployment Guide

## Prerequisites

1. **Laravel Forge Account**: Sign up at [forge.laravel.com](https://forge.laravel.com)
2. **Server Provider**: DigitalOcean, AWS, Linode, or other supported provider
3. **Domain Name**: Configured and pointing to your server

## Step 1: Create Server on Forge

1. Log in to Laravel Forge
2. Click "Create Server"
3. Choose your server provider
4. Select server specifications:
   - **Type**: App Server
   - **Size**: At least 2GB RAM for production
   - **Region**: Closest to your target audience
   - **PHP Version**: 8.2 or higher
   - **Database**: PostgreSQL 15+

5. Configure server:
   - Enable OPcache
   - Enable Redis
   - Enable Node.js (for queue workers)

## Step 2: Create Site

1. After server provisioning, click "New Site"
2. Configure:
   - **Root Domain**: yourdomain.com
   - **Project Type**: Laravel
   - **Web Directory**: `/public`

## Step 3: Install Repository

1. In your site, go to "Git Repository"
2. Choose your provider (GitHub/GitLab/Bitbucket)
3. Enter repository: `your-username/RentHub`
4. Branch: `master` or `main`
5. Click "Install Repository"

## Step 4: Configure Environment

1. Go to "Environment" tab
2. Copy content from `backend/.env.production`
3. Update the following critical variables:

```bash
APP_URL=https://yourdomain.com
DB_DATABASE=your_database_name
DB_USERNAME=forge
DB_PASSWORD=your_secure_password

# Generate new key
php artisan key:generate

# Update these with your actual credentials
STRIPE_KEY=sk_live_...
STRIPE_SECRET=...
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
```

## Step 5: Setup SSL Certificate

1. Go to "SSL" tab
2. Choose "LetsEncrypt"
3. Enter domain names (including www)
4. Click "Obtain Certificate"

## Step 6: Configure Scheduler

1. Go to "Scheduler" tab
2. Add cron job:
   - **Command**: `php artisan schedule:run`
   - **User**: forge
   - **Frequency**: Every Minute

## Step 7: Configure Queue Workers

1. Go to "Queue" tab
2. Add new worker:
   - **Connection**: redis
   - **Queue**: default
   - **Processes**: 3
   - **Max Time**: 300

3. For high-priority jobs, add another worker:
   - **Connection**: redis
   - **Queue**: high
   - **Processes**: 2

## Step 8: Configure Deployment Script

1. Go to "Deployment" tab
2. Replace default script with contents from `backend/deploy.sh`
3. Enable "Quick Deploy" if desired

## Step 9: Database Setup

1. SSH into your server: `forge ssh`
2. Navigate to site: `cd yourdomain.com`
3. Run migrations: `php artisan migrate --force`
4. Seed database: `php artisan db:seed --force`

## Step 10: Configure Supervisor (for Reverb)

If using Laravel Reverb for WebSockets:

1. Go to "Daemons" tab
2. Add new daemon:
   - **Command**: `php artisan reverb:start`
   - **Directory**: `/home/forge/yourdomain.com`
   - **User**: forge
   - **Processes**: 1

## Step 11: Configure Redis

1. SSH into server
2. Update Redis config:

```bash
sudo nano /etc/redis/redis.conf
```

3. Set:
```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

4. Restart Redis:
```bash
sudo service redis-server restart
```

## Step 12: Performance Optimization

### Enable OPcache

1. Go to server settings
2. Enable OPcache
3. Set memory to at least 128MB

### Enable HTTP/2

1. Automatically enabled with SSL

### Configure Nginx

Add to Nginx config (optional):

```nginx
# Gzip compression
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;

# Browser caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Step 13: Security Configuration

### Firewall Rules

1. Go to "Security" → "Firewall"
2. Keep only necessary ports open:
   - 22 (SSH)
   - 80 (HTTP)
   - 443 (HTTPS)

### Fail2Ban

1. Enable in server settings
2. Configure for SSH and HTTP

### Database Security

```bash
# Restrict database access to localhost only
sudo nano /etc/postgresql/15/main/postgresql.conf
# Set: listen_addresses = 'localhost'
```

## Step 14: Monitoring Setup

### Enable Forge Monitoring

1. Go to "Monitoring" tab
2. Enable:
   - Disk usage alerts
   - Memory alerts
   - CPU alerts

### Configure Laravel Pulse

```bash
php artisan pulse:install
php artisan migrate
```

### Setup Log Rotation

Automatic on Forge, but verify:

```bash
sudo nano /etc/logrotate.d/laravel
```

## Step 15: Backup Configuration

1. Go to "Backups" tab
2. Configure database backups:
   - **Frequency**: Daily
   - **Retention**: 14 days
   - **Storage**: S3 or other

## Step 16: Deploy!

1. Go to "Deployment" tab
2. Click "Deploy Now"
3. Monitor deployment log

## Post-Deployment Checks

```bash
# SSH into server
forge ssh

# Navigate to site
cd yourdomain.com

# Check application
php artisan about

# Verify queue workers
php artisan queue:monitor

# Check scheduled tasks
php artisan schedule:list

# Test database connection
php artisan db:show

# Clear all caches
php artisan optimize:clear
php artisan optimize
```

## Continuous Deployment

Enable "Quick Deploy" in Forge to automatically deploy on git push:

1. Go to "Deployment" tab
2. Enable "Quick Deploy"
3. Configure branch (master/main)

## Rollback Procedure

If deployment fails:

```bash
# SSH into server
forge ssh
cd yourdomain.com

# Put in maintenance mode
php artisan down

# Checkout previous commit
git log --oneline -10
git checkout <previous-commit-hash>

# Run deployment script
bash deploy.sh

# Or manually:
composer install --no-dev
php artisan migrate:rollback
php artisan config:cache
php artisan up
```

## Troubleshooting

### 500 Error

```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
sudo chown -R forge:forge /home/forge/yourdomain.com
sudo chmod -R 755 /home/forge/yourdomain.com/storage
```

### Queue Not Processing

```bash
# Restart workers
php artisan queue:restart
sudo supervisorctl restart all
```

### Database Connection Failed

```bash
# Verify credentials
php artisan config:clear
cat .env | grep DB_

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Performance Benchmarks

After deployment, test performance:

```bash
# API response time
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com/api/properties

# Database query optimization
php artisan telescope:install # For debugging only
```

## Security Checklist

- [ ] SSL certificate installed and auto-renewing
- [ ] Environment variables secured
- [ ] Database credentials strong and unique
- [ ] Firewall configured correctly
- [ ] Fail2Ban enabled
- [ ] Laravel security headers configured
- [ ] CORS properly configured
- [ ] API rate limiting enabled
- [ ] File upload validation enabled
- [ ] XSS protection enabled
- [ ] SQL injection protection (use Eloquent)
- [ ] CSRF protection enabled

## Maintenance Commands

```bash
# Weekly maintenance
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan optimize

# Monthly maintenance
php artisan queue:prune-batches
php artisan queue:prune-failed
php artisan telescope:prune
php artisan model:prune
```

## Support

- **Forge Documentation**: https://forge.laravel.com/docs
- **Laravel Documentation**: https://laravel.com/docs
- **Forge Discord**: https://discord.gg/forge
