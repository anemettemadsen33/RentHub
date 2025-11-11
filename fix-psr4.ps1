# Fix PSR-4 Naming Inconsistencies
# Renames API folder to Api to match PSR-4 standard

Write-Host "🔧 Fixing PSR-4 Naming Inconsistencies..." -ForegroundColor Cyan

Set-Location backend/app/Http/Controllers

# Check if API folder exists
if (Test-Path "API") {
    Write-Host "`n📁 Found API folder, renaming to Api..." -ForegroundColor Yellow
    
    # Rename folder
    Rename-Item -Path "API" -NewName "Api" -Force
    
    Write-Host "✅ Renamed: Controllers/API → Controllers/Api" -ForegroundColor Green
    
    # Update namespace in all files
    $files = Get-ChildItem -Path "Api" -Filter "*.php" -Recurse
    
    Write-Host "`n📝 Updating namespaces in $($files.Count) files..." -ForegroundColor Yellow
    
    foreach ($file in $files) {
        $content = Get-Content $file.FullName -Raw
        $modified = $false
        
        # Update namespace
        if ($content -match 'namespace App\\Http\\Controllers\\API') {
            $content = $content -replace 'namespace App\\Http\\Controllers\\API', 'namespace App\\Http\\Controllers\\Api'
            $modified = $true
        }
        
        # Update use statements
        if ($content -match 'use App\\Http\\Controllers\\API\\') {
            $content = $content -replace 'use App\\Http\\Controllers\\API\\', 'use App\\Http\\Controllers\\Api\\'
            $modified = $true
        }
        
        if ($modified) {
            Set-Content $file.FullName $content
            Write-Host "  ✅ Updated: $($file.Name)" -ForegroundColor Green
        }
    }
    
    Write-Host "`n✅ PSR-4 fixes complete!" -ForegroundColor Green
} else {
    Write-Host "✅ No PSR-4 issues found - API folder doesn't exist or already named correctly" -ForegroundColor Green
}

Set-Location ../../..
