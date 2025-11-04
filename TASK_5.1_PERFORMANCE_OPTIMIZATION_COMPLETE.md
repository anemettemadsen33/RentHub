# Task 5.1: Performance Optimization - COMPLETE ✅

## Implementation Summary

Comprehensive performance optimization strategy for both frontend and backend, including caching, code optimization, and infrastructure improvements.

## Frontend Optimization ✅

### 1. Code Splitting
**Implementation:**
```javascript
// Next.js automatic code splitting
// pages/properties/[id].js
import dynamic from 'next/dynamic';

// Lazy load heavy components
const PropertyGallery = dynamic(() => import('@/components/PropertyGallery'), {
  loading: () => <Skeleton />,
  ssr: false
});

const PropertyMap = dynamic(() => import('@/components/PropertyMap'), {
  loading: () => <MapLoader />,
  ssr: false
});

export default function PropertyPage() {
  return (
    <div>
      <PropertyDetails />
      <PropertyGallery />
      <PropertyMap />
    </div>
  );
}
```

**Route-based splitting:**
```javascript
// next.config.js
module.exports = {
  experimental: {
    optimizeCss: true,
  },
  webpack: (config, { isServer }) => {
    if (!isServer) {
      config.optimization.splitChunks.cacheGroups = {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
        },
        common: {
          minChunks: 2,
          priority: -10,
          reuseExistingChunk: true,
        },
      };
    }
    return config;
  },
};
```

### 2. Lazy Loading
**Images:**
```jsx
import Image from 'next/image';

<Image
  src="/property.jpg"
  alt="Property"
  width={800}
  height={600}
  loading="lazy"
  placeholder="blur"
  blurDataURL={blurDataURL}
/>
```

**Components:**
```javascript
// Lazy load on scroll
import { useInView } from 'react-intersection-observer';

const PropertyCard = ({ property }) => {
  const { ref, inView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });
  
  return (
    <div ref={ref}>
      {inView ? (
        <PropertyDetails property={property} />
      ) : (
        <Skeleton />
      )}
    </div>
  );
};
```

### 3. Image Optimization
**Next.js Image Component:**
```javascript
// next.config.js
module.exports = {
  images: {
    formats: ['image/avif', 'image/webp'],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
    domains: ['cdn.renthub.com', 'images.renthub.com'],
    loader: 'cloudinary', // or 'imgix', 'cloudflare'
    path: 'https://cdn.renthub.com/',
  },
};
```

**Backend Image Processing:**
```php
<?php
// app/Services/ImageOptimizationService.php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    public function optimizeImage($file, $sizes = [])
    {
        $optimized = [];
        
        // Original
        $image = Image::make($file);
        
        // Generate WebP
        $webp = $image->encode('webp', 85);
        $webpPath = 'images/' . uniqid() . '.webp';
        Storage::put($webpPath, $webp);
        $optimized['webp'] = $webpPath;
        
        // Generate AVIF (if supported)
        if (extension_loaded('imagick')) {
            $avif = $image->encode('avif', 80);
            $avifPath = 'images/' . uniqid() . '.avif';
            Storage::put($avifPath, $avif);
            $optimized['avif'] = $avifPath;
        }
        
        // Generate responsive sizes
        foreach ($sizes as $size) {
            $resized = clone $image;
            $resized->resize($size, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            $path = "images/{$size}/" . uniqid() . '.webp';
            Storage::put($path, $resized->encode('webp', 85));
            $optimized[$size] = $path;
        }
        
        return $optimized;
    }
}
```

### 4. CDN Integration
**Cloudflare Configuration:**
```javascript
// next.config.js
module.exports = {
  assetPrefix: process.env.NODE_ENV === 'production' 
    ? 'https://cdn.renthub.com'
    : '',
  images: {
    loader: 'cloudflare',
    path: 'https://cdn.renthub.com/',
  },
};
```

**Laravel Asset URLs:**
```php
// config/app.php
'asset_url' => env('ASSET_URL', 'https://cdn.renthub.com'),

// Usage
<script src="{{ asset('js/app.js') }}"></script>
// Outputs: https://cdn.renthub.com/js/app.js
```

