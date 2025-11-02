# RentHub Setup Script for Windows
# This script helps set up the development environment

Write-Host "🚀 RentHub Development Setup" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path ".\backend") -or -not (Test-Path ".\frontend")) {
    Write-Host "❌ Error: Please run this script from the RentHub root directory" -ForegroundColor Red
    exit 1
}

# Backend Setup
Write-Host "📦 Setting up Backend..." -ForegroundColor Yellow
Set-Location backend

# Check if composer is installed
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Composer not found. Please install Composer first." -ForegroundColor Red
    exit 1
}

# Install dependencies
Write-Host "Installing Composer dependencies..." -ForegroundColor Gray
composer install

# Setup .env
if (-not (Test-Path ".env")) {
    Write-Host "Creating .env file..." -ForegroundColor Gray
    Copy-Item .env.example .env
    
    Write-Host "Generating application key..." -ForegroundColor Gray
    php artisan key:generate
}

# Setup database
if (-not (Test-Path "database\database.sqlite")) {
    Write-Host "Creating SQLite database..." -ForegroundColor Gray
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
}

# Run migrations
Write-Host "Running migrations..." -ForegroundColor Gray
php artisan migrate

# Create storage link
Write-Host "Creating storage link..." -ForegroundColor Gray
php artisan storage:link

Write-Host "✅ Backend setup complete!" -ForegroundColor Green
Write-Host ""

# Frontend Setup
Set-Location ..\frontend
Write-Host "📦 Setting up Frontend..." -ForegroundColor Yellow

# Check if npm is installed
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Host "❌ NPM not found. Please install Node.js first." -ForegroundColor Red
    exit 1
}

# Install dependencies
Write-Host "Installing NPM dependencies..." -ForegroundColor Gray
npm install

# Setup .env.local
if (-not (Test-Path ".env.local")) {
    Write-Host "Creating .env.local file..." -ForegroundColor Gray
    Copy-Item .env.example .env.local
}

Write-Host "✅ Frontend setup complete!" -ForegroundColor Green
Write-Host ""

# Back to root
Set-Location ..

# Summary
Write-Host "================================" -ForegroundColor Cyan
Write-Host "✨ Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "To start development:" -ForegroundColor White
Write-Host ""
Write-Host "Backend:" -ForegroundColor Yellow
Write-Host "  cd backend" -ForegroundColor Gray
Write-Host "  php artisan serve" -ForegroundColor Gray
Write-Host "  (will run on http://localhost:8000)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "Frontend (in another terminal):" -ForegroundColor Yellow
Write-Host "  cd frontend" -ForegroundColor Gray
Write-Host "  npm run dev" -ForegroundColor Gray
Write-Host "  (will run on http://localhost:3000)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "Happy coding! 🎉" -ForegroundColor Cyan
