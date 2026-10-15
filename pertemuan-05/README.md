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