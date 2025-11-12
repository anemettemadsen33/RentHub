# Script to find and disable all pages with next-intl dependencies

Write-Host "🔍 Scanning for pages with next-intl dependencies..." -ForegroundColor Cyan

$problemPages = @()
$appDir = "C:\laragon\www\RentHub\frontend\src\app"

# Scan all page.tsx files
Get-ChildItem -Path $appDir -Recurse -Filter "page.tsx" | ForEach-Object {
    $relativePath = $_.DirectoryName -replace [regex]::Escape($appDir), ""
    
    # Skip already disabled pages
    if ($_.DirectoryName -notlike "*_*disabled*") {
        $content = Get-Content $_.FullName -Raw
        
        # Check for next-intl usage
        if ($content -match "useTranslations|getTranslations|NextIntlClientProvider") {
            $problemPages += @{
                Path = $_.DirectoryName
                RelativePath = $relativePath
                File = $_.Name
            }
            Write-Host "❌ Found problem: $relativePath" -ForegroundColor Red
        }
    }
}

if ($problemPages.Count -eq 0) {
    Write-Host "✅ No problematic pages found!" -ForegroundColor Green
    exit 0
}

Write-Host "`n📋 Found $($problemPages.Count) pages with next-intl:" -ForegroundColor Yellow
$problemPages | ForEach-Object {
    Write-Host "  - $($_.RelativePath)" -ForegroundColor Yellow
}

# Ask for confirmation
$response = Read-Host "`nDisable all these pages? (y/n)"

if ($response -eq 'y') {
    foreach ($page in $problemPages) {
        $dirName = Split-Path $page.Path -Leaf
        $parentDir = Split-Path $page.Path -Parent
        $newName = "_$dirName.disabled"
        $newPath = Join-Path $parentDir $newName
        
        if (Test-Path $newPath) {
            Write-Host "⚠️ Already disabled: $dirName" -ForegroundColor Yellow
        } else {
            Move-Item -Path $page.Path -Destination $newPath -Force
            Write-Host "✅ Disabled: $dirName → $newName" -ForegroundColor Green
        }
    }
    
    Write-Host "`n✅ All problematic pages disabled!" -ForegroundColor Green
    Write-Host "Now run: git add -A && git commit -m 'fix: disable remaining next-intl pages' && git push" -ForegroundColor Cyan
} else {
    Write-Host "❌ Cancelled" -ForegroundColor Red
}
