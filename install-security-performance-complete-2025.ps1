# RentHub - Comprehensive Security, Performance & UI Implementation
# PowerShell Installation Script
# Run: .\install-security-performance-complete-2025.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  RentHub Security & Performance Setup  " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running in backend directory
if (-Not (Test-Path ".\composer.json")) {
    Write-Host "Error: Please run this script from the backend directory!" -ForegroundColor Red
    exit 1
}

# Step 1: Install PHP dependencies
Write-Host "[1/10] Installing PHP dependencies..." -ForegroundColor Yellow
composer require tymon/jwt-auth
composer require laravel/socialite
composer require intervention/image
composer require predis/predis
composer require --dev barryvdh/laravel-debugbar
composer require spatie/laravel-permission

# Step 2: Create directories
Write-Host "[2/10] Creating directory structure..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "app\Services\Auth"
New-Item -ItemType Directory -Force -Path "app\Services\Security"
New-Item -ItemType Directory -Force -Path "app\Services\Performance"
New-Item -ItemType Directory -Force -Path "app\Http\Middleware\Security"
New-Item -ItemType Directory -Force -Path "app\Models\Security"
New-Item -ItemType Directory -Force -Path "app\Observers"
New-Item -ItemType Directory -Force -Path "database\migrations\security"
New-Item -ItemType Directory -Force -Path "tests\Feature\Security"
New-Item -ItemType Directory -Force -Path "tests\Feature\Performance"

# Step 3: Publish vendor configs
Write-Host "[3/10] Publishing vendor configurations..." -ForegroundColor Yellow
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Step 4: Generate JWT secret
Write-Host "[4/10] Generating JWT secret..." -ForegroundColor Yellow
php artisan jwt:secret

# Step 5: Create migration files
Write-Host "[5/10] Creating migration files..." -ForegroundColor Yellow

