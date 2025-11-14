# RentHub E2E Test Runner (PowerShell)
# Quick commands to run tests

Write-Host "🧪 RentHub E2E Test Suite" -ForegroundColor Cyan
Write-Host "==========================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Select an option:"
Write-Host "1. Run ALL tests on ALL browsers (Complete)"
Write-Host "2. Run Chrome only"
Write-Host "3. Run Firefox only"
Write-Host "4. Run Safari only"
Write-Host "5. Run Edge only"
Write-Host "6. Run Mobile tests (Chrome + Safari)"
Write-Host "7. Run Tablet tests (iPad + Android)"
Write-Host "8. Run with UI mode (Interactive)"
Write-Host "9. Run in headed mode (See browser)"
Write-Host "10. View test report"
Write-Host "11. Generate new tests (Codegen)"
Write-Host "0. Exit"
Write-Host ""

$choice = Read-Host "Enter your choice"

switch ($choice) {
    1 {
        Write-Host "🚀 Running ALL tests on ALL browsers..." -ForegroundColor Green
        npm run e2e:all-browsers
    }
    2 {
        Write-Host "🌐 Running Chrome tests..." -ForegroundColor Green
        npm run e2e:chrome
    }
    3 {
        Write-Host "🦊 Running Firefox tests..." -ForegroundColor Green
        npm run e2e:firefox
    }
    4 {
        Write-Host "🧭 Running Safari tests..." -ForegroundColor Green
        npm run e2e:safari
    }
    5 {
        Write-Host "📘 Running Edge tests..." -ForegroundColor Green
        npm run e2e:edge
    }
    6 {
        Write-Host "📱 Running Mobile tests..." -ForegroundColor Green
        npm run e2e:mobile
    }
    7 {
        Write-Host "📱 Running Tablet tests..." -ForegroundColor Green
        npm run e2e:tablet
    }
    8 {
        Write-Host "🎨 Opening UI mode..." -ForegroundColor Green
        npm run e2e:ui
    }
    9 {
        Write-Host "👀 Running in headed mode..." -ForegroundColor Green
        npm run e2e:headed
    }
    10 {
        Write-Host "📊 Opening test report..." -ForegroundColor Green
        npm run e2e:report
    }
    11 {
        Write-Host "🎬 Starting Codegen..." -ForegroundColor Green
        npm run e2e:codegen
    }
    0 {
        Write-Host "👋 Goodbye!" -ForegroundColor Yellow
        exit 0
    }
    default {
        Write-Host "❌ Invalid choice" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "✅ Done!" -ForegroundColor Green
