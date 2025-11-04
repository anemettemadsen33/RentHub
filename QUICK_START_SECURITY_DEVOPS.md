# 🚀 Quick Start Guide - Security & DevOps

**Last Updated:** November 3, 2025  
**For:** Developers, DevOps Engineers, Security Team

---

## 📑 Table of Contents

1. [Security Features Quick Reference](#security-features)
2. [Performance Optimization Quick Guide](#performance-optimization)
3. [DevOps Deployment Guide](#devops-deployment)
4. [Common Tasks](#common-tasks)
5. [Troubleshooting](#troubleshooting)

---

## 🔐 Security Features

### OAuth 2.0 Authentication

```php
use App\Services\OAuth2Service;

// Initialize service
$oauth2 = app(OAuth2Service::class);

// Step 1: Generate authorization code
$code = $oauth2->generateAuthorizationCode(
    $user,
    $client,
    ['read', 'write', 'admin']
);

// Step 2: Exchange code for tokens
$tokens = $oauth2->exchangeAuthorizationCode(
    $code,
    $clientId,
    $clientSecret,
    $redirectUri
);
// Returns: ['access_token', 'refresh_token', 'token_type', 'expires_in', 'scope']

// Step 3: Refresh access token
$newTokens = $oauth2->refreshAccessToken($refreshToken, $clientId, $clientSecret);

// Validate token
$tokenData = $oauth2->validateAccessToken($accessToken);

// Revoke token
$oauth2->revokeToken($token);
```

### JWT Authentication

```php
use App\Services\JWTService;

$jwtService = app(JWTService::class);

// Generate tokens
$accessToken = $jwtService->generateAccessToken($user, ['admin' => true]);
$refreshToken = $jwtService->generateRefreshToken($user);

// Validate token
$decoded = $jwtService->validateToken($token);

// Refresh tokens
$newTokens = $jwtService->refreshAccessToken($refreshToken);

// Get user from token
$user = $jwtService->getUserFromToken($token);

// Revoke token
$jwtService->revokeToken($token);
```

### RBAC (Role-Based Access Control)

```php
use App\Services\RBACService;

$rbac = app(RBACService::class);

// Check permissions
if ($rbac->hasPermission($user, 'properties.edit')) {
    // User can edit properties
}

// Check multiple permissions
if ($rbac->hasAllPermissions($user, ['properties.edit', 'properties.delete'])) {
    // User has all permissions
}

// Check roles
if ($rbac->hasRole($user, 'landlord')) {
    // User is a landlord
}

// Assign role
$rbac->assignRole($user, 'admin');

// Give permission
$rbac->givePermissionTo($user, 'properties.manage');

// Check resource ownership
if ($rbac->ownsResource($user, $property)) {
    // User owns this property
}

// Check if user can perform action
if ($rbac->can($user, 'edit', $property)) {
    // User can edit this specific property
}
```

### API Key Management

```php
use App\Services\APIKeyService;

$apiKeyService = app(APIKeyService::class);

// Generate API key
$keyData = $apiKeyService->generateKey(
    $user,
    'Production API Key',
    ['properties.*', 'bookings.read'],  // Permissions
    now()->addYear(),                    // Expiration
    ['192.168.1.100', '10.0.0.5'],      // IP whitelist
    1000                                 // Rate limit per hour
);
// Returns: ['id', 'key', 'name', 'created_at', 'expires_at']
// ⚠️ Store the 'key' value - it won't be shown again!

// Validate API key
$apiKey = $apiKeyService->validateKey($request->header('X-API-Key'), $request->ip());

// Check rate limit
if (!$apiKeyService->checkRateLimit($apiKey)) {
    abort(429, 'Rate limit exceeded');
}

// Check permission
if ($apiKeyService->hasPermission($apiKey, 'properties.create')) {
    // API key has permission
}

// Rotate key
$newKeyData = $apiKeyService->rotateKey($oldApiKey);
```

### Data Encryption

```php
use App\Services\EncryptionService;

$encryption = app(EncryptionService::class);

// Encrypt sensitive data
$encrypted = $encryption->encryptAtRest($data);

// Decrypt data
$decrypted = $encryption->decryptAtRest($encrypted);

// Encrypt field
$encryptedField = $encryption->encryptField($user->ssn);

// Decrypt field
$ssn = $encryption->decryptField($encryptedField);

// Encrypt file
$encryption->encryptFile($sourcePath, $encryptedPath);

// Decrypt file
$encryption->decryptFile($encryptedPath, $decryptedPath);
```

### PII Protection

```php
use App\Services\PIIProtectionService;

$pii = app(PIIProtectionService::class);

// Anonymize data
$anonymized = $pii->anonymize($userData, 'hash');  // Methods: hash, mask, redact, pseudonymize

// Mask specific fields
$maskedEmail = $pii->maskEmail('user@example.com');        // us**@example.com
$maskedPhone = $pii->maskPhone('+1234567890');            // ******7890
$maskedCard = $pii->maskCreditCard('4111111111111111');   // ************1111

// Check PII access permission
if ($pii->canAccessPII($user, 'ssn')) {
    $ssn = $user->ssn;
    $pii->logPIIAccess($user, 'ssn', 'read');
}
```

### GDPR Compliance

```php
use App\Services\GDPRService;

$gdpr = app(GDPRService::class);

// Export user data (Right to Portability)
$exportedData = $gdpr->exportUserData($user, 'json');  // Formats: json, csv, pdf
Storage::put("exports/{$user->id}.json", $exportedData);

// Delete user data (Right to be Forgotten)
$deletionRequest = $gdpr->deleteUserData($user, $immediate = false);

// Record consent
$consent = $gdpr->recordConsent($user, 'marketing', true);

// Check consent
if ($gdpr->hasConsent($user, 'analytics')) {
    // Track analytics
}

// Revoke consent
$gdpr->revokeConsent($user, 'marketing');

// Get consent history
$history = $gdpr->getConsentHistory($user);

// Rectify data (Right to Rectification)
$gdpr->rectifyData($user, ['email' => 'new@example.com']);

// Restrict processing
$gdpr->restrictProcessing($user, true);
```

---

## ⚡ Performance Optimization

### Caching

```php
use App\Services\CachingService;

$cache = app(CachingService::class);

// Cache query result
$properties = $cache->cacheQuery('featured-properties', function() {
    return Property::where('featured', true)->with('images')->get();
}, 3600);  // Cache for 1 hour

// Cache paginated query
$properties = $cache->cachePaginatedQuery(
    'properties',
    $page,
    $perPage,
    function() use ($page, $perPage) {
        return Property::paginate($perPage, ['*'], 'page', $page);
    }
);

// Cache with tags
$data = $cache->cacheWithTags(
    ['properties', 'featured'],
    'properties:featured',
    function() {
        return Property::where('featured', true)->get();
    }
);

// Invalidate cache
$cache->invalidate('featured-properties');

// Invalidate by pattern
$cache->invalidatePattern('properties:*');

// Flush tags
$cache->flushTags(['properties']);

// Get cache stats
$stats = $cache->getStats();
```

### Query Optimization

```php
use App\Services\QueryOptimizationService;

$queryService = app(QueryOptimizationService::class);

// Prevent N+1 queries
$properties = Property::query();
$properties = $queryService->eagerLoad($properties, ['owner', 'images', 'amenities']);

// Selective loading
$properties = $queryService->selectiveLoad($properties, ['id', 'title', 'price']);

// Chunking for large datasets
$queryService->chunk(Property::query(), 100, function($properties) {
    foreach ($properties as $property) {
        // Process property
    }
});

// Batch insert
$data = [
    ['title' => 'Property 1', 'price' => 1000],
    ['title' => 'Property 2', 'price' => 2000],
];
$queryService->batchInsert('properties', $data, 1000);

// Use index hint
$properties = Property::query();
$properties = $queryService->useIndex($properties, 'idx_featured_created');

// Analyze query
$analysis = $queryService->analyzeQuery("SELECT * FROM properties WHERE featured = 1");

// Get slow queries
$slowQueries = $queryService->getSlowQueries(1000);  // Queries slower than 1000ms
```

### API Optimization

```php
use App\Services\APIOptimizationService;

$apiService = app(APIOptimizationService::class);

// Paginate results
$properties = Property::query();
$paginated = $apiService->paginate($properties, $request, 15);

// Apply field selection
$properties = $apiService->selectFields($properties, $request);
// GET /api/properties?fields=id,title,price

// Apply sorting
$properties = $apiService->applySorting($properties, $request);
// GET /api/properties?sort_by=price&sort_order=desc

// Apply filters
$properties = $apiService->applyFilters($properties, $request, ['status', 'type']);
// GET /api/properties?status=available&type=apartment

// Apply includes
$properties = $apiService->applyIncludes($properties, $request, ['owner', 'images']);
// GET /api/properties?include=owner,images

// Format response
return $apiService->formatResponse($properties, 'Properties retrieved successfully');

// Add cache headers
$response = response()->json($data);
$response = $apiService->addCacheHeaders($response, 300);  // Cache for 5 minutes

// Add ETag
$response = $apiService->addETag($response);
```

---

## 🚀 DevOps Deployment

### Docker

```bash
# Build image
docker build -t renthub:latest -f backend/Dockerfile backend/

# Run container
docker run -d -p 8000:8000 --name renthub renthub:latest

# Docker Compose (Development)
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop services
docker-compose down
```

### Kubernetes

```bash
# Update kubeconfig
aws eks update-kubeconfig --name renthub-eks-production --region us-east-1

# Deploy application
kubectl apply -f k8s/production/

# Check pods
kubectl get pods -n production

# Check services
kubectl get svc -n production

# View logs
kubectl logs -f deployment/renthub-app -n production

# Scale deployment
kubectl scale deployment renthub-app --replicas=5 -n production

# Check HPA status
kubectl get hpa -n production

# Port forward for debugging
kubectl port-forward svc/renthub-service 8000:80 -n production

# Execute command in pod
kubectl exec -it deployment/renthub-app -n production -- php artisan cache:clear

# Delete deployment
kubectl delete -f k8s/production/
```

### Terraform

```bash
cd terraform

# Initialize
terraform init

# Validate configuration
terraform validate

# Plan changes
terraform plan -var-file="environments/production.tfvars" -out=tfplan

# Apply changes
terraform apply tfplan

# Show current state
terraform show

# List resources
terraform state list

# Destroy resources (CAUTION!)
terraform destroy -var-file="environments/production.tfvars"

# Format code
terraform fmt -recursive

# Import existing resource
terraform import aws_instance.example i-1234567890abcdef0
```

### CI/CD Workflows

```bash
# Trigger blue-green deployment
gh workflow run blue-green-deployment.yml \
  -f environment=production

# Trigger canary deployment
gh workflow run canary-deployment.yml \
  -f canary-percentage=10

# Trigger security scan
gh workflow run advanced-security-scan.yml

# View workflow runs
gh run list --workflow=blue-green-deployment.yml

# View workflow logs
gh run view <run-id> --log

# Download artifacts
gh run download <run-id>
```

---

## 🛠️ Common Tasks

### Create New Role

```php
use App\Services\RBACService;

$rbac = app(RBACService::class);

// Create role with permissions
$role = $rbac->createRole(
    'property_manager',
    'Can manage properties',
    ['properties.*', 'bookings.view']
);

// Assign to user
$rbac->assignRole($user, 'property_manager');
```

### Generate API Documentation

```bash
# Generate OpenAPI/Swagger docs
php artisan l5-swagger:generate

# Access docs
# http://localhost:8000/api/documentation
```

### Run Security Audit

```bash
# Composer audit
composer audit

# NPM audit
npm audit

# Security scan
php artisan security:scan

# Check for outdated packages
composer outdated
npm outdated
```

### Performance Testing

```bash
# Install k6
# https://k6.io/docs/getting-started/installation/

# Run load test
k6 run --vus 100 --duration 30s tests/load/api-test.js

# Run stress test
k6 run --vus 1000 --duration 5m tests/load/stress-test.js
```

### Database Backup

```bash
# Create backup
php artisan backup:run

# List backups
php artisan backup:list

# Clean old backups
php artisan backup:clean

# Monitor backup health
php artisan backup:monitor
```

---

## 🔧 Troubleshooting

### High Response Times

```bash
# 1. Check slow queries
tail -f storage/logs/laravel.log | grep "slow query"

# 2. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize autoloader
composer dump-autoload -o

# 4. Check database connections
php artisan tinker
>>> DB::connection()->getPdo();

# 5. Monitor Redis
redis-cli
> INFO stats
> SLOWLOG GET 10
```

### Memory Issues

```bash
# 1. Check memory usage
kubectl top pods -n production

# 2. Increase PHP memory limit
# Edit .env
PHP_MEMORY_LIMIT=512M

# 3. Optimize queries
# Use chunking for large datasets

# 4. Clear unused cache
php artisan cache:clear --tags=unused
```

### Authentication Failures

```bash
# 1. Check token expiration
# JWT tokens expire after 1 hour by default

# 2. Verify API key
php artisan tinker
>>> $key = App\Models\APIKey::where('key_hash', hash('sha256', 'your_key'))->first();
>>> $key->is_active;
>>> $key->expires_at;

# 3. Check rate limits
# Rate limits reset every minute

# 4. Verify permissions
>>> $user->getAllPermissions()->pluck('name');
```

### Deployment Failures

```bash
# 1. Check pod status
kubectl describe pod <pod-name> -n production

# 2. View pod logs
kubectl logs <pod-name> -n production --previous

# 3. Check events
kubectl get events -n production --sort-by='.lastTimestamp'

# 4. Rollback deployment
kubectl rollout undo deployment/renthub-app -n production

# 5. Check health endpoint
curl https://api.renthub.com/health
```

### Cache Issues

```bash
# 1. Check Redis connection
redis-cli ping

# 2. Check cache keys
redis-cli KEYS "renthub:*"

# 3. Clear specific cache
php artisan cache:forget renthub:properties:featured

# 4. Flush all cache
php artisan cache:clear

# 5. Check cache statistics
redis-cli INFO stats
```

---

## 📚 Additional Resources

- [Full Documentation](SECURITY_PERFORMANCE_DEVOPS_COMPLETE.md)
- [API Endpoints](API_ENDPOINTS.md)
- [Security Guide](SECURITY_GUIDE.md)
- [DevOps Guide](README_DEVOPS.md)
- [Kubernetes Guide](KUBERNETES_GUIDE.md)

---

## 📞 Support

- **Email:** support@renthub.com
- **Slack:** #renthub-support
- **Documentation:** https://docs.renthub.com

---

**Last Updated:** November 3, 2025  
**Version:** 1.0
