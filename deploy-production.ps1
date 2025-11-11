# Deploy to Production Script
# Usage: .\deploy-production.ps1

Write-Host "🚀 RentHub Production Deployment" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're on master branch
$branch = git branch --show-current
if ($branch -ne "master") {
    Write-Host "⚠️  WARNING: You're on branch '$branch', not 'master'" -ForegroundColor Yellow
    $confirm = Read-Host "Continue anyway? (y/N)"
    if ($confirm -ne "y") {
        Write-Host "❌ Deployment cancelled" -ForegroundColor Red
        exit 1
    }
}

# Check for uncommitted changes
$status = git status --porcelain
if ($status) {
    Write-Host "⚠️  You have uncommitted changes:" -ForegroundColor Yellow
    git status --short
    $confirm = Read-Host "Commit and deploy? (y/N)"
    if ($confirm -eq "y") {
        $message = Read-Host "Commit message"
        git add -A
        git commit -m "$message"
    } else {
        Write-Host "❌ Deployment cancelled" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "📊 Deployment Summary:" -ForegroundColor Green
Write-Host "  Branch: $branch"
Write-Host "  Commit: $(git log -1 --oneline)"
Write-Host ""

# Push to GitHub (triggers GitHub Actions)
Write-Host "1️⃣ Pushing to GitHub..." -ForegroundColor Yellow
git push origin $branch

if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Pushed successfully!" -ForegroundColor Green
} else {
    Write-Host "   ❌ Push failed!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "2️⃣ GitHub Actions will now deploy:" -ForegroundColor Yellow
Write-Host "   - Backend to Forge (Laravel)" -ForegroundColor Cyan
Write-Host "   - Frontend to Vercel (Next.js)" -ForegroundColor Cyan
Write-Host ""

Write-Host "📊 Monitor deployment:" -ForegroundColor Green
Write-Host "   GitHub Actions: https://github.com/anemettemadsen33/RentHub/actions" -ForegroundColor Cyan
Write-Host "   Forge: https://forge.laravel.com/servers" -ForegroundColor Cyan
Write-Host "   Vercel: https://vercel.com/madsen-s-projects/rent-hub" -ForegroundColor Cyan
Write-Host ""

Write-Host "⏱️  Estimated deployment time: 2-3 minutes" -ForegroundColor Yellow
Write-Host ""

$wait = Read-Host "Open GitHub Actions in browser? (y/N)"
if ($wait -eq "y") {
    Start-Process "https://github.com/anemettemadsen33/RentHub/actions"
}

Write-Host ""
Write-Host "✅ Deployment initiated!" -ForegroundColor Green
Write-Host "🎉 Your changes will be live in ~3 minutes!" -ForegroundColor Green
