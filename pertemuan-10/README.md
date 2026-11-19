# README P10 — Pelunasan dan Dokumen Transaksi

Lanjutan aplikasi Sistem Informasi Penyewaan Kendaraan (PHP 8.4, homemade MVC, MySQLi, prepared statement) dari P9. Seluruh fitur P1–P9 dipertahankan.

## 1. Identitas Mahasiswa
- Nama        : Isabella Nunes Fernandes
- NIM         : 2522500038
- Kelas       : SI3A
- Mata Kuliah : Desain dan Pemrograman Web Lanjutan (IS203), Gasal 2026/2027

## 2. Artefak dan Kondisi Awal
- Baseline   : hasil akhir P9 (`pertemuan-09`), disalin ke workspace `dpwl-nim\`.
- Lingkungan : Laragon (Apache + MySQL 8.0), PHP 8.4, URL `http://localhost/dpwl-nim/`.
- Basis data : `db_dpwl_isabella` (dump akhir: `db_dpwl_isabella.sql`).
- Backup basis data sebelum P10: `<ISI: nama file backup sebelum ALTER TABLE>.sql`

## 3. Hasil Pemeriksaan Fitur P9 (Tahap A)
| No | Skenario | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Login admin, dashboard tampil | Dashboard tampil | Berhasil |
| 2 | Login penyewa, katalog tampil | Katalog tampil | Berhasil |
| 3 | Menu Pembayaran Saya: daftar dan detail pembayaran DP | Daftar dan detail tampil | Berhasil |
| 4 | Penyewa mengunggah bukti DP | Pesan berhasil, status pending | Berhasil |
| 5 | Admin membuka daftar dan detail verifikasi, bukti tampil | Daftar, detail, dan bukti tampil | Berhasil |
| 6 | Admin menerima DP | Pembayaran valid, transaksi disetujui | Berhasil |
| 7 | Detail kendaraan pada transaksi | Tetap tampil | Berhasil |

Temuan baseline: tidak ada galat yang menghambat P10.

## 4. Tahapan Pengembangan P10
| Tahap | Isi | Status |
|---|---|---|
| A | Baseline P9 dan dokumentasi | Selesai |
| B | Status `lunas` pada `t_sewa.status_sewa` | Selesai |
| C | Model pelunasan (`Pembayaran_model.php`) | Selesai |
| D | Controller pelunasan dan dokumen transaksi | Selesai |
| E | Route dan menu sidebar | Selesai |
| F | View daftar dan detail pelunasan | Selesai |
| G | View dokumen transaksi | Selesai |

## 5. Daftar Berkas Ditambah / Diubah
| Tahap | Berkas | Keterangan |
|---|---|---|
| A | `pertemuan-10\README.md` | Dokumentasi baseline dan hasil pemeriksaan P9 |
| B | `db_dpwl_isabella.sql` | Dump basis data setelah penambahan ENUM `lunas` |
| C | `application\models\Pembayaran_model.php` | Menambahkan `getDaftarPelunasan()`, `getDetailPelunasan()`, `buatKodeBayarPelunasan()`, `simpanPelunasan()` |
| C | `application\models\Transaksi_model.php` | `checkout()` kini memegang named lock `dpwl_payment_code_sequence` (sama dengan lock pelunasan) |
| D | `application\controllers\Pembayaran_controller.php` | Menambahkan `pelunasanIndex()`, `pelunasanDetail()`, `prosesPelunasan()`, `dokumenTransaksi()` + helper `wajibMetode()`, `redirectDenganPesan()`, `simpanFileBukti()`, `hapusBuktiBaru()` |
| E | `application\config\routes.php` | Menambahkan 4 route P10 |
| E | `application\views\templates\sidebar.php` | Menambahkan menu Pelunasan Transaksi |
| E | `system\core\router.php` | Menghapus baris kosong sebelum `<?php` |
| F | `application\views\admin\pelunasan.php` | Baru: daftar pelunasan |
| F | `application\views\admin\pelunasan_detail.php` | Baru: detail dan formulir pelunasan |
| G | `application\views\admin\dokumen_transaksi.php` | Baru: dokumen transaksi siap cetak |

