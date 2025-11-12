#!/usr/bin/env pwsh
# Trigger GitHub Actions Auto-Fix Workflow

Write-Host "🚀 Triggering Auto-Fix Workflow on GitHub..." -ForegroundColor Cyan

# Method 1: Using gh CLI (if available)
try {
    gh workflow run "daily-auto-fix.yml" --field fix_type=all
    Write-Host "✅ Workflow triggered via gh CLI!" -ForegroundColor Green
} catch {
    Write-Host "⚠️ gh CLI not available. Use manual trigger instead." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "📋 MANUAL STEPS:" -ForegroundColor Cyan
    Write-Host "1. Go to: https://github.com/anemettemadsen33/RentHub/actions/workflows/daily-auto-fix.yml"
    Write-Host "2. Click 'Run workflow' button (top right)"
    Write-Host "3. Select Branch: master"
    Write-Host "4. Select Fix type: all"
    Write-Host "5. Click green 'Run workflow' button"
    Write-Host "6. Wait 3-5 minutes"
    Write-Host ""
    Write-Host "Opening browser..." -ForegroundColor Cyan
    Start-Process "https://github.com/anemettemadsen33/RentHub/actions/workflows/daily-auto-fix.yml"
}
