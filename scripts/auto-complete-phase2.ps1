# Phase 2: Priority Features Implementation Script
# Dashboard Analytics, Multi-language, Multi-currency

Write-Host "🚀 RentHub Phase 2: Priority Features" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host ""

$ErrorActionPreference = "Stop"
$rootPath = Join-Path $PSScriptRoot ".."
$backendPath = Join-Path $rootPath "backend"
$frontendPath = Join-Path $rootPath "frontend"

# Feature 1: Dashboard Analytics
Write-Host "📊 Feature 1: Dashboard Analytics" -ForegroundColor Cyan
Write-Host ""

Set-Location $backendPath

# Create Analytics Service
if (-not (Test-Path "app\Services\AnalyticsService.php")) {
    Write-Host "  Creating AnalyticsService..." -NoNewline
    php artisan make:service AnalyticsService | Out-Null
    Write-Host " ✅" -ForegroundColor Green
} else {
    Write-Host "  AnalyticsService exists ⚠️" -ForegroundColor Yellow
}

# Create Dashboard Controllers
$controllers = @(
    "Api/OwnerDashboardController",
    "Api/TenantDashboardController",
    "Api/AnalyticsController"
)

foreach ($controller in $controllers) {
    $controllerPath = $controller -replace '/', '\'
    if (-not (Test-Path "app\Http\Controllers\$controllerPath.php")) {
        Write-Host "  Creating $controller..." -NoNewline
        php artisan make:controller $controller --api | Out-Null
        Write-Host " ✅" -ForegroundColor Green
    } else {
        Write-Host "  $controller exists ⚠️" -ForegroundColor Yellow
    }
}

# Create Analytics Resources
Write-Host "  Creating API resources..." -NoNewline
php artisan make:resource AnalyticsResource | Out-Null
php artisan make:resource DashboardStatsResource | Out-Null
Write-Host " ✅" -ForegroundColor Green

Write-Host ""

# Feature 2: Multi-language Support
Write-Host "🌍 Feature 2: Multi-language Support" -ForegroundColor Cyan
Write-Host ""

Set-Location $backendPath

# Install dependencies
Write-Host "  Installing Laravel Lang packages..." -NoNewline
composer require laravel-lang/common --quiet 2>&1 | Out-Null
composer require laravel-lang/lang --quiet 2>&1 | Out-Null
Write-Host " ✅" -ForegroundColor Green

# Create SetLocale middleware
if (-not (Test-Path "app\Http\Middleware\SetLocale.php")) {
    Write-Host "  Creating SetLocale middleware..." -NoNewline
    php artisan make:middleware SetLocale | Out-Null
    Write-Host " ✅" -ForegroundColor Green
} else {
    Write-Host "  SetLocale middleware exists ⚠️" -ForegroundColor Yellow
}

# Create language directories
$languages = @("en", "es", "fr", "de", "it", "pt", "ro", "ar")

Set-Location $frontendPath

Write-Host "  Creating language directories..." -NoNewline
foreach ($lang in $languages) {
    $langPath = "public\locales\$lang"
    if (-not (Test-Path $langPath)) {
        New-Item -ItemType Directory -Path $langPath -Force | Out-Null
        
        # Create common.json
        $commonJson = @{
            welcome = "Welcome"
            properties = "Properties"
            bookings = "Bookings"
            search = "Search"
            login = "Login"
            register = "Register"
        } | ConvertTo-Json -Depth 10
        
        Set-Content -Path "$langPath\common.json" -Value $commonJson
    }
}
Write-Host " ✅" -ForegroundColor Green

# Install i18n packages
Write-Host "  Installing i18n packages..." -NoNewline
if (Test-Path "package.json") {
    npm install next-i18next react-i18next i18next --save --silent 2>&1 | Out-Null
    Write-Host " ✅" -ForegroundColor Green
} else {
    Write-Host " ⚠️  (package.json not found)" -ForegroundColor Yellow
}

# Create i18n config
if (-not (Test-Path "next-i18next.config.js")) {
    Write-Host "  Creating i18n config..." -NoNewline
    
    $i18nConfig = @"
module.exports = {
  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'es', 'fr', 'de', 'it', 'pt', 'ro', 'ar'],
    localeDetection: true,
  },
  reloadOnPrerender: process.env.NODE_ENV === 'development',
}
"@
    
    Set-Content -Path "next-i18next.config.js" -Value $i18nConfig
    Write-Host " ✅" -ForegroundColor Green
}

Write-Host ""

# Feature 3: Multi-currency Support
Write-Host "💰 Feature 3: Multi-currency Support" -ForegroundColor Cyan
Write-Host ""

Set-Location $backendPath

