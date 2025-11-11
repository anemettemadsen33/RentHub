# 🚀 Automated CI/CD Deployment Guide

## Overview

This project uses **GitHub Actions** for automated deployment to:
- **Backend**: Laravel Forge (via SSH)
- **Frontend**: Vercel (via Vercel CLI)

Every push to `master` automatically deploys both backend and frontend!

---

## 🔧 Setup Instructions

### 1. Get Forge SSH Key

**Option A - Generate new SSH key for deployment:**

```bash
# On your local machine
ssh-keygen -t rsa -b 4096 -C "github-actions@renthub"
# Save as: ~/.ssh/github_actions_forge

# Copy public key
cat ~/.ssh/github_actions_forge.pub
```

**Then in Forge:**
1. Go to **Forge → Servers → Rental-Platform**
2. Click **SSH Keys**
3. Add the public key

**Option B - Use existing Forge key:**
1. **Forge → Account → SSH Keys**
2. Download your existing key

---

### 2. Get Vercel Token

1. Go to **Vercel Dashboard**: https://vercel.com/account/tokens
2. Click **"Create Token"**
3. Name: `GitHub Actions Deploy`
4. Scope: Full Account
5. Copy the token (save it - you won't see it again!)

---

### 3. Add GitHub Secrets

1. Go to **GitHub Repository**: https://github.com/anemettemadsen33/RentHub
2. Click **Settings → Secrets and variables → Actions**
3. Click **"New repository secret"**

Add these secrets:

**FORGE_SERVER_IP**
```
178.128.135.24
```

**FORGE_SSH_KEY**
```
-----BEGIN OPENSSH PRIVATE KEY-----
[Your SSH private key content]
-----END OPENSSH PRIVATE KEY-----
```

**VERCEL_TOKEN**
```
[Your Vercel token from step 2]
```

**VERCEL_ORG_ID** (optional)
```
[Your Vercel organization ID]
```

**VERCEL_PROJECT_ID** (optional)
```
[Your Vercel project ID]
```

To get Vercel IDs:
```bash
cd frontend
npx vercel login
npx vercel link
# This creates .vercel/project.json with IDs
cat .vercel/project.json
```

---

## 🚀 How It Works

### Automatic Deployment

Every push to `master` branch triggers:

1. **Backend Deploy**:
   - SSH into Forge server
   - Pull latest code
   - Install dependencies
   - Clear caches
   - Optimize for production

2. **Frontend Deploy**:
   - Install Vercel CLI
   - Build Next.js app
   - Deploy to Vercel production

3. **Health Check**:
   - Test backend API
   - Test frontend homepage
   - Report success/failure

### Manual Deployment

Trigger deployment manually:
1. Go to **Actions** tab in GitHub
2. Click **"Deploy to Production"**
3. Click **"Run workflow"** → **"Run workflow"**

---

## 📊 Monitoring Deployments

### GitHub Actions

View deployment status:
1. Go to **GitHub → Actions**
2. Click on latest workflow run
3. See logs for each step

### Forge

View backend deployment:
1. **Forge → Sites → renthub-mnnzqvzb.on-forge.com**
2. Click **"Deployments"**
3. See deployment history

### Vercel

View frontend deployment:
1. **Vercel Dashboard → rent-hub**
2. Click **"Deployments"**
3. See deployment history and logs

---

## 🔍 Troubleshooting

### Backend deployment fails

**Check SSH connection:**
```bash
ssh -i ~/.ssh/github_actions_forge forge@178.128.135.24
```

**Check GitHub Actions logs:**
- GitHub → Actions → Failed workflow → deploy-backend job

**Common issues:**
- SSH key not added to Forge
- Wrong server IP in secrets
- Permissions issue on server

### Frontend deployment fails

**Check Vercel token:**
```bash
vercel whoami --token YOUR_TOKEN
```

**Check GitHub Actions logs:**
- GitHub → Actions → Failed workflow → deploy-frontend job

**Common issues:**
- Invalid Vercel token
- Project not linked
- Build errors (check Vercel dashboard)

### Health check fails

**Backend health check:**
```bash
curl https://renthub-mnnzqvzb.on-forge.com/api/health
```

**Frontend health check:**
```bash
curl https://rent-hub-git-master-madsens-projects.vercel.app
```

---

## 🛠️ Local Testing

### Test backend deployment script locally

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan test
```

### Test frontend build locally

```bash
cd frontend
npm install
npm run build
npm run start
```

---

## 📝 Deployment Workflow

```
┌─────────────────────────────────────────────┐
│  Developer pushes to master                 │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│  GitHub Actions triggers                    │
└─────────────┬───────────────────────────────┘
              │
        ┌─────┴─────┐
        ▼           ▼
┌───────────┐ ┌───────────┐
│  Backend  │ │ Frontend  │
│  (Forge)  │ │ (Vercel)  │
└─────┬─────┘ └─────┬─────┘
      │             │
      └──────┬──────┘
             ▼
    ┌────────────────┐
    │ Health Check   │
    └────────┬───────┘
             │
        ┌────┴────┐
        ▼         ▼
    ✅ Success  ❌ Fail
```

---

## ⚡ Quick Commands

**Deploy everything:**
```bash
git add .
git commit -m "Update feature"
git push origin master
# Wait for GitHub Actions to complete
```

**Check deployment status:**
```bash
# Via GitHub CLI (if installed)
gh run list --limit 5

# Or visit GitHub Actions tab
open https://github.com/anemettemadsen33/RentHub/actions
```

**Rollback deployment:**
```bash
# Find previous commit
git log --oneline -5

# Rollback
git revert HEAD
git push origin master
# GitHub Actions will deploy previous version
```

---

## 🎯 Benefits

✅ **Automated**: Push to master = auto-deploy
✅ **Fast**: Deploys in ~2-3 minutes
✅ **Safe**: Health checks prevent bad deploys
✅ **Trackable**: Full deployment history
✅ **Rollback**: Easy to revert changes
✅ **Consistent**: Same process every time

---

## 📚 Additional Resources

- **GitHub Actions Docs**: https://docs.github.com/en/actions
- **Forge SSH Deployment**: https://forge.laravel.com/docs
- **Vercel CLI**: https://vercel.com/docs/cli

---

**Setup complete! Now every push to master auto-deploys! 🚀**
