## Ringkasan Hasil Pengujian Aktual

### 1. Tahap A: Pemeriksaan Kesiapan Artefak P6
* **Langkah Pengujian:** Memeriksa kesiapan basis data, struktur direktori workspace, serta fitur-fitur dari P6 (autentikasi, katalog, detail kendaraan, dan profil penyewa).
* **Hasil Aktual:** Seluruh fitur P6 berfungsi dengan baik dan siap digunakan sebagai baseline pengembangan P7.
* **Status:** Berhasil

---

### 2. Tahap B: Fondasi Model, Controller Transaksi, dan Route
* **Langkah Pengujian:** Menguji pembentukan nomor transaksi harian dengan format `INV/YYYYMMDD/NN`, mekanisme penguncian `GET_LOCK()`, pengontrolan metode HTTP (POST/GET), dan ketersediaan route `transaksi/tambah`.
* **Hasil Aktual:** Kelas `Transaksi_model` dan `Transaksi_controller` berhasil dibuat. Route `/transaksi/tambah` terhubung dengan benar.
* **Status:** Berhasil

---

### 3. Tahap C: Integrasi Tombol Keranjang pada Halaman Detail & Katalog
* **Langkah Pengujian:** 
  1. Mengakses halaman detail dan katalog sebelum login (memastikan tombol *Tambah ke Keranjang* nonaktif atau menampilkan opsi login/registrasi).
  2. Mengakses halaman detail dan katalog setelah login sebagai penyewa (memastikan tombol *Tambah ke Keranjang* aktif untuk kendaraan berstatus `tersedia`).
  3. Memeriksa kendaraan dengan status `tidak tersedia` atau `sedang disewa`.
* **Hasil Aktual:** Kondisi tombol merespons status login penyewa dan ketersediaan kendaraan secara presisi. Kendaraan yang tidak tersedia secara otomatis menampilkan tombol nonaktif *Tidak Dapat Disewa*.
* **Status:** Berhasil
### 4. Tahap D: Pesan Notifikasi, Tautan, dan Jumlah Keranjang pada Navbar
* **Langkah Pengujian:** 
  1. Memeriksa keberadaan ikon dan badge jumlah keranjang pada `navbar.php`.
  2. Menguji navigasi dari berbagai halaman (Katalog, Detail, dan Profil) untuk memastikan data jumlah keranjang terkirim dengan konsisten.
  3. Memeriksa penanganan pesan notifikasi (*flash message*) saat penambahan barang atau error.
* **Hasil Aktual:** Badge jumlah keranjang pada header/navbar secara dinamis menampilkan total kendaraan dalam keranjang milik penyewa aktif. Pesan notifikasi sukses/error tampil dan dapat ditutup (*dismissible*).
* **Status:** Berhasil
### 5. Tahap E: Integrasi Proses Penambahan Kendaraan ke Keranjang
* **Langkah Pengujian:**
  1. Login sebagai penyewa, kemudian buka halaman katalog.
  2. Pilih kendaraan dengan status `tersedia` dan klik tombol **Tambah ke Keranjang**.
  3. Pastikan aplikasi kembali ke halaman katalog dan menampilkan notifikasi bahwa kendaraan berhasil ditambahkan.
  4. Periksa tabel `t_sewa` dan `t_pilih` melalui phpMyAdmin untuk memastikan data transaksi dan kendaraan tersimpan dengan `kode_sewa` yang sama.
  5. Tambahkan kendaraan lain dan pastikan kendaraan tersebut masuk ke keranjang aktif yang sama.
  6. Coba tambahkan kendaraan yang sama untuk kedua kalinya dan pastikan sistem menolak penambahan duplikat.
* **Hasil Aktual:**
  Proses penambahan kendaraan ke keranjang berhasil dijalankan. Data transaksi tersimpan pada tabel `t_sewa`, sedangkan data kendaraan yang dipilih tersimpan pada tabel `t_pilih` dengan `kode_sewa` yang sesuai. Penambahan kendaraan lain menggunakan keranjang aktif yang sama, dan sistem berhasil mencegah penambahan kendaraan yang sama secara duplikat. Hasil pemeriksaan melalui phpMyAdmin menunjukkan bahwa penyimpanan data telah sesuai dengan ketentuan.
* **Status:** Berhasil

## F. Mengimplementasikan Halaman Keranjang dan Checkout Transaksi

### F.1. Membuat Halaman Isi Keranjang

Membuat halaman keranjang pada:

`application/views/transaksi/keranjang.php`

Halaman keranjang digunakan untuk menampilkan informasi transaksi penyewaan kendaraan, meliputi:

- Kode transaksi dan status sewa.
- Tanggal mulai dan tanggal selesai sewa.
- Waktu pengambilan kendaraan.
- Daftar kendaraan yang dipilih.
- Nomor polisi kendaraan.
- Harga sewa per hari.
- Durasi penyewaan.
- Subtotal dan total biaya sewa.

Halaman ini juga menyediakan beberapa fitur, yaitu:
- Memperbarui periode penyewaan.
- Menghapus kendaraan dari keranjang.
- Melanjutkan transaksi ke halaman konfirmasi checkout.

Seluruh data ditampilkan berdasarkan hasil dari Model dan di-escape menggunakan `htmlspecialchars()` sebelum dimasukkan ke HTML.

### F.2. Menambahkan Method Keranjang dan Lainnya pada Controller Transaksi

