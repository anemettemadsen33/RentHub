#!/bin/bash
# ====================================
# RentHub Production Environment Setup
# ====================================
# Run this on Laravel Forge server after deployment

set -e

echo "🚀 RentHub Production Environment Setup"
echo "========================================"

# Check if running on production server
if [ "$APP_ENV" != "production" ]; then
    read -p "⚠️  APP_ENV is not 'production'. Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# 1. Generate Application Key
echo "📝 Step 1: Generating APP_KEY..."
if grep -q "APP_KEY=base64:" .env; then
    echo "✅ APP_KEY already exists"
else
    php artisan key:generate
    echo "✅ APP_KEY generated"
fi

# 2. Generate VAPID Keys for Web Push
echo "📝 Step 2: Generating VAPID keys..."
if grep -q "VAPID_PUBLIC_KEY=B" .env && grep -q "VAPID_PRIVATE_KEY=" .env; then
    echo "✅ VAPID keys already exist"
else
    echo "⚠️  Generating VAPID keys via Tinker..."
    php artisan tinker --execute="
        \$keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        echo 'VAPID_PUBLIC_KEY=' . \$keys['publicKey'] . PHP_EOL;
        echo 'VAPID_PRIVATE_KEY=' . \$keys['privateKey'] . PHP_EOL;
    " | tee vapid-keys.txt
    echo "✅ VAPID keys saved to vapid-keys.txt - add to .env manually"
fi

# 3. Generate Laravel Reverb Keys
echo "📝 Step 3: Generating Reverb keys..."
REVERB_APP_KEY=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-32)
REVERB_APP_SECRET=$(openssl rand -base64 48 | tr -d "=+/" | cut -c1-64)
echo "REVERB_APP_KEY=$REVERB_APP_KEY"
echo "REVERB_APP_SECRET=$REVERB_APP_SECRET"
echo "✅ Add these to Forge Environment Variables"

# 4. Generate Meilisearch Master Key
echo "📝 Step 4: Generating Meilisearch master key..."
MEILISEARCH_KEY=$(openssl rand -base64 48)
echo "MEILISEARCH_KEY=$MEILISEARCH_KEY"
echo "✅ Add to Forge Environment Variables"

# 5. Generate Redis Password
echo "📝 Step 5: Generating Redis password..."
REDIS_PASSWORD=$(openssl rand -base64 32)
echo "REDIS_PASSWORD=$REDIS_PASSWORD"
echo "✅ Update Redis config and .env"

# 6. Run Database Migrations
echo "📝 Step 6: Running migrations..."
read -p "Run migrations now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo "✅ Migrations completed"
else
    echo "⏭️  Skipped migrations"
fi

# 7. Run Production Seeders
echo "📝 Step 7: Seeding production data..."
read -p "Seed database now? (roles, currencies, languages, amenities) (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --class=RolePermissionSeeder --force
    php artisan db:seed --class=LanguageSeeder --force
    php artisan db:seed --class=CurrencySeeder --force
    php artisan db:seed --class=AdminSeeder --force
    php artisan db:seed --class=AmenitySeeder --force
    echo "✅ Production data seeded"
    echo "📧 Admin credentials: admin@renthub.com / Admin@123456"
    echo "⚠️  CHANGE PASSWORD IMMEDIATELY at https://renthub-tbj7yxj7.on-forge.com/admin"
else
    echo "⏭️  Skipped seeding"
fi

# 8. Cache Configuration
echo "📝 Step 8: Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Configuration cached"

# 9. Storage Link
echo "📝 Step 9: Creating storage symlink..."
if [ -L "public/storage" ]; then
    echo "✅ Storage link already exists"
else
    php artisan storage:link
    echo "✅ Storage linked"
fi

# 10. Set Permissions
echo "📝 Step 10: Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
echo "✅ Permissions set"

# Summary
echo ""
echo "=========================================="
echo "✅ Production Setup Complete!"
echo "=========================================="
echo ""
echo "⚠️  NEXT STEPS:"
echo "1. Add generated keys to Forge Environment Variables"
echo "2. Rotate SendGrid API key (old one was leaked)"
echo "3. Set up AWS S3 credentials"
echo "4. Configure Stripe live keys"
echo "5. Set up Sentry DSN"
echo "6. Change admin password at /admin"
echo "7. Test all integrations"
echo ""
echo "📋 See PRODUCTION_SECRETS_CHECKLIST.md for full list"
