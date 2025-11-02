# RentHub

A modern rental management platform built with Laravel and Next.js.

## Project Structure

- **backend/** - Laravel API (PHP 8.2+)
- **frontend/** - Next.js application (React 19, TypeScript)

## Tech Stack

### Backend
- Laravel 11
- Filament Admin Panel
- Laravel Sanctum (API Authentication)
- SQLite/MySQL Database

### Frontend
- Next.js 16
- React 19
- TypeScript
- Tailwind CSS v4
- TanStack Query
- NextAuth.js
- React Hook Form + Zod

## Development Setup

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## Deployment

### Backend (Laravel Forge)
- Deploy to Laravel Forge
- Configure environment variables
- Set up database
- Run migrations

### Frontend (Vercel)
- Deploy to Vercel
- Configure environment variables
- Connect to backend API

## Environment Variables

### Backend (.env)
```
APP_URL=https://api.renthub.com
FRONTEND_URL=https://renthub.com
SESSION_DOMAIN=.renthub.com
SANCTUM_STATEFUL_DOMAINS=renthub.com
```

### Frontend (.env.local)
```
NEXT_PUBLIC_API_URL=https://api.renthub.com
NEXTAUTH_URL=https://renthub.com
NEXTAUTH_SECRET=your-secret-here
```

## License

Private
