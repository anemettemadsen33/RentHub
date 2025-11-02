.PHONY: help install setup backend frontend test clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

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
