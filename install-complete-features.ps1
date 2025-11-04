# RentHub - Complete Security, Performance & UI/UX Installation Script
# PowerShell Script for Windows

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "RentHub - Complete Features Installation" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the correct directory
if (-not (Test-Path "backend\composer.json")) {
    Write-Host "❌ Error: Please run this script from the RentHub root directory" -ForegroundColor Red
    exit 1
}

# Backend Setup
Write-Host "📦 Setting up Backend..." -ForegroundColor Yellow
Write-Host ""

Set-Location backend

# Install/Update Composer dependencies
Write-Host "Installing Composer dependencies..." -ForegroundColor Cyan
composer install --no-interaction --prefer-dist --optimize-autoloader

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Composer installation failed" -ForegroundColor Red
    exit 1
}

# Copy environment file if it doesn't exist
if (-not (Test-Path ".env")) {
    Write-Host "Creating .env file..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    Write-Host "⚠️  Please configure your .env file before continuing" -ForegroundColor Yellow
}

# Generate application key if not set
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "APP_KEY=.+") {
    Write-Host "Generating application key..." -ForegroundColor Cyan
    php artisan key:generate --no-interaction
}

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Cyan
php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Migration failed - please check your database configuration" -ForegroundColor Yellow
}

# Clear all caches
Write-Host "Clearing caches..." -ForegroundColor Cyan
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
Write-Host "Optimizing application..." -ForegroundColor Cyan
php artisan config:cache
php artisan route:cache

# Register middleware
Write-Host ""
Write-Host "📝 Registering Security Middleware..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Please add the following middleware to app/Http/Kernel.php:" -ForegroundColor Cyan
Write-Host ""
Write-Host "In the 'web' middleware group:" -ForegroundColor White
Write-Host "    \App\Http\Middleware\XssProtection::class," -ForegroundColor Green
Write-Host "    \App\Http\Middleware\SecurityHeadersMiddleware::class," -ForegroundColor Green
Write-Host ""
Write-Host "In the 'api' middleware group:" -ForegroundColor White
Write-Host "    \App\Http\Middleware\SqlInjectionProtection::class," -ForegroundColor Green
Write-Host "    \App\Http\Middleware\DdosProtection::class," -ForegroundColor Green
Write-Host "    \App\Http\Middleware\CompressionMiddleware::class," -ForegroundColor Green
Write-Host ""

Set-Location ..

# Frontend Setup
Write-Host "📦 Setting up Frontend..." -ForegroundColor Yellow
Write-Host ""

if (Test-Path "frontend\package.json") {
    Set-Location frontend
    
    Write-Host "Installing npm dependencies..." -ForegroundColor Cyan
    npm install
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ npm installation failed" -ForegroundColor Red
        Set-Location ..
        exit 1
    }
    
    # Build frontend
    Write-Host "Building frontend assets..." -ForegroundColor Cyan
    npm run build
    
    Set-Location ..
} else {
    Write-Host "⚠️  Frontend directory not found, skipping..." -ForegroundColor Yellow
}

# Create necessary directories
Write-Host ""
Write-Host "📁 Creating necessary directories..." -ForegroundColor Yellow
$directories = @(
    "backend\storage\app\private\uploads",
    "backend\storage\logs\security",
    "backend\storage\framework\cache\data"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Force -Path $dir | Out-Null
        Write-Host "✓ Created $dir" -ForegroundColor Green
    }
}

# Set permissions (Windows)
Write-Host ""
Write-Host "🔒 Setting permissions..." -ForegroundColor Yellow
$storagePath = "backend\storage"
if (Test-Path $storagePath) {
    icacls $storagePath /grant "Users:(OI)(CI)F" /T | Out-Null
    Write-Host "✓ Storage permissions set" -ForegroundColor Green
}

# Check Redis connection
Write-Host ""
Write-Host "🔍 Checking Redis connection..." -ForegroundColor Yellow
try {
    $redisTest = redis-cli ping 2>$null
    if ($redisTest -eq "PONG") {
        Write-Host "✓ Redis is running" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Redis is not running - caching features may not work" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Could not check Redis - make sure it's installed and running" -ForegroundColor Yellow
}

# Summary
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "✅ Installation Complete!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📚 Next Steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Configure your .env file:" -ForegroundColor White
Write-Host "   - Database credentials" -ForegroundColor Gray
Write-Host "   - Redis configuration" -ForegroundColor Gray
Write-Host "   - Security settings" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Register middleware in app/Http/Kernel.php (see above)" -ForegroundColor White
Write-Host ""
Write-Host "3. Start the development server:" -ForegroundColor White
Write-Host "   cd backend" -ForegroundColor Gray
Write-Host "   php artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Run tests:" -ForegroundColor White
Write-Host "   php artisan test" -ForegroundColor Gray
Write-Host ""
Write-Host "📖 Documentation: COMPLETE_SECURITY_PERFORMANCE_UI_IMPLEMENTATION.md" -ForegroundColor Cyan
Write-Host ""
Write-Host "🎉 Happy coding!" -ForegroundColor Green
Write-Host ""
