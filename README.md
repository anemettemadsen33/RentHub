# 🏠 RentHub - Property Rental Platform

> **Status**: ✅ Backend-Frontend FULLY CONNECTED & OPERATIONAL  
> **Last Updated**: 2025-11-07

A modern, full-stack property rental platform for long-term and short-term rentals with complete Laravel + Next.js integration.

## 📋 Description

RentHub is a comprehensive rental platform built with Laravel (backend) and Next.js (frontend). It supports property management, bookings, payments, real-time messaging, notifications, and multi-language/multi-currency features.

**🎯 Perfect Integration**: Backend API și Frontend sunt complet conectate prin Laravel Sanctum authentication, CORS configurat, și type-safe API service layer.

---

## ✨ Tech Stack

**Backend:**
- Laravel 11+ with Filament v4 Admin Panel
- MySQL/PostgreSQL Database
- Laravel Sanctum (API Authentication)
- Redis for caching and queues
- RESTful API with full CORS support

**Frontend:**
- Next.js 15 with App Router
- React 19 with TypeScript
- Tailwind CSS + shadcn/ui components
- Axios with request/response interceptors
- Type-safe API service layer
- React Context for auth & notifications

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- npm or yarn
- MySQL (sau SQLite pentru development)
- Redis (optional, recomandat pentru production)

---

## 🛠️ Setup & Pornire

### 1️⃣ Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configurează database în .env
# DB_DATABASE=renthub
# DB_USERNAME=root
# DB_PASSWORD=

php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Backend API: `http://localhost:8000`  
API Base: `http://localhost:8000/api/v1`

### 2️⃣ Frontend Setup

```bash
cd frontend
npm install

# .env.local este deja configurat
# NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1

npm run dev
```

Frontend: `http://localhost:3000`

### 3️⃣ Testare Conexiune

```bash
# Din root folder
.\test-connection.ps1
```

Ar trebui să vezi:
```
✅ Backend is running
✅ CORS is configured
✅ Public endpoints working
✅ Auth endpoints available
✅ Database connected
✅ Frontend .env.local configured
```

---

## 📚 Documentație Completă

### 📖 Ghiduri Principale

1. **[QUICK_START.md](QUICK_START.md)** - 🚀 Pornire rapidă & primul test
2. **[CONNECTION_STATUS.md](CONNECTION_STATUS.md)** - ✅ Status complet integrare
3. **[BACKEND_FRONTEND_CONNECTION.md](BACKEND_FRONTEND_CONNECTION.md)** - 📖 Ghid detaliat
4. **[LINKS_AND_RESOURCES.md](LINKS_AND_RESOURCES.md)** - 🔗 Link-uri & resurse

### 🧪 Testing

- **PowerShell Script**: `.\test-connection.ps1` - Testare automată
- **Browser Utils**: Console → `apiTest.testAllEndpoints()` - Test în browser
- **Manual Testing**: Vezi [BACKEND_FRONTEND_CONNECTION.md](BACKEND_FRONTEND_CONNECTION.md)

---

## 🛠️ Local Development

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The backend API will be available at `http://localhost:8000`

Admin panel (Filament): `http://localhost:8000/admin`

### Frontend Setup

**Option 1: Using setup script (Windows)**
```powershell
cd frontend
.\setup.ps1
npm run dev
```

**Option 2: Manual setup**
```bash
cd frontend
npm install
cp .env.example .env.local
# Edit .env.local with your backend URL
npm run dev
```

The frontend will be available at `http://localhost:3000`

## 📖 Full Documentation

- **Frontend Setup**: See `frontend/SETUP_COMPLETE.md`
- **Backend Setup**: See `backend/README.md`
- **Deployment Guide**: See `frontend/DEPLOYMENT.md`
- **API Documentation**: See `backend/openapi.yaml`

## ⚙️ Environment Configuration

### Backend (.env)

```env
APP_NAME=RentHub
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Frontend (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_NAME=RentHub
```

## 🌐 Deployment

### Production Setup

**Frontend → Vercel**
- Automatic deployments from GitHub
- Zero-config setup
- Global CDN

**Backend → Laravel Forge**
- One-click deployment
- Server management
- SSL certificates
- Queue workers

See `frontend/DEPLOYMENT.md` for detailed deployment instructions.

## 🔄 Continuous Integration & End-to-End Testing

The project uses GitHub Actions for a full CI/CD pipeline:

**Workflows**
- `ci.yml`: Core pipeline (backend static analysis & tests, frontend build, security audits, Docker images, deployments) + E2E full-stack job (DB/Redis + seeding + Playwright).
- `full-e2e-ci.yml`: Legacy full E2E workflow, now manual only (`workflow_dispatch`) to avoid duplicates.
- `e2e.yml`: Manual Playwright trigger (also `workflow_dispatch`).

**E2E Highlights**
- Shared Playwright helpers (`frontend/tests/e2e/helpers.ts`) provide `login`, `mockJson`, hydration readiness, and safe element interactions.
- Booking, invoices, insurance, property access, calendar, security audit, profile verification flows covered.
- JSON test report artifact plus HTML report & traces are uploaded for PRs.
- Automatic PR comment summarizes pass/fail counts & artifact names.

**Seeding**
- `Database\\Seeders\\E2ESeeder` seeds a test user, property, booking, and invoice ensuring deterministic E2E runs.

**Caching**
- Composer vendor, Node modules, and Playwright browsers cached to accelerate CI.

**To extend**
1. Add more seeds for complex scenarios (multi-bookings, varied statuses).
2. Publish HTML report via Pages or include screenshots directly in PR comment.
3. Add matrix strategy for PostgreSQL / MySQL dual-testing.
4. Include visual regression gating (already has snapshots; integrate thresholds).

## 🧪 Test Commands (Local)

```bash
# Run type-check
npm run type-check

# Run all Playwright tests
npm run e2e

# Single spec
npx playwright test tests/e2e/booking-flow.spec.ts

# Headed debug
npm run e2e:headed -- --project=chromium --trace=on
```

---

## 🎯 Features

### Implemented ✅
- User authentication (register, login, logout)
- Property listings with search
- User dashboard
- Responsive design with dark mode
- RESTful API
- Admin panel (Filament)
- CORS configured
- Toast notifications

### Coming Soon 🚧
- Property detail pages
- Booking system
- Payment integration (Stripe)
- Reviews and ratings
- Google Maps integration
- Real-time messaging
- Multi-language support
- Multi-currency support

## 📁 Project Structure

```
RentHub/
├── backend/                 # Laravel backend + Filament
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── ...
├── frontend/               # Next.js frontend
│   ├── src/
│   │   ├── app/           # Pages
│   │   ├── components/    # React components
│   │   ├── contexts/      # React contexts
│   │   ├── lib/           # Utilities
│   │   └── types/         # TypeScript types
│   ├── public/
│   └── ...
├── docker/                # Docker configuration
├── k8s/                   # Kubernetes configs
└── docs/                  # Documentation
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 💬 Support

For support and questions:
- Check documentation in `frontend/SETUP_COMPLETE.md`
- Review API docs at `backend/openapi.yaml`
- See deployment guide at `frontend/DEPLOYMENT.md`

---

**Made with ❤️ using Laravel, Filament, Next.js, and shadcn/ui**
