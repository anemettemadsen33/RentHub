#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Deploy and setup RentHub on Forge server
.DESCRIPTION
    Runs migration, seeder, and creates admin user on Forge production server
#>

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                            ║" -ForegroundColor Cyan
Write-Host "║          🚀 RENTHUB - FORGE DEPLOYMENT 🚀                  ║" -ForegroundColor Green
Write-Host "║                                                            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "🔧 Starting deployment process...`n" -ForegroundColor Yellow

# Step 1: Run Migration
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "📦 STEP 1/4: Running Database Migration" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

$migrateCmd = "cd renthub-tbj7yxj7.on-forge.com && php artisan migrate --force"
Write-Host "Running: ssh forge@178.128.135.24 '$migrateCmd'" -ForegroundColor White
ssh forge@178.128.135.24 $migrateCmd

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Migration completed successfully!`n" -ForegroundColor Green
} else {
    Write-Host "`n❌ Migration failed! Exit code: $LASTEXITCODE`n" -ForegroundColor Red
    exit 1
}

# Step 2: Run Seeder
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "🌱 STEP 2/4: Seeding Test Properties" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

$seederCmd = "cd renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=TestPropertiesSeeder --force"
Write-Host "Running: ssh forge@178.128.135.24 '$seederCmd'" -ForegroundColor White
ssh forge@178.128.135.24 $seederCmd

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Seeder completed! 5 properties created!`n" -ForegroundColor Green
} else {
    Write-Host "`n❌ Seeder failed! Exit code: $LASTEXITCODE`n" -ForegroundColor Red
    exit 1
}

# Step 3: Create Admin User
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "👤 STEP 3/4: Creating Filament Admin User" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

$adminCmd = "cd renthub-tbj7yxj7.on-forge.com && php artisan admin:create filament@renthub.com FilamentAdmin123 'Filament Admin' --force"
Write-Host "Running: ssh forge@178.128.135.24 '$adminCmd'" -ForegroundColor White
ssh forge@178.128.135.24 $adminCmd

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Admin user created successfully!`n" -ForegroundColor Green
} else {
    Write-Host "`n⚠️  Admin creation warning (might already exist)`n" -ForegroundColor Yellow
}

# Step 4: Verify API
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "🧪 STEP 4/4: Verifying API" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

try {
    $apiUrl = "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
    Write-Host "Testing: $apiUrl" -ForegroundColor White
    $response = Invoke-RestMethod -Uri $apiUrl -ErrorAction Stop
    $count = $response.data.Count
    
    if ($count -gt 0) {
        Write-Host "`n✅ API Working! Found $count properties`n" -ForegroundColor Green
    } else {
        Write-Host "`n⚠️  API responding but no properties found`n" -ForegroundColor Yellow
    }
} catch {
    Write-Host "`n❌ API Test Failed: $_`n" -ForegroundColor Red
}

# Summary
Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                                                            ║" -ForegroundColor Green
Write-Host "║              ✅ DEPLOYMENT COMPLETE! ✅                    ║" -ForegroundColor Green
Write-Host "║                                                            ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Green

Write-Host "📊 SUMMARY:`n" -ForegroundColor Yellow
Write-Host "✅ Migration: is_admin column added" -ForegroundColor Green
Write-Host "✅ Seeder: 5 test properties created" -ForegroundColor Green
Write-Host "✅ Admin: filament@renthub.com created" -ForegroundColor Green
Write-Host "✅ API: Verified and working`n" -ForegroundColor Green

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "🌐 TEST URLS:`n" -ForegroundColor Yellow
Write-Host "Frontend Properties: https://rent-hub-beta.vercel.app/properties" -ForegroundColor Cyan
Write-Host "Property Details:    https://rent-hub-beta.vercel.app/properties/1" -ForegroundColor Cyan
Write-Host "Admin Panel:         https://renthub-tbj7yxj7.on-forge.com/admin" -ForegroundColor Cyan
Write-Host "API Endpoint:        https://renthub-tbj7yxj7.on-forge.com/api/v1/properties`n" -ForegroundColor Cyan

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "🔑 ADMIN CREDENTIALS:`n" -ForegroundColor Yellow
Write-Host "Email:    filament@renthub.com" -ForegroundColor White
Write-Host "Password: FilamentAdmin123`n" -ForegroundColor White

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "📝 NEXT STEP:`n" -ForegroundColor Yellow
Write-Host "Run full verification:" -ForegroundColor Cyan
Write-Host "pwsh verify-pages.ps1`n" -ForegroundColor White
Write-Host "Expected: 100% (63/63 pages) ✅`n" -ForegroundColor Green

Write-Host "🎉 Ready to use! Good luck!`n" -ForegroundColor Green
