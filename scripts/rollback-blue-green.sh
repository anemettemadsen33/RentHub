#!/bin/bash
# Blue-Green Rollback Script

set -e

ENVIRONMENT=${1:-production}
NAMESPACE="renthub"

if [ "$ENVIRONMENT" == "staging" ]; then
    NAMESPACE="renthub-staging"
fi

echo "🔄 Blue-Green Rollback"
echo "Environment: $ENVIRONMENT"
echo "Namespace: $NAMESPACE"
echo ""

# Get current active
CURRENT=$(kubectl get svc backend-service -n $NAMESPACE -o jsonpath='{.spec.selector.version}')

if [ "$CURRENT" == "blue" ]; then
    PREVIOUS="green"
else
    PREVIOUS="blue"
fi

echo "Current: $CURRENT"
echo "Rolling back to: $PREVIOUS"
echo ""

# Switch traffic back
echo "Switching traffic to $PREVIOUS..."
kubectl patch svc backend-service -n $NAMESPACE \
    -p "{\"spec\":{\"selector\":{\"version\":\"$PREVIOUS\"}}}"

kubectl patch svc frontend-service -n $NAMESPACE \
    -p "{\"spec\":{\"selector\":{\"version\":\"$PREVIOUS\"}}}"

echo "✅ Rollback completed!"
echo "Active: $PREVIOUS"
echo "Inactive: $CURRENT"
