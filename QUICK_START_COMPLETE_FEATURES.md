# 🚀 Quick Start - Complete Features

## ⚡ 5-Minute Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- Redis (optional but recommended)
- MySQL/PostgreSQL

### Installation

#### Option 1: Automated Script (Windows)
```powershell
.\install-complete-features.ps1
```

#### Option 2: Automated Script (Linux/Mac)
```bash
chmod +x install-complete-features.sh
./install-complete-features.sh
```

#### Option 3: Manual Installation
```bash
# 1. Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# 2. Frontend setup
cd ../frontend
npm install
npm run build

# 3. Start servers
cd ../backend
php artisan serve
```

---

## 🔧 Essential Configuration

### .env Settings
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=

# Cache (Redis)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Security
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=1
MAX_FILE_SIZE=10485760

# Performance
CACHE_ENABLED=true
COMPRESSION_ENABLED=true
QUERY_OPTIMIZATION=true
```

### Register Middleware

Edit `backend/app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\XssProtection::class,
        \App\Http\Middleware\SecurityHeadersMiddleware::class,
    ],

    'api' => [
        // ... existing middleware
        \App\Http\Middleware\SqlInjectionProtection::class,
        \App\Http\Middleware\DdosProtection::class,
        \App\Http\Middleware\CompressionMiddleware::class,
    ],
];
```

---

## 📚 Feature Usage

### 🔐 Security

#### File Upload with Validation
```php
use App\Services\FileUploadSecurityService;

$service = new FileUploadSecurityService();
$path = $service->storeSecurely($request->file('document'));
```

#### Security Audit Logging
```php
use App\Services\SecurityAuditService;

$audit = new SecurityAuditService();
$audit->logAuthAttempt($email, $successful);
$audit->logSuspiciousActivity('Multiple failed attempts', $context);
```

### ⚡ Performance

#### Query Optimization
```php
use App\Services\QueryOptimizationService;

$optimizer = new QueryOptimizationService();

// Prevent N+1
$properties = $optimizer->preventN1(Property::class, ['owner', 'amenities']);

// Cache query
$results = $optimizer->cacheQuery('key', 600, fn() => Property::all());
```

#### Caching Strategy
```php
use App\Services\CacheStrategyService;

$cache = new CacheStrategyService();

// Query cache
$data = $cache->queryCache('properties.all', fn() => Property::all(), 600);

// Invalidate by tag
$cache->invalidateByTag('properties');
```

### 🎨 UI Components

#### Loading State
```tsx
import { LoadingState } from '@/components/ui/LoadingState';

<LoadingState text="Loading properties..." />
```

#### Toast Notifications
```tsx
import { toast } from '@/components/ui/Toast';

toast.success('Saved successfully!');
toast.error('Something went wrong');
```

#### Empty State
```tsx
import { EmptyState } from '@/components/ui/EmptyState';

<EmptyState
  icon={<span>🏠</span>}
  title="No properties"
  action={{ label: "Add Property", onClick: handleAdd }}
/>
```

#### Error Handling
```tsx
import { ErrorBoundary } from '@/components/ui/ErrorState';

<ErrorBoundary>
  <YourApp />
</ErrorBoundary>
```

### ♿ Accessibility

#### Focus Trap
```tsx
import { useFocusTrap } from '@/hooks/useAccessibility';

const Modal = ({ isOpen }) => {
  const containerRef = useFocusTrap(isOpen);
  return <div ref={containerRef}>{/* content */}</div>;
};
```

#### ARIA Announcements
```tsx
import { useAriaLive } from '@/hooks/useAccessibility';

const { announce } = useAriaLive();
announce('5 properties found');
```

---

## 🧪 Quick Tests

### Test Security
```bash
# SQL Injection (should be blocked)
curl -X POST http://localhost:8000/api/properties \
  -d "search=1' OR '1'='1"

# Rate Limiting (should block after 100 requests)
for i in {1..150}; do curl http://localhost:8000/api/properties; done
```

### Test Performance
```php
php artisan tinker

# Query optimization
DB::enableQueryLog();
Property::with('owner')->get();
count(DB::getQueryLog()); // Should be 1 query

# Cache test
use App\Services\CacheStrategyService;
$cache = new CacheStrategyService();
$stats = $cache->getCacheStats();
```

### Test Accessibility
```bash
# Install axe-core
npm install -D @axe-core/cli

# Run audit
npx axe http://localhost:3000
```

---

## 📖 Key Files

### Backend
- `app/Http/Middleware/SqlInjectionProtection.php` - SQL injection protection
- `app/Http/Middleware/XssProtection.php` - XSS protection
- `app/Http/Middleware/DdosProtection.php` - DDoS protection
- `app/Services/FileUploadSecurityService.php` - Secure file uploads
- `app/Services/SecurityAuditService.php` - Security audit logging
- `app/Services/QueryOptimizationService.php` - Query optimization
- `app/Services/CacheStrategyService.php` - Caching strategies
- `config/security.php` - Security configuration
- `config/performance.php` - Performance configuration

### Frontend
- `src/components/ui/LoadingState.tsx` - Loading states
- `src/components/ui/EmptyState.tsx` - Empty states
- `src/components/ui/ErrorState.tsx` - Error states
- `src/components/ui/Toast.tsx` - Toast notifications
- `src/components/ui/Button.tsx` - Accessible button
- `src/components/ui/Modal.tsx` - Accessible modal
- `src/hooks/useAccessibility.ts` - Accessibility hooks
- `src/styles/design-system.css` - Design system

---

## 🆘 Troubleshooting

### Middleware not working
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Redis connection error
```bash
# Check Redis is running
redis-cli ping

# Start Redis
# Windows: redis-server.exe
# Linux: sudo service redis-server start
# Mac: brew services start redis
```

### Frontend build errors
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Permission errors (Linux/Mac)
```bash
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
```

---

## 📊 Monitoring

### Check Security Logs
```php
php artisan tinker

use App\Services\SecurityAuditService;
$audit = new SecurityAuditService();
$incidents = $audit->getSecurityIncidents(10);
```

### Check Performance Stats
```php
php artisan tinker

use App\Services\QueryOptimizationService;
$optimizer = new QueryOptimizationService();
$stats = $optimizer->getQueryStats();

use App\Services\CacheStrategyService;
$cache = new CacheStrategyService();
$cacheStats = $cache->getCacheStats();
```

---

## 📚 Next Steps

1. **Read Full Documentation**
   - [Complete Implementation Guide](COMPLETE_SECURITY_PERFORMANCE_UI_IMPLEMENTATION.md)
   - [Testing Guide](TESTING_COMPLETE_FEATURES.md)

2. **Configure for Production**
   - Enable HTTPS
   - Configure Redis
   - Set up CDN
   - Configure backups

3. **Run Tests**
   - Security tests
   - Performance benchmarks
   - Accessibility audit
   - E2E tests

4. **Monitor**
   - Security logs
   - Performance metrics
   - Error rates
   - Cache hit rates

---

## 🔗 Resources

- **Security**: OWASP Top 10, Laravel Security Docs
- **Performance**: Laravel Performance, Redis Docs
- **Accessibility**: WCAG 2.1, React A11y Docs
- **UI/UX**: Material Design, Apple HIG

---

**Need Help?** Check the full documentation or open an issue on GitHub.

**Last Updated:** November 3, 2025
