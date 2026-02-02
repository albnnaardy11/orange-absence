# Keuangan & Log Kas

Modul keuangan mengotomatiskan penagihan dan pelacakan "Kas" divisi.

## Penagihan Mingguan Otomatis

Setiap hari Senin, sebuah *background job* menghitung iuran untuk setiap anggota aktif.

- **Perhitungan**: Berdasarkan `weekly_fee` yang diatur di tabel `divisions`.
- **Tunggakan**: Jika seorang anggota belum membayar selama 3 minggu, mereka akan ditandai dalam "Daftar Merah" Sekretaris.

## Mengelola Log Kas

Setiap pembayaran harus dicatat melalui Panel Admin.
1. **Sumber**: Anggota yang membayar.
2. **Jumlah**: Total yang dibayar (bisa cicilan/parsial).
3. **Catatan Admin**: Digunakan untuk melacak bukti fisik atau transfer bank.

:::danger Integritas Keuangan
Setelah transaksi "Finalized" (Selesai), transaksi tersebut tidak dapat dihapus oleh Sekretaris. Hanya Super Admin dengan akses database yang dapat membatalkan transaksi yang sudah selesai untuk mencegah penggelapan.
:::

## Praktik Terbaik

### Jejak Audit (Audit Trail)
Selalu gunakan sistem **Poin** sebagai *reward*. Sistem dapat dikonfigurasi untuk secara otomatis memberikan poin kepada pengguna yang membayar Kas tepat waktu.

### Pembayaran Parsial
Jika anggota tidak dapat membayar penuh, sistem akan melacak sisa saldo. Sekretaris harus mencatat persis berapa yang diterima untuk menjaga akurasi "Total Uang Tunai di Tangan".
