# README P10 — Pelunasan dan Dokumen Transaksi

Lanjutan aplikasi Sistem Informasi Penyewaan Kendaraan (PHP 8.4, homemade MVC, MySQLi, prepared statement) dari P9. Seluruh fitur P1–P9 dipertahankan.

## 1. Identitas Mahasiswa
- Nama   : Isabella Nunes Fernandes
- NIM    : 2522500038
- Kelas  : SI3A

## A. ## 2. Artefak dan Kondisi Awal
- Baseline  : hasil akhir P9 (`pertemuan-09`), disalin ke workspace `dpwl-nim\`.
- Lingkungan: Laragon (Apache + MySQL), PHP 8.4, URL `http://localhost/dpwl-nim/`.
- Backup basis data sebelum P10: `<nama-file>.sql`.

## 3. Hasil Pemeriksaan Fitur P9
| No | Skenario | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Login admin, dashboard tampil | Dashboard tampil | Berhasil |
| 2 | Login penyewa, katalog tampil | Katalog tampil | Berhasil |
| 3 | Transaksi Saya dan detail pembayaran DP | Daftar dan detail tampil | Berhasil |
| 4 | Penyewa mengunggah bukti DP | Pesan berhasil, status pending | Berhasil |
| 5 | Admin membuka daftar dan detail verifikasi, bukti tampil | Daftar, detail, dan bukti tampil | Berhasil |
| 6 | Admin menerima DP | Pembayaran valid, transaksi disetujui | Berhasil |
| 7 | Detail kendaraan pada transaksi | Tetap tampil | Berhasil |

Temuan baseline: tidak ada galat yang menghambat P10.

## 4. Tahapan Pengembangan P10
| Tahap | Isi | Status |
|---|---|---|
| A | Baseline P9 dan dokumentasi | Selesai |
| B | Status `lunas` pada `t_sewa` | Belum |
| C | Model pelunasan | Belum |
| D | Controller pelunasan dan dokumen | Belum |
| E | Route dan menu sidebar | Belum |
| F | View daftar dan detail pelunasan | Belum |
| G | View dokumen transaksi | Belum |

## 5. Daftar Berkas Ditambah / Diubah
(diisi bertahap)

## 6. Perubahan Struktur Basis Data
(diisi pada tahap B)

## 7. Kendala, Debugging, dan Perbaikan
(diisi bertahap)

## 8. Riwayat Commit
- Menyiapkan artefak P9 untuk baseline pengembangan P10

## 6. Perubahan Struktur Basis Data
### Tahap B — Status `lunas` pada `t_sewa.status_sewa`
Nilai ENUM `lunas` ditambahkan agar transaksi yang sudah dilunasi dapat dibedakan dari transaksi `disetujui`. Seluruh nilai lama dipertahankan. Status kendaraan pada `t_mobil` tidak diubah.

SQL yang dijalankan (satu kali):

    ALTER TABLE `t_sewa`
      CHANGE `status_sewa` `status_sewa`
      ENUM('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang','lunas')
      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'keranjang';

Pemeriksaan:
| No | Pemeriksaan | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Tabel `t_sewa` pada tab Structure dapat dibuka | Dapat dibuka | Berhasil |
| 2 | Kolom `status_sewa` diperiksa | Kolom ENUM ditemukan | Berhasil |
| 3 | Nilai `lunas` tersedia | Tersedia pada urutan ke-8 | Berhasil |
| 4 | Nilai ENUM lama tetap ada | Ketujuh nilai lama tetap ada | Berhasil |
| 5 | Hanya kolom `status_sewa` yang berubah | Kolom lain dan data lama tidak berubah | Berhasil |
Kendala: tidak ada.