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
