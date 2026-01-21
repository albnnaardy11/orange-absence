# 🎯 cPanel Deployment Guide - Orange Absence

> **One-Command Deployment**: `bash deploy.sh`

## 📋 Prerequisites

Before deploying to cPanel, ensure you have:

- ✅ cPanel hosting with **PHP 8.2+** (change in cPanel → Select PHP Version)
- ✅ MySQL database created in cPanel
- ✅ SSH access enabled (optional but recommended)
- ✅ Git installed (check in Terminal: `git --version`)
- ✅ Composer installed (check in Terminal: `composer --version`)
- ✅ Node.js installed (optional for asset building)

---

## 🚀 Quick Start (3 Minutes)

### Method 1: SSH Access (Recommended ⭐)

```bash
# 1. SSH into your cPanel
ssh your_cpanel_username@your-domain.com

# 2. Navigate to public_html (or your desired directory)
cd public_html

# 3. Clone repository
git clone https://github.com/YOUR_USERNAME/orange-absence.git .

# 4. Run deployment script
bash deploy.sh
```

The script will:
- ✅ Copy `.env.example` to `.env`
- ✅ Prompt you to configure database credentials
- ✅ Install all dependencies
- ✅ Generate app key
- ✅ Run migrations with seed data
- ✅ Build frontend assets
- ✅ Optimize for production
- ✅ Display setup instructions

### Method 2: File Manager (No SSH)

1. **Download Repository**
   - Download ZIP from GitHub
   - Extract locally

2. **Upload via cPanel File Manager**
   - Login to cPanel → File Manager
   - Navigate to `public_html`
   - Upload all files
   - Extract if necessary

3. **Setup via Terminal**
   - cPanel → Terminal
   - Navigate to your directory: `cd public_html`
   - Run: `bash deploy.sh`

---

## ⚙️ Configuration Steps

### 1. Database Setup

**Create Database in cPanel:**
1. cPanel → MySQL® Databases
2. Create new database: `your_cpanel_username_orange`
3. Create new user: `your_cpanel_username_admin`
4. Add user to database with ALL PRIVILEGES

**Update `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_username_orange
DB_USERNAME=your_cpanel_username_admin
DB_PASSWORD=your_secure_password
```

### 2. Application URL

Update `.env`:
```env
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false
```

### 3. Queue & Cache Configuration

For **cPanel** (recommended):
```env
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

For **cPanel with Redis** (if available):
```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

---

## 🔄 Setup Cron Jobs (IMPORTANT!)

### Required Cron Jobs

Login to cPanel → Cron Jobs, add these two cron jobs:

#### **1. Laravel Scheduler** (Required for scheduled tasks)

| Field | Value |
|-------|-------|
| **Minute** | `*` |
| **Hour** | `*` |
| **Day** | `*` |
| **Month** | `*` |
| **Weekday** | `*` |
| **Command** | `cd /home/USERNAME/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1` |

Replace:
- `USERNAME` with your cPanel username
- `/home/USERNAME/public_html` with your actual path

**This runs:**
- Weekly cash generation (Tuesday 05:00)
- Division code generation (Tuesday & Thursday 05:00)
- Schedule expiration check (Every minute)
- Debt reminders (Daily 09:00)

#### **2. Queue Worker** (Required for notifications!)

| Field | Value |
|-------|-------|
| **Minute** | `*` |
| **Hour** | `*` |
| **Day** | `*` |
| **Month** | `*` |
| **Weekday** | `*` |
| **Command** | `cd /home/USERNAME/public_html && /usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1` |

**⚠️ CRITICAL**: Without this cron job, **notifications will NOT work**!

This processes:
- Leave request notifications
- Cash log notifications
- Debt reminder notifications

---

## 📁 Document Root Configuration

### Option A: Deploy in Root Domain

If you want `https://your-domain.com` to point to Orange Absence:

1. **cPanel → Domains → Domains**
2. Find your domain
3. Set Document Root to: `/home/USERNAME/public_html/public`

### Option B: Deploy in Subdomain

If you want `https://absence.your-domain.com`:

1. **cPanel → Domains → Subdomains**
2. Create subdomain: `absence`
3. Document Root: `/home/USERNAME/public_html/public`

### Option C: Deploy in Subdirectory

If you want `https://your-domain.com/absence`:

1. Upload to `/home/USERNAME/public_html/absence`
2. Create `.htaccess` in `/public_html`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^absence(.*)$ absence/public/$1 [L]
</IfModule>
```

---

## 🔒 Security Configuration

### 1. Protect Sensitive Files

Add to `.htaccess` in root:

```apache
# Deny access to .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Deny access to storage directory
RedirectMatch 403 ^/storage/
```

### 2. Force HTTPS (If SSL installed)

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. File Permissions

```bash
# Via SSH or Terminal
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 755 storage bootstrap/cache
```

---

## 🧪 Testing Deployment

### 1. Check Application Access

Visit these URLs:

- **Home**: `https://your-domain.com`
- **Admin Panel**: `https://your-domain.com/admin`
- **Member Panel**: `https://your-domain.com/member`

### 2. Test Default Logins

**Super Admin:**
- Email: `admin@orange.test`
- Password: `password`

**Secretary:**
- Email: `secretary@orange.test`
- Password: `password`

**Member:**
- Email: `member@orange.test`
- Password: `password`

