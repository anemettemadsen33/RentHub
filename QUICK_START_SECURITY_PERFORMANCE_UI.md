# 🚀 Quick Start: Security, Performance & UI/UX

## ⚡ 5-Minute Setup

### 1. Install Dependencies
```bash
cd backend
composer install
php artisan migrate
php artisan passport:install
```

### 2. Configure Environment
```bash
# .env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...

# Redis Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# GDPR Settings
GDPR_MIN_RETENTION_DAYS=30
GDPR_BOOKING_RETENTION_DAYS=2555
GDPR_DELETION_GRACE_PERIOD=30
```

### 3. Register Middleware
Add to `app/Http/Kernel.php`:
```php
protected $middleware = [
    \App\Http\Middleware\SecurityHeadersMiddleware::class,
    \App\Http\Middleware\CompressionMiddleware::class,
    \App\Http\Middleware\InputSanitizationMiddleware::class,
];

protected $routeMiddleware = [
    'rate' => \App\Http\Middleware\RateLimitMiddleware::class,
];
```

### 4. Add Routes
Add to `routes/api.php`:
```php
Route::middleware('auth:api')->group(function () {
    Route::get('/gdpr/export', [GDPRController::class, 'exportData']);
    Route::post('/gdpr/request-deletion', [GDPRController::class, 'requestDeletion']);
    Route::post('/gdpr/cancel-deletion', [GDPRController::class, 'cancelDeletion']);
    Route::get('/gdpr/retention-status', [GDPRController::class, 'retentionStatus']);
});
```

---

## 🔐 Security Features

### Enable Security Headers
```php
// Automatic with SecurityHeadersMiddleware
// All responses include:
// - Content-Security-Policy
// - Strict-Transport-Security
// - X-Frame-Options
// - X-Content-Type-Options
// - Referrer-Policy
```

### Rate Limiting
```php
// In your routes
Route::middleware('rate:60:1')->group(function () {
    // 60 requests per minute
});

Route::middleware('rate:10:1')->group(function () {
    // 10 requests per minute (stricter)
});
```

### Audit Logging
```php
use App\Services\AuditLogService;

$auditLog = app(AuditLogService::class);

// Log user action
$auditLog->log('create', 'property', $propertyId, ['data' => ...]);

// Log security event
$auditLog->logSecurityEvent('failed_login', ['email' => $email]);

// Log authentication
$auditLog->logAuthentication('login', $userId, true);

// Log data access
$auditLog->logDataAccess('property', $propertyId);
```

### Data Encryption
```php
use App\Services\EncryptionService;

$encryption = app(EncryptionService::class);

// Encrypt PII data
$encrypted = $encryption->encryptPII([
    'ssn' => '123-45-6789',
    'passport' => 'AB123456',
]);

// Decrypt PII data
$decrypted = $encryption->decryptPII($encrypted);

// Anonymize data
$anonymized = $encryption->anonymize('user@example.com', 'email');
// Result: 'us**@example.com'
```

---

## ⚡ Performance Features

### Caching
```php
use App\Services\CacheService;

$cache = app(CacheService::class);

// Cache a property
$property = $cache->cacheProperty($propertyId, function() {
    return Property::with('amenities', 'images')->find($propertyId);
});

// Cache property list
$properties = $cache->cachePropertyList($filters, function() {
    return Property::where($filters)->get();
});

// Invalidate cache
$cache->invalidateProperty($propertyId);
```

### Database Optimization
```php
use App\Services\DatabaseOptimizationService;

$optimizer = app(DatabaseOptimizationService::class);

// Analyze slow queries
$analysis = $optimizer->analyzeQueryPerformance();

// Optimize table
$optimizer->optimizeTable('properties');

// Check missing indexes
$missing = $optimizer->checkMissingIndexes('properties');

// Get database statistics
$stats = $optimizer->getDatabaseStatistics();
```

### Response Compression
```php
// Automatic with CompressionMiddleware
// Supports both Brotli and Gzip
// Automatically selects best compression based on Accept-Encoding
```

---

## 🎨 UI/UX Components

### Loading States
```tsx
import { 
  ButtonLoader, 
  PageLoader, 
  SkeletonLoader,
  PropertyCardSkeleton 
} from '@/components/ui/LoadingStates';

// Button loader
<button disabled>
  <ButtonLoader size="sm" />
  Loading...
</button>

// Page loader
<PageLoader />

// Skeleton
<PropertyCardSkeleton />

// List skeleton
<ListSkeleton count={5} />
```

### Error States
```tsx
import { 
  ErrorState, 
  NotFoundState, 
  EmptyState,
  InlineError 
} from '@/components/ui/ErrorStates';

// Full error page
<ErrorState
  title="Something went wrong"
  message="Please try again later"
  onRetry={() => refetch()}
  onGoHome={() => navigate('/')}
/>

// 404 page
<NotFoundState resourceType="Property" />

// Empty state
<EmptyState
  title="No properties found"
  message="Try adjusting your filters"
  action={{
    label: 'Clear Filters',
    onClick: () => clearFilters()
  }}
/>
```

