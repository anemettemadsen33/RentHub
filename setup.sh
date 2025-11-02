#!/bin/bash

# RentHub Setup Script for Linux/Mac
# This script helps set up the development environment

echo "🚀 RentHub Development Setup"
echo "================================"
echo ""

# Check if we're in the right directory
if [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    echo "❌ Error: Please run this script from the RentHub root directory"
    exit 1
fi

# Backend Setup
echo "📦 Setting up Backend..."
cd backend

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Please install Composer first."
    exit 1
fi

# Install dependencies
echo "Installing Composer dependencies..."
composer install

# Setup .env
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
    
    echo "Generating application key..."
    php artisan key:generate
fi

# Setup database
if [ ! -f "database/database.sqlite" ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

# Run migrations
echo "Running migrations..."
php artisan migrate

# Create storage link
echo "Creating storage link..."
php artisan storage:link

echo "✅ Backend setup complete!"
echo ""

# Frontend Setup
cd ../frontend
echo "📦 Setting up Frontend..."

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ NPM not found. Please install Node.js first."
    exit 1
fi

# Install dependencies
echo "Installing NPM dependencies..."
npm install

# Setup .env.local
if [ ! -f ".env.local" ]; then
    echo "Creating .env.local file..."
    cp .env.example .env.local
fi

echo "✅ Frontend setup complete!"
echo ""

# Back to root
cd ..

# Summary
echo "================================"
echo "✨ Setup Complete!"
echo ""
echo "To start development:"
echo ""
echo "Backend:"
echo "  cd backend"
echo "  php artisan serve"
echo "  (will run on http://localhost:8000)"
echo ""
echo "Frontend (in another terminal):"
echo "  cd frontend"
echo "  npm run dev"
echo "  (will run on http://localhost:3000)"
echo ""
echo "Happy coding! 🎉"
