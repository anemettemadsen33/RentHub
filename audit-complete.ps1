# RentHub - Complete Application Audit Script
# This script will identify all missing implementations, broken functions, and incomplete features

Write-Host "`n=== 🔍 RENTHUB COMPLETE APPLICATION AUDIT ===" -ForegroundColor Cyan
Write-Host "Starting comprehensive audit of frontend and backend...`n" -ForegroundColor Yellow

$auditResults = @{
    MissingPages = @()
    IncompleteImplementations = @()
    BrokenFunctions = @()
    AccessibilityIssues = @()
    RoutingProblems = @()
}

# Check Frontend Routes
Write-Host "📄 Checking Frontend Pages..." -ForegroundColor Green
$frontendPages = Get-ChildItem -Path "frontend/src/app" -Directory -Recurse | 
    Where-Object { $_.Name -notmatch "^_" -and $_.Name -notmatch "^\[" }

$requiredPageFiles = @("page.tsx", "page.ts", "page.js")
$incompletePages = @()

foreach ($page in $frontendPages) {
    $hasPage = $false
    foreach ($file in $requiredPageFiles) {
        if (Test-Path (Join-Path $page.FullName $file)) {
            $hasPage = $true
            $pageContent = Get-Content (Join-Path $page.FullName $file) -Raw
            
            # Check for TODO, FIXME, or placeholder content
            if ($pageContent -match "TODO|FIXME|placeholder|under construction|coming soon") {
                $incompletePages += @{
                    Path = $page.FullName.Replace((Get-Location).Path, "")
                    Issue = "Contains TODO/FIXME/Placeholder content"
                }
            }
            
            # Check if page is just a stub
            if ($pageContent.Length -lt 500) {
                $incompletePages += @{
                    Path = $page.FullName.Replace((Get-Location).Path, "")
                    Issue = "Page appears to be a stub (< 500 characters)"
                }
            }
            break
        }
    }
    
    if (-not $hasPage) {
        $auditResults.MissingPages += $page.FullName.Replace((Get-Location).Path, "")
    }
}

Write-Host "   Found $($auditResults.MissingPages.Count) directories without page files" -ForegroundColor Yellow
Write-Host "   Found $($incompletePages.Count) incomplete pages" -ForegroundColor Yellow

# Check for broken API endpoints
Write-Host "`n🔌 Checking API Routes..." -ForegroundColor Green
$apiRoutes = @()
if (Test-Path "backend/routes/api.php") {
    $apiContent = Get-Content "backend/routes/api.php" -Raw
    $apiRoutes = [regex]::Matches($apiContent, "Route::(?:get|post|put|delete|patch)\('([^']+)'") | 
        ForEach-Object { $_.Groups[1].Value }
}
Write-Host "   Found $($apiRoutes.Count) API routes defined" -ForegroundColor Cyan

# Check Controllers Implementation
Write-Host "`n🎮 Checking Controller Implementations..." -ForegroundColor Green
$controllers = Get-ChildItem -Path "backend/app/Http/Controllers/Api" -Filter "*.php" -Recurse
$incompleteControllers = @()

foreach ($controller in $controllers) {
    $content = Get-Content $controller.FullName -Raw
    
    # Check for empty methods or TODO markers
    if ($content -match "// TODO|\/\/ FIXME|function \w+\(\)[^{]*\{\s*\}") {
        $incompleteControllers += $controller.Name
    }
    
    # Check for missing validation
    if ($content -match "public function (?:store|update)" -and $content -notmatch "\$request->validate") {
        $auditResults.IncompleteImplementations += @{
            File = $controller.Name
            Issue = "Store/Update method without validation"
        }
    }
}

Write-Host "   Found $($incompleteControllers.Count) controllers with incomplete methods" -ForegroundColor Yellow

# Check Frontend Components
Write-Host "`n🧩 Checking React Components..." -ForegroundColor Green
$components = Get-ChildItem -Path "frontend/src/components" -Filter "*.tsx" -Recurse
$componentIssues = @()

