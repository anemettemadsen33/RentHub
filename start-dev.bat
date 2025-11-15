@echo off
REM ============================================
REM RentHub - Quick Start Script (Windows)
REM ============================================
REM Pornește toate serviciile necesare pentru development

echo.
echo ========================================
echo  RentHub - Development Environment
echo ========================================
echo.

REM Check if running from correct directory
if not exist "backend" (
    echo ERROR: Please run this script from RentHub root directory!
    pause
    exit /b 1
)

echo [1/4] Starting Backend Laravel Server...
start "RentHub Backend" cmd /k "cd backend && php artisan serve"
timeout /t 2 /nobreak >nul

echo [2/4] Starting Reverb WebSocket Server...
start "RentHub WebSocket" cmd /k "cd backend && php artisan reverb:start"
timeout /t 2 /nobreak >nul

echo [3/4] Starting Frontend Next.js Server...
start "RentHub Frontend" cmd /k "cd frontend && npm run dev"
timeout /t 3 /nobreak >nul

echo [4/4] Opening Browser...
timeout /t 5 /nobreak >nul
start http://localhost:3000

echo.
echo ========================================
echo  All Services Started Successfully!
echo ========================================
echo.
echo Backend API:     http://localhost:8000
echo WebSocket:       ws://localhost:8080
echo Frontend:        http://localhost:3000
echo.
echo Press any key to open monitoring dashboard...
pause >nul

REM Show all running processes
echo.
echo Active Processes:
netstat -ano | findstr ":8000 :8080 :3000"
echo.

pause
