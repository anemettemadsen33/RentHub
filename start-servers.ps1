# RentHub - Start Development Servers
# Run this script after fixing the npm/tailwind issue

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " RentHub Development Servers Starter" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Check if we're in the right directory
if (!(Test-Path "frontend") -or !(Test-Path "backend")) {
    Write-Host "ERROR: Please run this script from the RentHub root directory" -ForegroundColor Red
    exit 1
}

# Function to check if port is in use
function Test-Port {
    param($Port)
    $connection = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    return $null -ne $connection
}

Write-Host "Checking ports..." -ForegroundColor Yellow

if (Test-Port 3000) {
    Write-Host "⚠️  Port 3000 is already in use" -ForegroundColor Yellow
} else {
    Write-Host "✅ Port 3000 is free" -ForegroundColor Green
}

if (Test-Port 8001) {
    Write-Host "⚠️  Port 8001 is already in use" -ForegroundColor Yellow
} else {
    Write-Host "✅ Port 8001 is free" -ForegroundColor Green
}

Write-Host "`nChecking dependencies..." -ForegroundColor Yellow

# Check frontend dependencies
$frontendNodeModules = Test-Path "frontend/node_modules"
$tailwindInstalled = Test-Path "frontend/node_modules/@tailwindcss/postcss"

if ($frontendNodeModules) {
    Write-Host "✅ Frontend node_modules exists" -ForegroundColor Green
} else {
    Write-Host "❌ Frontend node_modules missing - run: cd frontend && npm install" -ForegroundColor Red
}

if ($tailwindInstalled) {
    Write-Host "✅ @tailwindcss/postcss is installed" -ForegroundColor Green
} else {
    Write-Host "❌ @tailwindcss/postcss missing - see SERVER_STATUS.md for solutions" -ForegroundColor Red
}

# Check backend dependencies
$backendVendor = Test-Path "backend/vendor/autoload.php"

if ($backendVendor) {
    Write-Host "✅ Backend vendor/autoload.php exists" -ForegroundColor Green
} else {
    Write-Host "❌ Backend dependencies missing - run: cd backend && composer install" -ForegroundColor Red
}

# Check .env files
$backendEnv = Test-Path "backend/.env"
$frontendEnv = Test-Path "frontend/.env.local"

if ($backendEnv) {
    Write-Host "✅ Backend .env exists" -ForegroundColor Green
} else {
    Write-Host "⚠️  Backend .env missing - will create from .env.example" -ForegroundColor Yellow
    Copy-Item "backend/.env.example" "backend/.env"
    Write-Host "✅ Created backend/.env" -ForegroundColor Green
}

if ($frontendEnv) {
    Write-Host "✅ Frontend .env.local exists" -ForegroundColor Green
} else {
    Write-Host "⚠️  Frontend .env.local missing - will create from .env.example" -ForegroundColor Yellow
    if (Test-Path "frontend/.env.example") {
        Copy-Item "frontend/.env.example" "frontend/.env.local"
        Write-Host "✅ Created frontend/.env.local" -ForegroundColor Green
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan

# Check if we can start servers
$canStartFrontend = $frontendNodeModules -and $tailwindInstalled
$canStartBackend = $backendVendor

if (-not $canStartFrontend) {
    Write-Host "`n⚠️  CANNOT START FRONTEND" -ForegroundColor Red
    Write-Host "   Reason: Missing @tailwindcss/postcss package" -ForegroundColor Yellow
    Write-Host "   Solutions:" -ForegroundColor Yellow
    Write-Host "   1. Try: npm install -g pnpm && cd frontend && pnpm install" -ForegroundColor White
    Write-Host "   2. Or use Docker: docker-compose up" -ForegroundColor White
    Write-Host "   3. See SERVER_STATUS.md for more options`n" -ForegroundColor White
}

if (-not $canStartBackend) {
    Write-Host "⚠️  CANNOT START BACKEND" -ForegroundColor Red
    Write-Host "   Reason: Composer dependencies not installed" -ForegroundColor Yellow
    Write-Host "   Action: cd backend && composer install`n" -ForegroundColor White
}

if ($canStartFrontend -and $canStartBackend) {
    Write-Host "`n✅ All dependencies ready!" -ForegroundColor Green
    Write-Host "`nStarting servers...`n" -ForegroundColor Cyan
    
    # Start backend in new window
    Write-Host "Starting Backend on http://localhost:8001..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PWD/backend'; php artisan serve --port=8001"
    
    Start-Sleep -Seconds 2
    
    # Start frontend in new window
    Write-Host "Starting Frontend on http://localhost:3000..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PWD/frontend'; npm run dev"
    
    Start-Sleep -Seconds 3
    
    Write-Host "`n========================================" -ForegroundColor Cyan
    Write-Host "✅ Servers are starting!" -ForegroundColor Green
    Write-Host "`nFrontend: http://localhost:3000" -ForegroundColor Cyan
    Write-Host "Backend:  http://localhost:8001`n" -ForegroundColor Cyan
    Write-Host "Check the new PowerShell windows for server logs" -ForegroundColor Yellow
    Write-Host "Press Ctrl+C in those windows to stop servers`n" -ForegroundColor Yellow
} else {
    Write-Host "`nPlease fix the issues above before starting servers." -ForegroundColor Yellow
    Write-Host "See SERVER_STATUS.md for detailed information.`n" -ForegroundColor Yellow
}

Write-Host "========================================`n" -ForegroundColor Cyan
