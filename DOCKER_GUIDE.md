# 🐳 Docker Containerization Guide - RentHub

## Overview

This guide covers Docker containerization for the RentHub platform, including multi-stage builds, orchestration with Docker Compose, and best practices for development and production.

## 📋 Table of Contents

- [Architecture](#architecture)
- [Quick Start](#quick-start)
- [Services](#services)
- [Development](#development)
- [Production](#production)
- [Commands](#commands)
- [Troubleshooting](#troubleshooting)

## 🏗️ Architecture

### Container Stack

```
┌─────────────────────────────────────────┐
│              Nginx (Port 80)            │
│         Reverse Proxy & Load Balancer   │
└──────────┬─────────────┬────────────────┘
           │             │
    ┌──────▼─────┐  ┌───▼────────┐
    │  Backend   │  │  Frontend  │
    │  Laravel   │  │  Next.js   │
    │  (PHP-FPM) │  │  (Node.js) │
    └──────┬─────┘  └────────────┘
           │
    ┌──────▼─────────────────┐
    │                        │
┌───▼─────┐          ┌──────▼────┐
│PostgreSQL│         │   Redis   │
│ Database │         │   Cache   │
└──────────┘         └───────────┘
```

### Services Breakdown

1. **Backend (Laravel/PHP-FPM)**: API server
2. **Frontend (Next.js)**: Client application
3. **PostgreSQL**: Primary database
4. **Redis**: Cache and session storage
5. **Nginx**: Reverse proxy and static file server
6. **Queue Worker**: Background job processing
7. **Scheduler**: Cron job handler
8. **MailHog** (dev): Email testing
9. **MinIO** (dev): S3-compatible storage

## 🚀 Quick Start

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Make (optional, for shortcuts)

### Initial Setup

```bash
# 1. Clone and navigate to project
cd RentHub

# 2. Create environment file
cp .env.example .env

# 3. Build and start containers
make docker-build
make docker-up

# Or using docker-compose directly:
docker-compose build
docker-compose up -d

# 4. Run migrations
make docker-migrate

# 5. Access application
# Frontend: http://localhost:3000
# Backend API: http://localhost/api
# Adminer: http://localhost:8080 (dev)
```

## 🛠️ Services

### Backend (Laravel)

**Dockerfile**: `backend/Dockerfile`

Multi-stage build:
- **Base stage**: Install dependencies, build assets
- **Production stage**: Minimal runtime image

```dockerfile
# Key features:
- PHP 8.3 FPM Alpine
- Composer dependencies optimized
- Built Vite assets included
- Optimized autoloader
- Security hardened
```

**Environment Variables**:
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=postgres
REDIS_HOST=redis
```

### Frontend (Next.js)

**Dockerfile**: `frontend/Dockerfile`

Multi-stage build:
- **deps**: Install dependencies
- **builder**: Build Next.js app
- **runner**: Production runtime
- **development**: Dev environment

```dockerfile
# Key features:
- Node.js 20 Alpine
- Standalone output
- Non-root user
- Development hot reload
```

### PostgreSQL

**Image**: `postgres:16-alpine`

Features:
- Persistent volume storage
- Custom initialization script
- Performance tuning
- Health checks

### Redis

**Image**: `redis:7-alpine`

Features:
- Password authentication
- Append-only persistence
- Health checks
- Used for cache, sessions, queues

### Nginx

**Image**: `nginx:alpine`

Features:
- Reverse proxy for backend/frontend
- Static file caching
- Gzip compression
- Rate limiting
- Security headers
- SSL/TLS ready

## 💻 Development

### Development Environment

```bash
# Start with development overrides
make docker-dev

# Or:
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

**Development Features**:
- Hot reload for both backend and frontend
- Source code mounted as volumes
- Debug mode enabled
- Additional tools: Adminer, Redis Commander
- MailHog for email testing
- MinIO for S3 testing

### Development Tools

**Adminer** (Database Management):
```
URL: http://localhost:8080
Server: postgres
Username: postgres
Password: secret
Database: renthub
```

**Redis Commander**:
```
URL: http://localhost:8081
```

**MailHog** (Email Testing):
```
SMTP: localhost:1025
Web UI: http://localhost:8025
```

### Working with Containers

```bash
# Backend shell access
make docker-shell-backend
# Or: docker-compose exec backend sh

# Frontend shell access
make docker-shell-frontend
# Or: docker-compose exec frontend sh

# Database access
make docker-db-shell
# Or: docker-compose exec postgres psql -U postgres -d renthub

# View logs
make docker-logs
make docker-logs-backend
make docker-logs-frontend

# Run Artisan commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan tinker

# Run npm commands
docker-compose exec frontend npm run test
docker-compose exec frontend npm run lint
```

## 🏭 Production

### Production Build

```bash
# Build production images
docker-compose build

# Start production stack
docker-compose up -d

# Optimize
make docker-optimize
```

### Production Optimizations

**Backend**:
- No dev dependencies
- Opcache enabled
- Config/route/view caching
- Autoloader optimization
- Read-only filesystem (except storage)

**Frontend**:
- Standalone output mode
- Static optimization
- Image optimization
- Minimal runtime dependencies

### Environment Configuration

**Required Environment Variables**:
```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # Generate with: php artisan key:generate
APP_URL=https://renthub.com

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=renthub
DB_USERNAME=postgres
DB_PASSWORD=<strong-password>

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=<strong-password>

# Frontend
NEXT_PUBLIC_API_URL=https://api.renthub.com
```

### SSL/TLS Configuration

1. Place SSL certificates in `docker/nginx/ssl/`:
```
docker/nginx/ssl/
├── certificate.crt
└── private.key
```

2. Uncomment HTTPS server block in `docker/nginx/conf.d/default.conf`

3. Restart Nginx:
```bash
docker-compose restart nginx
```

## 📝 Commands

### Make Commands

```bash
# Docker-specific commands
make docker-build          # Build containers
make docker-up            # Start all services
make docker-dev           # Start dev environment
make docker-down          # Stop all services
make docker-restart       # Restart services
make docker-logs          # View all logs
make docker-shell-backend # Access backend shell
make docker-shell-frontend # Access frontend shell
make docker-migrate       # Run migrations
make docker-cache-clear   # Clear caches
make docker-optimize      # Optimize app
make docker-clean         # Clean containers/volumes
make docker-ps            # Show running containers
make docker-stats         # Show container stats
```

### Docker Compose Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f [service]

# Execute commands
docker-compose exec [service] [command]

# Scale services
docker-compose up -d --scale queue=3

# Rebuild services
docker-compose up -d --build

# Stop and remove
docker-compose down

# Stop, remove, and delete volumes
docker-compose down -v
```

## 🔧 Troubleshooting

### Common Issues

**1. Port already in use**
```bash
# Check what's using the port
netstat -ano | findstr :3000
netstat -ano | findstr :80

# Change port in .env
FRONTEND_PORT=3001
NGINX_HTTP_PORT=8080
```

**2. Database connection failed**
```bash
# Check if PostgreSQL is healthy
docker-compose ps postgres

# View logs
docker-compose logs postgres

# Restart database
docker-compose restart postgres
```

**3. Permission issues**
```bash
# Fix storage permissions (inside container)
docker-compose exec backend chmod -R 775 storage
docker-compose exec backend chown -R www-data:www-data storage
```

**4. Frontend won't start**
```bash
# Check logs
docker-compose logs frontend

# Rebuild
docker-compose up -d --build frontend

# Clear node_modules
docker-compose exec frontend rm -rf node_modules
docker-compose exec frontend npm install
```

**5. Redis connection failed**
```bash
# Test Redis connection
docker-compose exec redis redis-cli -a secret ping

# Restart Redis
docker-compose restart redis
```

### Health Checks

```bash
# Check all container health
docker-compose ps

# Manual health checks
curl http://localhost/health
docker-compose exec postgres pg_isready
docker-compose exec redis redis-cli -a secret ping
```

### Performance Tuning

**Increase Resources**:
```yaml
# docker-compose.yml
services:
  backend:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
```

**Database Connection Pool**:
```env
# .env
DB_POOL_MIN=2
DB_POOL_MAX=20
```

**Redis Memory**:
```yaml
# docker-compose.yml
redis:
  command: redis-server --maxmemory 256mb --maxmemory-policy allkeys-lru
```

### Logs and Monitoring

```bash
# View specific service logs
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f nginx

# Follow last 100 lines
docker-compose logs --tail=100 -f

# Export logs
docker-compose logs > logs.txt

# Container stats
docker stats
```

## 🔐 Security

### Best Practices

1. **Never commit .env files**
2. **Use strong passwords for databases**
3. **Keep images updated**: `docker-compose pull`
4. **Run security scans**: `docker scan renthub-backend`
5. **Use non-root users** (already configured)
6. **Limit container resources**
7. **Enable SSL/TLS in production**
8. **Use secrets management** for sensitive data

### Secrets Management

For production, use Docker secrets:
```yaml
services:
  backend:
    secrets:
      - db_password
      - app_key

secrets:
  db_password:
    external: true
  app_key:
    external: true
```

## 📊 Monitoring

### Container Monitoring

```bash
# Real-time stats
docker stats

# Disk usage
docker system df

# Detailed inspection
docker inspect renthub-backend
```

### Application Monitoring

Consider adding:
- **Prometheus** for metrics
- **Grafana** for visualization
- **Loki** for log aggregation
- **Jaeger** for tracing

## 🚢 Deployment

### CI/CD Integration

Example GitHub Actions:
```yaml
- name: Build Docker images
  run: docker-compose build

- name: Push to registry
  run: |
    docker tag renthub-backend registry.example.com/renthub-backend
    docker push registry.example.com/renthub-backend
```

### Production Checklist

- [ ] SSL certificates configured
- [ ] Environment variables set
- [ ] Database backups configured
- [ ] Resource limits set
- [ ] Health checks passing
- [ ] Monitoring enabled
- [ ] Logs aggregated
- [ ] Security scan passed

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Next.js Deployment](https://nextjs.org/docs/deployment)

## ✅ Status

- [x] Docker containerization
- [ ] Kubernetes orchestration (next)
- [ ] CI/CD improvements (next)
- [ ] Blue-green deployment (next)

---

**Next Steps**: Proceed with Kubernetes orchestration setup.
