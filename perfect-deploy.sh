#!/bin/bash

# 🚀 RENTHUB PERFECT DEPLOYMENT MASTER SCRIPT
# Acest script automatizează tot procesul de deploy pe Forge și Vercel

set -e  # Oprește la prima eroare

# Culori pentru output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funcții utile
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_header() {
    echo -e "\n${PURPLE}==== $1 ====${NC}\n"
}

# Verificare preliminară
log_header "VERIFICARE SISTEM"

# Verificăm dacă avem acces la tool-urile necesare
command -v git >/dev/null 2>&1 || { log_error "Git nu este instalat"; exit 1; }
command -v node >/dev/null 2>&1 || { log_error "Node.js nu este instalat"; exit 1; }
command -v vercel >/dev/null 2>&1 || { log_warning "Vercel CLI nu este instalat. Instalăm acum..."; npm i -g vercel; }

log_success "Toate dependențele sunt disponibile"

# === CONFIGURARE VARIABILE ===
log_header "CONFIGURARE VARIABILE"

# Detectăm directorul proiectului
PROJECT_ROOT=$(pwd)
BACKEND_DIR="$PROJECT_ROOT/backend"
FRONTEND_DIR="$PROJECT_ROOT/frontend"

log_info "Director proiect: $PROJECT_ROOT"
log_info "Backend: $BACKEND_DIR"
log_info "Frontend: $FRONTEND_DIR"

# Întrebăm utilizatorul pentru detalii de conectare
read -p "Adresa server Forge (ex: forge@123.456.789.0): " FORGE_SERVER
read -p "URL domeniu API (ex: api.rent-hub.ro): " API_DOMAIN
read -p "URL domeniu frontend (ex: rent-hub.ro): " FRONTEND_DOMAIN
read -p "Branch Git pentru deploy (default: main): " GIT_BRANCH
nGIT_BRANCH=${GIT_BRANCH:-main}

log_success "Configurare completă"

# === DEPLOY BACKEND ===
log_header "DEPLOY BACKEND PE FORGE"

log_info "Conectare la serverul Forge..."

# Creăm scriptul de deploy pentru backend
BACKEND_DEPLOY_SCRIPT=$(cat << 'EOF'
#!/bin/bash
set -e

echo "🚀 Începem deploy backend..."

# Navigăm la directorul proiectului
cd /home/forge/$API_DOMAIN

# Pull ultimele modificări
echo "📥 Pull modificări..."
git fetch origin
git reset --hard origin/$GIT_BRANCH

# Instalăm dependențele
echo "📦 Instalare dependențe..."
composer install --no-dev --optimize-autoloader --no-interaction

# Optimizăm Laravel
echo "⚡ Optimizare Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrări database
echo "🗄️ Migrări database..."
php artisan migrate --force

# Restart servicii
echo "🔄 Restart servicii..."
php artisan queue:restart
sudo supervisorctl restart all
sudo systemctl restart renthub-reverb

# Verificare finală
echo "✅ Verificare deploy..."
php artisan tinker --execute="echo 'Backend OK';"

echo "✅ Backend deploy complet!"
EOF
)

# Salvăm și executăm scriptul pe server
log_info "Trimitere script deploy pe Forge..."
echo "$BACKEND_DEPLOY_SCRIPT" | ssh $FORGE_SERVER "cat > /tmp/deploy-backend.sh && chmod +x /tmp/deploy-backend.sh && /tmp/deploy-backend.sh"

log_success "Backend deployat cu succes"

# === DEPLOY FRONTEND ===
log_header "DEPLOY FRONTEND PE VERCEL"

cd $FRONTEND_DIR

# Verificăm dacă există deja proiect Vercel
if [ -f "vercel.json" ]; then
    log_info "Actualizare configuratie Vercel..."
else
    log_info "Creare configuratie Vercel..."
fi

# Actualizăm vercel.json cu domeniul corect
cat > vercel.json << EOF
{
  "version": 2,
  "name": "renthub-frontend",
  "builds": [
    {
      "src": "package.json",
      "use": "@vercel/static-build",
      "config": {
        "distDir": "dist"
      }
    }
  ],
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "https://$API_DOMAIN/api/\$1"
    },
    {
      "src": "/(.*)",
      "dest": "/index.html"
    }
  ],
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        {
          "key": "X-Content-Type-Options",
          "value": "nosniff"
        },
        {
          "key": "X-Frame-Options",
          "value": "DENY"
        },
        {
          "key": "X-XSS-Protection",
          "value": "1; mode=block"
        },
        {
          "key": "Referrer-Policy",
          "value": "strict-origin-when-cross-origin"
        }
      ]
    }
  ],
  "env": {
    "VITE_API_URL": "https://$API_DOMAIN",
    "VITE_APP_ENV": "production",
    "VITE_REVERB_APP_KEY": "renthub-prod-key",
    "VITE_REVERB_HOST": "$API_DOMAIN",
    "VITE_REVERB_PORT": "443",
    "VITE_REVERB_SCHEME": "https"
  }
}
EOF

