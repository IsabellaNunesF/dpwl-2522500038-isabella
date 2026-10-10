### Integrasi Route P3

Pada tahap ini dilakukan integrasi route untuk komponen autentikasi dan halaman administrasi.
Route yang ditambahkan:
- /login -> route login -> Controller Auth, method login
- /auth/login -> route auth/login -> Controller Auth, method login
- /auth/logout -> route auth/logout -> Controller Auth, method logout
- /admin -> route admin -> Controller Admin, method index
index.php tetap digunakan sebagai satu-satunya front controller aplikasi.

Pengujian integrasi route dilakukan terhadap:
| No. | Skenario Pengujian | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Akses `index.php/login` | Diarahkan melalui route login ke Controller Auth, method login | Sesuai |
| 2 | Login menggunakan akun admin yang valid | Proses autentikasi berjalan dan diarahkan ke halaman admin | Sesuai |
| 3 | Akses `index.php/admin` setelah login | Diarahkan melalui route admin/index dan halaman admin ditampilkan | Sesuai |
| 4 | Logout melalui halaman administrasi | Session autentikasi admin diakhiri | Sesuai |
| 5 | Akses `index.php/admin` setelah logout | Akses ditolak dan diarahkan ke halaman login | Sesuai |