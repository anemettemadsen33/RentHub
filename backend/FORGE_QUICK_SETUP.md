# Quick Forge Setup - Step by Step

## Connect to SSH

```bash
ssh forge@178.128.135.24
```

## Option 1: Quick Manual Fix (FASTEST)

```bash
# 1. Edit .env file
cd /home/forge/renthub-ny52mbov.on-forge.com/backend
nano .env

# 2. Find this line:
DB_CONNECTION=sqlite

# 3. Change to:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=YOUR_PASSWORD_HERE

# 4. Save: Ctrl+X, then Y, then Enter

# 5. Get database password from Forge UI → Database tab
# Then come back and edit .env again to add the password

# 6. Run migrations
php artisan migrate --force

# 7. Seed database
php artisan db:seed --force

# 8. Test
curl http://localhost/api/health
```

## Option 2: Use Automated Script

```bash
# Download script
cd /home/forge/renthub-ny52mbov.on-forge.com/backend
wget https://raw.githubusercontent.com/anemettemadsen33/RentHub/master/backend/setup-forge.sh

# Or create it manually:
nano setup-forge.sh
# Paste the script content
# Save: Ctrl+X, Y, Enter

# Make executable
chmod +x setup-forge.sh

# Run it
./setup-forge.sh
```

## Get Database Password

You need the MySQL password for user `forge`.

**From Forge UI:**
1. Go to Forge Dashboard
2. Click your site: renthub-ny52mbov.on-forge.com
3. Click "Database" tab
4. Copy the password shown there

**Or from SSH:**
```bash
# The password should be in Forge's configuration
# Check if Forge has a .my.cnf file
cat ~/.my.cnf
```

## After Setup

Test these URLs:
- Health: https://renthub-ny52mbov.on-forge.com/api/health
- Admin: https://renthub-ny52mbov.on-forge.com/admin

---

## Troubleshooting

**Database connection fails:**
```bash
# Test MySQL connection
mysql -u forge -p forge
# Enter the password when prompted

# If this works, the password is correct
# Update .env with this password
```

**Migrations fail:**
```bash
# Check database exists
mysql -u forge -p -e "SHOW DATABASES;"

# Create database if missing
mysql -u forge -p -e "CREATE DATABASE IF NOT EXISTS forge;"
```

**Permissions errors:**
```bash
cd /home/forge/renthub-ny52mbov.on-forge.com/backend
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
```

---

**Need help? Copy the error message and I'll help you fix it!**
