#!/bin/bash

echo "🔍 Testing Deployment Status"
echo "=============================="
echo ""

# Test Backend API
echo "📡 Testing Backend API (Forge)..."
echo ""

echo "1. Health Check:"
HEALTH=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/health)
if [ "$HEALTH" = "200" ]; then
    echo "   ✅ Health endpoint: OK ($HEALTH)"
else
    echo "   ❌ Health endpoint: FAILED ($HEALTH)"
fi

echo ""
echo "2. Properties API:"
PROPERTIES=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/v1/properties)
if [ "$PROPERTIES" = "200" ] || [ "$PROPERTIES" = "401" ]; then
    echo "   ✅ Properties endpoint: OK ($PROPERTIES)"
    curl -s https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | head -c 200
    echo ""
else
    echo "   ❌ Properties endpoint: FAILED ($PROPERTIES)"
    echo "   Response preview:"
    curl -s https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | head -c 500
    echo ""
fi

echo ""
echo "3. API Root:"
API_ROOT=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api)
echo "   Status: $API_ROOT"

echo ""
echo "================================"
echo "📱 Testing Frontend (Vercel)..."
echo ""

FRONTEND=$(curl -s -o /dev/null -w "%{http_code}" https://rent-gvirbwqas-madsens-projects.vercel.app)
if [ "$FRONTEND" = "200" ]; then
    echo "✅ Frontend: OK ($FRONTEND)"
else
    echo "❌ Frontend: FAILED ($FRONTEND)"
fi

echo ""
echo "================================"
echo "🔍 Summary:"
echo ""

if [ "$HEALTH" = "200" ] && [ "$FRONTEND" = "200" ]; then
    echo "✅ All systems operational!"
else
    echo "❌ Some systems need attention:"
    [ "$HEALTH" != "200" ] && echo "   - Backend API needs fixing"
    [ "$FRONTEND" != "200" ] && echo "   - Frontend needs fixing"
fi

echo ""
echo "📋 Next Steps:"
echo "1. Check QUICK_FIX_DEPLOYMENT.md for manual steps"
echo "2. Verify Forge web directory is set to /public"
echo "3. Update Vercel environment variables"
echo "4. Redeploy both services"
