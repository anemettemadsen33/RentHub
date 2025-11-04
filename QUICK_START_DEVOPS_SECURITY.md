# ⚡ Quick Start Guide - DevOps, Security & Performance

## 🚀 5-Minute Setup

### 1. Security Setup (2 minutes)

```bash
# Install security packages
composer require laravel/passport firebase/php-jwt

# Run security migrations
php artisan migrate

# Install Passport OAuth
php artisan passport:install

# Generate JWT secret
php artisan key:generate
```

### 2. Performance Setup (2 minutes)

```bash
# Install Redis
# Windows (using Chocolatey):
choco install redis-64

# Start Redis
redis-server

# Configure Laravel cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start queue workers
php artisan queue:work redis --queue=high,default,low
```

### 3. Monitoring Setup (1 minute)

```bash
# Install Telescope (development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

## 🔐 Security Quick Commands

### API Key Management
```php
// Generate API key
$apiKey = ApiKey::generate($userId, 'My App', 30); // Expires in 30 days

// Validate API key
$key = ApiKey::where('key', $request->header('X-API-Key'))->first();
if ($key && $key->isValid()) {
    // Valid key
}
```

### JWT Token Management
```php
// Generate tokens
$jwtService = app(JWTService::class);
$accessToken = $jwtService->generateAccessToken($user);
$refreshToken = $jwtService->generateRefreshToken($user);

// Refresh token
$tokens = $jwtService->refreshAccessToken($refreshToken);
```

### Role & Permission Check
```php
// Check role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check permission
if ($user->hasPermission('properties.delete')) {
    // User can delete properties
}

// Assign role
$user->assignRole('host');
```

### Data Encryption
```php
// Using Encryptable trait
class Booking extends Model
{
    use Encryptable;
    
    protected $encryptable = [
        'credit_card_last_four',
        'guest_phone_number'
    ];
}

// Data is automatically encrypted/decrypted
$booking->credit_card_last_four = '1234'; // Encrypted in DB
echo $booking->credit_card_last_four; // Decrypted: '1234'
```

---

## ⚡ Performance Quick Commands

### Caching
```php
// Cache a query result
$properties = Cache::remember('featured_properties', 3600, function () {
    return Property::where('is_featured', true)->get();
});

// Cache with tags
Cache::tags(['properties', 'featured'])->remember('key', 3600, fn() => $data);

// Invalidate cache
Cache::tags(['properties'])->flush();

// Use Cacheable trait
$property = Property::cached($id); // Auto-cached for 1 hour
```

### Query Optimization
```php
// ❌ Bad: N+1 queries
$properties = Property::all();
foreach ($properties as $property) {
    echo $property->user->name; // Extra query per property
}

// ✅ Good: Eager loading
$properties = Property::with('user')->get();
foreach ($properties as $property) {
    echo $property->user->name; // No extra queries
}

// ✅ Better: Select only needed columns
$properties = Property::select('id', 'title', 'price')->with('user:id,name')->get();
```

### Queue Jobs
```php
// Dispatch to specific queue
ProcessPayment::dispatch($booking)->onQueue('high');

// Delay job
SendEmail::dispatch($user)->delay(now()->addMinutes(5));

// Chain jobs
Bus::chain([
    new ProcessPayment($booking),
    new SendConfirmation($booking),
    new NotifyHost($booking),
])->dispatch();
```

---

## 🔍 Monitoring Quick Commands

### Check Application Health
```bash
# Health check endpoints
curl https://api.renthub.com/health
curl https://api.renthub.com/health/database
curl https://api.renthub.com/health/redis
curl https://api.renthub.com/health/queue
```

### View Logs
```bash
# Tail Laravel logs
tail -f storage/logs/laravel.log

# View specific log level
tail -f storage/logs/laravel.log | grep ERROR

# Kubernetes logs
kubectl logs -f deployment/renthub-stable -n production

# Follow logs from multiple pods
kubectl logs -f -l app=renthub -n production --all-containers=true
```

### Performance Monitoring
```bash
# Analyze slow queries
php artisan db:analyze-indexes

# View Telescope
# Open: http://localhost/telescope

# Check Redis stats
redis-cli INFO stats

# Monitor queue
php artisan queue:monitor redis:default,redis:high
```

---

## 🚀 Deployment Quick Commands

### Local Development
```bash
# Start all services
docker-compose up -d

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start queue workers
php artisan queue:work

# Start dev server
php artisan serve
```

### Staging Deployment
```bash
# Deploy to staging
git push origin develop

# Manual deployment
kubectl apply -f k8s/staging/

# Check deployment status
kubectl rollout status deployment/renthub-green -n staging

# Run smoke tests
./scripts/smoke-test.sh green staging
```

### Production Deployment
```bash
# Deploy to production (triggers canary)
git push origin main

# Monitor canary
./scripts/monitor-canary.sh 10

# Analyze canary
./scripts/analyze-canary.sh

# Manual rollback if needed
kubectl rollout undo deployment/renthub-stable -n production
```

### Kubernetes Commands
```bash
# Get pods
kubectl get pods -n production

# Describe pod
kubectl describe pod <pod-name> -n production

# Execute command in pod
kubectl exec -it <pod-name> -n production -- php artisan cache:clear

