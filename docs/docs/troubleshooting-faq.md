# Troubleshooting & FAQ

Solusi cepat untuk masalah operasional yang sering ditemui oleh Secretary dan Member.

## Common Issues (Member)

### "Anda berada di luar area eskul"
- **Penyebab**: Akurasi GPS ponsel sedang lemah atau user benar-benar di luar radius.
- **Solusi**: Minta member untuk membuka Google Maps terlebih dahulu agar GPS "terkunci" (lock) ke lokasi yang benar, lalu kembali ke portal untuk scan.

### QR Code Tidak Bisa Discan
- **Penyebab**: Kamera kurang fokus atau kode sudah kadaluarsa (expired).
- **Solusi**: Refresh halaman portal untuk mendapatkan token QR terbaru. Pastikan lensa kamera bersih.

## Common Issues (Admin)

### Jadwal Tidak Muncul di Dashboard
- **Penyebab**: Jadwal belum diatur untuk hari ini atau divisi yang dipilih salah.
- **Solusi**: Cek menu **Schedules** di Admin Panel dan pastikan hari (Monday-Sunday) sudah dicentang dengan benar.

### Penagihan Kas Tidak Berjalan
- **Penyebab**: Cron Job di server tidak aktif atau mati.
- **Solusi**: Pastikan command `php artisan schedule:run` terdaftar di Cron Jobs server.

## FAQ

**Apakah data absen bisa diubah manual?**
Ya. Secretary bisa mengubah status lewat Admin Panel (menu Attendances) jika ada member yang lupa scan tapi terbukti hadir.

**Bagaimana jika member berganti Divisi?**
Admin cukup mengubah divisi di profil User. Riwayat absen lama akan tetap ada, tapi tagihan Kas selanjutnya akan mengikuti aturan divisi yang baru.
