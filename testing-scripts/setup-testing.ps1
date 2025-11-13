#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Setup complete testing environment for RentHub
.DESCRIPTION
    Installs all testing dependencies and prepares test databases
#>

param(
    [switch]$SkipBackend,
    [switch]$SkipFrontend
)

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  🧪 SETUP TESTING ENVIRONMENT         ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

$ErrorActionPreference = "Continue"

# Backend Testing Setup
if (-not $SkipBackend) {
    Write-Host "📦 BACKEND - Installing testing dependencies..." -ForegroundColor Yellow
    
    Push-Location backend
    
    # Install Pest (modern PHP testing framework)
    Write-Host "   → Installing Pest..." -ForegroundColor Gray
    composer require pestphp/pest --dev --with-all-dependencies 2>&1 | Out-Null
    composer require pestphp/pest-plugin-laravel --dev 2>&1 | Out-Null
    
    # Install other testing tools
    Write-Host "   → Installing Faker, Mockery..." -ForegroundColor Gray
    composer require fakerphp/faker --dev 2>&1 | Out-Null
    composer require mockery/mockery --dev 2>&1 | Out-Null
    
    # Setup test database
    Write-Host "   → Setting up test database..." -ForegroundColor Gray
    if (-not (Test-Path ".env.testing")) {
        Copy-Item ".env.example" ".env.testing"
        (Get-Content ".env.testing") -replace "DB_DATABASE=.*", "DB_DATABASE=:memory:" | Set-Content ".env.testing"
        (Get-Content ".env.testing") -replace "DB_CONNECTION=.*", "DB_CONNECTION=sqlite" | Set-Content ".env.testing"
    }
    
    # Generate application key for testing
    php artisan key:generate --env=testing 2>&1 | Out-Null
    
    Pop-Location
    
    Write-Host "   ✅ Backend testing setup complete`n" -ForegroundColor Green
}

# Frontend Testing Setup
if (-not $SkipFrontend) {
    Write-Host "📦 FRONTEND - Installing testing dependencies..." -ForegroundColor Yellow
    
    Push-Location frontend
    
    # Install testing libraries
    Write-Host "   → Installing Vitest, Testing Library..." -ForegroundColor Gray
    npm install -D @testing-library/react @testing-library/jest-dom @testing-library/user-event 2>&1 | Out-Null
    npm install -D @vitejs/plugin-react vitest jsdom 2>&1 | Out-Null
    
    # Install Playwright for E2E
    Write-Host "   → Installing Playwright..." -ForegroundColor Gray
    npm install -D @playwright/test 2>&1 | Out-Null
    
    # Install Playwright browsers
    Write-Host "   → Installing Playwright browsers (this may take a few minutes)..." -ForegroundColor Gray
    npx playwright install chromium firefox 2>&1 | Out-Null
    
    # Install MSW for API mocking
    Write-Host "   → Installing MSW (API mocking)..." -ForegroundColor Gray
    npm install -D msw 2>&1 | Out-Null
    
    Pop-Location
    
    Write-Host "   ✅ Frontend testing setup complete`n" -ForegroundColor Green
}

# Create test directories
Write-Host "📁 Creating test directory structure..." -ForegroundColor Yellow

$testDirs = @(
    "backend/tests/Feature/Api",
    "backend/tests/Unit/Models",
    "backend/tests/Unit/Services",
    "frontend/tests/unit/components",
    "frontend/tests/unit/hooks",
    "frontend/tests/unit/utils",
    "frontend/tests/integration",
    "frontend/tests/e2e",
    "test-results"
)

foreach ($dir in $testDirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "   ✅ Created: $dir" -ForegroundColor Gray
    }
}

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  ✅ TESTING SETUP COMPLETE            ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "🎯 NEXT STEPS:`n" -ForegroundColor Yellow
Write-Host "1. Run backend tests:" -ForegroundColor White
Write-Host "   cd backend && php artisan test`n" -ForegroundColor Cyan

Write-Host "2. Run frontend tests:" -ForegroundColor White
Write-Host "   cd frontend && npm run test`n" -ForegroundColor Cyan

Write-Host "3. Run E2E tests:" -ForegroundColor White
Write-Host "   cd frontend && npm run e2e`n" -ForegroundColor Cyan

Write-Host "4. Run all tests:" -ForegroundColor White
Write-Host "   .\testing-scripts\test-all.ps1`n" -ForegroundColor Cyan

Write-Host "📚 Documentation: COMPLETE_TESTING_STRATEGY.md`n" -ForegroundColor DarkGray
