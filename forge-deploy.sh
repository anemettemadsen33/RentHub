cd /home/forge/renthub-tbj7yxj7.on-forge.com
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

if [ -f artisan ]; then
    echo "Running migrations..."
    $FORGE_PHP artisan migrate --force
    
    echo "Clearing caches..."
    $FORGE_PHP artisan config:clear
    $FORGE_PHP artisan cache:clear
    $FORGE_PHP artisan route:clear
    $FORGE_PHP artisan view:clear
    
    echo "Optimizing..."
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    
    echo "Creating storage link..."
    $FORGE_PHP artisan storage:link || true
    
    # Check if database is empty and seed if needed
    PROPERTY_COUNT=$($FORGE_PHP artisan tinker --execute="echo App\Models\Property::count();")
    if [ "$PROPERTY_COUNT" -eq "0" ]; then
        echo "Database is empty. Running production seeder..."
        $FORGE_PHP artisan db:seed --class=ProductionSeeder --force
    fi
fi
