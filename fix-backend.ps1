# Fix RentHub Backend
Write-Host "Fixing RentHub Backend..." -ForegroundColor Cyan

# Go to backend directory
Set-Location backend

# Remove vendor lock
Write-Host "Cleaning vendor directory..." -ForegroundColor Yellow
if (Test-Path "vendor") {
    Remove-Item "vendor\composer\*.lock" -Force -ErrorAction SilentlyContinue
}

# Generate autoload without optimization first
Write-Host "Generating basic autoload..." -ForegroundColor Yellow
& composer dump-autoload --no-scripts 2>&1 | Out-Null

Start-Sleep -Seconds 2

# Generate APP_KEY if missing
Write-Host "Generating APP_KEY..." -ForegroundColor Yellow
$envContent = Get-Content .env -Raw
if ($envContent -match "APP_KEY=\s*$") {
    $key = "base64:" + [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((New-Guid).ToString()))
    $envContent = $envContent -replace "APP_KEY=", "APP_KEY=$key"
    Set-Content .env $envContent -NoNewline
    Write-Host "✅ APP_KEY generated" -ForegroundColor Green
} else {
    Write-Host "✅ APP_KEY already exists" -ForegroundColor Green
}

# Test if Laravel can boot
Write-Host "`nTesting Laravel..." -ForegroundColor Yellow
try {
    $output = php -r "require 'vendor/autoload.php';"
    Write-Host "✅ Autoload working!" -ForegroundColor Green
} catch {
    Write-Host "❌ Autoload failed: $_" -ForegroundColor Red
}

Write-Host "`n✅ Backend fixed! You can now start it with:" -ForegroundColor Green
Write-Host "   cd backend && php -S localhost:8001 -t public" -ForegroundColor Cyan

Set-Location ..
