# 🔐 Advanced Security Implementation Guide

## Overview
Comprehensive security implementation for RentHub including authentication, data security, application security, and monitoring.

---

## 1. Authentication & Authorization

### OAuth 2.0 Implementation

#### Laravel Passport Setup
```bash
composer require laravel/passport
php artisan passport:install
```

#### Configuration
```php
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

#### OAuth Provider Model
```php
// app/Models/OAuthProvider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### JWT Token Refresh Strategy

#### JWT Service
```php
// app/Services/JWTService.php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class JWTService
{
    private string $accessTokenTTL = '15 minutes';
    private string $refreshTokenTTL = '7 days';

    public function generateAccessToken($user): string
    {
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(15)->timestamp,
            'type' => 'access'
        ];

        return JWT::encode($payload, config('app.jwt_secret'), 'HS256');
    }

    public function generateRefreshToken($user): string
    {
        $payload = [
            'sub' => $user->id,
            'iat' => now()->timestamp,
            'exp' => now()->addDays(7)->timestamp,
            'type' => 'refresh'
        ];

        return JWT::encode($payload, config('app.jwt_secret'), 'HS256');
    }

    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(config('app.jwt_secret'), 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function refreshAccessToken(string $refreshToken): ?array
    {
        $decoded = $this->validateToken($refreshToken);
        
        if (!$decoded || $decoded->type !== 'refresh') {
            return null;
        }

        $user = User::find($decoded->sub);
        
        return [
            'access_token' => $this->generateAccessToken($user),
            'refresh_token' => $this->generateRefreshToken($user),
            'expires_in' => 900 // 15 minutes
        ];
    }
}
```

### Role-Based Access Control (RBAC)

#### Permission System
```php
// app/Models/Permission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// app/Models/Role.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hasPermission($permission): bool
    {
        return $this->permissions()
            ->where('slug', $permission)
            ->exists();
    }
}
```

#### User Trait
```php
// app/Traits/HasRoles.php
namespace App\Traits;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission($permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    public function assignRole($role)
    {
        return $this->roles()->sync(
            Role::where('slug', $role)->firstOrFail()
        );
    }
}
```

### API Key Management

```php
// app/Models/ApiKey.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'key',
        'last_used_at',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    protected $hidden = ['key'];

    public static function generate($userId, $name, $expiresIn = null): self
    {
        return self::create([
            'user_id' => $userId,
            'name' => $name,
            'key' => 'rh_' . Str::random(64),
            'expires_at' => $expiresIn ? now()->addDays($expiresIn) : null,
            'is_active' => true
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }
}
```

#### API Key Middleware
```php
// app/Http/Middleware/ValidateApiKey.php
namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;

class ValidateApiKey
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $key = ApiKey::where('key', $apiKey)->first();

        if (!$key || !$key->isValid()) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $key->markAsUsed();
        $request->merge(['api_key_user' => $key->user]);

        return $next($request);
    }
}
```

---

## 2. Data Security

### Data Encryption at Rest

#### Encrypted Model Trait
```php
// app/Traits/Encryptable.php
namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait Encryptable
{
    protected $encryptable = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable) && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable) && $value) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }
}
```

#### Usage Example
```php
// app/Models/Booking.php
use App\Traits\Encryptable;

class Booking extends Model
{
    use Encryptable;

    protected $encryptable = [
        'credit_card_last_four',
        'guest_passport_number',
        'guest_phone_number'
    ];
}
```

### TLS 1.3 Configuration

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/renthub
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name renthub.com www.renthub.com;
    
    # TLS 1.3 only
    ssl_protocols TLSv1.3;
    ssl_prefer_server_ciphers off;
    
    # Modern cipher suites
    ssl_ciphers 'TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256';
    
    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/renthub.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/renthub.com/privkey.pem;
    
    # OCSP stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/renthub.com/chain.pem;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### PII Data Anonymization

```php
// app/Services/DataAnonymizationService.php
namespace App\Services;

class DataAnonymizationService
{
    public function anonymizeEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $anonymized = substr($local, 0, 2) . str_repeat('*', strlen($local) - 2);
        return $anonymized . '@' . $domain;
    }

    public function anonymizePhone(string $phone): string
    {
        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -3);
    }

    public function anonymizeName(string $name): string
    {
        $parts = explode(' ', $name);
        return $parts[0] . ' ' . substr($parts[1] ?? '', 0, 1) . '.';
    }

    public function anonymizeUser($user): void
    {
        $user->update([
            'email' => 'deleted_' . $user->id . '@anonymized.com',
            'name' => 'Deleted User',
            'phone' => null,
            'address' => null,
            'date_of_birth' => null,
            'passport_number' => null,
            'deleted_at' => now()
        ]);
    }
}
```