Method P9 (`index()`, `detail()`, `verifikasi()`) tetap dipertahankan. Fungsi DP/verifikasi P9 tetap berada di `Transaksi_model.php`, sedangkan `Pembayaran_model.php` berisi method pelunasan P10.

## 6. Perubahan Struktur Basis Data
### Tahap B — Status `lunas` pada `t_sewa.status_sewa`
Nilai ENUM `lunas` ditambahkan agar transaksi yang sudah dilunasi dapat dibedakan dari transaksi `disetujui`. Seluruh nilai lama dipertahankan. Status kendaraan pada `t_mobil` tidak diubah.

SQL yang dijalankan (satu kali):

```sql
ALTER TABLE `t_sewa`
  CHANGE `status_sewa` `status_sewa`
  ENUM('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang','lunas')
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'keranjang';
```

Pemeriksaan:
| No | Pemeriksaan | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Tabel `t_sewa` pada tab Structure dapat dibuka | Dapat dibuka | Berhasil |
| 2 | Kolom `status_sewa` diperiksa | Kolom ENUM ditemukan | Berhasil |
| 3 | Nilai `lunas` tersedia | Tersedia pada urutan ke-8 | Berhasil |
| 4 | Nilai ENUM lama tetap ada | Ketujuh nilai lama tetap ada | Berhasil |
| 5 | Hanya kolom `status_sewa` yang berubah | Kolom lain dan data lama tidak berubah | Berhasil |

Kendala: tidak ada.

## 7. Implementasi Model Pelunasan (Tahap C)
Berkas: `application\models\Pembayaran_model.php`

| Method | Fungsi |
|---|---|
| `getDaftarPelunasan()` | Mengambil transaksi berstatus `disetujui`/`lunas` yang memiliki detail di `t_pilih`; menghitung `total_transaksi` (snapshot `hrg_sewa * n_hari`), `total_terbayar` (hanya `sttus_byr = 'valid'`), dan `sisa_kewajiban`. Transaksi ditampilkan bila sisa > 0 atau berstatus `lunas`. |
| `getDetailPelunasan($kodeSewa)` | Mengambil data transaksi, penyewa, total, sisa kewajiban, riwayat pembayaran (`t_bayar`), dan rincian kendaraan (`t_pilih` + `t_mobil`). Mengembalikan `null` bila tidak ditemukan. |
| `buatKodeBayarPelunasan($tanggalKode)` | Membuat kode `PAY/YYYYMMDD/NN` (maks. 99 per hari), dipanggil saat named lock dipegang. |
| `simpanPelunasan($kodeSewa, $metode, $bukti)` | Mencatat pelunasan penuh oleh admin secara atomik (alur di bawah). |

Alur `simpanPelunasan()` (satu transaksi basis data):
1. `GET_LOCK('dpwl_payment_code_sequence')`, kunci yang sama dengan checkout DP P9.
2. `begin_transaction()`.
3. `SELECT ... FOR UPDATE` pada transaksi sewa.
4. Validasi status (`disetujui`/`lunas`), total > 0, sisa kewajiban > 0, dan sisa ≤ 8.388.607 (batas `MEDIUMINT`).
5. Pastikan belum ada record `jns_byr = 'pelunasan'` untuk transaksi tersebut.
6. Buat kode pembayaran.
7. `INSERT` ke `t_bayar` (`jns_byr = 'pelunasan'`, `sttus_byr = 'valid'`, nominal = seluruh sisa kewajiban).
8. `UPDATE t_sewa SET status_sewa = 'lunas'` dan pastikan `affected_rows = 1`.
9. `COMMIT`; bila gagal `ROLLBACK`; named lock selalu dilepas pada `finally`.

