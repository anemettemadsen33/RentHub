# ⚡ Advanced Performance Optimization Guide

## Overview
Comprehensive performance optimization for RentHub including database optimization, caching strategies, and application-level improvements.

---

## 1. Database Optimization

### Query Optimization

#### Eager Loading to Prevent N+1 Queries
```php
// ❌ N+1 Query Problem
$properties = Property::all();
foreach ($properties as $property) {
    echo $property->user->name; // N queries
    foreach ($property->bookings as $booking) {
        echo $booking->guest->name; // N*M queries
    }
}

// ✅ Solution: Eager Loading
$properties = Property::with(['user', 'bookings.guest'])->get();
foreach ($properties as $property) {
    echo $property->user->name; // 1 query
    foreach ($property->bookings as $booking) {
        echo $booking->guest->name; // No additional queries
    }
}
```

#### Lazy Eager Loading
```php
// Load relationship only when needed
$properties = Property::all();

if ($needsUserData) {
    $properties->load('user');
}

if ($needsReviews) {
    $properties->load('reviews.user');
}
```

#### Query Optimization Service
```php
// app/Services/QueryOptimizationService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    public function analyzeSlowQueries()
    {
        // Enable query log
        DB::enableQueryLog();
        
        // Execute queries
        Property::with('user', 'amenities', 'images')->paginate(20);
        
        // Get queries
        $queries = DB::getQueryLog();
        
        // Analyze
        foreach ($queries as $query) {
            if ($query['time'] > 100) { // Queries taking > 100ms
                Log::warning('Slow query detected', [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $query['time']
                ]);
            }
        }
        
        return $queries;
    }

    public function optimizeQuery($query)
    {
        // Add EXPLAIN to query
        $explained = DB::select("EXPLAIN " . $query);
        
        return $explained;
    }
}
```

### Index Optimization

```php
// database/migrations/xxxx_add_performance_indexes.php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('properties', function (Blueprint $table) {
    // Single column indexes
    $table->index('status');
    $table->index('property_type');
    $table->index('city');
    $table->index('created_at');
    
    // Composite indexes for common queries
    $table->index(['status', 'property_type']); // WHERE status AND property_type
    $table->index(['city', 'price_per_night']); // WHERE city ORDER BY price
    $table->index(['user_id', 'status']); // WHERE user_id AND status
    
    // Full-text search index
    $table->fullText(['title', 'description']);
});

Schema::table('bookings', function (Blueprint $table) {
    $table->index('status');
    $table->index('check_in_date');
    $table->index('check_out_date');
    $table->index(['property_id', 'status']);
    $table->index(['user_id', 'status']);
    
    // Composite index for date range queries
    $table->index(['property_id', 'check_in_date', 'check_out_date']);
});

Schema::table('reviews', function (Blueprint $table) {
    $table->index('property_id');
    $table->index('user_id');
    $table->index(['property_id', 'created_at']);
    $table->index('rating');
});

Schema::table('amenities', function (Blueprint $table) {
    $table->index('category');
});

// Pivot table indexes
Schema::table('amenity_property', function (Blueprint $table) {
    $table->index('property_id');
    $table->index('amenity_id');
});
```

#### Index Analysis Command
```php
// app/Console/Commands/AnalyzeIndexes.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeIndexes extends Command
{
    protected $signature = 'db:analyze-indexes';
    protected $description = 'Analyze database indexes and suggest improvements';

    public function handle()
    {
        $this->info('Analyzing database indexes...');
        
        // Get unused indexes
        $unusedIndexes = DB::select("
            SELECT 
                OBJECT_NAME(i.object_id) AS table_name,
                i.name AS index_name
            FROM sys.indexes i
            LEFT JOIN sys.dm_db_index_usage_stats s ON i.object_id = s.object_id
                AND i.index_id = s.index_id
            WHERE OBJECTPROPERTY(i.object_id, 'IsUserTable') = 1
                AND s.index_id IS NULL
                AND i.name IS NOT NULL
        ");
        
        $this->table(['Table', 'Index'], 
            array_map(fn($idx) => [$idx->table_name, $idx->index_name], $unusedIndexes)
        );
        
        // Get missing indexes suggestions
        $missingIndexes = DB::select("
            SELECT 
                d.statement AS table_name,
                d.equality_columns,
                d.inequality_columns,
                d.included_columns,
                s.avg_user_impact
            FROM sys.dm_db_missing_index_details d
            INNER JOIN sys.dm_db_missing_index_groups g ON d.index_handle = g.index_handle
            INNER JOIN sys.dm_db_missing_index_group_stats s ON g.index_group_handle = s.group_handle
            WHERE s.avg_user_impact > 50
            ORDER BY s.avg_user_impact DESC
        ");
        
        $this->info('Missing indexes that would improve performance:');
        foreach ($missingIndexes as $idx) {
            $this->warn("Table: {$idx->table_name}");
            $this->line("Columns: {$idx->equality_columns}");
            $this->line("Impact: {$idx->avg_user_impact}%");
        }
        
        return 0;
    }
}
```

