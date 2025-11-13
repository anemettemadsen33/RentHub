#!/bin/bash

# Automatic Frontend Deployment Script for Vercel
# Requires: vercel login completed

set -e

echo "🚀 Starting Automatic Frontend Deployment..."
echo "============================================"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

cd /workspaces/RentHub/frontend

echo -e "${YELLOW}📋 Checking Vercel authentication...${NC}"
if ! vercel whoami &>/dev/null; then
    echo -e "${RED}❌ Not logged in to Vercel${NC}"
    echo ""
    echo "Please run these steps:"
    echo "1. Run: vercel login"
    echo "2. Visit the URL shown"
    echo "3. Authorize the device"
    echo "4. Run this script again"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ Logged in to Vercel as: $(vercel whoami)${NC}"
echo ""

echo -e "${YELLOW}📦 Setting production environment variables...${NC}"

# Set all required environment variables for production
vercel env rm NEXT_PUBLIC_APP_URL production --yes 2>/dev/null || true
vercel env add NEXT_PUBLIC_APP_URL production <<< "https://rent-gvirbwqas-madsens-projects.vercel.app"

vercel env rm NEXT_PUBLIC_API_URL production --yes 2>/dev/null || true
vercel env add NEXT_PUBLIC_API_URL production <<< "https://renthub-tbj7yxj7.on-forge.com/api"

vercel env rm NEXT_PUBLIC_API_BASE_URL production --yes 2>/dev/null || true
vercel env add NEXT_PUBLIC_API_BASE_URL production <<< "https://renthub-tbj7yxj7.on-forge.com/api/v1"

vercel env rm NEXTAUTH_URL production --yes 2>/dev/null || true
vercel env add NEXTAUTH_URL production <<< "https://rent-gvirbwqas-madsens-projects.vercel.app"

vercel env rm NEXTAUTH_SECRET production --yes 2>/dev/null || true
vercel env add NEXTAUTH_SECRET production <<< "JJbZoOgDVutqa9ZPrcpxPoNT3PUgONPInumvvo/8UTI="

vercel env rm NEXT_PUBLIC_APP_NAME production --yes 2>/dev/null || true
vercel env add NEXT_PUBLIC_APP_NAME production <<< "RentHub"

vercel env rm NEXT_PUBLIC_APP_ENV production --yes 2>/dev/null || true
vercel env add NEXT_PUBLIC_APP_ENV production <<< "production"

echo -e "${GREEN}✅ Environment variables set${NC}"
echo ""

echo -e "${YELLOW}🏗️  Building and deploying to production...${NC}"
echo ""

# Deploy to production
vercel --prod --yes

echo ""
echo -e "${GREEN}✅ Frontend deployed successfully!${NC}"
echo ""

echo -e "${YELLOW}📊 Testing deployment...${NC}"
sleep 5

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://rent-gvirbwqas-madsens-projects.vercel.app)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Frontend is live and responding: $HTTP_CODE${NC}"
else
    echo -e "${RED}⚠️  Frontend returned: $HTTP_CODE${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Deployment Complete!${NC}"
echo ""
echo "URLs:"
echo "  Frontend: https://rent-gvirbwqas-madsens-projects.vercel.app"
echo "  Backend:  https://renthub-tbj7yxj7.on-forge.com"
echo ""
echo "Next: Test the integration with ./test-deployment.sh"
