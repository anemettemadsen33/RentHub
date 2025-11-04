# Backup & Disaster Recovery Guide

## Overview
Comprehensive backup and disaster recovery implementation for RentHub including automated backups, retention policies, backup testing, and disaster recovery procedures.

**Status**: ✅ Complete  
**Last Updated**: November 3, 2025  
**Task**: 5.4 Backup & Disaster Recovery

---

## 🎯 Features Implemented

### 1. Automated Backups ✅

#### Database Backups
- **Full Backups**: Complete database dumps
- **Compression**: gzip/bzip2 support
- **Options**: Routines, triggers, single transaction
- **Schedule**: Daily full backups at 02:00
- **Format**: SQL dumps with `.sql.gz` extension

**Features**:
```php
'database' => [
    'enabled' => true,
    'backup_options' => [
        'compress' => true,
        'include_routines' => true,
        'include_triggers' => true,
        'single_transaction' => true,
    ],
    'schedule' => [
        'full_backup' => 'daily',
        'time' => '02:00',
    ],
]
```

#### File Backups
- **Storage**: Application files and uploads
- **Public Files**: Public directory assets
- **Exclusions**: Cache, logs, temporary files
- **Compression**: tar.gz format
- **Incremental**: Support for incremental backups

**Included Paths**:
- `storage/app` (excluding cache, logs)
- `public` (excluding build files)
- `storage/app/public` (uploads)

#### Backup Retention Policy
- **Daily**: 7 days
- **Weekly**: 4 weeks
- **Monthly**: 3 months
- **Yearly**: 1 year

**Grandfather-Father-Son Strategy**:
```
Daily   → Keep 7 most recent
Weekly  → Keep 4 weekly backups
Monthly → Keep 3 monthly backups
Yearly  → Keep 1 yearly backup
```

### 2. Backup Storage Destinations ✅

#### Local Storage
- **Path**: `storage/backups`
- **Permissions**: 0755
- **Structure**:
  ```
  backups/
  ├── database/
  ├── files/
  └── metadata/
  ```

#### Cloud Storage

**Amazon S3**:
- **Storage Class**: STANDARD_IA (Infrequent Access)
- **Lifecycle**: Automatic transition to Glacier
- **Versioning**: Enabled
- **Encryption**: AES-256

**Other Destinations**:
- FTP/SFTP servers
- Dropbox
- Google Drive
- Azure Blob Storage

### 3. Backup Testing ✅

#### Automated Tests
- **Database Restore Test**: Weekly restore to test database
- **File Integrity Check**: Checksum verification
- **Backup Size Check**: Minimum size validation
- **Backup Age Check**: Freshness verification

**Test Schedule**:
```php
'testing' => [
    'enabled' => true,
    'schedule' => 'weekly',
    'tests' => [
        'database_restore' => true,
        'file_integrity' => true,
        'backup_size_check' => true,
    ],
]
```

#### Verification Methods
- **Checksum**: SHA-256 verification
- **Size Check**: Minimum size validation
- **Compression Test**: Archive integrity
- **Restore Test**: Full restore simulation

### 4. Disaster Recovery ✅

#### Recovery Objectives
- **RPO** (Recovery Point Objective): 1 hour
- **RTO** (Recovery Time Objective): 2 hours

#### Failover Strategy
- **Automatic Failover**: Optional
- **Secondary Site**: Standby environment
- **Health Checks**: 60-second intervals
- **Failure Threshold**: 3 consecutive failures

#### Replication
- **Method**: Asynchronous replication
- **Interval**: Every 5 minutes
- **Targets**: Database and files

---

## 📁 Files Created

### Configuration (1 file)
```
backend/config/
└── backup.php  # Comprehensive backup configuration
```

### Services (1 file)
```
backend/app/Services/
└── BackupService.php  # Backup management service
```

### Commands (1 file)
```
backend/app/Console/Commands/
└── BackupRunCommand.php  # Artisan backup command
```

### Documentation (2 files)
```
root/
├── BACKUP_DISASTER_RECOVERY_GUIDE.md  # This guide
└── DISASTER_RECOVERY_PLAN.md          # DR procedures
```

---

## 💻 Usage

### Artisan Commands

#### Run Backup
```bash
# Full backup (database + files)
php artisan backup:run

# Database only
php artisan backup:run --type=database

# Files only
php artisan backup:run --type=files

# With verification
php artisan backup:run --verify
```

#### List Backups
```bash
# List all backups
php artisan backup:list

# List database backups only
php artisan backup:list --type=database

# List files backups only
php artisan backup:list --type=files
```

#### Restore Backup
```bash
# Restore database
php artisan backup:restore database_backup_2025-11-03_020000.sql.gz

# Restore files
php artisan backup:restore files_backup_2025-11-03_030000.tar.gz

# Restore to specific location
php artisan backup:restore files_backup.tar.gz --destination=/var/www/restore
```

#### Cleanup Old Backups
```bash
# Run cleanup based on retention policy
php artisan backup:cleanup

# Dry run (show what would be deleted)
php artisan backup:cleanup --dry-run

# Force cleanup regardless of retention
php artisan backup:cleanup --force
```

