<?php
// admin/controller/dashboard_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/dashboard_model.php';

// Proteksi
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new DashboardModel();

// Ambil semua statistik
$stat_mobil = $model->getStatMobil();
$jumlah_penyewa = $model->getJumlahPenyewa();
$stat_sewa = $model->getStatSewa();
$pendapatan_sewa = $model->getPendapatanSewa();
$pendapatan_denda = $model->getPendapatanDenda();
$total_pendapatan = $model->getTotalPendapatan();
$pembayaran_pending = $model->getPembayaranPending();
$pengembalian_terlambat = $model->getPengembalianTerlambat();

// Siapkan data untuk view
$data_untuk_view = [
  'judul_halaman' => 'Dashboard Admin',
  'nama_admin' => $_SESSION['admin_username'],
  'stat_mobil' => $stat_mobil,
  'jumlah_penyewa' => $jumlah_penyewa,
  'stat_sewa' => $stat_sewa,
  'pendapatan_sewa' => $pendapatan_sewa,
  'pendapatan_denda' => $pendapatan_denda,
  'total_pendapatan' => $total_pendapatan,
  'pembayaran_pending' => $pembayaran_pending,
  'pengembalian_terlambat' => $pengembalian_terlambat
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/dashboard_view.php';
