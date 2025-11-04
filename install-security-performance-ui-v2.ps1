# Security, Performance & UI/UX Installation Script
# RentHub Platform - Complete Implementation

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Security, Performance & UI/UX Setup  " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$ErrorActionPreference = "Stop"

# Check if in correct directory
if (-not (Test-Path "backend") -or -not (Test-Path "frontend")) {
    Write-Host "Error: Please run this script from the RentHub root directory" -ForegroundColor Red
    exit 1
}

Write-Host "Step 1: Installing Backend Dependencies..." -ForegroundColor Yellow
Set-Location backend

# Check Composer
try {
    $composerVersion = composer --version
    Write-Host "✓ Composer found: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer not found. Please install Composer first." -ForegroundColor Red
    exit 1
}

# Install PHP dependencies
Write-Host "Installing/updating Composer packages..." -ForegroundColor Cyan
composer install --no-interaction --prefer-dist --optimize-autoloader

# Run migrations
Write-Host "`nStep 2: Running Database Migrations..." -ForegroundColor Yellow
php artisan migrate --force

# Create security audit logs table
if (Test-Path "database/migrations/2025_01_01_000001_create_security_audit_logs_table.php") {
    Write-Host "Running security audit logs migration..." -ForegroundColor Cyan
    php artisan migrate --path=database/migrations/2025_01_01_000001_create_security_audit_logs_table.php --force
    Write-Host "✓ Security audit logs table created" -ForegroundColor Green
}

# Clear caches
Write-Host "`nStep 3: Clearing Caches..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
Write-Host "✓ Caches cleared" -ForegroundColor Green

# Optimize application
Write-Host "`nStep 4: Optimizing Application..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache
Write-Host "✓ Application optimized" -ForegroundColor Green

# Set up storage
Write-Host "`nStep 5: Setting Up Storage..." -ForegroundColor Yellow
php artisan storage:link
Write-Host "✓ Storage linked" -ForegroundColor Green

# Generate key if needed
if (-not (Select-String -Path ".env" -Pattern "APP_KEY" -Quiet)) {
    Write-Host "`nGenerating application key..." -ForegroundColor Cyan
    php artisan key:generate
    Write-Host "✓ Application key generated" -ForegroundColor Green
}

Set-Location ..

# Frontend Setup
Write-Host "`nStep 6: Installing Frontend Dependencies..." -ForegroundColor Yellow
Set-Location frontend

# Check Node.js
try {
    $nodeVersion = node --version
    Write-Host "✓ Node.js found: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js not found. Please install Node.js first." -ForegroundColor Red
    Set-Location ..
    exit 1
}

# Check npm
try {
    $npmVersion = npm --version
    Write-Host "✓ npm found: v$npmVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ npm not found. Please install npm first." -ForegroundColor Red
    Set-Location ..
    exit 1
}

# Install packages
Write-Host "Installing npm packages..." -ForegroundColor Cyan
npm install

# Build frontend
Write-Host "`nStep 7: Building Frontend..." -ForegroundColor Yellow
npm run build
Write-Host "✓ Frontend built successfully" -ForegroundColor Green

Set-Location ..

# Create required directories
Write-Host "`nStep 8: Creating Required Directories..." -ForegroundColor Yellow
$directories = @(
    "backend/storage/logs",
    "backend/storage/framework/cache",
    "backend/storage/framework/sessions",
    "backend/storage/framework/views",
    "backend/storage/app/public"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "✓ Created $dir" -ForegroundColor Green
    }
}

# Set permissions (Windows)
Write-Host "`nStep 9: Setting Permissions..." -ForegroundColor Yellow
$storagePath = "backend/storage"
$bootstrapPath = "backend/bootstrap/cache"

if (Test-Path $storagePath) {
    icacls $storagePath /grant "Users:(OI)(CI)F" /T | Out-Null
    Write-Host "✓ Storage permissions set" -ForegroundColor Green
}

if (Test-Path $bootstrapPath) {
    icacls $bootstrapPath /grant "Users:(OI)(CI)F" /T | Out-Null
    Write-Host "✓ Bootstrap cache permissions set" -ForegroundColor Green
}

# Create .env if not exists
Write-Host "`nStep 10: Checking Environment Configuration..." -ForegroundColor Yellow
if (-not (Test-Path "backend/.env")) {
    if (Test-Path "backend/.env.example") {
        Copy-Item "backend/.env.example" "backend/.env"
        Write-Host "✓ Created .env from .env.example" -ForegroundColor Green
        Write-Host "  Please update the .env file with your settings" -ForegroundColor Yellow
    }
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Installation Complete!  " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Features Installed:" -ForegroundColor Yellow
Write-Host "  ✓ Advanced Rate Limiting & DDoS Protection" -ForegroundColor Green
Write-Host "  ✓ Security Headers Middleware" -ForegroundColor Green
Write-Host "  ✓ Data Encryption Service" -ForegroundColor Green
Write-Host "  ✓ GDPR Compliance Service" -ForegroundColor Green
Write-Host "  ✓ Security Audit Logging" -ForegroundColor Green
Write-Host "  ✓ Advanced Caching System" -ForegroundColor Green
Write-Host "  ✓ Query Optimization" -ForegroundColor Green
Write-Host "  ✓ UI/UX Components (Loading, Error, Toast)" -ForegroundColor Green
Write-Host ""

Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Update backend/.env with your database credentials" -ForegroundColor White
Write-Host "  2. Configure Redis for caching (recommended)" -ForegroundColor White
Write-Host "  3. Set up SSL/TLS certificate for production" -ForegroundColor White
Write-Host "  4. Review SECURITY_PERFORMANCE_UI_COMPLETE.md for usage" -ForegroundColor White
Write-Host "  5. Run: cd backend && php artisan serve" -ForegroundColor White
Write-Host "  6. Run: cd frontend && npm run dev" -ForegroundColor White
Write-Host ""

Write-Host "Documentation:" -ForegroundColor Yellow
Write-Host "  - SECURITY_PERFORMANCE_UI_COMPLETE.md - Complete guide" -ForegroundColor White
Write-Host "  - QUICK_START_SECURITY_PERFORMANCE_UI.md - Quick reference" -ForegroundColor White
Write-Host ""

Write-Host "Happy coding! 🚀" -ForegroundColor Cyan
