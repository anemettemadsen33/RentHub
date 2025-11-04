#!/bin/bash
set -e

echo "================================"
echo "Starting deployment..."
echo "================================"

# Get the deployment directory (where Forge clones the repo)
DEPLOYMENT_DIR="${FORGE_SITE_PATH:-/home/forge/renthub-dji696t0.on-forge.com}"
BACKEND_DIR="$DEPLOYMENT_DIR/backend"

echo "Deployment directory: $DEPLOYMENT_DIR"
echo "Backend directory: $BACKEND_DIR"

# Verify backend directory exists
if [ ! -d "$BACKEND_DIR" ]; then
    echo "ERROR: Backend directory not found at $BACKEND_DIR"
    exit 1
fi

# Navigate to backend directory
cd "$BACKEND_DIR"
echo "Changed to backend directory: $(pwd)"

# Enter maintenance mode
echo "Entering maintenance mode..."
php artisan down --render="errors::503" --retry=60 || true

# Install/update composer dependencies in backend
echo "Installing/updating Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear all caches
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link if it doesn't exist
echo "Setting up storage link..."
php artisan storage:link || true

# Cache configuration and routes for performance
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Install NPM dependencies and build assets if package.json exists
if [ -f "package.json" ]; then
    echo "Building frontend assets..."
    npm ci
    npm run build
fi

# Set correct permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Restart queue workers
echo "Restarting queue workers..."
php artisan queue:restart

# Exit maintenance mode
echo "Exiting maintenance mode..."
php artisan up

# Run a health check
echo "Running health check..."
php artisan optimize

echo "================================"
echo "Deployment completed successfully!"
echo "================================"
