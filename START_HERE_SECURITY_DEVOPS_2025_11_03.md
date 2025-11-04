# 🚀 Quick Start - Security & DevOps Implementation

**Welcome to the RentHub Security & DevOps Setup!**

This guide will help you get started with the newly implemented security features, DevOps infrastructure, and monitoring systems.

---

## 📦 What's New?

### 🔐 Security Features
✅ **OAuth 2.0** - Enterprise authentication  
✅ **JWT Tokens** - Secure token management  
✅ **GDPR Compliance** - Data privacy & rights  
✅ **Data Encryption** - AES-256 encryption  
✅ **Security Auditing** - Complete activity logging  
✅ **Rate Limiting** - DDoS protection  
✅ **Security Headers** - CSP, HSTS, and more  

### 🚀 DevOps Infrastructure
✅ **CI/CD Pipeline** - Automated deployments  
✅ **Blue-Green Deployment** - Zero-downtime updates  
✅ **Canary Releases** - Gradual rollouts  
✅ **Terraform** - Infrastructure as Code  
✅ **Kubernetes** - Container orchestration  
✅ **Monitoring** - Prometheus + Grafana  

---

## ⚡ Quick Install

### Option 1: Automated Install (Recommended)

**Windows**:
```powershell
.\install-security-complete.ps1
```

**Linux/Mac**:
```bash
chmod +x install-security-complete.sh
./install-security-complete.sh
```

### Option 2: Manual Install

```bash
# 1. Backend Dependencies
cd backend
composer install
composer require firebase/php-jwt

# 2. Environment Setup
cp .env.example .env
php artisan key:generate

# Add JWT secret to .env
JWT_SECRET=$(openssl rand -base64 32)

# 3. Database Migrations
php artisan migrate

# 4. Start Monitoring Stack
cd ../docker/monitoring
docker-compose -f docker-compose.monitoring.yml up -d

# 5. Frontend Dependencies
cd ../../frontend
npm install
```

---

## 🎯 Testing the Features

### 1. Test OAuth 2.0

```bash
# Get authorization code (requires authenticated user)
curl -X POST http://localhost:8000/api/v1/oauth/authorize \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "renthub_web",
    "redirect_uri": "http://localhost:3000/callback",
    "response_type": "code",
    "scope": "read write"
  }'

# Exchange code for tokens
curl -X POST http://localhost:8000/api/v1/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "authorization_code",
    "code": "AUTHORIZATION_CODE",
    "client_id": "renthub_web",
    "client_secret": "YOUR_CLIENT_SECRET"
  }'

# Refresh token
curl -X POST http://localhost:8000/api/v1/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "refresh_token",
    "refresh_token": "YOUR_REFRESH_TOKEN",
    "client_id": "renthub_web",
    "client_secret": "YOUR_CLIENT_SECRET"
  }'
```

### 2. Test GDPR Features

```bash
# Export user data
curl -X POST http://localhost:8000/api/v1/gdpr/export \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get consent status
curl -X GET http://localhost:8000/api/v1/gdpr/consent \
  -H "Authorization: Bearer YOUR_TOKEN"

# Update consent
curl -X PUT http://localhost:8000/api/v1/gdpr/consent \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "marketing_emails": true,
    "analytics_tracking": true,
    "third_party_sharing": false
  }'

# Request account deletion
curl -X DELETE http://localhost:8000/api/v1/gdpr/forget-me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Security Audit (Admin Only)

```bash
# Get audit logs
curl -X GET "http://localhost:8000/api/v1/security/audit-logs?start_date=2025-11-01&end_date=2025-11-03" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Detect anomalies
curl -X GET http://localhost:8000/api/v1/security/anomalies \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Get compliance report
curl -X GET http://localhost:8000/api/v1/gdpr/compliance-report \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### 4. Test Rate Limiting

```bash
# This should fail after 5 attempts
for i in {1..10}; do
  curl -X POST http://localhost:8000/api/v1/login \
    -H "Content-Type: application/json" \
    -d '{
      "email": "test@example.com",
      "password": "wrong_password"
    }'
  echo "\nAttempt $i"
  sleep 1
done
```

---

## 📊 Access Monitoring Dashboards

### Prometheus
**URL**: http://localhost:9090  
**Purpose**: Metrics collection & queries

**Try these queries**:
```promql
# Request rate
rate(http_requests_total[5m])

# Error rate
rate(http_requests_total{status=~"5.."}[5m])

# API latency (p95)
histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))

# CPU usage
100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)
```

