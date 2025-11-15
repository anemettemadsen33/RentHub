# 🔧 Configurare Database pe Laravel Forge via SSH

## Pasul 1: Conectare la Server

```bash
# Conectează-te la serverul tău Forge
ssh forge@IP_SERVERULUI_TAU

# SAU dacă ai configurat un alias în ~/.ssh/config:
ssh forge-server
```

## Pasul 2: Navighează la Directorul Site-ului

```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
# SAU verifică exact numele directorului:
ls -la /home/forge/
```

## Pasul 3: Verifică Fișierul .env Actual

```bash
cat .env | grep DB_
```

**Ar trebui să vezi:**
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=PAROLA_GREȘITĂ
```

## Pasul 4: Găsește Credențialele Corecte ale Bazei de Date

### A. Verifică ce baze de date există:
```bash
mysql -u forge -p
# Introdu parola MySQL (NU parola SSH!)
```

Dacă parola nu merge, încearcă:
```bash
sudo mysql -u root
```

### B. Din MySQL, rulează:
```sql
SHOW DATABASES;
SELECT user, host FROM mysql.user WHERE user = 'forge';
EXIT;
```

### C. SAU verifică în Forge Dashboard:
1. Mergi la **Database** tab
2. Copiază:
   - Database Name
   - Database User
   - Database Password

## Pasul 5: Actualizează .env cu Credențialele Corecte

```bash
# Editează fișierul .env
nano .env

# SAU
vim .env
```

**Actualizează aceste linii cu valorile CORECTE din Forge Dashboard:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=numele_bazei_tale  # De obicei: forge sau renthub
DB_USERNAME=userul_bazei       # De obicei: forge
DB_PASSWORD=parola_reala_din_forge_dashboard
```

**Salvează fișierul:**
- Nano: `Ctrl+O` (save), `Enter`, `Ctrl+X` (exit)
- Vim: `ESC`, `:wq`, `Enter`

## Pasul 6: Clear Cache și Rulează Migrații

```bash
# Clear configuration cache
php artisan config:clear
php artisan cache:clear

# Recompilează configurația
php artisan config:cache

# Testează conexiunea
php artisan migrate:status

# Dacă merge, rulează migrațiile
php artisan migrate --force
```

## Pasul 7: Restart PHP-FPM (Opțional dar Recomandat)

```bash
sudo service php8.3-fpm restart
# SAU
sudo systemctl restart php8.3-fpm
```

## Pasul 8: Verifică Aplicația

```bash
curl http://localhost/api/health
```

**Ar trebui să vezi:**
```json
{
  "status": "healthy",
  "database": "connected",
  "cache": "connected",
  "queue": "connected"
}
```

## 🆘 Troubleshooting

### Problemă: "Access denied for user 'forge'@'localhost'"

**Soluție:** Parola din .env nu este cea corectă. Verifică în Forge Dashboard.

### Problemă: "Database does not exist"

**Soluție:** 
```bash
# Creează baza de date
mysql -u forge -p
CREATE DATABASE renthub;
EXIT;
```

### Problemă: "Too many connections"

**Soluție:**
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Adaugă: max_connections = 200
sudo service mysql restart
```

### Problemă: Permission denied la .env

**Soluție:**
```bash
sudo chown forge:forge /home/forge/renthub-tbj7yxj7.on-forge.com/.env
sudo chmod 644 /home/forge/renthub-tbj7yxj7.on-forge.com/.env
```

## 📊 Verificare Finală

După ce ai setat totul corect, testează din browser:

1. **Backend Health:** https://renthub-tbj7yxj7.on-forge.com/api/health
2. **Frontend:** https://rent-hub-beta.vercel.app/

Ambele ar trebui să funcționeze perfect! ✅

## 🔐 Notă de Securitate

**NU commit-a NICIODATĂ fișierul `.env` în Git!**

Fișierul `.env` este deja în `.gitignore` - păstrează-l acolo!
