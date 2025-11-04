# 🚀 Quick Start Guide - Security & Performance Implementation

## Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8.0+ or PostgreSQL 13+
- Redis 6.0+
- Git

## Installation

### 1. Clone & Setup

```bash
# Navigate to backend directory
cd backend

# Run installation script
# For Windows:
.\install-security-performance-complete-2025.ps1

# For Linux/Mac:
chmod +x install-security-performance-complete-2025.sh
./install-security-performance-complete-2025.sh
```

### 2. Configure Environment

Update `.env` file with your credentials:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# OAuth - Google
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# OAuth - Facebook
FACEBOOK_CLIENT_ID=your-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-facebook-client-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

# JWT
JWT_SECRET=your-generated-secret
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Security
SECURITY_RATE_LIMIT_PER_MINUTE=60
SECURITY_MAX_LOGIN_ATTEMPTS=5
SECURITY_LOCKOUT_DURATION=900
```

### 3. Run Migrations

```bash
php artisan migrate --seed
```

### 4. Start Services

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Queue Worker
php artisan queue:work

# Terminal 3 - Redis Server (if not running as service)
redis-server
```

## 🔐 Security Features

### 1. OAuth 2.0 Authentication

#### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback`
   - `https://yourdomain.com/auth/google/callback`
6. Copy Client ID and Secret to `.env`

#### Usage Example

```javascript
// Frontend - Initiate OAuth
const loginWithGoogle = async () => {
    window.location.href = 'http://localhost:8000/api/auth/google';
};

// Backend handles callback and returns JWT token
```

### 2. JWT Authentication

#### Login Request

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

#### Response

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

#### Authenticated Request

```bash
curl -X GET http://localhost:8000/api/user/profile \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

#### Refresh Token

```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### 3. API Key Management

#### Generate API Key

```bash
curl -X POST http://localhost:8000/api/api-keys \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mobile App",
    "permissions": ["properties.read", "bookings.create"],
    "rate_limit": 1000
  }'
```

#### Response

```json
{
  "id": 1,
  "name": "Mobile App",
  "key": "rh_abc123...",
  "secret": "hashed_secret",
  "rate_limit": 1000,
  "expires_at": "2025-11-03T00:00:00Z"
}
```

#### Use API Key

```bash
curl -X GET http://localhost:8000/api/properties \
  -H "X-API-Key: rh_abc123..."
```

### 4. Role-Based Access Control (RBAC)

#### Create Roles

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$admin = Role::create(['name' => 'admin']);
$host = Role::create(['name' => 'host']);
$guest = Role::create(['name' => 'guest']);

// Create permissions
$permissions = [
    'properties.create',
    'properties.edit',
    'properties.delete',
    'bookings.create',
    'bookings.manage',
    'users.manage'
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}

// Assign permissions to roles
$admin->givePermissionTo(Permission::all());
$host->givePermissionTo(['properties.create', 'properties.edit', 'bookings.manage']);
$guest->givePermissionTo(['bookings.create']);
```

#### Assign Role to User

```php
$user->assignRole('host');
```

#### Check Permissions in Controller

```php
public function store(Request $request)
{
    if (!auth()->user()->hasPermissionTo('properties.create')) {
        abort(403, 'Unauthorized');
    }
    
    // Create property
}
```

#### Use Middleware

```php
Route::middleware(['auth', 'permission:properties.create'])
    ->post('/properties', [PropertyController::class, 'store']);
```

### 5. Rate Limiting

#### API Routes

```php
// routes/api.php
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
});

// Custom rate limits
Route::middleware(['throttle:booking'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
});
```

#### Login Rate Limiting

```php
Route::middleware(['throttle:login'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});
```

### 6. Security Headers

All responses automatically include:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: ...`

### 7. GDPR Compliance

#### Export User Data

```bash
curl -X GET http://localhost:8000/api/gdpr/export \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Delete Account (Right to be Forgotten)

```bash
curl -X DELETE http://localhost:8000/api/gdpr/delete \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "confirmation": "DELETE MY ACCOUNT",
    "password": "user_password"
  }'
```

## ⚡ Performance Features

### 1. Database Query Optimization

#### Eager Loading

```php
// ✅ GOOD - Eager load relationships
$properties = Property::with(['amenities', 'images', 'owner'])
    ->get();

// ❌ BAD - N+1 query problem
$properties = Property::all();
foreach ($properties as $property) {
    $property->amenities; // Separate query for each property
}
```

#### Select Specific Columns

```php
// ✅ GOOD
$properties = Property::select(['id', 'name', 'price', 'city'])
    ->get();

// ❌ BAD
$properties = Property::all();
```

#### Use Chunks for Large Datasets

```php
Property::chunk(100, function ($properties) {
    foreach ($properties as $property) {
        // Process property
    }
});
```

### 2. Caching

#### Cache Properties List

```php
use App\Services\CacheService;

public function index(Request $request, CacheService $cache)
{
    $cacheKey = 'properties:list:' . md5(json_encode($request->all()));
    
    $properties = $cache->remember(
        $cacheKey,
        CacheService::CACHE_15_MINUTES,
        function () use ($request) {
            return Property::filter($request->all())
                ->with(['amenities', 'images'])
                ->paginate(20);
        },
        ['properties']
    );

    return response()->json($properties);
}
```

#### Invalidate Cache

```php
// When property is updated
$cache->invalidate(['properties']);
```

#### Cache Tags

```php
// Cache with tags
Cache::tags(['properties', 'featured'])->put('featured-properties', $properties, 3600);