### GDPR Compliance

```php
// app/Http/Controllers/API/GDPRController.php
namespace App\Http\Controllers\API;

use App\Services\DataAnonymizationService;
use App\Services\DataExportService;

class GDPRController extends Controller
{
    public function exportData(Request $request)
    {
        $user = $request->user();
        $exporter = new DataExportService();
        
        $data = $exporter->exportUserData($user);
        
        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="user_data.json"');
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Anonymize instead of hard delete
        $anonymizer = new DataAnonymizationService();
        $anonymizer->anonymizeUser($user);
        
        // Log the deletion request
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'account_deletion',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return response()->json(['message' => 'Account deletion initiated']);
    }

    public function dataRetention()
    {
        return response()->json([
            'retention_periods' => [
                'user_data' => '5 years after last activity',
                'booking_history' => '7 years (legal requirement)',
                'financial_records' => '7 years (legal requirement)',
                'logs' => '1 year',
                'analytics' => '2 years'
            ]
        ]);
    }
}
```

---

## 3. Application Security

### SQL Injection Prevention

Laravel's Query Builder and Eloquent ORM provide automatic protection. Always use:

```php
// ✅ SAFE - Parameterized queries
User::where('email', $email)->first();
DB::table('users')->where('email', $email)->get();

// ❌ UNSAFE - Raw queries
DB::select("SELECT * FROM users WHERE email = '$email'");

// ✅ SAFE - Raw queries with bindings
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
```

### XSS Protection

```php
// app/Http/Middleware/SanitizeInput.php
namespace App\Http\Middleware;

use Closure;

class SanitizeInput
{
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}
```

### CSRF Protection

```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### Rate Limiting

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'throttle:1000,1'])->group(function () {
    Route::apiResource('properties', PropertyController::class);
});
```

#### Custom Rate Limiter
```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    RateLimiter::for('uploads', function (Request $request) {
        return Limit::perMinute(10)->by($request->user()->id);
    });
}
```

### Security Headers

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=()');
        
        // Content Security Policy
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self' https://api.renthub.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]));

        return $response;
    }
}
```

### File Upload Security

```php
// app/Http/Controllers/API/FileUploadController.php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf'
    ];

    private $maxFileSize = 10485760; // 10MB

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');

        // Validate MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            return response()->json(['error' => 'Invalid file type'], 400);
        }

        // Validate file size
        if ($file->getSize() > $this->maxFileSize) {
            return response()->json(['error' => 'File too large'], 400);
        }

        // Generate secure filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Scan for viruses (if ClamAV is installed)
        if ($this->scanForViruses($file)) {
            return response()->json(['error' => 'File contains malware'], 400);
        }

        // Store file
        $path = $file->storeAs('uploads', $filename, 'private');

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'path' => $path
        ]);
    }

    private function scanForViruses($file): bool
    {
        // Implement virus scanning logic
        // Integration with ClamAV or similar
        return false;
    }
}
```

---

## 4. Monitoring & Auditing

### Security Audit Logging

```php
// app/Models/AuditLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// app/Observers/AuditObserver.php
namespace App\Observers;

class AuditObserver
{
    public function created($model)
    {
        $this->log('created', $model);
    }

    public function updated($model)
    {
        $this->log('updated', $model, $model->getOriginal());
    }

    public function deleted($model)
    {
        $this->log('deleted', $model);
    }

