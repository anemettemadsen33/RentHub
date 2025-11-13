#!/usr/bin/env pwsh
# RentHub Production Deployment Checklist
# Run this script before deploying to production

Write-Host "🚀 RentHub Production Deployment Checklist" -ForegroundColor Cyan
Write-Host "==========================================`n" -ForegroundColor Cyan

$issues = @()
$warnings = @()
$success = @()

# Check 1: Frontend Environment Variables
Write-Host "📋 Checking Frontend Environment..." -ForegroundColor Yellow
if (Test-Path "frontend/.env.production") {
    $success += "✅ Frontend .env.production exists"
    
    $env = Get-Content "frontend/.env.production" -Raw
    
    if ($env -match "NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com") {
        $success += "✅ Production API URL configured"
    } else {
        $issues += "❌ NEXT_PUBLIC_API_URL not set to production"
    }
    
    if ($env -match "NEXT_PUBLIC_SENTRY_DSN=") {
        $warnings += "⚠️  Sentry DSN might be missing"
    }
} else {
    $warnings += "⚠️  No .env.production file - will use defaults"
}

# Check 2: Backend Environment
Write-Host "📋 Checking Backend Environment..." -ForegroundColor Yellow
if (Test-Path "backend/.env") {
    $success += "✅ Backend .env exists"
    
    $env = Get-Content "backend/.env" -Raw
    
    if ($env -match "APP_ENV=production") {
        $success += "✅ APP_ENV set to production"
    } else {
        $warnings += "⚠️  APP_ENV not set to production"
    }
    
    if ($env -match "APP_DEBUG=false") {
        $success += "✅ APP_DEBUG disabled"
    } else {
        $issues += "❌ APP_DEBUG should be false in production"
    }
    
    if ($env -match "SANCTUM_STATEFUL_DOMAINS=.*vercel\.app") {
        $success += "✅ SANCTUM_STATEFUL_DOMAINS includes Vercel"
    } else {
        $issues += "❌ SANCTUM_STATEFUL_DOMAINS missing Vercel domain"
    }
} else {
    $issues += "❌ Backend .env file missing!"
}

# Check 3: CORS Configuration
Write-Host "📋 Checking CORS Configuration..." -ForegroundColor Yellow
if (Test-Path "backend/config/cors.php") {
    $cors = Get-Content "backend/config/cors.php" -Raw
    
    if ($cors -match "rent-ljgrpeajm-madsens-projects\.vercel\.app") {
        $success += "✅ CORS includes current Vercel domain"
    } else {
        $warnings += "⚠️  CORS might not include current Vercel domain"
    }
    
    if ($cors -match "supports_credentials.*true") {
        $success += "✅ CORS credentials enabled"
    }
}

# Check 4: Vercel Configuration
Write-Host "📋 Checking Vercel Configuration..." -ForegroundColor Yellow
if (Test-Path "frontend/vercel.json") {
    $vercel = Get-Content "frontend/vercel.json" -Raw | ConvertFrom-Json
    
    if ($vercel.rewrites) {
        $success += "✅ Vercel rewrites configured"
    }
    
    if ($vercel.headers) {
        $success += "✅ Security headers configured"
    }
}

# Check 5: Build Test
Write-Host "📋 Testing Frontend Build..." -ForegroundColor Yellow
Push-Location frontend
try {
    $buildOutput = npm run build 2>&1
    if ($LASTEXITCODE -eq 0) {
        $success += "✅ Frontend builds successfully"
    } else {
        $issues += "❌ Frontend build failed"
    }
} catch {
    $warnings += "⚠️  Could not test frontend build"
} finally {
    Pop-Location
}

# Check 6: Database Migrations
Write-Host "📋 Checking Database..." -ForegroundColor Yellow
Push-Location backend
try {
    $migrationStatus = php artisan migrate:status 2>&1
    if ($LASTEXITCODE -eq 0) {
        $success += "✅ Database migrations OK"
    } else {
        $warnings += "⚠️  Check database migration status"
    }
} catch {
    $warnings += "⚠️  Could not check database migrations"
} finally {
    Pop-Location
}

# Print Results
Write-Host "`n==========================================`n" -ForegroundColor Cyan
Write-Host "📊 DEPLOYMENT CHECKLIST RESULTS`n" -ForegroundColor Cyan

if ($success.Count -gt 0) {
    Write-Host "✅ SUCCESS ($($success.Count)):" -ForegroundColor Green
    $success | ForEach-Object { Write-Host "   $_" -ForegroundColor Green }
    Write-Host ""
}

if ($warnings.Count -gt 0) {
    Write-Host "⚠️  WARNINGS ($($warnings.Count)):" -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host "   $_" -ForegroundColor Yellow }
    Write-Host ""
}

if ($issues.Count -gt 0) {
    Write-Host "❌ CRITICAL ISSUES ($($issues.Count)):" -ForegroundColor Red
    $issues | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
    Write-Host ""
    Write-Host "❌ DEPLOYMENT BLOCKED - Fix critical issues first!" -ForegroundColor Red
    exit 1
} else {
    Write-Host "✅ ALL CHECKS PASSED - Ready for deployment!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Commit and push changes" -ForegroundColor White
    Write-Host "2. Deploy frontend: git push (auto-deploys on Vercel)" -ForegroundColor White
    Write-Host "3. Deploy backend: ssh to Forge and run deployment" -ForegroundColor White
    Write-Host "4. Test production URLs" -ForegroundColor White
    Write-Host "5. Monitor logs for errors" -ForegroundColor White
    exit 0
}
