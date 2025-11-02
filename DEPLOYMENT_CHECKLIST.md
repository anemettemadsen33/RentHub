# RentHub Deployment Checklist ✅

Use this checklist to ensure a smooth deployment to production.

## Pre-Deployment Setup

### Repository Setup
- [ ] Repository created on GitHub/GitLab
- [ ] Repository is private (if needed)
- [ ] All code pushed to repository
- [ ] Main branch protected
- [ ] Required reviewers set

### Backend Domain & Hosting
- [ ] Laravel Forge account created
- [ ] Server provisioned (minimum requirements met)
- [ ] Domain purchased and configured (e.g., api.renthub.com)
- [ ] DNS records pointing to Forge server
- [ ] SSL certificate configured (Let's Encrypt)

### Frontend Hosting
- [ ] Vercel account created
- [ ] Domain purchased (e.g., renthub.com)
- [ ] Domain DNS configured

### Database
- [ ] Database server chosen (MySQL recommended)
- [ ] Database created on server
- [ ] Database user created with appropriate permissions
- [ ] Database credentials documented securely

### Email Service
- [ ] Email service provider chosen (Mailgun, SendGrid, etc.)
- [ ] Account created and verified
- [ ] SMTP credentials obtained
- [ ] Sender domain verified

## Backend Deployment (Laravel Forge)

### Server Setup
- [ ] Create site on Forge with domain name
- [ ] PHP version set to 8.2 or higher
- [ ] Web directory set to `/public`
- [ ] Site deployed successfully

### Repository Connection
- [ ] Repository connected to Forge site
- [ ] Branch set to `main`
- [ ] SSH key added to repository (if private)
- [ ] Quick Deploy enabled

### Environment Configuration
- [ ] `.env` file created via Forge interface
- [ ] Copy values from `backend/.env.production`
- [ ] `APP_KEY` generated (`php artisan key:generate`)
- [ ] `APP_ENV` set to `production`
- [ ] `APP_DEBUG` set to `false`
- [ ] `APP_URL` set to your domain (https://api.renthub.com)
- [ ] Database credentials configured
- [ ] `FRONTEND_URL` set (https://renthub.com)
- [ ] `SANCTUM_STATEFUL_DOMAINS` configured
- [ ] `SESSION_DOMAIN` set (.renthub.com)
- [ ] `SESSION_SECURE_COOKIE` set to `true`
- [ ] Mail credentials configured
- [ ] AWS S3 credentials (if using file uploads)

### Database Setup
- [ ] SSH into server
- [ ] Run `php artisan migrate`
- [ ] Create storage link: `php artisan storage:link`
- [ ] Set proper permissions: `chmod -R 755 storage bootstrap/cache`

### Deploy Script
- [ ] Deploy script updated in Forge
- [ ] Copy contents from `backend/forge-deploy.sh`
- [ ] Test deploy script works

### Additional Configuration
- [ ] Queue worker configured (if needed)
  - Connection: database
  - Queue: default
  - Processes: 1
  - Max Tries: 3
- [ ] Scheduler configured (if needed)
  - Add cron job in Forge
- [ ] SSL redirect enabled
- [ ] Environment cached: `php artisan config:cache`

### Admin User
- [ ] Create first admin user
  - SSH into server
  - Run: `php artisan make:admin`
- [ ] Test admin login at https://api.renthub.com/admin

## Frontend Deployment (Vercel)

### Project Setup
- [ ] Log in to Vercel
- [ ] Import repository
- [ ] Root directory set to `frontend`
- [ ] Framework preset: Next.js
- [ ] Build command: `npm run build`
- [ ] Output directory: `.next`

### Environment Variables
- [ ] `NEXT_PUBLIC_API_URL` = https://api.renthub.com
- [ ] `NEXTAUTH_URL` = https://renthub.com
- [ ] `NEXTAUTH_SECRET` = (generate with: `openssl rand -base64 32`)
- [ ] `API_URL` = https://api.renthub.com

### Domain Configuration
- [ ] Add custom domain in Vercel
- [ ] Add www subdomain (optional)
- [ ] DNS records configured
- [ ] SSL certificate active

### Build & Deploy
- [ ] Initial deployment triggered
- [ ] Build successful
- [ ] Site accessible at domain
- [ ] No build errors in logs

## Post-Deployment Testing

### Backend Testing
- [ ] API accessible at https://api.renthub.com
- [ ] Health check endpoint works: https://api.renthub.com/up
- [ ] Admin panel accessible: https://api.renthub.com/admin
- [ ] Admin login works
- [ ] CORS working (check browser console)

### Frontend Testing
- [ ] Site accessible at https://renthub.com
- [ ] Homepage loads correctly
- [ ] Images loading
- [ ] Navigation working

### API Integration
- [ ] Frontend can connect to backend API
- [ ] Register new user works
- [ ] Login works
- [ ] Logout works
- [ ] Protected routes work
- [ ] Data fetching works

### Authentication Flow
- [ ] Register form validation works
- [ ] Login form validation works
- [ ] Session persists on refresh
- [ ] Logout clears session
- [ ] Protected routes redirect to login

### CRUD Operations
- [ ] Create property (admin)
- [ ] Read properties list
- [ ] Update property (admin)
- [ ] Delete property (admin)
- [ ] Create booking
- [ ] View bookings
- [ ] Create review
- [ ] View reviews

## Security Checks

### SSL & HTTPS
- [ ] SSL certificate active on both domains
- [ ] HTTP redirects to HTTPS
- [ ] No mixed content warnings
- [ ] Secure cookies working

### Authentication
- [ ] CSRF protection working
- [ ] XSS protection enabled
- [ ] SQL injection prevented (using Eloquent)
- [ ] Sanctum tokens working

### Environment
- [ ] `.env` files not committed to git
- [ ] Sensitive data in environment variables
- [ ] API keys secure
- [ ] Database credentials secure

### Headers & Cookies
- [ ] Security headers configured
- [ ] Cookie settings correct (secure, httpOnly)
- [ ] CORS settings correct
- [ ] Session settings secure

## Performance Checks

### Backend
- [ ] API response times < 200ms
- [ ] Database queries optimized
- [ ] Config cached: `php artisan config:cache`
- [ ] Routes cached: `php artisan route:cache`
- [ ] Views cached: `php artisan view:cache`

### Frontend
- [ ] Lighthouse score > 90
- [ ] First Contentful Paint < 1.5s
- [ ] Time to Interactive < 3s
- [ ] No console errors
- [ ] Images optimized

## Monitoring & Logging

### Backend Logs
- [ ] Laravel logs accessible
- [ ] Error reporting configured
- [ ] Log rotation set up

### Error Tracking
- [ ] Error tracking service integrated (Sentry, Bugsnag, etc.) [Optional]
- [ ] Email notifications for errors [Optional]

### Uptime Monitoring
- [ ] Uptime monitoring service configured [Optional]
- [ ] Alert emails configured [Optional]

## Backup & Recovery

### Database Backups
- [ ] Automated database backups enabled in Forge
- [ ] Backup frequency set (daily recommended)
- [ ] Backup retention configured
- [ ] Test backup restoration

### File Backups
- [ ] Storage files backed up (if not using S3)
- [ ] Backup schedule configured

### Disaster Recovery
- [ ] Recovery procedure documented
- [ ] Rollback procedure tested

## Documentation

### Internal Documentation
- [ ] Deployment credentials documented (securely)
- [ ] API endpoints documented
- [ ] Admin procedures documented
- [ ] Emergency contact list created

### User Documentation
- [ ] User guide created (if needed)
- [ ] FAQ page created
- [ ] Support contact information added

## Continuous Deployment

### Automated Deployment
- [ ] GitHub Actions workflow configured
- [ ] Tests run on every commit
- [ ] Forge quick deploy enabled
- [ ] Vercel auto-deploy enabled

### Version Control
- [ ] Git tags for releases
- [ ] CHANGELOG.md updated
- [ ] Release notes created

## Final Checks

### Business Requirements
- [ ] All required features working
- [ ] Stakeholder approval obtained
- [ ] User acceptance testing completed

### Legal & Compliance
- [ ] Privacy policy added (if collecting user data)
- [ ] Terms of service added
- [ ] GDPR compliance checked (if applicable)
- [ ] Cookie consent banner (if needed)

### Go-Live
- [ ] Maintenance mode disabled
- [ ] Cache cleared and warmed
- [ ] Final smoke test
- [ ] Team notified
- [ ] Monitoring active

## Post-Launch

### Week 1
- [ ] Monitor error logs daily
- [ ] Check performance metrics
- [ ] Collect user feedback
- [ ] Fix critical bugs

### Month 1
- [ ] Review analytics
- [ ] Performance optimization
- [ ] Security audit
- [ ] Backup integrity check

---

## Emergency Contacts

**Backend Issues:**
- Forge Support: https://forge.laravel.com/support
- Server Admin: [Contact]

**Frontend Issues:**
- Vercel Support: https://vercel.com/support
- Frontend Dev: [Contact]

**Database Issues:**
- Database Admin: [Contact]

---

## Rollback Procedure

If something goes wrong:

### Backend Rollback
```bash
# SSH into server
forge ssh api.renthub.com

# View commits
git log --oneline

# Rollback to previous version
git reset --hard <previous-commit-hash>

# Run deploy
bash forge-deploy.sh
```

### Frontend Rollback
1. Go to Vercel dashboard
2. Navigate to Deployments
3. Find previous working deployment
4. Click "..." → "Promote to Production"

---

## Notes

- Keep this checklist updated
- Document any deviations from standard procedure
- Share lessons learned with team
- Update deployment documentation based on experience

---

**Deployment Date:** _____________

**Deployed By:** _____________

**Sign-off:** _____________

---

*This checklist ensures nothing is missed during deployment. Check off each item as you complete it.*
