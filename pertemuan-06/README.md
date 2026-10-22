## Tahap A
### 1. Tahap Pengembangan
* Menyiapkan aset UI dari templat TemplateMo 559 Zay Shop untuk aplikasi rental kendaraan.
* Menyiapkan struktur direktori pengumpulan pada `pertemuan-06/assets/zay/`.
### 2. Berkas / Komponen yang Ditambahkan
* `assets/zay/css/` (bootstrap.min.css, templatemo.css, custom.css, fontawesome.min.css, slick.min.css, slick-theme.css)
* `assets/zay/img/` (gambar banner, kategori, icon, logo, dan aset tampilan)
* `assets/zay/js/` (jquery, bootstrap.bundle.min.js, templatemo.js, custom.js, slick.min.js)
* `assets/zay/webfonts/` (font ikon FontAwesome)
### 3. Langkah Pengujian
1. Memeriksa ketersediaan folder dan berkas aset Zay Shop di direktori `assets/zay/`.
2. Memastikan seluruh subfolder (`css`, `img`, `js`, `webfonts`) telah tersalin dengan lengkap tanpa menghapus aset P5 yang sudah ada.
3. Mengidentifikasi penyesuaian jalur relatif aset dari berkas HTML templat asli ke struktur direktori aplikasi MVC.
### 4. Hasil Aktual Pengujian
* Seluruh berkas aset CSS, JavaScript, Gambar, dan Webfonts berhasil dipindahkan ke folder `assets/zay/`.
* Struktur direktori workspace dan pengumpulan telah sesuai dengan ketentuan praktikum.
* Status: **Berhasil (Aset siap diintegrasikan ke View)**

---

## Tahap B: Mengimplementasikan Katalog Kendaraan Berbasis MVC

### 1. Tahap Pengembangan
* Menambahkan method `getKatalog()` pada `Mobil_model.php` untuk mengambil seluruh data kendaraan yang tersimpan di basis data secara terurut berdasarkan nama kendaraan
* Menambahkan route `$route['katalog']` pada `application/config/routes.php`.
* Membuat `Katalog_controller.php` untuk mengelola logika tampilan katalog kendaraan
* Membagi komponen antarmuka katalog ke dalam berkas View terpisah pada `application/views/katalog/`: `header.php`, `navbar.php`, `footer.php`, dan `index.php`

### 2. Berkas / Komponen yang Ditambahkan & Diubah
* **Model:** `application/models/Mobil_model.php` (menambahkan method `getKatalog()`)
* **Controller:** `application/controllers/Katalog_controller.php`
* **Config:** `application/config/routes.php`
* **View:**
  * `application/views/katalog/header.php`
  * `application/views/katalog/navbar.php`
  * `application/views/katalog/footer.php`
  * `application/views/katalog/index.php`

### 3. Langkah Pengujian
1. Membuka URL `http://localhost/dpwl-2522500038/index.php/katalog` di peramban web.
2. Memeriksa pemuatan aset CSS, JavaScript, gambar banner, dan font ikon agar tampil dengan benar tanpa ada *broken link*.
3. Memeriksa kueri data kendaraan yang diambil dari basis data dan ditampilkan pada kartu kendaraan (nama, jenis, tahun, transmisi, kapasitas, harga sewa, dan badge status unit).
4. Memeriksa tampilan kondisi jika foto kendaraan belum diunggah atau jika daftar data kendaraan kosong.

### 4. Hasil Aktual Pengujian
* Halaman katalog kendaraan berhasil diakses melalui route `/katalog`.
* Seluruh data kendaraan dari tabel `t_mobil` berhasil dipanggil dan ditampilkan dalam bentuk kartu katalog secara dinamis namun untuk beberapa foto katalog tidak muncul karena belum di upload dalam sistem admin.
* Komponen header, navigasi, carousel hero, kategori, dan footer terintegrasi dengan baik menggunakan aset Zay Shop.
* Status: **Berhasil**

---

## Tahap C: Mengimplementasikan Detail Kendaraan Berbasis MVC

