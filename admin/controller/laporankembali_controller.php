<?php
// admin/controller/laporankembali_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/laporankembali_model.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new LaporanKembali();

// Ambil filter dari GET
$filter = [
  'tgl_mulai'   => $_GET['tgl_mulai'] ?? '',
  'tgl_selesai' => $_GET['tgl_selesai'] ?? ''
];

// Panggil model untuk mendapatkan data
$laporan = $model->getLaporan($filter);

// Siapkan data untuk dikirim ke view
$data_untuk_view = [
  'judul_halaman'      => 'Laporan Pengembalian Mobil',
  'nama_admin'         => $_SESSION['admin_username'],
  'filter'             => $filter,
  'data_laporan'       => $laporan['data'],
  'rekap_mobil'        => $laporan['rekap_mobil'],
  'total_pengembalian' => $laporan['total_pengembalian'],
  'total_denda'        => $laporan['total_denda'],
  'total_terlambat'    => $laporan['total_terlambat']
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/laporankembali_view.php';
