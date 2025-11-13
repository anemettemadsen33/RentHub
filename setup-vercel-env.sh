#!/bin/bash

# Vercel Environment Variables Setup
# Run this to get the exact commands for Vercel CLI

echo "📋 Copy and paste these commands in Vercel Dashboard or via CLI"
echo ""
echo "=== PRODUCTION ENVIRONMENT VARIABLES ==="
echo ""

cat << 'EOF'
# Application
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=https://rent-gvirbwqas-madsens-projects.vercel.app
NEXT_PUBLIC_APP_ENV=production
NODE_ENV=production

# API Configuration
NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com/api
NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1
NEXT_PUBLIC_API_TIMEOUT=30000

# Authentication
NEXTAUTH_URL=https://rent-gvirbwqas-madsens-projects.vercel.app
NEXTAUTH_SECRET=JJbZoOgDVutqa9ZPrcpxPoNT3PUgONPInumvvo/8UTI=

# Features
NEXT_PUBLIC_ENABLE_ANALYTICS=true
NEXT_PUBLIC_ENABLE_TRACKING=true
NEXT_PUBLIC_ENABLE_PWA=true

# Monitoring
NEXT_PUBLIC_SENTRY_DSN=your-sentry-dsn-here
EOF

echo ""
echo "=== VERCEL CLI COMMANDS ==="
echo ""
echo "cd frontend"
echo "vercel env add NEXT_PUBLIC_APP_URL production"
echo "# Then paste: https://rent-gvirbwqas-madsens-projects.vercel.app"
echo ""
echo "vercel env add NEXT_PUBLIC_API_URL production"
echo "# Then paste: https://renthub-tbj7yxj7.on-forge.com/api"
echo ""
echo "vercel env add NEXT_PUBLIC_API_BASE_URL production"
echo "# Then paste: https://renthub-tbj7yxj7.on-forge.com/api/v1"
echo ""
echo "vercel env add NEXTAUTH_URL production"
echo "# Then paste: https://rent-gvirbwqas-madsens-projects.vercel.app"
