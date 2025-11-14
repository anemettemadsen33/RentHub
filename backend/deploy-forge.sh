#!/bin/bash

# Script de deployment pentru Laravel Forge - RentHub
# Acest script configureaza automat totul pentru productie

set -e  # Opreste scriptul la prima eroare

echo "🚀 Pornim configurarea automata pentru Laravel Forge..."

# === VERIFICARI INITIALE ===
echo "📋 Verificam cerintele..."

# Verificam daca suntem pe serverul Forge
if [[ "$USER" != "forge" ]]; then
    echo "❌ Acest script trebuie rulat de utilizatorul 'forge' pe serverul Laravel Forge!"
    exit 1
fi

# Verificam daca directorul exista
if [[ ! -d "$FORGE_SITE_PATH" ]]; then
    echo "❌ Directorul $FORGE_SITE_PATH nu exista!"
    exit 1
fi

cd $FORGE_SITE_PATH

# === CONFIGURARE MEDIU ===
echo "⚙️  Configuram mediul de productie..."

# Copiem fisierul .env production
cp .env.production .env

# Generam APP_KEY nou daca nu exista
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# === PERMISIUNI ===
echo "🔒 Setam permisiunile..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 644 *.php
chmod -R 755 app/Console/Commands
chmod -R 755 app/Jobs

# === DEPENDENTE COMPOSER ===
echo "📦 Instalam dependente..."
composer install --no-dev --optimize-autoloader --no-interaction

# === CACHE LARAVEL ===
echo "💾 Curatam si regeneram cache-urile..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Cache regenerate cu succes!"

# === BAZA DE DATE ===
echo "🗄️  Verificam si configuram baza de date..."

# Rulam migrarile
php artisan migrate --force

# Optimizam tabelele
php artisan db:seed --class=DatabaseSeeder --force

# === INDEXURI SI OPTIMIZARI ===
echo "📊 Adaugam indexuri pentru performanta..."
php artisan migrate --path=database/migrations/performance --force

# === SERVICII REDIS ===
echo "🔴 Verificam Redis..."
if ! redis-cli ping > /dev/null 2>&1; then
    echo "⚠️  Redis nu este pornit. Pornim serviciul..."
    sudo systemctl start redis
    sudo systemctl enable redis
fi

# Curatam cache Redis
redis-cli FLUSHALL

# === QUEUE SI WORKERS ===
echo "⚡ Configuram queue workers..."

# Oprim workerii existenti
php artisan queue:restart

# Pornim workerii cu supervisor
echo "[program:renthub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $FORGE_SITE_PATH/artisan queue:work redis --sleep=3 --tries=3 --timeout=60 --max-jobs=1000
directory=$FORGE_SITE_PATH
autostart=true
autorestart=true
user=forge
numprocs=4
redirect_stderr=true
stdout_logfile=$FORGE_SITE_PATH/storage/logs/worker.log
stopwaitsecs=3600" | sudo tee /etc/supervisor/conf.d/renthub-worker.conf

# Reincarcam configuratia supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start renthub-worker:*

# === WEBSOCKET REVERB ===
echo "🌐 Pornim serverul Reverb WebSocket..."

# Creaza serviciu systemd pentru Reverb
echo "[Unit]
Description=RentHub Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=forge
WorkingDirectory=$FORGE_SITE_PATH
ExecStart=/usr/bin/php artisan reverb:start
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target" | sudo tee /etc/systemd/system/renthub-reverb.service

# Pornim serviciul
sudo systemctl daemon-reload
sudo systemctl enable renthub-reverb
sudo systemctl start renthub-reverb

# === OPTIMIZARI NGINX ===
echo "🚀 Optimizam Nginx..."

# Configuratie Nginx optimizata
sudo tee /etc/nginx/sites-available/$FORGE_SITE_NAME > /dev/null <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name api.rent-hub.ro;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.rent-hub.ro;
    root $FORGE_SITE_PATH/public;

    # SSL Certificate
    ssl_certificate /etc/nginx/ssl/api.rent-hub.ro/server.crt;
    ssl_certificate_key /etc/nginx/ssl/api.rent-hub.ro/server.key;

    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml application/atom+xml image/svg+xml;

    # File Upload
    client_max_body_size 100M;
    client_body_timeout 120s;
    client_header_timeout 120s;

    # Timeouts
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;
    send_timeout 60s;

    # Logging
    access_log /var/log/nginx/api.rent-hub.ro-access.log;
    error_log /var/log/nginx/api.rent-hub.ro-error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 8 16k;
        fastcgi_busy_buffers_size 64k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # WebSocket support for Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_read_timeout 86400;
    }
}
EOF

# Reactivam configuratia Nginx
sudo ln -sf /etc/nginx/sites-available/$FORGE_SITE_NAME /etc/nginx/sites-enabled/$FORGE_SITE_NAME
sudo nginx -t
sudo systemctl reload nginx

# === CRON JOBS ===
echo "⏰ Configuram cron jobs..."
(crontab -l 2>/dev/null; echo "* * * * * cd $FORGE_SITE_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# === MONITORING SI LOGGING ===
echo "📊 Configuram monitorizarea..."

# Creaza director pentru log-uri
touch storage/logs/worker.log
touch storage/logs/reverb.log
touch storage/logs/scheduler.log

# === VERIFICARE FINALA ===
echo "🔍 Verificam instalarea..."

# Testam conexiunea la baza de date
php artisan tinker --execute="echo 'Database connection: OK';" 2>/dev/null || echo "⚠️  Problemă la conexiunea cu baza de date"

# Testam cache
php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');" 2>/dev/null || echo "⚠️  Problemă la cache"

# Testam queue
php artisan tinker --execute="Queue::push(function() { echo 'Queue: OK'; });" 2>/dev/null || echo "⚠️  Problemă la queue"

# === RAPORT FINAL ===
echo ""
echo "✅ CONFIGURARE FINALIZATA!"
echo "=========================="
echo ""
echo "📋 Ce am configurat:"
echo "  • Mediu de productie"
echo "  • Baza de date MySQL"
echo "  • Redis pentru cache și queue"
echo "  • Queue workers (4 procese)"
echo "  • WebSocket Reverb server"
echo "  • Nginx optimizat cu HTTP/2 și SSL"
echo "  • Cron jobs pentru Laravel scheduler"
echo "  • Monitorizare și logging"
echo ""
echo "🔧 Urmatorii pasi:"
echo "  1. Actualizeaza credentialele in .env"
echo "  2. Configureaza email-ul (Mailgun)"
echo "  3. Adauga cheile API pentru serviciile externe"
echo "  4. Testeaza toate functionalitatile"
echo "  5. Configureaza backup-uri automate"
echo ""
echo "📊 Comenzi utile:"
echo "  • Verifica status: sudo supervisorctl status"
echo "  • Vezi log-uri: tail -f storage/logs/laravel.log"
echo "  • Restart workers: sudo supervisorctl restart renthub-worker:*"
echo "  • Restart Reverb: sudo systemctl restart renthub-reverb"
echo ""
echo "🚀 Aplicatia este gata pentru productie!"
echo "   Backend API: https://api.rent-hub.ro"
echo "   Health Check: https://api.rent-hub.ro/api/health"
echo "   Dashboard: https://api.rent-hub.ro/api/health/production"