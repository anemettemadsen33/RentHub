#!/bin/bash

# RentHub - Comprehensive Security, Performance & UI Implementation
# Bash Installation Script
# Run: chmod +x install-security-performance-complete-2025.sh && ./install-security-performance-complete-2025.sh

echo "========================================"
echo "  RentHub Security & Performance Setup  "
echo "========================================"
echo ""

# Check if running in backend directory
if [ ! -f "composer.json" ]; then
    echo "Error: Please run this script from the backend directory!"
    exit 1
fi

# Step 1: Install PHP dependencies
echo "[1/10] Installing PHP dependencies..."
composer require tymon/jwt-auth
composer require laravel/socialite
composer require intervention/image
composer require predis/predis
composer require --dev barryvdh/laravel-debugbar
composer require spatie/laravel-permission

# Step 2: Create directories
echo "[2/10] Creating directory structure..."
mkdir -p app/Services/Auth
mkdir -p app/Services/Security
mkdir -p app/Services/Performance
mkdir -p app/Http/Middleware/Security
mkdir -p app/Models/Security
mkdir -p app/Observers
mkdir -p database/migrations/security
mkdir -p tests/Feature/Security
mkdir -p tests/Feature/Performance

# Step 3: Publish vendor configs
echo "[3/10] Publishing vendor configurations..."
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Step 4: Generate JWT secret
echo "[4/10] Generating JWT secret..."
php artisan jwt:secret

# Step 5: Create migration files
echo "[5/10] Creating migration files..."

# API Keys migration
cat > database/migrations/2024_01_01_000001_create_api_keys_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key')->unique();
            $table->string('secret');
            $table->json('permissions')->nullable();
            $table->integer('rate_limit')->default(1000);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['key', 'is_active']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
};
EOF

# Audit Logs migration
cat > database/migrations/2024_01_01_000002_create_audit_logs_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('severity')->default('info');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('severity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
EOF

# Security Events migration
cat > database/migrations/2024_01_01_000003_create_security_events_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->ipAddress('ip_address');
            $table->text('input')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('severity')->default('medium');
            $table->boolean('blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'created_at']);
            $table->index('type');
            $table->index('severity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('security_events');
    }
};
EOF

# Performance indexes migration
cat > database/migrations/2024_01_01_000004_add_performance_indexes.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->index('status');
            $table->index('city');
            $table->index('price');
            $table->index('created_at');
            $table->index(['city', 'status']);
            $table->index(['price', 'bedrooms', 'bathrooms']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index(['check_in', 'check_out']);
            $table->index(['property_id', 'status']);
            $table->index('created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['city']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['city', 'status']);
            $table->dropIndex(['price', 'bedrooms', 'bathrooms']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['check_in', 'check_out']);
            $table->dropIndex(['property_id', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });
    }
};
EOF

# Step 6: Run migrations
echo "[6/10] Running migrations..."
php artisan migrate

# Step 7: Create service files
echo "[7/10] Creating service files..."

# OAuth2Service
cat > app/Services/Auth/OAuth2Service.php << 'EOF'
<?php

namespace App\Services\Auth;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class OAuth2Service
{
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)
            ->scopes(['email', 'profile'])
            ->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $socialUser = Socialite::driver($provider)->user();
        
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]
        );

        return $user;
    }
}
EOF

# Step 8: Update .env file
echo "[8/10] Updating .env configuration..."
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Add configuration to .env if not exists
cat >> .env << 'EOF'

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
EOF

# Step 9: Clear and cache configuration
echo "[9/10] Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Step 10: Run tests
echo "[10/10] Running tests..."
if [ -f "./vendor/bin/phpunit" ]; then
    php artisan test --filter Security
fi

echo ""
echo "========================================"
echo "  Installation Complete!"
echo "========================================"
echo ""
echo "Next Steps:"
echo "1. Update your .env file with real OAuth credentials"
echo "2. Configure Redis connection"
echo "3. Review COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md"
echo "4. Run: php artisan serve"
echo ""
echo "Documentation: COMPREHENSIVE_SECURITY_PERFORMANCE_UI_2025.md"
echo ""
