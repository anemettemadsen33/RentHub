# 🚀 Quick Start: Advanced Security & DevOps

Fast setup guide for implementing all security and DevOps features.

## ⚡ 5-Minute Setup

### 1. Database Migrations

```bash
cd backend

# Run new security migrations
php artisan migrate

# Verify tables created
php artisan tinker
>>> Schema::hasTable('refresh_tokens')
>>> Schema::hasTable('security_incidents')
>>> Schema::hasTable('api_keys')
```

### 2. Environment Configuration

Add to `.env`:

```env
# Advanced Security
RBAC_CACHE_TTL=300
JWT_REFRESH_TTL=2592000
API_GATEWAY_ENABLED=true
SECURITY_INCIDENT_AUTO_RESPONSE=true

# API Gateway
API_REQUIRE_SIGNATURE=false
API_MAX_REQUEST_SIZE=10485760

# Monitoring
PROMETHEUS_ENABLED=true
GRAFANA_ENABLED=true

# Incident Response
PAGERDUTY_ENABLED=false
PAGERDUTY_ROUTING_KEY=your_key_here
```

### 3. Register Middleware

Update `backend/app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // Existing middleware...
        \App\Http\Middleware\APIGatewayMiddleware::class,
    ],
];

protected $routeMiddleware = [
    // Existing...
    'rbac' => \App\Http\Middleware\AdvancedRBACMiddleware::class,
];
```

### 4. Update Routes

```php
// backend/routes/api.php

Route::middleware(['auth:api', 'api.gateway'])->group(function () {
    
    // Example: RBAC protected routes
    Route::middleware('rbac:properties.create')->post('/properties', [PropertyController::class, 'store']);
    Route::middleware('rbac:properties.update:own')->put('/properties/{id}', [PropertyController::class, 'update']);
    Route::middleware('rbac:properties.delete')->delete('/properties/{id}', [PropertyController::class, 'destroy']);
    
});
```

### 5. Test Security Features

```bash
# Test JWT refresh
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer YOUR_REFRESH_TOKEN"

# Test RBAC
curl http://localhost:8000/api/v1/properties \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test API Gateway
curl http://localhost:8000/api/v1/health \
  -H "X-API-Key: your_api_key"
```

---

## 🔐 Generate API Keys

### For Users

```php
// In your controller or command
use App\Models\ApiKey;

$result = ApiKey::generate([
    'user_id' => $user->id,
    'name' => 'My Application',
    'permissions' => ['properties.read', 'bookings.create'],
    'expires_at' => now()->addYear(),
]);

// Store these securely!
// $result['api_key']
// $result['api_secret']
```

### CLI Command

```bash
php artisan make:command GenerateApiKey

# Then use:
php artisan api:generate --user=1 --name="Mobile App"
```

---

## 📊 Setup Monitoring (Kubernetes)

### 1. Install Prometheus + Grafana

```bash
# Add Helm repo
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update

# Install with custom values
helm install prometheus prometheus-community/kube-prometheus-stack \
  -n monitoring \
  --create-namespace \
  -f k8s/monitoring/prometheus-values.yaml

# Check installation
kubectl get pods -n monitoring
```

### 2. Access Grafana

```bash
# Port forward
kubectl port-forward -n monitoring svc/prometheus-grafana 3000:80

# Open browser
open http://localhost:3000

# Default credentials
Username: admin
Password: (from prometheus-values.yaml)
```

### 3. Import Dashboards

1. Navigate to Dashboards → Import
2. Upload JSON files from `k8s/monitoring/dashboards/`
3. Select Prometheus datasource
4. Click Import

---

## 🏗️ Deploy Infrastructure (Terraform)

### 1. Setup AWS Credentials

```bash
aws configure
# Enter your AWS credentials
```

### 2. Initialize Terraform

```bash
cd terraform

# Initialize
terraform init

# Validate
terraform validate
```

### 3. Create Backend

```bash
# Create S3 bucket for state
aws s3api create-bucket \
  --bucket renthub-terraform-state \
  --region us-east-1

# Enable versioning
aws s3api put-bucket-versioning \
  --bucket renthub-terraform-state \
  --versioning-configuration Status=Enabled

# Create DynamoDB for locking
aws dynamodb create-table \
  --table-name terraform-state-lock \
  --attribute-definitions AttributeName=LockID,AttributeType=S \
  --key-schema AttributeName=LockID,KeyType=HASH \
  --billing-mode PAY_PER_REQUEST
```

### 4. Store Secrets

```bash
# Create secrets in AWS Secrets Manager
aws secretsmanager create-secret \
  --name renthub/production/secrets \
  --secret-string '{
    "db_password": "your-secure-password",
    "redis_auth_token": "your-redis-token",
    "jwt_secret": "your-jwt-secret"
  }'
```

### 5. Plan & Apply

```bash
# Plan for production
terraform plan \
  -var-file="environments/production.tfvars" \
  -var="db_password=$(aws secretsmanager get-secret-value --secret-id renthub/production/secrets --query SecretString --output text | jq -r .db_password)" \
  -out=tfplan

# Review plan
terraform show tfplan

# Apply
terraform apply tfplan
```

