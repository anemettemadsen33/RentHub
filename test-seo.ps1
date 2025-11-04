# SEO Implementation Test Script
# Tests all SEO features and generates a report

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "   RentHub SEO Implementation Test   " -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$frontendUrl = "http://localhost:3000"
$backendUrl = "http://localhost:8000"
$passed = 0
$failed = 0
$warnings = 0

function Test-Endpoint {
    param(
        [string]$Url,
        [string]$Description
    )
    
    try {
        $response = Invoke-WebRequest -Uri $Url -Method Get -ErrorAction Stop
        if ($response.StatusCode -eq 200) {
            Write-Host "[PASS] " -ForegroundColor Green -NoNewline
            Write-Host "$Description"
            $script:passed++
            return $true
        }
    } catch {
        Write-Host "[FAIL] " -ForegroundColor Red -NoNewline
        Write-Host "$Description - Error: $($_.Exception.Message)"
        $script:failed++
        return $false
    }
}

function Test-FileExists {
    param(
        [string]$Path,
        [string]$Description
    )
    
    if (Test-Path $Path) {
        Write-Host "[PASS] " -ForegroundColor Green -NoNewline
        Write-Host "$Description"
        $script:passed++
        return $true
    } else {
        Write-Host "[FAIL] " -ForegroundColor Red -NoNewline
        Write-Host "$Description - File not found"
        $script:failed++
        return $false
    }
}

function Test-ContentContains {
    param(
        [string]$Url,
        [string]$SearchString,
        [string]$Description
    )
    
    try {
        $response = Invoke-WebRequest -Uri $Url -Method Get -ErrorAction Stop
        if ($response.Content -match $SearchString) {
            Write-Host "[PASS] " -ForegroundColor Green -NoNewline
            Write-Host "$Description"
            $script:passed++
            return $true
        } else {
            Write-Host "[WARN] " -ForegroundColor Yellow -NoNewline
            Write-Host "$Description - Content not found"
            $script:warnings++
            return $false
        }
    } catch {
        Write-Host "[FAIL] " -ForegroundColor Red -NoNewline
        Write-Host "$Description - Error: $($_.Exception.Message)"
        $script:failed++
        return $false
    }
}

# Test 1: Frontend Files
Write-Host "`n1. Testing Frontend SEO Files..." -ForegroundColor Yellow
Write-Host "================================" -ForegroundColor Yellow

Test-FileExists -Path ".\frontend\src\lib\seo.ts" -Description "SEO utility library exists"
Test-FileExists -Path ".\frontend\src\lib\schema.ts" -Description "Schema markup library exists"
Test-FileExists -Path ".\frontend\src\lib\canonical.ts" -Description "Canonical URL library exists"
Test-FileExists -Path ".\frontend\src\app\sitemap.ts" -Description "Sitemap generator exists"
Test-FileExists -Path ".\frontend\src\app\robots.ts" -Description "Robots.txt generator exists"
Test-FileExists -Path ".\frontend\src\components\seo\JsonLd.tsx" -Description "JsonLd component exists"
Test-FileExists -Path ".\frontend\src\components\seo\BreadcrumbSEO.tsx" -Description "Breadcrumb component exists"

# Test 2: Backend Files
Write-Host "`n2. Testing Backend SEO Files..." -ForegroundColor Yellow
Write-Host "================================" -ForegroundColor Yellow

Test-FileExists -Path ".\backend\app\Http\Controllers\Api\SeoController.php" -Description "SEO controller exists"

# Test 3: Frontend Endpoints
Write-Host "`n3. Testing Frontend SEO Endpoints..." -ForegroundColor Yellow
Write-Host "=====================================" -ForegroundColor Yellow

Write-Host "`nNote: Make sure frontend is running on $frontendUrl" -ForegroundColor Cyan

Test-Endpoint -Url "$frontendUrl/sitemap.xml" -Description "Sitemap is accessible"
Test-Endpoint -Url "$frontendUrl/robots.txt" -Description "Robots.txt is accessible"

# Test 4: Sitemap Content
Write-Host "`n4. Testing Sitemap Content..." -ForegroundColor Yellow
Write-Host "==============================" -ForegroundColor Yellow

Test-ContentContains -Url "$frontendUrl/sitemap.xml" -SearchString "<urlset" -Description "Sitemap has valid XML structure"
Test-ContentContains -Url "$frontendUrl/sitemap.xml" -SearchString "<loc>" -Description "Sitemap contains URLs"

