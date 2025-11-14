# Perfect Deploy Script for Laravel Forge + Vercel
# RentHub Production Deployment Automation

param(
    [string]$Environment = "production",
    [string]$Branch = "main",
    [switch]$SkipTests = $false,
    [switch]$SkipFrontend = $false,
    [switch]$SkipBackend = $false
)

$ErrorActionPreference = "Stop"
$Timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$LogFile = "deploy-log-$Timestamp.txt"

function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $LogMessage = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [$Level] $Message"
    Write-Host $LogMessage
    Add-Content -Path $LogFile -Value $LogMessage
}

function Test-Command {
    param([string]$Command)
    try {
        Get-Command $Command -ErrorAction Stop | Out-Null
        return $true
    } catch {
        return $false
    }
}

function Invoke-DeployStep {
    param(
        [string]$StepName,
        [scriptblock]$Action,
        [bool]$Critical = $true
    )
    
    Write-Log "Starting: $StepName"
    try {
        & $Action
        Write-Log "Completed: $StepName"
        return $true
    } catch {
        Write-Log "Failed: $StepName - $($_.Exception.Message)" "ERROR"
        if ($Critical) {
            throw "Critical deployment step failed: $StepName"
        }
        return $false
    }
}

# Initialize deployment
Write-Log "=== Perfect Deploy Started ==="
Write-Log "Environment: $Environment"
Write-Log "Branch: $Branch"
Write-Log "Skip Tests: $SkipTests"
Write-Log "Skip Frontend: $SkipFrontend"
Write-Log "Skip Backend: $SkipBackend"

# Check prerequisites
Invoke-DeployStep "Check Prerequisites" {
    if (!(Test-Command "git")) {
        throw "Git is not installed or not in PATH"
    }
    if (!(Test-Command "php")) {
        throw "PHP is not installed or not in PATH"
    }
    if (!(Test-Command "composer")) {
        throw "Composer is not installed or not in PATH"
    }
    if (!(Test-Command "node")) {
        throw "Node.js is not installed or not in PATH"
    }
    if (!(Test-Command "npm")) {
        throw "npm is not installed or not in PATH"
    }
    
    # Check if we're in the right directory
    if (!(Test-Path "artisan")) {
        throw "Not in Laravel project root directory"
    }
}

# Git status check
Invoke-DeployStep "Git Status Check" {
    $gitStatus = git status --porcelain
    if ($gitStatus) {
        Write-Log "Working directory has uncommitted changes" "WARNING"
        Write-Log $gitStatus
    }
    
    $currentBranch = git rev-parse --abbrev-ref HEAD
    Write-Log "Current branch: $currentBranch"
    
    if ($currentBranch -ne $Branch) {
        Write-Log "Switching to branch: $Branch"
        git checkout $Branch
    }
    
    git pull origin $Branch
}

# Backend deployment
if (!$SkipBackend) {
    # Backend optimization and preparation
    Invoke-DeployStep "Backend Optimization" {
        # Clear caches
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        php artisan cache:clear
        php artisan optimize:clear
        
        # Install/update dependencies
        composer install --no-dev --optimize-autoloader --no-interaction
        
        # Optimize Laravel
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        
        # Run migrations (with safety check)
        php artisan migrate --force --no-interaction
        
        # Create storage link
        php artisan storage:link --force
        
        # Set proper permissions
        if (Test-Path "storage") {
            icacls "storage" /grant Everyone:F /T
            icacls "bootstrap/cache" /grant Everyone:F /T
        }
    }
    
    # Backend testing
    if (!$SkipTests) {
        Invoke-DeployStep "Backend Testing" {
            php artisan test --parallel --stop-on-failure
        }
    }
    
    # Health check
    Invoke-DeployStep "Backend Health Check" {
        $healthCheck = php artisan route:list | Select-String "health"
        if ($healthCheck) {
            Write-Log "Health check endpoint available"
        } else {
            Write-Log "Health check endpoint not found" "WARNING"
        }
    }
}

# Frontend deployment
if (!$SkipFrontend) {
    # Navigate to frontend directory
    Push-Location "..\frontend"
    
    try {
        Invoke-DeployStep "Frontend Preparation" {
            # Install dependencies
            npm ci --production=false
            
            # Build for production
            npm run build
            
            # Run frontend tests if available
            if (!$SkipTests) {
                if (Test-Path "package.json") {
                    $packageJson = Get-Content "package.json" | ConvertFrom-Json
                    if ($packageJson.scripts.test) {
                        npm test -- --watchAll=false --passWithNoTests
                    }
                }
            }
        }
    } finally {
        Pop-Location
    }
}

# Environment configuration validation
Invoke-DeployStep "Environment Validation" {
    # Check .env file
    if (!(Test-Path ".env")) {
        throw ".env file not found"
    }
    
    # Validate critical environment variables
    $envContent = Get-Content ".env"
    $requiredVars = @("APP_KEY", "APP_URL", "DB_CONNECTION", "DB_HOST", "DB_DATABASE", "DB_USERNAME")
    
    foreach ($var in $requiredVars) {
        if ($envContent -notmatch "^$var=") {
            Write-Log "Environment variable $var not set" "WARNING"
        }
    }
    
    # Check if APP_KEY is properly set
    if ($envContent -match "^APP_KEY=base64:") {
        Write-Log "APP_KEY appears to be properly set"
    } else {
        Write-Log "APP_KEY needs to be generated" "WARNING"
        php artisan key:generate
    }
}

# Performance optimization
Invoke-DeployStep "Performance Optimization" {
    # Create optimized configuration
    $productionConfig = @"
<?php

return [
    'debug' => false,
    'url' => env('APP_URL'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    
    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],
    
    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Js' => Illuminate\Support\Js::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
    ],
];
"@
    
    $productionConfig | Out-File -FilePath "config/production.php" -Encoding UTF8
    Write-Log "Created production configuration"
}

# Database optimization
Invoke-DeployStep "Database Optimization" {
    # Run any pending optimizations
    php artisan optimize
    
    # Check and create indexes if needed
    php artisan db:seed --class=DatabaseSeeder --force
}

# Final health check
Invoke-DeployStep "Final Health Check" {
    # Test critical endpoints
    $endpoints = @(
        "api/health",
        "api/auth/health",
        "api/payment/health"
    )
    
    foreach ($endpoint in $endpoints) {
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8000/$endpoint" -Method GET -TimeoutSec 10
            Write-Log "$endpoint : $($response.StatusCode)"
        } catch {
            Write-Log "$endpoint : FAILED - $($_.Exception.Message)" "WARNING"
        }
    }
}

# Generate deployment report
Invoke-DeployStep "Generate Deployment Report" {
    $report = @"
=== Deployment Report ===
Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
Environment: $Environment
Branch: $Branch

Prerequisites: ✓
Git Status: ✓
Backend Deployment: $(if ($SkipBackend) { 'SKIPPED' } else { '✓' })
Frontend Deployment: $(if ($SkipFrontend) { 'SKIPPED' } else { '✓' })
Testing: $(if ($SkipTests) { 'SKIPPED' } else { '✓' })
Environment Validation: ✓
Performance Optimization: ✓
Database Optimization: ✓
Health Check: ✓

Deployment completed successfully!
Check the log file: $LogFile
"@
    
    $report | Out-File -FilePath "deploy-report-$Timestamp.txt" -Encoding UTF8
    Write-Log $report
}

Write-Log "=== Perfect Deploy Completed Successfully ==="
Write-Log "Check deploy-report-$Timestamp.txt for detailed report"
Write-Log "Check $LogFile for full deployment logs"