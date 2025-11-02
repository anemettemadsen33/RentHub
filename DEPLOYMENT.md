# RentHub Deployment Guide

## Backend Deployment (Laravel Forge)

### Prerequisites
- Laravel Forge account
- Server provisioned on Forge
- Domain configured (e.g., api.renthub.com)
- SSL certificate installed

### Step-by-Step Deployment

#### 1. Create Site on Forge
1. Log in to Laravel Forge
2. Create a new site with domain: `api.renthub.com`
3. Choose PHP 8.2 or higher
4. Set web directory to `/public`

#### 2. Connect Repository
1. In Forge, go to your site
2. Navigate to "Apps" → "Git Repository"
3. Connect your GitHub repository
4. Branch: `main`
5. Enable "Quick Deploy" if desired

#### 3. Configure Environment Variables
Copy values from `backend/.env.production` and update:

```bash
APP_NAME=RentHub
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.renthub.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

FRONTEND_URL=https://renthub.com
SANCTUM_STATEFUL_DOMAINS=renthub.com,www.renthub.com
SESSION_DOMAIN=.renthub.com
SESSION_SECURE_COOKIE=true

# Add your mail, AWS, etc. credentials
```

#### 4. Set Up Database
1. In Forge, navigate to "Database"
2. Create a new database
3. Note the credentials and update .env

#### 5. Configure Deploy Script
In Forge, go to "Apps" → "Deploy Script" and paste the contents of `backend/forge-deploy.sh`

#### 6. Initial Deployment
```bash
# SSH into your server
forge ssh api.renthub.com

# Navigate to site
cd api.renthub.com

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
```

#### 7. Configure Scheduler (Optional)
If using Laravel Scheduler, add cron job in Forge:
```
* * * * * php /home/forge/api.renthub.com/artisan schedule:run >> /dev/null 2>&1
```

#### 8. Configure Queue Worker (Optional)
In Forge, navigate to "Queue" and add:
- Connection: database
- Queue: default
- Max Tries: 3
- Sleep: 3

---

## Frontend Deployment (Vercel)

### Prerequisites
- Vercel account
- Domain configured (e.g., renthub.com)

### Step-by-Step Deployment

#### 1. Connect Repository to Vercel
1. Log in to Vercel
2. Click "Add New Project"
3. Import your GitHub repository
4. Select the `frontend` directory as the root

#### 2. Configure Project Settings
- **Framework Preset**: Next.js
- **Root Directory**: `frontend`
- **Build Command**: `npm run build`
- **Output Directory**: `.next`
- **Install Command**: `npm install`

#### 3. Configure Environment Variables
Add the following environment variables in Vercel:

```bash
NEXT_PUBLIC_API_URL=https://api.renthub.com
NEXTAUTH_URL=https://renthub.com
NEXTAUTH_SECRET=generate-a-secure-random-string-here
API_URL=https://api.renthub.com
```

To generate `NEXTAUTH_SECRET`:
```bash
openssl rand -base64 32
```

#### 4. Configure Custom Domain
1. In Vercel project settings, go to "Domains"
2. Add your custom domain: `renthub.com`
3. Add `www.renthub.com` (optional)
4. Follow DNS configuration instructions

#### 5. Deploy
1. Click "Deploy"
2. Vercel will automatically build and deploy
3. Subsequent pushes to `main` branch will auto-deploy

---

## Post-Deployment Checklist

### Backend
- [ ] SSL certificate installed and working
- [ ] Environment variables configured
- [ ] Database created and migrated
- [ ] Storage symlink created
- [ ] File permissions set correctly
- [ ] Queue workers running (if needed)
- [ ] Scheduler configured (if needed)
- [ ] API accessible at https://api.renthub.com
- [ ] CORS configured correctly

### Frontend
- [ ] Environment variables set
- [ ] Custom domain configured
- [ ] SSL certificate active
- [ ] Frontend accessible at https://renthub.com
- [ ] API calls working correctly
- [ ] Authentication working

### Testing
- [ ] Register new user
- [ ] Login/logout functionality
- [ ] API endpoints responding correctly
- [ ] File uploads working (if applicable)
- [ ] Email notifications working (if applicable)

---

## Troubleshooting

### Backend Issues

**500 Error**
- Check `.env` file is configured correctly
- Run `php artisan config:cache`
- Check storage permissions: `chmod -R 755 storage bootstrap/cache`
- Check error logs in `storage/logs`

**CORS Errors**
- Verify `FRONTEND_URL` in `.env`
- Check `config/cors.php`
- Ensure `SANCTUM_STATEFUL_DOMAINS` is correct

**Database Connection Failed**
- Verify database credentials in `.env`
- Ensure database exists
- Check database user has correct permissions

### Frontend Issues

**API Connection Failed**
- Verify `NEXT_PUBLIC_API_URL` is correct
- Check CORS configuration on backend
- Verify backend is accessible

**Authentication Not Working**
- Check `NEXTAUTH_SECRET` is set
- Verify `NEXTAUTH_URL` matches your domain
- Check cookie settings (domain, secure flag)

---

## Continuous Deployment

### Backend (Forge)
Enable "Quick Deploy" in Forge to automatically deploy when you push to main branch.

### Frontend (Vercel)
Vercel automatically deploys on every push to main branch.

---

## Rollback Procedure

### Backend
```bash
# SSH into server
forge ssh api.renthub.com

# View git log
git log --oneline

# Rollback to previous commit
git reset --hard <commit-hash>

# Run deploy script
bash forge-deploy.sh
```

### Frontend
1. Go to Vercel dashboard
2. Navigate to "Deployments"
3. Find previous working deployment
4. Click "..." → "Promote to Production"

---

## Security Recommendations

1. **Never commit `.env` files** - They're gitignored for a reason
2. **Use strong passwords** for database and admin accounts
3. **Enable 2FA** on Forge and Vercel accounts
4. **Regular backups** - Set up automated database backups in Forge
5. **Keep dependencies updated** - Regularly run `composer update` and `npm update`
6. **Monitor logs** - Set up log monitoring and alerting
7. **SSL only** - Always use HTTPS in production

---

## Useful Commands

### Backend
```bash
# Clear all caches
php artisan optimize:clear

# Cache everything
php artisan optimize

# View logs
tail -f storage/logs/laravel.log

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

### Frontend
```bash
# Build locally
npm run build

# Start production server locally
npm run start

# Lint code
npm run lint
```
