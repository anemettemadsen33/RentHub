#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Quick smoke test - Test critical functionality
.DESCRIPTION
    Fast test of most important features
#>

Write-Host "`n🔥 SMOKE TEST - Critical Functionality`n" -ForegroundColor Yellow

$tests = @()

# Test 1: Backend is accessible
Write-Host "1. Testing Backend API accessibility..." -ForegroundColor Gray
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/health" -TimeoutSec 5 -UseBasicParsing -ErrorAction Stop
    Write-Host "   ✅ Backend API responding" -ForegroundColor Green
    $tests += @{Name="Backend API"; Passed=$true}
} catch {
    Write-Host "   ❌ Backend API not accessible" -ForegroundColor Red
    $tests += @{Name="Backend API"; Passed=$false}
}

# Test 2: Frontend is accessible
Write-Host "2. Testing Frontend accessibility..." -ForegroundColor Gray
try {
    $response = Invoke-WebRequest -Uri "http://localhost:3000" -TimeoutSec 5 -UseBasicParsing -ErrorAction Stop
    Write-Host "   ✅ Frontend responding" -ForegroundColor Green
    $tests += @{Name="Frontend"; Passed=$true}
} catch {
    Write-Host "   ❌ Frontend not accessible" -ForegroundColor Red
    $tests += @{Name="Frontend"; Passed=$false}
}

# Test 3: Database connection
Write-Host "3. Testing Database connection..." -ForegroundColor Gray
Push-Location backend
$dbTest = php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>&1
Pop-Location
if ($dbTest -match "OK") {
    Write-Host "   ✅ Database connected" -ForegroundColor Green
    $tests += @{Name="Database"; Passed=$true}
} else {
    Write-Host "   ❌ Database connection failed" -ForegroundColor Red
    $tests += @{Name="Database"; Passed=$false}
}

# Test 4: API endpoints responding
Write-Host "4. Testing API endpoints..." -ForegroundColor Gray
$endpoints = @(
    "/api/v1/properties",
    "/api/v1/categories",
    "/api/v1/amenities"
)
$allPassed = $true
foreach ($endpoint in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000$endpoint" -TimeoutSec 3 -UseBasicParsing -ErrorAction Stop
        Write-Host "   ✅ $endpoint" -ForegroundColor Green
    } catch {
        Write-Host "   ❌ $endpoint" -ForegroundColor Red
        $allPassed = $false
    }
}
$tests += @{Name="API Endpoints"; Passed=$allPassed}

# Test 5: Frontend build
Write-Host "5. Testing Frontend build..." -ForegroundColor Gray
Push-Location frontend
$buildTest = npm run build 2>&1
Pop-Location
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Frontend builds successfully" -ForegroundColor Green
    $tests += @{Name="Frontend Build"; Passed=$true}
} else {
    Write-Host "   ❌ Frontend build failed" -ForegroundColor Red
    $tests += @{Name="Frontend Build"; Passed=$false}
}

# Summary
Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "SMOKE TEST SUMMARY" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Cyan

$passed = ($tests | Where-Object {$_.Passed -eq $true}).Count
$total = $tests.Count

Write-Host "Passed: $passed / $total`n" -ForegroundColor $(if($passed -eq $total){'Green'}else{'Yellow'})

if ($passed -eq $total) {
    Write-Host "✅ ALL SMOKE TESTS PASSED!`n" -ForegroundColor Green
    exit 0
} else {
    Write-Host "⚠️  SOME SMOKE TESTS FAILED`n" -ForegroundColor Yellow
    exit 1
}
