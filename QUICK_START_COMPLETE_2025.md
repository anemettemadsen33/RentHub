# 🚀 RentHub - Quick Start Guide
## Complete Security, Performance & Marketing Implementation

---

## ⚡ 5-Minute Quick Start

### Prerequisites
```bash
✅ PHP 8.2+
✅ Node.js 18+
✅ Composer
✅ Docker & Docker Compose
✅ Git
```

### Installation (Windows)
```powershell
# 1. Clone repository
git clone https://github.com/yourusername/renthub.git
cd renthub

# 2. Run installation script (as Administrator)
.\install-security-performance-complete.ps1

# 3. Start services
docker-compose up -d

# 4. Access application
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# Grafana: http://localhost:3001
```

### Installation (Linux/Mac)
```bash
# 1. Clone repository
git clone https://github.com/yourusername/renthub.git
cd renthub

# 2. Run installation script
chmod +x install-security-performance-complete.sh
./install-security-performance-complete.sh

# 3. Start services
docker-compose up -d

# 4. Access application
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# Grafana: http://localhost:3001
```

---

## 🔐 Security Quick Setup

### 1. Configure Environment Variables
```bash
# Copy example file
cp backend/.env.example backend/.env

# Edit .env file with your settings
nano backend/.env
```

**Essential Security Settings:**
```env
# Application
APP_KEY=base64:your-generated-key-here
APP_ENV=production
APP_DEBUG=false

# JWT Configuration
JWT_SECRET=your-jwt-secret-here
JWT_TTL=15
JWT_REFRESH_TTL=10080

# OAuth Providers
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
FACEBOOK_CLIENT_ID=your-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-facebook-client-secret

# Encryption
ENCRYPTION_KEY=your-32-character-encryption-key

# Security
RATE_LIMIT_ENABLED=true
DDOS_PROTECTION=true
SECURITY_HEADERS=true
```

### 2. Generate Keys
```bash
cd backend

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Generate encryption keys
php artisan encryption:generate
```

### 3. Enable Security Middleware
All security middleware is pre-configured in `backend/app/Http/Kernel.php`:
- ✅ Rate limiting
- ✅ CSRF protection
- ✅ XSS protection
- ✅ SQL injection prevention
- ✅ DDoS protection
- ✅ Security headers

### 4. Configure SSL/TLS
```bash
# For local development (self-signed)
./scripts/generate-ssl-cert.sh

# For production (Let's Encrypt)
./scripts/setup-letsencrypt.sh yourdomain.com
```

---

## ⚡ Performance Quick Setup

### 1. Configure Redis Cache
```env
# .env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Enable OpCache (php.ini)
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.revalidate_freq=60
opcache.validate_timestamps=0
```

### 3. Configure Database Connection Pool
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=
DB_POOL_MIN=5
DB_POOL_MAX=20
```

### 4. Setup Queue Workers
```bash
# Start queue workers
php artisan queue:work --queue=high,default,low --tries=3 --daemon

# Or use Supervisor (recommended)
sudo cp supervisor/queue-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start renthub-queue:*
```

### 5. Enable Caching
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Warm up cache
php artisan cache:warm-up
```

---

## 🐳 Docker Deployment

### Development Environment
```bash
# Start all services
docker-compose -f docker-compose.dev.yml up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Production Environment
```bash
# Build images
docker-compose build --no-cache

# Start services
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f backend frontend
```

### Services Running:
- **Backend API:** http://localhost:8000
- **Frontend:** http://localhost:3000
- **PostgreSQL:** localhost:5432
- **Redis:** localhost:6379
- **Grafana:** http://localhost:3001
- **Prometheus:** http://localhost:9090

---

## ☸️ Kubernetes Deployment

### Prerequisites
```bash
# Install kubectl
# Install helm
# Configure kubeconfig
```

### Deploy to Kubernetes
```bash
# Create namespace
kubectl create namespace renthub

# Apply configurations
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secrets.yaml

# Deploy database
kubectl apply -f k8s/postgres-statefulset.yaml
kubectl apply -f k8s/redis-statefulset.yaml

# Deploy application
kubectl apply -f k8s/backend-deployment.yaml
kubectl apply -f k8s/frontend-deployment.yaml
kubectl apply -f k8s/queue-deployment.yaml

# Deploy ingress
kubectl apply -f k8s/ingress.yaml

# Deploy monitoring
kubectl apply -f k8s/monitoring/prometheus-deployment.yaml
kubectl apply -f k8s/monitoring/grafana-deployment.yaml
kubectl apply -f k8s/monitoring/alertmanager-deployment.yaml

# Check status
kubectl get pods -n renthub
kubectl get services -n renthub
```

---

## 📊 Monitoring Setup

### Grafana Access
```
URL: http://localhost:3001 (dev) or https://grafana.yourdomain.com (prod)
Username: admin
Password: (check secrets or set in docker-compose.yml)
```

### Pre-configured Dashboards
1. **Application Overview**
   - Request rate, error rate, response times
   - Active users, bookings per hour

2. **Infrastructure**
   - CPU, memory, disk usage
   - Network traffic
   - Pod status

3. **Database Performance**
   - Query times
   - Connection pool
   - Slow queries

4. **Business Metrics**
   - Booking conversion rate
   - Revenue per day
   - User registrations

### Prometheus Access
```
URL: http://localhost:9090 (dev) or https://prometheus.yourdomain.com (prod)
```

### Alertmanager
Configure alerts in `k8s/monitoring/alertmanager-deployment.yaml`

**Alert Channels:**
- Slack: #critical-alerts, #warning-alerts
- Email: alerts@yourdomain.com
- PagerDuty: (configure in alertmanager.yml)

---

## 🧪 Testing

### Run All Tests
```bash
# Backend tests
cd backend
php artisan test
php artisan test --coverage

