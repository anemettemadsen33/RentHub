#!/bin/bash

echo "🔧 Diagnosticare și Rezolvare Probleme Vercel"
echo "============================================="

# Verificare structură frontend
echo "📁 Verificare structură frontend..."
cd ../frontend || exit 1

echo "📊 Verificare package.json:"
cat package.json | grep -E '"scripts"|"dependencies"|"devDependencies"' -A 5

echo ""
echo "🔍 Verificare fișiere lipsă sau incomplete:"

# Verificare pagini principale
PAGES=("src/pages/Home.tsx" "src/pages/Login.tsx" "src/pages/Register.tsx" "src/pages/Dashboard.tsx" "src/pages/Properties.tsx")
for page in "${PAGES[@]}"; do
    if [ -f "$page" ]; then
        echo "✅ $page există"
        # Verificare dimensiune și conținut
        SIZE=$(wc -c < "$page")
        if [ $SIZE -lt 100 ]; then
            echo "⚠️  $page pare incomplet (dimensiune: $SIZE bytes)"
        fi
    else
        echo "❌ $page lipsește"
    fi
done

echo ""
echo "🔍 Verificare componente esențiale:"
COMPONENTS=("src/components/Header.tsx" "src/components/Footer.tsx" "src/components/PropertyCard.tsx" "src/components/BookingForm.tsx")
for component in "${COMPONENTS[@]}"; do
    if [ -f "$component" ]; then
        echo "✅ $component există"
        SIZE=$(wc -c < "$component")
        if [ $SIZE -lt 50 ]; then
            echo "⚠️  $component pare incomplet (dimensiune: $SIZE bytes)"
        fi
    else
        echo "❌ $component lipsește"
    fi
done

echo ""
echo "🔍 Verificare dependențe:"
npm list --depth=0 2>/dev/null | grep -E "(react|react-dom|react-router|axios|tailwind)" || echo "⚠️  Dependențe lipsă sau erori"

echo ""
echo "🔍 Verificare build:"
if [ -d "dist" ]; then
    echo "✅ Folder dist există"
    echo "📊 Conținut dist:"
    ls -la dist/
else
    echo "❌ Folder dist lipsește - build nereușit"
fi

echo ""
echo "🧪 Testare build local:"
npm run build 2>&1 | tail -20

echo ""
echo "✅ Diagnostic complet! Verifică rezultatele de mai sus."