# Fix RentHub Backend on Laravel Forge via SSH
# Run this from your local machine

$ForgeIP = "178.128.135.24"
$ForgeUser = "forge"
$ProjectPath = "/home/forge/renthub-tbj7yxj7.on-forge.com/current/backend"

Write-Host "🚀 Connecting to Forge server..." -ForegroundColor Cyan
Write-Host "Server: $ForgeIP" -ForegroundColor Yellow
Write-Host ""

# Create the fix script inline
$FixScript = @'
#!/bin/bash
set -e

echo "🔧 Fixing RentHub Backend..."
cd /home/forge/renthub-tbj7yxj7.on-forge.com/current/backend

# Fix namespace issues
echo "📝 Fixing PHP namespaces..."
find . -name "*.php" -type f -not -path "./vendor/*" -exec sed -i 's/namespace App\\\\/namespace App\\/g' {} \;
find . -name "*.php" -type f -not -path "./vendor/*" -exec sed -i 's/use App\\\\/use App\\/g' {} \;

# Clear caches
echo "🗑️ Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Rebuild caches
echo "⚡ Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "✅ Backend fixed!"
echo "🌐 Test: https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
'@

# Execute via SSH
ssh "${ForgeUser}@${ForgeIP}" "bash -s" @"
$FixScript
"@

Write-Host ""
Write-Host "✅ Done! Testing API..." -ForegroundColor Green

# Test the API
try {
    $response = Invoke-WebRequest -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties" -Method GET -TimeoutSec 10 -SkipHttpErrorCheck
    Write-Host "Backend Status: $($response.StatusCode)" -ForegroundColor $(if ($response.StatusCode -eq 200 -or $response.StatusCode -eq 401) { 'Green' } else { 'Red' })
} catch {
    Write-Host "❌ Could not test API: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "🎉 Backend should now be working!" -ForegroundColor Cyan
Write-Host "📱 Check Vercel deployment at: https://rent-hub-beta.vercel.app" -ForegroundColor Yellow