### 1. Tahap Pengembangan
* Menambahkan route khusus detail kendaraan dengan parameter nomor plat pada `application/config/routes.php`.
* Menambahkan method `detail()` pada `Katalog_controller.php` untuk menangani pencarian data kendaraan berdasarkan nomor plat serta penanganan kondisi 404 jika data tidak ditemukan.
* Membuat tampilan halaman detail kendaraan pada `application/views/katalog/detail.php` dengan galeri foto interaktif, rincian spesifikasi unit, status ketersediaan, dan deskripsi kendaraan.

### 2. Berkas / Komponen yang Ditambahkan & Diubah
* **Config:** `application/config/routes.php`
* **Controller:** `application/controllers/Katalog_controller.php` (menambahkan method `detail()`)
* **View:** `application/views/katalog/detail.php`

### 3. Langkah Pengujian
1. Membuka halaman katalog, lalu mengklik salah satu gambar atau nama kendaraan untuk menuju halaman detail (misal: `http://localhost/dpwl-2522500038/index.php/katalog/detail/B%201173%20HFF`).
2. Memeriksa kecocokan data spesifikasi kendaraan (nama, jenis, tahun, transmisi, kapasitas, harga, status, dan deskripsi).
3. Menguji fitur interaktif galeri foto kendaraan (mengklik thumbnail foto depan, belakang, dan interior untuk mengganti tampilan foto utama).
4. Menguji penanganan galat dengan menguji URL yang menyertakan nomor plat yang tidak terdaftar di basis data.

### 4. Hasil Aktual Pengujian
* Halaman detail kendaraan berhasil menampilkan data spesifikasi secara dinamis sesuai plat nomor yang dipilih.
* Skrip JavaScript galeri gambar thumbnail berfungsi dengan lancar untuk berpindah foto utama.
* Penanganan galat HTTP 404 berjalan dengan baik jika nomor plat tidak ditemukan.
* Status: **Berhasil**

---

## Tahap D: Mengimplementasikan Registrasi Akun Penyewa

### 1. Tahap Pengembangan
* Menambahkan route khusus registrasi akun penyewa `$route['auth/register']` pada `application/config/routes.php`.
* Membuat `Penyewa_model.php` untuk menangani pengecekan ketersediaan username dan penyimpanan data akun penyewa baru ke tabel `t_penyewa`.
* Menambahkan method `register()` pada `application/controllers/Auth.php` untuk memproses validasi input (username, format NIK 16 digit, nomor HP, nama, alamat, serta kecocokan konfirmasi password) dan pengamanan password menggunakan `password_hash()`.
* Membuat tampilan formulir registrasi pada `application/views/auth/register.php` yang dilengkapi pesan notifikasi error dan sukses.

### 2. Berkas / Komponen yang Ditambahkan & Diubah
* **Config:** `application/config/routes.php`
* **Model:** `application/models/Penyewa_model.php`
* **Controller:** `application/controllers/Auth.php` (menambahkan method `register()`)
* **View:** `application/views/auth/register.php`

### 3. Langkah Pengujian
1. Membuka URL `http://localhost/dpwl-2522500038/index.php/auth/register`.
2. Menguji pengisian data valid seluruh kolom wajib formulir registrasi, lalu menekan tombol Daftar.
3. Menguji validasi penanganan input tidak valid, seperti memasukkan konfirmasi password yang berbeda dengan kolom password.
4. Menguji pendaftaran dengan username yang sudah terdaftar di basis data untuk mengecek penanganan akun duplikat.
5. Memeriksa ketersediaan data akun baru pada tabel `t_penyewa` di basis data setelah registrasi berhasil.

### 4. Hasil Aktual Pengujian
* Proses registrasi berhasil menyimpan data akun penyewa baru ke dalam tabel `t_penyewa` dengan password yang tersimpan secara aman.
* Sistem secara otomatis menolak dan menampilkan pesan peringatan jika konfirmasi password tidak cocok dengan password yang dimasukkan, sehingga akun tidak dapat dibuat atau digunakan untuk login.
* Penanganan kesalahan untuk input tidak valid dan username duplikat berjalan sesuai ketentuan.
* Status: **Berhasil**