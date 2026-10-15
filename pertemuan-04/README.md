# Dokumentasi Pertemuan 04 (P4) - Autentikasi & Manajemen Admin

## Ringkasan Fitur
Pada Modul P4, telah diimplementasikan sistem Autentikasi (Login/Logout) serta Manajemen Data Admin berbasis arsitektur MVC dan MySQL.

---

## Berkas yang Terdampak & Dibuat
1. **`application/controllers/Auth.php`**
   - Menangani alur verifikasi login admin, pembuatan session login, dan proses logout.
2. **`application/controllers/Madmin_controller.php`**
   - Menangani alur CRUD untuk data admin (menampilkan daftar admin, menambah admin baru, dan mengubah status/password admin).
3. **`application/models/Admin_model.php`**
   - Mengelola query ke tabel admin di basis data, termasuk enkripsi/pencocokan kata sandi (`password_verify`).
4. **`application/helpers/auth_helper.php`**
   - Helper proteksi untuk memastikan hanya pengguna yang sudah login yang dapat mengakses halaman admin.
5. **`application/helpers/flash_helper.php`**
   - Helper pemberitahuan notifikasi pesan sukses atau gagal (flash message).
6. **`application/views/auth/index.php`**
   - Antarmuka formulir login admin berbasis AdminLTE.
7. **`application/views/admin/madmin_view.php`**
   - Antarmuka halaman kelola data admin.
8. **`application/config/routes.php`**
   - Konfigurasi rute login (`auth/login`), logout (`auth/logout`), dan manajemen admin (`admin/madmin`).

---

## Hasil Pengujian
- **Pengujian Login:** Berhasil masuk ke dalam dashboard admin jika username dan password sesuai dengan yang tersimpan di basis data.
- **Pengujian Proteksi Route:** Membuka URL admin secara langsung tanpa login akan di-redirect kembali ke halaman login secara otomatis.
- **Pengujian Manajemen Admin:** Tambah dan ubah data admin pada tabel berhasil tersimpan serta terenkripsi dengan aman.
- **Pengujian Logout:** Sesi login berhasil dihancurkan (*destroy session*) dan pengguna dikembalikan ke halaman login.