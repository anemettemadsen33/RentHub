#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Run ALL tests - Backend + Frontend + E2E
.DESCRIPTION
    Comprehensive test suite covering:
    - Backend API tests (PHPUnit/Pest)
    - Frontend unit/component tests (Vitest)
    - E2E tests (Playwright)
    - Generate coverage reports
#>

param(
    [switch]$Coverage,
    [switch]$Parallel,
    [switch]$StopOnFailure,
    [ValidateSet("all", "backend", "frontend", "e2e")]
    [string]$Target = "all"
)

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  🧪 RUNNING COMPLETE TEST SUITE       ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

$script:FailedTests = @()
$script:PassedTests = @()
$script:StartTime = Get-Date

function Test-Backend {
    Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
    Write-Host "🔧 BACKEND TESTS (Laravel + PHPUnit)" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Blue
    
    Push-Location backend
    
    $testCmd = "php artisan test"
    
    if ($Parallel) { $testCmd += " --parallel" }
    if ($StopOnFailure) { $testCmd += " --stop-on-failure" }
    if ($Coverage) { $testCmd += " --coverage --min=70" }
    
    Write-Host "Running: $testCmd`n" -ForegroundColor Gray
    
    $result = Invoke-Expression $testCmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✅ Backend tests PASSED" -ForegroundColor Green
        $script:PassedTests += "Backend API Tests"
    } else {
        Write-Host "`n❌ Backend tests FAILED" -ForegroundColor Red
        $script:FailedTests += "Backend API Tests"
    }
    
    Pop-Location
    
    return $LASTEXITCODE -eq 0
}

function Test-Frontend {
    Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
    Write-Host "⚛️  FRONTEND TESTS (Vitest + React Testing Library)" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Blue
    
    Push-Location frontend
    
    $testCmd = "npm run test"
    
    if ($Coverage) { $testCmd += " -- --coverage" }
    
    Write-Host "Running: $testCmd`n" -ForegroundColor Gray
    
    $result = Invoke-Expression $testCmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✅ Frontend tests PASSED" -ForegroundColor Green
        $script:PassedTests += "Frontend Unit/Component Tests"
    } else {
        Write-Host "`n❌ Frontend tests FAILED" -ForegroundColor Red
        $script:FailedTests += "Frontend Unit/Component Tests"
    }
    
    Pop-Location
    
    return $LASTEXITCODE -eq 0
}

function Test-E2E {
    Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
    Write-Host "🎭 E2E TESTS (Playwright)" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Blue
    
    # Check if backend and frontend are running
    Write-Host "Checking if servers are running..." -ForegroundColor Gray
    
    $frontendRunning = $false
    $backendRunning = $false
    
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:3000" -TimeoutSec 2 -UseBasicParsing -ErrorAction SilentlyContinue
        $frontendRunning = $true
    } catch {}
    
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000/api/health" -TimeoutSec 2 -UseBasicParsing -ErrorAction SilentlyContinue
        $backendRunning = $true
    } catch {}
    
    if (-not $frontendRunning -or -not $backendRunning) {
        Write-Host "⚠️  WARNING: Servers not running!" -ForegroundColor Yellow
        Write-Host "   Frontend (localhost:3000): $(if($frontendRunning){'✅'}else{'❌'})" -ForegroundColor $(if($frontendRunning){'Green'}else{'Red'})
        Write-Host "   Backend (localhost:8000): $(if($backendRunning){'✅'}else{'❌'})" -ForegroundColor $(if($backendRunning){'Green'}else{'Red'})
        Write-Host "`nSkipping E2E tests. Start servers with:" -ForegroundColor Yellow
        Write-Host "   Terminal 1: cd backend && php artisan serve" -ForegroundColor Cyan
        Write-Host "   Terminal 2: cd frontend && npm run dev`n" -ForegroundColor Cyan
        return $false
    }
    
    Push-Location frontend
    
    Write-Host "Running E2E tests...`n" -ForegroundColor Gray
    
    $result = npm run e2e
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✅ E2E tests PASSED" -ForegroundColor Green
        $script:PassedTests += "E2E Tests"
    } else {
        Write-Host "`n❌ E2E tests FAILED" -ForegroundColor Red
        $script:FailedTests += "E2E Tests"
    }
    
    Pop-Location
    
    return $LASTEXITCODE -eq 0
}

# Run tests based on target
$allPassed = $true

if ($Target -eq "all" -or $Target -eq "backend") {
    $allPassed = (Test-Backend) -and $allPassed
}

if ($Target -eq "all" -or $Target -eq "frontend") {
    $allPassed = (Test-Frontend) -and $allPassed
}

if ($Target -eq "all" -or $Target -eq "e2e") {
    $allPassed = (Test-E2E) -and $allPassed
}

# Final Summary
$EndTime = Get-Date
$Duration = $EndTime - $script:StartTime

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  📊 TEST SUMMARY                      ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "Duration: $($Duration.TotalSeconds) seconds`n" -ForegroundColor Gray

if ($script:PassedTests.Count -gt 0) {
    Write-Host "✅ PASSED ($($script:PassedTests.Count)):" -ForegroundColor Green
    foreach ($test in $script:PassedTests) {
        Write-Host "   • $test" -ForegroundColor Green
    }
    Write-Host ""
}

if ($script:FailedTests.Count -gt 0) {
    Write-Host "❌ FAILED ($($script:FailedTests.Count)):" -ForegroundColor Red
    foreach ($test in $script:FailedTests) {
        Write-Host "   • $test" -ForegroundColor Red
    }
    Write-Host ""
}

if ($allPassed) {
    Write-Host "╔════════════════════════════════════════╗" -ForegroundColor Green
    Write-Host "║  ✅ ALL TESTS PASSED!                 ║" -ForegroundColor Green
    Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Green
    
    if ($Coverage) {
        Write-Host "📊 Coverage reports generated:" -ForegroundColor Yellow
        Write-Host "   Backend:  backend/coverage/index.html" -ForegroundColor Cyan
        Write-Host "   Frontend: frontend/coverage/index.html`n" -ForegroundColor Cyan
    }
} else {
    Write-Host "╔════════════════════════════════════════╗" -ForegroundColor Red
    Write-Host "║  ❌ SOME TESTS FAILED                 ║" -ForegroundColor Red
    Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Red
    exit 1
}
