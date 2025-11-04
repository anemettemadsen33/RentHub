# Kubernetes Deployment Script for RentHub (PowerShell)

param(
    [Parameter(Position=0)]
    [ValidateSet('development', 'staging', 'production')]
    [string]$Environment = 'production',
    
    [Parameter(Position=1)]
    [ValidateSet('apply', 'delete', 'status', 'logs')]
    [string]$Action = 'apply',
    
    [Parameter(Position=2)]
    [string]$Service = 'backend'
)

$ErrorActionPreference = "Stop"

# Colors
function Write-ColorOutput($ForegroundColor) {
    $fc = $host.UI.RawUI.ForegroundColor
    $host.UI.RawUI.ForegroundColor = $ForegroundColor
    if ($args) {
        Write-Output $args
    }
    $host.UI.RawUI.ForegroundColor = $fc
}

Write-ColorOutput Green "RentHub Kubernetes Deployment"
Write-Output "Environment: $Environment"
Write-Output "Action: $Action"
Write-Output ""

# Set namespace based on environment
$Namespace = switch ($Environment) {
    'development' { 'renthub-dev' }
    'staging' { 'renthub-staging' }
    'production' { 'renthub' }
}

# Check if kubectl is installed
try {
    $null = kubectl version --client 2>&1
} catch {
    Write-ColorOutput Red "kubectl not found. Please install kubectl."
    exit 1
}

# Check cluster connectivity
Write-ColorOutput Yellow "Checking cluster connectivity..."
try {
    $null = kubectl cluster-info 2>&1
    Write-ColorOutput Green "✓ Connected to cluster"
} catch {
    Write-ColorOutput Red "Cannot connect to Kubernetes cluster."
    exit 1
}

# Function to wait for deployment
function Wait-ForDeployment {
    param($DeploymentName, $Namespace)
    Write-ColorOutput Yellow "Waiting for $DeploymentName to be ready..."
    kubectl rollout status deployment/$DeploymentName -n $Namespace --timeout=5m
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput Green "✓ $DeploymentName is ready"
    }
}

# Function to wait for statefulset
function Wait-ForStatefulSet {
    param($StatefulSetName, $Namespace)
    Write-ColorOutput Yellow "Waiting for $StatefulSetName to be ready..."
    kubectl rollout status statefulset/$StatefulSetName -n $Namespace --timeout=5m
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput Green "✓ $StatefulSetName is ready"
    }
}

# Deploy based on action
switch ($Action) {
    'apply' {
        Write-ColorOutput Yellow "Deploying to $Environment environment..."
        
        # Apply manifests
        $overlay = "k8s/overlays/$Environment/"
        kubectl apply -k $overlay
        
        if ($LASTEXITCODE -ne 0) {
            Write-ColorOutput Red "Failed to apply manifests"
            exit 1
        }
        Write-ColorOutput Green "✓ Manifests applied"
        
        # Wait for StatefulSets
        Write-Output ""
        Write-ColorOutput Yellow "Waiting for databases..."
        Wait-ForStatefulSet "postgres" $Namespace
        Wait-ForStatefulSet "redis" $Namespace
        
        # Wait for Deployments
        Write-Output ""
        Write-ColorOutput Yellow "Waiting for applications..."
        Wait-ForDeployment "backend" $Namespace
        Wait-ForDeployment "frontend" $Namespace
        Wait-ForDeployment "queue-worker" $Namespace
        
        # Run migrations
        Write-Output ""
        Write-ColorOutput Yellow "Running database migrations..."
        kubectl exec -it deployment/backend -n $Namespace -- php artisan migrate --force
        Write-ColorOutput Green "✓ Migrations completed"
        
        # Show status
        Write-Output ""
        Write-ColorOutput Green "Deployment completed successfully!"
        Write-Output ""
        kubectl get all -n $Namespace
    }
    
    'delete' {
        Write-ColorOutput Red "Deleting resources from $Environment environment..."
        $confirmation = Read-Host "Are you sure? (yes/no)"
        if ($confirmation -eq 'yes') {
            $overlay = "k8s/overlays/$Environment/"
            kubectl delete -k $overlay
            Write-ColorOutput Green "✓ Resources deleted"
        } else {
            Write-Output "Cancelled"
            exit 0
        }
    }
    
    'status' {
        Write-ColorOutput Yellow "Status of $Environment environment:"
        Write-Output ""
        kubectl get all -n $Namespace
        Write-Output ""
        Write-ColorOutput Yellow "HPA Status:"
        kubectl get hpa -n $Namespace
        Write-Output ""
        Write-ColorOutput Yellow "PVC Status:"
        kubectl get pvc -n $Namespace
    }
    
    'logs' {
        Write-ColorOutput Yellow "Showing logs for $Service..."
        kubectl logs -f deployment/$Service -n $Namespace
    }
}
