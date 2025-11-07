#!/usr/bin/env pwsh
# RentHub - Start All Services Script
# Usage: .\start-all.ps1

Write-Host "`n╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║           RENTHUB - START ALL SERVICES                       ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Stop all previous instances
Write-Host "🛑 Stopping previous instances..." -ForegroundColor Yellow
Stop-Process -Name "php","node" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Check if ports are free
$portsInUse = @()
$requiredPorts = @(3000, 8000)

foreach ($port in $requiredPorts) {
    $portCheck = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
    if ($portCheck) {
        $portsInUse += $port
    }
}

if ($portsInUse.Count -gt 0) {
    Write-Host "⚠️  Ports still in use: $($portsInUse -join ', ')" -ForegroundColor Red
    Write-Host "Waiting 3 seconds..." -ForegroundColor Yellow
    Start-Sleep -Seconds 3
}

# Clear Laravel cache
Write-Host "`n📦 Clearing Laravel cache..." -ForegroundColor Cyan
Set-Location "C:\laragon\www\RentHub\backend"
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null
php artisan route:clear | Out-Null
php artisan view:clear | Out-Null
Write-Host "✅ Cache cleared`n" -ForegroundColor Green

# Start Backend (Laravel)
Write-Host "🚀 Starting Laravel Backend on port 8000..." -ForegroundColor Cyan
$backendJob = Start-Job -ScriptBlock {
    Set-Location "C:\laragon\www\RentHub\backend"
    php artisan serve --host=127.0.0.1 --port=8000
}
Start-Sleep -Seconds 3

# Check if backend started
$backendPort = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($backendPort) {
    Write-Host "✅ Backend started successfully on http://localhost:8000`n" -ForegroundColor Green
} else {
    Write-Host "❌ Backend failed to start!" -ForegroundColor Red
    exit 1
}

# Start Frontend (Next.js)
Write-Host "🚀 Starting Next.js Frontend on port 3000..." -ForegroundColor Cyan
$frontendJob = Start-Job -ScriptBlock {
    Set-Location "C:\laragon\www\RentHub\frontend"
    npm run dev
}
Start-Sleep -Seconds 5

# Check if frontend started
$frontendPort = Get-NetTCPConnection -LocalPort 3000 -State Listen -ErrorAction SilentlyContinue
if ($frontendPort) {
    Write-Host "✅ Frontend started successfully on http://localhost:3000`n" -ForegroundColor Green
} else {
    Write-Host "❌ Frontend failed to start!" -ForegroundColor Red
}

# Display status
Write-Host "`n╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                 ✅ RENTHUB IS RUNNING                        ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Green

Write-Host "📍 URLs:" -ForegroundColor Cyan
Write-Host "   Frontend:  http://localhost:3000" -ForegroundColor White
Write-Host "   Backend:   http://localhost:8000" -ForegroundColor White
Write-Host "   API:       http://localhost:8000/api" -ForegroundColor White
Write-Host "   Admin:     admin@renthub.com / Admin@123456`n" -ForegroundColor Yellow

Write-Host "📝 Commands:" -ForegroundColor Cyan
Write-Host "   View Backend logs:  Receive-Job $($backendJob.Id)" -ForegroundColor White
Write-Host "   View Frontend logs: Receive-Job $($frontendJob.Id)" -ForegroundColor White
Write-Host "   Stop all:           Stop-Job $($backendJob.Id),$($frontendJob.Id)`n" -ForegroundColor White

Write-Host "⚠️  Keep this window open! Press Ctrl+C to stop all services.`n" -ForegroundColor Yellow

# Keep script running and show logs
try {
    while ($true) {
        Start-Sleep -Seconds 5
        # Check if jobs are still running
        if ($backendJob.State -ne 'Running' -or $frontendJob.State -ne 'Running') {
            Write-Host "`n❌ A service stopped unexpectedly!" -ForegroundColor Red
            Write-Host "Backend status: $($backendJob.State)" -ForegroundColor Yellow
            Write-Host "Frontend status: $($frontendJob.State)" -ForegroundColor Yellow
            break
        }
    }
} finally {
    Write-Host "`n🛑 Stopping all services..." -ForegroundColor Yellow
    Stop-Job -Job $backendJob,$frontendJob -ErrorAction SilentlyContinue
    Remove-Job -Job $backendJob,$frontendJob -Force -ErrorAction SilentlyContinue
    Write-Host "✅ All services stopped`n" -ForegroundColor Green
}
