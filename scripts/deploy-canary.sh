#!/bin/bash
# Canary Deployment Script

set -e

ENVIRONMENT=${1:-production}
VERSION=${2:-latest}
NAMESPACE="renthub"

if [ "$ENVIRONMENT" == "staging" ]; then
    NAMESPACE="renthub-staging"
fi

echo "🐤 Canary Deployment"
echo "Environment: $ENVIRONMENT"
echo "Version: $VERSION"
echo "Namespace: $NAMESPACE"
echo ""

# Deploy canary
echo "📦 Phase 1: Deploying canary (10% traffic)..."
kubectl apply -f k8s/canary/backend-canary.yaml -n $NAMESPACE

kubectl set image deployment/backend-canary \
    backend=ghcr.io/renthub/backend:$VERSION \
    -n $NAMESPACE

# Wait for canary to be ready
kubectl rollout status deployment/backend-canary -n $NAMESPACE --timeout=5m

echo "⏳ Monitoring canary for 5 minutes..."
sleep 300

# Check metrics
echo "📊 Checking canary metrics..."
ERROR_RATE=$(kubectl exec -n $NAMESPACE deployment/backend-canary -- \
    php artisan metrics:error-rate 2>/dev/null || echo "0")

if [ "$ERROR_RATE" -gt "1" ]; then
    echo "❌ Canary error rate too high: $ERROR_RATE%"
    echo "Rolling back..."
    kubectl delete deployment/backend-canary -n $NAMESPACE
    exit 1
fi

echo "✅ Phase 1 metrics good (error rate: $ERROR_RATE%)"

# Phase 2: Increase to 50%
echo "📦 Phase 2: Increasing canary to 50% traffic..."
kubectl scale deployment/backend-canary --replicas=2 -n $NAMESPACE

echo "⏳ Monitoring for 5 more minutes..."
sleep 300

# Check metrics again
ERROR_RATE=$(kubectl exec -n $NAMESPACE deployment/backend-canary -- \
    php artisan metrics:error-rate 2>/dev/null || echo "0")

if [ "$ERROR_RATE" -gt "1" ]; then
    echo "❌ Canary error rate too high: $ERROR_RATE%"
    echo "Rolling back..."
    kubectl delete deployment/backend-canary -n $NAMESPACE
    exit 1
fi

echo "✅ Phase 2 metrics good (error rate: $ERROR_RATE%)"

# Phase 3: Full rollout
echo "📦 Phase 3: Promoting canary to full rollout..."
kubectl set image deployment/backend \
    backend=ghcr.io/renthub/backend:$VERSION \
    -n $NAMESPACE

kubectl rollout status deployment/backend -n $NAMESPACE --timeout=10m

# Clean up canary
echo "🧹 Cleaning up canary deployment..."
kubectl delete deployment/backend-canary -n $NAMESPACE

echo "✅ Canary deployment completed successfully!"
echo "Version $VERSION is now fully deployed"