---

## 🧪 Run Penetration Tests

### 1. Install Tools

```bash
# macOS
brew install nmap nikto

# Linux (Debian/Ubuntu)
sudo apt install -y nmap nikto

# Install Python dependencies
pip install requests jwt
```

### 2. Run Automated Tests

```bash
# Set target
export TARGET_URL=https://staging.renthub.com
export API_URL=https://api.staging.renthub.com

# Run tests
bash security/penetration-testing/automated-pentest.sh

# View results
ls -la pentest-results-*/
```

### 3. Review Report

```bash
# Open HTML report in browser
open pentest-results-*/penetration-test-report.html
```

---

## 🚨 Test Incident Response

### Trigger Test Incident

```php
use App\Services\SecurityIncidentResponseService;

$service = app(SecurityIncidentResponseService::class);

// Test brute force response
$incident = $service->handleIncident(
    type: SecurityIncidentResponseService::TYPE_BRUTE_FORCE,
    severity: SecurityIncidentResponseService::SEVERITY_HIGH,
    details: [
        'failed_attempts' => 15,
        'target_email' => 'user@example.com'
    ],
    userId: 1
);

// Check incident was created
echo "Incident ID: {$incident->id}";
echo "Status: {$incident->status}";
```

### View Incidents

```php
// Get incident statistics
$stats = $service->getIncidentStats(30);

print_r($stats);
```

---

## 📈 Verify Everything Works

### Health Checks

```bash
# API Health
curl http://localhost:8000/api/v1/health

# Database Connection
php artisan tinker
>>> DB::connection()->getPdo();

# Redis Connection
php artisan tinker
>>> Cache::store('redis')->ping();

# Queue Workers
php artisan queue:work --once
```

### Security Checks

```bash
# Check rate limiting
for i in {1..100}; do
  curl -w "\n" http://localhost:8000/api/v1/health
done

# Check RBAC
curl http://localhost:8000/api/v1/admin/users \
  -H "Authorization: Bearer GUEST_TOKEN"
# Should return 403

# Check API Gateway
curl http://localhost:8000/api/v1/properties
# Should return 401 (missing API key)
```

### Monitoring Checks

```bash
# Check Prometheus targets
curl http://localhost:9090/api/v1/targets

# Check metrics endpoint
curl http://localhost:8000/api/v1/metrics

# Test alert
curl -X POST http://localhost:9093/api/v1/alerts \
  -d '{"alerts":[{"labels":{"alertname":"test"}}]}'
```

---

## 🎯 Next Steps

1. **Review Security Documentation**
   - Read `COMPREHENSIVE_SECURITY_GUIDE.md`
   - Understand incident response procedures
   - Configure alerting channels

2. **Customize Configuration**
   - Adjust rate limits for your needs
   - Configure custom RBAC permissions
   - Set up monitoring dashboards

3. **Test in Staging**
   - Deploy to staging environment
   - Run penetration tests
   - Simulate security incidents

4. **Production Deployment**
   - Follow deployment checklist
   - Enable all security features
   - Monitor closely for 24-48 hours

5. **Ongoing Maintenance**
   - Weekly security reviews
   - Monthly penetration testing
   - Quarterly security audits

---

## 🆘 Troubleshooting

### JWT Token Issues

```bash
# Clear token cache
php artisan cache:clear

# Check JWT secret
php artisan tinker
>>> config('jwt.secret')

# Regenerate tokens
php artisan jwt:secret
```

### RBAC Not Working

```bash
# Clear permission cache
php artisan cache:forget "rbac:user:1:permissions"

# Verify middleware registered
php artisan route:list | grep rbac
```

### Rate Limiting Too Aggressive

```env
# Adjust in .env
RATE_LIMITER_DECAY_MINUTES=1
RATE_LIMITER_MAX_ATTEMPTS=100
```

### Monitoring Not Showing Data

```bash
# Check Prometheus targets
kubectl port-forward -n monitoring svc/prometheus-operated 9090:9090
open http://localhost:9090/targets

# Restart pods
kubectl rollout restart deployment/backend -n renthub
```

---

## 📚 Additional Resources

- **Full Documentation**: See `ADVANCED_SECURITY_DEVOPS_COMPLETE.md`
- **API Documentation**: `API_ENDPOINTS.md`
- **Deployment Guide**: `DEPLOYMENT_CHECKLIST.md`
- **Security Guide**: `COMPREHENSIVE_SECURITY_GUIDE.md`

---

## ✅ Verification Checklist

- [ ] Migrations run successfully
- [ ] Middleware registered
- [ ] JWT refresh working
- [ ] RBAC protecting routes
- [ ] API Gateway validating keys
- [ ] Rate limiting active
- [ ] Monitoring collecting metrics
- [ ] Alerts configured
- [ ] Incident response tested
- [ ] Documentation reviewed

---

**Setup Time**: ~30 minutes
**Difficulty**: Medium
**Support**: Check Slack #renthub-devops

🎉 **You're all set! Your application now has enterprise-grade security.**
