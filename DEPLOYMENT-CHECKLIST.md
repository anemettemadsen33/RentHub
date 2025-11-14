# 🚀 RENTHUB DEPLOYMENT CHECKLIST

## 📋 PRE-DEPLOYMENT VERIFICATION

### ✅ Backend (Laravel Forge)
- [ ] Database credentials configured in `.env`
- [ ] Redis server installed and running
- [ ] SSL certificate configured
- [ ] Domain pointing to server IP
- [ ] Mailgun/SMTP credentials added
- [ ] API keys for external services added
- [ ] Sentry DSN configured (optional)

### ✅ Frontend (Vercel)
- [ ] Environment variables configured in Vercel dashboard
- [ ] Domain configured
- [ ] Build command set to `npm run build`
- [ ] Output directory set to `dist`

---

## 🔧 BACKEND DEPLOYMENT STEPS

### 1. Server Setup (Laravel Forge)
```bash
# SSH into your Forge server
ssh forge@your-server-ip

# Navigate to project directory
cd /home/forge/api.rent-hub.ro

# Run deployment script
bash deploy-forge.sh
```

### 2. Manual Configuration Required
```bash
# Edit .env file with production credentials
nano .env

# Required updates:
# - DB_PASSWORD=your_actual_password
# - MAIL_PASSWORD=your_mailgun_password
# - STRIPE_SECRET=your_stripe_secret_key
# - SENTRY_LARAVEL_DSN=your_sentry_dsn
```

### 3. Database Setup
```bash
# Create database (if not exists)
mysql -u forge -p
CREATE DATABASE renthub_production;
exit

# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force
```

### 4. SSL Certificate (Let's Encrypt)
```bash
# Laravel Forge will handle this automatically
# But verify it's working:
curl -I https://api.rent-hub.ro/api/health
```

### 5. Queue Workers
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers if needed
sudo supervisorctl restart renthub-worker:*
```

### 6. WebSocket Server (Reverb)
```bash
# Check Reverb status
sudo systemctl status renthub-reverb

# Restart if needed
sudo systemctl restart renthub-reverb
```

---

## 🎨 FRONTEND DEPLOYMENT STEPS

### 1. Vercel Setup
```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy to Vercel
vercel --prod
```

### 2. Environment Variables (Vercel Dashboard)
Add these to Vercel dashboard:
```
VITE_API_URL=https://api.rent-hub.ro
VITE_APP_ENV=production
VITE_REVERB_APP_KEY=renthub-prod-key
VITE_REVERB_HOST=api.rent-hub.ro
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
VITE_GOOGLE_ANALYTICS_ID=
VITE_ENABLE_ANALYTICS=true
VITE_ENABLE_SENTRY=true
VITE_SENTRY_DSN=
VITE_API_TIMEOUT=30000
VITE_RETRY_ATTEMPTS=3
```

### 3. Domain Configuration
```bash
# Add custom domain in Vercel dashboard
# Point CNAME to cname.vercel-dns.com
```

---

## 🧪 POST-DEPLOYMENT TESTING

### API Health Checks
```bash
# Test basic health
curl https://api.rent-hub.ro/api/health

# Test production health dashboard
curl https://api.rent-hub.ro/api/health/production

# Test database connection
curl https://api.rent-hub.ro/api/health/database
```

### Authentication Testing
```bash
# Test registration
curl -X POST https://api.rent-hub.ro/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Test login
curl -X POST https://api.rent-hub.ro/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Frontend Testing
```bash
# Test main page
curl -I https://rent-hub.ro

# Test API connectivity from frontend
curl -I https://rent-hub.ro/api/health
```

### Performance Testing
```bash
# Test API response times
curl -w "@curl-format.txt" -o /dev/null -s https://api.rent-hub.ro/api/v1/properties

# Test with multiple concurrent requests
ab -n 100 -c 10 https://api.rent-hub.ro/api/health
```

---

## 🔍 MONITORING & DEBUGGING

### Log Files to Monitor
```bash
# Laravel logs
tail -f /home/forge/api.rent-hub.ro/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/api.rent-hub.ro-error.log
tail -f /var/log/nginx/api.rent-hub.ro-access.log

# Queue worker logs
tail -f /home/forge/api.rent-hub.ro/storage/logs/worker.log

# WebSocket logs
tail -f /home/forge/api.rent-hub.ro/storage/logs/reverb.log
```

