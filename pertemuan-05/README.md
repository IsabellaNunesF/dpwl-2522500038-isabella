# Dokumentasi Pertemuan 05 (P5) - Tahap A

## Fitur Baru & Perubahan Kode
Pada Tahap A, telah dilakukan penambahan menu navigasi **Manajemen Kendaraan** untuk mengakses fitur pengelolaan data mobil pada aplikasi rental mobil.

### Berkas yang Terdampak:
1. **`application/views/templates/sidebar.php`**
   - Menambahkan elemen menu baru `Manajemen Kendaraan` di bawah menu `Manajemen Admin` menggunakan ikon car (`fa-car`) dan tautan `site_url('admin/mobil')`.
2. **`application/config/routes.php`**
   - Menambahkan konfigurasi route baru: `$route['admin/mobil'] = 'Mobil_controller/index';`
   - Melakukan penyesuaian penulisan nama Controller pada route sebelumnya agar konsisten dengan nama class/berkas.

---

## Hasil Pengujian
- **Tampilan Navigasi:** Menu *Manajemen Kendaraan* berhasil muncul dengan rapi di bawah menu *Manajemen Admin* pada sidebar AdminLTE.
- **Pengujian Navigasi:** Saat menu *Manajemen Kendaraan* diklik, peramban mengarah ke URL `http://localhost/dpwl-nim/index.php/admin/mobil`.
- **Status Akhir Tahap A:** Muncul pesan *"Controller tidak ditemukan"*. Hal ini merupakan **kondisi yang diharapkan** (bukan kesalahan) karena `Mobil_controller.php` baru akan dibuat pada tahap selanjutnya (Tahap C).

# Dokumentasi Pertemuan 05 (P5) - Tahap B

## Fitur Baru & Perubahan Kode
Pada Tahap B, telah dilakukan penambahan tiga konfigurasi *route* baru pada aplikasi untuk mendukung fungsionalitas CRUD (Tambah, Ubah, Hapus) data kendaraan.

### Berkas yang Terdampak:
1. **`application/config/routes.php`**
   - Menambahkan *route* tambah kendaraan: `$route['admin/mobil/tambah'] = 'Mobil_controller/tambah';`
   - Menambahkan *route* ubah kendaraan dengan parameter: `$route['admin/mobil/ubah/(:any)'] = 'Mobil_controller/ubah/$1';`
   - Menambahkan *route* hapus kendaraan dengan parameter: `$route['admin/mobil/hapus/(:any)'] = 'Mobil_controller/hapus/$1';`

---

## Hasil Pengujian
- **Pengujian Route:** Saat mengakses URL `admin/mobil/tambah`, `admin/mobil/ubah/BN1234XX`, maupun `admin/mobil/hapus/BN1234XX`, sistem memberikan respon pesan *"Controller tidak ditemukan."*.
- **Status Akhir Tahap B:** Hasil respon tersebut merupakan **kondisi yang diharapkan** (bukan kesalahan) karena `Mobil_controller.php` belum dibuat dan baru akan diimplementasikan pada Tahap C.
# Dokumentasi P5 - Tahap C (READ Data Kendaraan)

## Perubahan Berkas
- `application/models/Mobil_model.php`: Menambahkan method `getAll()` untuk mengambil data `t_mobil`.
- `application/controllers/Mobil_controller.php`: Membuat controller & proteksi auth untuk alur `admin/mobil`.
- `application/views/admin/mobil_view.php`: Membuat antarmuka tabel data kendaraan (AdminLTE).

## Hasil Pengujian
- URL `admin/mobil` berhasil dibuka tanpa error.
- Data dari `t_mobil` tampil rapi di tabel.
- Fitur P4 (login & admin) tetap berjalan normal.

# Dokumentasi P5 - Tahap D (INSERT Data Kendaraan)
## Perubahan Berkas
- `application/models/Mobil_model.php`: Menambahkan method `create()` dengan prepared statement untuk menyimpan data kendaraan baru.
- `application/controllers/Mobil_controller.php`: Menambahkan method `tambah()`, `uploadFoto()`, `validateMobilInput()`, dan `hapusFotoBaru()` dengan penerapan pola PRG.
- `application/views/admin/mobil_view.php`: Menambahkan formulir tambah kendaraan beserta fitur unggah foto.

## Hasil Pengujian
- Formulir tambah kendaraan tampil saat mengklik tombol **Tambah Kendaraan**.
- Data kendaraan dan unggahan foto berhasil tersimpan ke basis data `t_mobil` serta folder `uploads/mobil/`.
- Mekanisme PRG berjalan lancar menampilkan pesanan sukses via flash message.

# Dokumentasi P5 - Tahap E (UPDATE Data Kendaraan)
## Perubahan Berkas
- `application/models/Mobil_model.php`: Menambahkan method `findByNoPlat()` untuk mengambil data spesifik berdasarkan nomor plat dan `update()` untuk memperbarui data pada tabel `t_mobil`.
- `application/controllers/Mobil_controller.php`: Menambahkan method `ubah()` untuk menangani pembacaan data lama, validasi input perubahan, penggantian/penghapusan berkas foto lama jika diunggah foto baru, dan penanganan pola PRG.

## Hasil Pengujian
- Menekan tombol **Ubah** pada baris data tabel berhasil menampilkan formulir beserta isi data lama kendaraan yang dipilih (dengan nomor plat bersifat *readonly*).
- Memperbarui data kendaraan tanpa mengunggah foto baru berhasil tersimpan tanpa merusak/menghapus foto lama.
- Mengunggah foto baru saat proses ubah berhasil menggantikan file foto lama serta secara otomatis menghapus berkas fisik foto lama dari direktori `uploads/mobil/`.

# Dokumentasi P5 - Tahap F (DELETE Data Kendaraan)
## Perubahan Berkas
- `application/models/Mobil_model.php`: Menambahkan method `delete()` untuk menghapus baris data kendaraan berdasarkan nomor plat dengan prepared statement.
- `application/controllers/Mobil_controller.php`: Menambahkan method `hapus()` yang memvalidasi metode HTTP POST, menghapus record di basis data, dan menghapus seluruh berkas fisik foto kendaraan terkait dari direktori `uploads/mobil/`.

## Hasil Pengujian
- Menekan tombol **Hapus** pada tabel data memicu dialog konfirmasi JavaScript.
- Setelah dikonfirmasi, data kendaraan berhasil terhapus dari tabel database `t_mobil`.
- Seluruh foto kendaraan yang terhubung (`ft_depan`, `ft_blkg`, `ft_interior`) berhasil dihapus secara permanen dari folder `uploads/mobil/`.
- Halaman langsung diarahkan kembali ke daftar kendaraan (*PRG Redirect*) dengan menampilkan flash message sukses.