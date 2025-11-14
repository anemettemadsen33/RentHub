#!/bin/bash
# Manual Git Setup for Forge

echo "🔧 Fixing Git Repository Issue in Forge"
echo "========================================"

# Remove corrupted directory
cd /home/forge
rm -rf renthub-mnnzqvzb.on-forge.com

# Clone fresh from GitHub
git clone git@github.com:anemettemadsen33/RentHub.git renthub-mnnzqvzb.on-forge.com

# Enter site directory
cd renthub-mnnzqvzb.on-forge.com

# Checkout master branch
git checkout master

# Enter backend directory
cd backend

# Copy .env from Forge
# (Forge should have created this in /home/forge/renthub-mnnzqvzb.on-forge.com/.env)
if [ -f ../.env ]; then
    cp ../.env .env
    echo "✅ .env copied"
else
    echo "⚠️  .env not found - will need to create manually"
fi

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Generate app key
php artisan key:generate --force

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan optimize

echo ""
echo "✅ Setup complete!"
echo "🧪 Testing..."
php artisan route:list | head -10
curl http://localhost/api/health

echo ""
echo "If health check works, your backend is ready! 🎉"
