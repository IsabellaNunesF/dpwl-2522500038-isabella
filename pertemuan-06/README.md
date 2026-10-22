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
* Status: **Berhasil**[cite: 21]