# API Keys migration
$apiKeysMigration = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_keys', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('user_id')->constrained()->onDelete('cascade');
            `$table->string('name');
            `$table->string('key')->unique();
            `$table->string('secret');
            `$table->json('permissions')->nullable();
            `$table->integer('rate_limit')->default(1000);
            `$table->timestamp('last_used_at')->nullable();
            `$table->timestamp('expires_at')->nullable();
            `$table->boolean('is_active')->default(true);
            `$table->timestamps();

            `$table->index(['key', 'is_active']);
            `$table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
};
"@

$apiKeysMigration | Out-File -FilePath "database\migrations\2024_01_01_000001_create_api_keys_table.php" -Encoding UTF8

# Audit Logs migration
$auditLogsMigration = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            `$table->string('action');
            `$table->string('model_type')->nullable();
            `$table->unsignedBigInteger('model_id')->nullable();
            `$table->ipAddress('ip_address')->nullable();
            `$table->text('user_agent')->nullable();
            `$table->json('old_values')->nullable();
            `$table->json('new_values')->nullable();
            `$table->string('url')->nullable();
            `$table->string('method')->nullable();
            `$table->string('severity')->default('info');
            `$table->timestamps();

            `$table->index(['user_id', 'created_at']);
            `$table->index(['action', 'created_at']);
            `$table->index('severity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
"@

$auditLogsMigration | Out-File -FilePath "database\migrations\2024_01_01_000002_create_audit_logs_table.php" -Encoding UTF8

# Security Events migration
$securityEventsMigration = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('security_events', function (Blueprint `$table) {
            `$table->id();
            `$table->string('type'); // sql_injection, xss, path_traversal, etc.
            `$table->ipAddress('ip_address');
            `$table->text('input')->nullable();
            `$table->text('user_agent')->nullable();
            `$table->string('url')->nullable();
            `$table->string('severity')->default('medium'); // low, medium, high, critical
            `$table->boolean('blocked')->default(false);
            `$table->timestamp('blocked_until')->nullable();
            `$table->timestamps();

            `$table->index(['ip_address', 'created_at']);
            `$table->index('type');
            `$table->index('severity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('security_events');
    }
};
"@

$securityEventsMigration | Out-File -FilePath "database\migrations\2024_01_01_000003_create_security_events_table.php" -Encoding UTF8

# Add indexes migration
$indexesMigration = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint `$table) {
            `$table->index('status');
            `$table->index('city');
            `$table->index('price');
            `$table->index('created_at');
            `$table->index(['city', 'status']);
            `$table->index(['price', 'bedrooms', 'bathrooms']);
        });

        Schema::table('bookings', function (Blueprint `$table) {
            `$table->index('status');
            `$table->index(['check_in', 'check_out']);
            `$table->index(['property_id', 'status']);
            `$table->index('created_at');
        });

        Schema::table('users', function (Blueprint `$table) {
            `$table->index('email');
            `$table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint `$table) {
            `$table->dropIndex(['status']);
            `$table->dropIndex(['city']);
            `$table->dropIndex(['price']);
            `$table->dropIndex(['created_at']);
            `$table->dropIndex(['city', 'status']);
            `$table->dropIndex(['price', 'bedrooms', 'bathrooms']);
        });

        Schema::table('bookings', function (Blueprint `$table) {
            `$table->dropIndex(['status']);
            `$table->dropIndex(['check_in', 'check_out']);
            `$table->dropIndex(['property_id', 'status']);
            `$table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint `$table) {
            `$table->dropIndex(['email']);
            `$table->dropIndex(['created_at']);
        });
    }
};
"@

$indexesMigration | Out-File -FilePath "database\migrations\2024_01_01_000004_add_performance_indexes.php" -Encoding UTF8

# Step 6: Run migrations
Write-Host "[6/10] Running migrations..." -ForegroundColor Yellow
php artisan migrate

# Step 7: Create service files
Write-Host "[7/10] Creating service files..." -ForegroundColor Yellow

# OAuth2Service
$oAuth2Service = @"
<?php

namespace App\Services\Auth;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class OAuth2Service
{
    public function redirectToProvider(string `$provider)
    {
        return Socialite::driver(`$provider)
            ->scopes(['email', 'profile'])
            ->redirect();
    }

    public function handleProviderCallback(string `$provider)
    {
        `$socialUser = Socialite::driver(`$provider)->user();
        
        `$user = User::updateOrCreate(
            ['email' => `$socialUser->getEmail()],
            [
                'name' => `$socialUser->getName(),
                'avatar' => `$socialUser->getAvatar(),
                'provider' => `$provider,
                'provider_id' => `$socialUser->getId(),
                'email_verified_at' => now(),
            ]
        );

        return `$user;
    }
}
"@

$oAuth2Service | Out-File -FilePath "app\Services\Auth\OAuth2Service.php" -Encoding UTF8

# Step 8: Update .env file
Write-Host "[8/10] Updating .env configuration..." -ForegroundColor Yellow
if (-Not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

# Add JWT and OAuth configurations
$envAdditions = @"

# JWT Configuration
JWT_SECRET=your-jwt-secret-key
JWT_TTL=60
JWT_REFRESH_TTL=20160

# OAuth2 Configuration
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=your-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-facebook-client-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Security Configuration
SECURITY_RATE_LIMIT_PER_MINUTE=60
SECURITY_MAX_LOGIN_ATTEMPTS=5
SECURITY_LOCKOUT_DURATION=900
"@

Add-Content -Path ".env" -Value $envAdditions

# Step 9: Clear and cache configuration
Write-Host "[9/10] Clearing and caching configuration..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Step 10: Run tests
Write-Host "[10/10] Running tests..." -ForegroundColor Yellow
if (Test-Path ".\vendor\bin\phpunit") {
    php artisan test --filter Security
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Installation Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Update your .env file with real OAuth credentials" -ForegroundColor White
Write-Host "2. Configure Redis connection" -ForegroundColor White
Write-Host "3. Review COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md" -ForegroundColor White
Write-Host "4. Run: php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "Documentation: COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md" -ForegroundColor Yellow
Write-Host ""