Ketentuan: metode hanya `tunai`/`transfer`; transfer wajib bukti (`ft_byr`), tunai tanpa bukti; satu transaksi hanya satu record pelunasan; status kendaraan (`t_mobil`) tidak diubah.

Perubahan pendukung pada `Transaksi_model::checkout()`: lock `dpwl_payment_code_sequence` diambil sebelum transaksi dimulai dan dilepas pada `finally`, sehingga nomor urut kode pembayaran DP dan pelunasan tidak bentrok.

### Hasil Pemeriksaan Model (C.5)
| No | Pemeriksaan | Hasil Aktual | Status |
|---|---|---|---|
| 1 | `getDaftarPelunasan()` hanya memuat transaksi `disetujui` dengan sisa > 0 (dan `lunas`) | Daftar sesuai ketentuan | Berhasil |
| 2 | `getDetailPelunasan()` memuat total, pembayaran valid, sisa, riwayat, kendaraan | Seluruh data termuat | Berhasil |
| 3 | Format kode pembayaran `PAY/YYYYMMDD/NN` | Kode sesuai format | Berhasil |
| 4 | Nominal pelunasan = sisa kewajiban | Nominal sama dengan sisa kewajiban | Berhasil |
| 5 | Metode selain tunai/transfer ditolak | Ditolak | Berhasil |
| 6 | Transaksi yang sudah memiliki record pelunasan tidak dapat dilunasi lagi | Ditolak | Berhasil |
| 7 | Pelunasan mengubah `status_sewa` menjadi `lunas` | Status berubah menjadi `lunas` | Berhasil |
| 8 | Status kendaraan tidak berubah | `t_mobil` tidak berubah | Berhasil |
| 9 | Kegagalan proses menyebabkan ROLLBACK | Tidak ada data tersimpan sebagian | Berhasil |

Kendala tahap C: `checkout()` P9 belum memakai named lock yang sama dengan pelunasan (lihat bagian 9).

## 8. Implementasi Controller, Route, dan View (Tahap D–G)
- **D. Controller** (`Pembayaran_controller.php`): `pelunasanIndex()` (GET), `pelunasanDetail()` (GET), `prosesPelunasan()` (POST, pola Post-Redirect-Get), dan `dokumenTransaksi()` (GET). Semua dilindungi `require_admin_login()`. Perhitungan total dan sisa kewajiban tetap di Model.
- **E. Route dan menu**: `admin/pembayaran/pelunasan`, `admin/pembayaran/pelunasan/proses/(:any)`, `admin/pembayaran/dokumen/(:any)`, `admin/pembayaran/pelunasan/(:any)`. Kode transaksi pada URL memakai `~` pengganti `/` (contoh `INV~20261029~02`). Menu Pelunasan Transaksi ditambahkan pada sidebar admin.
- **F. View**: `pelunasan.php` (daftar transaksi dan tautan Detail / Proses) dan `pelunasan_detail.php` (info transaksi, rincian kendaraan, ringkasan, riwayat pembayaran, formulir tunai/transfer). Formulir hilang bila transaksi sudah `lunas`. Nominal tidak diisi manual oleh admin.
- **G. Dokumen transaksi**: `dokumen_transaksi.php` memuat informasi transaksi, identitas penyewa, rincian kendaraan, riwayat pembayaran, dan ringkasan. Data berasal dari tabel `t_sewa`, `t_penyewa`, `t_pilih`, `t_mobil`, dan `t_bayar`. Tombol Cetak / Simpan sebagai PDF memakai fitur cetak peramban dan disembunyikan saat dicetak (`@media print`).

