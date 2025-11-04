# 🚀 Quick Start Guide - Security, Performance & UI/UX

## 📦 Installation

### Windows (PowerShell)
```powershell
.\install-security-performance-ui-v2.ps1
```

### Linux/Mac (Bash)
```bash
chmod +x install-security-performance-ui-v2.sh
./install-security-performance-ui-v2.sh
```

---

## ⚡ Quick Usage Examples

### 1. Rate Limiting

```php
// Apply to routes
Route::middleware(['rate-limit:login'])->post('/login', [AuthController::class, 'login']);
Route::middleware(['rate-limit:api'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
});
```

### 2. Security Headers

Automatically applied to all requests. No additional configuration needed!

### 3. Data Encryption

```php
use App\Services\DataEncryptionService;

$encryptionService = app(DataEncryptionService::class);

// Encrypt sensitive data
$encrypted = $encryptionService->encryptData($sensitiveData);

// Decrypt
$decrypted = $encryptionService->decryptData($encrypted);

// Anonymize PII
$anonymized = $encryptionService->anonymizePII([
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'name' => 'John Doe'
]);
// Output: ['email' => 'jo**@example.com', 'phone' => '******7890', 'name' => 'Anonymous User']
```

### 4. GDPR Compliance

```php
use App\Services\GDPRComplianceService;

$gdprService = app(GDPRComplianceService::class);

// Export user data (Right to data portability)
$userData = $gdprService->exportUserData($user);
return response()->json($userData);

// Delete user data (Right to be forgotten)
$gdprService->deleteUserData($user, $preserveBookingHistory = true);

// Manage consent
$consents = [
    'marketing_emails' => true,
    'data_processing' => true,
    'third_party_sharing' => false,
    'analytics' => true
];
$gdprService->updateUserConsent($user, $consents);
```

### 5. Security Audit Logging

```php
use App\Services\SecurityAuditService;

$auditService = app(SecurityAuditService::class);

// Log authentication attempt
$auditService->logAuthAttempt(
    email: $request->email,
    successful: false,
    request: $request,
    reason: 'Invalid password'
);

// Log suspicious activity
$auditService->logSuspiciousActivity(
    'brute_force',
    $user,
    $request,
    ['attempts' => 10, 'ip' => $request->ip()]
);

// Detect brute force
if ($auditService->detectBruteForce($request->ip(), threshold: 5, timeWindow: 300)) {
    return response()->json(['error' => 'Too many attempts'], 429);
}

// Generate security report
$report = $auditService->generateSecurityReport('week');
```

### 6. Caching

```php
use App\Services\CacheService;

$cacheService = app(CacheService::class);

// Cache property listings
$properties = $cacheService->cachePropertyListings($filters, function() {
    return Property::with(['images', 'amenities'])->get();
});

// Cache with custom TTL
$data = $cacheService->remember('my-key', function() {
    return expensiveOperation();
}, CacheService::TTL_LONG, ['tag1', 'tag2']);

// Invalidate cache
$cacheService->invalidatePropertyCache($propertyId);
$cacheService->forgetByTags(['properties']);

// Get cache statistics
$stats = $cacheService->getStats();
// Output: ['hits' => 1234, 'misses' => 56, 'hit_rate' => 95.67]
```

### 7. Query Optimization

```php
use App\Services\QueryOptimizationService;

$queryService = app(QueryOptimizationService::class);

// Get optimized properties
$properties = $queryService->getOptimizedProperties([
    'location' => 'New York',
    'min_price' => 100,
    'max_price' => 500,
    'bedrooms' => 2,
    'sort_by' => 'price',
    'sort_order' => 'asc'
], perPage: 20);

// Batch update
$queryService->batchUpdatePropertyViews([1, 2, 3, 4, 5]);

// Get user bookings optimized
$bookings = $queryService->getUserBookingsOptimized($userId);

// Analyze slow queries
$slowQueries = $queryService->analyzeSlowQueries();

// Get database statistics
$stats = $queryService->getDatabaseStats();
```

---

## 🎨 Frontend Components

### 1. Loading States

```tsx
import { 
  SkeletonList, 
  LoadingSpinner, 
  LoadingOverlay,
  ProgressBar 
} from '@/components/ui/LoadingStates';

// Skeleton loader
{isLoading ? <SkeletonList count={6} /> : <PropertyList properties={properties} />}

// Spinner
<LoadingSpinner size="lg" />

// Full-screen overlay
{isProcessing && <LoadingOverlay message="Processing payment..." />}

// Progress bar
<ProgressBar progress={uploadProgress} label="Uploading images..." />
```

### 2. Error States

