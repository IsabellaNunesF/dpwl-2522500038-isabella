<?php
session_start();
// ==========================================
// GUARD: Wajib login
// ==========================================
if (empty($_SESSION['login'])) {
  header('Location: ' . BASE_URL . 'penyewa/controller/tlogin_controller.php');
  exit;
}

require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/riwayat_model.php';

$model    = new RiwayatModel($conn);
$user_plg = $_SESSION['user_plg'];

// Filter status dari URL
$filter_status = $_GET['status'] ?? 'semua';

// Ambil riwayat yang sudah di-group per kode_sewa
$riwayat = $model->getRiwayatSewa($user_plg, $filter_status);

// Statistik
$totalSewa        = $model->getTotalSewa($user_plg);
$totalPengeluaran = $model->getTotalPengeluaran($user_plg);

// Helper: label status sewa
function labelStatusSewa($status)
{
  $map = [
    'diajukan'            => ['Diajukan', '#3498db', 'fa-hourglass-half'],
    'disetujui'           => ['Disetujui', '#2ecc71', 'fa-circle-check'],
    'aktif'               => ['Sedang Berjalan', '#27ae60', 'fa-car'],
    'selesai'             => ['Selesai', '#95a5a6', 'fa-flag-checkered'],
    'batal'               => ['Dibatalkan', '#e74c3c', 'fa-ban'],
    'ditolak'             => ['Ditolak', '#c0392b', 'fa-times-circle'],
  ];
  return $map[$status] ?? ['Tidak Diketahui', '#95a5a6', 'fa-question'];
}

// ==========================================
// DATA UNTUK HEADER
// ==========================================
$judul_halaman = 'Riwayat Sewa';
$isLoggedIn    = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userLogin     = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'] ?? '',
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
] : null;
$notifikasi    = $_SESSION['notifikasi'] ?? '';
unset($_SESSION['notifikasi']);

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/riwayat_view.php';