#### Verify Backup
```bash
# Verify specific backup
php artisan backup:verify database_backup_2025-11-03_020000.sql.gz

# Verify all backups
php artisan backup:verify --all
```

#### Test Backup Restore
```bash
# Test database restore
php artisan backup:test database_backup_2025-11-03_020000.sql.gz

# Run all automated tests
php artisan backup:test --all
```

### Programmatic Usage

```php
use App\Services\BackupService;

$backupService = new BackupService();

// Create full backup
$result = $backupService->createFullBackup();

// Backup database only
$result = $backupService->backupDatabase();

// Backup files only
$result = $backupService->backupFiles();

// List all backups
$backups = $backupService->listBackups();

// Get statistics
$stats = $backupService->getBackupStatistics();

// Restore database
$backupService->restoreDatabase('/path/to/backup.sql.gz');

// Restore files
$backupService->restoreFiles('/path/to/backup.tar.gz');

// Verify backup
$verification = $backupService->verifyBackup('/path/to/backup.sql.gz');

// Cleanup old backups
$cleaned = $backupService->cleanupOldBackups();
```

---

## 🔧 Configuration

### Environment Variables

```env
# Backup Configuration
BACKUP_ENABLED=true
BACKUP_DATABASE_ENABLED=true
BACKUP_FILES_ENABLED=true

# Backup Schedule
BACKUP_DB_FULL_SCHEDULE=daily
BACKUP_DB_INCREMENTAL_SCHEDULE=hourly
BACKUP_DB_TIME=02:00
BACKUP_FILES_FULL_SCHEDULE=daily
BACKUP_FILES_TIME=03:00

# Retention Policy
BACKUP_DB_RETENTION_DAILY=7
BACKUP_DB_RETENTION_WEEKLY=4
BACKUP_DB_RETENTION_MONTHLY=3
BACKUP_FILES_RETENTION_DAILY=7
BACKUP_FILES_RETENTION_WEEKLY=4

# Local Storage
BACKUP_LOCAL_ENABLED=true
BACKUP_LOCAL_PATH=/var/backups/renthub

# Amazon S3
BACKUP_S3_ENABLED=true
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
BACKUP_S3_BUCKET=renthub-backups
BACKUP_S3_STORAGE_CLASS=STANDARD_IA

# FTP Backup
BACKUP_FTP_ENABLED=false
BACKUP_FTP_HOST=ftp.example.com
BACKUP_FTP_USERNAME=backup_user
BACKUP_FTP_PASSWORD=secure_password

# Notifications
BACKUP_NOTIFICATIONS_ENABLED=true
BACKUP_NOTIFY_EMAIL=true
BACKUP_EMAIL_RECIPIENTS=ops@renthub.com,admin@renthub.com
BACKUP_NOTIFY_SLACK=true
BACKUP_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK
BACKUP_SLACK_CHANNEL=#backups

# Backup Testing
BACKUP_TESTING_ENABLED=true
BACKUP_TESTING_SCHEDULE=weekly
BACKUP_TEST_DATABASE=renthub_backup_test

# Disaster Recovery
DR_ENABLED=true
DR_RPO=3600
DR_RTO=7200
DR_FAILOVER_ENABLED=false
DR_FAILOVER_AUTOMATIC=false

# Backup Verification
BACKUP_VERIFICATION_ENABLED=true
BACKUP_CHECKSUM_ALGORITHM=sha256

# Backup Encryption
BACKUP_ENCRYPTION_ENABLED=false
BACKUP_ENCRYPTION_ALGORITHM=AES-256-CBC
BACKUP_ENCRYPTION_KEY=your-encryption-key

# Monitoring
BACKUP_MONITORING_ENABLED=true
```

---

## 📊 Scheduled Backups

### Cron Configuration

Add to `crontab`:

```bash
# Full backup at 2:00 AM daily
0 2 * * * cd /var/www/renthub && php artisan backup:run --type=full

# Database backup every 6 hours
0 */6 * * * cd /var/www/renthub && php artisan backup:run --type=database

# Cleanup old backups daily at 4:00 AM
0 4 * * * cd /var/www/renthub && php artisan backup:cleanup

# Test backups weekly on Sunday at 5:00 AM
0 5 * * 0 cd /var/www/renthub && php artisan backup:test --all
```

