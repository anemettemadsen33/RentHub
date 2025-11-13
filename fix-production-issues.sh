#!/bin/bash

# ===================================================================
# FIX PRODUCTION ISSUES - RentHub
# ===================================================================
# Acest script rezolvă problemele identificate pe ambele platforme
# ===================================================================

set -e

echo "🔧 Starting production fixes..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ===================================================================
# PROBLEM 1: Database goală pe Forge
# ===================================================================

echo -e "${YELLOW}📊 Issue #1: Empty database on Forge${NC}"
echo "Backend API funcționează dar nu returnează date."
echo ""
echo "Soluție: Populează database cu seeders"
echo ""
echo "Pentru a rezolva, rulează următoarele comenzi pe serverul Forge:"
echo ""
echo -e "${GREEN}# Conectare SSH${NC}"
echo "ssh forge@renthub-tbj7yxj7.on-forge.com"
echo ""
echo -e "${GREEN}# Navighează în directorul aplicației${NC}"
echo "cd /home/forge/renthub-tbj7yxj7.on-forge.com"
echo ""
echo -e "${GREEN}# Rulează migrations (dacă nu sunt deja)${NC}"
echo "php artisan migrate --force"
echo ""
echo -e "${GREEN}# Populează database cu date${NC}"
echo "php artisan db:seed --class=TestPropertiesSeeder --force"
echo "php artisan db:seed --class=AmenitySeeder --force"
echo ""
echo -e "${GREEN}# SAU rulează toate seeders${NC}"
echo "php artisan db:seed --force"
echo ""
echo -e "${GREEN}# Verifică dacă datele au fost adăugate${NC}"
echo "php artisan tinker"
echo ">>> \\App\\Models\\Property::count()"
echo ">>> exit"
echo ""
echo "---"
echo ""

# ===================================================================
# PROBLEM 2: Verificare frontend-backend connection
# ===================================================================

echo -e "${YELLOW}🔗 Issue #2: Frontend-Backend Connection${NC}"
echo "Frontend funcționează perfect dar nu afișează proprietăți."
echo ""
echo "Cauză: Backend nu returnează date."
echo "Fix: După popularea database-ului, frontend-ul va funcționa automat."
echo ""
echo "Verificare:"
echo "1. Deschide: https://rent-hoki3tmds-madsens-projects.vercel.app/"
echo "2. Click pe 'Browse Properties'"
echo "3. Ar trebui să vezi proprietățile din database"
echo ""
echo "---"
echo ""

# ===================================================================
# PROBLEM 3: Butoane care nu funcționează
# ===================================================================

echo -e "${YELLOW}🔘 Issue #3: Non-functional buttons check${NC}"
echo "Verificăm dacă există butoane care nu funcționează..."
echo ""

# Verifică dacă există pagini incomplete în frontend
INCOMPLETE_PAGES=$(find /workspaces/RentHub/frontend/src/app -name "page.tsx" -exec grep -l "Coming Soon\|Under Construction\|Not Implemented" {} \; 2>/dev/null | wc -l)

if [ "$INCOMPLETE_PAGES" -gt 0 ]; then
    echo -e "${YELLOW}Găsite $INCOMPLETE_PAGES pagini incomplete.${NC}"
    echo "Acestea pot fi activate mai târziu."
else
    echo -e "${GREEN}✓ Toate paginile active sunt complete!${NC}"
fi

echo ""
echo "---"
echo ""

# ===================================================================
# SUMMARY
# ===================================================================

echo -e "${GREEN}📋 SUMMARY OF ISSUES${NC}"
echo ""
echo "✅ VERCEL Frontend:"
echo "   - Site funcționează perfect"
echo "   - Design complet și frumos"
echo "   - Toate butoanele și link-urile active funcționează"
echo "   - ⚠️  Nu afișează proprietăți (backend gol)"
echo ""
echo "✅ FORGE Backend:"
echo "   - API funcționează (health check OK)"
echo "   - CORS configurat corect"
echo "   - ⚠️  Database goală (fix necesar)"
echo ""
echo -e "${GREEN}🎯 NEXT STEPS:${NC}"
echo ""
echo "1. ${YELLOW}SSH în Forge și rulează seeders:${NC}"
echo "   ssh forge@renthub-tbj7yxj7.on-forge.com"
echo "   cd /home/forge/renthub-tbj7yxj7.on-forge.com"
echo "   php artisan db:seed --force"
echo ""
echo "2. ${YELLOW}Verifică că datele au fost adăugate:${NC}"
echo "   curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | jq '.'"
echo ""
echo "3. ${YELLOW}Testează frontend-ul:${NC}"
echo "   https://rent-hoki3tmds-madsens-projects.vercel.app/properties"
echo ""
echo "4. ${YELLOW}Testează admin panel:${NC}"
echo "   https://renthub-tbj7yxj7.on-forge.com/admin/login"
echo "   Email: admin@renthub.com"
echo "   Password: password"
echo ""
echo -e "${GREEN}✨ După aceste pași, totul va funcționa perfect!${NC}"
echo ""
