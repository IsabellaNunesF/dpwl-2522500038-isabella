### Penerapan Post-Redirect-Get (PRG)

Pada tahap ini dilakukan penelusuran dan pengujian pola Post-Redirect-Get (PRG) pada proses autentikasi.
Penerapan PRG:
- Login berhasil menggunakan POST untuk proses autentikasi, kemudian redirect ke /admin dan dilanjutkan dengan GET.
- Logout menggunakan POST untuk mengakhiri session, kemudian redirect ke /auth/login dan dilanjutkan dengan GET.
Login gagal tidak menggunakan redirect karena Controller mengembalikan View login beserta pesan kesalahan pada request tersebut.
Pengujian aktual dilakukan terhadap:

| No. | Skenario Pengujian | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Login dengan kredensial valid | Berhasil autentikasi via POST dan diarahkan redirect ke halaman admin (GET) | Sesuai |
| 2 | Login dengan kredensial tidak valid | Menampilkan kembali halaman login beserta pesan error tanpa melakukan redirect | Sesuai |
| 3 | Logout melalui halaman administrasi | Berhasil mengakhiri session via POST dan di-redirect kembali ke halaman login (GET) | Sesuai |
| 4 | Akses halaman admin setelah logout | Akses ditolak dan diarahkan ke halaman login | Sesuai |

### Perbaikan
Tidak ditemukan masalah pada saat pengujian M.3.