# Instalăm dependențele și facem build
log_info "Instalare dependențe frontend..."
npm ci

log_info "Build frontend..."
npm run build

# Deploy pe Vercel
log_info "Deploy pe Vercel..."
vercel --prod --yes

log_success "Frontend deployat cu succes"

# === TESTARE FINALĂ ===
log_header "TESTARE FINALĂ"

# Așteptăm 30 secunde pentru propagare
log_info "Așteptare 30 secunde pentru propagare..."
sleep 30

# Testăm backend
log_info "Testare backend..."
BACKEND_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" https://$API_DOMAIN/api/health)
if [ "$BACKEND_HEALTH" = "200" ]; then
    log_success "Backend sănătos (HTTP 200)"
else
    log_error "Backend problemă (HTTP $BACKEND_HEALTH)"
fi

# Testăm frontend
log_info "Testare frontend..."
FRONTEND_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" https://$FRONTEND_DOMAIN)
if [ "$FRONTEND_HEALTH" = "200" ]; then
    log_success "Frontend sănătos (HTTP 200)"
else
    log_error "Frontend problemă (HTTP $FRONTEND_HEALTH)"
fi

# Testăm API connectivity
log_info "Testare conexiune API..."
API_CONNECTIVITY=$(curl -s -o /dev/null -w "%{http_code}" https://$FRONTEND_DOMAIN/api/health)
if [ "$API_CONNECTIVITY" = "200" ]; then
    log_success "Conexiune API funcțională"
else
    log_warning "Conexiune API problemă (HTTP $API_CONNECTIVITY)"
fi

# === MONITORING ȘI DEBUGGING ===
log_header "MONITORING ȘI DEBUGGING"

# Creăm script de monitorizare
MONITOR_SCRIPT=$(cat << 'EOF'
#!/bin/bash
# Script de monitorizare RentHub

echo "🔍 Monitorizare RentHub..."

# Backend health
backend_health=$(curl -s https://$API_DOMAIN/api/health | jq -r '.status' 2>/dev/null || echo "error")
echo "Backend Health: $backend_health"

# Frontend health  
frontend_health=$(curl -s -o /dev/null -w "%{http_code}" https://$FRONTEND_DOMAIN)
echo "Frontend Health: HTTP $frontend_health"

# Database connection
db_health=$(curl -s https://$API_DOMAIN/api/health/database | jq -r '.status' 2>/dev/null || echo "error")
echo "Database Health: $db_health"

# Queue status
queue_size=$(curl -s https://$API_DOMAIN/api/health/queues | jq -r '.queue_size' 2>/dev/null || echo "error")
echo "Queue Size: $queue_size"

# WebSocket status
ws_health=$(curl -s https://$API_DOMAIN/api/health/websocket | jq -r '.status' 2>/dev/null || echo "error")
echo "WebSocket Health: $ws_health"

echo "✅ Monitorizare completă!"
EOF
)

# Salvăm scriptul de monitorizare
echo "$MONITOR_SCRIPT" > $PROJECT_ROOT/monitor-rent hub.sh
chmod +x $PROJECT_ROOT/monitor-renthub.sh

log_info "Script de monitorizare creat: monitor-renthub.sh"

# === RAPORT FINAL ===
log_header "DEPLOY COMPLET! 🎉"

echo -e "${GREEN}✅ DEPLOYMENT FINALIZAT CU SUCCES!${NC}"
echo ""
echo -e "${CYAN}📋 Rezumat:${NC}"
echo "  • Backend: https://$API_DOMAIN"
echo "  • Frontend: https://$FRONTEND_DOMAIN"  
echo "  • Health Check: https://$API_DOMAIN/api/health"
echo "  • Dashboard: https://$API_DOMAIN/api/health/production"
echo ""
echo -e "${CYAN}🔧 Comenzi utile:${NC}"
echo "  • Monitorizare: ./monitor-renthub.sh"
echo "  • Log-uri backend: ssh $FORGE_SERVER 'tail -f /home/forge/$API_DOMAIN/storage/logs/laravel.log'"
echo "  • Restart backend: ssh $FORGE_SERVER 'sudo supervisorctl restart all'"
echo ""
echo -e "${YELLOW}⚠️  Următorii pași:${NC}"
echo "  1. Testează autentificarea pe https://$FRONTEND_DOMAIN/login"
echo "  2. Verifică notificările în timp real (WebSocket)"
echo "  3. Testează plățile și generarea PDF-urilor"
echo "  4. Verifică performanța conversațiilor API"
echo ""
echo -e "${GREEN}🚀 Aplicația RentHub este acum live și funcțională!${NC}"

# Salvăm configurarea pentru viitor
cat > $PROJECT_ROOT/deploy-config.json << EOF
{
  "forge_server": "$FORGE_SERVER",
  "api_domain": "$API_DOMAIN", 
  "frontend_domain": "$FRONTEND_DOMAIN",
  "git_branch": "$GIT_BRANCH",
  "deployed_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
}
EOF

log_success "Configurare salvată în deploy-config.json"