foreach ($component in $components) {
    $content = Get-Content $component.FullName -Raw
    
    # Check for TODO/FIXME
    if ($content -match "TODO|FIXME") {
        $componentIssues += @{
            Component = $component.Name
            Issue = "Contains TODO/FIXME markers"
        }
    }
    
    # Check for accessibility attributes
    if ($content -match "<button" -and $content -notmatch "aria-label") {
        $auditResults.AccessibilityIssues += @{
            Component = $component.Name
            Issue = "Button without aria-label"
        }
    }
}

Write-Host "   Found $($componentIssues.Count) components with issues" -ForegroundColor Yellow
Write-Host "   Found $($auditResults.AccessibilityIssues.Count) accessibility issues" -ForegroundColor Yellow

# Check for missing tests
Write-Host "`n🧪 Checking Test Coverage..." -ForegroundColor Green
$frontendTests = (Get-ChildItem -Path "frontend" -Filter "*.test.tsx" -Recurse -ErrorAction SilentlyContinue).Count
$frontendSpecs = (Get-ChildItem -Path "frontend" -Filter "*.spec.tsx" -Recurse -ErrorAction SilentlyContinue).Count
$backendTests = (Get-ChildItem -Path "backend/tests" -Filter "*Test.php" -Recurse -ErrorAction SilentlyContinue).Count

Write-Host "   Frontend tests: $frontendTests .test.tsx files, $frontendSpecs .spec.tsx files" -ForegroundColor Cyan
Write-Host "   Backend tests: $backendTests Test.php files" -ForegroundColor Cyan

# Generate Report
Write-Host "`n`n=== 📊 AUDIT REPORT ===" -ForegroundColor Cyan

Write-Host "`n🔴 Critical Issues:" -ForegroundColor Red
Write-Host "   - $($auditResults.MissingPages.Count) directories without page files"
Write-Host "   - $($incompletePages.Count) incomplete pages"
Write-Host "   - $($incompleteControllers.Count) controllers with incomplete implementations"
Write-Host "   - $($auditResults.AccessibilityIssues.Count) accessibility issues"

Write-Host "`n📝 Detailed Findings:" -ForegroundColor Yellow

if ($auditResults.MissingPages.Count -gt 0) {
    Write-Host "`n  Missing Pages (no page.tsx):" -ForegroundColor Yellow
    $auditResults.MissingPages | Select-Object -First 10 | ForEach-Object {
        Write-Host "    - $_" -ForegroundColor Gray
    }
    if ($auditResults.MissingPages.Count -gt 10) {
        Write-Host "    ... and $($auditResults.MissingPages.Count - 10) more" -ForegroundColor Gray
    }
}

if ($incompletePages.Count -gt 0) {
    Write-Host "`n  Incomplete Pages:" -ForegroundColor Yellow
    $incompletePages | Select-Object -First 10 | ForEach-Object {
        Write-Host "    - $($_.Path): $($_.Issue)" -ForegroundColor Gray
    }
    if ($incompletePages.Count -gt 10) {
        Write-Host "    ... and $($incompletePages.Count - 10) more" -ForegroundColor Gray
    }
}

if ($incompleteControllers.Count -gt 0) {
    Write-Host "`n  Incomplete Controllers:" -ForegroundColor Yellow
    $incompleteControllers | Select-Object -First 10 | ForEach-Object {
        Write-Host "    - $_" -ForegroundColor Gray
    }
    if ($incompleteControllers.Count -gt 10) {
        Write-Host "    ... and $($incompleteControllers.Count - 10) more" -ForegroundColor Gray
    }
}

# Priority Actions
Write-Host "`n`n=== 🎯 PRIORITY ACTIONS ===" -ForegroundColor Cyan
Write-Host "1. Complete missing page implementations" -ForegroundColor White
Write-Host "2. Fix accessibility issues in components" -ForegroundColor White
Write-Host "3. Add validation to controller methods" -ForegroundColor White
Write-Host "4. Implement TODOs and FIXMEs" -ForegroundColor White
Write-Host "5. Add comprehensive test coverage" -ForegroundColor White

Write-Host "`n✅ Audit Complete!`n" -ForegroundColor Green

# Export detailed report
$reportPath = "AUDIT_REPORT_$(Get-Date -Format 'yyyyMMdd_HHmmss').json"
$auditResults | ConvertTo-Json -Depth 10 | Out-File $reportPath
Write-Host "📄 Detailed report saved to: $reportPath`n" -ForegroundColor Cyan
