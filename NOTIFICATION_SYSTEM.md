# 🔔 Sistem Notifikasi - Orange Absence

## Overview
Sistem notifikasi terintegrasi untuk memberitahu user tentang event penting dalam sistem.

## Notification Types

### 1. **Leave Request Notification** 📋
**Trigger:** Saat member mengajukan izin/sakit
**Recipients:** Super Admin + Sekretaris di divisi yang sama
**Channel:** Database Notification
**Data:**
- Member name
- Leave type (izin/sakit)
- Division name
- Reason/description
- Action link ke halaman edit attendance

**Location:** `app/Notifications/LeaveRequestNotification.php`

### 2. **Cash Log Created Notification** 💰
**Trigger:** Saat tagihan kas baru dibuat untuk member
**Recipients:** Member yang bersangkutan
**Channel:** Database Notification
**Data:**
- Amount (jumlah tagihan)
- Division name
- Status (unpaid)

**Location:** `app/Notifications/CashLogCreatedNotification.php`

### 3. **Debt Reminder Notification** ⚠️
**Trigger:** Scheduled command (artisan command)
**Recipients:** Members dengan 3+ tunggakan kas overdue
**Channel:** Database Notification
**Data:**
- Jumlah tunggakan
- Total amount
- Action link ke riwayat kas

**Command:** `php artisan app:send-debt-reminder`

---

## Delivery Mechanism

### Database Notifications
Semua notifikasi menggunakan **database channel** yang disimpan di tabel `notifications`.

**Advantages:**
- Persistent (tidak hilang setelah page refresh)
- Bisa di-mark as read
- Bisa ditampilkan di bell icon Filament
- Tidak butuh email/SMS configuration

**Disadvantages:**
- User harus login untuk melihat notifikasi
- Tidak ada push notification ke device

---

## Queue System

### Development
```env
QUEUE_CONNECTION=sync
```
Notifikasi langsung dikirim (synchronous)

### Production
```env
QUEUE_CONNECTION=redis
```
Notifikasi masuk queue untuk diproses background (asynchronous)

### Running Queue Worker

**Development:**
```bash
php artisan queue:work
```

**Production (Supervisor Recommended):**
```bash
php artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
```

**Using Supervisor Config:**
```ini
[program:orange-absence-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

---

## Observers

### AttendanceObserver
**Location:** `app/Observers/AttendanceObserver.php`
**Events:**
- `creating`: Auto-approve status "hadir"
- `updating`: Auto-approve jika status diubah ke "hadir"

### CashLogObserver  
**Location:** `app/Observers/CashLogObserver.php`
**Events:**
- `created`: Kirim notifikasi ke member saat cash log baru dibuat

**Registration:** `app/Providers/AppServiceProvider.php`

---

## How It Works

### 1. Leave Request Flow
```
Member submits izin/sakit
    ↓
PengajuanIzin.php create() method
    ↓
Attendance created in database
    ↓
Find recipients (admin + secretaries)
    ↓
Send LeaveRequestNotification to each recipient
    ↓
Notification stored in database
    ↓
Recipients see notification in Filament bell icon
```

### 2. Cash Log Flow
```
CashLog created (manual/automatic)
    ↓
CashLogObserver::created() triggered
    ↓
Check if status === 'unpaid'
    ↓
Send CashLogCreatedNotification to member
    ↓
Member sees notification in bell icon
```

---

## Viewing Notifications

### In Filament Panel
Notifications automatically appear in:
- **Bell icon** (top right navbar)
- Shows unread count
- Click to view list
- Click notification to mark as read and navigate to action URL

### Programmatically
```php
// Get user's unread notifications
$notifications = auth()->user()->unreadNotifications;

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();

// Get specific notification
$notification = auth()->user()->notifications()->find($id);
```

---

## Troubleshooting

### Notifications Not Showing?

**1. Check Queue Status**
```bash
php artisan queue:work
```
If using redis, make sure Redis is running and queue worker is active.

**2. Check Database**
```sql
SELECT * FROM notifications WHERE notifiable_id = YOUR_USER_ID;
```

**3. Check Observer Registration**
```php
// AppServiceProvider.php
CashLog::observe(CashLogObserver::class);
```

**4. Clear Cache**
```bash
php artisan cache:clear
php artisan config:clear
```

**5. Check Logs**
```bash
tail -f storage/logs/laravel.log
```

---

## Production Deployment Checklist

- [ ] Set `QUEUE_CONNECTION=redis` in `.env`
- [ ] Make sure Redis is installed and running
- [ ] Configure Supervisor for queue workers
- [ ] Test notifications in staging first
- [ ] Monitor queue dashboard with Laravel Horizon (optional)
- [ ] Setup queue failure notifications
- [ ] Implement retry logic for failed jobs

---

## Future Enhancements

### Email Notifications
Add email channel untuk notifications:
```php
public function via(object $notifiable): array
{
    return ['database', 'mail'];
}
```

### WhatsApp Notifications
Integration dengan Twilio/Fonnte:
```php
public function via(object $notifiable): array
{
    return ['database', WhatsAppChannel::class];
}
```

### Push Notifications
PWA push notifications untuk real-time alerts

### SMS Notifications
SMS untuk debt reminders yang critical

---

## Testing

### Send Test Notification
```php
// In tinker (php artisan tinker)
$user = User::find(1);
$attendance = Attendance::first();
$user->notify(new \App\Notifications\LeaveRequestNotification($attendance));
```

### Clear All Notifications
```php
\DB::table('notifications')->truncate();
```

---

## Maintenance

### Clean Old Notifications
Run monthly to prevent table bloat:
```bash
php artisan notifications:clean --days=30
```

Create custom command if not exists:
```php
// app/Console/Commands/CleanNotifications.php
$this->info('Cleaning notifications older than 30 days...');
\DB::table('notifications')
    ->where('created_at', '<', now()->subDays(30))
    ->delete();
```

---

## Contact
For questions about notification system, contact: **Developer Team**
