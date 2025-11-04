#!/bin/bash
# Kubernetes Deployment Script for RentHub

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Default values
ENVIRONMENT=${1:-production}
ACTION=${2:-apply}
NAMESPACE="renthub"

echo -e "${GREEN}RentHub Kubernetes Deployment${NC}"
echo "Environment: $ENVIRONMENT"
echo "Action: $ACTION"
echo ""

# Set namespace based on environment
case $ENVIRONMENT in
  development)
    NAMESPACE="renthub-dev"
    ;;
  staging)
    NAMESPACE="renthub-staging"
    ;;
  production)
    NAMESPACE="renthub"
    ;;
  *)
    echo -e "${RED}Invalid environment: $ENVIRONMENT${NC}"
    echo "Valid options: development, staging, production"
    exit 1
    ;;
esac

# Check if kubectl is installed
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}kubectl not found. Please install kubectl.${NC}"
    exit 1
fi

# Check cluster connectivity
echo -e "${YELLOW}Checking cluster connectivity...${NC}"
if ! kubectl cluster-info &> /dev/null; then
    echo -e "${RED}Cannot connect to Kubernetes cluster.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Connected to cluster${NC}"

# Function to wait for deployment
wait_for_deployment() {
    local deployment=$1
    local namespace=$2
    echo -e "${YELLOW}Waiting for $deployment to be ready...${NC}"
    kubectl rollout status deployment/$deployment -n $namespace --timeout=5m
    echo -e "${GREEN}✓ $deployment is ready${NC}"
}

# Function to wait for statefulset
wait_for_statefulset() {
    local statefulset=$1
    local namespace=$2
    echo -e "${YELLOW}Waiting for $statefulset to be ready...${NC}"
    kubectl rollout status statefulset/$statefulset -n $namespace --timeout=5m
    echo -e "${GREEN}✓ $statefulset is ready${NC}"
}

# Deploy based on action
if [ "$ACTION" = "apply" ]; then
    echo -e "${YELLOW}Deploying to $ENVIRONMENT environment...${NC}"
    
    # Apply manifests
    if [ "$ENVIRONMENT" = "production" ]; then
        kubectl apply -k k8s/overlays/production/
    elif [ "$ENVIRONMENT" = "staging" ]; then
        kubectl apply -k k8s/overlays/staging/
    else
        kubectl apply -k k8s/overlays/development/
    fi
    
    echo -e "${GREEN}✓ Manifests applied${NC}"
    
    # Wait for StatefulSets
    echo ""
    echo -e "${YELLOW}Waiting for databases...${NC}"
    wait_for_statefulset "postgres" $NAMESPACE
    wait_for_statefulset "redis" $NAMESPACE
    
    # Wait for Deployments
    echo ""
    echo -e "${YELLOW}Waiting for applications...${NC}"
    wait_for_deployment "backend" $NAMESPACE
    wait_for_deployment "frontend" $NAMESPACE
    wait_for_deployment "queue-worker" $NAMESPACE
    
    # Run migrations
    echo ""
    echo -e "${YELLOW}Running database migrations...${NC}"
    kubectl exec -it deployment/backend -n $NAMESPACE -- php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
    
    # Show status
    echo ""
    echo -e "${GREEN}Deployment completed successfully!${NC}"
    echo ""
    kubectl get all -n $NAMESPACE
    
elif [ "$ACTION" = "delete" ]; then
    echo -e "${RED}Deleting resources from $ENVIRONMENT environment...${NC}"
    read -p "Are you sure? (yes/no): " -r
    if [[ $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        if [ "$ENVIRONMENT" = "production" ]; then
            kubectl delete -k k8s/overlays/production/
        elif [ "$ENVIRONMENT" = "staging" ]; then
            kubectl delete -k k8s/overlays/staging/
        else
            kubectl delete -k k8s/overlays/development/
        fi
        echo -e "${GREEN}✓ Resources deleted${NC}"
    else
        echo "Cancelled"
        exit 0
    fi
    
elif [ "$ACTION" = "status" ]; then
    echo -e "${YELLOW}Status of $ENVIRONMENT environment:${NC}"
    echo ""
    kubectl get all -n $NAMESPACE
    echo ""
    echo -e "${YELLOW}HPA Status:${NC}"
    kubectl get hpa -n $NAMESPACE
    echo ""
    echo -e "${YELLOW}PVC Status:${NC}"
    kubectl get pvc -n $NAMESPACE
    
elif [ "$ACTION" = "logs" ]; then
    SERVICE=${3:-backend}
    echo -e "${YELLOW}Showing logs for $SERVICE...${NC}"
    kubectl logs -f deployment/$SERVICE -n $NAMESPACE
    
else
    echo -e "${RED}Invalid action: $ACTION${NC}"
    echo "Valid actions: apply, delete, status, logs"
    exit 1
fi
