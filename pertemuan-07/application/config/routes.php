<?php
// DIPERBARUI - application/config/routes.php[cite: 16, 17]

$route = [];

// Route P2/P3 - dipertahankan[cite: 17]
$route['default_controller'] = 'home';
$route['info/(:any)']        = 'home/info/$1';
$route['login']              = 'auth/login';
$route['auth/login']         = 'auth/login';
$route['auth/logout']        = 'auth/logout';
$route['admin']              = 'admin/index';

// P4: BARU - semua proses CRUD admin dipindahkan di bawah /admin/madmin[cite: 17]
$route['admin/madmin']               = 'madmin_controller/index';
$route['admin/madmin/tambah']        = 'madmin_controller/tambah';
$route['admin/madmin/ubah/(:any)']   = 'madmin_controller/ubah/$1';
$route['admin/madmin/hapus/(:any)']  = 'madmin_controller/hapus/$1';

// Route P5
// dari href(menu), diproses ke Mobil_controller dengan mengerjakan method index() yang memanggil sadmin\mobil_view
$route['admin/mobil'] = 'Mobil_controller/index';
$route['admin/mobil/tambah'] = 'Mobil_controller/tambah';
$route['admin/mobil/ubah/(:any)'] = 'Mobil_controller/ubah/$1';
$route['admin/mobil/hapus/(:any)'] = 'Mobil_controller/hapus/$1';

// Route P6
$route['katalog'] = 'Katalog_controller/index';
$route['katalog/detail/(:any)'] = 'Katalog_controller/detail/$1';
$route['auth/register'] = 'Auth/register';
$route['auth/login-penyewa'] = 'Auth/loginPenyewa';
$route['auth/logout-penyewa'] = 'Auth/logoutPenyewa';
$route['penyewa/profil'] = 'Penyewa_controller/profil';
$route['default_controller'] = 'Katalog_controller';
// Route P7 - Transaksi dan Keranjang
$route['transaksi/tambah'] = 'Transaksi_controller/tambah';
$route['transaksi/keranjang'] = 'Transaksi_controller/keranjang';
$route['transaksi/perbarui-periode'] = 'Transaksi_controller/perbaruiPeriode';
$route['transaksi/hapus'] = 'Transaksi_controller/hapus';
$route['transaksi/hapus/(:any)'] = 'Transaksi_controller/hapus/$1';
$route['transaksi/checkout'] = 'Transaksi_controller/checkout';
