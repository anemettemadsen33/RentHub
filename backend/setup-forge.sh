#!/bin/bash
# Forge Complete Setup Script
# Run this in SSH: bash setup-forge.sh

echo "🔧 RentHub Forge Complete Setup"
echo "================================"
echo ""

# Navigate to site directory
SITE_PATH="/home/forge/renthub-ny52mbov.on-forge.com/backend"
cd $SITE_PATH

echo "1️⃣ Backing up current .env..."
cp .env .env.backup

echo "2️⃣ Updating database configuration..."

# Update .env file
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env

# Check if MySQL config exists, if not add it
if ! grep -q "DB_HOST=" .env; then
    echo "" >> .env
    echo "# MySQL Database Configuration" >> .env
    echo "DB_HOST=127.0.0.1" >> .env
    echo "DB_PORT=3306" >> .env
    echo "DB_DATABASE=forge" >> .env
    echo "DB_USERNAME=forge" >> .env
    echo "DB_PASSWORD=" >> .env
fi

echo "3️⃣ .env updated! Now you need to add DB_PASSWORD manually"
echo ""
echo "   Run this command to edit:"
echo "   nano $SITE_PATH/.env"
echo ""
echo "   Find DB_PASSWORD= and add your database password"
echo "   (Get password from Forge Dashboard → Database tab)"
echo ""
echo "   Save: Ctrl+X, then Y, then Enter"
echo ""

read -p "Press Enter after you've added the DB_PASSWORD..."

echo "4️⃣ Testing database connection..."
cd $SITE_PATH
php artisan config:clear

if php artisan migrate:status > /dev/null 2>&1; then
    echo "✅ Database connection successful!"
else
    echo "❌ Database connection failed. Check your DB_PASSWORD"
    echo ""
    echo "Current .env database settings:"
    grep "DB_" .env
    exit 1
fi

echo "5️⃣ Running migrations..."
php artisan migrate --force

echo "6️⃣ Seeding database..."
php artisan db:seed --force

echo "7️⃣ Optimizing application..."
php artisan optimize:clear
php artisan config:cache
php artisan optimize

echo "8️⃣ Fixing permissions..."
chmod -R 775 storage bootstrap/cache

echo "9️⃣ Testing health endpoint..."
cd public
php -r "echo 'Testing PHP...\n'; var_dump(file_exists('index.php'));"

echo ""
echo "✅ Setup complete!"
echo ""
echo "🧪 Test your backend:"
echo "   curl http://localhost/api/health"
echo ""
echo "🌐 Or in browser:"
echo "   https://renthub-ny52mbov.on-forge.com/api/health"
echo ""
