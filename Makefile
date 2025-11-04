# RentHub Makefile - Docker & Local Development
.PHONY: help install setup backend frontend test clean docker-build docker-up docker-down

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Local Development Commands
install: ## Install all dependencies (backend + frontend)
	@echo "Installing backend dependencies..."
	cd backend && composer install
	@echo "Installing frontend dependencies..."
	cd frontend && npm install

setup: ## Setup the project (install + configure)
	@echo "Setting up backend..."
	cd backend && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate
	@echo "Setting up frontend..."
	cd frontend && cp .env.example .env.local
	@echo "Setup complete!"

backend: ## Start backend server
	cd backend && php artisan serve

frontend: ## Start frontend dev server
	cd frontend && npm run dev

test: ## Run all tests
	@echo "Running backend tests..."
	cd backend && php artisan test
	@echo "Running frontend build test..."
	cd frontend && npm run build

clean: ## Clean caches and temp files
	@echo "Cleaning backend..."
	cd backend && php artisan optimize:clear
	@echo "Cleaning frontend..."
	cd frontend && rm -rf .next
	@echo "Clean complete!"

migrate: ## Run database migrations
	cd backend && php artisan migrate

fresh: ## Fresh database with migrations
	cd backend && php artisan migrate:fresh

seed: ## Seed the database
	cd backend && php artisan db:seed

lint-backend: ## Lint backend code
	cd backend && ./vendor/bin/pint

lint-frontend: ## Lint frontend code
	cd frontend && npm run lint

build-frontend: ## Build frontend for production
	cd frontend && npm run build

deploy-check: ## Check if ready for deployment
	@echo "Checking backend..."
	cd backend && composer install --no-dev --optimize-autoloader
	cd backend && php artisan config:cache
	@echo "Checking frontend..."
	cd frontend && npm run build
	@echo "Deployment check complete!"

# Docker Commands
docker-build: ## Build all Docker containers
	docker-compose build

docker-up: ## Start all Docker services
	docker-compose up -d

docker-dev: ## Start development environment
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

docker-down: ## Stop all Docker services
	docker-compose down

docker-restart: ## Restart all Docker services
	docker-compose restart

docker-logs: ## Show logs from all Docker services
	docker-compose logs -f

docker-logs-backend: ## Show backend logs
	docker-compose logs -f backend

docker-logs-frontend: ## Show frontend logs
	docker-compose logs -f frontend

docker-shell-backend: ## Access backend shell
	docker-compose exec backend sh

docker-shell-frontend: ## Access frontend shell
	docker-compose exec frontend sh

docker-db-shell: ## Access database shell
	docker-compose exec postgres psql -U postgres -d renthub

docker-migrate: ## Run database migrations in Docker
	docker-compose exec backend php artisan migrate

docker-migrate-fresh: ## Fresh database with seed in Docker
	docker-compose exec backend php artisan migrate:fresh --seed

docker-cache-clear: ## Clear all caches in Docker
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan route:clear
	docker-compose exec backend php artisan view:clear

docker-optimize: ## Optimize application in Docker
	docker-compose exec backend php artisan config:cache
	docker-compose exec backend php artisan route:cache
	docker-compose exec backend php artisan view:cache

docker-clean: ## Clean up Docker containers and volumes
	docker-compose down -v
	docker system prune -f

docker-install: ## Initial Docker installation
	@echo "Installing RentHub with Docker..."
	cp .env.example .env
	docker-compose build
	docker-compose up -d
	docker-compose exec backend composer install
	docker-compose exec backend php artisan key:generate
	docker-compose exec backend php artisan migrate --seed
	docker-compose exec frontend npm install
	@echo "Docker installation complete! Access at http://localhost:3000"

docker-ps: ## Show running Docker containers
	docker-compose ps

docker-stats: ## Show Docker container stats
	docker stats

# Kubernetes Commands
k8s-deploy-dev: ## Deploy to Kubernetes development
	pwsh -File scripts/k8s-deploy.ps1 -Environment development -Action apply

k8s-deploy-staging: ## Deploy to Kubernetes staging
	pwsh -File scripts/k8s-deploy.ps1 -Environment staging -Action apply

k8s-deploy-prod: ## Deploy to Kubernetes production
	pwsh -File scripts/k8s-deploy.ps1 -Environment production -Action apply

k8s-status: ## Show Kubernetes cluster status
	kubectl get all -n renthub

k8s-logs-backend: ## Show backend logs from Kubernetes
	kubectl logs -f deployment/backend -n renthub

k8s-logs-frontend: ## Show frontend logs from Kubernetes
	kubectl logs -f deployment/frontend -n renthub

k8s-shell-backend: ## Access backend shell in Kubernetes
	kubectl exec -it deployment/backend -n renthub -- sh

k8s-delete: ## Delete all Kubernetes resources
	kubectl delete namespace renthub

# CI/CD Commands
ci-test-backend: ## Run backend tests locally
	cd backend && php artisan test

ci-test-frontend: ## Run frontend tests locally
	cd frontend && npm test

ci-lint-backend: ## Run backend linting
	cd backend && ./vendor/bin/pint

ci-lint-frontend: ## Run frontend linting
	cd frontend && npm run lint

ci-security-scan: ## Run security scan locally
	docker run --rm -v $(pwd):/app aquasecurity/trivy fs /app

# Deployment Strategy Commands
deploy-blue-green: ## Deploy using blue-green strategy
	./scripts/deploy-blue-green.sh production latest

rollback-blue-green: ## Rollback blue-green deployment
	./scripts/rollback-blue-green.sh production

deploy-canary: ## Deploy using canary strategy
	./scripts/deploy-canary.sh production latest

# Monitoring Commands
monitoring-setup: ## Setup monitoring stack
	helm install prometheus prometheus-community/kube-prometheus-stack \
		--namespace monitoring --create-namespace

monitoring-port-forward: ## Access Grafana locally
	kubectl port-forward svc/prometheus-grafana 3000:80 -n monitoring

monitoring-alerts: ## Check active alerts
	kubectl get prometheusrule -n monitoring