Melakukan integrasi Model, Controller, dan Route untuk mengelola keranjang transaksi.

Berkas yang digunakan:

- `application/controllers/Transaksi_controller.php`
- `application/models/Transaksi_model.php`
- `application/config/routes.php`

Method yang diintegrasikan pada Controller meliputi:

- `tambah()` untuk menambahkan kendaraan ke keranjang.
- `keranjang()` untuk menampilkan isi keranjang.
- `perbaruiPeriode()` untuk memperbarui periode penyewaan.
- `hapus()` untuk menghapus kendaraan dari keranjang.
- `checkout()` untuk menampilkan konfirmasi dan memproses pengajuan transaksi.

Route transaksi yang digunakan:

- `transaksi/tambah`
- `transaksi/keranjang`
- `transaksi/perbarui-periode`
- `transaksi/hapus`
- `transaksi/hapus/(:any)`
- `transaksi/checkout`

**Pengujian F.2:**

| No. | Skenario Pengujian | Hasil |
|---|---|---|
| 1 | Login sebagai penyewa yang memiliki keranjang aktif dan membuka halaman keranjang. | Belum diverifikasi |
| 2 | Login sebagai penyewa yang belum memiliki keranjang aktif dan membuka halaman keranjang. | Belum diverifikasi |
| 3 | Memastikan jumlah kendaraan pada ikon keranjang sesuai dengan isi keranjang. | Belum diverifikasi |

### F.3. Membuat Halaman Konfirmasi Transaksi dan Menguji Checkout

#### F.3.1. Membuat Halaman Konfirmasi Transaksi

Membuat halaman konfirmasi transaksi pada:

`application/views/transaksi/konfirmasi.php`

Halaman konfirmasi digunakan untuk menampilkan ringkasan penyewaan sebelum transaksi diajukan kepada admin.

Informasi yang ditampilkan meliputi:

- Kode transaksi.
- Tanggal mulai dan tanggal selesai.
- Waktu pengambilan.
- Daftar kendaraan dan nomor polisi.
- Harga sewa per hari.
- Durasi penyewaan.
- Subtotal dan total sewa.
- Uang muka atau DP sebesar 30%.
- Sisa pembayaran setelah DP.

Halaman menyediakan tombol **Kembali ke Keranjang** dan **Konfirmasi dan Ajukan Transaksi**.

Tombol konfirmasi menggunakan metode POST dan menampilkan dialog konfirmasi JavaScript:

`Ajukan transaksi ini kepada admin?`

#### F.3.2. Menguji Tampilan Konfirmasi Checkout

Skenario pengujian:

1. Menjalankan Laragon dan membuka aplikasi DPWL.
2. Login sebagai penyewa yang memiliki keranjang berisi minimal satu kendaraan.
3. Membuka halaman keranjang dan klik tombol Checkout / Lihat Konfirmasi.
4. Memastikan halaman Konfirmasi Transaksi menampilkan kode transaksi, periode sewa, waktu pengambilan, daftar kendaraan, harga per hari, durasi, subtotal, dan total sewa.
5. Memeriksa perhitungan DP sebesar 30% dari total sewa dan sisa pembayaran setelah DP.
6. Klik Kembali ke Keranjang dan memastikan aplikasi kembali ke keranjang dengan kode transaksi yang sama.
7. Memastikan halaman konfirmasi tidak dapat ditampilkan untuk transaksi milik penyewa lain atau transaksi yang tidak lagi berstatus keranjang.

**Hasil pengujian: Belum diverifikasi seluruhnya.**

#### F.3.3. Menguji Proses Checkout

Pengujian dilakukan menggunakan transaksi pengujian yang disiapkan untuk proses checkout.

Skenario pengujian:

1. Membuka halaman konfirmasi transaksi dan memeriksa kembali seluruh rincian sewa.
2. Klik Konfirmasi dan Ajukan Transaksi, kemudian menyetujui dialog konfirmasi.
3. Memastikan aplikasi memproses permintaan POST dan mengarahkan penyewa ke halaman katalog dengan pesan bahwa transaksi telah diajukan dan menunggu persetujuan admin.
4. Membuka phpMyAdmin dan memeriksa tabel `t_sewa`. Memastikan `status_sewa` berubah dari `keranjang` menjadi `diajukan`.
5. Memeriksa tabel `t_bayar`. Memastikan terdapat satu catatan pembayaran DP untuk kode transaksi tersebut dengan ketentuan:
   - `jns_byr` = `dp`
   - `mtode_byr` = `transfer`
   - `nominal` = DP sebesar 30% dari total sewa
   - `sttus_byr` = `pending`
6. Memastikan transaksi yang telah diajukan tidak dapat di-checkout kembali sebagai keranjang aktif.

**Hasil pengujian: Belum diverifikasi seluruhnya.**

### Kesimpulan Pengujian Bagian F

Implementasi bagian F mencakup halaman keranjang, pengelolaan periode penyewaan, penghapusan kendaraan, halaman konfirmasi transaksi, dan proses pengajuan checkout kepada admin.

Checkout merupakan proses pengajuan transaksi, bukan bukti bahwa pembayaran telah dilakukan atau transaksi telah disetujui admin. Pencatatan pembayaran DP dilakukan dengan status `pending`, sedangkan pengunggahan bukti pembayaran dan verifikasi pembayaran dibahas pada P9.