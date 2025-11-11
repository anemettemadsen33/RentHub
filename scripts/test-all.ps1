# ===================================
# RentHub - Complete Testing Script (PowerShell)
# ===================================

Write-Host "🧪 RentHub - Running Complete Test Suite" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$script:Failures = 0

# ===================================
# Backend Tests
# ===================================

Write-Host ""
Write-Host "📦 Testing Backend (Laravel)" -ForegroundColor Yellow
Write-Host "-----------------------------------" -ForegroundColor Yellow

Set-Location backend

Write-Host "→ Installing dependencies..." -ForegroundColor Gray
composer install --quiet

Write-Host "→ Running PHPUnit tests..." -ForegroundColor Gray
if (php artisan test --parallel) {
    Write-Host "✓ Backend tests passed" -ForegroundColor Green
} else {
    Write-Host "✗ Backend tests failed" -ForegroundColor Red
    $script:Failures++
}

Write-Host "→ Running PHPStan static analysis..." -ForegroundColor Gray
if (.\vendor\bin\phpstan analyse --no-progress) {
    Write-Host "✓ PHPStan analysis passed" -ForegroundColor Green
} else {
    Write-Host "✗ PHPStan analysis failed" -ForegroundColor Red
    $script:Failures++
}

Write-Host "→ Checking code style..." -ForegroundColor Gray
if (.\vendor\bin\pint --test) {
    Write-Host "✓ Code style check passed" -ForegroundColor Green
} else {
    Write-Host "✗ Code style check failed" -ForegroundColor Red
    $script:Failures++
}

Set-Location ..

# ===================================
# Frontend Tests
# ===================================

Write-Host ""
Write-Host "🎨 Testing Frontend (Next.js)" -ForegroundColor Yellow
Write-Host "-----------------------------------" -ForegroundColor Yellow

Set-Location frontend

Write-Host "→ Installing dependencies..." -ForegroundColor Gray
npm install --silent

Write-Host "→ Running Vitest unit tests..." -ForegroundColor Gray
if (npm test -- --run) {
    Write-Host "✓ Frontend unit tests passed" -ForegroundColor Green
} else {
    Write-Host "✗ Frontend unit tests failed" -ForegroundColor Red
    $script:Failures++
}

Write-Host "→ Running TypeScript type check..." -ForegroundColor Gray
if (npm run type-check) {
    Write-Host "✓ Type checking passed" -ForegroundColor Green
} else {
    Write-Host "✗ Type checking failed" -ForegroundColor Red
    $script:Failures++
}

Write-Host "→ Running ESLint..." -ForegroundColor Gray
if (npm run lint) {
    Write-Host "✓ Linting passed" -ForegroundColor Green
} else {
    Write-Host "✗ Linting failed" -ForegroundColor Red
    $script:Failures++
}

Write-Host "→ Testing production build..." -ForegroundColor Gray
if (npm run build) {
    Write-Host "✓ Production build successful" -ForegroundColor Green
} else {
    Write-Host "✗ Production build failed" -ForegroundColor Red
    $script:Failures++
}

Set-Location ..

# ===================================
# Summary
# ===================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 Test Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($script:Failures -eq 0) {
    Write-Host "✅ All tests passed! Ready for deployment." -ForegroundColor Green
    exit 0
} else {
    Write-Host "❌ $($script:Failures) test suite(s) failed." -ForegroundColor Red
    Write-Host "Please fix the issues before deploying." -ForegroundColor Yellow
    exit 1
}
