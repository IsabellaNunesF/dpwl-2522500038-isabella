# README P10 — Pelunasan dan Dokumen Transaksi

Lanjutan aplikasi Sistem Informasi Penyewaan Kendaraan (PHP 8.4, homemade MVC, MySQLi, prepared statement) dari P9. Seluruh fitur P1–P9 dipertahankan.

## 1. Identitas Mahasiswa
- Nama  : Isabella Nunes Fernandes
- NIM   : 2522500038
- Kelas : SI3A
- Mata Kuliah : Desain dan Pemrograman Web Lanjutan (IS203), Gasal 2026/2027

## 2. Artefak dan Kondisi Awal
- Baseline  : hasil akhir P9 (`pertemuan-09`), disalin ke workspace `dpwl-nim\`.
- Lingkungan: Laragon (Apache + MySQL 8.0), PHP 8.4, URL `http://localhost/dpwl-nim/`.
- Basis data: `db_dpwl_isabella` (dump: `db_dpwl_isabella.sql`).
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
| D | Controller pelunasan dan dokumen transaksi | Kode selesai, uji fungsional menyusul |
| E | Route dan menu sidebar | Kode selesai, uji fungsional menyusul |
| F | View daftar dan detail pelunasan | Kode selesai, uji fungsional menyusul |
| G | View dokumen transaksi | Kode selesai, uji fungsional menyusul |

## 5. Daftar Berkas Ditambah / Diubah
| Tahap | Berkas | Keterangan |
|---|---|---|
| A | `pertemuan-10\README.md` | Dokumentasi baseline dan hasil pemeriksaan P9 |
| B | `db_dpwl_isabella.sql` | Dump basis data setelah penambahan ENUM `lunas` |
| C | `application\models\Pembayaran_model.php` | Menambahkan `getDaftarPelunasan()`, `getDetailPelunasan()`, `buatKodeBayarPelunasan()`, `simpanPelunasan()` |

| D | `application\controllers\Pembayaran_controller.php` | Menambahkan `pelunasanIndex()`, `pelunasanDetail()`, `prosesPelunasan()`, `dokumenTransaksi()` + helper `wajibMetode()`, `redirectDenganPesan()`, `simpanFileBukti()`, `hapusBuktiBaru()` |
| D | `application\models\Transaksi_model.php` | `checkout()` kini memegang named lock `dpwl_payment_code_sequence` (sama dengan lock pelunasan) |
| E | `application\config\routes.php` | 4 route P10 |
| E | `application\views\templates\sidebar.php` | Menu Pelunasan Transaksi |
| F | `application\views\admin\pelunasan.php` | Baru |
| F | `application\views\admin\pelunasan_detail.php` | Baru |
| G | `application\views\admin\dokumen_transaksi.php` | Baru |
| - | `system\core\router.php` | Menghapus baris kosong sebelum `<?php` |

Catatan: method P9 (`index()`, `detail()`, `verifikasi()`) tetap dipertahankan; fungsi DP/verifikasi P9 tetap berada di `Transaksi_model.php`.

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
| `simpanPelunasan($kodeSewa, $metode, $bukti)` | Mencatat pelunasan penuh oleh admin secara atomik (lihat alur di bawah). |

Alur `simpanPelunasan()` (satu transaksi basis data):
1. `GET_LOCK('dpwl_payment_code_sequence')` — kunci yang sama dengan checkout DP P9.
2. `begin_transaction()`.
3. `SELECT ... FOR UPDATE` pada transaksi sewa.
4. Validasi status (`disetujui`/`lunas`), total > 0, sisa kewajiban > 0, dan sisa ≤ 8.388.607 (batas `MEDIUMINT`).
5. Pastikan belum ada record `jns_byr = 'pelunasan'` untuk transaksi tersebut.
6. Buat kode pembayaran.
7. `INSERT` ke `t_bayar` (`jns_byr = 'pelunasan'`, `sttus_byr = 'valid'`, nominal = seluruh sisa kewajiban).
8. `UPDATE t_sewa SET status_sewa = 'lunas'` dan pastikan `affected_rows = 1`.
9. `COMMIT`; bila gagal `ROLLBACK`; named lock selalu dilepas pada `finally`.

Ketentuan: metode hanya `tunai`/`transfer`; transfer wajib bukti (`ft_byr`), tunai tanpa bukti; satu transaksi hanya satu record pelunasan; status kendaraan (`t_mobil`) tidak diubah.

### Hasil Pemeriksaan Model (C.5)
Isi dengan hasil aktual setelah pengujian:
| No | Pemeriksaan | Hasil Aktual | Status |
|---|---|---|---|
| 1 | `getDaftarPelunasan()` hanya memuat transaksi `disetujui` dengan sisa > 0 (dan `lunas`) | `<ISI>` | `<ISI>` |
| 2 | `getDetailPelunasan()` memuat total, pembayaran valid, sisa, riwayat, kendaraan | `<ISI>` | `<ISI>` |
| 3 | Format kode pembayaran `PAY/YYYYMMDD/NN` | `<ISI>` | `<ISI>` |
| 4 | Nominal pelunasan = sisa kewajiban | `<ISI>` | `<ISI>` |
| 5 | Metode selain tunai/transfer ditolak | `<ISI>` | `<ISI>` |
| 6 | Transaksi yang sudah memiliki record pelunasan tidak dapat dilunasi lagi | `<ISI>` | `<ISI>` |
| 7 | Pelunasan mengubah `status_sewa` menjadi `lunas` | `<ISI>` | `<ISI>` |
| 8 | Status kendaraan tidak berubah | `<ISI>` | `<ISI>` |
| 9 | Kegagalan proses menyebabkan ROLLBACK | `<ISI>` | `<ISI>` |