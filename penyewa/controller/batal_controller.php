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
require_once __DIR__ . '/../model/batal_model.php';

$model     = new BatalModel($conn);
$user_plg  = $_SESSION['user_plg'];
$kode_sewa = $_REQUEST['kode_sewa'] ?? '';

if (empty($kode_sewa)) {
  $_SESSION['notifikasi'] = 'Kode sewa tidak valid.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// ==========================================
// PROSES PEMBATALAN (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $berhasil = $model->batalkanSewa($kode_sewa, $user_plg);

  if ($berhasil) {
    $_SESSION['notifikasi'] = 'Transaksi #' . htmlspecialchars($kode_sewa) . ' berhasil dibatalkan.';
  } else {
    $_SESSION['notifikasi'] = 'Gagal membatalkan transaksi. Pastikan status masih Diajukan.';
  }

  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// ==========================================
// TAMPILAN KONFIRMASI (GET)
// ==========================================
$detail = $model->getDetailSewa($kode_sewa, $user_plg);

if (empty($detail)) {
  $_SESSION['notifikasi'] = 'Transaksi tidak ditemukan atau bukan milik Anda.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// Validasi: Cek apakah status masih 'diajukan'
if ($detail[0]['status_sewa'] !== 'diajukan') {
  $_SESSION['notifikasi'] = 'Transaksi ini tidak dapat dibatalkan karena statusnya sudah berubah.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// Hitung total harga sewa
$total_harga = 0;
foreach ($detail as $d) {
  $total_harga += ($d['hrg_sewa'] * $d['n_hari']);
}

// ==========================================
// DATA UNTUK HEADER
// ==========================================
$judul_halaman = 'Batalkan Transaksi';
$isLoggedIn    = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userLogin     = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'] ?? '',
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
] : null;
$notifikasi    = ''; // Kosongkan notifikasi di halaman konfirmasi agar tidak ganda

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/batal_view.php';
