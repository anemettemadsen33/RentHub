# Quick Forge Configuration Reference

This file provides the exact configuration settings needed in Laravel Forge to fix the deployment error.

## The Problem

```
Composer could not find a composer.json file in /home/forge/renthub-dji696t0.on-forge.com/releases/58652454
```

This error occurs because the Laravel application is in the `backend/` subdirectory, not at the repository root.

## The Solution

### Step 1: Update Web Directory

In Forge, go to your site settings and update:

**Web Directory**: `/backend/public`

This tells Nginx to serve from the correct Laravel public directory.

### Step 2: Update Deployment Script

In Forge, go to **Apps** → **Deployment Script** and replace the content with:

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

# Run the deployment script
bash forge-deploy.sh
```

The `forge-deploy.sh` script at the root will handle:
- Navigation to the backend directory
- Running composer install in the backend
- Running all Laravel artisan commands
- Cache management and optimization

### Step 3: Deploy

Click **Deploy Now** in Forge. The deployment should now succeed.

## What Was Added to the Repository

1. **`/composer.json`**: Minimal composer file at root for Forge's initial check
2. **`/forge-deploy.sh`**: Smart deployment script that works with the monorepo structure
3. **`/FORGE_DEPLOYMENT.md`**: Complete deployment guide

## Verification

After deployment, verify:

1. **Check the site loads**: Visit your domain
2. **Check the logs**: No errors in `/home/forge/your-site/backend/storage/logs/laravel.log`
3. **Check the deployment log**: Look for "Deployment completed successfully!" message

## Need More Help?

See the complete guide: [FORGE_DEPLOYMENT.md](FORGE_DEPLOYMENT.md)
