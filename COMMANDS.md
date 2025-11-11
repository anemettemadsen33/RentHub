# 🛠️ RentHub - Comenzi Utile

## 📦 Frontend (Next.js)

### Development
```bash
npm run dev              # Pornește dev server (localhost:3000)
npm run build            # Build pentru producție
npm start                # Pornește production server
npm run lint             # Rulează ESLint
npm run type-check       # Verifică TypeScript
```

### shadcn/ui
```bash
# Adaugă componente noi
npx shadcn@latest add button
npx shadcn@latest add card
npx shadcn@latest add dialog
npx shadcn@latest add select
npx shadcn@latest add table
npx shadcn@latest add form
npx shadcn@latest add tabs
npx shadcn@latest add avatar
npx shadcn@latest add badge
npx shadcn@latest add calendar
```

### Maintenance
```bash
rm -rf .next             # Șterge cache Next.js
rm -rf node_modules      # Șterge dependențele
npm install              # Reinstalează dependențele
npm update               # Update dependențe
npm outdated             # Verifică versiuni vechi
```

---

## 🔧 Backend (Laravel)

### Artisan Commands
```bash
# Development
php artisan serve                    # Pornește dev server (localhost:8000)
php artisan serve --port=8001        # Server pe alt port

# Database
php artisan migrate                  # Rulează migrații
php artisan migrate:fresh            # Drop all + migrate
php artisan migrate:fresh --seed     # Drop all + migrate + seed
php artisan migrate:rollback         # Rollback ultima migrație
php artisan migrate:reset            # Rollback toate
php artisan db:seed                  # Rulează seeders

# Cache
php artisan cache:clear              # Șterge cache aplicație
php artisan config:clear             # Șterge cache config
php artisan route:clear              # Șterge cache rute
php artisan view:clear               # Șterge cache views
php artisan optimize:clear           # Șterge toate cache-urile

php artisan config:cache             # Cache config
php artisan route:cache              # Cache rute
php artisan view:cache               # Cache views
php artisan optimize                 # Optimize pentru producție

# Queue & Jobs
php artisan queue:work               # Procesează queue
php artisan queue:work --tries=3     # Cu retry
php artisan queue:restart            # Restart workers
php artisan queue:failed             # Vezi job-uri eșuate
php artisan queue:retry all          # Retry toate job-urile

# Storage
php artisan storage:link             # Link storage public
```

### Filament Commands
```bash
php artisan make:filament-user       # Creează user admin
php artisan filament:upgrade         # Upgrade Filament
```

### Composer
```bash
composer install                     # Instalează dependențe
composer update                      # Update dependențe
composer dump-autoload               # Regenerează autoload
composer require package/name        # Instalează pachet nou
composer show                        # Vezi toate pachetele
```

---

## 🗄️ Database

### MySQL/MariaDB
```bash
# Login
mysql -u root -p

# Comenzi în MySQL
SHOW DATABASES;
USE renthub;
SHOW TABLES;
DESCRIBE properties;
SELECT * FROM users;

# Export database
mysqldump -u root -p renthub > backup.sql

# Import database
mysql -u root -p renthub < backup.sql
```

### PostgreSQL
```bash
# Login
psql -U postgres

# Comenzi în PostgreSQL
\l                  # List databases
\c renthub          # Connect to database
\dt                 # List tables
\d properties       # Describe table
SELECT * FROM users;

# Export
pg_dump renthub > backup.sql

# Import
psql renthub < backup.sql
```

---

## 🐳 Docker (Optional)

```bash
# Pornește containerele
docker-compose up -d

# Oprește containerele
docker-compose down

# Vezi log-uri
docker-compose logs -f

# Rebuild containers
docker-compose up -d --build

# Acces în container
docker-compose exec app bash
```

---

## 🚀 Deployment

### Vercel (Frontend)
```bash
# Install CLI
npm i -g vercel

# Login
vercel login

# Deploy preview
vercel

# Deploy production
vercel --prod

# Environment variables
vercel env add NEXT_PUBLIC_API_URL
vercel env ls
vercel env rm NEXT_PUBLIC_API_URL
```

### Git
```bash
# Setup
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/username/renthub.git
git push -u origin main

# Daily workflow
git status
git add .
git commit -m "Description"
git push

# Branches
git checkout -b feature-name
git checkout main
git merge feature-name
git branch -d feature-name
```

---

## 🔍 Testing & Debugging

### Frontend
```bash
# Console browser
console.log('Debug message')
console.error('Error')
console.table(data)

# React DevTools
# Instalează extension Chrome/Firefox

# Network tab
# Verifică request-uri API în DevTools
```

### Backend
```bash
# Laravel Telescope (development)
php artisan telescope:install
php artisan migrate

# Tinker (Laravel REPL)
php artisan tinker
>>> User::all()
>>> Property::count()

# Logs
tail -f storage/logs/laravel.log

# Debug în cod
dd($variable)           # Dump and die
dump($variable)         # Dump continue
logger('Message')       # Log message
```

---

## 📊 Performance

### Frontend
```bash
# Analyze bundle
npm run build
# Verifică output pentru bundle sizes

# Lighthouse audit
# Run în Chrome DevTools
```

### Backend
```bash
# Query optimization
php artisan telescope:install

# Cache all
php artisan optimize

# Profile queries
# Folosește Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev
```

---

## 🔒 Security

### Update Dependencies
```bash
# Frontend
npm audit
npm audit fix
npm update

# Backend
composer audit
composer update
```

### Environment
```bash
# Generează APP_KEY nou
php artisan key:generate

# Verifică .env
# Nu commit-a niciodată .env în git!
```

---

## 📝 Useful Scripts

### Cleanup All
```bash
# Frontend
cd frontend
rm -rf .next node_modules
npm install

# Backend
cd backend
php artisan optimize:clear
composer dump-autoload
```

### Fresh Start
```bash
# Backend
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan storage:link

# Frontend
rm -rf .next
npm install
```

### Production Build Test
```bash
# Frontend
npm run build
npm start

# Backend
php artisan optimize
php artisan config:cache
php artisan route:cache
```

---

## 🆘 Troubleshooting

### Port Already in Use
```bash
# Windows - Find process on port
netstat -ano | findstr :3000
taskkill /PID [PID] /F

# Linux/Mac
lsof -i :3000
kill -9 [PID]
```

### Permission Issues
```bash
# Laravel storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection
```bash
# Test connection
php artisan db:show

# Verify .env
cat .env | grep DB_
```

---

## 📚 Documentation

```bash
# Generate API docs
php artisan l5-swagger:generate

# Generate TypeScript types from API
# (manual sau cu tools)
```

---

## 🎯 Quick Commands Summary

### Daily Development
```bash
# Start everything
cd backend && php artisan serve &
cd frontend && npm run dev

# Commit changes
git add .
git commit -m "Message"
git push

# Update dependencies
npm update
composer update
```

### Before Deploy
```bash
# Test build
cd frontend && npm run build
cd backend && php artisan optimize

# Run tests
npm test
php artisan test
```

---

**💡 Pro Tip:** Salvează această pagină în bookmark-uri pentru acces rapid!
