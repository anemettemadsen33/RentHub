# RentHub - Quick Start Instructions

## ✅ Frontend is RUNNING NOW!

### Access Your Application

**Open your browser and visit:**
```
http://localhost:3000
```

or

```
http://10.5.0.2:3000
```

---

## 🎯 What's Working

### Frontend Server ✅
- **Status:** RUNNING
- **Port:** 3000
- **Framework:** Next.js 16 + React 19
- **UI Library:** shadcn/ui (57 components ready)
- **Styling:** Tailwind CSS v4

### Backend Server ⚠️
- **Status:** Awaiting autoload generation
- **Issue:** Composer is stuck generating autoload files
- **Solution:** See "Backend Setup" below

---

## 🚀 Commands Reference

### Frontend Commands

```powershell
# Navigate to frontend
cd C:\laragon\www\RentHub\frontend

# Install dependencies (already done)
pnpm install

# Start dev server (already running)
pnpm dev

# Build for production
pnpm build

# Start production server
pnpm start

# Lint code
pnpm lint
```

### Backend Commands

```powershell
# Navigate to backend
cd C:\laragon\www\RentHub\backend

# Install dependencies (packages downloaded, autoload pending)
composer install

# Generate app key (after autoload completes)
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve --port=8001

# Or use specific host
php artisan serve --host=0.0.0.0 --port=8001
```

---

## 🛠️ Backend Setup (To Complete)

### Option 1: Wait for Composer (Simplest)

The composer process may still be running. Check if `vendor/autoload.php` exists:

```powershell
cd C:\laragon\www\RentHub\backend
Test-Path vendor\autoload.php
```

If it exists, you can start the backend:
```powershell
php artisan key:generate
php artisan migrate
php artisan serve --port=8001
```

### Option 2: Use Docker (Recommended)

```powershell
cd C:\laragon\www\RentHub
docker-compose up backend
```

### Option 3: Restart Composer

```powershell
cd C:\laragon\www\RentHub\backend

# Kill any hanging composer processes
taskkill /F /IM composer.phar

# Try again with increased timeout
$env:COMPOSER_PROCESS_TIMEOUT=1800
composer install --no-scripts
composer dump-autoload
```

### Option 4: Manual Autoload Fix

If composer keeps hanging, you can try:

```powershell
cd C:\laragon\www\RentHub\backend

# Generate autoload without optimization
composer dump-autoload --no-dev --classmap-authoritative
```

---

## 📚 Development Guide

### Using shadcn/ui Components

All components are in `frontend/src/components/ui/`:

```tsx
// Import components
import { Button } from "@/components/ui/button"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"

// Use in your page
export default function MyPage() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Welcome to RentHub</CardTitle>
      </CardHeader>
      <CardContent>
        <Input placeholder="Search properties..." />
        <Button>Search</Button>
      </CardContent>
    </Card>
  )
}
```

### Available Components (57 total)

See full list in: `frontend/SHADCN_COMPONENTS.md`

**Popular ones:**
- Button, Card, Dialog, Input, Select, Checkbox
- Table, Tabs, Tooltip, Dropdown Menu
- Calendar, Form, Alert, Avatar, Badge
- And 42 more!

### Creating New Pages

```powershell
# Pages in Next.js 16 App Router
cd C:\laragon\www\RentHub\frontend\src\app

# Create new page
mkdir my-page
New-Item my-page\page.tsx
```

Example page.tsx:
```tsx
export default function MyPage() {
  return (
    <div className="container mx-auto p-6">
      <h1 className="text-3xl font-bold">My Page</h1>
    </div>
  )
}
```

---

## 🔧 Troubleshooting

### Frontend Issues

**Port 3000 already in use:**
```powershell
# Find process using port 3000
Get-NetTCPConnection -LocalPort 3000

# Kill the process
taskkill /F /PID <process_id>

# Or use different port
pnpm dev -- -p 3001
```

**Module not found errors:**
```powershell
cd frontend
rm -rf node_modules pnpm-lock.yaml
pnpm install
```

### Backend Issues

**Composer hanging:**
```powershell
# Increase PHP memory
php -d memory_limit=2G C:\path\to\composer.phar install

# Or edit php.ini
# memory_limit = 2G
```

**Database connection error:**
```powershell
# Check .env file
cd backend
notepad .env

# Verify database settings:
DB_CONNECTION=sqlite
DB_DATABASE=C:\laragon\www\RentHub\backend\database\database.sqlite

# Create SQLite database
New-Item database\database.sqlite
```

---

## 📊 Server Status Check

### Check if Frontend is Running

```powershell
# Test with curl
curl http://localhost:3000 -UseBasicParsing

# Or check port
Get-NetTCPConnection -LocalPort 3000 -State Listen
```

### Check if Backend is Ready

```powershell
cd C:\laragon\www\RentHub\backend

# Check vendor folder
Test-Path vendor\autoload.php

# Test artisan
php artisan --version
```

---

## 🌐 API Configuration

The frontend is configured to proxy API requests to the backend.

**Environment Variables** (in `frontend/.env.local`):

```env
NEXT_PUBLIC_API_URL=http://localhost:8001
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

Once backend is running, API calls from frontend will automatically work.

---

## 📖 Documentation Files

- **FINAL_STATUS.md** - Current status and what's working
- **TESTING_SUMMARY.md** - Complete testing results
- **SERVER_STATUS.md** - Detailed server information
- **frontend/SHADCN_COMPONENTS.md** - UI components guide
- **README.md** - Project overview
- **start-servers.ps1** - Automated server startup script

---

## 🎨 Styling Guide

### Tailwind CSS v4

The project uses the latest Tailwind CSS v4:

```tsx
<div className="flex items-center gap-4 p-6 bg-white rounded-lg shadow-md">
  <img src="/logo.png" className="w-12 h-12" />
  <h2 className="text-xl font-semibold text-gray-900">
    Property Title
  </h2>
</div>
```

### Dark Mode

```tsx
import { useTheme } from "next-themes"

export function ThemeToggle() {
  const { theme, setTheme } = useTheme()
  
  return (
    <button onClick={() => setTheme(theme === "dark" ? "light" : "dark")}>
      Toggle Theme
    </button>
  )
}
```

---

## 🚀 Deployment

### Frontend (Vercel)

```bash
# Already configured in vercel.json
# Just connect your repo to Vercel

# Build command: pnpm build
# Output directory: .next
# Install command: pnpm install
```

### Backend (Laravel Forge)

See `FORGE_DEPLOYMENT.md` for detailed instructions.

---

## ⚡ Quick Commands

```powershell
# Start frontend (already running)
cd C:\laragon\www\RentHub\frontend && pnpm dev

# Start backend (when ready)
cd C:\laragon\www\RentHub\backend && php artisan serve --port=8001

# Run both (when backend is ready)
.\start-servers.ps1
```

---

## 📞 Support

If you encounter issues:

1. Check **FINAL_STATUS.md** for current status
2. Check **SERVER_STATUS.md** for troubleshooting
3. Review logs in terminal windows
4. Check `.next` and `storage/logs` for error logs

---

## 🎉 You're All Set!

**Frontend is running at:** http://localhost:3000

Start building amazing property rental features with all 57 shadcn/ui components at your disposal!

**Happy Coding! 🚀**
