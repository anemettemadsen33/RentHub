#!/usr/bin/env pwsh
# RentHub - Complete Deployment Script
# This script deploys both frontend (Vercel) and backend (Forge)

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet('all', 'frontend', 'backend', 'check')]
    [string]$Target = 'check',
    
    [Parameter(Mandatory=$false)]
    [string]$Message = "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
)

$ErrorActionPreference = "Stop"

Write-Host "`n🚀 RentHub Deployment Tool" -ForegroundColor Cyan
Write-Host "========================`n" -ForegroundColor Cyan

# Function to check if we're in git repository
function Test-GitRepository {
    try {
        git rev-parse --git-dir 2>&1 | Out-Null
        return $true
    } catch {
        return $false
    }
}

# Function to check git status
function Get-GitStatus {
    $status = git status --porcelain
    return $status
}

# Function to deploy to GitHub
function Deploy-ToGitHub {
    param([string]$CommitMessage)
    
    Write-Host "📦 Deploying to GitHub..." -ForegroundColor Yellow
    
    if (-not (Test-GitRepository)) {
        Write-Host "❌ Not a git repository!" -ForegroundColor Red
        return $false
    }
    
    $changes = Get-GitStatus
    if (-not $changes) {
        Write-Host "ℹ️  No changes to commit" -ForegroundColor Blue
        return $true
    }
    
    try {
        Write-Host "   • Adding files..." -ForegroundColor White
        git add .
        
        Write-Host "   • Committing: $CommitMessage" -ForegroundColor White
        git commit -m $CommitMessage
        
        Write-Host "   • Pushing to origin..." -ForegroundColor White
        git push origin master
        
        Write-Host "   ✅ Pushed to GitHub successfully" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "   ❌ Git push failed: $_" -ForegroundColor Red
        return $false
    }
}

# Function to check Vercel CLI
function Test-VercelCLI {
    try {
        vercel --version | Out-Null
        return $true
    } catch {
        return $false
    }
}

# Function to deploy frontend to Vercel
function Deploy-Frontend {
    Write-Host "`n🌐 Deploying Frontend to Vercel..." -ForegroundColor Yellow
    
    if (-not (Test-VercelCLI)) {
        Write-Host "   ⚠️  Vercel CLI not installed" -ForegroundColor Yellow
        Write-Host "   Install with: npm i -g vercel" -ForegroundColor White
        Write-Host "   ℹ️  Skipping Vercel deployment (will auto-deploy via GitHub)" -ForegroundColor Blue
        return $true
    }
    
    Push-Location frontend
    try {
        Write-Host "   • Running Vercel deploy..." -ForegroundColor White
        
        # Production deployment
        vercel --prod --yes
        
        Write-Host "   ✅ Frontend deployed to Vercel" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "   ❌ Vercel deployment failed: $_" -ForegroundColor Red
        return $false
    } finally {
        Pop-Location
    }
}

# Function to create Forge deployment script
function Deploy-Backend {
    Write-Host "`n🔧 Preparing Backend deployment for Forge..." -ForegroundColor Yellow
    
    $forgeScript = @"
#!/bin/bash
# Laravel Forge Deployment Script
# This runs automatically when you push to GitHub

cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Activate maintenance mode
php artisan down --retry=60 --secret="deployment-secret-key" || true

# Pull latest changes
git pull origin master

# Install/update dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
php artisan queue:restart

# Clear application cache
php artisan cache:clear

# Deactivate maintenance mode
php artisan up

echo "✅ Backend deployment completed!"
"@

    $forgeScript | Out-File -FilePath "backend/forge-deploy.sh" -Encoding UTF8 -NoNewline
    Write-Host "   ✅ Created forge-deploy.sh script" -ForegroundColor Green
    Write-Host "   ℹ️  Copy this script to Laravel Forge → Your Site → App → Deployment Script" -ForegroundColor Blue
    
    return $true
}

