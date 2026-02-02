# Integrasi API

Orange Absence menyediakan API yang kuat untuk integrasi eksternal dan portal mobile.

## Panduan Otentikasi

Permintaan API memerlukan otentikasi berbasis session atau token tergantung pada client.

### Untuk Portal Mobile
Portal mobile menggunakan otentikasi cookie stateful dari Laravel Sanctum.
1. `GET /sanctum/csrf-cookie` (untuk inisialisasi session)
2. `POST /login` (mengirim kredensial)

## Endpoint Utama

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/portal` | Mengambil dashboard user dan jadwal aktif. |
| `POST` | `/attendance/scan` | Mengirim payload QR dan koordinat GPS. |
| `GET` | `/member/payments` | Mengambil riwayat keuangan pribadi. |

## Dokumentasi sebagai Kode

Kami menggunakan `L5-Swagger` (OpenAPI 3.0). Dokumentasi di-generate langsung dari **PHP Attributes** di dalam Controller.

### Lihat Swagger UI Interaktif
Kamu bisa mengakses dokumentasi interaktif di server development lokal:
- URL: `http://localhost:8000/api/documentation`

:::tip Advice: Keamanan Integrasi
Jangan pernah mengekspos `APP_KEY` atau `JWT_SECRET` kamu. Aplikasi eksternal harus menggunakan Service Account khusus dengan izin terbatas.
:::

## Penanganan Error
API mengembalikan kode status HTTP standar:
- `200`: Success (Berhasil)
- `401`: Unauthorized (Butuh Login)
- `403`: Forbidden (Terlarang, misal: Geofence gagal)
- `422`: Validation Error (Gagal Validasi, misal: Data GPS tidak ada)
