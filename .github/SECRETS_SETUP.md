# 🔐 GitHub Actions Secrets Setup Guide

## Required Secrets

Configure these secrets in GitHub: **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

### Backend Deployment (Laravel Forge)

```
FORGE_DEPLOY_WEBHOOK
```
- **Description**: Forge deployment webhook URL
- **How to get**: 
  1. Go to Laravel Forge → Sites → renthub-tbj7yxj7.on-forge.com
  2. Click "Apps" tab
  3. Find "Deploy Webhook" URL
  4. Copy the full URL (e.g., `https://forge.laravel.com/servers/.../sites/.../deploy/http?token=...`)

```
FORGE_HOST
```
- **Value**: `178.128.135.24`
- **Description**: Your Forge server IP address

```
FORGE_SSH_KEY
```
- **Description**: Private SSH key for Forge server access
- **How to get**:
  ```bash
  # Generate new SSH key (or use existing)
  ssh-keygen -t ed25519 -C "github-actions@renthub" -f ~/.ssh/github_actions_renthub
  
  # Copy private key content
  cat ~/.ssh/github_actions_renthub
  
  # Add public key to Forge
  cat ~/.ssh/github_actions_renthub.pub
  # Then add this public key in Forge → Server → SSH Keys
  ```
- **⚠️ Important**: Paste the ENTIRE private key including:
  ```
  -----BEGIN OPENSSH PRIVATE KEY-----
  ...key content...
  -----END OPENSSH PRIVATE KEY-----
  ```

### Frontend Deployment (Vercel)

```
VERCEL_TOKEN
```
- **Description**: Vercel authentication token
- **How to get**:
  1. Go to https://vercel.com/account/tokens
  2. Click "Create Token"
  3. Name: "GitHub Actions RentHub"
  4. Scope: Full Account
  5. Copy the token (it's shown only once!)

```
VERCEL_ORG_ID
```
- **Description**: Your Vercel organization/team ID
- **How to get**:
  ```bash
  # Install Vercel CLI
  npm i -g vercel
  
  # Login
  vercel login
  
  # Navigate to frontend folder
  cd frontend
  
  # Link project (if not already linked)
  vercel link
  
  # Get org ID from .vercel/project.json
  cat .vercel/project.json
  ```
- **Value format**: `team_xxxxxxxxxxxxxxxxxxxx` or `user_xxxxxxxxxxxxxxxxxxxx`

```
VERCEL_PROJECT_ID
```
- **Description**: Vercel project ID
- **How to get**: Same as above, from `.vercel/project.json`
- **Value format**: `prj_xxxxxxxxxxxxxxxxxxxx`

## Quick Setup Commands

### 1. Generate SSH Key for CI/CD
```bash
ssh-keygen -t ed25519 -C "github-actions@renthub" -f ~/.ssh/github_actions_renthub -N ""
```

### 2. Add SSH Key to Forge
```bash
# Display public key
cat ~/.ssh/github_actions_renthub.pub

# Copy this and add to Forge → Server → SSH Keys → Add SSH Key
```

### 3. Get Private Key for GitHub Secret
```bash
# Display private key (copy entire content)
cat ~/.ssh/github_actions_renthub
```

### 4. Get Vercel Credentials
```bash
cd frontend
npm i -g vercel
vercel login
vercel link
cat .vercel/project.json
```

### 5. Test SSH Connection
```bash
ssh -i ~/.ssh/github_actions_renthub forge@178.128.135.24 "echo 'SSH connection successful!'"
```

## Verification Checklist

- [ ] `FORGE_DEPLOY_WEBHOOK` - Test by triggering deployment manually
- [ ] `FORGE_HOST` - Verify IP is correct (`178.128.135.24`)
- [ ] `FORGE_SSH_KEY` - Test SSH connection works
- [ ] `VERCEL_TOKEN` - Verify token has correct permissions
- [ ] `VERCEL_ORG_ID` - Check format matches pattern
- [ ] `VERCEL_PROJECT_ID` - Check format matches pattern

## Testing GitHub Actions

### 1. Test Workflow Locally
```bash
# Install act (GitHub Actions local runner)
# Windows (with Chocolatey):
choco install act-cli

# Or with winget:
winget install nektos.act

# Run workflow
act -j backend-lint
act -j frontend-build
```

### 2. Test Individual Jobs
```bash
# Test backend lint
act -j backend-lint

# Test frontend build
act -j frontend-build

# Test deployment (requires secrets)
act -j deploy-backend --secret-file .secrets
```

### 3. Monitor First Run
1. Push to `master` branch
2. Go to GitHub → Actions tab
3. Watch workflow execution
4. Check logs for any errors

## Troubleshooting

### SSH Connection Fails
```bash
# Test connection manually
ssh -i ~/.ssh/github_actions_renthub -v forge@178.128.135.24

# Common issues:
# - Wrong IP address
# - Public key not added to Forge
# - Private key format incorrect (must include BEGIN/END lines)
```

### Vercel Deployment Fails
```bash
# Test Vercel deployment locally
cd frontend
vercel --token YOUR_VERCEL_TOKEN

# Common issues:
# - Wrong ORG_ID or PROJECT_ID
# - Token expired or insufficient permissions
# - Project not linked
```

### Forge Deployment Fails
```bash
# Check Forge deployment logs
ssh forge@178.128.135.24 "tail -100 ~/renthub-tbj7yxj7.on-forge.com/releases/000000/backend/storage/logs/deploy.log"

# Common issues:
# - Webhook URL incorrect
# - Composer dependencies fail
# - Migration errors
```

## Security Best Practices

1. **Never commit secrets** to the repository
2. **Rotate tokens** every 90 days
3. **Use separate SSH keys** for CI/CD (don't reuse personal keys)
4. **Limit token permissions** to minimum required
5. **Monitor Actions logs** for exposed secrets
6. **Enable branch protection** rules on `master`

## Next Steps

After setting up all secrets:

1. ✅ Commit and push `.github/workflows/ci-cd.yml`
2. ✅ Add all secrets in GitHub Settings
3. ✅ Test workflow by pushing to `master`
4. ✅ Monitor first deployment
5. ✅ Set up Slack/Discord notifications (optional)

## Support

If you encounter issues:
- Check GitHub Actions logs
- Verify all secrets are set correctly
- Test SSH/Vercel connections manually
- Review Forge deployment logs