## 9. Kendala, Debugging, dan Perbaikan
| Tahap | Kendala | Perbaikan |
|---|---|---|
| A | Tidak ada | - |
| B | Tidak ada | - |
| C | `checkout()` P9 belum memakai named lock `dpwl_payment_code_sequence` seperti yang dirujuk modul | Lock diambil sebelum transaksi dimulai dan dilepas pada `finally` |
| D | Folder `uploads/bayar/` privat (`.htaccess` Require all denied), sehingga tautan `base_url('uploads/bayar/...')` pada contoh modul akan 403 | Tautan bukti memakai `transaksi/bukti?kode_bayar=` (dilayani controller P9, hanya admin atau pemilik) |
| D | Contoh modul memakai `$_SESSION['flash_pembayaran']`, sedangkan P9 memakai `set_flash_message()` / `get_flash_message()` | P10 memakai helper flash P9 agar konsisten |
| D/F | Formulir POST admin belum terlindungi CSRF | Ditambahkan `csrf_field()` dan `csrf_validate()` seperti P9 |
| E | `routes.php` dan `router.php` diawali baris kosong sebelum `<?php` (dapat memicu "headers already sent") | Baris kosong dihapus |
| F | Tidak ada | - |
| G | Tidak ada | - |

## 10. Pengujian Fungsional Tahap D–G
| No | Tahap | Skenario | Hasil Aktual | Status |
|---|---|---|---|---|
| 1 | E | Menu Pelunasan Transaksi tampil di sidebar admin dan membuka daftar | Menu tampil dan daftar terbuka | Berhasil |
| 2 | F | Daftar hanya memuat transaksi `disetujui` dengan sisa > 0 (dan `lunas`) | Daftar sesuai ketentuan | Berhasil |
| 3 | F | Detail / Proses membuka transaksi yang sesuai (kode `INV~...`) | Detail transaksi yang benar terbuka | Berhasil |
| 4 | F | Pelunasan tunai: berhasil, status `lunas`, tanpa bukti | Tercatat valid, status `lunas` | Berhasil |
| 5 | F | Pelunasan transfer tanpa bukti ditolak | Ditolak dengan pesan kesalahan | Berhasil |
| 6 | F | Pelunasan transfer dengan bukti valid (JPG/PNG/WEBP, maks. 5 MB) berhasil | Tercatat valid, bukti tersimpan | Berhasil |
| 7 | F | Berkas bukan gambar atau lebih dari 5 MB ditolak | Ditolak dengan pesan kesalahan | Berhasil |
| 8 | F | Transaksi `lunas` tidak menampilkan formulir lagi | Formulir tidak tampil | Berhasil |
| 9 | F | Pelunasan kedua untuk transaksi yang sama ditolak | Ditolak | Berhasil |
| 10 | D | Akses tanpa login admin dialihkan ke halaman login | Dialihkan ke login | Berhasil |
| 11 | D | Akses GET ke route proses ditolak (405) | Method Not Allowed | Berhasil |
| 12 | F | Status kendaraan di `t_mobil` tidak berubah | Tidak berubah | Berhasil |
| 13 | G | Dokumen transaksi tampil lengkap (transaksi, penyewa, kendaraan, riwayat DP dan pelunasan, ringkasan) | Seluruh bagian tampil | Berhasil |
| 14 | G | Cetak / Simpan sebagai PDF: tombol tidak ikut tercetak, tata letak tidak terpotong | Pratinjau cetak dan PDF sesuai | Berhasil |
| 15 | D-G | Fitur P9 (DP, unggah bukti, verifikasi admin, checkout) tetap berjalan | Tetap berfungsi | Berhasil |

## 11. Riwayat Commit
| Tahap | Pesan Commit |
|---|---|
| A | Menyiapkan artefak P9 untuk baseline pengembangan P10 |
| B | Menambahkan status lunas pada transaksi P10 |
| C | Mengimplementasikan model pelunasan P10 |
| D | Menambahkan controller pelunasan dan dokumen transaksi P10 |
| E | Menambahkan route dan menu pelunasan P10 |
| F | Menambahkan view daftar dan detail pelunasan P10 |
| G | Membuat View dokumen transaksi P10 |