# Frontend tests
cd frontend
npm run test
npm run test:coverage

# E2E tests
npm run cypress:open
npm run cypress:run
```

### Security Tests
```bash
# Run security scan
./scripts/security-scan.sh

# Or via GitHub Actions
git push origin main  # Triggers CI/CD
```

---

## 🔍 Health Checks

### Application Health
```bash
# Backend health check
curl http://localhost:8000/health

# Frontend health check
curl http://localhost:3000/api/health

# Database connection
curl http://localhost:8000/api/health/database

# Redis connection
curl http://localhost:8000/api/health/redis
```

### Kubernetes Health
```bash
kubectl get pods -n renthub
kubectl describe pod <pod-name> -n renthub
kubectl logs -f <pod-name> -n renthub
```

---

## 📝 Common Commands

### Backend
```bash
# Clear cache
php artisan cache:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Generate API documentation
php artisan l5-swagger:generate

# Create admin user
php artisan user:create-admin

# Run queue worker
php artisan queue:work
```

### Frontend
```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Format code
npm run format
```

### Docker
```bash
# Rebuild images
docker-compose build --no-cache

# View logs
docker-compose logs -f <service-name>

# Execute command in container
docker-compose exec backend bash
docker-compose exec frontend sh

# Restart service
docker-compose restart <service-name>

# Stop all services
docker-compose down

# Remove volumes
docker-compose down -v
```

### Kubernetes
```bash
# Get resources
kubectl get pods -n renthub
kubectl get services -n renthub
kubectl get deployments -n renthub

# Describe resource
kubectl describe pod <pod-name> -n renthub

# View logs
kubectl logs -f <pod-name> -n renthub

# Execute command in pod
kubectl exec -it <pod-name> -n renthub -- bash

# Port forward
kubectl port-forward <pod-name> 8000:8000 -n renthub

# Scale deployment
kubectl scale deployment backend --replicas=5 -n renthub

# Restart deployment
kubectl rollout restart deployment backend -n renthub

# Check rollout status
kubectl rollout status deployment backend -n renthub
```

---

## 🐛 Troubleshooting

### Backend Issues

#### Database Connection Error
```bash
# Check database is running
docker-compose ps postgres

# Check credentials in .env
cat backend/.env | grep DB_

# Test connection
php artisan tinker
> DB::connection()->getPdo();
```

#### Redis Connection Error
```bash
# Check Redis is running
docker-compose ps redis

# Test connection
redis-cli ping
```

#### Permission Errors
```bash
# Fix storage permissions
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache

# Or in Docker
docker-compose exec backend chmod -R 775 storage bootstrap/cache
```

### Frontend Issues

#### Build Errors
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear build cache
rm -rf .next (Next.js) or dist (Vite)
npm run build
```

#### Environment Variables Not Loading
```bash
# Check .env.local exists
ls frontend/.env.local

# Restart dev server
npm run dev
```

### Docker Issues

#### Container Not Starting
```bash
# View logs
docker-compose logs <service-name>

# Check container status
docker-compose ps

# Restart container
docker-compose restart <service-name>
```

#### Port Already in Use
```bash
# Find process using port
lsof -i :8000  # macOS/Linux
netstat -ano | findstr :8000  # Windows

# Kill process or change port in docker-compose.yml
```

### Kubernetes Issues

#### Pod Not Starting
```bash
# Check pod events
kubectl describe pod <pod-name> -n renthub

# Check logs
kubectl logs <pod-name> -n renthub

# Check resource limits
kubectl top pods -n renthub
```

#### Service Not Accessible
```bash
# Check service endpoints
kubectl get endpoints -n renthub

# Check ingress
kubectl describe ingress -n renthub

# Test service internally
kubectl run test --image=busybox -it --rm -- wget -O- http://backend:8000/health
```

---

## 📚 Documentation Links

- [Complete Implementation Guide](./SECURITY_PERFORMANCE_MARKETING_COMPLETE_2025_11_03.md)
- [Security Guide](./COMPREHENSIVE_SECURITY_GUIDE.md)
- [Performance Guide](./ADVANCED_PERFORMANCE_OPTIMIZATION.md)
- [DevOps Guide](./DEVOPS_COMPLETE.md)
- [API Documentation](./API_ENDPOINTS.md)
- [Deployment Guide](./DEPLOYMENT.md)
- [Contributing Guide](./CONTRIBUTING.md)

---

## 🆘 Getting Help

### Support Channels
- **Email:** support@renthub.com
- **Slack:** #renthub-support
- **GitHub Issues:** https://github.com/yourusername/renthub/issues
- **Documentation:** https://docs.renthub.com

### Reporting Security Issues
Please report security vulnerabilities to: security@renthub.com

---

## ✅ Post-Installation Checklist

- [ ] Environment variables configured
- [ ] SSL certificates installed
- [ ] Database migrated and seeded
- [ ] Redis cache working
- [ ] Queue workers running
- [ ] Monitoring dashboards accessible
- [ ] Alerts configured
- [ ] Backups scheduled
- [ ] Security scan passed
- [ ] Performance benchmarks met
- [ ] Documentation reviewed
- [ ] Team trained

---

**Congratulations! Your RentHub platform is ready! 🎉**

For production deployment, please review the [Deployment Checklist](./DEPLOYMENT_CHECKLIST.md).