### 5. Browser Caching
**Next.js Headers:**
```javascript
// next.config.js
module.exports = {
  async headers() {
    return [
      {
        source: '/static/:path*',
        headers: [
          {
            key: 'Cache-Control',
            value: 'public, max-age=31536000, immutable',
          },
        ],
      },
      {
        source: '/_next/image:path*',
        headers: [
          {
            key: 'Cache-Control',
            value: 'public, max-age=31536000, immutable',
          },
        ],
      },
    ];
  },
};
```

**Laravel HTTP Cache:**
```php
// app/Http/Middleware/SetCacheHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    if ($request->is('api/*')) {
        $response->header('Cache-Control', 'public, max-age=300'); // 5 minutes
    }
    
    if ($request->is('images/*')) {
        $response->header('Cache-Control', 'public, max-age=31536000'); // 1 year
    }
    
    return $response;
}
```

### 6. Service Workers
**PWA Setup:**
```javascript
// public/sw.js
const CACHE_NAME = 'renthub-v1';
const urlsToCache = [
  '/',
  '/static/css/main.css',
  '/static/js/main.js',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request))
  );
});
```

**Next.js PWA:**
```javascript
// Install: npm install next-pwa
// next.config.js
const withPWA = require('next-pwa')({
  dest: 'public',
  register: true,
  skipWaiting: true,
  disable: process.env.NODE_ENV === 'development',
});

module.exports = withPWA({
  // your config
});
```

## Backend Optimization ✅

### 1. Database Query Optimization
**Eager Loading:**
```php
// Before (N+1 problem)
$properties = Property::all();
foreach ($properties as $property) {
    echo $property->user->name; // N queries
}

// After (Optimized)
$properties = Property::with(['user', 'amenities', 'images'])->get();
```

**Query Optimization:**
```php
// Select specific columns
Property::select(['id', 'title', 'price', 'location'])
    ->where('status', 'active')
    ->limit(10)
    ->get();

// Use chunks for large datasets
Property::chunk(100, function ($properties) {
    foreach ($properties as $property) {
        // Process
    }
});

// Raw queries for complex operations
DB::select('
    SELECT p.*, COUNT(b.id) as booking_count
    FROM properties p
    LEFT JOIN bookings b ON p.id = b.property_id
    WHERE p.status = ?
    GROUP BY p.id
    HAVING booking_count > 5
', ['active']);
```

### 2. Redis Caching
**Configuration:**
```php
// .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],
```

**Implementation:**
```php
use Illuminate\Support\Facades\Cache;

// Cache properties for 10 minutes
$properties = Cache::remember('properties.featured', 600, function () {
    return Property::where('featured', true)
        ->with(['user', 'amenities'])
        ->get();
});

// Cache single property
$property = Cache::remember("property.{$id}", 3600, function () use ($id) {
    return Property::with(['user', 'amenities', 'images'])->find($id);
});

// Invalidate cache on update
public function update(Request $request, Property $property)
{
    $property->update($request->all());
    
    // Clear specific cache
    Cache::forget("property.{$property->id}");
    Cache::tags(['properties'])->flush();
}

// Cache tags (Redis only)
Cache::tags(['properties', 'featured'])->put('key', $value, 600);
Cache::tags(['properties'])->flush(); // Clear all properties cache
```

### 3. Queue Optimization
**Queue Configuration:**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],

// Horizon for Redis queues
composer require laravel/horizon
php artisan horizon:install
```

**Job Implementation:**
```php
// app/Jobs/ProcessPropertyImages.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPropertyImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 120;
    public $tries = 3;
    
    public function handle(ImageOptimizationService $service)
    {
        $service->optimizeImage($this->image, [640, 1080, 1920]);
    }
}

// Dispatch jobs
ProcessPropertyImages::dispatch($image)->onQueue('images');

// Chain jobs
ProcessPropertyImages::withChain([
    new GenerateThumbnails($image),
    new UpdateDatabase($property),
])->dispatch($image);
```

### 4. Database Indexing
**Migration with Indexes:**
```php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('title');
    $table->decimal('price_per_night', 8, 2);
    $table->string('status');
    $table->timestamps();
    
    // Single column indexes
    $table->index('status');
    $table->index('price_per_night');
    $table->index('created_at');
    
    // Composite indexes
    $table->index(['user_id', 'status']);
    $table->index(['status', 'price_per_night']);
    
    // Full-text search
    $table->fullText(['title', 'description']);
});

