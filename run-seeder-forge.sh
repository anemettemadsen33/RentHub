#!/bin/bash
# Quick guide for running seeder on Forge

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                                                            ║"
echo "║     🚀 RentHub - Running Test Data Seeder on Forge        ║"
echo "║                                                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel root directory"
    echo "📁 Current directory: $(pwd)"
    echo ""
    echo "💡 Run this instead:"
    echo "   cd ~/renthub-tbj7yxj7.on-forge.com"
    echo "   php artisan db:seed --class=TestPropertiesSeeder"
    exit 1
fi

echo "📂 Current directory: $(pwd)"
echo "✅ Found artisan file"
echo ""

echo "🌱 Running TestPropertiesSeeder..."
php artisan db:seed --class=TestPropertiesSeeder

echo ""
echo "🔍 Verifying properties..."
php artisan tinker --execute="echo 'Total properties: ' . App\Models\Property::count() . PHP_EOL;"

echo ""
echo "✅ DONE! Test your site now:"
echo "   • https://rent-hub-beta.vercel.app/properties"
echo "   • https://rent-hub-beta.vercel.app/properties/1"
echo ""
