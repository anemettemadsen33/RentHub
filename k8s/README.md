# Kubernetes Manifests

This directory contains Kubernetes manifests for deploying RentHub to a Kubernetes cluster.

## Structure

```
k8s/
├── namespace.yaml                 # Namespace definition
├── configmap.yaml                 # Application configuration
├── secrets.yaml                   # Sensitive data (template)
├── postgres-statefulset.yaml      # PostgreSQL database
├── redis-statefulset.yaml         # Redis cache
├── backend-deployment.yaml        # Laravel backend with HPA
├── frontend-deployment.yaml       # Next.js frontend with HPA
├── queue-deployment.yaml          # Queue workers with HPA
├── scheduler-deployment.yaml      # Scheduler cron jobs
├── ingress.yaml                   # Ingress configuration
├── cert-manager.yaml              # SSL certificates
├── network-policy.yaml            # Network security policies
├── kustomization.yaml             # Kustomize base
└── overlays/
    ├── development/               # Dev environment
    ├── staging/                   # Staging environment
    └── production/                # Production environment
```

## Quick Start

### Prerequisites

- Kubernetes cluster (v1.24+)
- kubectl configured
- Helm 3 (optional, for some dependencies)

### Deploy Base

```bash
# Apply all manifests
kubectl apply -k k8s/

# Or use kustomize
kustomize build k8s/ | kubectl apply -f -
```

### Deploy to Specific Environment

```bash
# Development
kubectl apply -k k8s/overlays/development/

# Staging
kubectl apply -k k8s/overlays/staging/

# Production
kubectl apply -k k8s/overlays/production/
```

## Configuration

### Secrets

Before deploying, create actual secrets:

```bash
# Create namespace first
kubectl create namespace renthub

# Create secrets
kubectl create secret generic renthub-secrets \
  --from-literal=DB_USERNAME=postgres \
  --from-literal=DB_PASSWORD=your-secure-password \
  --from-literal=REDIS_PASSWORD=your-redis-password \
  --from-literal=APP_KEY=base64:your-app-key \
  --from-literal=JWT_SECRET=your-jwt-secret \
  -n renthub
```

### SSL Certificates

Install cert-manager:

```bash
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.13.0/cert-manager.yaml
```

Then apply cert-manager configuration:

```bash
kubectl apply -f k8s/cert-manager.yaml
```

## Monitoring

### Check Deployment Status

```bash
# All resources in namespace
kubectl get all -n renthub

# Deployments
kubectl get deployments -n renthub

# Pods
kubectl get pods -n renthub

# Services
kubectl get services -n renthub

# HPA status
kubectl get hpa -n renthub
```

### View Logs

```bash
# Backend logs
kubectl logs -f deployment/backend -n renthub

# Frontend logs
kubectl logs -f deployment/frontend -n renthub

# Queue worker logs
kubectl logs -f deployment/queue-worker -n renthub
```

### Exec into Pod

```bash
# Backend shell
kubectl exec -it deployment/backend -n renthub -- sh

# Run migrations
kubectl exec -it deployment/backend -n renthub -- php artisan migrate
```

## Scaling

### Manual Scaling

```bash
# Scale backend
kubectl scale deployment backend --replicas=5 -n renthub

# Scale frontend
kubectl scale deployment frontend --replicas=5 -n renthub

# Scale queue workers
kubectl scale deployment queue-worker --replicas=3 -n renthub
```

### Auto-scaling (HPA)

HPA is already configured in the manifests. It will automatically scale based on CPU/memory usage.

```bash
# Check HPA status
kubectl get hpa -n renthub

# Describe HPA
kubectl describe hpa backend-hpa -n renthub
```

## Updates

### Rolling Update

```bash
# Update backend image
kubectl set image deployment/backend backend=renthub/backend:v1.1.0 -n renthub

# Update frontend image
kubectl set image deployment/frontend frontend=renthub/frontend:v1.1.0 -n renthub
```

### Rollback

```bash
# View rollout history
kubectl rollout history deployment/backend -n renthub

# Rollback to previous version
kubectl rollout undo deployment/backend -n renthub

# Rollback to specific revision
kubectl rollout undo deployment/backend --to-revision=2 -n renthub
```

## Troubleshooting

### Pod Not Starting

```bash
# Describe pod
kubectl describe pod <pod-name> -n renthub

# Check events
kubectl get events -n renthub --sort-by='.lastTimestamp'
```

### Connection Issues

```bash
# Test database connectivity
kubectl run -it --rm debug --image=postgres:16-alpine --restart=Never -n renthub -- psql -h postgres-service -U postgres

# Test Redis connectivity
kubectl run -it --rm debug --image=redis:7-alpine --restart=Never -n renthub -- redis-cli -h redis-service
```

### Resource Issues

```bash
# Check resource usage
kubectl top nodes
kubectl top pods -n renthub

# Describe node
kubectl describe node <node-name>
```

## Cleanup

```bash
# Delete all resources in namespace
kubectl delete namespace renthub

# Or delete specific resources
kubectl delete -k k8s/
```
