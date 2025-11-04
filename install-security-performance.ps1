# RentHub Security & Performance Installation Script (PowerShell)

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  RentHub Security & Performance  " -ForegroundColor Cyan
Write-Host "      Installation Script          " -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check if running in backend directory
if (!(Test-Path "composer.json")) {
    Write-Host "❌ Error: Please run this script from the backend directory" -ForegroundColor Red
    exit 1
}

Write-Host "📦 Step 1: Installing Dependencies..." -ForegroundColor Yellow
composer require predis/predis --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Warning: Failed to install predis, continuing..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🗄️  Step 2: Running Database Migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error: Database migration failed" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🔧 Step 3: Publishing Configuration..." -ForegroundColor Yellow
php artisan vendor:publish --tag=config --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Warning: Failed to publish config, continuing..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🧹 Step 4: Clearing Caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

Write-Host ""
Write-Host "📝 Step 5: Generating Application Key..." -ForegroundColor Yellow
if (!(Select-String -Path ".env" -Pattern "APP_KEY=" -Quiet)) {
    php artisan key:generate
}

Write-Host ""
Write-Host "🔐 Step 6: Setting up Security Configuration..." -ForegroundColor Yellow

# Check if .env exists
if (!(Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✅ Created .env file" -ForegroundColor Green
}

# Add security and performance configurations to .env if not present
$envContent = Get-Content ".env" -Raw

$securityConfig = @"

# Security Configuration
RATE_LIMIT_ENABLED=true
RATE_LIMIT_DEFAULT=60:1
GDPR_DATA_RETENTION_DAYS=365

# Performance Configuration
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PROPERTY_TTL=3600
CACHE_SEARCH_TTL=1800
SLOW_QUERY_THRESHOLD=100
COMPRESSION_ENABLED=true
COMPRESSION_PREFER_BROTLI=true

# Monitoring
MONITORING_ENABLED=true
SLOW_REQUEST_THRESHOLD=1000
LOG_SLOW_REQUESTS=true
"@

if (!$envContent.Contains("RATE_LIMIT_ENABLED")) {
    Add-Content -Path ".env" -Value $securityConfig
    Write-Host "✅ Added security and performance configuration to .env" -ForegroundColor Green
}

Write-Host ""
Write-Host "🔄 Step 7: Creating Performance Indexes..." -ForegroundColor Yellow
php artisan migrate --path=database/migrations/2025_01_03_200001_create_performance_indexes.php --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Warning: Performance indexes migration may have already run" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📊 Step 8: Optimizing Application..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host ""
Write-Host "✅ Installation Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "      Next Steps                  " -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Configure Redis connection in .env:" -ForegroundColor White
Write-Host "   REDIS_HOST=127.0.0.1" -ForegroundColor Gray
Write-Host "   REDIS_PASSWORD=null" -ForegroundColor Gray
Write-Host "   REDIS_PORT=6379" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Test security features:" -ForegroundColor White
Write-Host "   php artisan test --filter SecurityTest" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Check health status:" -ForegroundColor White
Write-Host "   curl http://localhost:8000/api/health" -ForegroundColor Gray
Write-Host ""
Write-Host "4. View documentation:" -ForegroundColor White
Write-Host "   SECURITY_PERFORMANCE_IMPLEMENTATION_COMPLETE.md" -ForegroundColor Gray
Write-Host ""
Write-Host "🎉 Your application is now secured and optimized!" -ForegroundColor Green
Write-Host ""
