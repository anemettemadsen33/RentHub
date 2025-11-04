# 🚀 Quick Start Guide: DevOps, Security, Performance & UI/UX

## ⚡ 5-Minute Setup

### Prerequisites
```bash
✅ Docker & Docker Compose
✅ Kubernetes CLI (kubectl)
✅ Terraform >= 1.5.0
✅ AWS CLI configured
✅ GitHub account with Actions enabled
✅ Node.js >= 18
✅ PHP >= 8.2
```

---

## 📋 Step-by-Step Implementation

### 1️⃣ Infrastructure Setup (10 minutes)

```bash
# Navigate to Terraform directory
cd terraform

# Copy example variables
cp terraform.tfvars.example terraform.tfvars

# Edit with your values
nano terraform.tfvars

# Initialize Terraform
terraform init

# Plan infrastructure
terraform plan -out=tfplan

# Apply (creates AWS resources)
terraform apply tfplan
```

**Expected Output**: VPC, EKS, RDS, Redis, S3, CloudFront, ALB

---

### 2️⃣ Kubernetes Configuration (5 minutes)

```bash
# Update kubeconfig
aws eks update-kubeconfig --region us-east-1 --name renthub-production

# Create namespaces
kubectl create namespace production
kubectl create namespace monitoring

# Deploy monitoring stack
kubectl apply -f k8s/monitoring/

# Deploy application (Blue-Green)
kubectl apply -f k8s/blue-green-deployment.yaml

# Verify deployments
kubectl get pods -n production
kubectl get pods -n monitoring
```

---

### 3️⃣ GitHub Actions Setup (3 minutes)

**Set Repository Secrets**:
```
Settings → Secrets and Variables → Actions → New repository secret
```

**Required Secrets**:
```
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_REGION
SNYK_TOKEN
GITGUARDIAN_API_KEY
SLACK_WEBHOOK
```

**Test Pipeline**:
```bash
# Push to trigger workflow
git add .
git commit -m "feat: setup CI/CD pipeline"
git push origin main
```

---

### 4️⃣ Backend Security Setup (5 minutes)

```bash
cd backend

# Install dependencies
composer install

# Run migrations
php artisan migrate

# Register middleware in app/Http/Kernel.php
# Add to $middleware array:
\App\Http\Middleware\SecurityHeaders::class,

# Add to $routeMiddleware:
'rate_limit' => \App\Http\Middleware\RateLimitMiddleware::class,

# Clear cache
php artisan config:cache
php artisan route:cache
```

**Environment Variables**:
```env
# Add to .env
CACHE_API_RESPONSES=true
CACHE_API_TTL=3600
RESPONSE_COMPRESSION=true
COMPRESSION_ALGORITHM=gzip
```

---

### 5️⃣ Frontend Design System (2 minutes)

```bash
cd frontend

# Import design system in main CSS/SCSS file
@import './src/styles/design-system.css';

# Or in your main.tsx/jsx
import './src/styles/design-system.css';

# Use components
<button className="btn btn-primary btn-lg">
  Get Started
</button>

<div className="card">
  <div className="card-header">
    <h3 className="heading-3">Welcome</h3>
  </div>
  <div className="card-body">
    <p className="body-base">Content here</p>
  </div>
</div>
```

---

## 🔍 Verification Checklist

### ✅ Infrastructure
```bash
# Check EKS cluster
kubectl cluster-info

# Check RDS
aws rds describe-db-instances --db-instance-identifier renthub-production-db

# Check Redis
aws elasticache describe-cache-clusters --cache-cluster-id renthub-production-redis

# Check S3
aws s3 ls | grep renthub
```

### ✅ Deployments
```bash
# Check pods
kubectl get pods -n production

# Check services
kubectl get svc -n production

# Check ingress
kubectl get ingress -n production

# View logs
kubectl logs -f deployment/renthub-blue -n production
```

### ✅ Monitoring
```bash
# Port forward Grafana
kubectl port-forward -n monitoring svc/grafana 3000:80

# Open http://localhost:3000
# Default credentials: admin / (from secret)

# Port forward Prometheus
kubectl port-forward -n monitoring svc/prometheus 9090:9090

# Open http://localhost:9090
```

### ✅ Security
```bash
# Test security headers
curl -I https://your-domain.com

# Expected headers:
# Content-Security-Policy: ...
# Strict-Transport-Security: ...
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff

# Test rate limiting
for i in {1..100}; do curl https://your-domain.com/api/endpoint; done

# Should return 429 after limit
```

### ✅ Performance
```bash
# Test cache
php artisan tinker
>>> app(App\Services\CacheService::class)->getStats()

# Expected output:
# [
#   "hits" => 1234,
#   "misses" => 567,
#   "hit_rate" => 68.5
# ]

# Test compression
curl -H "Accept-Encoding: gzip" -I https://your-domain.com

# Should see: Content-Encoding: gzip
```

---

## 🎯 Common Commands

### Deployment Commands

```bash
# Deploy to staging
kubectl set image deployment/renthub-app \
  renthub-app=ghcr.io/yourusername/renthub:latest \
  --namespace=staging

# Blue-Green switch
kubectl patch service renthub-service -n production \
  -p '{"spec":{"selector":{"version":"green"}}}'

# Canary deployment (10% traffic)
kubectl scale deployment/renthub-canary --replicas=1 -n production
kubectl scale deployment/renthub-stable --replicas=9 -n production

# Rollback
kubectl rollout undo deployment/renthub-app -n production
```