### Grafana
**URL**: http://localhost:3001  
**Default Login**: admin / admin

**Pre-configured Dashboards**:
1. System Overview
2. Application Metrics
3. Database Performance
4. Redis Performance
5. Security Dashboard
6. Business Metrics

### Alertmanager
**URL**: http://localhost:9093  
**Purpose**: Alert management & routing

---

## 🔧 Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# JWT Configuration
JWT_SECRET=your-jwt-secret-key

# OAuth 2.0
OAUTH_CLIENT_ID=renthub_web
OAUTH_CLIENT_SECRET=your-client-secret

# Monitoring
PROMETHEUS_ENABLED=true
GRAFANA_ADMIN_USER=admin
GRAFANA_ADMIN_PASSWORD=your-secure-password

# Alerting
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
PAGERDUTY_SERVICE_KEY=your-pagerduty-key

# GDPR
DATA_RETENTION_DAYS=90
GDPR_COMPLIANCE_MODE=true
```

### 2. Slack Notifications

1. Create a Slack webhook: https://api.slack.com/messaging/webhooks
2. Add to `.env`: `SLACK_WEBHOOK_URL=your-webhook-url`
3. Create channels:
   - `#renthub-alerts` - General alerts
   - `#renthub-critical` - Critical alerts
   - `#renthub-warnings` - Warning alerts

### 3. Security Headers

Already configured in `SecurityHeadersMiddleware.php`:

```
Content-Security-Policy ✅
Strict-Transport-Security ✅
X-Frame-Options ✅
X-Content-Type-Options ✅
X-XSS-Protection ✅
Referrer-Policy ✅
Permissions-Policy ✅
```

---

## 📚 API Documentation

### OAuth 2.0 Endpoints

```
POST /api/v1/oauth/authorize     - Get authorization code (protected)
POST /api/v1/oauth/token         - Exchange code/refresh token (public)
POST /api/v1/oauth/revoke        - Revoke token (protected)
POST /api/v1/oauth/introspect    - Validate token (protected)
```

### GDPR Endpoints

```
POST   /api/v1/gdpr/export              - Export user data (protected)
DELETE /api/v1/gdpr/forget-me           - Request deletion (protected)
GET    /api/v1/gdpr/consent             - Get consent status (protected)
PUT    /api/v1/gdpr/consent             - Update consent (protected)
GET    /api/v1/gdpr/data-protection     - Get protection info (public)
GET    /api/v1/gdpr/compliance-report   - Compliance report (admin)
```

### Security Audit Endpoints

```
GET    /api/v1/security/audit-logs   - Get audit logs (admin)
GET    /api/v1/security/anomalies    - Detect anomalies (admin)
POST   /api/v1/security/log          - Log event (admin)
DELETE /api/v1/security/cleanup      - Cleanup old logs (admin)
```

---

## 🎨 Frontend Integration Examples

### OAuth Login Flow

```javascript
// 1. Redirect to authorization endpoint
const authorizeUrl = `/api/v1/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&response_type=code&scope=read write`;
window.location.href = authorizeUrl;

// 2. Handle callback (after user authorizes)
const urlParams = new URLSearchParams(window.location.search);
const code = urlParams.get('code');

// 3. Exchange code for tokens
const response = await fetch('/api/v1/oauth/token', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    grant_type: 'authorization_code',
    code,
    client_id: clientId,
    client_secret: clientSecret
  })
});

const { access_token, refresh_token } = await response.json();
```

### GDPR Consent Management

```javascript
// Get current consent status
const consent = await fetch('/api/v1/gdpr/consent', {
  headers: { 'Authorization': `Bearer ${token}` }
}).then(r => r.json());

// Update consent
await fetch('/api/v1/gdpr/consent', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    marketing_emails: true,
    analytics_tracking: false,
    third_party_sharing: false
  })
});

// Export data
const exportData = await fetch('/api/v1/gdpr/export', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
}).then(r => r.json());
```

### Security Event Logging (Admin Dashboard)

```javascript
// Fetch audit logs
const logs = await fetch('/api/v1/security/audit-logs?start_date=2025-11-01&end_date=2025-11-03', {
  headers: { 'Authorization': `Bearer ${adminToken}` }
}).then(r => r.json());

// Detect anomalies
const anomalies = await fetch('/api/v1/security/anomalies', {
  headers: { 'Authorization': `Bearer ${adminToken}` }
}).then(r => r.json());

// Display in dashboard
console.log('Security Events:', logs);
console.log('Anomalies Detected:', anomalies);
```

