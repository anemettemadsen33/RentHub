# Deploying RentHub Frontend to Vercel

This guide will help you deploy the RentHub frontend to Vercel.

## Prerequisites

1. A [Vercel account](https://vercel.com/signup) (free tier is sufficient)
2. Your backend API deployed and accessible (e.g., on Railway, Heroku, or DigitalOcean)
3. Git repository pushed to GitHub

## Deployment Steps

### Option 1: Deploy via Vercel Dashboard (Recommended)

1. **Go to Vercel Dashboard**
   - Visit [vercel.com](https://vercel.com)
   - Log in with your account

2. **Import Project**
   - Click "Add New..." → "Project"
   - Select your GitHub repository: `anemettemadsen33/RentHub`
   - Vercel will automatically detect it's a Next.js project

3. **Configure Project**
   - **Framework Preset**: Next.js (auto-detected)
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build` (default)
   - **Output Directory**: `.next` (default)
   - **Install Command**: `npm install` (default)

4. **Set Environment Variables**
   
   Add these environment variables in the Vercel dashboard:

   ```
   NEXT_PUBLIC_API_URL=https://your-backend-api.com
   NEXT_PUBLIC_SITE_URL=https://your-vercel-app.vercel.app
   NEXT_PUBLIC_AMP_ENABLED=false
   NEXTAUTH_URL=https://your-vercel-app.vercel.app
   NEXTAUTH_SECRET=your-random-secret-key-here
   ```

   **Important**: Replace the URLs with your actual backend API and Vercel deployment URLs.

5. **Deploy**
   - Click "Deploy"
   - Vercel will build and deploy your application
   - Wait for deployment to complete (~2-3 minutes)

6. **Access Your App**
   - Once deployed, Vercel will provide you with a URL like: `https://rent-hub-xxxxx.vercel.app`
   - Visit the URL to see your deployed application

### Option 2: Deploy via Vercel CLI

1. **Install Vercel CLI**
   ```bash
   npm install -g vercel
   ```

2. **Login to Vercel**
   ```bash
   vercel login
   ```

3. **Deploy from Frontend Directory**
   ```bash
   cd frontend
   vercel
   ```

4. **Follow the Prompts**
   - Set up and deploy? `Y`
   - Which scope? Select your account
   - Link to existing project? `N` (first time) or `Y` (subsequent deploys)
   - Project name? `renthub-frontend` (or your preferred name)
   - Directory? `./` (current directory)

5. **Set Environment Variables**
   ```bash
   vercel env add NEXT_PUBLIC_API_URL
   vercel env add NEXT_PUBLIC_SITE_URL
   vercel env add NEXTAUTH_SECRET
   ```

6. **Deploy to Production**
   ```bash
   vercel --prod
   ```

## Environment Variables Explained

| Variable | Description | Example |
|----------|-------------|---------|
| `NEXT_PUBLIC_API_URL` | Your Laravel backend API URL | `https://api.renthub.com` |
| `NEXT_PUBLIC_SITE_URL` | Your Vercel deployment URL | `https://renthub.vercel.app` |
| `NEXTAUTH_URL` | Same as SITE_URL (for authentication) | `https://renthub.vercel.app` |
| `NEXTAUTH_SECRET` | Random secret for NextAuth (generate with `openssl rand -base64 32`) | `your-secret-key` |
| `NEXT_PUBLIC_MAPBOX_TOKEN` | Mapbox API token (optional, for maps) | `pk.xxx...` |

## Backend Configuration

Don't forget to configure your Laravel backend:

1. **Update CORS Settings** in `backend/config/cors.php`:
   ```php
   'allowed_origins' => [
       'https://your-vercel-app.vercel.app',
   ],
   ```

2. **Update Sanctum Configuration** in `backend/config/sanctum.php`:
   ```php
   'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'your-vercel-app.vercel.app')),
   ```

3. **Set Environment Variables** in your backend:
   ```
   FRONTEND_URL=https://your-vercel-app.vercel.app
   SANCTUM_STATEFUL_DOMAINS=your-vercel-app.vercel.app
   ```

## Automatic Deployments

Vercel automatically deploys:
- **Production**: Every push to `main` branch
- **Preview**: Every push to other branches and pull requests

## Custom Domain (Optional)

1. Go to your project settings in Vercel
2. Navigate to "Domains"
3. Add your custom domain (e.g., `renthub.com`)
4. Follow Vercel's instructions to configure DNS

## Troubleshooting

### Build Fails

- Check build logs in Vercel dashboard
- Ensure all dependencies are in `package.json`
- Verify environment variables are set correctly

### API Connection Issues

- Verify `NEXT_PUBLIC_API_URL` is correct
- Check CORS settings in backend
- Ensure backend is accessible publicly

### Authentication Issues

- Verify `NEXTAUTH_SECRET` is set
- Check `NEXTAUTH_URL` matches your deployment URL
- Ensure Sanctum is configured correctly in backend

## Performance Optimization

Vercel automatically provides:
- ✅ Global CDN
- ✅ Automatic HTTPS
- ✅ Image optimization
- ✅ Edge caching
- ✅ Serverless functions

## Monitoring

- View deployment logs in Vercel dashboard
- Monitor performance via Vercel Analytics (optional)
- Set up error tracking (Sentry, LogRocket, etc.)

## Support

- [Vercel Documentation](https://vercel.com/docs)
- [Next.js Deployment Guide](https://nextjs.org/docs/deployment)
- [RentHub GitHub Issues](https://github.com/anemettemadsen33/RentHub/issues)

---

**Ready to Deploy?** Start with Option 1 (Vercel Dashboard) - it's the easiest way to get started!
