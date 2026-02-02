# Deployment & Hosting

Panduan lengkap untuk melakukan deploy Orange Absence ke server produksi (Shared Hosting/cPanel atau VPS).

## Requirements

- **PHP**: 8.3+ (wajib)
- **Node.js**: v20+ (untuk dokumentasi)
- **Database**: MySQL 8.0+
- **Extensions**: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`.

## cPanel Deployment Steps

### 1. File Upload
Pastikan folder `public` diarahkan dengan benar. Jika menggunakan Shared Hosting, letakkan folder `public` di dalam `public_html`.

### 2. Environment Configuration (`.env`)
Pastikan variabel berikut disesuaikan:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nama-domain-kamu.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=pass_db
```

### 3. Setup Cron Jobs
Sistem ini sangat bergantung pada **Cron Jobs** untuk penagihan Kas otomatis dan reset harian. Tambahkan ini di cPanel Cron Jobs (setiap menit):
```bash
* * * * * /usr/local/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Optimization Commands

Setiap kali melakukan update kode di server, jalankan command ini untuk performa maksimal:
```bash
# Optimalisasi Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

:::warning Permohonan Izin Folder
Pastikan folder `storage` dan `bootstrap/cache` memiliki izin **775** atau **755** agar Laravel bisa menulis file log dan cache.
:::
