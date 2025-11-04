# RentHub

A comprehensive property rental management platform built with Laravel (backend) and Next.js (frontend).

## Overview

RentHub is a full-stack property rental platform that enables property owners to list their properties and renters to find and book accommodations. The platform includes advanced features like payment processing, messaging, multi-language support, and comprehensive property management tools.

## Technology Stack

### Backend
- **Framework**: Laravel 11.31
- **Language**: PHP 8.2+
- **Authentication**: Laravel Sanctum, Laravel Socialite
- **Database**: MySQL/PostgreSQL (with SQLite for development)
- **Admin Panel**: Filament 4.0
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel

### Frontend
- **Framework**: Next.js (React)
- **Language**: TypeScript/JavaScript
- **Styling**: Tailwind CSS
- **API Communication**: REST API

### Infrastructure
- **Containerization**: Docker, Docker Compose
- **Orchestration**: Kubernetes (k8s configurations included)
- **Infrastructure as Code**: Terraform
- **CI/CD**: GitHub Actions

## Features

### Core Features
- User authentication and authorization
- Property listing and management
- Search and filtering
- Booking system
- Payment processing
- Messaging system between owners and renters
- Reviews and ratings
- Property verification
- Multi-language support (English, Romanian, Spanish, French, German)
- Multi-currency support (USD, EUR, GBP, RON)

### Advanced Features
- Social authentication (Google, Facebook, GitHub)
- Smart pricing system
- Progressive Web App (PWA) with offline support
- Performance monitoring
- Advanced security headers and rate limiting

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/PostgreSQL (or SQLite for development)
- Docker and Docker Compose (for containerized deployment)

## Installation

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/anemettemadsen33/RentHub.git
   cd RentHub
   ```

2. **Install dependencies**
   ```bash
   make install
   ```

3. **Setup the project**
   ```bash
   make setup
   ```
   This will:
   - Copy environment files
   - Generate application key
   - Create database
   - Run migrations

4. **Start the development servers**
   
   Backend (in one terminal):
   ```bash
   make backend
   ```
   
   Frontend (in another terminal):
   ```bash
   make frontend
   ```

### Docker Deployment

1. **Build containers**
   ```bash
   make docker-build
   ```

2. **Start services**
   ```bash
   make docker-up
   ```
   
   Or for development:
   ```bash
   make docker-dev
   ```

3. **Stop services**
   ```bash
   make docker-down
   ```

## Available Commands

Run `make help` to see all available commands:

### Development
- `make install` - Install all dependencies
- `make setup` - Setup the project
- `make backend` - Start backend server
- `make frontend` - Start frontend dev server
- `make test` - Run all tests
- `make clean` - Clean caches and temp files

### Database
- `make migrate` - Run database migrations
- `make fresh` - Fresh database with migrations
- `make seed` - Seed the database

### Code Quality
- `make lint-backend` - Lint backend code
- `make lint-frontend` - Lint frontend code
- `make build-frontend` - Build frontend for production

### Docker
- `make docker-build` - Build all Docker containers
- `make docker-up` - Start all Docker services
- `make docker-dev` - Start development environment
- `make docker-down` - Stop all Docker services

## Project Structure

```
RentHub/
├── backend/           # Laravel backend application
│   ├── app/          # Application code
│   ├── config/       # Configuration files
│   ├── database/     # Migrations, seeders, factories
│   ├── routes/       # API and web routes
│   └── tests/        # Backend tests
├── frontend/         # Next.js frontend application
│   ├── src/         # Source code
│   ├── public/      # Static assets
│   └── e2e/         # End-to-end tests
├── docker/          # Docker configuration files
├── k8s/             # Kubernetes deployment configs
├── terraform/       # Infrastructure as code
├── scripts/         # Deployment and utility scripts
└── docs/            # Documentation
    └── api/         # API documentation and Postman collections
```

## Environment Configuration

### Backend (.env)
Copy `backend/.env.example` to `backend/.env` and configure:
- Database credentials
- Application URL
- Mail configuration
- Payment gateway credentials
- Social authentication credentials

### Frontend (.env.local)
Copy `frontend/.env.example` to `frontend/.env.local` and configure:
- API URL
- Public keys for services

## API Documentation

API documentation and Postman collections are available in the `docs/api/` directory:
- `SECURITY_POSTMAN_COLLECTION.json` - Security-related API endpoints
- `SECURITY_POSTMAN_TESTS.json` - Security test suite

## Testing

### Backend Tests
```bash
cd backend && php artisan test
```

### Frontend Build Test
```bash
cd frontend && npm run build
```

### Run All Tests
```bash
make test
```

## Deployment

### Production Deployment (Forge + Vercel)

**Quick Start**: Get your production environment up in 15 minutes!
- **Quick Setup Guide**: [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) - Start here!
- **Step-by-Step Checklist**: [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **Complete Guide**: [PRODUCTION_DEPLOYMENT_GUIDE.md](PRODUCTION_DEPLOYMENT_GUIDE.md)

### Laravel Forge (Backend)

The backend can be easily deployed to Laravel Forge:
- **Quick Setup**: [FORGE_CONFIG.md](FORGE_CONFIG.md)
- **Complete Guide**: [FORGE_DEPLOYMENT.md](FORGE_DEPLOYMENT.md)

**Key Points**:
- Set Web Directory to `/backend/public`
- Use the provided `forge-deploy.sh` script
- Repository has a monorepo structure with backend in `backend/` directory

### Vercel (Frontend)

The frontend deploys seamlessly to Vercel:
- **Vercel Guide**: [VERCEL_DEPLOYMENT.md](VERCEL_DEPLOYMENT.md)
- Set Root Directory to `frontend`
- Configure environment variables in Vercel dashboard

### Alternative Deployment Options

**Docker Compose**:
```bash
docker-compose -f docker-compose.production.yml up -d
```

**Kubernetes**:
```bash
kubectl apply -f k8s/
```

**Pre-Deployment Check**:
```bash
make deploy-check
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions, please open an issue in the GitHub repository.