### Success States
```tsx
import { 
  SuccessMessage, 
  SuccessToast, 
  SuccessModal 
} from '@/components/ui/SuccessStates';

// Success message
<SuccessMessage
  message="Property created successfully!"
  onClose={() => setShowSuccess(false)}
/>

// Toast notification
<SuccessToast
  message="Booking confirmed!"
  duration={3000}
  onClose={() => setShowToast(false)}
/>

// Success modal
<SuccessModal
  title="Success!"
  message="Your booking has been confirmed"
  onClose={() => setShowModal(false)}
  action={{
    label: 'View Booking',
    onClick: () => navigate('/bookings')
  }}
/>
```

---

## 📱 GDPR Features

### Export User Data
```php
use App\Services\GDPRComplianceService;

$gdpr = app(GDPRComplianceService::class);
$user = Auth::user();

// Export all user data
$filePath = $gdpr->exportUserData($user);

// Returns JSON file with:
// - User information
// - Bookings
// - Payments
// - Reviews
// - Messages
// - Preferences
// - Audit logs
```

### Request Account Deletion
```php
// Process deletion request (30-day grace period)
$gdpr->processDataDeletionRequest($user, 'User requested account closure');

// Cancel deletion request
$gdpr->cancelDataDeletionRequest($user);

// Check if user can be deleted
$status = $gdpr->getDataRetentionStatus($user);
```

### Anonymize User Data
```php
// Anonymize user (GDPR right to be forgotten)
$gdpr->anonymizeUser($user);

// Permanently delete user data
$gdpr->deleteUserData($user);
```

---

## 🎯 SEO Features

### Property Meta Tags
```php
use App\Services\SEOService;

$seo = app(SEOService::class);
$property = Property::find($id);

// Generate all meta tags
$meta = $seo->generatePropertyMeta($property);

// Returns:
// - title, description, keywords
// - Open Graph tags
// - Twitter Card tags
// - Schema.org structured data
// - Canonical URL
```

### Sitemap Generation
```php
// Generate sitemap
$xml = $seo->generateSitemap();
file_put_contents(public_path('sitemap.xml'), $xml);

// Generate robots.txt
$robots = $seo->generateRobotsTxt();
file_put_contents(public_path('robots.txt'), $robots);
```

---

## 📊 Monitoring

### View Audit Logs
```php
use App\Models\AuditLog;

// Get all logs
$logs = AuditLog::with('user')
    ->orderBy('created_at', 'desc')
    ->paginate(50);

// Get security events
$security = AuditLog::where('level', 'warning')
    ->where('entity', 'security')
    ->get();

// Get user activity
$activity = AuditLog::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->limit(100)
    ->get();
```

### Performance Metrics
```php
// Check cache hit rate
$hits = Cache::get('cache_hits', 0);
$misses = Cache::get('cache_misses', 0);
$hitRate = $hits / ($hits + $misses) * 100;

// Monitor slow queries
$slowQueries = DB::getQueryLog()
    ->where('time', '>', 1000) // > 1 second
    ->get();
```

---

## 🧪 Testing

### Test Security Headers
```bash
curl -I https://your-domain.com

# Should see:
# content-security-policy: ...
# strict-transport-security: max-age=31536000
# x-frame-options: DENY
# x-content-type-options: nosniff
```

### Test Rate Limiting
```bash
# Make 61 requests in quick succession
for i in {1..61}; do
  curl -X POST https://your-domain.com/api/login
done

# Should see 429 Too Many Requests
```

### Test GDPR Export
```bash
curl -X GET https://your-domain.com/api/gdpr/export \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o user_data.json
```

### Test Compression
```bash
curl -I -H "Accept-Encoding: gzip, br" https://your-domain.com

# Should see:
# content-encoding: br (or gzip)
```

---

## 🚨 Troubleshooting

### Cache Issues
```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Warm cache
php artisan cache:warm
```

### Database Issues
```bash
# Optimize all tables
php artisan db:optimize

# Analyze slow queries
php artisan db:analyze-performance
```

### Security Issues
```bash
# Check audit logs
php artisan audit:review

# Run security scan
php artisan security:scan
```

---

## 📚 Additional Resources

- [Full Documentation](./SECURITY_PERFORMANCE_UI_COMPLETE_2025_11_03.md)
- [Security Guide](./SECURITY_GUIDE.md)
- [Performance Guide](./PERFORMANCE_GUIDE.md)
- [GDPR Guide](./GDPR_COMPLIANCE.md)
- [SEO Guide](./SEO_GUIDE.md)

---

**Ready to deploy!** 🚀

All security, performance, and UI/UX features are production-ready.
