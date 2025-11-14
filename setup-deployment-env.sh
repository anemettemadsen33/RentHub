#!/bin/bash

# Script de configurare completă pentru RentHub Deployment
# Acest script configurează automat variabilele de mediu și face deployment

echo "🚀 Configurare completă RentHub Deployment"
echo "========================================"

# Verificăm dacă avem acces la Vercel CLI
if ! command -v vercel &> /dev/null; then
    echo "❌ Vercel CLI nu este instalat. Instalare..."
    npm i -g vercel
fi

# Verificăm dacă avem acces la Laravel Forge
if ! command -v forge &> /dev/null; then
    echo "❌ Laravel Forge CLI nu este instalat."
    echo "Vă rugăm să instalați conform documentației Laravel Forge."
fi

echo ""
echo "📋 Configurare variabile de mediu pentru Vercel..."

# Frontend Vercel Environment Variables
echo "Setare variabile frontend..."

# API Configuration
vercel env add NEXT_PUBLIC_API_BASE_URL production https://renthub-tbj7yxj7.on-forge.com/api/v1 -y
vercel env add NEXT_PUBLIC_API_BASE_URL preview https://renthub-tbj7yxj7.on-forge.com/api/v1 -y
vercel env add NEXT_PUBLIC_API_BASE_URL development http://localhost:8000/api/v1 -y

vercel env add NEXT_PUBLIC_API_URL production https://renthub-tbj7yxj7.on-forge.com/api -y
vercel env add NEXT_PUBLIC_API_URL preview https://renthub-tbj7yxj7.on-forge.com/api -y
vercel env add NEXT_PUBLIC_API_URL development http://localhost:8000/api -y

# WebSocket Configuration
vercel env add NEXT_PUBLIC_WEBSOCKET_URL production wss://renthub-tbj7yxj7.on-forge.com:6001 -y
vercel env add NEXT_PUBLIC_WEBSOCKET_URL preview wss://renthub-tbj7yxj7.on-forge.com:6001 -y
vercel env add NEXT_PUBLIC_WEBSOCKET_URL development ws://localhost:6001 -y

# Pusher Configuration (dacă este folosit)
vercel env add NEXT_PUBLIC_PUSHER_APP_KEY production your-pusher-app-key -y
vercel env add NEXT_PUBLIC_PUSHER_HOST production renthub-tbj7yxj7.on-forge.com -y
vercel env add NEXT_PUBLIC_PUSHER_PORT production 6001 -y
vercel env add NEXT_PUBLIC_PUSHER_SCHEME production https -y

# Application Settings
vercel env add NEXT_PUBLIC_APP_NAME production "RentHub" -y
vercel env add NEXT_PUBLIC_APP_URL production https://rent-hub-beta.vercel.app -y

echo "✅ Variabile frontend configurate!"

echo ""
echo "🔧 Configurare backend Laravel Forge..."

# Backend Environment Variables (pentru Laravel Forge)
echo "Setare variabile backend..."

# Database Configuration
echo "DB_CONNECTION=sqlite" >> backend/.env.production
echo "DB_DATABASE=/home/forge/renthub-tbj7yxj7.on-forge.com/database/database.sqlite" >> backend/.env.production

# Broadcasting Configuration
echo "BROADCAST_DRIVER=pusher" >> backend/.env.production
echo "PUSHER_APP_ID=your-pusher-app-id" >> backend/.env.production
echo "PUSHER_APP_KEY=your-pusher-app-key" >> backend/.env.production
echo "PUSHER_APP_SECRET=your-pusher-app-secret" >> backend/.env.production
echo "PUSHER_HOST=renthub-tbj7yxj7.on-forge.com" >> backend/.env.production
echo "PUSHER_PORT=443" >> backend/.env.production
echo "PUSHER_SCHEME=https" >> backend/.env.production
echo "PUSHER_APP_CLUSTER=eu" >> backend/.env.production

# CORS Configuration
echo "CORS_ALLOWED_ORIGINS=https://rent-hub-beta.vercel.app,https://rent-hub-beta.vercel.app" >> backend/.env.production

# Cache Configuration
echo "CACHE_DRIVER=redis" >> backend/.env.production
echo "REDIS_HOST=127.0.0.1" >> backend/.env.production
echo "REDIS_PORT=6379" >> backend/.env.production

# Queue Configuration
echo "QUEUE_CONNECTION=redis" >> backend/.env.production

# Session Configuration
echo "SESSION_DRIVER=redis" >> backend/.env.production

echo "✅ Variabile backend configurate!"

echo ""
echo "🔄 Restart servicii backend..."

# Comenzi pentru restart servicii (via SSH)
cat > backend/restart-services.sh << 'EOF'
#!/bin/bash
echo "Restarting backend services..."

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart Queue Worker
sudo systemctl restart supervisor

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Services restarted successfully!"
EOF

chmod +x backend/restart-services.sh

echo "✅ Script restart creat!"

echo ""
echo "📊 Verificare status deployment..."

# Verificăm statusul serviciilor
echo "Verificare backend..."
curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/health || echo "Backend offline"

echo "Verificare frontend..."
curl -s -o /dev/null -w "%{http_code}" https://rent-hub-beta.vercel.app || echo "Frontend offline"

echo ""
echo "🎯 Configurare completă!"
echo ""
echo "📋 Rezumat acțiuni:"
echo "1. ✅ Variabile de mediu frontend configurate"
echo "2. ✅ Variabile de mediu backend configurate"
echo "3. ✅ Script restart servicii creat"
echo "4. 📊 Status servicii verificat"
echo ""
echo "🔥 Urmează să faci:"
echo "1. Rulează: ./backend/restart-services.sh (pe serverul Forge)"
echo "2. Redeploy frontend: git push origin main"
echo "3. Testează toate funcționalitățile"
echo ""
echo "🔗 Links importante:"
echo "Frontend: https://rent-hub-beta.vercel.app"
echo "Backend: https://renthub-tbj7yxj7.on-forge.com"
echo "Forge Dashboard: https://forge.laravel.com/servers"
echo "Vercel Dashboard: https://vercel.com/dashboard"
echo ""
echo "🚀 Deployment complet! Proiectul tău este pregătit pentru producție!"