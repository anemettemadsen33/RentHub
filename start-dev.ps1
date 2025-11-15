# ============================================
# RentHub - Quick Start Script (PowerShell)
# ============================================
# Pornește toate serviciile necesare pentru development

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " RentHub - Development Environment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running from correct directory
if (-not (Test-Path "backend")) {
    Write-Host "ERROR: Please run this script from RentHub root directory!" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Start Backend Laravel Server
Write-Host "[1/4] Starting Backend Laravel Server..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\backend'; Write-Host 'Backend Laravel - Port 8000' -ForegroundColor Green; php artisan serve"
Start-Sleep -Seconds 2

# Start Reverb WebSocket Server
Write-Host "[2/4] Starting Reverb WebSocket Server..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\backend'; Write-Host 'Reverb WebSocket - Port 8080' -ForegroundColor Green; php artisan reverb:start"
Start-Sleep -Seconds 2

# Start Frontend Next.js Server
Write-Host "[3/4] Starting Frontend Next.js Server..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\frontend'; Write-Host 'Frontend Next.js - Port 3000' -ForegroundColor Green; npm run dev"
Start-Sleep -Seconds 5

# Open Browser
Write-Host "[4/4] Opening Browser..." -ForegroundColor Yellow
Start-Sleep -Seconds 3
Start-Process "http://localhost:3000"

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host " All Services Started Successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Backend API:     " -NoNewline; Write-Host "http://localhost:8000" -ForegroundColor Cyan
Write-Host "WebSocket:       " -NoNewline; Write-Host "ws://localhost:8080" -ForegroundColor Cyan
Write-Host "Frontend:        " -NoNewline; Write-Host "http://localhost:3000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C in each window to stop services" -ForegroundColor Yellow
Write-Host ""

# Show active processes
Write-Host "Active Processes:" -ForegroundColor Magenta
netstat -ano | Select-String ":8000|:8080|:3000" | ForEach-Object { $_.Line }
Write-Host ""

Read-Host "Press Enter to exit this window (services will continue running)"