---

## 🚀 Deployment

### Using GitHub Actions (Automated)

The CI/CD pipeline automatically runs on:
- Push to `main` branch
- Pull requests to `main`

**Deployment Strategies**:

1. **Blue-Green** (recommended for production):
```yaml
# Triggered manually or on release tag
git tag v1.0.0
git push origin v1.0.0
```

2. **Canary** (gradual rollout):
```yaml
# Automatically deploys with gradual traffic shift
# 10% → 25% → 50% → 100%
```

### Manual Deployment

```bash
# 1. Build Docker images
docker build -t renthub-backend:latest ./backend
docker build -t renthub-frontend:latest ./frontend

# 2. Push to registry
docker push your-registry/renthub-backend:latest
docker push your-registry/renthub-frontend:latest

# 3. Deploy to Kubernetes
kubectl apply -f k8s/production-deployment.yaml

# 4. Check deployment status
kubectl get deployments
kubectl get pods
```

### Using Terraform

```bash
# Initialize Terraform
cd terraform
terraform init

# Plan changes
terraform plan -var-file="production.tfvars"

# Apply infrastructure
terraform apply -var-file="production.tfvars"

# Check resources
terraform show
```

---

## 🔍 Monitoring & Troubleshooting

### Check Application Health

```bash
# Health check
curl http://localhost:8000/api/health

# Readiness check
curl http://localhost:8000/api/health/readiness

# Liveness check
curl http://localhost:8000/api/health/liveness

# Metrics
curl http://localhost:8000/api/metrics
```

### View Logs

```bash
# Backend logs
tail -f backend/storage/logs/laravel.log

# Docker logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Kubernetes logs
kubectl logs -f deployment/renthub-backend
kubectl logs -f deployment/renthub-frontend
```

### Common Issues

**Issue**: JWT token not working  
**Solution**: Make sure `JWT_SECRET` is set in `.env`

**Issue**: Rate limiting too strict  
**Solution**: Adjust limits in `RateLimitMiddleware.php`

**Issue**: Monitoring not showing data  
**Solution**: Check if exporters are running:
```bash
docker-compose -f docker/monitoring/docker-compose.monitoring.yml ps
```

**Issue**: OAuth authorization fails  
**Solution**: Verify OAuth client exists in database:
```bash
php artisan tinker
>>> App\Models\OAuthClient::all()
```

---

## 📖 Additional Resources

### Documentation
- [Complete Implementation Guide](./COMPLETE_SECURITY_DEVOPS_IMPLEMENTATION_2025_11_03.md)
- [Implementation Status](./FINAL_IMPLEMENTATION_STATUS_2025_11_03.md)
- [Security Guide](./SECURITY_GUIDE.md)
- [DevOps Guide](./DEVOPS_GUIDE.md)
- [API Endpoints](./API_ENDPOINTS.md)

### Tools & Services
- **Prometheus**: https://prometheus.io/docs/
- **Grafana**: https://grafana.com/docs/
- **Kubernetes**: https://kubernetes.io/docs/
- **Terraform**: https://www.terraform.io/docs/
- **Docker**: https://docs.docker.com/

### Support
- **Security Issues**: security@renthub.com
- **DevOps Support**: devops@renthub.com
- **General Support**: support@renthub.com

---

## ✅ Checklist

### Before Going to Production

- [ ] Update `.env` with production values
- [ ] Set strong passwords for Grafana/databases
- [ ] Configure SSL certificates
- [ ] Set up Slack webhooks
- [ ] Configure backup strategy
- [ ] Run security penetration tests
- [ ] Perform load testing
- [ ] Review and test alert rules
- [ ] Document emergency procedures
- [ ] Train team on new features

### Regular Maintenance

- [ ] Review security audit logs (daily)
- [ ] Check monitoring dashboards (daily)
- [ ] Update dependencies (weekly)
- [ ] Review and rotate secrets (monthly)
- [ ] Conduct security review (monthly)
- [ ] Test disaster recovery (quarterly)

---

## 🎉 You're All Set!

Your RentHub platform now has enterprise-grade security and DevOps infrastructure. Here's what you can do next:

1. ✅ Explore the monitoring dashboards
2. ✅ Test the security features
3. ✅ Review the API documentation
4. ✅ Set up production environment
5. ✅ Deploy with confidence!

**Need help?** Check the documentation or contact support.

---

**Last Updated**: November 3, 2025  
**Version**: 1.0.0  
**Status**: Ready to Deploy 🚀
