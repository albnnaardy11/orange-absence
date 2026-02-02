# Deploy & Hosting

Panduan lengkap untuk melakukan deploy Orange Absence ke server produksi (Shared Hosting/cPanel atau VPS).

## Persyaratan Sistem

- **PHP**: 8.3+ (wajib)
- **Node.js**: v20+ (untuk dokumentasi)
- **Database**: MySQL 8.0+
- **Extensions**: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`.

## Langkah Deploy di cPanel

### 1. Unggah File
Pastikan folder `public` diarahkan dengan benar. Jika menggunakan Shared Hosting, letakkan isi folder `public` di dalam `public_html`.

### 2. Konfigurasi Lingkungan (`.env`)
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
Sistem ini sangat bergantung pada **Cron Jobs** untuk penagihan Kas otomatis dan reset harian. Tambahkan ini di menu Cron Jobs cPanel (setiap menit):
```bash
* * * * * /usr/local/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Perintah Optimasi

Setiap kali melakukan update kode di server, jalankan perintah ini untuk performa maksimal:
```bash
# Optimalisasi Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

:::warning Izin Folder
Pastikan folder `storage` dan `bootstrap/cache` memiliki izin **775** atau **755** agar Laravel bisa menulis file log dan cache.
:::
