# 🚀 RentHub - Quick Command Reference

## 🧪 Testing Commands

### Backend Tests
```bash
cd backend

# Run all tests
php artisan test

# Run with increased memory
php -d memory_limit=512M artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/Api/PropertyApiTest.php

# Run with coverage
php artisan test --coverage

# Run parallel tests (faster)
php artisan test --parallel

# Code quality checks
./vendor/bin/phpstan analyse
./vendor/bin/pint
./vendor/bin/pint --test  # Check without fixing
```

### Frontend Tests
```bash
cd frontend

# Run all unit tests
npm test

# Run tests in watch mode
npm run test:watch

# Run with coverage
npm test -- --coverage

# Run E2E tests
npm run e2e
npm run e2e:headed  # With browser visible

# Type checking
npm run type-check

# Linting
npm run lint
npm run lint -- --fix

# Build test
npm run build
```

### Complete Test Suite
```powershell
# Windows
.\scripts\test-all.ps1

# Linux/Mac
bash scripts/test-all.sh
```

## 🚀 Development Commands

### Backend
```bash
cd backend

# Start development server
php artisan serve

# Run queue workers
php artisan queue:work

# Watch for changes
php artisan queue:listen

# Run Reverb (WebSockets)
php artisan reverb:start

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
php artisan optimize:clear
```

### Frontend
```bash
cd frontend

# Start development server
npm run dev

# Build for production
npm run build

# Start production server
npm run start

# Analyze bundle
ANALYZE=true npm run build
```

## 🔧 Database Commands

### Migrations
```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Fresh migrations with seeds
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Seeders
```bash
# Create seeder
php artisan make:seeder TableSeeder

# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## 📦 Deployment Commands

### Laravel Forge (Backend)
```bash
# SSH into server
forge ssh

# Navigate to site
cd yourdomain.com

# Pull latest changes
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize

# Restart queue workers
php artisan queue:restart
sudo supervisorctl restart all

# Check application
php artisan about
php artisan queue:monitor
```

### Vercel (Frontend)
```bash
cd frontend

# Login to Vercel
vercel login

# Deploy preview
vercel

# Deploy to production
vercel --prod

# Check deployment status
vercel ls

# View logs
vercel logs

# Environment variables
vercel env ls
vercel env pull
vercel env add

# Promote deployment to production
vercel promote [deployment-url]

# Rollback
vercel rollback [deployment-url]
```

## 🔍 Debugging Commands

### Laravel
```bash
# Tail logs
tail -f storage/logs/laravel.log

# Tinker (REPL)
php artisan tinker

# List routes
php artisan route:list

# Show database info
php artisan db:show

# Show model info
php artisan model:show User

# Check queue jobs
php artisan queue:failed
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Next.js
```bash
# Check for unused dependencies
npx depcheck

# Check bundle size
npx @next/bundle-analyzer

# Lighthouse audit
npx lighthouse http://localhost:3000

# Check for security vulnerabilities
npm audit
npm audit fix
```

## 📊 Monitoring Commands

### Backend
```bash
# Monitor queue
php artisan queue:monitor

# Monitor Pulse (if installed)
# Visit: https://yourdomain.com/pulse

# Monitor Telescope (development only)
# Visit: http://localhost:8000/telescope

# Check server status
php artisan about

# Check storage usage
du -sh storage/
```

### Frontend
```bash
# Check build size
npm run build
# Look for "First Load JS" in output

# Lighthouse CI
npx @lhci/cli autorun

# Check dependencies
npm outdated
```

## 🔒 Security Commands

### Backend
```bash
# Generate app key
php artisan key:generate

# Generate VAPID keys (web push)
php artisan tinker
>>> \Minishlink\WebPush\VAPID::createVapidKeys()

# Clear sessions
php artisan session:flush

# Clear password reset tokens
php artisan auth:clear-resets

# Check for vulnerabilities
composer audit
```

### Frontend
```bash
# Security audit
npm audit
npm audit fix

# Update dependencies
npm update

# Check for outdated packages
npm outdated
```

## 🛠️ Maintenance Commands

### Backend
```bash
# Put in maintenance mode
php artisan down

# Exit maintenance mode
php artisan up

# Maintenance mode with secret
php artisan down --secret="your-secret"
# Access via: yourdomain.com/your-secret

# Prune models
php artisan model:prune

# Prune telescope entries
php artisan telescope:prune

# Prune failed jobs
php artisan queue:prune-failed
```

### Cache Management
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Clear all caches
php artisan optimize:clear
```

## 📝 Code Generation

### Laravel
```bash
# Controller
php artisan make:controller PropertyController --resource --api

# Model with migration and factory
php artisan make:model Property -mf

# Request
php artisan make:request StorePropertyRequest

# Resource
php artisan make:resource PropertyResource

# Service
php artisan make:service PropertyService

# Job
php artisan make:job ProcessBooking

# Event
php artisan make:event BookingCreated

# Listener
php artisan make:listener SendBookingConfirmation --event=BookingCreated

# Notification
php artisan make:notification BookingConfirmation

# Policy
php artisan make:policy PropertyPolicy --model=Property

# Test
php artisan make:test PropertyTest
php artisan make:test PropertyTest --unit
```

## 🔄 Git Commands

### Common Workflow
```bash
# Check status
git status

# Add changes
git add .

# Commit
git commit -m "Add feature"

# Push to remote
git push origin master

# Pull latest
git pull origin master

# Create branch
git checkout -b feature/new-feature

# Merge branch
git checkout master
git merge feature/new-feature

# Tag release
git tag -a v1.0.0 -m "Version 1.0.0"
git push --tags
```

## 📊 Performance Commands

### Backend
```bash
# Run performance tests
ab -n 1000 -c 10 http://localhost:8000/api/properties

# Profile with Clockwork
# Install: composer require itsgoingd/clockwork
# View: http://localhost:8000/__clockwork

# Cache entire application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Frontend
```bash
# Build and analyze
npm run build

# Measure bundle size
npx size-limit

# Check Core Web Vitals
npx unlighthouse

# Performance profiling
npm run build && npm run start
# Then open Chrome DevTools > Performance
```

## 🎯 Quick Troubleshooting

### "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### "No application encryption key"
```bash
php artisan key:generate
```

### "Database connection error"
```bash
# Check .env
cat .env | grep DB_

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### "Queue not processing"
```bash
php artisan queue:restart
sudo supervisorctl restart all
```

### "Frontend build fails"
```bash
rm -rf .next node_modules
npm install
npm run build
```

### "Type errors in frontend"
```bash
npm run type-check
# Fix errors, then:
npm run build
```

## 📚 Documentation Commands

### Generate API Docs
```bash
# Backend (OpenAPI/Swagger)
php artisan l5-swagger:generate

# Frontend (TypeDoc)
npx typedoc --out docs src/
```

## 🎉 Success!

You now have all the commands you need for development, testing, deployment, and maintenance!

**Pro Tips:**
- Add aliases to your shell for frequently used commands
- Create custom artisan commands for repeated tasks
- Use `make` or `composer scripts` for command chains
- Set up Git hooks for automated testing

---

*Last Updated: 2025-11-10*
