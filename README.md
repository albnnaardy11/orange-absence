# 🍊 ORANGE ABSENCE & CASH SYSTEM

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Filament-V3-D97706?style=for-the-badge&logo=filament" alt="Filament V3">
  <img src="https://img.shields.io/badge/Concurrency-10k+-065F46?style=for-the-badge" alt="10k Users">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="MIT License">
</p>

<p align="center">
  <strong>Empowering Seamless Attendance and Financial Mastery</strong><br>
  <em>Engineered for massive scalability and real-time operational excellence.</em>
</p>

---

## 📖 Overview

**Orange Absence** adalah platform manajemen berbasis Laravel yang dirancang khusus untuk menangani absensi, penjadwalan, dan pencatatan keuangan dalam organisasi skala besar. Sistem ini menggabungkan otomasi tingkat tinggi, kontrol akses berbasis peran (RBAC), dan manajemen data *real-time* untuk menyederhanakan alur kerja operasional yang kompleks.

### ✨ Mengapa Memilih Orange Absence?

| Fitur Utama | Deskripsi Singkat |
| :--- | :--- |
| 🧩 **Key Management** | Generasi kode verifikasi otomatis untuk kontrol akses yang aman. |
| 🎯 **Multi-Role Dashboard** | Panel khusus untuk Admin, Sekretaris, dan Member dengan pengalaman yang dipersonalisasi. |
| 💾 **Data Integration** | Pelacakan absensi dan *cash logs* yang mulus dengan model relasional yang kuat. |
| 🚀 **High Concurrency** | Dioptimalkan untuk menangani **10,000+ pengguna aktif** secara bersamaan. |

---

## 🚀 Fitur Unggulan (Core Logic)

<details>
<summary><b>📅 Lifecycle Jadwal Otomatis (Klik untuk detail)</b></summary>

- **Dynamic Display**: Member hanya melihat jadwal aktif yang tersedia pada hari berjalan.
- **Auto-Expiration**: Jadwal otomatis disembunyikan dan berubah status menjadi "Finished" setelah melewati `end_time`.
- **Background Cleanup**: Worker otomatis (`expire:schedules`) berjalan setiap menit untuk membersihkan kode verifikasi usang.
</details>

<details>
<summary><b>💰 Manajemen Kas Cerdas (Klik untuk detail)</b></summary>

- **Weekly Generation**: Command konsol otomatis menghasilkan log kas mingguan untuk seluruh member.
- **Overdue Tracking**: Logika cerdas untuk mendeteksi keterlambatan pembayaran berdasarkan deadline (Jumat, 17:00).
- **Batch Processing**: Optimasi query database untuk menangani ribuan catatan keuangan sekaligus.
</details>

<details>
<summary><b>🔐 Arsitektur Multi-Role (Klik untuk detail)</b></summary>

- **Super Admin**: Kontrol penuh atas divisi, pengguna, dan pengaturan global.
- **Secretary**: Fokus pada manajemen log kas dan laporan absensi.
- **Member**: Panel responsif (mobile-ready) untuk verifikasi kehadiran dan riwayat pribadi.
</details>

---

## ⚡ Performa & Skalabilitas

Kami melakukan audit performa komprehensif untuk menjamin stabilitas di bawah beban berat:
* **Query Optimization**: Implementasi *Full Eager Loading* (`with`) untuk mengeliminasi isu N+1.
* **Database Indexing**: Kolom kritis (`status`, `day`, `date`, `user_id`) telah diindeks untuk respon milidetik.
* **Efficient Aggregations**: Field kalkulasi menggunakan `withCount` untuk meminimalkan penggunaan memori.
* **Redis Integration**: Dioptimalkan untuk driver cache dan session menggunakan Redis.

---

## 🛠 Tech Stack

- **Backend**: Laravel 11.x
- **Admin Panel**: Filament V3
- **Database**: MySQL / MariaDB (Optimized Indexing)
- **Permissions**: Spatie Laravel-Permission
- **Frontend**: Blade, Tailwind CSS, Vite, Axios

---

## 📦 Jalankan Project

### Prerequisites
Pastikan lingkungan Anda memiliki: **PHP >= 8.2**, **Composer**, **Node.js & NPM**.

### Langkah Instalasi
1. **Clone & Masuk ke Direktori**:
   ```bash
   git clone [https://github.com/albnnaardy11/orange-absence.git](https://github.com/albnnaardy11/orange-absence.git)
   cd orange-absence
