# 🍊 Orange Absence & Cash System

A high-performance, professional management system for attendance and cash logging, built with **Laravel 11**, **Filament V3**, and **Spatie Permissions**. Engineered for scalability and capable of handling **10,000+ concurrent users**.

## 🚀 Key Features

### 📅 Automated Schedule Lifecycle
- **Dynamic Display**: Members only see active classes scheduled for the current day.
- **Auto-Expiration**: Classes automatically hide and transition to "Finished" status once past their `end_time`.
- **Background Cleanup**: An automated worker (`expire:schedules`) runs every minute to clean up verification codes and update statuses.

### 💰 Automated Cash Management
- **Weekly Generation**: Automated console command generates weekly unpaid cash logs for all members.
- **Overdue Tracking**: Intelligent logic determines overdue payments based on custom deadlines (Friday 17:00).
- **Batch Processing**: Optimized database queries for massive record handling.

### 🔐 Multi-Role Architecture
- **Super Admin**: Full control over divisions, users, and global settings.
- **Secretary**: Management of cash logs and attendance reports.
- **Member**: Mobile-optimized panel for attendance verification and personal history.

## ⚡ Performance & Scalability

This project has undergone a comprehensive performance audit to ensure stability under heavy load:
- **Query Optimization**: Full eager loading (`with`) implemented across all resources to eliminate N+1 issues.
- **Database Indexing**: Critical columns (`status`, `day`, `date`, `user_id`) are indexed for millisecond response times.
- **Efficient Aggregations**: Calculated fields like "Financial Status" use `withCount` to minimize memory usage.
- **Production Ready**: Includes a `deploy.sh` script for automated caching (Config, Route, View, Icon, and Filament components).
- **Redis Integration**: Optimized for Redis session and cache drivers.

## 🛠 Tech Stack
- **Framework**: Laravel 11.x
- **Admin Panel**: Filament V3
- **Database**: MySQL / MariaDB (Optimized)
- **Role Management**: Spatie Laravel-Permission
- **Frontend**: Blade, Tailwind CSS, Heroicons

## 📦 Deployment

1. **Clone the repository**:
   ```bash
   git clone https://github.com/your-username/orange-absence.git
   ```

2. **Run the optimized deployment script**:
   ```bash
   sh deploy.sh
   ```
   *This script handles composer installation, migrations, and warming up all application caches.*

3. **Configure Environment**:
   - Set `SESSION_DRIVER=redis` and `CACHE_STORE=redis` for maximum concurrency.
   - Update `APP_DEBUG=false` in production.

##  cron / Task Scheduling
Ensure the Laravel Scheduler is running on your server:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---
*Developed with focus on performance, aesthetics, and reliability.*
