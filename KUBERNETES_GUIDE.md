

# ☸️ Kubernetes Orchestration Guide - RentHub

## Overview

Complete Kubernetes orchestration setup for RentHub, including high availability, auto-scaling, security policies, and production-ready configurations.

## 📋 Table of Contents

- [Architecture](#architecture)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Deployment](#deployment)
- [Monitoring](#monitoring)
- [Scaling](#scaling)
- [Security](#security)
- [Troubleshooting](#troubleshooting)

## 🏗️ Architecture

### Cluster Architecture

```
┌─────────────────────────────────────────────────────┐
│                  Load Balancer                       │
│              (Ingress Controller)                    │
└───────────────┬──────────────┬──────────────────────┘
                │              │
        ┌───────▼───────┐  ┌──▼──────────────┐
        │   Frontend    │  │    Backend      │
        │   (3-10 pods) │  │   (3-10 pods)   │
        │   Next.js     │  │   Laravel       │
        └───────────────┘  └──┬──────────────┘
                              │
        ┌─────────────────────┼─────────────────┐
        │                     │                 │
  ┌─────▼─────┐     ┌────────▼────┐   ┌───────▼──────┐
  │PostgreSQL │     │    Redis    │   │Queue Workers │
  │StatefulSet│     │ StatefulSet │   │  (2-8 pods)  │
  └───────────┘     └─────────────┘   └──────────────┘
```

### Resource Types

1. **Namespace**: Logical isolation
2. **ConfigMaps**: Non-sensitive configuration
3. **Secrets**: Sensitive data (encrypted)
4. **StatefulSets**: Databases (PostgreSQL, Redis)
5. **Deployments**: Stateless apps (Backend, Frontend)
6. **Services**: Internal networking
7. **Ingress**: External access
8. **HPA**: Horizontal Pod Autoscalers
9. **NetworkPolicy**: Security rules
10. **PersistentVolumeClaims**: Data persistence

## 📦 Prerequisites

### Required Tools

```bash
# kubectl
curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/windows/amd64/kubectl.exe"

# Or with Chocolatey
choco install kubernetes-cli

# Verify
kubectl version --client

# Kustomize (optional, built into kubectl)
kubectl kustomize --help

# Helm (optional)
choco install kubernetes-helm
```

### Kubernetes Cluster

Options:
- **Local Development**: Docker Desktop, Minikube, Kind
- **Cloud Providers**: AWS EKS, Google GKE, Azure AKS, DigitalOcean
- **On-Premise**: Rancher, OpenShift, kubeadm

### Enable Kubernetes in Docker Desktop

```
Docker Desktop → Settings → Kubernetes → Enable Kubernetes
```

## 🚀 Installation

### Step 1: Clone Repository

```bash
cd RentHub
```

### Step 2: Install Dependencies

#### Nginx Ingress Controller

```bash
# For Docker Desktop / Minikube
kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.9.0/deploy/static/provider/cloud/deploy.yaml

# Verify
kubectl get pods -n ingress-nginx
```

#### Cert-Manager (SSL)

```bash
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.13.0/cert-manager.yaml

# Verify
kubectl get pods -n cert-manager
```

#### Metrics Server (for HPA)

```bash
kubectl apply -f https://github.com/kubernetes-sigs/metrics-server/releases/latest/download/components.yaml

# Verify
kubectl get deployment metrics-server -n kube-system
```

### Step 3: Build and Push Images

```bash
# Build Docker images
docker build -t renthub/backend:latest ./backend
docker build -t renthub/frontend:latest ./frontend

# Tag for registry
docker tag renthub/backend:latest registry.example.com/renthub/backend:latest
docker tag renthub/frontend:latest registry.example.com/renthub/frontend:latest

# Push to registry
docker push registry.example.com/renthub/backend:latest
docker push registry.example.com/renthub/frontend:latest
```

## ⚙️ Configuration

### 1. Create Namespace

```bash
kubectl apply -f k8s/namespace.yaml
```

### 2. Configure Secrets

**IMPORTANT**: Never commit real secrets to git!

```bash
# Create secrets manually
kubectl create secret generic renthub-secrets \
  --from-literal=DB_USERNAME=postgres \
  --from-literal=DB_PASSWORD=$(openssl rand -base64 32) \
  --from-literal=REDIS_PASSWORD=$(openssl rand -base64 32) \
  --from-literal=APP_KEY=base64:$(openssl rand -base64 32) \
  --from-literal=JWT_SECRET=$(openssl rand -base64 64) \
  -n renthub

# Verify
kubectl get secrets -n renthub
```

### 3. Update ConfigMap

Edit `k8s/configmap.yaml` with your domain:

```yaml
data:
  APP_URL: "https://your-domain.com"
  NEXT_PUBLIC_API_URL: "https://api.your-domain.com"
```

### 4. Configure SSL

Edit `k8s/cert-manager.yaml` with your email:

```yaml
email: your-email@example.com
```

## 🚀 Deployment

### Deploy to Production

```bash
# Option 1: Apply all manifests
kubectl apply -k k8s/

# Option 2: Use kustomize with overlays
kubectl apply -k k8s/overlays/production/

# Option 3: Apply individual files
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secrets.yaml
kubectl apply -f k8s/postgres-statefulset.yaml
kubectl apply -f k8s/redis-statefulset.yaml
kubectl apply -f k8s/backend-deployment.yaml
kubectl apply -f k8s/frontend-deployment.yaml
kubectl apply -f k8s/queue-deployment.yaml
kubectl apply -f k8s/scheduler-deployment.yaml
kubectl apply -f k8s/ingress.yaml
kubectl apply -f k8s/cert-manager.yaml
kubectl apply -f k8s/network-policy.yaml
```

### Deploy to Development

```bash
kubectl apply -k k8s/overlays/development/
```

### Deploy to Staging

```bash
kubectl apply -k k8s/overlays/staging/
```

### Verify Deployment

```bash
# Check all resources
kubectl get all -n renthub

# Check pods status
kubectl get pods -n renthub

# Check services
kubectl get svc -n renthub

# Check ingress
kubectl get ingress -n renthub

# Check HPA
kubectl get hpa -n renthub

# Check PVCs
kubectl get pvc -n renthub
```

### Initial Setup

```bash
# Run migrations
kubectl exec -it deployment/backend -n renthub -- php artisan migrate --force

# Seed database (if needed)
kubectl exec -it deployment/backend -n renthub -- php artisan db:seed --force

# Clear cache
kubectl exec -it deployment/backend -n renthub -- php artisan cache:clear
kubectl exec -it deployment/backend -n renthub -- php artisan config:cache
```

## 📊 Monitoring

### View Logs

```bash
# Real-time logs
kubectl logs -f deployment/backend -n renthub
kubectl logs -f deployment/frontend -n renthub
kubectl logs -f deployment/queue-worker -n renthub

# Logs from specific pod
kubectl logs <pod-name> -n renthub

# Logs from all pods in deployment
kubectl logs -l app=backend -n renthub --tail=100
```

### Check Resource Usage

```bash
# Node resources
kubectl top nodes

# Pod resources
kubectl top pods -n renthub

# Specific pod details
kubectl describe pod <pod-name> -n renthub
```

### Monitor HPA

```bash
# Watch HPA status
kubectl get hpa -n renthub -w

# Describe HPA
kubectl describe hpa backend-hpa -n renthub
```

### Events

```bash
# Recent events
kubectl get events -n renthub --sort-by='.lastTimestamp'

# Watch events
kubectl get events -n renthub -w
```

## 📈 Scaling

### Horizontal Pod Autoscaling

**Already configured** in deployment manifests:

- **Backend**: 3-10 replicas (CPU 70%, Memory 80%)
- **Frontend**: 3-10 replicas (CPU 70%, Memory 80%)
- **Queue Worker**: 2-8 replicas (CPU 70%, Memory 80%)

### Manual Scaling

```bash
# Scale backend
kubectl scale deployment backend --replicas=5 -n renthub

# Scale frontend
kubectl scale deployment frontend --replicas=5 -n renthub

# Scale queue workers
kubectl scale deployment queue-worker --replicas=3 -n renthub
```

### Vertical Pod Autoscaling

```bash
# Install VPA (optional)
kubectl apply -f https://github.com/kubernetes/autoscaler/releases/latest/download/vertical-pod-autoscaler.yaml

# Create VPA for backend
cat <<EOF | kubectl apply -f -
apiVersion: autoscaling.k8s.io/v1
kind: VerticalPodAutoscaler
metadata:
  name: backend-vpa
  namespace: renthub
spec:
  targetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: backend
  updatePolicy:
    updateMode: "Auto"
EOF
```

### Cluster Autoscaling

Depends on cloud provider (AWS, GCP, Azure). Enable in cluster settings.

## 🔐 Security

### Network Policies

Already configured in `k8s/network-policy.yaml`:

- Backend can access PostgreSQL and Redis
- Frontend can access Backend
- PostgreSQL/Redis isolated from external access
- Ingress controller can access Frontend/Backend

### Secrets Management

**Best Practices**:

1. **Never commit secrets to git**
2. Use Kubernetes secrets with RBAC
3. Consider external secret managers:
   - **AWS Secrets Manager**
   - **Azure Key Vault**
   - **HashiCorp Vault**
   - **Sealed Secrets**

#### Using Sealed Secrets

```bash
# Install sealed-secrets controller
kubectl apply -f https://github.com/bitnami-labs/sealed-secrets/releases/download/v0.24.0/controller.yaml

# Install kubeseal CLI
choco install kubeseal

# Create sealed secret
kubectl create secret generic renthub-secrets \
  --from-literal=DB_PASSWORD=secret \
  --dry-run=client -o yaml | \
  kubeseal -o yaml > k8s/sealed-secrets.yaml

# Apply
kubectl apply -f k8s/sealed-secrets.yaml
```

### RBAC

Create service accounts with minimal permissions:

```bash
# Create service account
kubectl create serviceaccount renthub-sa -n renthub

# Create role
cat <<EOF | kubectl apply -f -
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  name: renthub-role
  namespace: renthub
rules:
- apiGroups: [""]
  resources: ["pods", "services"]
  verbs: ["get", "list"]
EOF

# Create role binding
kubectl create rolebinding renthub-binding \
  --role=renthub-role \
  --serviceaccount=renthub:renthub-sa \
  -n renthub
```

### Pod Security Standards

```yaml
# Add to namespace
apiVersion: v1
kind: Namespace
metadata:
  name: renthub
  labels:
    pod-security.kubernetes.io/enforce: restricted
    pod-security.kubernetes.io/audit: restricted
    pod-security.kubernetes.io/warn: restricted
```

## 🔄 Updates & Rollbacks

### Rolling Updates

```bash
# Update backend image
kubectl set image deployment/backend \
  backend=renthub/backend:v1.1.0 \
  -n renthub

# Update frontend image
kubectl set image deployment/frontend \
  frontend=renthub/frontend:v1.1.0 \
  -n renthub

# Watch rollout status
kubectl rollout status deployment/backend -n renthub
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

### Pause/Resume Rollout

```bash
# Pause rollout
kubectl rollout pause deployment/backend -n renthub

# Resume rollout
kubectl rollout resume deployment/backend -n renthub
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Pods Not Starting

```bash
# Check pod status
kubectl get pods -n renthub

# Describe problematic pod
kubectl describe pod <pod-name> -n renthub

# Check logs
kubectl logs <pod-name> -n renthub

# Check events
kubectl get events -n renthub --field-selector involvedObject.name=<pod-name>
```

#### 2. ImagePullBackOff

```bash
# Check image name and tag
kubectl describe pod <pod-name> -n renthub | grep Image

# Verify registry access
docker login registry.example.com

# Create image pull secret
kubectl create secret docker-registry regcred \
  --docker-server=registry.example.com \
  --docker-username=<username> \
  --docker-password=<password> \
  -n renthub

# Update deployment to use secret
kubectl patch deployment backend -n renthub \
  -p '{"spec":{"template":{"spec":{"imagePullSecrets":[{"name":"regcred"}]}}}}'
```

#### 3. CrashLoopBackOff

```bash
# Check logs
kubectl logs <pod-name> -n renthub --previous

# Check startup probe
kubectl describe pod <pod-name> -n renthub | grep -A 10 "Liveness\|Readiness"

# Exec into container (if possible)
kubectl exec -it <pod-name> -n renthub -- sh
```

#### 4. Database Connection Issues

```bash
# Test PostgreSQL connectivity
kubectl run -it --rm debug \
  --image=postgres:16-alpine \
  --restart=Never \
  -n renthub \
  -- psql -h postgres-service -U postgres -d renthub

# Check PostgreSQL logs
kubectl logs statefulset/postgres -n renthub

# Verify secret
kubectl get secret renthub-secrets -n renthub -o jsonpath='{.data.DB_PASSWORD}' | base64 -d
```

#### 5. Ingress Not Working

```bash
# Check ingress
kubectl describe ingress renthub-ingress -n renthub

# Check ingress controller
kubectl get pods -n ingress-nginx

# Check ingress controller logs
kubectl logs -n ingress-nginx -l app.kubernetes.io/name=ingress-nginx

# Test internal service
kubectl run -it --rm debug \
  --image=curlimages/curl \
  --restart=Never \
  -n renthub \
  -- curl http://frontend-service:3000
```

### Debug Commands

```bash
# Interactive shell in pod
kubectl exec -it deployment/backend -n renthub -- sh

# Port forward to local
kubectl port-forward deployment/backend 9000:9000 -n renthub
kubectl port-forward deployment/frontend 3000:3000 -n renthub

# Copy files from pod
kubectl cp renthub/<pod-name>:/var/www/storage/logs/laravel.log ./local-laravel.log

# Run artisan commands
kubectl exec -it deployment/backend -n renthub -- php artisan tinker
kubectl exec -it deployment/backend -n renthub -- php artisan queue:work --once
```

## 🎯 Best Practices

1. **Use namespaces** for environment isolation
2. **Set resource requests and limits** for all containers
3. **Implement health checks** (liveness, readiness, startup)
4. **Use secrets** for sensitive data
5. **Enable network policies** for security
6. **Implement HPA** for auto-scaling
7. **Use rolling updates** for zero-downtime deployments
8. **Monitor logs and metrics** continuously
9. **Regular backups** of StatefulSets
10. **Test in staging** before production

## 📚 Additional Resources

- [Kubernetes Documentation](https://kubernetes.io/docs/)
- [Kubectl Cheat Sheet](https://kubernetes.io/docs/reference/kubectl/cheatsheet/)
- [Kustomize Documentation](https://kustomize.io/)
- [Helm Documentation](https://helm.sh/docs/)
- [cert-manager Documentation](https://cert-manager.io/docs/)

## ✅ Status

- [x] Docker containerization
- [x] Kubernetes orchestration
- [ ] CI/CD improvements (next)
- [ ] Blue-green deployment (next)

---

**Next Steps**: Setup CI/CD pipeline for automated deployments.
