# RentHub

A modern property rental platform for long-term and short-term rentals.

## Description

RentHub is a full-stack rental platform built with Laravel (backend) and Next.js (frontend). It supports property management, bookings, payments, real-time messaging, and multi-language/multi-currency features.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- npm
- MySQL/PostgreSQL (or SQLite for development)

## Local Development

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The backend will be available at http://localhost:8000

### Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

The frontend will be available at http://localhost:3000

## Environment Configuration

### Backend (.env)

Copy `backend/.env.example` to `backend/.env` and configure:

- Database credentials
- Application key (generated with `php artisan key:generate`)
- Any API keys (optional for basic setup)

### Frontend (.env.local)

Copy `frontend/.env.example` to `frontend/.env.local` and configure:

- `NEXT_PUBLIC_API_URL` - Backend API URL (default: http://localhost:8000)
- `NEXT_PUBLIC_API_BASE_URL` - API base path (default: http://localhost:8000/api)

## Tech Stack

**Backend:**
- Laravel 11+
- Filament v4 (Admin Panel)
- MySQL/PostgreSQL
- Redis (optional)

**Frontend:**
- Next.js 16
- React 19
- TypeScript
- Tailwind CSS
- shadcn/ui

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines.