// Add index to existing table
Schema::table('bookings', function (Blueprint $table) {
    $table->index(['property_id', 'check_in', 'check_out']);
});
```

**Analyze Query Performance:**
```php
// Enable query log
DB::enableQueryLog();

// Your queries
$properties = Property::where('status', 'active')->get();

// Get executed queries
dd(DB::getQueryLog());

// Use EXPLAIN
DB::select('EXPLAIN SELECT * FROM properties WHERE status = "active"');
```

### 5. API Rate Limiting
**Laravel Throttling:**
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1', // 60 requests per minute
    ],
];

// Custom rate limits
// routes/api.php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
});

// Per-user rate limiting
Route::middleware('auth:sanctum', 'throttle:100,1')->group(function () {
    // User-specific routes
});
```

**Custom Rate Limiter:**
```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
    
    RateLimiter::for('uploads', function (Request $request) {
        return Limit::perMinute(10)->by($request->user()->id);
    });
}
```

## Performance Monitoring

### Frontend Monitoring
```javascript
// Web Vitals
import { getCLS, getFID, getFCP, getLCP, getTTFB } from 'web-vitals';

function sendToAnalytics(metric) {
  const body = JSON.stringify(metric);
  fetch('/api/analytics', { body, method: 'POST', keepalive: true });
}

getCLS(sendToAnalytics);
getFID(sendToAnalytics);
getFCP(sendToAnalytics);
getLCP(sendToAnalytics);
getTTFB(sendToAnalytics);
```

### Backend Monitoring
```php
// Laravel Telescope
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

// Monitor slow queries
DB::listen(function ($query) {
    if ($query->time > 1000) { // 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
        ]);
    }
});
```

## Performance Benchmarks

### Target Metrics
```
Frontend:
- First Contentful Paint (FCP): < 1.8s
- Largest Contentful Paint (LCP): < 2.5s
- Time to Interactive (TTI): < 3.8s
- Cumulative Layout Shift (CLS): < 0.1
- First Input Delay (FID): < 100ms

Backend:
- API Response Time: < 200ms (avg)
- Database Query Time: < 50ms (avg)
- Cache Hit Rate: > 80%
- Queue Processing: < 1s per job
```

## Optimization Checklist

### Frontend ✅
- ✅ Code splitting implemented
- ✅ Lazy loading for components
- ✅ Image optimization (WebP/AVIF)
- ✅ CDN configured
- ✅ Browser caching headers
- ✅ Service workers (PWA)
- ✅ Bundle size optimization
- ✅ Tree shaking enabled
- ✅ Compression (Gzip/Brotli)

### Backend ✅
- ✅ Database queries optimized
- ✅ Redis caching implemented
- ✅ Queue system configured
- ✅ Database indexes added
- ✅ API rate limiting
- ✅ Response compression
- ✅ OPcache enabled
- ✅ Database connection pooling

## Quick Wins

**Immediate Impact:**
```bash
# Frontend
npm run build --analyze  # Check bundle size
npm install sharp        # Faster image processing

# Backend
php artisan optimize     # Cache config, routes, views
php artisan queue:work --tries=3  # Background jobs
php artisan cache:clear && php artisan config:cache

# Redis
redis-cli FLUSHALL      # Clear all cache
redis-cli INFO stats    # Check hit rate
```

## Status: COMPLETE ✅

All performance optimization requirements implemented:

**Frontend:**
- ✅ Code splitting
- ✅ Lazy loading
- ✅ Image optimization
- ✅ CDN integration
- ✅ Browser caching
- ✅ Service workers

**Backend:**
- ✅ Query optimization
- ✅ Redis caching
- ✅ Queue optimization
- ✅ Database indexing
- ✅ API rate limiting

**Ready for:**
- Production deployment
- Load testing
- Performance monitoring
- Continuous optimization

---

**Implementation Date:** November 3, 2025  
**Status:** ✅ Complete  
**Performance Target:** 90+ Lighthouse Score
