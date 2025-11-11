# Deployment Guide - RentHub

## Frontend Deployment (Vercel)

### Prerequisites
- Vercel account (free tier available)
- GitHub repository (recommended)
- Backend API deployed and accessible

### Method 1: GitHub Integration (Recommended)

1. **Push to GitHub**
   ```bash
   cd frontend
   git add .
   git commit -m "Frontend setup"
   git push origin main
   ```

2. **Import to Vercel**
   - Go to [vercel.com](https://vercel.com)
   - Click "Add New Project"
   - Import your GitHub repository
   - Select the `frontend` folder as root directory
   - Framework Preset: Next.js (auto-detected)

3. **Configure Environment Variables**
   
   In Vercel project settings → Environment Variables, add:
   ```
   NEXT_PUBLIC_API_URL=https://your-backend-domain.com
   NEXT_PUBLIC_API_BASE_URL=https://your-backend-domain.com/api/v1
   ```

4. **Deploy**
   - Click "Deploy"
   - Vercel will automatically build and deploy
   - Your site will be live at `your-project.vercel.app`

### Method 2: Vercel CLI

1. **Install Vercel CLI**
   ```bash
   npm i -g vercel
   ```

2. **Login**
   ```bash
   vercel login
   ```

3. **Deploy from frontend directory**
   ```bash
   cd frontend
   vercel
   ```

4. **Set environment variables**
   ```bash
   vercel env add NEXT_PUBLIC_API_URL
   vercel env add NEXT_PUBLIC_API_BASE_URL
   ```

5. **Deploy to production**
   ```bash
   vercel --prod
   ```

### Custom Domain

1. In Vercel dashboard → Project Settings → Domains
2. Add your custom domain
3. Update DNS records as instructed by Vercel
4. SSL certificate is automatically provisioned

## Backend Deployment (Laravel Forge)

### Prerequisites
- Laravel Forge account
- VPS (DigitalOcean, AWS, Linode, etc.)
- Domain name

### Setup Steps

1. **Create Server in Forge**
   - Choose your VPS provider
   - Select server size (minimum: 1GB RAM)
   - Choose PHP version: 8.2+
   - Database: PostgreSQL or MySQL
   - Enable Redis (recommended)

2. **Create Site**
   - Domain: `api.yourdomain.com`
   - Project Type: Laravel
   - Web Directory: `/public`

3. **Deploy Repository**
   - Connect GitHub repository
   - Branch: `main` or `master`
   - Deploy Script:
     ```bash
     cd /home/forge/api.yourdomain.com
     git pull origin master
     composer install --no-dev --optimize-autoloader
     php artisan migrate --force
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     php artisan storage:link
     ```

4. **Environment Variables**
   
   In Forge → Site → Environment, set:
   ```env
   APP_NAME=RentHub
   APP_ENV=production
   APP_KEY=base64:...
   APP_DEBUG=false
   APP_URL=https://api.yourdomain.com

   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=renthub
   DB_USERNAME=forge
   DB_PASSWORD=your_password

   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis

   FRONTEND_URL=https://yourdomain.com

   # Add other required variables
   ```

5. **SSL Certificate**
   - Forge → SSL → LetsEncrypt
   - Enable "Force HTTPS"

6. **Queue Worker**
   - Forge → Site → Queue
   - Add worker: `php artisan queue:work --tries=3`

7. **Scheduler**
   - Forge → Site → Scheduler
   - Already configured by Forge

### Post-Deployment

1. **CORS Configuration**
   
   Update `config/cors.php`:
   ```php
   'allowed_origins' => [
       env('FRONTEND_URL', 'http://localhost:3000'),
   ],
   ```

2. **Database Migration**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

3. **Storage Link**
   ```bash
   php artisan storage:link
   ```

## Architecture

```
┌─────────────────┐         ┌──────────────────┐
│                 │         │                  │
│  Vercel         │────────▶│  Laravel Forge   │
│  (Frontend)     │  HTTPS  │  (Backend API)   │
│  Next.js        │         │  Laravel         │
│                 │         │  Filament        │
└─────────────────┘         └──────────────────┘
        │                            │
        │                            │
        ▼                            ▼
   yourdomain.com          api.yourdomain.com
```

## DNS Configuration

### For Frontend (Vercel)
```
Type    Name    Value
A       @       76.76.21.21
CNAME   www     cname.vercel-dns.com
```

### For Backend (Forge)
```
Type    Name    Value
A       api     YOUR_SERVER_IP
```

## Environment Variables Checklist

### Frontend (Vercel)
- ✅ `NEXT_PUBLIC_API_URL`
- ✅ `NEXT_PUBLIC_API_BASE_URL`
- ✅ `NEXT_PUBLIC_APP_NAME` (optional)
- ✅ `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY` (optional)

### Backend (Forge)
- ✅ `APP_URL`
- ✅ `FRONTEND_URL`
- ✅ `DB_*` variables
- ✅ `REDIS_*` variables
- ✅ `MAIL_*` variables
- ✅ `AWS_*` variables (for S3)

## Continuous Deployment

### Frontend (Vercel)
- Auto-deploys on `git push` to main branch
- Preview deployments for pull requests
- Instant rollbacks available

### Backend (Forge)
1. Enable Quick Deploy:
   - Forge → Site → Apps → Quick Deploy
2. Deploy on push:
   - Enable "Deploy When Code Is Pushed"
3. Webhook URL provided for manual triggers

## Monitoring

### Frontend
- Vercel Analytics (built-in)
- Error tracking with Vercel integrated tools

### Backend
- Laravel Telescope (development)
- Laravel Horizon (queue monitoring)
- Server monitoring in Forge dashboard

## Troubleshooting

### CORS Errors
```php
// config/cors.php
'supports_credentials' => true,
'allowed_origins' => [env('FRONTEND_URL')],
```

### Build Errors (Vercel)
- Check build logs in Vercel dashboard
- Verify all dependencies are in `package.json`
- Ensure environment variables are set

### API Connection Issues
- Verify backend is accessible
- Check CORS configuration
- Confirm API URL in frontend env variables

## Security Checklist

- ✅ HTTPS enabled on both frontend and backend
- ✅ Environment variables properly set
- ✅ CORS configured correctly
- ✅ APP_DEBUG=false in production
- ✅ Strong database passwords
- ✅ Rate limiting enabled
- ✅ Regular backups configured in Forge

## Backup Strategy

### Database (Forge)
- Forge → Server → Backups
- Enable automated daily backups
- Store in S3 or similar

### Files
- Regular server snapshots via VPS provider
- Version control via Git

## Cost Estimate

- **Frontend (Vercel)**: Free tier available, Pro $20/month
- **Backend (Forge)**: $12/month
- **Server (DigitalOcean)**: Starting $6/month (1GB RAM)
- **Domain**: ~$10/year

**Total**: ~$30-50/month for production setup

## Support

For deployment issues:
- Vercel: [vercel.com/support](https://vercel.com/support)
- Laravel Forge: [forge.laravel.com/docs](https://forge.laravel.com/docs)