# Function to run pre-deployment checks
function Test-Deployment {
    Write-Host "`n🔍 Running Pre-Deployment Checks..." -ForegroundColor Yellow
    
    $allPassed = $true
    
    # Check 1: Frontend dependencies
    Write-Host "   • Checking frontend dependencies..." -ForegroundColor White
    if (Test-Path "frontend/package.json") {
        Push-Location frontend
        try {
            npm list --depth=0 2>&1 | Out-Null
            Write-Host "     ✅ Frontend dependencies OK" -ForegroundColor Green
        } catch {
            Write-Host "     ⚠️  Run 'npm install' in frontend/" -ForegroundColor Yellow
            $allPassed = $false
        } finally {
            Pop-Location
        }
    }
    
    # Check 2: Backend dependencies
    Write-Host "   • Checking backend dependencies..." -ForegroundColor White
    if (Test-Path "backend/composer.json") {
        Push-Location backend
        try {
            composer validate --quiet 2>&1 | Out-Null
            Write-Host "     ✅ Backend dependencies OK" -ForegroundColor Green
        } catch {
            Write-Host "     ⚠️  Run 'composer install' in backend/" -ForegroundColor Yellow
            $allPassed = $false
        } finally {
            Pop-Location
        }
    }
    
    # Check 3: Environment files
    Write-Host "   • Checking environment configuration..." -ForegroundColor White
    if (Test-Path "frontend/.env.example") {
        Write-Host "     ✅ Frontend .env.example exists" -ForegroundColor Green
    } else {
        Write-Host "     ❌ Missing frontend/.env.example" -ForegroundColor Red
        $allPassed = $false
    }
    
    if (Test-Path "backend/.env.example") {
        Write-Host "     ✅ Backend .env.example exists" -ForegroundColor Green
    } else {
        Write-Host "     ❌ Missing backend/.env.example" -ForegroundColor Red
        $allPassed = $false
    }
    
    # Check 4: Git status
    Write-Host "   • Checking git status..." -ForegroundColor White
    if (Test-GitRepository) {
        $branch = git rev-parse --abbrev-ref HEAD
        Write-Host "     ✅ On branch: $branch" -ForegroundColor Green
        
        $changes = Get-GitStatus
        if ($changes) {
            Write-Host "     ⚠️  Uncommitted changes detected" -ForegroundColor Yellow
            Write-Host "     $($changes.Count) file(s) modified" -ForegroundColor White
        }
    }
    
    # Check 5: Build test
    Write-Host "   • Testing frontend build..." -ForegroundColor White
    Push-Location frontend
    try {
        $env:NODE_ENV = "production"
        npm run build 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "     ✅ Frontend builds successfully" -ForegroundColor Green
        } else {
            Write-Host "     ❌ Frontend build failed" -ForegroundColor Red
            $allPassed = $false
        }
    } catch {
        Write-Host "     ⚠️  Could not test build" -ForegroundColor Yellow
    } finally {
        Pop-Location
    }
    
    return $allPassed
}

# Main deployment logic
switch ($Target) {
    'check' {
        Write-Host "Running deployment checks only...`n" -ForegroundColor Cyan
        $result = Test-Deployment
        
        if ($result) {
            Write-Host "`n✅ All checks passed! Ready to deploy." -ForegroundColor Green
            Write-Host "`nRun deployment with:" -ForegroundColor Cyan
            Write-Host "  .\deploy.ps1 -Target all -Message 'Your commit message'" -ForegroundColor White
        } else {
            Write-Host "`n⚠️  Some checks failed. Please fix issues before deploying." -ForegroundColor Yellow
        }
    }
    
    'frontend' {
        Write-Host "Deploying frontend only...`n" -ForegroundColor Cyan
        
        if (Deploy-ToGitHub -CommitMessage $Message) {
            Write-Host "`n✅ Frontend will auto-deploy via Vercel (GitHub integration)" -ForegroundColor Green
            Write-Host "   Check status: https://vercel.com/madsens-projects" -ForegroundColor Blue
        }
    }
    
    'backend' {
        Write-Host "Deploying backend only...`n" -ForegroundColor Cyan
        
        if (Deploy-ToGitHub -CommitMessage $Message) {
            Deploy-Backend
            Write-Host "`n✅ Backend will auto-deploy via Laravel Forge (GitHub integration)" -ForegroundColor Green
            Write-Host "   Check status: https://forge.laravel.com" -ForegroundColor Blue
        }
    }
    
    'all' {
        Write-Host "Deploying both frontend and backend...`n" -ForegroundColor Cyan
        
        # Run checks first
        $checksPass = Test-Deployment
        if (-not $checksPass) {
            Write-Host "`n⚠️  Pre-deployment checks failed!" -ForegroundColor Yellow
            $continue = Read-Host "Continue anyway? (y/N)"
            if ($continue -ne 'y') {
                Write-Host "❌ Deployment cancelled" -ForegroundColor Red
                exit 1
            }
        }
        
        # Deploy to GitHub (triggers auto-deploy on both platforms)
        if (Deploy-ToGitHub -CommitMessage $Message) {
            Deploy-Backend
            
            Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Green
            Write-Host "║  ✅ DEPLOYMENT INITIATED SUCCESSFULLY  ║" -ForegroundColor Green
            Write-Host "╚════════════════════════════════════════╝" -ForegroundColor Green
            
            Write-Host "`nAuto-deployment status:" -ForegroundColor Cyan
            Write-Host "  • Frontend (Vercel): https://vercel.com/madsens-projects" -ForegroundColor White
            Write-Host "  • Backend (Forge):   https://forge.laravel.com" -ForegroundColor White
            
            Write-Host "`nProduction URLs:" -ForegroundColor Cyan
            Write-Host "  • Frontend: https://rent-ljgrpeajm-madsens-projects.vercel.app" -ForegroundColor White
            Write-Host "  • Backend:  https://renthub-tbj7yxj7.on-forge.com" -ForegroundColor White
            
            Write-Host "`n⏱️  Deployment usually takes 2-5 minutes" -ForegroundColor Yellow
            Write-Host "Monitor deployment logs in respective dashboards" -ForegroundColor White
        }
    }
}

Write-Host "`n" -ForegroundColor White
