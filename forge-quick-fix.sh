#!/bin/bash

# ===================================================================
# QUICK FIX - RentHub Production Database
# ===================================================================
# Rulează acest script pentru a popula automat database-ul
# ===================================================================

echo "🚀 RentHub Quick Fix - Database Seeding"
echo "========================================"
echo ""

# Verifică dacă suntem pe server Forge
if [[ ! -d "/home/forge/renthub-tbj7yxj7.on-forge.com" ]]; then
    echo "❌ Acest script trebuie rulat pe serverul Forge!"
    echo ""
    echo "Rulează următoarea comandă pentru a te conecta:"
    echo "  ssh forge@renthub-tbj7yxj7.on-forge.com"
    echo ""
    echo "Apoi rulează din nou acest script."
    exit 1
fi

# Navighează în directorul aplicației
cd /home/forge/renthub-tbj7yxj7.on-forge.com || exit 1

echo "📂 Working directory: $(pwd)"
echo ""

# Verifică conexiunea la database
echo "🔍 Verificare conexiune database..."
if ! php artisan db:show > /dev/null 2>&1; then
    echo "❌ Nu pot conecta la database!"
    echo "Verifică configurația .env"
    exit 1
fi
echo "✅ Database connection OK"
echo ""

# Rulează migrations (în caz că lipsesc)
echo "🔄 Rulare migrations..."
php artisan migrate --force
echo "✅ Migrations complete"
echo ""

# Rulează seeders
echo "🌱 Populare database cu date..."
echo ""

echo "  → Rulare RolePermissionSeeder..."
php artisan db:seed --class=RolePermissionSeeder --force

echo "  → Rulare AdminSeeder..."
php artisan db:seed --class=AdminSeeder --force

echo "  → Rulare AmenitySeeder..."
php artisan db:seed --class=AmenitySeeder --force

echo "  → Rulare TestPropertiesSeeder..."
php artisan db:seed --class=TestPropertiesSeeder --force

echo ""
echo "✅ Seeders complete!"
echo ""

# Verifică rezultatele
echo "📊 Verificare date adăugate..."
echo ""

PROPERTIES_COUNT=$(php artisan tinker --execute="echo \App\Models\Property::count();")
USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();")
AMENITIES_COUNT=$(php artisan tinker --execute="echo \App\Models\Amenity::count();")

echo "  Properties: $PROPERTIES_COUNT"
echo "  Users: $USERS_COUNT"
echo "  Amenities: $AMENITIES_COUNT"
echo ""

if [ "$PROPERTIES_COUNT" -gt 0 ]; then
    echo "✅ SUCCESS! Database populat cu succes!"
    echo ""
    echo "🎯 Next Steps:"
    echo ""
    echo "1. Test API:"
    echo "   curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | jq '.'"
    echo ""
    echo "2. Test Frontend:"
    echo "   https://rent-hoki3tmds-madsens-projects.vercel.app/properties"
    echo ""
    echo "3. Login Admin Panel:"
    echo "   https://renthub-tbj7yxj7.on-forge.com/admin/login"
    echo "   Email: admin@renthub.com"
    echo "   Password: password"
    echo ""
else
    echo "⚠️  WARNING: Properties count is still 0!"
    echo "Check logs for errors:"
    echo "  tail -50 storage/logs/laravel.log"
fi

# Clear cache
echo "🧹 Curățare cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize
echo "✅ Cache cleared"
echo ""

echo "✨ Done! Aplicația este gata de folosit!"