### Connection Pooling

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'renthub'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    
    // Connection pooling settings
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
    
    // Pool settings
    'pool' => [
        'min_connections' => 5,
        'max_connections' => 20,
        'connect_timeout' => 10,
        'wait_timeout' => 3,
        'heartbeat' => 30,
        'max_idle_time' => 60,
    ],
],
```

### Read Replicas

```php
// config/database.php
'mysql' => [
    'read' => [
        'host' => [
            '192.168.1.2', // Read replica 1
            '192.168.1.3', // Read replica 2
        ],
    ],
    'write' => [
        'host' => [
            '192.168.1.1' // Master database
        ],
    ],
    'sticky' => true, // Keep user on same connection after write
    'driver' => 'mysql',
    'database' => env('DB_DATABASE', 'renthub'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
],
```

#### Read Replica Service
```php
// app/Services/DatabaseReplicationService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseReplicationService
{
    public function forceWriteConnection()
    {
        DB::connection('mysql')->useWriteConnection();
    }

    public function forceReadConnection()
    {
        DB::connection('mysql')->useReadConnection();
    }

    public function checkReplicationLag()
    {
        $lag = DB::connection('mysql')
            ->select('SHOW SLAVE STATUS')[0]
            ->Seconds_Behind_Master ?? 0;
        
        if ($lag > 10) {
            Log::warning('Replication lag detected', ['seconds' => $lag]);
        }
        
        return $lag;
    }
}
```

### Query Caching

```php
// app/Services/QueryCacheService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryCacheService
{
    public function remember($key, $query, $ttl = 3600)
    {
        return Cache::remember($key, $ttl, function () use ($query) {
            return DB::select($query);
        });
    }

    public function rememberQuery($key, $callback, $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function invalidate($tags)
    {
        Cache::tags($tags)->flush();
    }
}

// Usage
$properties = app(QueryCacheService::class)->rememberQuery(
    'properties.featured',
    fn() => Property::where('is_featured', true)
        ->with('user', 'images')
        ->get(),
    3600 // 1 hour
);
```

---

## 2. Caching Strategy

### Redis Configuration

```php
// config/cache.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
        'read_write_timeout' => 60,
        'serializer' => Redis::SERIALIZER_IGBINARY, // Faster serialization
    ],
    
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
    
    'session' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_SESSION_DB', '2'),
    ],
],

// config/session.php
'driver' => env('SESSION_DRIVER', 'redis'),
'lifetime' => env('SESSION_LIFETIME', 120),
'expire_on_close' => false,
'encrypt' => false,
'files' => storage_path('framework/sessions'),
'connection' => 'session',
'table' => 'sessions',
'store' => env('SESSION_STORE', null),
'lottery' => [2, 100],
'cookie' => env(
    'SESSION_COOKIE',
    Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
),
'path' => '/',
'domain' => env('SESSION_DOMAIN', null),
'secure' => env('SESSION_SECURE_COOKIE'),
'http_only' => true,
'same_site' => 'lax',
```

### Application Cache

```php
// app/Services/CacheService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Cache durations
    const SHORT = 300; // 5 minutes
    const MEDIUM = 1800; // 30 minutes
    const LONG = 3600; // 1 hour
    const VERY_LONG = 86400; // 24 hours

    public function getProperty($id)
    {
        return Cache::tags(['properties'])->remember(
            "property.{$id}",
            self::LONG,
            fn() => Property::with('user', 'amenities', 'images', 'reviews')
                ->findOrFail($id)
        );
    }

    public function getFeaturedProperties()
    {
        return Cache::tags(['properties', 'featured'])->remember(
            'properties.featured',
            self::MEDIUM,
            fn() => Property::where('is_featured', true)
                ->with('user', 'images')
                ->get()
        );
    }

    public function getSearchResults($params)
    {
        $cacheKey = 'search.' . md5(json_encode($params));
        
        return Cache::tags(['search'])->remember(
            $cacheKey,
            self::SHORT,
            fn() => Property::search($params)->get()
        );
    }

    public function getUserBookings($userId)
    {
        return Cache::tags(['bookings', "user.{$userId}"])->remember(
            "user.{$userId}.bookings",
            self::MEDIUM,
            fn() => Booking::where('user_id', $userId)
                ->with('property')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function invalidateProperty($id)
    {
        Cache::tags(['properties'])->forget("property.{$id}");
        Cache::tags(['properties'])->flush();
    }

    public function invalidateUser($userId)
    {
        Cache::tags(["user.{$userId}"])->flush();
    }

    public function invalidateSearch()
    {
        Cache::tags(['search'])->flush();
    }
}
```

### Model Caching Trait

```php
// app/Traits/Cacheable.php
namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    protected static function bootCacheable()
    {
        static::created(function ($model) {
            $model->clearCache();
        });

        static::updated(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }

    public function getCacheKey(): string
    {
        return sprintf(
            "%s.%s",
            $this->getTable(),
            $this->getKey()
        );
    }

    public function getCacheTags(): array
    {
        return [
            $this->getTable(),
            $this->getCacheKey()
        ];
    }

    public function clearCache()
    {
        Cache::tags($this->getCacheTags())->flush();
    }

    public static function cached($id, $ttl = 3600)
    {
        $instance = new static;
        $cacheKey = sprintf("%s.%s", $instance->getTable(), $id);
        
        return Cache::tags([$instance->getTable()])->remember(
            $cacheKey,
            $ttl,
            fn() => static::findOrFail($id)
        );
    }
}

// Usage in models
class Property extends Model
{
    use Cacheable;
}

// Usage
$property = Property::cached(1); // Automatically cached
```

### Fragment Cache

```php
// app/View/Components/CachedPropertyCard.php
namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class CachedPropertyCard extends Component
{
    public $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function render()
    {
        return Cache::remember(
            "property_card.{$this->property->id}",
            3600,
            fn() => view('components.property-card', ['property' => $this->property])
        );
    }
}
```

### CDN Cache

```nginx
# Nginx CDN configuration
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header X-Cache-Status $upstream_cache_status;
    
    # Enable proxy cache
    proxy_cache my_cache;
    proxy_cache_valid 200 1y;
    proxy_cache_use_stale error timeout http_500 http_502 http_503 http_504;
}

