# 🚀 Production Deployment Guide - Notification System

## Pre-Deployment Checklist

### 1. Environment Configuration
Pastikan `.env` production sudah dikonfigurasi:

```env
# Queue & Cache (WAJIB untuk notifications)
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Install Redis
**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

**Test Redis:**
```bash
redis-cli ping
# Should return: PONG
```

### 3. Install PHP Redis Extension
```bash
sudo apt install php-redis
# atau
sudo pecl install redis
```

**Verify:**
```bash
php -m | grep redis
```

---

## Deployment Steps

### 1. Deploy Code
```bash
cd /var/www/orange-absence
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 2. Run Migrations
```bash
php artisan migrate --force
```

Pastikan tabel `notifications` sudah ter-create.

### 3. Clear & Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icon:cache
php artisan filament:cache-components
```

### 4. Setup Queue Worker dengan Supervisor

**Install Supervisor:**
```bash
sudo apt install supervisor
```

**Create Config File:**
```bash
sudo nano /etc/supervisor/conf.d/orange-absence-worker.conf
```

**Paste Configuration:**
```ini
[program:orange-absence-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/orange-absence/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/orange-absence/storage/logs/worker.log
stopwaitsecs=3600
startsecs=0
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start orange-absence-worker:*
```

**Check Status:**
```bash
sudo supervisorctl status
```

### 5. Setup Cron untuk Scheduled Tasks

**Edit Crontab:**
```bash
sudo crontab -e -u www-data
```

**Add This Line:**
```
* * * * * cd /var/www/orange-absence && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled Tasks:**
- `app:generate-weekly-cash` - Every Tuesday 05:00
- `app:generate-division-codes` - Tuesday & Thursday 05:00
- `expire:schedules` - Every minute
- `app:send-debt-reminder` - Daily 09:00

---

## Testing Notifications

### 1. Test Queue Worker
```bash
# Monitor queue
php artisan queue:monitor redis

# Check queue stats
php artisan queue:stats
```

### 2. Send Test Notification via Tinker
```bash
php artisan tinker
```

```php
// Test Leave Request Notification
$user = User::role('super_admin')->first();
$attendance = Attendance::where('status', 'izin')->first();
$user->notify(new \App\Notifications\LeaveRequestNotification($attendance));

// Test Cash Log Notification
$member = User::role('member')->first();
$cashLog = $member->cashLogs()->first();
$member->notify(new \App\Notifications\CashLogCreatedNotification($cashLog));

// Check if notification created
\DB::table('notifications')->latest()->first();
```

### 3. Test Real Scenario

**A. Test Pengajuan Izin:**
1. Login sebagai member
2. Buka "Pengajuan Izin/Sakit"
3. Submit form
4. Logout
5. Login sebagai admin/secretary
6. Check bell icon → should see notification

**B. Test Cash Log:**
1. Login sebagai admin
2. Create manual cash log untuk member
3. Logout
4. Login sebagai member tersebut
5. Check bell icon → should see notification

---

## Monitoring & Troubleshooting

### Monitor Queue in Real-time
```bash
# Watch supervisor logs
sudo tail -f /var/www/orange-absence/storage/logs/worker.log

# Watch Laravel logs
sudo tail -f /var/www/orange-absence/storage/logs/laravel.log

# Monitor queue jobs
php artisan queue:work redis --verbose
```

### Common Issues

#### **1. Notifications tidak masuk**

**Check Queue Worker Status:**
```bash
sudo supervisorctl status orange-absence-worker:*
```

**If not running:**
```bash
sudo supervisorctl start orange-absence-worker:*
```

**Check Redis:**
```bash
redis-cli
> PING
> KEYS *
> LLEN queues:default
```

**Check Logs:**
```bash
tail -100 storage/logs/laravel.log
```

#### **2. Queue stuck / not processing**

**Clear failed jobs:**
```bash
php artisan queue:flush
php artisan queue:restart
```

**Restart supervisor:**
```bash
sudo supervisorctl restart orange-absence-worker:*
```

#### **3. Too many failed jobs**

**View failed jobs:**
```bash
php artisan queue:failed
```

**Retry all:**
```bash
php artisan queue:retry all
```

**Clear failed:**
```bash
php artisan queue:flush
```

---

## Performance Optimization

### Use Laravel Horizon (Optional)
Laravel Horizon provides better queue monitoring:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon:publish
```

Update Supervisor config:
```ini
command=php /var/www/orange-absence/artisan horizon
```

Access dashboard: `https://your-domain.com/horizon`

### Redis Memory Optimization
```bash
# Edit redis.conf
sudo nano /etc/redis/redis.conf
```

```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### Database Indexes
Ensure `notifications` table has indexes:
```sql
CREATE INDEX notifications_notifiable ON notifications(notifiable_type, notifiable_id);
CREATE INDEX notifications_read_at ON notifications(read_at);
```

---

## Backup & Recovery

### Backup Notifications Data
```bash
mysqldump -u root -p orange_absence notifications > notifications_backup.sql
```

### Clear Old Notifications (Monthly Maintenance)
```bash
# Create command or run manually
mysql -u root -p orange_absence -e "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);"
```

---

## Security Checklist

- [ ] Queue worker running as `www-data` user (not root)
- [ ] Redis protected dengan password (production)
- [ ] Firewall blocks Redis port from external access
- [ ] SSL certificate installed untuk production domain
- [ ] Rate limiting enabled untuk notification endpoints
- [ ] Logs rotated to prevent disk full

---

## Maintenance Schedule

| Task | Frequency | Command |
|------|-----------|---------|
| Check queue worker | Daily | `sudo supervisorctl status` |
| Clear old notifications | Monthly | Custom command |
| Review failed jobs | Weekly | `php artisan queue:failed` |
| Monitor Redis memory | Weekly | `redis-cli INFO memory` |
| Restart queue worker | After deployment | `sudo supervisorctl restart orange-absence-worker:*` |

---

## Emergency Procedures

### If notifications completely broken:

1. **Switch to sync queue temporarily:**
```env
QUEUE_CONNECTION=sync
```

2. **Restart all services:**
```bash
sudo systemctl restart redis-server
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart all
```

3. **Check all logs:**
```bash
tail -100 /var/log/nginx/error.log
tail -100 storage/logs/laravel.log
sudo journalctl -u supervisor -n 50
```

4. **Contact developer if issue persists**

---

## Success Criteria

✅ Queue worker running without errors for 24 hours
✅ Notifications appear within 5 seconds of trigger
✅ No memory leaks in Redis
✅ Scheduled tasks running on time
✅ No failed jobs accumulating
✅ Bell icon shows correct unread count

---

## Support

For production issues:
- Check `NOTIFICATION_SYSTEM.md` for detailed documentation
- Review `storage/logs/laravel.log`
- Contact: Developer Team

Last Updated: 2026-01-21
