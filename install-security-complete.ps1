# RentHub - Complete Security & DevOps Installation Script
# PowerShell Script for Windows
# Version: 1.0.0
# Date: November 3, 2025

Write-Host "=================================" -ForegroundColor Cyan
Write-Host "RentHub Security & DevOps Setup" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check Prerequisites
Write-Host "Step 1: Checking Prerequisites..." -ForegroundColor Yellow
$prerequisites = @{
    "PHP" = { php --version }
    "Composer" = { composer --version }
    "Docker" = { docker --version }
    "Node.js" = { node --version }
}

foreach ($tool in $prerequisites.Keys) {
    try {
        & $prerequisites[$tool] | Out-Null
        Write-Host "  ✓ $tool is installed" -ForegroundColor Green
    } catch {
        Write-Host "  ✗ $tool is not installed" -ForegroundColor Red
        exit 1
    }
}

# Step 2: Install PHP Dependencies
Write-Host "`nStep 2: Installing PHP dependencies..." -ForegroundColor Yellow
Set-Location backend
composer install --no-interaction --prefer-dist --optimize-autoloader
composer require firebase/php-jwt

Write-Host "  ✓ PHP dependencies installed" -ForegroundColor Green

# Step 3: Configure Environment
Write-Host "`nStep 3: Configuring environment..." -ForegroundColor Yellow
if (!(Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "  ✓ Created .env file" -ForegroundColor Green
}

php artisan key:generate --force

# Generate JWT secret
$jwtSecret = [Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }))
(Get-Content .env) -replace 'JWT_SECRET=.*', "JWT_SECRET=$jwtSecret" | Set-Content .env

Write-Host "  ✓ Environment configured" -ForegroundColor Green

# Step 4: Run Database Migrations
Write-Host "`nStep 4: Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force

Write-Host "  ✓ Database migrations completed" -ForegroundColor Green

# Step 5: Set up Monitoring Stack
Write-Host "`nStep 5: Setting up monitoring stack..." -ForegroundColor Yellow
Set-Location ..\docker\monitoring

if (Test-Path docker-compose.monitoring.yml) {
    docker-compose -f docker-compose.monitoring.yml up -d
    Write-Host "  ✓ Monitoring stack started" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Monitoring configuration not found" -ForegroundColor Yellow
}

# Step 6: Install Frontend Dependencies
Write-Host "`nStep 6: Installing frontend dependencies..." -ForegroundColor Yellow
Set-Location ..\..\frontend
npm install

Write-Host "  ✓ Frontend dependencies installed" -ForegroundColor Green

# Step 7: Create OAuth Client
Write-Host "`nStep 7: Creating OAuth client..." -ForegroundColor Yellow
Set-Location ..\backend

$createClient = @"
use App\Models\OAuthClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

`$client = OAuthClient::create([
    'client_id' => 'renthub_web',
    'client_secret' => Hash::make(Str::random(40)),
    'name' => 'RentHub Web Application',
    'redirect_uris' => json_encode(['http://localhost:3000/callback']),
    'scopes' => json_encode(['read', 'write']),
    'is_confidential' => true,
    'is_active' => true,
]);

echo 'OAuth Client Created: ' . `$client->client_id;
"@

php artisan tinker --execute="$createClient"

Write-Host "  ✓ OAuth client created" -ForegroundColor Green

# Step 8: Display Access URLs
Write-Host "`n=================================" -ForegroundColor Cyan
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Access Points:" -ForegroundColor Yellow
Write-Host "  Backend API:      http://localhost:8000" -ForegroundColor White
Write-Host "  Frontend:         http://localhost:3000" -ForegroundColor White
Write-Host "  Prometheus:       http://localhost:9090" -ForegroundColor White
Write-Host "  Grafana:          http://localhost:3001" -ForegroundColor White
Write-Host "  Alertmanager:     http://localhost:9093" -ForegroundColor White
Write-Host ""
Write-Host "Default Credentials:" -ForegroundColor Yellow
Write-Host "  Grafana:          admin / admin" -ForegroundColor White
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Configure Slack webhook in .env" -ForegroundColor White
Write-Host "  2. Set up SSL certificates" -ForegroundColor White
Write-Host "  3. Review security audit logs" -ForegroundColor White
Write-Host "  4. Configure monitoring alerts" -ForegroundColor White
Write-Host ""
Write-Host "Documentation:" -ForegroundColor Yellow
Write-Host "  Security Guide:   ./COMPLETE_SECURITY_DEVOPS_IMPLEMENTATION_2025_11_03.md" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Ready to launch!" -ForegroundColor Green