// Flush by tag
Cache::tags(['properties'])->flush();
```

### 3. Redis Configuration

#### Install Redis

```bash
# Ubuntu
sudo apt-get install redis-server

# Mac
brew install redis

# Windows
# Download from https://redis.io/download
```

#### Start Redis

```bash
# Linux/Mac
redis-server

# Windows
redis-server.exe
```

#### Monitor Redis

```bash
redis-cli monitor
```

### 4. API Response Compression

Automatically enabled for all API responses. Supports:
- Brotli (best compression)
- Gzip (fallback)

### 5. Database Indexes

All performance indexes are automatically created during migration:

- Properties: `status`, `city`, `price`, `created_at`
- Composite: `[city, status]`, `[price, bedrooms, bathrooms]`
- Bookings: `status`, `[check_in, check_out]`, `[property_id, status]`
- Users: `email`, `created_at`

## 📊 Testing

### Run All Tests

```bash
php artisan test
```

### Run Security Tests

```bash
php artisan test --filter Security
```

### Run Performance Tests

```bash
php artisan test --filter Performance
```

### Test Coverage

```bash
php artisan test --coverage
```

## 🔍 Monitoring & Debugging

### Enable Query Logging

```php
// In AppServiceProvider boot method
if (app()->environment('local')) {
    DB::listen(function ($query) {
        Log::info('Query', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time
        ]);
    });
}
```

### Monitor Slow Queries

```bash
php artisan tinker

use App\Services\QueryOptimizationService;

$service = app(QueryOptimizationService::class);
$service->enableQueryLog();

// Run your code here

$slowQueries = $service->analyzeSlowQueries(1.0); // Queries slower than 1 second
dd($slowQueries);
```

### View Audit Logs

```bash
php artisan tinker

use App\Models\AuditLog;

// Recent audit logs
AuditLog::latest()->take(10)->get();

// User activity
AuditLog::where('user_id', 1)->latest()->get();

// Security events
AuditLog::where('severity', 'critical')->latest()->get();
```

### Check Security Events

```bash
php artisan tinker

use App\Models\SecurityEvent;

// Recent threats
SecurityEvent::latest()->take(10)->get();

// Blocked IPs
SecurityEvent::where('blocked', true)->get();

// SQL injection attempts
SecurityEvent::where('type', 'sql_injection')->get();
```

## 🚨 Common Issues & Solutions

### Issue: Redis Connection Failed

```bash
# Check Redis is running
redis-cli ping
# Should return: PONG

# If not running, start it
redis-server
```

### Issue: JWT Token Invalid

```bash
# Regenerate JWT secret
php artisan jwt:secret

# Clear config cache
php artisan config:clear
php artisan config:cache
```

### Issue: Rate Limit Too Restrictive

Update `.env`:
```env
SECURITY_RATE_LIMIT_PER_MINUTE=120
```

Then:
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Slow Query Performance

```bash
# Analyze queries
php artisan db:monitor

# Optimize tables
php artisan db:optimize

# Check indexes
php artisan db:show --indexes
```

## 📚 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Login with credentials |
| POST | `/api/auth/logout` | Logout current user |
| POST | `/api/auth/refresh` | Refresh JWT token |
| GET | `/api/auth/google` | Redirect to Google OAuth |
| GET | `/api/auth/google/callback` | Handle Google OAuth callback |
| GET | `/api/auth/facebook` | Redirect to Facebook OAuth |
| GET | `/api/auth/facebook/callback` | Handle Facebook OAuth callback |

### API Key Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/api-keys` | List user's API keys |
| POST | `/api/api-keys` | Create new API key |
| PUT | `/api/api-keys/{id}` | Update API key |
| DELETE | `/api/api-keys/{id}` | Revoke API key |

### GDPR Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/gdpr/export` | Export user data |
| DELETE | `/api/gdpr/delete` | Delete user account |
| PUT | `/api/gdpr/update` | Update user data |

### Security Monitoring

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/security/audit-logs` | View audit logs |
| GET | `/api/security/events` | View security events |
| GET | `/api/security/blocked-ips` | List blocked IPs |

## 🎯 Performance Benchmarks

### Target Metrics

- **API Response Time**: < 200ms (95th percentile)
- **Database Query Time**: < 100ms per query
- **Cache Hit Ratio**: > 80%
- **Memory Usage**: < 512MB per request
- **Concurrent Users**: 1000+ simultaneous connections

### Monitoring Tools

- **New Relic**: Application performance monitoring
- **Redis Insights**: Redis monitoring and debugging
- **Laravel Telescope**: Development debugging
- **Laravel Horizon**: Queue monitoring

## 📖 Additional Resources

- [Comprehensive Security Guide](./COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md)
- [API Documentation](./API_ENDPOINTS.md)
- [Deployment Guide](./DEPLOYMENT.md)
- [Testing Guide](./TESTING_GUIDE.md)

## 🆘 Support

For issues or questions:
1. Check this documentation
2. Review [COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md](./COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md)
3. Check Laravel logs: `storage/logs/laravel.log`
4. Enable debug mode in `.env`: `APP_DEBUG=true`

---

**Last Updated**: 2025-11-03
**Version**: 1.0.0
