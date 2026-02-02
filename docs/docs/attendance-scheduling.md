# Absensi & Penjadwalan

Inti dari Orange Absence adalah sistem absensi pintar. Sistem ini menggabungkan lokasi GPS dan kode QR yang berputar untuk mencegah kecurangan.

## Cara Kerja Geofencing

Sistem menghitung jarak antara **Ponsel Pengguna** dan **Garis Lintang/Bujur Jadwal** menggunakan rumus Haversine.

- **Radius**: Default adalah 50 meter (bisa dikonfigurasi via env).
- **Periode Toleransi**: Pengguna dapat memindai hingga 15 menit sebelum dan 30 menit setelah jadwal dimulai.

:::tip Advice: Akurasi GPS
Jelaskan kepada pengguna bahwa mereka sebaiknya berada di luar ruangan atau di dekat jendela saat memindai. Gangguan GPS di dalam ruangan terkadang dapat menempatkan mereka sejauh 100m, yang menyebabkan error "Luar Area".
:::

## Logika Penjadwalan

Jadwal bersifat berulang. Sebuah jadwal didefinisikan oleh:
- **Lokasi**: Koordinat Lintang/Bujur (Lat/Long).
- **Waktu**: Waktu mulai dan berakhir.
- **Divisi**: Kelompok mana yang memiliki jadwal ini.

### Aturan "Auto-Absent"
Jika pengguna tidak memindai selama waktu jadwal, sistem tidak akan secara otomatis menandai mereka sebagai "Alpa". Sekretaris harus meninjau daftar "Missing" dan mengonfirmasi statusnya di akhir hari.

## Keamanan Kode QR

Kode QR **TIDAK statis**. Setiap sesi pemindaian menghasilkan tanda tangan unik.
1. Jika pengguna mencoba mengambil tangkapan layar (screenshot) dan mengirim QR ke teman, teman tersebut kemungkinan besar akan gagal dalam **Pemeriksaan Geofence**.
2. Jika sekretaris membuat ulang kode, kode lama akan langsung tidak berlaku.
