#!/bin/bash
# RentHub E2E Test Runner
# Quick commands to run tests

echo "🧪 RentHub E2E Test Suite"
echo "=========================="
echo ""
echo "Select an option:"
echo "1. Run ALL tests on ALL browsers (Complete)"
echo "2. Run Chrome only"
echo "3. Run Firefox only"
echo "4. Run Safari only"
echo "5. Run Edge only"
echo "6. Run Mobile tests (Chrome + Safari)"
echo "7. Run Tablet tests (iPad + Android)"
echo "8. Run with UI mode (Interactive)"
echo "9. Run in headed mode (See browser)"
echo "10. View test report"
echo "11. Generate new tests (Codegen)"
echo "0. Exit"
echo ""
read -p "Enter your choice: " choice

case $choice in
    1)
        echo "🚀 Running ALL tests on ALL browsers..."
        npm run e2e:all-browsers
        ;;
    2)
        echo "🌐 Running Chrome tests..."
        npm run e2e:chrome
        ;;
    3)
        echo "🦊 Running Firefox tests..."
        npm run e2e:firefox
        ;;
    4)
        echo "🧭 Running Safari tests..."
        npm run e2e:safari
        ;;
    5)
        echo "📘 Running Edge tests..."
        npm run e2e:edge
        ;;
    6)
        echo "📱 Running Mobile tests..."
        npm run e2e:mobile
        ;;
    7)
        echo "📱 Running Tablet tests..."
        npm run e2e:tablet
        ;;
    8)
        echo "🎨 Opening UI mode..."
        npm run e2e:ui
        ;;
    9)
        echo "👀 Running in headed mode..."
        npm run e2e:headed
        ;;
    10)
        echo "📊 Opening test report..."
        npm run e2e:report
        ;;
    11)
        echo "🎬 Starting Codegen..."
        npm run e2e:codegen
        ;;
    0)
        echo "👋 Goodbye!"
        exit 0
        ;;
    *)
        echo "❌ Invalid choice"
        exit 1
        ;;
esac

echo ""
echo "✅ Done!"