### Cache Commands

```bash
# Clear all cache
php artisan cache:clear

# Clear specific tags
php artisan cache:forget-tags api,responses

# Warm cache
php artisan cache:warm

# View cache stats
php artisan cache:stats
```

### Monitoring Commands

```bash
# View metrics
kubectl top nodes
kubectl top pods -n production

# View logs
kubectl logs -f -l app=renthub -n production

# View events
kubectl get events -n production --sort-by='.lastTimestamp'

# Check resource usage
kubectl describe pod <pod-name> -n production
```

---

## 🐛 Troubleshooting

### Issue: Pods not starting

```bash
# Check pod status
kubectl describe pod <pod-name> -n production

# Check logs
kubectl logs <pod-name> -n production

# Common fixes:
# 1. Check image pull secrets
# 2. Verify resource limits
# 3. Check persistent volume claims
# 4. Verify environment variables
```

### Issue: High response time

```bash
# Check cache hit rate
php artisan cache:stats

# Check database connections
# In Laravel Tinker:
DB::connection()->getDoctrineConnection()->getDriver()->getConnection()->getAttribute(PDO::ATTR_CONNECTION_STATUS)

# Check Redis connection
redis-cli -h <redis-host> INFO stats

# Enable query logging
DB::enableQueryLog();
// Run your code
dd(DB::getQueryLog());
```

### Issue: Security scan failures

```bash
# Run local security scan
docker run --rm -v $(pwd):/app aquasec/trivy fs /app

# Update dependencies
composer update --with-all-dependencies
npm update

# Fix vulnerabilities
npm audit fix
composer audit
```

### Issue: CI/CD pipeline failing

```bash
# Check GitHub Actions logs
# Go to: Repository → Actions → Select workflow

# Test locally with act
act -j security-scan

# Common fixes:
# 1. Update secrets in GitHub
# 2. Check Docker registry permissions
# 3. Verify AWS credentials
# 4. Review test failures
```

---

## 📊 Performance Benchmarks

### Expected Metrics

**Before Optimization**:
- Page Load: 3-5 seconds
- API Response: 200-500ms
- Database Query: 100-300ms
- Cache Hit Rate: N/A

**After Optimization**:
- Page Load: <2 seconds (60% improvement)
- API Response: 50-150ms (70% improvement)
- Database Query: 20-80ms (75% improvement)
- Cache Hit Rate: 85-90%

### Load Testing

```bash
# Install Apache Bench
apt-get install apache2-utils

# Test API endpoint
ab -n 1000 -c 10 https://your-domain.com/api/properties

# Expected results:
# Requests per second: >500
# Time per request: <20ms (mean)
# Failed requests: 0
```

---

## 🔐 Security Best Practices

### 1. Secrets Management
```bash
# Never commit secrets
# Use Kubernetes secrets
kubectl create secret generic renthub-secrets \
  --from-literal=db-password='your-password' \
  --namespace=production

# Use sealed secrets for GitOps
kubeseal --format=yaml < secret.yaml > sealed-secret.yaml
```

### 2. RBAC Setup
```bash
# Create service account
kubectl create serviceaccount renthub-sa -n production

# Create role
kubectl create role renthub-role \
  --verb=get,list,watch \
  --resource=pods,services \
  -n production

# Create role binding
kubectl create rolebinding renthub-binding \
  --role=renthub-role \
  --serviceaccount=production:renthub-sa \
  -n production
```

### 3. Network Policies
```bash
# Apply network policies
kubectl apply -f k8s/network-policies.yaml

# Verify
kubectl get networkpolicies -n production
```

---

## 📚 Additional Resources

### Documentation
- [Full Implementation Guide](./DEVOPS_SECURITY_PERFORMANCE_UI_COMPLETE.md)
- [Terraform Documentation](./terraform/README.md)
- [Kubernetes Guide](./k8s/README.md)
- [Security Guide](./SECURITY_GUIDE.md)
- [Performance Guide](./PERFORMANCE_GUIDE.md)

### Monitoring URLs
- Grafana: `http://grafana.your-domain.com`
- Prometheus: `http://prometheus.your-domain.com`
- Kubernetes Dashboard: `http://k8s.your-domain.com`

### Support Channels
- Slack: `#renthub-devops`
- Email: `devops@renthub.com`
- Documentation: `https://docs.renthub.com`

---

## ✅ Success Criteria

After completing this guide, you should have:

- ✅ Infrastructure running on AWS
- ✅ Kubernetes cluster with application deployed
- ✅ CI/CD pipeline executing successfully
- ✅ Security headers and encryption enabled
- ✅ Caching layer operational
- ✅ Monitoring dashboards accessible
- ✅ Design system integrated
- ✅ All tests passing
- ✅ Zero security vulnerabilities
- ✅ Performance metrics meeting targets

---

## 🎉 Congratulations!

You've successfully implemented:
- ✅ Advanced CI/CD pipeline
- ✅ Infrastructure as Code
- ✅ Kubernetes orchestration
- ✅ Comprehensive security
- ✅ Performance optimization
- ✅ Professional design system
- ✅ Full observability

**Your application is now production-ready! 🚀**

---

**Need Help?**
- Read the [Complete Guide](./DEVOPS_SECURITY_PERFORMANCE_UI_COMPLETE.md)
- Check [Troubleshooting](#-troubleshooting)
- Contact the DevOps team

**Version**: 2.0.0
**Last Updated**: November 3, 2025
