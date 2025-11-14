#!/bin/bash

# Script de deployment pentru Vercel - RentHub Frontend
# Acest script configureaza automat totul pentru productie

set -e  # Opreste scriptul la prima eroare

echo "🚀 Pornim configurarea automata pentru Vercel..."

# === VERIFICARI INITIALE ===
echo "📋 Verificam cerintele..."

# Verificam Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js nu este instalat!"
    exit 1
fi

# Verificam npm sau yarn
if command -v yarn &> /dev/null; then
    PKG_MANAGER="yarn"
elif command -v npm &> /dev/null; then
    PKG_MANAGER="npm"
else
    echo "❌ Nici npm nici yarn nu sunt instalate!"
    exit 1
fi

echo "📦 Package manager detectat: $PKG_MANAGER"

# === CREARE FISIERE CONFIGURATIE VERCEL ===
echo "⚙️  Creem fisierele de configurare pentru Vercel..."

# Creaza vercel.json
cat > vercel.json <<EOF
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
      "dest": "https://api.rent-hub.ro/api/\$1"
    },
    {
      "src": "/static/(.*)",
      "headers": {
        "cache-control": "s-maxage=31536000,immutable"
      }
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
    "VITE_API_URL": "https://api.rent-hub.ro",
    "VITE_APP_ENV": "production",
    "VITE_REVERB_APP_KEY": "renthub-prod-key",
    "VITE_REVERB_HOST": "api.rent-hub.ro",
    "VITE_REVERB_PORT": "443",
    "VITE_REVERB_SCHEME": "https"
  },
  "functions": {
    "api/**/*.js": {
      "maxDuration": 30
    }
  }
}
EOF

# === CREARE FISIER .env.production ===
cat > .env.production <<EOF
# Frontend Production Environment Variables
VITE_API_URL=https://api.rent-hub.ro
VITE_APP_ENV=production
VITE_APP_NAME="RentHub"

# WebSocket Configuration
VITE_REVERB_APP_KEY=renthub-prod-key
VITE_REVERB_HOST=api.rent-hub.ro
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

# Analytics
VITE_GOOGLE_ANALYTICS_ID=

# Features
VITE_ENABLE_ANALYTICS=true
VITE_ENABLE_SENTRY=true
VITE_SENTRY_DSN=

# Performance
VITE_API_TIMEOUT=30000
VITE_RETRY_ATTEMPTS=3
EOF

# === OPTIMIZARI PENTRU PRODUCTIE ===
echo "🔧 Aplicam optimizarile pentru productie..."

# Instalam dependențele
if [[ "$PKG_MANAGER" == "yarn" ]]; then
    yarn install --frozen-lockfile --production=false
    yarn build
else
    npm ci
    npm run build
fi

# === VERIFICARI POST-BUILD ===
echo "🔍 Verificam build-ul..."

# Verificam daca exista directorul dist
if [[ ! -d "dist" ]]; then
    echo "❌ Directorul 'dist' nu a fost creat! Build-ul a esuat."
    exit 1
fi

# Verificam daca exista index.html
if [[ ! -f "dist/index.html" ]]; then
    echo "❌ Fisierul 'dist/index.html' nu exista! Build-ul a esuat."
    exit 1
fi

# === ANALIZA DIMENSIUNI ===
echo "📊 Analizam dimensiunile fisierelor..."

# Calculam dimensiunea totala
total_size=$(du -sh dist | cut -f1)
echo "📦 Dimensiunea totala a build-ului: $total_size"

# Găsim cele mai mari fisiere
echo "📋 Cele mai mari fisiere:"
find dist -type f -exec du -h {} + | sort -rh | head -10

# === OPTIMIZARI SUPPLIMENTARE ===
echo "⚡ Aplicam optimizari suplimentare..."

# Comprimam fisierele JavaScript
echo "📦 Comprimam fisierele JavaScript..."
find dist -name "*.js" -exec gzip -k -9 {} \; -o -name "*.css" -exec gzip -k -9 {} \;

# === CREARE SCRIPT DE MONITORING ===
cat > scripts/monitor-frontend.sh <<EOF
#!/bin/bash

# Script de monitorizare pentru frontend

echo "🔍 Monitorizam frontend-ul..."

# Verificam daca build-ul este functional
if [[ -f "dist/index.html" ]]; then
    echo "✅ Build-ul exista"
