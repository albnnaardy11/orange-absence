# Skema Database

Orange Absence menggunakan arsitektur database relasional yang kuat untuk memastikan integritas data.

## Skema Inti

### Tabel Users
Pusat dari segala data. Menampung informasi personal, poin, dan status suspensi.
- Berelasi ke `divisions` (BelongsTo).
- Berelasi ke `roles` (Spatie Permissions).

### Tabel Attendances
Mencatat setiap scan QR yang berhasil.
- **Foreign Keys**: `user_id`, `schedule_id`.
- **Metadata**: Menyimpan koordinat GPS saat scan dilakukan untuk kebutuhan audit.

### Tabel CashLogs
Mencatat sejarah keuangan.
- Setiap entri memiliki `causer_id` (admin yang mencatat) dan `user_id` (member yang membayar).
- Menggunakan `decimal(15,2)` untuk presisi nilai uang.

## Saran Relasi Entitas (ERD)

:::info On Delete Cascade
Hampir semua relasi menggunakan `onDelete('cascade')` pada level database. Jika sebuah Divisi dihapus, maka seluruh jadwal yang terkait dengan divisi tersebut akan ikut terhapus secara otomatis untuk menjaga kebersihan database.
:::

## Jejak Audit (Audit Trail)

Kami menggunakan `spatie/laravel-activitylog` untuk mencatat setiap perubahan data sensitif. Kamu bisa melihat siapa yang mengubah status kehadiran seseorang atau siapa yang mengedit nominal uang Kas di tabel `activity_log`.