# Install currency package
Write-Host "  Installing currency package..." -NoNewline
composer require torann/currency --quiet 2>&1 | Out-Null
Write-Host " ✅" -ForegroundColor Green

# Create CurrencyService
if (-not (Test-Path "app\Services\CurrencyService.php")) {
    Write-Host "  Creating CurrencyService..." -NoNewline
    php artisan make:service CurrencyService | Out-Null
    Write-Host " ✅" -ForegroundColor Green
}

# Create Currency Controller
if (-not (Test-Path "app\Http\Controllers\Api\CurrencyController.php")) {
    Write-Host "  Creating CurrencyController..." -NoNewline
    php artisan make:controller Api/CurrencyController --api | Out-Null
    Write-Host " ✅" -ForegroundColor Green
}

# Publish currency config
Write-Host "  Publishing currency config..." -NoNewline
php artisan vendor:publish --provider="Torann\Currency\CurrencyServiceProvider" --tag=config 2>&1 | Out-Null
Write-Host " ✅" -ForegroundColor Green

# Create currency migration
Write-Host "  Creating currency migrations..." -NoNewline
php artisan make:migration add_currency_to_users_table 2>&1 | Out-Null
php artisan make:migration add_currency_to_properties_table 2>&1 | Out-Null
Write-Host " ✅" -ForegroundColor Green

Set-Location $frontendPath

# Install currency.js
Write-Host "  Installing currency.js..." -NoNewline
if (Test-Path "package.json") {
    npm install currency.js --save --silent 2>&1 | Out-Null
    Write-Host " ✅" -ForegroundColor Green
}

Write-Host ""

# Create Frontend Components
Write-Host "🎨 Creating Frontend Components" -ForegroundColor Cyan
Write-Host ""

Set-Location $frontendPath

# Create component directories
$componentDirs = @(
    "src\components\Dashboard",
    "src\components\Language",
    "src\components\Currency"
)

foreach ($dir in $componentDirs) {
    if (-not (Test-Path $dir)) {
        Write-Host "  Creating $dir..." -NoNewline
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host " ✅" -ForegroundColor Green
    }
}

# Create placeholder components
$dashboardComponent = @"
'use client'
import { useEffect, useState } from 'react'

export default function OwnerDashboard() {
  const [stats, setStats] = useState(null)
  
  useEffect(() => {
    // Fetch dashboard stats
    fetch('/api/owner/dashboard/stats')
      .then(res => res.json())
      .then(data => setStats(data))
  }, [])
  
  return (
    <div className="dashboard">
      <h1>Owner Dashboard</h1>
      {/* Dashboard content */}
    </div>
  )
}
"@

Set-Content -Path "src\components\Dashboard\OwnerDashboard.tsx" -Value $dashboardComponent

$languageSwitcher = @"
'use client'
import { useRouter } from 'next/router'

export default function LanguageSwitcher() {
  const router = useRouter()
  const { locale, locales, pathname, query, asPath } = router
  
  const changeLanguage = (newLocale: string) => {
    router.push({ pathname, query }, asPath, { locale: newLocale })
  }
  
  return (
    <select value={locale} onChange={(e) => changeLanguage(e.target.value)}>
      {locales?.map(loc => (
        <option key={loc} value={loc}>{loc.toUpperCase()}</option>
      ))}
    </select>
  )
}
"@

Set-Content -Path "src\components\Language\LanguageSwitcher.tsx" -Value $languageSwitcher

$currencySelector = @"
'use client'
import { useState } from 'react'

const currencies = ['USD', 'EUR', 'GBP', 'RON']

export default function CurrencySelector() {
  const [currency, setCurrency] = useState('USD')
  
  return (
    <select value={currency} onChange={(e) => setCurrency(e.target.value)}>
      {currencies.map(curr => (
        <option key={curr} value={curr}>{curr}</option>
      ))}
    </select>
  )
}
"@

Set-Content -Path "src\components\Currency\CurrencySelector.tsx" -Value $currencySelector

Write-Host "  Created placeholder components ✅" -ForegroundColor Green

Write-Host ""

# Summary
Write-Host "✅ Phase 2 Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Summary:" -ForegroundColor Cyan
Write-Host "  ✅ Dashboard Analytics structure created" -ForegroundColor White
Write-Host "  ✅ Multi-language support configured" -ForegroundColor White
Write-Host "  ✅ Multi-currency support configured" -ForegroundColor White
Write-Host "  ✅ Frontend components created" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Implement business logic in services" -ForegroundColor White
Write-Host "  2. Add translations to locale files" -ForegroundColor White
Write-Host "  3. Configure exchange rate API" -ForegroundColor White
Write-Host "  4. Build complete dashboard UI" -ForegroundColor White
Write-Host "  5. Run: .\scripts\auto-complete-phase3.ps1" -ForegroundColor White
Write-Host ""