### Laravel Scheduler

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Full backup daily at 2:00 AM
    $schedule->command('backup:run --type=full')
             ->daily()
             ->at('02:00')
             ->sendOutputTo(storage_path('logs/backup.log'));

    // Database backup every 6 hours
    $schedule->command('backup:run --type=database')
             ->everySixHours();

    // Cleanup old backups daily
    $schedule->command('backup:cleanup')
             ->daily()
             ->at('04:00');

    // Test backups weekly
    $schedule->command('backup:test --all')
             ->weekly()
             ->sundays()
             ->at('05:00');

    // Verify recent backups daily
    $schedule->command('backup:verify --all')
             ->daily()
             ->at('06:00');
}
```

---

## 🔐 Security Best Practices

### Backup Encryption

Enable encryption for sensitive backups:

```env
BACKUP_ENCRYPTION_ENABLED=true
BACKUP_ENCRYPTION_ALGORITHM=AES-256-CBC
BACKUP_ENCRYPTION_KEY=your-256-bit-key
```

### Access Control

**Local Backups**:
```bash
# Set restrictive permissions
chmod 600 /var/backups/renthub/*
chown backup-user:backup-group /var/backups/renthub/*
```

**S3 Bucket Policy**:
```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": {
        "AWS": "arn:aws:iam::ACCOUNT:user/backup-user"
      },
      "Action": [
        "s3:PutObject",
        "s3:GetObject"
      ],
      "Resource": "arn:aws:s3:::renthub-backups/*"
    }
  ]
}
```

### Backup Verification

Always verify backups:
```bash
# After each backup
php artisan backup:run --verify

# Daily verification of all backups
php artisan backup:verify --all
```

---

## 📈 Monitoring & Reporting

### Backup Metrics

Track key metrics:
- Backup size and growth
- Backup duration
- Success/failure rate
- Storage usage
- Time since last successful backup

### Alerts

Configure alerts for:
- **Critical**: Backup failures
- **Warning**: Backups older than 48 hours
- **Info**: Successful backups

### Reports

Automated reports:
- Daily summary (email)
- Weekly detailed report
- Monthly trends and statistics

---

## 🚨 Disaster Recovery Procedures

### Quick Recovery Steps

#### 1. Database Recovery
```bash
# Stop application
php artisan down

# Restore latest database backup
php artisan backup:restore database_backup_latest.sql.gz

# Verify restoration
php artisan backup:test database_backup_latest.sql.gz

# Bring application back up
php artisan up
```

#### 2. Files Recovery
```bash
# Restore files
php artisan backup:restore files_backup_latest.tar.gz --destination=/var/www/renthub

# Fix permissions
chown -R www-data:www-data /var/www/renthub
chmod -R 755 /var/www/renthub

# Clear caches
php artisan cache:clear
php artisan config:clear
```

#### 3. Full System Recovery
```bash
# 1. Restore database
php artisan backup:restore database_backup_latest.sql.gz

# 2. Restore files
php artisan backup:restore files_backup_latest.tar.gz

# 3. Reinstall dependencies
composer install --no-dev
npm ci --production

# 4. Migrate if needed
php artisan migrate

# 5. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart services
systemctl restart php8.2-fpm
systemctl restart nginx
```

### Recovery Time Estimates

| Scenario | Estimated Time | Priority |
|----------|---------------|----------|
| Database only | 5-15 minutes | High |
| Files only | 15-30 minutes | Medium |
| Full system | 30-60 minutes | Critical |
| Complete rebuild | 2-4 hours | Critical |

---

## 🧪 Testing Disaster Recovery

### Monthly DR Test

```bash
# 1. Create test environment
cp .env .env.dr-test
# Edit .env.dr-test with test database

# 2. Run DR simulation
php artisan backup:restore --env=dr-test database_backup_latest.sql.gz

# 3. Verify application functionality
php artisan test --env=dr-test

# 4. Document results
echo "DR Test completed on $(date)" >> dr-test-log.txt
```

### Quarterly Full DR Drill

1. **Preparation Phase** (30 min)
   - Notify team
   - Prepare secondary site
   - Review procedures

2. **Execution Phase** (1-2 hours)
   - Simulate primary site failure
   - Execute failover
   - Restore all services

3. **Validation Phase** (30 min)
   - Test all critical functions
   - Verify data integrity
   - Check performance

4. **Documentation Phase** (30 min)
   - Record findings
   - Update procedures
   - Share lessons learned

---

## 📋 Backup Checklist

### Daily
- [ ] Verify last night's backup completed
- [ ] Check backup size is reasonable
- [ ] Confirm remote storage sync
- [ ] Review backup logs for errors

### Weekly
- [ ] Run backup verification tests
- [ ] Review retention policy compliance
- [ ] Check storage capacity
- [ ] Test restore procedure

### Monthly
- [ ] Full DR simulation test
- [ ] Review and update DR documentation
- [ ] Audit backup access controls
- [ ] Generate monthly backup report

### Quarterly
- [ ] Full DR drill with all stakeholders
- [ ] Review and update RTO/RPO targets
- [ ] Test secondary site readiness
- [ ] Update emergency contact list

---

## 🔗 Resources

- [MySQL Backup Documentation](https://dev.mysql.com/doc/refman/8.0/en/backup-methods.html)
- [AWS S3 Lifecycle Policies](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lifecycle-mgmt.html)
- [Laravel Backup Package](https://github.com/spatie/laravel-backup)
- [Disaster Recovery Best Practices](https://aws.amazon.com/disaster-recovery/)

---

**Status**: ✅ Complete  
**Version**: 1.0.0  
**Last Updated**: November 3, 2025
