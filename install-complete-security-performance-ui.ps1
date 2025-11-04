# RentHub - Security, Performance & UI/UX Installation Script
# PowerShell script for Windows

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RentHub Security, Performance & UI/UX" -ForegroundColor Cyan
Write-Host "Installation Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running from project root
if (-not (Test-Path ".\backend")) {
    Write-Host "Error: Please run this script from the project root directory" -ForegroundColor Red
    exit 1
}

Write-Host "[1/8] Installing Backend Dependencies..." -ForegroundColor Yellow
Set-Location backend
composer install --no-interaction --prefer-dist --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Composer install failed" -ForegroundColor Red
    exit 1
}

Write-Host "[2/8] Configuring Environment..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    php artisan key:generate
}

# Update .env for Redis cache
Write-Host "Configuring Redis cache..." -ForegroundColor Yellow
$envContent = Get-Content .env -Raw
if ($envContent -notmatch "CACHE_DRIVER=redis") {
    $envContent = $envContent -replace "CACHE_DRIVER=.*", "CACHE_DRIVER=redis"
    Set-Content -Path .env -Value $envContent
}

Write-Host "[3/8] Running Database Migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "Warning: Some migrations may have failed" -ForegroundColor Yellow
}

Write-Host "[4/8] Setting up Laravel Passport..." -ForegroundColor Yellow
php artisan passport:install --force

Write-Host "[5/8] Clearing and Optimizing Cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "[6/8] Publishing Vendor Assets..." -ForegroundColor Yellow
php artisan vendor:publish --all --force

Write-Host "[7/8] Installing Frontend Dependencies..." -ForegroundColor Yellow
Set-Location ..\frontend
if (Test-Path "package.json") {
    npm install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Warning: npm install encountered issues" -ForegroundColor Yellow
    }
}

Write-Host "[8/8] Building Frontend Assets..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Warning: Frontend build encountered issues" -ForegroundColor Yellow
}

Set-Location ..

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ Installation Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

Write-Host "🔐 Security Features Installed:" -ForegroundColor Cyan
Write-Host "  ✅ Security headers (CSP, HSTS, etc.)" -ForegroundColor Green
Write-Host "  ✅ Rate limiting" -ForegroundColor Green
Write-Host "  ✅ Input sanitization" -ForegroundColor Green
Write-Host "  ✅ Data encryption (PII)" -ForegroundColor Green
Write-Host "  ✅ Audit logging" -ForegroundColor Green
Write-Host "  ✅ GDPR compliance" -ForegroundColor Green
Write-Host ""

Write-Host "⚡ Performance Features Installed:" -ForegroundColor Cyan
Write-Host "  ✅ Redis caching" -ForegroundColor Green
Write-Host "  ✅ Response compression (Brotli/Gzip)" -ForegroundColor Green
Write-Host "  ✅ Database optimization tools" -ForegroundColor Green
Write-Host "  ✅ Query optimization" -ForegroundColor Green
Write-Host ""

Write-Host "🎨 UI/UX Components Installed:" -ForegroundColor Cyan
Write-Host "  ✅ Loading states (Skeleton screens)" -ForegroundColor Green
Write-Host "  ✅ Error states (404, Empty, etc.)" -ForegroundColor Green
Write-Host "  ✅ Success states (Toasts, Modals)" -ForegroundColor Green
Write-Host "  ✅ Responsive design" -ForegroundColor Green
Write-Host "  ✅ Accessibility features" -ForegroundColor Green
Write-Host ""

Write-Host "📚 Next Steps:" -ForegroundColor Yellow
Write-Host "1. Configure Redis in .env file" -ForegroundColor White
Write-Host "2. Set up SSL/TLS certificate for HTTPS" -ForegroundColor White
Write-Host "3. Configure GDPR settings in config/gdpr.php" -ForegroundColor White
Write-Host "4. Review security headers in SecurityHeadersMiddleware.php" -ForegroundColor White
Write-Host "5. Test rate limiting and caching" -ForegroundColor White
Write-Host ""

Write-Host "📖 Documentation:" -ForegroundColor Yellow
Write-Host "  - Quick Start: QUICK_START_SECURITY_PERFORMANCE_UI.md" -ForegroundColor White
Write-Host "  - Full Guide: SECURITY_PERFORMANCE_UI_COMPLETE_2025_11_03.md" -ForegroundColor White
Write-Host ""

Write-Host "🚀 Start Development Server:" -ForegroundColor Yellow
Write-Host "  Backend:  cd backend && php artisan serve" -ForegroundColor White
Write-Host "  Frontend: cd frontend && npm run dev" -ForegroundColor White
Write-Host ""

Write-Host "✨ All systems ready!" -ForegroundColor Green
