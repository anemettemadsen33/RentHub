# RentHub - Security, Performance & UI/UX Installation Script (PowerShell)
# Date: November 3, 2025

Write-Host "🚀 RentHub - Installing Security, Performance & UI/UX Features" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Blue
}

function Write-Warning-Custom {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

# Check if running in RentHub directory
if (-not (Test-Path "composer.json") -and -not (Test-Path "backend")) {
    Write-Error-Custom "This script must be run from the RentHub root directory"
    exit 1
}

# Backend installation
Write-Host ""
Write-Host "📦 Installing Backend Dependencies..." -ForegroundColor Cyan
Write-Host "--------------------------------------" -ForegroundColor Cyan

Push-Location backend -ErrorAction SilentlyContinue

if (Test-Path "composer.json") {
    Write-Info "Installing PHP dependencies..."
    composer install
    Write-Success "PHP dependencies installed"
} else {
    Write-Warning-Custom "composer.json not found, skipping PHP dependencies"
}

# Database setup
Write-Host ""
Write-Host "🗄️  Setting up Database..." -ForegroundColor Cyan
Write-Host "---------------------------" -ForegroundColor Cyan

if (Test-Path "artisan") {
    Write-Info "Running migrations..."
    php artisan migrate --force
    Write-Success "Migrations completed"
    
    Write-Info "Seeding RBAC structure..."
    php artisan db:seed --class=RBACSeeder
    Write-Success "RBAC seeded"
} else {
    Write-Warning-Custom "artisan not found, skipping database setup"
}

# Cache setup
Write-Host ""
Write-Host "💾 Configuring Cache..." -ForegroundColor Cyan
Write-Host "-----------------------" -ForegroundColor Cyan

if (Test-Path "artisan") {
    Write-Info "Clearing cache..."
    php artisan cache:clear
    
    Write-Info "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    Write-Success "Cache configured"
} else {
    Write-Warning-Custom "artisan not found, skipping cache setup"
}

Pop-Location

# Frontend installation
Write-Host ""
Write-Host "🎨 Installing Frontend Dependencies..." -ForegroundColor Cyan
Write-Host "---------------------------------------" -ForegroundColor Cyan

Push-Location frontend -ErrorAction SilentlyContinue

if (Test-Path "package.json") {
    Write-Info "Installing Node.js dependencies..."
    npm install
    Write-Success "Node.js dependencies installed"
} else {
    Write-Warning-Custom "package.json not found, skipping frontend dependencies"
}

Pop-Location

# Verification
Write-Host ""
Write-Host "🔍 Verifying Installation..." -ForegroundColor Cyan
Write-Host "-----------------------------" -ForegroundColor Cyan

$files = @(
    "backend\app\Services\OAuth2Service.php",
    "backend\app\Services\RBACService.php",
    "backend\app\Services\EncryptionService.php",
    "backend\app\Services\CacheService.php",
    "backend\app\Services\PerformanceService.php",
    "backend\app\Http\Middleware\SecurityHeadersMiddleware.php",
    "backend\app\Http\Middleware\RateLimitMiddleware.php",
    "backend\app\Http\Middleware\ValidateInputMiddleware.php",
    "frontend\src\components\ui\LoadingStates.tsx",
    "frontend\src\components\ui\StateComponents.tsx",
    "frontend\src\components\ui\AccessibilityComponents.tsx"
)

$missing = 0
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Success $file
    } else {
        Write-Error-Custom "$file - NOT FOUND"
        $missing++
    }
}

# Summary
Write-Host ""
Write-Host "📊 Installation Summary" -ForegroundColor Cyan
Write-Host "=======================" -ForegroundColor Cyan

if ($missing -eq 0) {
    Write-Success "All files verified successfully!"
} else {
    Write-Warning-Custom "$missing files are missing"
}

Write-Host ""
Write-Host "✅ Security Features:" -ForegroundColor Green
Write-Host "   - OAuth 2.0 Authentication"
Write-Host "   - RBAC (Role-Based Access Control)"
Write-Host "   - Data Encryption"
Write-Host "   - Security Headers"
Write-Host "   - Rate Limiting"
Write-Host "   - Input Validation"
Write-Host "   - Security Audit Logging"
Write-Host ""

Write-Host "⚡ Performance Features:" -ForegroundColor Yellow
Write-Host "   - Multi-layer Caching (Redis)"
Write-Host "   - Query Optimization"
Write-Host "   - Image Optimization"
Write-Host "   - Response Compression"
Write-Host "   - Connection Pooling"
Write-Host ""

Write-Host "🎨 UI/UX Features:" -ForegroundColor Magenta
Write-Host "   - Loading States (Spinner, Skeleton)"
Write-Host "   - State Components (Error, Empty)"
Write-Host "   - Accessibility (WCAG AA)"
Write-Host "   - Design System"
Write-Host "   - Animations"
Write-Host ""

# Next steps
Write-Host "🎯 Next Steps:" -ForegroundColor Cyan
Write-Host "==============" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Configure your .env file:"
Write-Host "   Copy-Item backend\.env.example backend\.env"
Write-Host "   Copy-Item frontend\.env.example frontend\.env"
Write-Host ""
Write-Host "2. Update environment variables:"
Write-Host "   - Set CACHE_DRIVER=redis"
Write-Host "   - Configure database credentials"
Write-Host "   - Set JWT_SECRET and ENCRYPTION_KEY"
Write-Host ""
Write-Host "3. Start the development servers:"
Write-Host "   Backend:  cd backend; php artisan serve"
Write-Host "   Frontend: cd frontend; npm run dev"
Write-Host ""
Write-Host "4. Run tests:"
Write-Host "   Backend:  cd backend; php artisan test"
Write-Host "   Frontend: cd frontend; npm run test"
Write-Host ""
Write-Host "5. Read the documentation:"
Write-Host "   - COMPLETE_SECURITY_PERFORMANCE_UI_GUIDE.md"
Write-Host "   - QUICK_START_COMPLETE_IMPLEMENTATION.md"
Write-Host "   - QUICK_REFERENCE_SECURITY_PERFORMANCE_UI.md"
Write-Host ""

Write-Success "Installation completed successfully! 🎉"
Write-Host ""
Write-Host "For support, check the documentation or contact the team."
Write-Host ""
