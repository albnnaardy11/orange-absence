# 🍊 ORANGE ABSENCE & CASH SYSTEM

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Filament-V4-D97706?style=for-the-badge&logo=filament" alt="Filament V4">
  <img src="https://img.shields.io/badge/Concurrency-10k+-065F46?style=for-the-badge" alt="10k Users">
  <img src="https://img.shields.io/badge/Standard-Unicorp_Enterprise-blueviolet?style=for-the-badge" alt="Enterprise Standard">
</p>

<p align="center">
  <strong>Empowering Seamless Attendance and Financial Mastery</strong><br>
  <em>Engineered for massive scalability and real-time operational excellence.</em>
</p>

---

## 📖 Overview

**Orange Absence** adalah platform manajemen kehadiran dan finansial berbasis Laravel yang dirancang untuk skala enterprise. Sistem ini menggabungkan otomasi tingkat tinggi, kontrol akses berbasis peran (RBAC), dan arsitektur data yang dioptimalkan untuk menangani beban kerja masif secara real-time.

### ✨ Enterprise Core Features

| Fitur Utama | Deskripsi Standar Unicorp |
| :--- | :--- |
| 🛡️ **Advanced RBAC** | Permission management yang dioptimalkan dengan caching layer untuk performa tinggi. |
| 📱 **Fully Responsive** | Optimasi penuh untuk semua device (Mobile, Tablet, Desktop) dengan touch-friendly interface. |
| 📍 **GPS Cookie Sync** | Sinkronisasi GPS berlapis (Form + Cookies) untuk menjamin akurasi data check-in di berbagai browser. |
| 🌍 **Jakarta Orientation** | Teroptimasi untuk penggunaan di wilayah Jakarta (WIB) dengan koordinat default Jakarta Timur. |
| 🔔 **Debt Reminder** | Notifikasi push otomatis untuk penunggak kas (Smart Alert System). |
| 📟 **Activity Audit** | Tracking setiap mutasi data, IP Address, dan perangkat pengguna yang digunakan. |
| 📊 **Smart Reporting** | Export laporan bulanan otomatis dalam format profesional Excel & PDF. |
| 🚀 **High Concurrency** | Teruji untuk menangani **10,000+ pengguna aktif** secara simultan. |

---

## 🚀 Logika Bisnis & Fitur Unggulan

<details>
<summary><b>📍 Geofencing & GPS Sync (Bulletproof)</b></summary>

- **Multi-Channel Sync**: Menggunakan jalur `Hidden Input` dan `Browser Cookies` (unencrypted channel) untuk memastikan kordinat GPS sampai ke server meskipun sesi browser tidak stabil.
- **Smart Validation**: Script otomatis mendeteksi status GPS (Locked/Error) dengan indikator visual real-time di UI.
- **Radius Tolerance**: Memberikan toleransi extra 10 meter untuk akurasi GPS perangkat mobile yang bervariasi.
</details>

<details>
<summary><b>📅 Lifecycle Jadwal Otomatis</b></summary>

- **Dynamic Availability**: Member hanya melihat jadwal aktif pada hari berjalan (Context-Aware UI).
- **Auto-Code Generation**: Kode verifikasi unik digenerate otomatis setiap kali jadwal dimulai (jika fitur Auto-Generate aktif).
- **Overnight Support**: Sistem mendukung jadwal lintas hari (Shift Malam) dengan deteksi cerdas.
</details>

<details>
<summary><b>💰 Intelligent Cash Management</b></summary>

- **Automated Weekly Billing**: Generasi tagihan kas otomatis setiap minggu (Selasa, 05:00) untuk seluruh member.
- **Deadline Enforcement**: Audit otomatis status pembayaran berdasarkan deadline ketat (Jumat, 17:00).
- **Attendance Linkage**: Sistem otomatis menghubungkan pembayaran kas dengan kehadiran member yang sah.
</details>

<details>
<summary><b>🛠️ Audit & Security Layers</b></summary>

- **Login Tracker**: Mencatat waktu login terakhir, IP Address, dan Device Info langsung ke tabel user.
- **Spatie Activity Log**: Setiap perubahan data kritis (Lunas/Hadir/Admin Action) tercatat lengkap dengan metadata jaringan.
- **Debt Notification**: Pemicu notifikasi otomatis (Database Notification) kepada member dengan tunggakan >= 3 kali.
</details>

<details>
<summary><b>⚖️ Point System & Auto-Lock</b></summary>

- **Fair Penalty**: Poin bertambah otomatis (+10 Alfa, +2 Izin/Sakit).
- **Auto-Suspension**: Akun terkunci otomatis (Suspent) jika poin mencapai 30.
- **Admin Control**: Resource khusus `SuspendedMember` untuk review dan pemulihan akun (Reset Poin).
</details>

<details>
<summary><b>📱 Dynamic QR, Anti-Cheat & Smart Notifications [NEW]</b></summary>

- **Encrypted Payload**: QR Code berisi data terenkripsi (Division ID + Timestamp + Secret).
- **Rolling Codes**: QR valid hanya 60 detik (30s refresh + toleransi).
- **Double Validation**: Absensi wajib lolos dekripsi QR **DAN** radius Geofencing (10 meter) secara simultan.
- **Anti-Double Protection**: Sistem "Smart Lock" mencegah user absen dua kali di hari yang sama, baik via QR maupun Kode Manual.
- **Interactive UI**: Sapaan dinamis (Pagi/Siang/Sore), Kuotasi Motivasi acak, dan Toast Alert yang interaktif.
</details>

---

## ⚡ Performa & Skalabilitas (Audit v2.0)
 
 Pada update terbaru, sistem telah melalui audit performa menyeluruh:
 
 * **Optimasi Database (Latency < 50ms)**: Penambahan Composite Indexes pada kolom kritis untuk mempercepat query filtering dan duplicate check.
 * **SPA Mode Enabled**: Menggunakan Filament SPA mode untuk transisi halaman instan (Zero Refresh UX).
 * **Method Memoization**: Optimasi logic pada method-method berat untuk mencegah query database berulang.
 * **SEO Excellence**: Optimasi struktur HTML dan meta description untuk mencapai skor SEO > 90.
 * **Resource Eager Loading**: Implementasi Eager Loading menyeluruh pada seluruh resource untuk eliminasi total isu N+1.
 * **Production Optimized**: Script deployment (`deploy.sh`) kini menyertakan optimasi level produksi (`view:cache`, dll).

---

## 🛠 Tech Stack

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Admin Panel**: Filament V4 (TALL Stack)
- **Database**: MySQL/MariaDB (Advanced Composite Indexing)
- **Reporting**: Barryvdh DomPDF & Maatwebsite Excel
- **Security**: Spatie Permission (Cached) & Spatie Activity Log

---

## 📦 Langkah Instalasi & Host (cPanel)

1. **Setup Awal**:
   ```bash
   composer install && npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   npm run build
   ```

2. **Produksi (Cron Job)**:
   Tambahkan baris berikut di Cron Job cPanel Anda (Setiap Menit):
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Storage Link**:
   ```bash
   php artisan storage:link
   ```

<p align="center">Built for Excellence & Scalability</p>