⚠️ **Change these passwords immediately in production!**

### 3. Test Notifications

1. Login as Member
2. Go to "Pengajuan Izin/Sakit"
3. Submit a leave request
4. Logout, login as Admin
5. Check bell icon (🔔) - should see notification
6. If notification appears ✅ Queue is working!
7. If no notification ❌ Check queue cron job

### 4. Test Scheduled Tasks

```bash
# Via Terminal
php artisan schedule:list

# Test run schedule
php artisan schedule:run
```

---

## 🔧 Troubleshooting

### Issue: "500 Internal Server Error"

**Solutions:**
1. Check `.env` file exists and configured
2. Check file permissions: `chmod -R 755 storage bootstrap/cache`
3. Check error logs: `cPanel → Errors`
4. Run: `php artisan config:clear && php artisan cache:clear`

### Issue: "Database Connection Error"

**Solutions:**
1. Verify database credentials in `.env`
2. Ensure user has ALL PRIVILEGES
3. Check `DB_HOST` is `localhost` not `127.0.0.1`
4. Test connection: `php artisan db:show`

### Issue: "Queue/Notifications Not Working"

**Solutions:**
1. Verify queue cron job is set up correctly
2. Check cron job is running: `cPanel → Cron Jobs → Current Cron Jobs`
3. Test manually: `php artisan queue:work --once`
4. Check queue table: `SELECT * FROM jobs;`
5. Ensure `QUEUE_CONNECTION=database` in `.env`

### Issue: "Assets Not Loading (CSS/JS)"

**Solutions:**
1. Run: `npm run build`
2. Clear cache: `php artisan view:clear`
3. Check `APP_URL` in `.env` matches your domain
4. Run: `php artisan filament:cache-components`

### Issue: "Storage Images Not Showing"

**Solutions:**
1. Check symlink: `php artisan storage:link --force`
2. Verify `public/storage` symlink exists
3. Check file permissions: `chmod -R 755 storage/app/public`
4. Change `FILESYSTEM_DISK=public` in `.env`

---

## 🔄 Updating Application

When you push updates to GitHub:

```bash
# SSH into server
cd /home/USERNAME/public_html

# Pull latest changes
git pull origin main

# Run deployment
bash deploy.sh
```

The script automatically:
- Puts app in maintenance mode
- Updates dependencies
- Runs new migrations
- Rebuilds assets
- Clears & rebuilds cache
- Exits maintenance mode

---

## 📊 Performance Optimization

### Enable OPcache (cPanel)

1. cPanel → Select PHP Version
2. Click "Switch To PHP Options"
3. Enable these extensions:
   - `opcache`
   - `pdo_mysql`
   - `mbstring`
   - `fileinfo`
   - `json`
   - `zip`

### Optimize Database

```bash
# Run monthly
php artisan db:show
php artisan optimize:clear
php artisan optimize
```

### Clean Old Data

```bash
# Clean notifications older than 90 days
php artisan tinker
>>> DB::table('notifications')->where('created_at', '<', now()->subDays(90))->delete();

# Clean activity logs older than 180 days
>>> \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(180))->delete();
```

---

## 📦 Backup Strategy

### Automated Backup (via cPanel)

1. **cPanel → Backup**
2. Enable automatic backups
3. Schedule: Weekly
4. Include: Home directory + MySQL databases

### Manual Backup

```bash
# Database backup
mysqldump -u USERNAME -p DATABASE_NAME > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf backup_files_$(date +%Y%m%d).tar.gz /home/USERNAME/public_html
```

---

## 🆘 Support Resources

### Log Files Location

```bash
# Application logs
storage/logs/laravel.log

# cPanel error logs
cPanel → Errors → Error Log
```

### Useful Commands

```bash
# Check PHP version
php -v

# Check Laravel info
php artisan about

# List all routes
php artisan route:list

# Check scheduled tasks
php artisan schedule:list

# Test database connection
php artisan db:show

# Clear all cache
php artisan optimize:clear
```

### Documentation Files

- `README.md` - General documentation
- `NOTIFICATION_SYSTEM.md` - Notification system details  
- `DEPLOYMENT_NOTIFICATIONS.md` - Advanced notification deployment

---

## ✅ Post-Deployment Checklist

After successful deployment:

- [ ] Application accessible via browser
- [ ] All three panels working (Admin, Secretary, Member)
- [ ] Can login with default credentials
- [ ] Database populated with seed data
- [ ] Scheduled tasks cron job configured
- [ ] Queue worker cron job configured
- [ ] Notifications working (test leave request)
- [ ] File uploads working (test izin/sakit with image)
- [ ] SSL certificate installed (HTTPS)
- [ ] Default passwords changed
- [ ] Backup strategy configured
- [ ] Error monitoring setup

---

## 🎉 Success!

Your Orange Absence system is now live on cPanel!

**Next Steps:**
1. ✅ Change default passwords
2. ✅ Configure divisions as needed
3. ✅ Add real users
4. ✅ Setup email SMTP for notifications
5. ✅ Monitor logs for first 24 hours

**Need Help?**
- Check logs: `storage/logs/laravel.log`
- Run diagnostics: `php artisan about`
- Review documentation: `README.md`

---

**Built for Excellence | Deployed with Confidence**

Last Updated: 2026-01-21
Version: 2.0.0 (cPanel Optimized)