```tsx
import { 
  ErrorMessage, 
  EmptyState, 
  NotFound 
} from '@/components/ui/ErrorStates';

// Error message with retry
{error && (
  <ErrorMessage
    title="Failed to load properties"
    message={error.message}
    onRetry={() => refetch()}
    variant="error"
  />
)}

// Empty state
{properties.length === 0 && (
  <EmptyState
    title="No properties found"
    message="Try adjusting your search filters"
    actionLabel="Clear filters"
    onAction={() => clearFilters()}
  />
)}

// 404 page
<NotFound 
  title="Property Not Found"
  message="The property you're looking for doesn't exist"
  actionLabel="Browse Properties"
  onAction={() => router.push('/properties')}
/>
```

### 3. Toast Notifications

```tsx
import { useToast } from '@/components/ui/Toast';

function MyComponent() {
  const { showToast } = useToast();
  
  const handleBooking = async () => {
    try {
      await createBooking();
      showToast({
        type: 'success',
        title: 'Booking confirmed!',
        message: 'Check your email for details',
        duration: 5000
      });
    } catch (error) {
      showToast({
        type: 'error',
        title: 'Booking failed',
        message: error.message
      });
    }
  };
  
  return <button onClick={handleBooking}>Book Now</button>;
}

// Wrap your app with ToastProvider
import { ToastProvider } from '@/components/ui/Toast';

function App() {
  return (
    <ToastProvider>
      <YourApp />
    </ToastProvider>
  );
}
```

---

## 🔧 Configuration

### Environment Variables

```env
# Security
SESSION_LIFETIME=120
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,renthub.com
RATE_LIMIT_PER_MINUTE=60

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# GDPR
DATA_RETENTION_DAYS=730
AUTO_DELETE_INACTIVE_USERS=false

# Security Headers
CONTENT_SECURITY_POLICY=default-src 'self'
HSTS_MAX_AGE=31536000
```

### Redis Setup (Recommended)

```bash
# Install Redis
# Windows: Download from https://github.com/microsoftarchive/redis/releases
# Linux: sudo apt-get install redis-server
# Mac: brew install redis

# Start Redis
redis-server

# Install PHP Redis extension
composer require predis/predis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 🧪 Testing

### Run Tests

```bash
# Backend tests
cd backend
php artisan test

# Security tests only
php artisan test --testsuite=Security

# Performance tests
php artisan test --testsuite=Performance

# Frontend tests
cd frontend
npm test
```

### Manual Testing

#### Test Rate Limiting
```bash
# Make multiple rapid requests
for i in {1..10}; do curl http://localhost:8000/api/properties; done
```

#### Test GDPR Export
```bash
curl -X GET http://localhost:8000/api/gdpr/export \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Test Security Audit
```bash
# View security logs
php artisan tinker
>>> DB::table('security_audit_logs')->latest()->limit(10)->get();
```

---

## 📊 Monitoring

### Check Cache Statistics

```php
php artisan tinker
>>> app(App\Services\CacheService::class)->getStats();
```

### View Security Report

```php
php artisan tinker
>>> $report = app(App\Services\SecurityAuditService::class)->generateSecurityReport('week');
>>> print_r($report);
```

### Database Performance

```php
php artisan tinker
>>> $stats = app(App\Services\QueryOptimizationService::class)->getDatabaseStats();
>>> print_r($stats);
```

---

## 🚨 Troubleshooting

### Cache Not Working
```bash
# Clear cache
php artisan cache:clear

# Check Redis connection
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### Migrations Failed
```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Security Headers Not Applied
```bash
# Clear config cache
php artisan config:clear

# Verify middleware is registered
php artisan route:list
```

### Frontend Build Failed
```bash
# Clear node modules
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 📚 Additional Resources

- **Full Documentation**: `SECURITY_PERFORMANCE_UI_COMPLETE.md`
- **API Documentation**: `API_ENDPOINTS.md`
- **Security Guide**: `COMPREHENSIVE_SECURITY_GUIDE.md`
- **Performance Guide**: `ADVANCED_PERFORMANCE_OPTIMIZATION.md`

---

## ✅ Checklist

### After Installation
- [ ] Update `.env` with database credentials
- [ ] Configure Redis for caching
- [ ] Set up SSL/TLS certificate (production)
- [ ] Test rate limiting endpoints
- [ ] Review security audit logs
- [ ] Configure backup strategy
- [ ] Set up monitoring alerts
- [ ] Test GDPR export/delete functionality
- [ ] Verify all UI components render correctly
- [ ] Run full test suite

### Production Deployment
- [ ] Enable HTTPS
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper cache driver (Redis)
- [ ] Set up CDN for static assets
- [ ] Enable log rotation
- [ ] Configure automated backups
- [ ] Set up error monitoring (Sentry)
- [ ] Configure rate limiting for production traffic
- [ ] Review and adjust security headers

---

## 🎉 You're All Set!

Your RentHub platform now has enterprise-level security, optimized performance, and enhanced UI/UX!

For questions or issues, refer to the full documentation or contact the development team.

**Happy coding! 🚀**
