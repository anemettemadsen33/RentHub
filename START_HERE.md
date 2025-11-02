# 👋 START HERE - RentHub Quick Setup

**New to this project? Start here!** This guide will get you running in 5 minutes.

## ⚡ Super Quick Start

### Step 1: Run Setup Script

**On Windows (PowerShell):**
```powershell
.\setup.ps1
```

**On Linux/Mac:**
```bash
chmod +x setup.sh
./setup.sh
```

This will:
- Install all backend dependencies (Composer)
- Install all frontend dependencies (NPM)
- Create environment files
- Generate application keys
- Create database
- Run migrations

### Step 2: Start Servers

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```
✅ Backend running at: http://localhost:8000

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```
✅ Frontend running at: http://localhost:3000

### Step 3: Create Admin User

**Terminal 3:**
```bash
cd backend
php artisan make:admin
```

Enter your admin details when prompted.

### Step 4: Test Everything

1. **Visit Frontend:** http://localhost:3000
2. **Visit Admin Panel:** http://localhost:8000/admin
3. **Login with admin credentials**
4. **Explore the admin panel**

## ✅ That's It!

You're now ready to develop! 🎉

---

## 📚 What to Read Next

1. **QUICKSTART.md** - More detailed setup guide
2. **README.md** - Full project overview
3. **PROJECT_STATUS.md** - See what's implemented
4. **CONTRIBUTING.md** - Before making changes

## 🆘 Having Issues?

### "Composer not found"
Install Composer: https://getcomposer.org/download/

### "PHP not found"
Install PHP 8.2+: https://www.php.net/downloads

### "Node not found"
Install Node.js 20+: https://nodejs.org/

### "Permission denied"
```bash
chmod -R 755 backend/storage backend/bootstrap/cache
```

### Frontend won't start
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

### Backend errors
```bash
cd backend
php artisan optimize:clear
composer dump-autoload
```

## 🎯 Quick Commands Reference

### Backend
```bash
php artisan serve          # Start server
php artisan migrate       # Run migrations
php artisan test         # Run tests
php artisan make:admin   # Create admin
```

### Frontend
```bash
npm run dev     # Start dev server
npm run build   # Build for production
npm run lint    # Lint code
```

## 📖 Full Documentation

- **QUICKSTART.md** - Detailed setup (5 min read)
- **DEPLOYMENT.md** - How to deploy (15 min read)
- **CONTRIBUTING.md** - How to contribute (10 min read)
- **PROJECT_STATUS.md** - What's done/todo (5 min read)

## 🚀 Ready to Deploy?

When you're ready for production:
1. Read **DEPLOYMENT.md**
2. Follow **DEPLOYMENT_CHECKLIST.md**
3. Deploy backend to Laravel Forge
4. Deploy frontend to Vercel

---

## 💡 Pro Tips

1. **Use the admin panel** - It's easier than writing API code
2. **Check logs** - `backend/storage/logs/laravel.log`
3. **VS Code** - Recommended extensions are in `.vscode/extensions.json`
4. **Git** - Commit often, push regularly

---

**Questions?** Check the documentation or open an issue!

**Happy Coding!** 🚀
