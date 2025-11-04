# Quick Setup Instructions

## What Was Done

I've configured your RentHub application to connect:
- **Backend (Laravel)** on Forge: https://renthub-dji696t0.on-forge.com/
- **Frontend (Next.js)** on Vercel: https://rent-hub-six.vercel.app/
- **Database**: MySQL on Forge with the provided password

## What You Need To Do

### Step 1: Configure Backend on Forge (5 minutes)

1. Go to [Laravel Forge](https://forge.laravel.com) and open your site **renthub-dji696t0.on-forge.com**
2. Click on the **Environment** tab
3. Copy the ENTIRE contents of the file `backend/.env` (see below)
4. Paste it into the Forge Environment editor, replacing everything
5. Click **Save**

**To get the backend/.env content:**
```bash
cat backend/.env
```

Or open it in a text editor on your local machine after pulling this branch.

### Step 2: Configure Frontend on Vercel (5 minutes)

1. Go to [Vercel](https://vercel.com) and open your project **rent-hub-six**
2. Go to **Settings** → **Environment Variables**
3. Add these variables:

| Variable Name | Value |
|--------------|-------|
| `NEXT_PUBLIC_API_URL` | `https://renthub-dji696t0.on-forge.com` |
| `NEXT_PUBLIC_SITE_URL` | `https://rent-hub-six.vercel.app` |
| `NEXTAUTH_URL` | `https://rent-hub-six.vercel.app` |
| `NEXTAUTH_SECRET` | Generate with: `openssl rand -base64 32` |
| `NODE_ENV` | `production` |

**To generate NEXTAUTH_SECRET:**
```bash
openssl rand -base64 32
```

### Step 3: Deploy (2 minutes)

**On Forge:**
1. Go to your site in Forge
2. Click **Deploy Now** button

**On Vercel:**
1. Vercel will automatically redeploy when you push to main, OR
2. Go to **Deployments** → Click **...** on latest → **Redeploy**

### Step 4: Verify (2 minutes)

1. Visit https://renthub-dji696t0.on-forge.com - should load without errors
2. Visit https://rent-hub-six.vercel.app - should load the homepage
3. Check browser console - should be no CORS errors

## Files I Created

✅ **backend/.env** - Complete backend environment configuration (NOT in git for security)
✅ **frontend/.env** - Complete frontend environment configuration (NOT in git for security)  
✅ **frontend/next.config.ts** - Updated to include Forge hostname for images
✅ **PRODUCTION_DEPLOYMENT_GUIDE.md** - Detailed deployment instructions with troubleshooting

## Important Notes

⚠️ The `.env` files are NOT committed to git because they contain sensitive information (database password).

⚠️ You need to manually copy the environment variables to Forge and Vercel using their web interfaces.

⚠️ After setting up, you may need to:
- Generate APP_KEY on Forge: `php artisan key:generate`
- Run migrations: `php artisan migrate --force`
- Clear caches: `php artisan config:cache`

## Need More Help?

See the complete guide: **PRODUCTION_DEPLOYMENT_GUIDE.md**

## Quick Links

- [Laravel Forge Dashboard](https://forge.laravel.com)
- [Vercel Dashboard](https://vercel.com)
- [Backend Site](https://renthub-dji696t0.on-forge.com)
- [Frontend Site](https://rent-hub-six.vercel.app)
