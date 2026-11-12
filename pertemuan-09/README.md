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

## 4. Tahap D: Halaman Transaksi Saya dan Pembayaran DP
- Langkah Pengujian:
Login sebagai penyewa, lakukan checkout, dan pastikan aplikasi mengarahkan ke halaman Pembayaran DP.
Unggah berkas PDF dan berkas lebih dari 5 MB, pastikan ditolak dengan pesan galat.
Unggah foto JPG yang valid, pastikan bukti tampil dan status menjadi "Menunggu verifikasi admin".
Periksa t_bayar.ft_byr dan berkas pada uploads/bukti/.
Buka menu Transaksi Saya dan periksa daftar transaksi.
- Hasil Aktual: Setelah checkout penyewa diarahkan ke halaman pembayaran; validasi berkas berjalan; bukti tersimpan dan tampil.
- Status: Berhasil

## 5. Tahap E: Model Verifikasi Pembayaran
- Langkah Pengujian: Menambahkan getPembayaranUntukBukti(), getDaftarPembayaran(), dan verifikasiPembayaran(), lalu memeriksa aplikasi dan penyajian bukti lewat transaksi/bukti.
- Hasil Aktual: Model berhasil ditambahkan. Verifikasi valid mengubah t_sewa menjadi disetujui, sedangkan verifikasi tolak membiarkan t_sewa tetap diajukan.
- Status: Berhasil

## 6. Tahap F: Verifikasi Pembayaran oleh Admin
- Langkah Pengujian:
Login sebagai admin, buka menu Verifikasi Pembayaran, dan uji filter Menunggu/Valid/Ditolak/Semua.
Pastikan tombol Valid nonaktif pada pembayaran transfer yang belum memiliki bukti.
Tolak satu pembayaran, lalu login sebagai penyewa dan pastikan status "Ditolak admin" muncul serta bukti dapat diunggah ulang (status kembali pending, berkas lama terhapus).
Validasi satu pembayaran, lalu periksa t_bayar (sttus_byr = valid, tgl_wkt_ver terisi) dan t_sewa (status_sewa = disetujui).
Ulangi aksi Valid/Tolak pada data yang sama dan pastikan ditolak.
- Hasil Aktual: Menu **Verifikasi Pembayaran** pada sidebar admin berhasil menampilkan daftar pembayaran beserta bukti transfer, dan filter Menunggu/Valid/Ditolak/Semua berfungsi. Tombol **Valid** nonaktif pada pembayaran transfer yang belum memiliki bukti. Pada pembayaran yang ditolak, penyewa melihat status "Ditolak admin" dan dapat mengunggah ulang bukti; status kembali menjadi `pending` dan berkas lama terhapus dari `uploads/bukti/`. Pada pembayaran yang divalidasi, `t_bayar.sttus_byr` berubah menjadi `valid`, `tgl_wkt_ver` terisi, dan `t_sewa.status_sewa` berubah dari `diajukan` menjadi `disetujui`. Upaya verifikasi ulang pada data yang sama ditolak sistem dengan pesan bahwa pembayaran sudah diverifikasi.
- Status: Berhasil