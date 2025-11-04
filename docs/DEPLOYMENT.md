# RentHub Deployment Guide

This guide covers various deployment options for RentHub.

## Table of Contents

- [Laravel Forge Deployment](#laravel-forge-deployment)
- [Docker Deployment](#docker-deployment)
- [Kubernetes Deployment](#kubernetes-deployment)
- [Production Checklist](#production-checklist)
- [Environment Configuration](#environment-configuration)
- [Monitoring and Logging](#monitoring-and-logging)

## Laravel Forge Deployment

Laravel Forge provides a simple way to deploy and manage Laravel applications on DigitalOcean, AWS, Linode, and other cloud providers.

### Prerequisites

- Laravel Forge account
- Server provisioned through Forge
- Git repository connected to Forge
- Database created on the server

### Initial Setup

1. **Create a New Site in Forge**
   - Log in to Laravel Forge
   - Select your server
   - Click "New Site"
   - Enter your domain (e.g., `api.renthub.com`)
   - Choose "Show Advanced" and set:
     - Web Directory: `/public`
     - PHP Version: `8.2` or higher

2. **Install Repository**
   - Go to your site's "Apps" tab
   - Click "Install Repository"
   - Select your Git provider (GitHub)
   - Enter repository: `anemettemadsen33/RentHub`
   - Branch: `main`
   - Check "Install Composer Dependencies"

3. **Configure Environment**
   - Go to the "Environment" tab
   - Update your `.env` file with production settings
   - Key variables to set:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://api.renthub.com
     DB_CONNECTION=mysql
     DB_HOST=localhost
     DB_DATABASE=your_database
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```
   - Click "Save" when done

4. **Run Initial Commands**
   - SSH into your server or use Forge's terminal
   - Navigate to `/home/forge/api.renthub.com`
   - Run:
     ```bash
     php artisan key:generate
     php artisan migrate --force
     php artisan storage:link
     php artisan optimize
     ```

### Deployment Hook

The deployment script is located at `backend/forge-deploy.sh`. This script runs automatically when you deploy through Forge.

To set up the deployment hook:

1. Go to your site's "Apps" tab in Forge
2. Click "Edit Deployment Script"
3. Update the script to match the path to your backend directory:

```bash
cd /home/forge/api.renthub.com
bash backend/forge-deploy.sh
```

Or if your repository root is the backend:

```bash
cd /home/forge/api.renthub.com
bash forge-deploy.sh
```

The deployment script includes:
- Git pull from main branch
- Composer dependency updates
- Database migrations
- Cache optimization
- Queue worker restart
- Asset compilation

### Quick Deploy

To deploy your application:
1. Push your changes to the `main` branch
2. Forge will automatically deploy (if Quick Deploy is enabled)
3. Or manually click "Deploy Now" in Forge

### Queue Workers

Set up queue workers in Forge:

1. Go to "Queue" tab
2. Click "New Worker"
3. Configure:
   - Connection: `redis` or `database`
   - Queue: `default`
   - Processes: `1` (adjust based on load)
   - Timeout: `60`
   - Sleep: `3`

### Scheduled Tasks

The Laravel scheduler should run every minute:

1. Go to "Scheduler" tab
2. Verify the scheduler command is set:
   ```bash
   php artisan schedule:run
   ```

### SSL Certificate

Enable SSL for your site:

1. Go to "SSL" tab
2. Click "LetsEncrypt"
3. Enter your domain
4. Click "Obtain Certificate"

### Monitoring

- Enable "Quick Deploy" for automatic deployments on push
- Set up "Notification Channels" for deployment notifications
- Monitor logs at `/home/forge/api.renthub.com/storage/logs/`

### Manual Deployment Commands

If needed, you can manually run deployment commands via SSH:

```bash
cd /home/forge/api.renthub.com
bash backend/forge-deploy.sh
```

Or individual commands:
```bash
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan queue:restart
```

### Troubleshooting

**Permission Issues:**
```bash
sudo chown -R forge:forge /home/forge/api.renthub.com
sudo chmod -R 775 /home/forge/api.renthub.com/storage
sudo chmod -R 775 /home/forge/api.renthub.com/bootstrap/cache
```

**Queue Not Processing:**
- Check queue worker status in Forge
- Restart queue workers: `php artisan queue:restart`
- Check logs: `tail -f storage/logs/laravel.log`

**Cache Issues:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Docker Deployment

### Development Environment

Run the development environment with hot-reloading:

```bash
make docker-dev
```

Or manually:

```bash
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

### Production Environment

1. Build the containers:
```bash
docker-compose -f docker-compose.production.yml build
```

2. Start the services:
```bash
docker-compose -f docker-compose.production.yml up -d
```

3. Run migrations:
```bash
docker-compose exec backend php artisan migrate --force
```

### Services

The Docker setup includes:
- **Backend**: Laravel application (port 8000)
- **Frontend**: Next.js application (port 3000)
- **Database**: MySQL/PostgreSQL
- **Redis**: Cache and queue management
- **Nginx**: Reverse proxy (production)

## Kubernetes Deployment

### Prerequisites

- Kubernetes cluster (v1.20+)
- kubectl configured
- Docker images pushed to a container registry

### Deployment Steps

1. Update the image names in `k8s/` configuration files

2. Deploy to Kubernetes:
```bash
kubectl apply -f k8s/
```

Or use the deployment script:
```bash
./scripts/k8s-deploy.sh
```

3. Check deployment status:
```bash
kubectl get pods
kubectl get services
```

### Available Deployment Strategies

#### Blue-Green Deployment

Deploy a new version alongside the old one:
```bash
./scripts/deploy-blue-green.sh
```

Rollback if needed:
```bash
./scripts/rollback-blue-green.sh
```

#### Canary Deployment

Gradually roll out to a percentage of users:
```bash
./scripts/deploy-canary.sh
```

Monitor canary metrics:
```bash
./scripts/monitor-canary.sh
```

Analyze canary performance:
```bash
./scripts/analyze-canary.sh
```

## Production Checklist

Before deploying to production:

### Backend

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate a strong `APP_KEY`
- [ ] Configure production database credentials
- [ ] Set up mail service (SMTP/SendGrid/etc.)
- [ ] Configure payment gateway credentials
- [ ] Set up social authentication keys
- [ ] Enable HTTPS and configure SSL certificates
- [ ] Configure CORS settings
- [ ] Set up backup strategy for database
- [ ] Configure logging and error tracking
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`

### Frontend

- [ ] Set production API URL in environment variables
- [ ] Build optimized production bundle: `npm run build`
- [ ] Configure CDN for static assets (optional)
- [ ] Set up analytics tracking
- [ ] Configure PWA settings
- [ ] Test service worker functionality

### Infrastructure

- [ ] Set up monitoring (Prometheus, Grafana)
- [ ] Configure log aggregation
- [ ] Set up alerting
- [ ] Configure backup automation
- [ ] Set up CI/CD pipeline
- [ ] Configure rate limiting
- [ ] Set up DDoS protection
- [ ] Configure firewall rules
- [ ] Set up SSL/TLS certificates
- [ ] Configure database replication (if needed)

## Environment Configuration

### Backend Environment Variables

```env
# Application
APP_NAME="RentHub"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Cache & Queue
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# Payment Gateway
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret

# Social Authentication
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

FACEBOOK_CLIENT_ID=your-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-facebook-client-secret
FACEBOOK_REDIRECT_URI=https://yourdomain.com/auth/facebook/callback
```

### Frontend Environment Variables

```env
# API Configuration
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
NEXT_PUBLIC_API_TIMEOUT=30000

# Google Maps
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=your-google-maps-key

# Analytics
NEXT_PUBLIC_GA_TRACKING_ID=your-ga-tracking-id

# Feature Flags
NEXT_PUBLIC_ENABLE_PWA=true
NEXT_PUBLIC_ENABLE_SOCIAL_AUTH=true
```

## Monitoring and Logging

### Application Monitoring

The application includes built-in performance monitoring. Configure these services:

- **Sentry**: Error tracking
- **New Relic**: Application performance monitoring
- **DataDog**: Infrastructure monitoring

### Log Management

Logs are stored in:
- Backend: `backend/storage/logs/`
- Frontend: stdout/stderr (captured by Docker)

For production, use centralized logging:
- ELK Stack (Elasticsearch, Logstash, Kibana)
- CloudWatch (AWS)
- Stackdriver (GCP)

### Health Checks

Backend health endpoint:
```
GET /api/health
```

Frontend health endpoint:
```
GET /api/health
```

### Performance Monitoring

Run smoke tests after deployment:
```bash
./scripts/smoke-tests.sh
```

Post-deployment tests:
```bash
./scripts/post-deployment-tests.sh
```

## Rollback Procedure

If issues are detected after deployment:

1. For Kubernetes:
```bash
kubectl rollout undo deployment/renthub-backend
kubectl rollout undo deployment/renthub-frontend
```

2. For Docker Compose:
```bash
docker-compose -f docker-compose.production.yml down
# Switch to previous image tags
docker-compose -f docker-compose.production.yml up -d
```

3. For Blue-Green:
```bash
./scripts/rollback-blue-green.sh
```

## Scaling

### Horizontal Scaling

Scale the application horizontally in Kubernetes:
```bash
kubectl scale deployment/renthub-backend --replicas=5
kubectl scale deployment/renthub-frontend --replicas=3
```

### Database Scaling

For high traffic, consider:
- Read replicas for the database
- Database connection pooling
- Redis caching layer
- CDN for static assets

## Backup and Recovery

### Database Backup

Automate daily backups:
```bash
# MySQL
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_$(date +%Y%m%d).sql

# PostgreSQL
pg_dump -U $DB_USER $DB_NAME > backup_$(date +%Y%m%d).sql
```

### Application Backup

Backup important directories:
- `backend/storage/app/` - Uploaded files
- `backend/.env` - Environment configuration

## Support

For deployment issues, consult:
- [Infrastructure documentation](./INFRASTRUCTURE.md)
- [API documentation](./api/)
- GitHub Issues