# View pod logs
kubectl logs <pod-name> -n production

# Port forward to service
kubectl port-forward svc/renthub-service 8080:80 -n production

# Scale deployment
kubectl scale deployment renthub-stable --replicas=5 -n production

# Update image
kubectl set image deployment/renthub-stable renthub=ghcr.io/renthub/renthub:v1.2.0 -n production
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Redis Connection Error
```bash
# Check Redis is running
redis-cli ping
# Should return: PONG

# Check Redis config in .env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Restart Redis
sudo service redis-server restart
```

#### 2. Queue Not Processing
```bash
# Check queue workers
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all
```

#### 3. Slow Queries
```bash
# Enable query logging
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());

# Check missing indexes
php artisan db:analyze-indexes

# View slow queries in MySQL
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;
```

#### 4. High Memory Usage
```bash
# Check memory usage
kubectl top pods -n production

# Check PHP memory limit
php -i | grep memory_limit

# Increase memory limit in php.ini
memory_limit = 512M

# Optimize Composer autoload
composer dump-autoload --optimize
```

#### 5. SSL/TLS Issues
```bash
# Check certificate
openssl s_client -connect renthub.com:443

# Renew Let's Encrypt certificate
certbot renew

# Test SSL configuration
curl -I https://renthub.com
```

---

## 📊 Quick Metrics

### Check System Performance
```bash
# Application metrics
curl http://prometheus.renthub.com/api/v1/query?query=rate(http_requests_total[5m])

# Error rate
curl http://prometheus.renthub.com/api/v1/query?query=rate(http_requests_total{status=~"5.."}[5m])

# Response time (P95)
curl http://prometheus.renthub.com/api/v1/query?query=histogram_quantile(0.95,rate(http_request_duration_seconds_bucket[5m]))

# Database connections
curl http://prometheus.renthub.com/api/v1/query?query=mysql_global_status_threads_connected

# Redis hit rate
curl http://prometheus.renthub.com/api/v1/query?query=rate(redis_keyspace_hits_total[5m])/(rate(redis_keyspace_hits_total[5m])+rate(redis_keyspace_misses_total[5m]))
```

---

## 🔒 Security Checklist

### Before Deployment
- [ ] All secrets in environment variables (not in code)
- [ ] HTTPS/TLS enabled
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] Input validation on all endpoints
- [ ] SQL injection protection verified
- [ ] XSS protection enabled
- [ ] CSRF protection enabled
- [ ] Authentication required on protected routes
- [ ] API keys validated
- [ ] File upload validation
- [ ] Error messages don't expose sensitive info

### API Security Headers
```php
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'
Referrer-Policy: strict-origin-when-cross-origin
```

---

## ⚡ Performance Checklist

### Before Deployment
- [ ] Database queries optimized (no N+1)
- [ ] Indexes added for common queries
- [ ] Caching enabled (Redis)
- [ ] Assets minified and compressed
- [ ] Images optimized (WebP format)
- [ ] CDN configured
- [ ] Lazy loading implemented
- [ ] OPcache enabled
- [ ] Queue workers running
- [ ] Connection pooling configured

### Performance Targets
- Response time P95: < 500ms
- Response time P99: < 1s
- Error rate: < 0.1%
- Cache hit rate: > 90%
- Uptime: 99.95%

---

## 📞 Emergency Contacts

### Critical Issues (P1)
- **Slack**: #critical-alerts
- **PagerDuty**: https://renthub.pagerduty.com
- **On-Call**: oncall@renthub.com

### Security Issues
- **Email**: security@renthub.com
- **Slack**: #security-alerts

### General Support
- **Slack**: #renthub-support
- **Email**: support@renthub.com

---

## 🔗 Quick Links

- **Grafana**: https://grafana.renthub.com
- **Prometheus**: https://prometheus.renthub.com
- **Kibana**: https://kibana.renthub.com
- **Telescope**: http://localhost/telescope
- **API Docs**: https://api.renthub.com/docs
- **Status Page**: https://status.renthub.com

---

## 📚 Useful Commands Reference

### Laravel Artisan
```bash
php artisan cache:clear          # Clear application cache
php artisan config:cache         # Cache configuration
php artisan route:cache          # Cache routes
php artisan view:cache           # Cache views
php artisan optimize             # Optimize application
php artisan queue:work           # Start queue worker
php artisan schedule:run         # Run scheduled tasks
php artisan migrate              # Run migrations
php artisan db:seed              # Seed database
php artisan telescope:prune      # Prune Telescope data
```

### Docker
```bash
docker-compose up -d             # Start containers
docker-compose down              # Stop containers
docker-compose logs -f           # View logs
docker-compose exec app bash    # Execute bash in container
docker-compose ps                # List containers
docker system prune -a           # Clean up Docker
```

### Git
```bash
git status                       # Check status
git add .                        # Stage all changes
git commit -m "message"          # Commit changes
git push origin main             # Push to main
git pull origin main             # Pull from main
git checkout -b feature/new      # Create new branch
git merge develop                # Merge develop into current
```

---

**Pro Tip**: Bookmark this page for quick reference during development! 🎯

**Last Updated**: November 3, 2025