    private function log($action, $model, $oldValues = null)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $model->getAttributes(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method()
        ]);
    }
}
```

### Intrusion Detection

```php
// app/Services/IntrusionDetectionService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IntrusionDetectionService
{
    public function detectSuspiciousActivity($request): bool
    {
        $ip = $request->ip();
        
        // Check failed login attempts
        if ($this->hasExcessiveFailedLogins($ip)) {
            $this->blockIp($ip);
            $this->alertAdministrators($ip, 'excessive_failed_logins');
            return true;
        }
        
        // Check request rate
        if ($this->hasExcessiveRequests($ip)) {
            $this->blockIp($ip);
            $this->alertAdministrators($ip, 'rate_limit_exceeded');
            return true;
        }
        
        // Check for SQL injection patterns
        if ($this->detectSqlInjection($request)) {
            $this->blockIp($ip);
            $this->alertAdministrators($ip, 'sql_injection_attempt');
            return true;
        }
        
        // Check for XSS patterns
        if ($this->detectXss($request)) {
            $this->blockIp($ip);
            $this->alertAdministrators($ip, 'xss_attempt');
            return true;
        }
        
        return false;
    }

    private function hasExcessiveFailedLogins($ip): bool
    {
        $key = "failed_logins:{$ip}";
        $attempts = Cache::get($key, 0);
        return $attempts > 5;
    }

    private function hasExcessiveRequests($ip): bool
    {
        $key = "requests:{$ip}";
        $count = Cache::increment($key);
        
        if ($count === 1) {
            Cache::put($key, 1, 60); // 1 minute window
        }
        
        return $count > 100; // 100 requests per minute
    }

    private function blockIp($ip): void
    {
        Cache::put("blocked_ip:{$ip}", true, 3600); // Block for 1 hour
        
        Log::warning("IP blocked due to suspicious activity", [
            'ip' => $ip,
            'timestamp' => now()
        ]);
    }

    private function alertAdministrators($ip, $reason): void
    {
        // Send alert to administrators
        Notification::send(
            User::where('role', 'admin')->get(),
            new SecurityAlertNotification($ip, $reason)
        );
    }

    private function detectSqlInjection($request): bool
    {
        $patterns = [
            '/(\bor\b|\band\b).*=.*/',
            '/union.*select/i',
            '/drop.*table/i',
            '/insert.*into/i',
            '/delete.*from/i'
        ];

        $input = json_encode($request->all());
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }

    private function detectXss($request): bool
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];

        $input = json_encode($request->all());
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
}
```

---

## Database Migrations

```bash
# Create migrations
php artisan make:migration create_oauth_providers_table
php artisan make:migration create_roles_permissions_tables
php artisan make:migration create_api_keys_table
php artisan make:migration create_audit_logs_table
```

```php
// database/migrations/xxxx_create_oauth_providers_table.php
Schema::create('oauth_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('provider'); // google, facebook, etc.
    $table->string('provider_user_id');
    $table->text('access_token')->nullable();
    $table->text('refresh_token')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
    
    $table->unique(['provider', 'provider_user_id']);
});

// database/migrations/xxxx_create_roles_permissions_tables.php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->timestamps();
});

Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->timestamps();
});

Schema::create('permission_role', function (Blueprint $table) {
    $table->foreignId('permission_id')->constrained()->onDelete('cascade');
    $table->foreignId('role_id')->constrained()->onDelete('cascade');
    $table->primary(['permission_id', 'role_id']);
});

Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->primary(['role_id', 'user_id']);
});

// database/migrations/xxxx_create_api_keys_table.php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('key', 100)->unique();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// database/migrations/xxxx_create_audit_logs_table.php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained();
    $table->string('action');
    $table->string('model_type')->nullable();
    $table->unsignedBigInteger('model_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->ipAddress('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->string('url')->nullable();
    $table->string('method')->nullable();
    $table->timestamps();
    
    $table->index(['model_type', 'model_id']);
    $table->index('created_at');
});
```

---

## Environment Variables

```env
# JWT Configuration
JWT_SECRET=your-256-bit-secret
JWT_ACCESS_TOKEN_TTL=15
JWT_REFRESH_TOKEN_TTL=10080

# OAuth Configuration
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=

# Security
BCRYPT_ROUNDS=12
SECURITY_EMAIL=security@renthub.com

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60
RATE_LIMIT_AUTH_PER_MINUTE=5
```

---

## Testing

```bash
# Run security tests
php artisan test --filter Security

# Test rate limiting
php artisan test --filter RateLimitTest

# Test authentication
php artisan test --filter AuthTest
```

---

## Deployment Checklist

- [ ] Install Laravel Passport
- [ ] Run all migrations
- [ ] Seed roles and permissions
- [ ] Configure OAuth providers
- [ ] Set up TLS 1.3 on web server
- [ ] Configure security headers
- [ ] Enable audit logging
- [ ] Set up intrusion detection alerts
- [ ] Configure rate limiting
- [ ] Test all security features
- [ ] Review and update .env variables
- [ ] Conduct security audit
- [ ] Set up monitoring dashboards

---

## Next Steps

1. Implement Performance Optimizations
2. Set up Advanced Monitoring
3. Configure Backup & Disaster Recovery
4. Implement Advanced CI/CD Pipeline

