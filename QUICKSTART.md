# RentHub Quick Start Guide 🚀

Get your RentHub development environment up and running in minutes!

## Prerequisites ✅

- **PHP 8.2+** with extensions: mbstring, pdo, pdo_sqlite
- **Composer** (latest version)
- **Node.js 20+** with NPM
- **Git**

## Quick Setup (Automated)

### Windows
```powershell
.\setup.ps1
```

### Linux/Mac
```bash
chmod +x setup.sh
./setup.sh
```

## Manual Setup

### 1️⃣ Backend Setup (5 minutes)

```bash
cd backend

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Create storage link
php artisan storage:link
```

### 2️⃣ Frontend Setup (3 minutes)

```bash
cd frontend

# Install dependencies
npm install

# Configure environment
cp .env.example .env.local
```

## Running the Application 🏃

### Start Backend
```bash
cd backend
php artisan serve
```
Backend will run at: http://localhost:8000

### Start Frontend (in new terminal)
```bash
cd frontend
npm run dev
```
Frontend will run at: http://localhost:3000

## Create Admin User 👤

```bash
cd backend
php artisan make:admin
```

Follow the prompts to create your admin account.

Access admin panel at: http://localhost:8000/admin

## Project URLs

| Service | URL | Description |
|---------|-----|-------------|
| Frontend | http://localhost:3000 | Main application |
| Backend API | http://localhost:8000/api | REST API |
| Admin Panel | http://localhost:8000/admin | Filament admin |
| API Docs | http://localhost:8000/docs | API documentation |

## Available Features 🎯

### Current Features
- ✅ User authentication (Register/Login)
- ✅ Property listings
- ✅ Property search and filters
- ✅ Booking management
- ✅ User reviews
- ✅ Admin panel (Filament)

### API Endpoints

**Authentication**
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/user` - Get current user

**Properties**
- `GET /api/properties` - List all properties
- `GET /api/properties/{id}` - Get property details
- `POST /api/properties` - Create property (admin)
- `PUT /api/properties/{id}` - Update property (admin)
- `DELETE /api/properties/{id}` - Delete property (admin)

**Bookings**
- `GET /api/bookings` - User bookings
- `POST /api/bookings` - Create booking
- `GET /api/bookings/{id}` - Booking details
- `PUT /api/bookings/{id}` - Update booking
- `DELETE /api/bookings/{id}` - Cancel booking

**Reviews**
- `GET /api/properties/{id}/reviews` - Property reviews
- `POST /api/reviews` - Create review

## Common Commands 📝

### Backend
```bash
# Run tests
php artisan test

# Clear caches
php artisan optimize:clear

# Generate new migration
php artisan make:migration create_table_name

# Generate new model
php artisan make:model ModelName -m

# Code style fix
./vendor/bin/pint
```

### Frontend
```bash
# Type check
npx tsc --noEmit

# Lint code
npm run lint

# Build for production
npm run build

# Start production server
npm run start
```

## Troubleshooting 🔧

### Backend Issues

**"Class not found" error**
```bash
composer dump-autoload
php artisan optimize:clear
```

**"Permission denied" on storage**
```bash
chmod -R 755 storage bootstrap/cache
```

**Database locked error**
```bash
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh
```

### Frontend Issues

**"Module not found" error**
```bash
rm -rf node_modules package-lock.json
npm install
```

**Build fails**
```bash
rm -rf .next
npm run build
```

**Port 3000 already in use**
```bash
# Use different port
PORT=3001 npm run dev
```

## Next Steps 📚

1. **Read the Documentation**
   - [README.md](README.md) - Project overview
   - [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
   - [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines

2. **Explore the Code**
   - Backend: `backend/app/`
   - Frontend: `frontend/src/`
   - API Routes: `backend/routes/api.php`

3. **Deploy to Production**
   - Backend → Laravel Forge
   - Frontend → Vercel
   - Follow [DEPLOYMENT.md](DEPLOYMENT.md)

## Getting Help 💬

- 📖 Check the documentation
- 🐛 Report bugs via GitHub Issues
- 💡 Suggest features via GitHub Discussions
- 📧 Contact the team

## Development Tips 💡

1. **Use the Admin Panel** - Most CRUD operations are easier via Filament
2. **Check Logs** - `backend/storage/logs/laravel.log`
3. **Use TanStack Query Devtools** - Available in development
4. **Git Hooks** - Consider setting up pre-commit hooks
5. **Database Seeding** - Create seeders for test data

## Quick Test 🧪

Verify everything works:

```bash
# Test backend
cd backend
php artisan test

# Test frontend (in new terminal)
cd frontend
npm run build
```

If both succeed, you're ready to develop! 🎉

---

**Happy Coding!** 🚀

For detailed information, check the main [README.md](README.md)
