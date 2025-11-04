#!/bin/bash
# Blue-Green Deployment Script

set -e

ENVIRONMENT=${1:-production}
VERSION=${2:-latest}
NAMESPACE="renthub"

if [ "$ENVIRONMENT" == "staging" ]; then
    NAMESPACE="renthub-staging"
fi

echo "🔵🟢 Blue-Green Deployment"
echo "Environment: $ENVIRONMENT"
echo "Version: $VERSION"
echo "Namespace: $NAMESPACE"
echo ""

# Determine current active environment
echo "Checking current active environment..."
CURRENT=$(kubectl get svc backend-service -n $NAMESPACE -o jsonpath='{.spec.selector.version}' 2>/dev/null || echo "blue")

if [ "$CURRENT" == "blue" ]; then
    TARGET="green"
else
    TARGET="blue"
fi

echo "Current: $CURRENT"
echo "Target: $TARGET"
echo ""

# Deploy to target environment
echo "Deploying to $TARGET environment..."
kubectl set image deployment/backend-$TARGET \
    backend=ghcr.io/renthub/backend:$VERSION \
    -n $NAMESPACE

kubectl set image deployment/frontend-$TARGET \
    frontend=ghcr.io/renthub/frontend:$VERSION \
    -n $NAMESPACE

# Wait for rollout
echo "Waiting for rollout to complete..."
kubectl rollout status deployment/backend-$TARGET -n $NAMESPACE --timeout=10m
kubectl rollout status deployment/frontend-$TARGET -n $NAMESPACE --timeout=10m

# Run smoke tests
echo "Running smoke tests on $TARGET..."
sleep 30

# Test internal services
kubectl run test-pod --image=curlimages/curl --rm -i --restart=Never -n $NAMESPACE -- \
    curl -f http://backend-service-$TARGET:9000/health || {
        echo "❌ Smoke test failed on $TARGET!"
        exit 1
    }

echo "✅ Smoke tests passed!"

# Switch traffic
echo "Switching traffic to $TARGET..."
kubectl patch svc backend-service -n $NAMESPACE \
    -p "{\"spec\":{\"selector\":{\"version\":\"$TARGET\"}}}"

kubectl patch svc frontend-service -n $NAMESPACE \
    -p "{\"spec\":{\"selector\":{\"version\":\"$TARGET\"}}}"

echo "✅ Traffic switched to $TARGET!"

# Final verification
echo "Running final verification..."
sleep 10

if [ "$ENVIRONMENT" == "production" ]; then
    curl -f https://renthub.com/health || {
        echo "❌ Production health check failed! Rolling back..."
        ./scripts/rollback-blue-green.sh $ENVIRONMENT
        exit 1
    }
else
    curl -f https://staging.renthub.com/health || {
        echo "❌ Staging health check failed! Rolling back..."
        ./scripts/rollback-blue-green.sh $ENVIRONMENT
        exit 1
    }
fi

echo "✅ Blue-Green deployment completed successfully!"
echo "Active: $TARGET"
echo "Inactive: $CURRENT (kept for quick rollback)"