else
    echo "❌ Build-ul lipseste"
    exit 1
fi

# Verificam API connectivity
api_response=\$(curl -s -o /dev/null -w "%{http_code}" https://api.rent-hub.ro/api/health)
if [[ "\$api_response" == "200" ]]; then
    echo "✅ API este functional (HTTP 200)"
else
    echo "⚠️  API returneaza HTTP \$api_response"
fi

# Verificam WebSocket connection (daca este configurat)
if [[ -n "\$VITE_REVERB_HOST" ]]; then
    echo "🔌 Verificam WebSocket..."
    # Test de baza pentru WebSocket
fi

echo "✅ Monitorizare finalizata!"
EOF

chmod +x scripts/monitor-frontend.sh

# === CREARE DOCUMENTATIE ===
cat > DEPLOYMENT.md <<EOF
# RentHub Frontend - Deployment Guide

## Configurare Vercel

### Variabile de Mediu (Environment Variables)
Adauga urmatoarele variabile in dashboard-ul Vercel:

\`\`\`bash
VITE_API_URL=https://api.rent-hub.ro
VITE_APP_ENV=production
VITE_REVERB_APP_KEY=renthub-prod-key
VITE_REVERB_HOST=api.rent-hub.ro
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
VITE_GOOGLE_ANALYTICS_ID=
VITE_ENABLE_ANALYTICS=true
VITE_ENABLE_SENTRY=true
VITE_SENTRY_DSN=
VITE_API_TIMEOUT=30000
VITE_RETRY_ATTEMPTS=3
\`\`\`

### Build Settings
- **Framework**: Vite
- **Build Command**: \`npm run build\` sau \`yarn build\`
- **Output Directory**: \`dist\`
- **Install Command**: \`npm ci\` sau \`yarn install --frozen-lockfile\`

### Domain Configuration
- **Production Domain**: https://rent-hub.ro
- **API Domain**: https://api.rent-hub.ro

### Performance Optimization
- Enable Gzip compression
- Enable HTTP/2
- Configure CDN for static assets
- Enable browser caching

### Security Headers
Aplicatia include urmatoarele security headers:
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin

### Monitoring
Pentru monitorizare, foloseste:
\`\`\`bash
./scripts/monitor-frontend.sh
\`\`\`

### Troubleshooting
1. **Build esuat**: Verifica log-urile de build in Vercel dashboard
2. **API errors**: Verifica CORS configuration pe backend
3. **WebSocket issues**: Verifica Reverb configuration
4. **Performance**: Analizeaza bundle size si lazy loading

EOF

# === TESTARE FINALA ===
echo "🧪 Testam configuratia..."

# Testam API connectivity
echo "🔗 Testam conexiunea cu API-ul..."
api_test=$(curl -s -o /dev/null -w "%{http_code}" https://api.rent-hub.ro/api/health 2>/dev/null || echo "000")
if [[ "$api_test" == "200" ]]; then
    echo "✅ API connectivity: OK"
else
    echo "⚠️  API connectivity: HTTP $api_test"
fi

# === RAPORT FINAL ===
echo ""
echo "✅ CONFIGURARE VERCEL FINALIZATA!"
echo "=================================="
echo ""
echo "📋 Fisiere create:"
echo "  • vercel.json - Configuratie Vercel"
echo "  • .env.production - Variabile de mediu"
echo "  • scripts/monitor-frontend.sh - Script monitoring"
echo "  • DEPLOYMENT.md - Documentatie completa"
echo ""
echo "🔧 Urmatorii pasi:"
echo "  1. Commit fisierele: git add . && git commit -m 'Add Vercel configuration'"
echo "  2. Push pe branch-ul principal"
echo "  3. Configureaza project in Vercel dashboard"
echo "  4. Adauga environment variables in Vercel"
echo "  5. Deploy!"
echo ""
echo "📊 Informatii utile:"
echo "  • Build size: $total_size"
echo "  • API test: HTTP $api_test"
echo "  • Package manager: $PKG_MANAGER"
echo ""
echo "🚀 Frontend-ul este gata pentru deploy pe Vercel!"
echo "   URL: https://rent-hub.ro"
echo "   Build: npm run build"
echo "   Deploy: git push"
echo ""
echo "Pentru troubleshooting, verifica DEPLOYMENT.md"