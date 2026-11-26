## Ringkasan Hasil Pengujian Aktual P11

### 1. Tahap A: Pemeriksaan Kesiapan Artefak P10
* **Yang Dikerjakan / Berubah:**
  * Artefak P10 disalin ke workspace `dpwl-2522500038/` sebagai baseline pengembangan kumulatif P11.
  * Konfigurasi `base_url` pada `application/config/config.php` dan `RewriteBase` pada `.htaccess` diperiksa agar sesuai nama folder workspace.
  * Struktur direktori MVC dan basis data `db_dpwl_isabella` diperiksa (tabel `t_sewa`, `t_pilih`, `t_mobil`, `t_bayar`, `t_penyewa`; kolom `t_sewa.status_sewa`, `t_sewa.wkt_ambil`, `t_penyewa.ft_ktp`, `t_penyewa.ft_sim_a`).
  * Berkas `pertemuan-11/README.md` dibuat. Tidak ada perubahan source code dan struktur basis data.
* **Langkah Pengujian:**
  1. Menjalankan Laragon (Apache dan MySQL) lalu membuka `http://localhost/dpwl-2522500038/`.
  2. Login sebagai admin dan memastikan halaman administrasi tampil.
  3. Login sebagai penyewa dan memastikan halaman penyewa tampil.
  4. Memeriksa pemilihan kendaraan dan keranjang transaksi.
  5. Memeriksa checkout dan pengajuan transaksi.
  6. Memeriksa unggah bukti pembayaran DP.
  7. Memeriksa verifikasi DP oleh admin dan perubahan status pembayaran serta status transaksi.
  8. Memeriksa pencatatan pelunasan oleh admin.
  9. Memeriksa informasi dan dokumen transaksi.
  10. Memeriksa informasi kendaraan dan riwayat pembayaran.
  11. Menyiapkan transaksi uji berstatus `disetujui` dengan DP `valid` yang belum dilunasi.
* **Hasil Aktual:** Seluruh fitur P10 berfungsi dengan baik dan siap digunakan sebagai baseline pengembangan P11. Struktur direktori, konfigurasi, dan basis data tersedia. Tidak ada kendala dan tidak ada perbaikan yang dilakukan.
* **Status:** Berhasil