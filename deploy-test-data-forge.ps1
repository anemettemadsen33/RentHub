#!/usr/bin/env pwsh
# Deploy Test Data to Forge Server

Write-Host "`n═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🚀 Deploying Test Data to Forge Backend" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════`n" -ForegroundColor Cyan

$forgeHost = "renthub-tbj7yxj7.on-forge.com"
$forgeUser = "forge"
$sitePath = "renthub-tbj7yxj7.on-forge.com"

Write-Host "📦 Step 1: Uploading TestPropertiesSeeder to Forge..." -ForegroundColor Yellow

# Using SCP to upload the seeder
$localSeeder = "c:\laragon\www\RentHub\backend\database\seeders\TestPropertiesSeeder.php"
$remoteSeeder = "${forgeUser}@${forgeHost}:${sitePath}/database/seeders/TestPropertiesSeeder.php"

try {
    scp $localSeeder $remoteSeeder
    Write-Host "   ✅ Seeder uploaded successfully" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Failed to upload seeder: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   💡 Make sure you have SSH access configured" -ForegroundColor Yellow
    exit 1
}

Write-Host "`n📝 Step 2: Running seeder on Forge..." -ForegroundColor Yellow

$sshCommand = @"
cd ${sitePath} && php artisan db:seed --class=TestPropertiesSeeder
"@

try {
    ssh "${forgeUser}@${forgeHost}" $sshCommand
    Write-Host "   ✅ Seeder executed successfully" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Failed to run seeder: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n🔍 Step 3: Verifying properties..." -ForegroundColor Yellow

Start-Sleep -Seconds 2

$response = Invoke-RestMethod -Uri "https://${forgeHost}/api/v1/properties" -Method GET

if ($response.data.Count -gt 0) {
    Write-Host "   ✅ Success! Found $($response.data.Count) properties" -ForegroundColor Green
    Write-Host "`n   📋 Properties:" -ForegroundColor Cyan
    $response.data | ForEach-Object {
        Write-Host "   - ID: $($_.id) | $($_.title)" -ForegroundColor White
    }
}
else {
    Write-Host "   ⚠️  No properties found yet" -ForegroundColor Yellow
}

Write-Host "`n═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  ✅ Deployment Complete!" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════`n" -ForegroundColor Cyan