# Test 5: Robots.txt Content
Write-Host "`n5. Testing Robots.txt Content..." -ForegroundColor Yellow
Write-Host "=================================" -ForegroundColor Yellow

Test-ContentContains -Url "$frontendUrl/robots.txt" -SearchString "User-agent:" -Description "Robots.txt has user agent rules"
Test-ContentContains -Url "$frontendUrl/robots.txt" -SearchString "Sitemap:" -Description "Robots.txt references sitemap"

# Test 6: Backend API Endpoints
Write-Host "`n6. Testing Backend SEO API..." -ForegroundColor Yellow
Write-Host "==============================" -ForegroundColor Yellow

Write-Host "`nNote: Make sure backend is running on $backendUrl" -ForegroundColor Cyan

Test-Endpoint -Url "$backendUrl/api/v1/seo/locations" -Description "SEO locations endpoint"
Test-Endpoint -Url "$backendUrl/api/v1/seo/property-urls" -Description "SEO property URLs endpoint"
Test-Endpoint -Url "$backendUrl/api/v1/seo/popular-searches" -Description "SEO popular searches endpoint"
Test-Endpoint -Url "$backendUrl/api/v1/seo/organization" -Description "SEO organization endpoint"

# Test 7: Documentation
Write-Host "`n7. Testing Documentation..." -ForegroundColor Yellow
Write-Host "===========================" -ForegroundColor Yellow

Test-FileExists -Path ".\SEO_IMPLEMENTATION_GUIDE.md" -Description "Implementation guide exists"
Test-FileExists -Path ".\SEO_QUICK_REFERENCE.md" -Description "Quick reference exists"

# Test 8: Configuration
Write-Host "`n8. Testing Configuration..." -ForegroundColor Yellow
Write-Host "===========================" -ForegroundColor Yellow

$envExample = Get-Content ".\frontend\.env.example" -Raw
if ($envExample -match "NEXT_PUBLIC_SITE_URL") {
    Write-Host "[PASS] " -ForegroundColor Green -NoNewline
    Write-Host "NEXT_PUBLIC_SITE_URL in .env.example"
    $passed++
} else {
    Write-Host "[WARN] " -ForegroundColor Yellow -NoNewline
    Write-Host "NEXT_PUBLIC_SITE_URL not in .env.example"
    $warnings++
}

if ($envExample -match "GOOGLE_VERIFICATION") {
    Write-Host "[PASS] " -ForegroundColor Green -NoNewline
    Write-Host "Google verification in .env.example"
    $passed++
} else {
    Write-Host "[WARN] " -ForegroundColor Yellow -NoNewline
    Write-Host "Google verification not in .env.example"
    $warnings++
}

# Summary
Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "           TEST SUMMARY               " -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$total = $passed + $failed + $warnings
$passRate = if ($total -gt 0) { [math]::Round(($passed / $total) * 100, 2) } else { 0 }

Write-Host "`nTotal Tests: $total" -ForegroundColor White
Write-Host "Passed:      " -NoNewline -ForegroundColor White
Write-Host $passed -ForegroundColor Green
Write-Host "Failed:      " -NoNewline -ForegroundColor White
Write-Host $failed -ForegroundColor Red
Write-Host "Warnings:    " -NoNewline -ForegroundColor White
Write-Host $warnings -ForegroundColor Yellow
Write-Host "Pass Rate:   " -NoNewline -ForegroundColor White
Write-Host "$passRate%" -ForegroundColor $(if ($passRate -ge 80) { "Green" } elseif ($passRate -ge 60) { "Yellow" } else { "Red" })

Write-Host "`n=====================================" -ForegroundColor Cyan

if ($failed -eq 0 -and $warnings -eq 0) {
    Write-Host "`n✓ All tests passed successfully!" -ForegroundColor Green
    Write-Host "SEO implementation is complete and working." -ForegroundColor Green
} elseif ($failed -eq 0) {
    Write-Host "`n✓ All critical tests passed!" -ForegroundColor Green
    Write-Host "⚠ Some optional features have warnings." -ForegroundColor Yellow
} else {
    Write-Host "`n✗ Some tests failed." -ForegroundColor Red
    Write-Host "Please check the errors above and fix them." -ForegroundColor Red
}

Write-Host "`nFor more information, see:" -ForegroundColor Cyan
Write-Host "  - SEO_IMPLEMENTATION_GUIDE.md" -ForegroundColor White
Write-Host "  - SEO_QUICK_REFERENCE.md" -ForegroundColor White
Write-Host ""
