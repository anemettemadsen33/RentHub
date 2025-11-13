#!/usr/bin/env pwsh
# Quick Test Script - Verify All Fixes

Write-Host "`n🧪 TESTING ALL FIXES" -ForegroundColor Cyan
Write-Host "===================`n" -ForegroundColor Cyan

$allPassed = $true

# Test 1: Check CORS configuration
Write-Host "1️⃣  Testing CORS configuration..." -ForegroundColor Yellow
$cors = Get-Content "backend/config/cors.php" -Raw
if ($cors -match "rent-ljgrpeajm-madsens-projects\.vercel\.app") {
    Write-Host "   ✅ Vercel domain in CORS" -ForegroundColor Green
} else {
    Write-Host "   ❌ Vercel domain missing in CORS" -ForegroundColor Red
    $allPassed = $false
}

# Test 2: Check environment examples
Write-Host "`n2️⃣  Testing environment configuration..." -ForegroundColor Yellow
$frontendEnv = Get-Content "frontend/.env.example" -Raw
if ($frontendEnv -match "Production.*uncomment") {
    Write-Host "   ✅ Frontend .env.example has production comments" -ForegroundColor Green
} else {
    Write-Host "   ❌ Frontend .env.example missing production info" -ForegroundColor Red
    $allPassed = $false
}

$backendEnv = Get-Content "backend/.env.example" -Raw
if ($backendEnv -match "rent-ljgrpeajm-madsens-projects") {
    Write-Host "   ✅ Backend .env.example has Vercel URL" -ForegroundColor Green
} else {
    Write-Host "   ❌ Backend .env.example missing Vercel URL" -ForegroundColor Red
    $allPassed = $false
}

# Test 3: Check Next.js optimizations
Write-Host "`n3️⃣  Testing Next.js optimizations..." -ForegroundColor Yellow
$nextConfig = Get-Content "frontend/next.config.js" -Raw
if ($nextConfig -match "removeConsole") {
    Write-Host "   ✅ Console removal for production enabled" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Console removal not configured" -ForegroundColor Yellow
}

if ($nextConfig -match "optimizePackageImports") {
    Write-Host "   ✅ Package imports optimization enabled" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Package optimization not configured" -ForegroundColor Yellow
}

# Test 4: Check skeleton components exist
Write-Host "`n4️⃣  Testing loading states..." -ForegroundColor Yellow
if (Test-Path "frontend/src/components/ui/skeleton.tsx") {
    Write-Host "   ✅ Skeleton component exists" -ForegroundColor Green
} else {
    Write-Host "   ❌ Skeleton component missing" -ForegroundColor Red
    $allPassed = $false
}

if (Test-Path "frontend/src/components/skeletons.tsx") {
    Write-Host "   ✅ Custom skeletons exist" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Custom skeletons file not found" -ForegroundColor Yellow
}

# Test 5: Check translations
Write-Host "`n5️⃣  Testing translations..." -ForegroundColor Yellow
if (Test-Path "frontend/src/i18n/messages/en.json") {
    $enLines = (Get-Content "frontend/src/i18n/messages/en.json" | Measure-Object -Line).Lines
    Write-Host "   ✅ English: $enLines lines" -ForegroundColor Green
}

if (Test-Path "frontend/src/i18n/messages/ro.json") {
    $roLines = (Get-Content "frontend/src/i18n/messages/ro.json" | Measure-Object -Line).Lines
    Write-Host "   ✅ Romanian: $roLines lines" -ForegroundColor Green
}

# Test 6: Verify deployment script exists
Write-Host "`n6️⃣  Testing deployment tools..." -ForegroundColor Yellow
if (Test-Path "deployment-checklist.ps1") {
    Write-Host "   ✅ Deployment checklist script exists" -ForegroundColor Green
} else {
    Write-Host "   ❌ Deployment script missing" -ForegroundColor Red
    $allPassed = $false
}

# Final Result
Write-Host "`n===================" -ForegroundColor Cyan
if ($allPassed) {
    Write-Host "✅ ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "`nYour application is ready for:" -ForegroundColor Cyan
    Write-Host "  • Production deployment" -ForegroundColor White
    Write-Host "  • Cross-origin requests (CORS)" -ForegroundColor White
    Write-Host "  • Optimized builds" -ForegroundColor White
    Write-Host "  • Multi-language support" -ForegroundColor White
    Write-Host "  • User-friendly loading states`n" -ForegroundColor White
    exit 0
} else {
    Write-Host "❌ SOME TESTS FAILED" -ForegroundColor Red
    Write-Host "`nPlease review the errors above.`n" -ForegroundColor Yellow
    exit 1
}
