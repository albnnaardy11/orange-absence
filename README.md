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
| 📊 **Financial Analytics** | Laporan kas otomatis dengan sistem deteksi keterlambatan (Late) yang presisi. |
| 🧩 **Stateless Design** | Arsitektur yang mendukung skalabilitas horizontal melalui integrasi Redis & S3. |
| 🚀 **High Concurrency** | Teruji untuk menangani **10,000+ pengguna aktif** secara simultan. |

---

## 🚀 Logika Bisnis & Fitur Unggulan

<details>
<summary><b>📅 Lifecycle Jadwal Otomatis</b></summary>

- **Dynamic Availability**: Member hanya melihat jadwal aktif pada hari berjalan (Context-Aware UI).
- **Auto-Expiration**: Sistem otomatis menutup jadwal dan membatalkan kode verifikasi setelah melewati `end_time`.
- **Background Cleanup**: Job terjadwal `expire:schedules` menjaga integritas data tanpa mengganggu performa dashboard.
</details>

<details>
<summary><b>💰 Intelligent Cash Management</b></summary>

- **Automated Billing**: Generasi log kas mingguan masif menggunakan Laravel Queue untuk efisiensi memori.
- **Deadline Enforcement**: Audit otomatis status pembayaran berdasarkan deadline ketat (Jumat, 17:00).
- **Audit Trails**: Setiap transaksi finansial tercatat secara permanen untuk kebutuhan audit (via Spatie Activity Log).
</details>

<details>
<summary><b>🔐 Multi-Tier Architecture</b></summary>

- **Super Admin**: Kontrol infrastruktur, manajemen divisi, dan pemantauan sistem global.
- **Secretary**: Fokus pada operasional harian, validasi kehadiran, dan manajemen keuangan.
- **Member**: Antarmuka responsif untuk klaim kehadiran via kode unik dan riwayat kontribusi.
</details>

---

## ⚡ Performa & Skalabilitas

* **Query Efficiency**: Implementasi Eager Loading menyeluruh pada Filament Resources untuk eliminasi isu N+1.
* **Database Indexing**: Strategi indexing pada kolom kritis (Composite Indexes) untuk kecepatan akses data milidetik.
* **Redis Integration**: Direkomendasikan sebagai backbone untuk session, cache, dan manajemen queue.
* **Enterprise Standards**: Implementasi Strict Typing (PHP 8.2+) dan Audit Trails untuk integritas data.

---

## 🛠 Tech Stack

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Admin Panel**: Filament V4 (TALL Stack)
- **Database**: MySQL/MariaDB (Advanced Composite Indexing)
- **Infrastructure**: Redis, S3 Compatible Storage, Laravel Horizon (Recommended)
- **Security**: Spatie Permission (Cached), Rate Limiting, Audit Log

---

## 📦 Langkah Instalasi

1. **Clone & Setup**:
   ```bash
   git clone https://github.com/albnnaardy11/orange-absence.git
   cd orange-absence
   cp .env.example .env
   composer install
   ```

2. **Optimization & Database**:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   npm install && npm run build
   ```

3. **Run Production**:
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

<p align="center">Built for Excellence & Scalability</p>
