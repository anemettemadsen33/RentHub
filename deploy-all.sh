#!/bin/bash

# Complete Automatic Deployment
# This is the master script that runs everything

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                                                                ║"
echo "║          🚀 RentHub Complete Auto-Deployment 🚀                ║"
echo "║                                                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Step 1: Check prerequisites
echo -e "${BLUE}Step 1: Checking prerequisites...${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if ! command -v gh &> /dev/null; then
    echo -e "${RED}❌ GitHub CLI not found${NC}"
    exit 1
fi
echo -e "${GREEN}✅ GitHub CLI installed${NC}"

if ! command -v vercel &> /dev/null; then
    echo -e "${RED}❌ Vercel CLI not found. Installing...${NC}"
    npm install -g vercel
fi
echo -e "${GREEN}✅ Vercel CLI installed${NC}"

if ! command -v ssh &> /dev/null; then
    echo -e "${RED}❌ SSH not found${NC}"
    exit 1
fi
echo -e "${GREEN}✅ SSH available${NC}"

echo ""

# Step 2: Vercel Login Check
echo -e "${BLUE}Step 2: Vercel Authentication${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if vercel whoami &>/dev/null; then
    echo -e "${GREEN}✅ Already logged in to Vercel as: $(vercel whoami)${NC}"
else
    echo -e "${YELLOW}⚠️  Not logged in to Vercel${NC}"
    echo ""
    echo "Please complete Vercel login:"
    echo "1. The browser will open (or visit the URL shown)"
    echo "2. Authorize the device"
    echo "3. Come back here"
    echo ""
    read -p "Press ENTER to start Vercel login..."
    
    vercel login
    
    if vercel whoami &>/dev/null; then
        echo -e "${GREEN}✅ Successfully logged in!${NC}"
    else
        echo -e "${RED}❌ Vercel login failed${NC}"
        exit 1
    fi
fi

echo ""

# Step 3: Backend Deployment
echo -e "${BLUE}Step 3: Backend Deployment (Forge)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Do you want to deploy backend to Forge?"
echo "This requires SSH access to your Forge server."
echo ""
read -p "Deploy backend? (y/n): " deploy_backend

if [[ $deploy_backend =~ ^[Yy]$ ]]; then
    ./auto-deploy-backend.sh
else
    echo -e "${YELLOW}⚠️  Skipping backend deployment${NC}"
fi

echo ""

# Step 4: Frontend Deployment
echo -e "${BLUE}Step 4: Frontend Deployment (Vercel)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
read -p "Deploy frontend to Vercel? (y/n): " deploy_frontend

if [[ $deploy_frontend =~ ^[Yy]$ ]]; then
    ./auto-deploy-frontend.sh
else
    echo -e "${YELLOW}⚠️  Skipping frontend deployment${NC}"
fi

echo ""

# Step 5: Final Testing
echo -e "${BLUE}Step 5: Integration Testing${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ -f ./test-deployment.sh ]; then
    ./test-deployment.sh
else
    echo -e "${YELLOW}⚠️  Test script not found, running basic tests...${NC}"
    
    echo "Testing Backend..."
    BACKEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/health)
    if [ "$BACKEND_STATUS" = "200" ]; then
        echo -e "${GREEN}✅ Backend: OK${NC}"
    else
        echo -e "${RED}❌ Backend: $BACKEND_STATUS${NC}"
    fi
    
    echo "Testing Frontend..."
    FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://rent-gvirbwqas-madsens-projects.vercel.app)
    if [ "$FRONTEND_STATUS" = "200" ]; then
        echo -e "${GREEN}✅ Frontend: OK${NC}"
    else
        echo -e "${RED}❌ Frontend: $FRONTEND_STATUS${NC}"
    fi
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                                                                ║"
echo "║                  🎉 Deployment Complete! 🎉                    ║"
echo "║                                                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}Your application is live at:${NC}"
echo -e "  ${BLUE}Frontend:${NC} https://rent-gvirbwqas-madsens-projects.vercel.app"
echo -e "  ${BLUE}Backend:${NC}  https://renthub-tbj7yxj7.on-forge.com"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Visit your frontend URL"
echo "  2. Check browser console (F12) for any errors"
echo "  3. Test user registration and login"
echo "  4. Verify API connections work"
echo ""
