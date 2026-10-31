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


## F. Implementasi Keranjang dan Checkout Transaksi

### F.1. Membuat Halaman Isi Keranjang
Membuat halaman keranjang pada `application/views/transaksi/keranjang.php` untuk menampilkan detail kendaraan, periode sewa, harga, durasi, dan total biaya. Halaman juga dilengkapi fitur memperbarui periode, menghapus kendaraan, dan melanjutkan checkout.

**Hasil: Berhasil dan terverifikasi.**

### F.2. Menambahkan Method Keranjang pada Controller
Mengintegrasikan Model, Controller, dan Route untuk mengelola penambahan kendaraan, tampilan keranjang, pembaruan periode, penghapusan kendaraan, dan checkout.

Pengujian dilakukan pada penyewa yang memiliki maupun belum memiliki keranjang aktif. Jumlah kendaraan pada navbar juga telah diperiksa.

**Hasil: Berhasil dan terverifikasi.**

### F.3. Membuat Halaman Konfirmasi dan Menguji Checkout

#### F.3.1. Halaman Konfirmasi Transaksi
Membuat `application/views/transaksi/konfirmasi.php` untuk menampilkan ringkasan sewa, total biaya, DP 30%, dan sisa pembayaran.

**Hasil: Berhasil dan terverifikasi.**

#### F.3.2. Menguji Tampilan Konfirmasi Checkout
Memastikan seluruh rincian transaksi ditampilkan dengan benar serta tombol kembali ke keranjang berfungsi.

**Hasil: Berhasil dan terverifikasi.**

#### F.3.3. Menguji Proses Checkout
Memastikan dialog konfirmasi JavaScript muncul, transaksi berhasil diajukan, dan aplikasi mengarahkan penyewa ke halaman katalog dengan pesan keberhasilan.

Pemeriksaan database juga memastikan:
- Status transaksi berubah dari `keranjang` menjadi `diajukan`.
- Pembayaran DP 30% tercatat pada tabel `t_bayar` dengan status `pending`.
- Transaksi yang telah diajukan tidak dapat di-checkout kembali sebagai keranjang aktif.

**Hasil: Berhasil dan terverifikasi.**
