# P11 – Pemenuhan atau Penyerahan Objek Transaksi dengan MVC

## A. Dokumentasi Persiapan Baseline P10

### A.1. Workspace dan Artefak P10
Artefak P10 telah disalin ke workspace `dpwl-2522500038\` sebagai dasar pengembangan P11. Pengembangan dilakukan secara kumulatif dengan mempertahankan struktur dan fitur aplikasi sebelumnya.

### A.2. Struktur Aplikasi dan Basis Data
Struktur aplikasi homemade MVC dan basis data MySQL telah diperiksa. Tabel yang digunakan dalam pengembangan P11 meliputi `t_sewa`, `t_pilih`, `t_mobil`, `t_bayar`, dan `t_penyewa`.

### A.3. Pemeriksaan Fitur P10
Fitur transaksi dan pembayaran P10 telah diperiksa melalui aplikasi. Hasil pemeriksaan:
- semua fitur pada P10 berhasil diuji.
- Tidak ada kendala yang ditemukan.
- Tidak ada perbaikan yang dilakukan.

### A.4. Dokumentasi dan Repositori Git
Dokumentasi P11 disiapkan pada `pertemuan-11\README.md`. Hasil pekerjaan disimpan dalam direktori `pertemuan-11` dan dikelola menggunakan Git.

## B. Analisis Persyaratan Serah Terima Kendaraan

P11 mengembangkan proses serah terima kendaraan setelah transaksi disetujui dan pembayaran DP diverifikasi valid. Sebelum penyerahan dikonfirmasi, admin harus memastikan data penyewa, dokumen KTP dan SIM A, serta detail kendaraan tersedia.

Proses serah terima menggunakan tabel `t_sewa`, `t_bayar`, `t_penyewa`, `t_pilih`, dan `t_mobil`. Sistem mencatat waktu aktual penyerahan pada `t_sewa.wkt_ambil` dan mengubah `t_sewa.status_sewa` menjadi `aktif`. Status kendaraan tetap `disewa` dan tidak dibuat pembayaran baru.

Implementasi direncanakan menggunakan pemisahan tanggung jawab MVC: Model mengelola pengambilan dan penyimpanan data, Controller menangani alur permintaan dan validasi proses, sedangkan View menyajikan informasi serta formulir serah terima.

Analisis ini menjadi dasar implementasi pada bagian C sampai dengan F.

## C. Implementasi Model Serah Terima

Membuat `application/models/SerahTerima_model.php` untuk mengelola pengambilan daftar dan detail transaksi serah terima serta pemrosesan penyerahan kendaraan. Model menggunakan MySQLi, prepared statement, kueri lintas tabel, validasi persyaratan transaksi, dan transaksi basis data.

Proses serah terima memeriksa status transaksi, validitas pembayaran DP, kelengkapan dokumen KTP dan SIM A, serta keberadaan kendaraan. Jika seluruh persyaratan terpenuhi, sistem mencatat waktu aktual pengambilan dan mengubah status transaksi menjadi `aktif`. Status kendaraan tetap `disewa`.

Pengujian fungsional Model: dilanjutkan setelah integrasi Controller, route, dan View.

## D. Implementasi Controller Daftar Serah Terima

### D.1. Menangani Permintaan Daftar Transaksi Serah Terima
Method `index()` dibuat untuk menangani permintaan daftar transaksi yang siap diserahterimakan. Controller mengambil data melalui `getDaftarSerahTerima()` pada Model dan meneruskannya kepada View. Akses dibatasi untuk admin dengan metode HTTP GET.

### D.2. Menangani Permintaan Detail Transaksi Serah Terima
Method `detail()` dibuat untuk menangani permintaan detail transaksi berdasarkan kode sewa. Data transaksi, penyewa, pembayaran, dan kendaraan diambil melalui `getDetailSerahTerima()` pada Model.

### D.3. Menangani Proses Konfirmasi Serah Terima Kendaraan
Method `proses()` dibuat untuk menangani konfirmasi serah terima menggunakan metode POST. Controller memproses unggahan dokumen, mencatat waktu aktual pengambilan, memanggil Model, serta menangani hasil proses dan pesan kepada admin.

### D.4. Memvalidasi Status Transaksi, Pembayaran, dan Persyaratan Serah Terima
Controller memanggil Model untuk memproses validasi status transaksi, pembayaran DP, dokumen KTP dan SIM A, serta keberadaan kendaraan. Jika persyaratan tidak terpenuhi, proses dibatalkan dan pesan kegagalan disampaikan kepada admin.

### D.5. Menerapkan Validasi, Sanitasi, dan Pengendalian Akses
Controller menerapkan pembatasan akses admin, pemeriksaan metode HTTP, serta validasi unggahan dokumen KTP dan SIM A. Dokumen yang diterima berupa JPEG, PNG, atau WEBP dengan ukuran maksimal 5 MB.

Controller menyediakan method `hapusSatuFile()` untuk membatasi penghapusan berkas hanya pada dokumen KTP dan SIM A yang dibuat oleh mekanisme unggah P11. Method `hapusFileBaru()` digunakan untuk membersihkan dokumen baru apabila proses serah terima gagal, sedangkan `hapusFileLama()` digunakan untuk menghapus dokumen lama setelah dokumen penggantinya berhasil disimpan.

Mekanisme ini membantu menjaga konsistensi berkas dokumen dan mencegah penghapusan berkas di luar cakupan unggahan P11.

### D.6. Menerapkan Pola Post-Redirect-Get (PRG)
Controller menerapkan pola Post-Redirect-Get (PRG) dengan menyimpan pesan sementara pada session dan mengarahkan admin ke halaman tujuan. Pesan ditampilkan satu kali setelah pengalihan.

### D.7. Mencegah Pemrosesan Ulang Serah Terima
Model menerapkan penguncian transaksi dan pemeriksaan status sebelum memproses serah terima. Transaksi yang sudah berubah menjadi `aktif` tidak dapat diproses kembali.

### D.8 Implementasi Controller Serah Terima
Controller `SerahTerima_controller.php` dibuat untuk menangani permintaan daftar transaksi, detail transaksi, dan konfirmasi serah terima kendaraan.

Controller diintegrasikan dengan `SerahTerima_model` serta menerapkan pengendalian akses admin, pembatasan metode HTTP, validasi unggahan dokumen, dan pola Post-Redirect-Get (PRG).

Pemeriksaan implementasi dilakukan dengan memastikan setiap method Controller menggunakan method Model yang sesuai. Pengujian fungsional melalui browser dilaksanakan setelah integrasi Route, View, dan Controller selesai.

## E. Integrasi Route dan Menu Serah Terima

### E.1. Menambahkan Route Daftar Serah Terima
Route `admin/serah-terima` ditambahkan untuk menghubungkan URL daftar serah terima dengan method `index()` pada `SerahTerima_controller`.

### E.2. Menambahkan Route Detail Transaksi
Route `admin/serah-terima/detail/(:any)` digunakan untuk mengakses detail transaksi berdasarkan kode sewa. Parameter kode transaksi diteruskan kepada method `detail()` pada `SerahTerima_controller`.

### E.3. Menambahkan Route Proses Serah Terima
Route `admin/serah-terima/proses/(:any)` digunakan untuk menghubungkan pengiriman formulir serah terima dengan method `proses()` pada `SerahTerima_controller`. Route ini digunakan untuk memproses konfirmasi serah terima menggunakan metode HTTP POST.

### E.4. Mengintegrasikan Menu Serah Terima pada Sidebar Admin
Menu Serah Terima ditambahkan pada `sidebar.php` setelah menu Pelunasan Transaksi. Menu menggunakan URL yang dibentuk melalui `site_url('admin/serah-terima')` sehingga admin dapat mengakses halaman daftar serah terima melalui navigasi aplikasi.

### E.5. Pemeriksaan Route dan Navigasi
Pemeriksaan dilakukan terhadap kesesuaian tiga route P11 dengan method pada `SerahTerima_controller` serta tautan menu Serah Terima pada sidebar admin. Route dan menu dari pertemuan sebelumnya dipastikan tetap dipertahankan.

Pengujian fungsional melalui peramban belum dilakukan pada tahap ini karena View serah terima akan diimplementasikan pada bagian F.

### E.6. Hasil Integrasi Route dan Menu
Integrasi P11 menambahkan tiga route untuk menangani daftar transaksi, detail transaksi, dan proses serah terima. Menu Serah Terima juga ditambahkan pada sidebar admin. Perubahan dilakukan dengan mempertahankan route dan menu yang telah digunakan pada pertemuan sebelumnya.

Pengujian fungsional melalui peramban dilanjutkan setelah implementasi View dan integrasi MVC selesai.

## F. Implementasi View Serah Terima

### F.1. Membuat View Daftar Serah Terima
Berkas `application/views/admin/serah_terima.php` dibuat untuk menampilkan daftar transaksi yang siap diserahterimakan. Informasi yang ditampilkan meliputi kode transaksi, identitas penyewa, nomor HP, tanggal dan waktu pengambilan, jumlah kendaraan, ketersediaan dokumen KTP dan SIM A, serta status transaksi.

View menerima data `$daftarSerahTerima` dan pesan `$flash` dari `SerahTerima_controller`. Setiap transaksi menyediakan tautan menuju halaman detail berdasarkan kode transaksi. Pengamanan output diterapkan menggunakan `htmlspecialchars()`.

### F.2. Membuat View Detail Serah Terima
Berkas `application/views/admin/serah_terima_detail.php` dibuat untuk menampilkan detail transaksi dan menyediakan formulir konfirmasi serah terima. Informasi yang ditampilkan meliputi identitas penyewa, periode sewa, pembayaran DP, seluruh kendaraan, serta dokumen KTP dan SIM A.

Formulir menggunakan metode POST dan `multipart/form-data` untuk mendukung unggah atau penggantian dokumen penyewa. Dokumen yang belum tersedia wajib diunggah, sedangkan penggantian dokumen yang sudah tersedia bersifat opsional.

View menampilkan kondisi transaksi dan formulir berdasarkan data yang diterima dari Controller. Validasi akhir persyaratan serah terima tetap dilakukan oleh Model. Pengamanan output diterapkan menggunakan `htmlspecialchars()`.

### F.3. Pemeriksaan Tampilan dan Integrasi Formulir
Pemeriksaan dilakukan terhadap kesesuaian data yang dikirim Controller dengan variabel yang digunakan oleh kedua View. Halaman daftar diperiksa untuk memastikan informasi transaksi dan tautan detail ditampilkan dengan benar. Halaman detail diperiksa untuk memastikan informasi penyewa, pembayaran DP, kendaraan, dokumen KTP dan SIM A, serta formulir konfirmasi serah terima ditampilkan sesuai kondisi data.

Formulir diperiksa untuk memastikan penggunaan metode POST dan `multipart/form-data`, serta ketentuan unggah dokumen wajib dan opsional.

### F.4. Menampilkan Seluruh Kendaraan Transaksi
View detail menampilkan seluruh kendaraan yang terkait dengan satu kode transaksi. Informasi kendaraan meliputi nomor polisi, nama kendaraan, tarif snapshot per hari, lama sewa, dan status kendaraan. Seluruh kendaraan dalam satu transaksi diserahterimakan secara bersamaan kepada penyewa yang tercatat pada transaksi.

View hanya menampilkan status kendaraan. Perubahan status kendaraan tidak dilakukan pada proses serah terima P11.

### F.5. Menampilkan dan Mengelola Dokumen Penyewa
View detail menampilkan ketersediaan dokumen KTP dan SIM A beserta tautan untuk melihat dokumen yang telah tersimpan pada direktori `uploads/penyewa/`. Formulir menyediakan input unggah dokumen. Dokumen yang belum tersedia wajib diunggah, sedangkan dokumen yang sudah tersedia dapat diganti secara opsional.

Petunjuk pada formulir mencantumkan format JPEG, PNG, dan WEBP dengan ukuran maksimal 5 MB. Validasi berkas dilakukan oleh Controller.

### F.6. Formulir Konfirmasi Serah Terima
Formulir konfirmasi dikirim menggunakan metode POST ke route proses serah terima berdasarkan kode transaksi. Formulir mendukung unggah dokumen melalui `multipart/form-data` dan menyediakan konfirmasi sebelum permintaan dikirimkan.

Admin harus memastikan seluruh kendaraan, kunci, dan dokumen pendukung telah diserahkan secara fisik kepada penyewa. Setelah proses berhasil, Model mencatat waktu aktual pengambilan pada `wkt_ambil` dan mengubah status transaksi menjadi `aktif`. Transaksi yang sudah aktif tidak dapat diproses kembali.

### Pengujian Integrasi View Serah Terima
Pengujian dilakukan melalui browser untuk memastikan halaman daftar dan detail serah terima kendaraan terintegrasi dengan Controller, Model, route, dan menu aplikasi.

Hasil pengujian:
- Menu Serah Terima: berhasil diakses melalui sidebar admin.
- Halaman daftar transaksi: menampilkan hanya transaksi berstatus `disetujui` dengan DP `valid`.
- Halaman detail transaksi: tombol Detail / Proses membuka detail transaksi yang sesuai.
- Informasi penyewa, pembayaran, dan kendaraan: ditampilkan dengan benar, termasuk seluruh kendaraan pada transaksi.
- Informasi dokumen KTP dan SIM A: status dokumen ditampilkan sesuai kondisi data; tautan dokumen berfungsi bila dokumen tersedia.
- Tampilan form serah terima: hanya tampil untuk transaksi yang memenuhi persyaratan; input dokumen wajib bila dokumen belum tersedia dan opsional bila sudah tersedia.
- Pencegahan pemrosesan ulang: transaksi berstatus `aktif` tidak tampil pada daftar dan tidak menampilkan formulir; percobaan proses ulang ditolak oleh Model.
- Pesan notifikasi: pesan keberhasilan dan kegagalan tampil satu kali setelah redirect.

Pengujian skenario valid, tidak valid, dan kasus batas:
- Serah terima valid (DP valid, KTP dan SIM A tersedia atau diunggah): berhasil, `status_sewa` menjadi `aktif`, `wkt_ambil` terisi waktu aktual, dan status kendaraan tetap `disewa`.
- DP belum valid: transaksi tidak tampil pada daftar dan formulir tidak tersedia pada halaman detail.
- Dokumen KTP atau SIM A belum lengkap: formulir mewajibkan unggah dokumen sebelum konfirmasi.
- Berkas tidak valid (bukan gambar atau lebih dari 5 MB): ditolak dengan pesan kesalahan dan data tidak berubah.
- Pemrosesan ulang (dua tab): permintaan kedua ditolak dengan pesan bahwa transaksi telah diproses.
- Akses tanpa login admin: dialihkan ke halaman login.
- Akses GET ke URL proses: ditolak dengan respons 405.
- Fitur P9 dan P10: tetap berfungsi.

Catatan atau kendala pengujian:
Tidak ada kendala berdasarkan hasil pengujian.

## G. Perbaikan Integrasi Pelunasan dan Serah Terima

### G.1. Masalah yang Ditemukan
Setelah serah terima dikonfirmasi pada halaman Serah Terima Kendaraan, transaksi yang bersangkutan tidak lagi tampil pada halaman Pelunasan Transaksi (P10), padahal transaksi tersebut masih dapat memiliki sisa kewajiban pembayaran. Selain itu, ditemukan kesalahan tampilan pada halaman detail serah terima: kode PHP pada blok pesan notifikasi tercetak sebagai teks.

### G.2. Analisis Penyebab
Proses serah terima mengubah `t_sewa.status_sewa` dari `disetujui` menjadi `aktif`. Pada P10, seluruh logika pelunasan hanya mengenali status `disetujui` dan `lunas`, sehingga transaksi berstatus `aktif` tidak lagi dianggap memenuhi syarat. Pembatasan ini terdapat pada empat titik:
- kueri `getDaftarPelunasan()` pada `Pembayaran_model.php` yang memfilter `status_sewa IN ('disetujui', 'lunas')`;
- pemeriksaan status pada `simpanPelunasan()` pada `Pembayaran_model.php`;
- kueri `UPDATE t_sewa SET status_sewa = 'lunas'` pada `simpanPelunasan()`;
- variabel `$statusMemenuhiSyarat` pada `views/admin/pelunasan_detail.php`.

Data transaksi tidak terhapus. Transaksi hanya tidak ditampilkan dan tidak dapat diproses karena status `aktif` tidak termasuk dalam kondisi yang diizinkan.

Pada `views/admin/serah_terima_detail.php`, baris `$jenisFlash = ...` pada blok pesan notifikasi tidak diawali tag `<?php`, sehingga dianggap sebagai teks HTML biasa.

### G.3. Perbaikan yang Dilakukan
**`application/models/Pembayaran_model.php`**
- `getDaftarPelunasan()` kini memfilter `status_sewa IN ('disetujui', 'aktif', 'lunas')`. Transaksi aktif yang masih memiliki sisa kewajiban tampil kembali pada daftar pelunasan.
- `simpanPelunasan()` kini menerima status `disetujui`, `aktif`, dan `lunas`.
- Pembaruan status menjadi `lunas` hanya dijalankan apabila status transaksi bukan `aktif`. Dengan demikian, transaksi yang sudah diserahterimakan tetap berstatus `aktif` setelah pelunasan dicatat, dan status serah terima tidak tertimpa. Pembayaran pelunasan tetap dicatat sebagai `valid` dalam transaksi basis data yang sama.

**`application/views/admin/pelunasan_detail.php`**
- `$statusMemenuhiSyarat` kini menerima status `disetujui`, `aktif`, dan `lunas`, sehingga tombol pencatatan pelunasan tampil untuk transaksi aktif yang masih memiliki sisa kewajiban.

**`application/views/admin/serah_terima_detail.php`**
- Penentuan `$jenisFlash` dibungkus dengan tag `<?php ... ?>` sehingga pesan notifikasi tampil sebagai alert dengan warna sesuai jenisnya.

Tidak ada perubahan pada struktur basis data, route, maupun menu.

### G.4. Pengujian Perbaikan
Hasil pengujian:
- Transaksi `disetujui` dengan DP `valid` diserahterimakan: `status_sewa` menjadi `aktif`.
- Halaman Pelunasan Transaksi: transaksi `aktif` dengan sisa kewajiban tampil kembali pada daftar.
- Halaman detail pelunasan transaksi `aktif`: informasi transaksi dan tombol Catat Pelunasan tampil.
- Pencatatan pelunasan transaksi `aktif`: berhasil, record pembayaran `pelunasan` berstatus `valid` terbentuk, dan `status_sewa` tetap `aktif`.
- Pencatatan pelunasan transaksi `disetujui`: tetap berhasil dan `status_sewa` menjadi `lunas` seperti pada P10.
- Pelunasan ganda pada transaksi yang sama: tetap ditolak.
- Pesan notifikasi pada detail serah terima: tampil sebagai alert tanpa kode PHP yang tercetak.
- Fitur P9, P10, dan serah terima P11: tetap berfungsi.

Catatan dan batasan:
- Transaksi `aktif` yang sisa kewajibannya sudah nol dan berstatus bukan `lunas` tidak ditampilkan pada daftar pelunasan, sesuai filter sisa kewajiban pada P10.
- Serah terima P11 tetap hanya menerima transaksi berstatus `disetujui`. Transaksi yang sudah berstatus `lunas` sebelum serah terima tidak tampil pada daftar serah terima.