### Health Monitoring
```bash
# Check all services
sudo supervisorctl status
sudo systemctl status renthub-reverb
sudo systemctl status redis
sudo systemctl status mysql

# Check disk space
df -h

# Check memory usage
free -h
```

### Performance Monitoring
```bash
# Check slow queries
mysql -u forge -p -e "SHOW PROCESSLIST;"

# Check Redis memory
redis-cli INFO memory

# Check queue size
php artisan queue:size
```

---

## 🚨 COMMON ISSUES & SOLUTIONS

### Issue: API Slow Response Times
**Symptoms:** Requests taking >1000ms
**Solutions:**
1. Check database indexes: `php artisan migrate:status`
2. Enable query caching: `php artisan config:cache`
3. Check for N+1 queries in logs
4. Optimize database queries

### Issue: CORS Errors
**Symptoms:** Frontend can't connect to API
**Solutions:**
1. Verify `FRONTEND_URL` in backend `.env`
2. Check CORS middleware configuration
3. Verify SSL certificates are valid

### Issue: Queue Jobs Not Processing
**Symptoms:** Emails/notifications not sending
**Solutions:**
1. Check supervisor status: `sudo supervisorctl status`
2. Restart workers: `sudo supervisorctl restart renthub-worker:*`
3. Check queue connection in `.env`

### Issue: WebSocket Connection Failed
**Symptoms:** Real-time features not working
**Solutions:**
1. Check Reverb service: `sudo systemctl status renthub-reverb`
2. Verify WebSocket ports are open
3. Check firewall settings

### Issue: File Upload Failures
**Symptoms:** Cannot upload images/documents
**Solutions:**
1. Check file permissions: `chmod -R 755 storage`
2. Verify `client_max_body_size` in Nginx
3. Check disk space availability

---

## 📊 PERFORMANCE BENCHMARKS

### Target Performance Metrics
- **API Response Time:** < 200ms for simple requests
- **Database Queries:** < 100ms per query
- **Page Load Time:** < 3 seconds
- **WebSocket Latency:** < 100ms
- **Queue Processing:** < 60 seconds per job

### Load Testing Commands
```bash
# Test with 100 concurrent users
ab -n 1000 -c 100 https://api.rent-hub.ro/api/health

# Test specific endpoints
ab -n 500 -c 50 https://api.rent-hub.ro/api/v1/properties
ab -n 500 -c 50 https://api.rent-hub.ro/api/v1/auth/login
```

---

## 🔐 SECURITY CHECKLIST

### SSL/TLS
- [ ] HTTPS enforced on all pages
- [ ] SSL certificate valid and auto-renewing
- [ ] HSTS headers configured
- [ ] TLS 1.2+ enforced

### Application Security
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] App key generated (`php artisan key:generate`)
- [ ] Database credentials secure
- [ ] File permissions correct (644 for files, 755 for directories)
- [ ] Rate limiting enabled
- [ ] CORS properly configured

### Infrastructure Security
- [ ] Firewall configured (only ports 80, 443, 22 open)
- [ ] SSH key authentication only
- [ ] Automatic security updates enabled
- [ ] Fail2ban configured
- [ ] Database not accessible from external network

---

## 📞 EMERGENCY CONTACTS & RESOURCES

### Service Status Pages
- Laravel Forge: https://forge.laravel.com/status
- Vercel: https://www.vercel-status.com/
- Mailgun: https://status.mailgun.com/

### Documentation
- Laravel Docs: https://laravel.com/docs
- Vercel Docs: https://vercel.com/docs
- Nginx Docs: http://nginx.org/en/docs/

### Support Channels
- Laravel Discord: https://discord.gg/laravel
- Vercel Support: https://vercel.com/support

---

## 🎯 SUCCESS CRITERIA

### ✅ Deployment Successful When:
1. All health check endpoints return 200 OK
2. Frontend loads without errors
3. API responds in < 200ms for simple requests
4. Authentication works (register/login)
5. Database queries execute properly
6. Queue jobs process successfully
7. WebSocket connections establish
8. File uploads work correctly
9. Emails send successfully
10. All E2E tests pass

### 🎉 Final Verification
Run this command for final check:
```bash
curl -s https://api.rent-hub.ro/api/health/production | jq .
```

All checks should return "status": "healthy"