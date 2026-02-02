---
sidebar_position: 1
---

# Pendahuluan

Dokumentasi teknis untuk **Orange Absence System**. Sistem ini dibangun untuk menangani pelacakan kehadiran yang krusial dan manajemen keuangan untuk organisasi berbasis divisi.

## Filosofi Teknis

Proyek ini mengikuti filosofi "Strict & Transparent" (Ketat & Transparan):
1. **Eksplisit di atas Implisit**: Kami menghindari "magic methods" di mana kode yang eksplisit memberikan dukungan IDE dan keterbacaan yang lebih baik.
2. **Database First**: Relasi dipaksakan pada level database (foreign keys) untuk mencegah korupsi data.
3. **Keamanan Tipe (Type Safety)**: Menggunakan fitur PHP 8.3 seperti *constructor property promotion* dan *strict typing* di seluruh bagian backend.

## Modul Utama

### 1. Mesin Absensi
Memanfaatkan pemindaian kode QR dengan geofencing real-time. Menangani verifikasi garis lintang/bujur untuk memastikan pengguna secara fisik hadir di lokasi eskul.

### 2. Otomatisasi Keuangan
Sistem secara otomatis menghitung "Kas" (iuran mingguan). Melacak siapa yang belum membayar dan menyediakan log audit untuk semua transaksi tunai.

### 3. Dashboard Sekretaris
Panel admin berperforma tinggi yang dibangun dengan FilamentPHP, memungkinkan sekretaris untuk mengelola ratusan catatan dengan mudah.

## Memulai

Jika Anda adalah pengembang yang ingin berkontribusi:
1. Clone repository.
2. Jalankan `./deploy.sh` untuk menyiapkan lingkungan.
3. Gunakan `php artisan serve` untuk menjalankan server lokal.

:::warning Catatan Produksi
Selalu pastikan bahwa `APP_URL` di `.env` sudah diatur dengan benar ke domain produksi Anda, jika tidak, ikon QR dan link penyimpanan akan rusak.
:::
