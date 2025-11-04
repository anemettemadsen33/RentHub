# RentHub - Complete Security & Performance Installation Script
# PowerShell Version for Windows
# Run as Administrator

Write-Host "🚀 RentHub - Security & Performance Installation" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
if (-NOT ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "❌ This script requires Administrator privileges!" -ForegroundColor Red
    Write-Host "Please run PowerShell as Administrator and try again." -ForegroundColor Yellow
    exit 1
}

# Function to check command exists
function Test-Command {
    param($Command)
    $null = Get-Command $Command -ErrorAction SilentlyContinue
    return $?
}

# Function to display step
function Show-Step {
    param($Step, $Message)
    Write-Host ""
    Write-Host "[$Step] $Message" -ForegroundColor Green
    Write-Host "-----------------------------------" -ForegroundColor Gray
}

# Check prerequisites
Show-Step "1/10" "Checking Prerequisites"

$prerequisites = @{
    "php" = "PHP"
    "composer" = "Composer"
    "node" = "Node.js"
    "npm" = "NPM"
    "docker" = "Docker"
}

$missing = @()
foreach ($cmd in $prerequisites.Keys) {
    if (Test-Command $cmd) {
        Write-Host "✅ $($prerequisites[$cmd]) found" -ForegroundColor Green
    } else {
        Write-Host "❌ $($prerequisites[$cmd]) not found" -ForegroundColor Red
        $missing += $prerequisites[$cmd]
    }
}

if ($missing.Count -gt 0) {
    Write-Host ""
    Write-Host "⚠️  Missing prerequisites: $($missing -join ', ')" -ForegroundColor Yellow
    Write-Host "Please install the missing tools and try again." -ForegroundColor Yellow
    exit 1
}

# Navigate to backend directory
Show-Step "2/10" "Setting up Backend"
Set-Location backend

# Install PHP dependencies
Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader

# Copy environment file
if (-not (Test-Path ".env")) {
    Write-Host "📝 Creating .env file..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    Write-Host "⚠️  Please configure your .env file before continuing!" -ForegroundColor Yellow
    Read-Host "Press Enter when ready to continue..."
}

# Generate application key
Write-Host "🔑 Generating application key..." -ForegroundColor Cyan
php artisan key:generate --force

# Run migrations
Show-Step "3/10" "Setting up Database"
Write-Host "🗄️  Running database migrations..." -ForegroundColor Cyan
php artisan migrate --force

# Seed database
Write-Host "🌱 Seeding database..." -ForegroundColor Cyan
php artisan db:seed --force

# Install security packages
Show-Step "4/10" "Installing Security Packages"
Write-Host "🔐 Installing security dependencies..." -ForegroundColor Cyan
composer require pragmarx/google2fa-laravel --no-interaction
composer require spatie/laravel-permission --no-interaction
composer require laravel/sanctum --no-interaction

# Publish configurations
Write-Host "📋 Publishing configurations..." -ForegroundColor Cyan
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Setup caching
Show-Step "5/10" "Configuring Caching"
Write-Host "⚡ Setting up Redis cache..." -ForegroundColor Cyan
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup queue
Show-Step "6/10" "Configuring Queue System"
Write-Host "📬 Setting up queue workers..." -ForegroundColor Cyan
php artisan queue:restart

# Setup storage
Show-Step "7/10" "Configuring Storage"
Write-Host "📁 Creating storage links..." -ForegroundColor Cyan
php artisan storage:link

# Set permissions
Write-Host "🔒 Setting directory permissions..." -ForegroundColor Cyan
$directories = @("storage", "bootstrap/cache")
foreach ($dir in $directories) {
    if (Test-Path $dir) {
        # Windows equivalent of chmod 775
        $acl = Get-Acl $dir
        $acl.SetAccessRuleProtection($false, $true)
        Set-Acl -Path $dir -AclObject $acl
        Write-Host "  ✓ Set permissions for $dir" -ForegroundColor Gray
    }
}

# Navigate to frontend directory
Show-Step "8/10" "Setting up Frontend"
Set-Location ../frontend

# Install Node dependencies
Write-Host "📦 Installing Node.js dependencies..." -ForegroundColor Cyan
npm install

# Build assets
Write-Host "🏗️  Building frontend assets..." -ForegroundColor Cyan
npm run build

# Setup monitoring
Show-Step "9/10" "Setting up Monitoring"
Set-Location ..

if (Test-Command "kubectl") {
    Write-Host "☸️  Setting up Kubernetes monitoring..." -ForegroundColor Cyan
    
    # Apply monitoring configurations
    kubectl apply -f k8s/monitoring/prometheus-deployment.yaml
    kubectl apply -f k8s/monitoring/grafana-deployment.yaml
    kubectl apply -f k8s/monitoring/alertmanager-deployment.yaml
    
    Write-Host "✅ Monitoring stack deployed" -ForegroundColor Green
} else {
    Write-Host "⚠️  kubectl not found, skipping Kubernetes setup" -ForegroundColor Yellow
}

# Setup Docker containers
Show-Step "10/10" "Starting Docker Containers"

if (Test-Command "docker-compose") {
    Write-Host "🐳 Starting Docker containers..." -ForegroundColor Cyan
    docker-compose up -d
    
    # Wait for services to be ready
    Write-Host "⏳ Waiting for services to start..." -ForegroundColor Cyan
    Start-Sleep -Seconds 10
    
    # Check container status
    docker-compose ps
} else {
    Write-Host "⚠️  docker-compose not found, skipping Docker setup" -ForegroundColor Yellow
}

# Final steps
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ Installation Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Configure your .env file" -ForegroundColor White
Write-Host "2. Update database credentials" -ForegroundColor White
Write-Host "3. Configure Redis connection" -ForegroundColor White
Write-Host "4. Set up email service (SMTP)" -ForegroundColor White
Write-Host "5. Configure OAuth providers" -ForegroundColor White
Write-Host ""
Write-Host "🚀 Access your application:" -ForegroundColor Yellow
Write-Host "  • Backend API: http://localhost:8000" -ForegroundColor White
Write-Host "  • Frontend: http://localhost:3000" -ForegroundColor White
Write-Host "  • Grafana: http://localhost:3001" -ForegroundColor White
Write-Host "  • Prometheus: http://localhost:9090" -ForegroundColor White
Write-Host ""
Write-Host "📚 Documentation:" -ForegroundColor Yellow
Write-Host "  • Security Guide: COMPREHENSIVE_SECURITY_GUIDE.md" -ForegroundColor White
Write-Host "  • Performance Guide: ADVANCED_PERFORMANCE_OPTIMIZATION.md" -ForegroundColor White
Write-Host "  • Complete Guide: SECURITY_PERFORMANCE_MARKETING_COMPLETE_2025_11_03.md" -ForegroundColor White
Write-Host ""
Write-Host "🔐 Security Checklist:" -ForegroundColor Yellow
Write-Host "  □ Change default passwords" -ForegroundColor White
Write-Host "  □ Configure SSL certificates" -ForegroundColor White
Write-Host "  □ Enable two-factor authentication" -ForegroundColor White
Write-Host "  □ Review security headers" -ForegroundColor White
Write-Host "  □ Set up backup strategy" -ForegroundColor White
Write-Host ""
Write-Host "Need help? Contact support@renthub.com" -ForegroundColor Cyan
Write-Host ""