# Cache configuration
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=my_cache:10m max_size=1g inactive=60m;
```

```php
// config/filesystems.php
'cloudfront' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'cloudfront' => [
        'domain' => env('CLOUDFRONT_DOMAIN'),
        'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID'),
        'private_key' => env('CLOUDFRONT_PRIVATE_KEY'),
    ],
],
```

### Browser Cache

```php
// app/Http/Middleware/SetCacheHeaders.php
namespace App\Http\Middleware;

use Closure;

class SetCacheHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Static assets - cache for 1 year
        if ($request->is('assets/*')) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
        }
        
        // API responses - cache for 5 minutes
        elseif ($request->is('api/*')) {
            $response->header('Cache-Control', 'public, max-age=300');
        }
        
        // Dynamic pages - no cache
        else {
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}
```

---

## 3. Application-Level Optimization

### Lazy Loading

```php
// app/Models/Property.php
class Property extends Model
{
    // Lazy load relationships
    protected $with = []; // Don't auto-load anything
    
    public function scopeWithBasicInfo($query)
    {
        return $query->select([
            'id',
            'title',
            'price_per_night',
            'city',
            'country',
            'thumbnail_url'
        ]);
    }

    public function scopeWithFullDetails($query)
    {
        return $query->with([
            'user:id,name,avatar',
            'amenities:id,name,icon',
            'images:id,property_id,url',
            'reviews' => function ($query) {
                $query->limit(5)->latest();
            }
        ]);
    }
}

// Usage
$properties = Property::withBasicInfo()->paginate(20); // Fast listing
$property = Property::withFullDetails()->findOrFail($id); // Detailed view
```

### Chunk Processing

```php
// app/Console/Commands/ProcessBookings.php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessBookings extends Command
{
    protected $signature = 'bookings:process';

    public function handle()
    {
        $this->info('Processing bookings...');
        
        $processed = 0;
        
        // Process in chunks to avoid memory issues
        Booking::where('status', 'pending')
            ->chunk(1000, function ($bookings) use (&$processed) {
                foreach ($bookings as $booking) {
                    $this->processBooking($booking);
                    $processed++;
                }
                
                $this->info("Processed {$processed} bookings");
            });
        
        $this->info("Total processed: {$processed}");
    }

    private function processBooking($booking)
    {
        // Process booking logic
    }
}
```

### Queue Optimization

```php
// config/queue.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => 5,
    'after_commit' => false,
],

