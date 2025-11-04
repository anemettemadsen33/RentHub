# RentHub Deployment Guide

This guide covers various deployment options for RentHub.

## Table of Contents

- [Docker Deployment](#docker-deployment)
- [Kubernetes Deployment](#kubernetes-deployment)
- [Production Checklist](#production-checklist)
- [Environment Configuration](#environment-configuration)
- [Monitoring and Logging](#monitoring-and-logging)

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
