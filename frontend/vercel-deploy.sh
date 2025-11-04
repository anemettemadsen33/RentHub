#!/bin/bash

# RentHub Frontend - Quick Vercel Deployment Script
# This script helps you deploy the frontend to Vercel quickly

echo "🚀 RentHub Frontend - Vercel Deployment Helper"
echo "================================================"
echo ""

# Check if vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "❌ Vercel CLI not found!"
    echo ""
    echo "Please install it first:"
    echo "  npm install -g vercel"
    echo ""
    exit 1
fi

echo "✅ Vercel CLI found"
echo ""

# Check if we're in the frontend directory
if [ ! -f "package.json" ]; then
    echo "❌ This script must be run from the frontend directory"
    echo ""
    echo "Run: cd frontend && ./vercel-deploy.sh"
    echo ""
    exit 1
fi

echo "📦 Current directory: $(pwd)"
echo ""

# Ask for environment variables
echo "🔧 Environment Configuration"
echo "----------------------------"
echo ""

read -p "Enter your backend API URL (e.g., https://api.renthub.com): " API_URL
read -p "Enter your frontend URL (will be provided by Vercel): " SITE_URL

if [ -z "$API_URL" ]; then
    echo "❌ Backend API URL is required!"
    exit 1
fi

echo ""
echo "🔐 Generating NextAuth secret..."
NEXTAUTH_SECRET=$(openssl rand -base64 32)
echo "✅ Secret generated"
echo ""

# Create .env.local for reference
echo "📝 Creating .env.local (for local development)"
cat > .env.local << EOF
# Local Development Environment
NEXT_PUBLIC_API_URL=${API_URL}
NEXT_PUBLIC_SITE_URL=${SITE_URL:-http://localhost:3000}
NEXT_PUBLIC_AMP_ENABLED=false
NEXTAUTH_URL=${SITE_URL:-http://localhost:3000}
NEXTAUTH_SECRET=${NEXTAUTH_SECRET}
EOF

echo "✅ .env.local created"
echo ""

# Ask to proceed with deployment
read -p "🚀 Ready to deploy to Vercel? (y/n): " DEPLOY

if [ "$DEPLOY" != "y" ] && [ "$DEPLOY" != "Y" ]; then
    echo ""
    echo "ℹ️  Deployment cancelled. You can deploy manually later with: vercel"
    echo ""
    echo "⚠️  Don't forget to set these environment variables in Vercel:"
    echo "   - NEXT_PUBLIC_API_URL=${API_URL}"
    echo "   - NEXT_PUBLIC_SITE_URL=<your-vercel-url>"
    echo "   - NEXTAUTH_SECRET=${NEXTAUTH_SECRET}"
    exit 0
fi

echo ""
echo "🚀 Starting Vercel deployment..."
echo ""

# Deploy to Vercel
vercel

echo ""
echo "✅ Deployment initiated!"
echo ""
echo "⚠️  IMPORTANT: Set these environment variables in Vercel Dashboard:"
echo "   1. Go to: https://vercel.com/dashboard"
echo "   2. Select your project"
echo "   3. Go to Settings → Environment Variables"
echo "   4. Add these variables:"
echo ""
echo "   NEXT_PUBLIC_API_URL=${API_URL}"
echo "   NEXT_PUBLIC_SITE_URL=<your-vercel-deployment-url>"
echo "   NEXTAUTH_URL=<your-vercel-deployment-url>"
echo "   NEXTAUTH_SECRET=${NEXTAUTH_SECRET}"
echo ""
echo "   5. Redeploy the project for changes to take effect"
echo ""
echo "📚 For detailed instructions, see: ../VERCEL_DEPLOYMENT.md"
echo ""
