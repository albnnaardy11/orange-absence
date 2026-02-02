# Maintenance & Health

Panduan menjaga agar sistem tetap cepat, aman, dan sehat dalam jangka panjang.

## Data Backup

Sangat disarankan melakukan backup database minimal seminggu sekali.
- **Manual**: Gunakan Export SQL di phpMyAdmin.
- **Otomatis**: Gunakan plugin cPanel Backup atau Laravel Backup.

## Cache Management

Jika kamu merasa aplikasi terasa lambat atau ada perubahan kode yang tidak muncul, jalankan:
```bash
php artisan optimize:clear
```
Command ini akan membersihkan:
- Config cache
- Route cache
- View cache
- Application cache

## Monitoring Logs

Selalu pantau folder `storage/logs/laravel.log` jika terjadi error 500. Log ini akan memberitahumu baris kode mana yang bermasalah.

## Security Best Practices

1. **APP_KEY**: Jangan pernah mengganti `APP_KEY` di `.env` saat aplikasi sudah berjalan, karena ini akan membuat semua password user tidak bisa didekripsi dan session hancur.
2. **Updates**: Selalu jalankan `composer update` secara berkala dilingkungan lokal (bukan production) untuk memastikan patch keamanan terbaru sudah terpasang.
3. **Storage**: Pastikan folder `storage/app/public` tidak bisa diakses secara langsung (directory listing) demi privasi user.
