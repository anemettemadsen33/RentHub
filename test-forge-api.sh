#!/bin/bash

echo "🔍 Testing RentHub Forge API Endpoints..."
echo ""

FORGE_URL="https://renthub-tbj7yxj7.on-forge.com"
VERCEL_URL="https://rent-n91e2fmia-madsens-projects.vercel.app"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 BACKEND (Forge) Tests"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test Health Check
echo "1️⃣  Health Check:"
curl -s "$FORGE_URL/api/health" | jq -r '.status // "ERROR"' || echo "❌ Failed"
echo ""

# Test Properties API
echo "2️⃣  Properties API:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$FORGE_URL/api/v1/properties")
echo "   Status: $HTTP_CODE"
if [ "$HTTP_CODE" = "200" ]; then
    curl -s "$FORGE_URL/api/v1/properties" | jq '.data[0].title // "No properties"' || echo "❌ Parse failed"
else
    echo "   ❌ Error details:"
    curl -s "$FORGE_URL/api/v1/properties" | jq '.' || echo "Cannot parse response"
fi
echo ""

# Test Public Settings
echo "3️⃣  Public Settings:"
curl -s "$FORGE_URL/api/v1/settings/public" | jq '.data.app_name // "ERROR"' || echo "❌ Failed"
echo ""

# Test Languages
echo "4️⃣  Languages API:"
curl -s "$FORGE_URL/api/v1/languages" | jq '.data[0].code // "ERROR"' || echo "❌ Failed"
echo ""

# Test Currencies
echo "5️⃣  Currencies API:"
curl -s "$FORGE_URL/api/v1/currencies" | jq '.data[0].code // "ERROR"' || echo "❌ Failed"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 FRONTEND (Vercel) Tests"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test Vercel Homepage
echo "6️⃣  Homepage:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$VERCEL_URL")
echo "   Status: $HTTP_CODE"
if [ "$HTTP_CODE" = "401" ]; then
    echo "   ⚠️  Site is password protected"
elif [ "$HTTP_CODE" = "200" ]; then
    echo "   ✅ OK"
else
    echo "   ❌ Unexpected status"
fi
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Backend URL: $FORGE_URL"
echo "Frontend URL: $VERCEL_URL"
echo ""
