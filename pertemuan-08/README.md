# UTS DPWL - Manajemen Penyewa

## Identitas
- Nama: Isabella Nunes Fernandes
- NIM: 2522500038
- Mata Kuliah: Desain dan Pemrograman Web Lanjut

## Tahap 1 : Mempersiapkan Artefak P7
# Tahap 2 : Menu Manajemen Penyewa
### Implementasi
Menambahkan menu Manajemen Penyewa pada navigasi admin.
### Pengujian
- Menu Manajemen Penyewa tampil pada sidebar admin.
- Menu dapat diklik.
- Menu mengarahkan pengguna ke halaman Manajemen Penyewa.
- Menu hanya dapat diakses oleh admin.
### Hasil
Menu Manajemen Penyewa berhasil ditambahkan.
## Tahap 3: Model Data Penyewa
### Implementasi
Mengembangkan Penyewa_model dengan mengikuti pola Mobil_model
yang telah diterapkan pada aplikasi sebelumnya.
Model menggunakan koneksi global $db, MySQLi, dan prepared
statement untuk mengakses tabel t_penyewa.
### Pengujian
- Model Penyewa berhasil dibuat.
- Method getAll() digunakan untuk membaca seluruh data penyewa.
- Method create() digunakan untuk menambahkan data penyewa.
- Method findByUserPlg() digunakan untuk mengambil data penyewa.
- Method update() digunakan untuk memperbarui data penyewa.
- Method delete() digunakan untuk menghapus data penyewa.
- Query menggunakan prepared statement.
### Hasil
Model Penyewa berhasil dikembangkan mengikuti pola
Mobil_model yang telah tersedia.