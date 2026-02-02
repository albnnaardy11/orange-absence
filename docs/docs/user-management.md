# Manajemen User

Mengelola anggota di Orange Absence melibatkan peran (roles), izin (permissions), dan penugasan divisi.

## Roles & Permissions

Sistem menggunakan `spatie/laravel-permission`. Ada tiga tingkatan utama:

| Role | Level Akses | Tanggung Jawab |
| :--- | :--- | :--- |
| **Super Admin** | Full | Konfigurasi sistem, Manajemen Database, Log Global. |
| **Secretary** | Divisi | Mengelola absensi dan keuangan divisi mereka sendiri. |
| **Member** | Pribadi | Melihat riwayat absensi dan log kas sendiri. |

### Pro-Tip: Menambah Sekretaris Baru
Saat menambah sekretaris baru, pastikan Anda menugaskan mereka ke dalam sebuah **Divisi**. Tanpa penugasan divisi, sekretaris mungkin akan melihat data kosong atau menemui error pada scope data spesifik divisi.

## Suspensi Akun

Jika seorang anggota disuspensi:
1. Mereka **tidak bisa** memindai kode QR.
2. Mereka **tidak bisa** login ke portal anggota.
3. Riwayat kehadiran mereka tetap tersimpan tetapi mereka disembunyikan dari daftar absensi aktif.

:::info Re-aktivasi
Suspensi hanyalah flag boolean sederhana di tabel `users`. Mengaktifkan kembali user akan langsung memulihkan semua akses mereka tanpa kehilangan data.
:::

## Logika Divisi

Setiap user terdaftar di tepat satu divisi. Divisi menentukan:
- **Jadwal** mana yang tersedia untuk user tersebut.
- **Sekretaris** mana yang bisa melihat data mereka.
- Jumlah **Kas** yang harus mereka bayar setiap minggunya.
