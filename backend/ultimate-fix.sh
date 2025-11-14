#!/bin/bash
# Ultimate Forge Backend Fix

echo "🔧 RentHub Backend - Ultimate Fix"
echo "=================================="

cd /home/forge/renthub-mnnzqvzb.on-forge.com/backend

echo "1️⃣ Finding PHP version..."
PHP_VERSION=$(php -v | head -1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo "PHP Version: $PHP_VERSION"

echo "2️⃣ Finding PHP-FPM socket..."
PHP_SOCKET=$(ls /var/run/php/php*-fpm.sock | head -1)
echo "Socket found: $PHP_SOCKET"

echo "3️⃣ Checking if index.php exists..."
if [ -f "public/index.php" ]; then
    echo "✅ index.php exists"
else
    echo "❌ index.php NOT FOUND!"
fi

echo "4️⃣ Testing PHP directly..."
cd public
php -r "echo 'PHP works! ✅\n';"

echo "5️⃣ Testing Laravel..."
cd ..
php artisan --version

echo "6️⃣ Testing route list..."
php artisan route:list | head -5

echo "7️⃣ Testing health endpoint directly..."
php artisan tinker --execute="echo app('Illuminate\Contracts\Routing\UrlGenerator')->to('/api/health');"

echo ""
echo "=================================="
echo "📋 COPY THIS INFO AND SEND TO ME:"
echo "=================================="
echo "PHP Version: $PHP_VERSION"
echo "PHP Socket: $PHP_SOCKET"
echo ""
echo "Now test with built-in PHP server:"
echo "cd /home/forge/renthub-mnnzqvzb.on-forge.com/backend/public"
echo "php -S 0.0.0.0:8080"
echo ""
echo "Then visit: http://178.128.135.24:8080/api/health"
