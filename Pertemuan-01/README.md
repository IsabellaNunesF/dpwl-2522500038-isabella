1. kesinambungan PWD–DPW–DPWL;
Jawaban: 
##Pada matakuliah PWD, mahasiswa lebih mempelajari pembuatan web dengan HTML sebagai penyusun struktur halaman, lalu CSS untuk merancang dan mendesain tata letak serta tampilan pada web, kemudian mahasiswa juga mempelajari Javascript yang memberikan interaksi pada web.
##Pada matakuliah DPW, mahasiswa mulai mempelajari pengembangan web dengan bahasa pemrograman PHP serta penggunaan MySql sebagai database, pada matakuliah ini juga HTML, CSS serta Javascript masih digunakan, mahasiswa juga mempelajari cara mengolah data(CRUD).
##Pada matakuliah DPWL, mahasiswa mempelajari pembuatan web yang berbasis konsep MVC dan OOP yang akan digunakan untuk merapikan kode sesuai fungsi sehingga lebih mudah di maintenance. 

2. perbedaan PHP terstruktur dan MVC;
Jawaban: PHP terstruktur adalah cara coding dimana HTML, CSS serta database(SQL) dicampur dalam satu file sehingga membuat codingan menjadi panjang dan rentan error.
Sedangkan MVC adalah cara pemrograman modern yang memisahkan antara Model(yang berisi data) View(khusus tampilan) serta Controller(logika penghubung) sehingga struktur program lebih rapi dan tidak merusak bagian bagian lain saat ada bagian yang ingin diperbaiki.

3. fungsi Model, View, dan Controller;
Jawaban: 
##Model berfungsi untuk mengatur seluruh data dalam program/aplikasi
##View berfungsi menampilkan data dalam bentuk HTML/CSS kepada user
##Controller berfungsi sebagai logika penghubung. Disaat menerima request dari user, controller akan menjadi jembatan dalam memproses pengambilan data ke Model yang kemudian akan ditampilkan melalui View.

4. alur request–response MVC;
Jawaban: 
1) User melakukan request di browser
2) router mengarahkan request ke controller yang sesuai, controller membaca input dari user
3) Controller memanggil model untuk mengambil, menyimpan ataupun memproses data ke database
4) Model mengolah data tersebut pada database lalu dikembalikan hasilnya kepada Controller
5) Controller meneruskan data dari Model kepada View agar dapat ditampilkan
6) View mengolah data tersebut dan mengembalikannya kepada user sebagai response akhir

5. pemetaan satu atau beberapa bagian/fitur aplikasi DPW ke Model, Controller, dan View disertai alasan
Jawaban: Sistem informasi Rumah Sakit
#Model 
Komponen: File koneksi database, file database SQL, serta query untuk mengambil, menyimpan, menghapus serta mengedit data pasien, daftar obat dan resep.
Alasan: Berperan utama dalam mengelola data di database mysql.
##View: 
Komponen: Halaman Utama, formulir pendaftaan, tabel stok dan halaman tampil resep.
Alasan: Berfungsi untuk menampilan informasi secara visual kepada pelanggan/user serta menerima input dari formulir.
#Controller
Komponen: File pengolah seperti proses pencatatan resep baru, sistem login.
Alasan: Berfungsi sebagai pemroses logika. Controller menerima data dari Form, memeriksa kodenya lalu memerintahkan Model untuk memprosesnya ke Database sebelum mengarahkan user kembali ke halaman utama.

6. kesimpulan P1.
Jawaban: Pertemuan 1 memberikan pengetahuan baru mengenai perbedaan dari PHP terstruktur dengan MVC, serta fungsi dan penerapan MVC pada program yang dapat membuat program menjadi lebih rapi dan mudah dikembangkan dalam skala besar. Selain itu, pengerjaan berbasis Git dan Github membantu pencatatan riwayat pengembangan sistem maupun web secara teratur dan terstruktur.