// Multiple queues for priority
'connections' => [
    'high' => ['driver' => 'redis', 'queue' => 'high'],
    'default' => ['driver' => 'redis', 'queue' => 'default'],
    'low' => ['driver' => 'redis', 'queue' => 'low'],
],
```

```php
// app/Jobs/ProcessPayment.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $maxExceptions = 3;

    public function __construct(
        public Booking $booking
    ) {
        $this->onQueue('high'); // High priority
    }

    public function handle()
    {
        // Process payment logic
    }

    public function failed(\Throwable $exception)
    {
        // Handle failed job
        Log::error('Payment processing failed', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

### Asset Optimization

```javascript
// webpack.mix.js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .extract(['vue', 'axios']) // Vendor splitting
   .sass('resources/sass/app.scss', 'public/css')
   .version() // Cache busting
   .sourceMaps(false, 'source-map')
   .options({
       processCssUrls: false,
       postCss: [
           require('autoprefixer'),
           require('cssnano')({
               preset: ['default', {
                   discardComments: {
                       removeAll: true,
                   },
               }]
           })
       ]
   });

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}
```

### Image Optimization

```php
// app/Services/ImageOptimizationService.php
namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    public function optimize($imagePath, $quality = 85)
    {
        $img = Image::make(Storage::path($imagePath));
        
        // Resize if too large
        if ($img->width() > 2000) {
            $img->resize(2000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Optimize quality
        $img->save(null, $quality);
        
        return $img;
    }

    public function createThumbnails($imagePath)
    {
        $sizes = [
            'thumbnail' => [300, 200],
            'medium' => [800, 600],
            'large' => [1200, 900]
        ];
        
        $thumbnails = [];
        
        foreach ($sizes as $name => $dimensions) {
            $img = Image::make(Storage::path($imagePath));
            $img->fit($dimensions[0], $dimensions[1]);
            
            $thumbnailPath = str_replace('.jpg', "_{$name}.jpg", $imagePath);
            $img->save(Storage::path($thumbnailPath), 85);
            
            $thumbnails[$name] = $thumbnailPath;
        }
        
        return $thumbnails;
    }

    public function convertToWebP($imagePath)
    {
        $img = Image::make(Storage::path($imagePath));
        $webpPath = str_replace(['.jpg', '.png'], '.webp', $imagePath);
        
        $img->encode('webp', 85)->save(Storage::path($webpPath));
        
        return $webpPath;
    }
}
```

---

## 4. Monitoring & Profiling

### Laravel Telescope

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', true),

'storage' => [
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'chunk' => 1000,
    ],
],

'watchers' => [
    Watchers\CacheWatcher::class => env('TELESCOPE_CACHE_WATCHER', true),
    Watchers\CommandWatcher::class => env('TELESCOPE_COMMAND_WATCHER', true),
    Watchers\DumpWatcher::class => env('TELESCOPE_DUMP_WATCHER', true),
    Watchers\EventWatcher::class => env('TELESCOPE_EVENT_WATCHER', true),
    Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
    Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
    Watchers\LogWatcher::class => env('TELESCOPE_LOG_WATCHER', true),
    Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),
    Watchers\ModelWatcher::class => env('TELESCOPE_MODEL_WATCHER', true),
    Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),
    Watchers\QueryWatcher::class => env('TELESCOPE_QUERY_WATCHER', true),
    Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),
    Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
    Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
    Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', true),
],
```

### Performance Monitoring

```php
// app/Http/Middleware/PerformanceMonitoring.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoring
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $memoryStart = memory_get_usage();
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        $memoryUsed = memory_get_usage() - $memoryStart;
        
        if ($duration > 1) { // Slow request > 1 second
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => $duration,
                'memory' => $memoryUsed,
                'user_id' => $request->user()?->id
            ]);
        }
        
        $response->headers->set('X-Response-Time', round($duration * 1000, 2) . 'ms');
        $response->headers->set('X-Memory-Usage', round($memoryUsed / 1024 / 1024, 2) . 'MB');
        
        return $response;
    }
}
```

---

## Environment Variables

```env
# Cache Configuration
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Database Optimization
DB_CONNECTION=mysql
DB_READ_HOST=read-replica.example.com
DB_WRITE_HOST=master.example.com

# CDN Configuration
CDN_URL=https://cdn.renthub.com
CLOUDFRONT_DOMAIN=d111111abcdef8.cloudfront.net

# Performance
QUEUE_WORKERS=5
HORIZON_BALANCE_STRATEGY=auto
```

---

## Performance Testing

```bash
# Load testing with Apache Bench
ab -n 10000 -c 100 https://api.renthub.com/properties

# Load testing with wrk
wrk -t12 -c400 -d30s https://api.renthub.com/properties

# Database query analysis
php artisan db:analyze-indexes
php artisan telescope:prune --hours=48
```

---

## Deployment Checklist

- [ ] Enable Redis caching
- [ ] Configure read replicas
- [ ] Add database indexes
- [ ] Enable query caching
- [ ] Set up CDN
- [ ] Optimize images
- [ ] Enable OPcache
- [ ] Configure connection pooling
- [ ] Set up queue workers
- [ ] Enable Telescope monitoring
- [ ] Configure cache headers
- [ ] Optimize Composer autoload
- [ ] Enable asset versioning
- [ ] Set up performance monitoring

---

## Next Steps

1. Implement Advanced Monitoring with Prometheus/Grafana
2. Set up Load Balancing
3. Configure Auto-scaling
4. Implement Database Sharding

