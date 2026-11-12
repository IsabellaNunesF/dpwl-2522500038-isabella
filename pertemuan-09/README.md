# README P9 — Pembayaran Awal dan Verifikasi Pembayaran
## Tahap A: Pemeriksaan baseline P7/P8
- Pengujian: katalog, keranjang, checkout, manajemen penyewa.
- Hasil aktual: Hasil Aktual: Seluruh fitur P7 dan P8 berfungsi dengan baik dan siap digunakan sebagai baseline pengembangan P9. Checkout menghasilkan transaksi diajukan dan DP 30% berstatus pending pada t_bayar.
- Status: Berhasil

## Tahap B: Model pembayaran DP penyewa
- Menambah getTransaksiPenyewa, getTransaksiDenganDp, simpanBuktiDp (transaksi DB + FOR UPDATE).
- Hasil: aplikasi tetap berjalan tanpa galat. 
- Status: Berhasil

## Tahap C: Controller Penyewa, Route, dan Folder Bukti
- Langkah Pengujian: Menambahkan method saya(), pembayaran(), unggahBukti(), dan bukti(); mengubah redirect checkout(); menambahkan route P9 penyewa; membuat uploads/bukti/.htaccess.
- Hasil Aktual: Controller dan route berhasil dibuat. Halaman belum tampil karena view dibuat pada tahap D.