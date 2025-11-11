#!/usr/bin/env pwsh
# Quick Start Script - Start Both Servers

Write-Host "`n🚀 Starting RentHub Servers...`n" -ForegroundColor Cyan

# Start Backend in new window
Write-Host "Starting Backend Server..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Set-Location 'C:\laragon\www\RentHub\backend'; Write-Host '🔧 Backend Server Starting...' -ForegroundColor Green; php artisan serve --host=127.0.0.1 --port=8000"

Start-Sleep -Seconds 2

# Start Frontend in new window
Write-Host "Starting Frontend Server..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Set-Location 'C:\laragon\www\RentHub\frontend'; Write-Host '⚛️ Frontend Server Starting...' -ForegroundColor Green; npm run dev"

Start-Sleep -Seconds 3

Write-Host "`n✅ Both servers are starting!`n" -ForegroundColor Green
Write-Host "Backend:  http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "Frontend: http://localhost:3000" -ForegroundColor Cyan
Write-Host "Admin:    http://127.0.0.1:8000/admin`n" -ForegroundColor Cyan

Write-Host "Check the new terminal windows for server status.`n" -ForegroundColor Gray
