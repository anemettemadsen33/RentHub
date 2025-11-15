#!/bin/bash

# Quick Database Setup for Forge Production
# Run this manually to populate the database

echo "🚀 Setting up RentHub Production Database..."
echo ""

cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Check current property count
PROPERTY_COUNT=$(php artisan tinker --execute="echo App\Models\Property::count();")
echo "Current properties in database: $PROPERTY_COUNT"

if [ "$PROPERTY_COUNT" -eq "0" ]; then
    echo ""
    echo "💾 Database is empty. Running ProductionSeeder..."
    php artisan db:seed --class=ProductionSeeder --force
    echo "✅ Seeding complete!"
else
    echo ""
    echo "⚠️  Database already has $PROPERTY_COUNT properties."
    echo "Do you want to reseed? (This will add more data, not replace)"
    read -p "Continue? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed --class=ProductionSeeder --force
        echo "✅ Additional data seeded!"
    fi
fi

# Clear all caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Final check
echo ""
echo "📈 Final Statistics:"
php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count();
echo PHP_EOL;
echo 'Properties: ' . App\Models\Property::count();
echo PHP_EOL;
echo 'Bookings: ' . App\Models\Booking::count();
"

echo ""
echo "✅ Setup complete! Your production site is ready."
echo "🌐 Visit: https://renthub-tbj7yxj7.on-forge.com"
