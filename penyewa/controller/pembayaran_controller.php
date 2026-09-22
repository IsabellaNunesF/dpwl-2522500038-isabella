<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/header_controller.php';
require_once __DIR__ . '/../model/pembayaran_model.php';

// ==========================================
// 1. AMBIL NOTIFIKASI
// ==========================================
$notifikasi = $_SESSION['notifikasi'] ?? '';
unset($_SESSION['notifikasi']);

// ==========================================
// 2. INISIALISASI HEADER
// ==========================================
$headerCtrl = new HeaderController();
$headerData = $headerCtrl->getHeaderData();
$headerData['notifikasi'] = '';

// ==========================================
// 3. GUARD: WAJIB LOGIN
// ==========================================
if (!$headerData['isLoggedIn']) {
  $_SESSION['notifikasi'] = 'Silakan login terlebih dahulu.';
  header('Location: ' . BASE_URL . 'penyewa/controller/tlogin_controller.php');
  exit;
}

$model = new PembayaranModel($conn);
$userPlg = $_SESSION['user_plg'];

// ==========================================
// 4. HANDLE POST: Upload Bukti Pembayaran
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'upload_bukti') {
  $kodeBayar = $_POST['kode_bayar'] ?? '';

  if (empty($kodeBayar)) {
    $_SESSION['notifikasi'] = 'Data pembayaran tidak valid.';
    header('Location: ' . BASE_URL . 'penyewa/controller/pembayaran_controller.php');
    exit;
  }

  // Cek apakah file diupload
  if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['notifikasi'] = 'Silakan pilih file bukti pembayaran.';
    header('Location: ' . BASE_URL . 'penyewa/controller/pembayaran_controller.php?kode_bayar=' . $kodeBayar);
    exit;
  }

  // Upload file
  $result = $model->uploadBuktiBayar($kodeBayar, $_FILES['bukti_bayar']);

  if ($result['success']) {
    $_SESSION['notifikasi'] = 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.';
    header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
    exit;
  } else {
    $_SESSION['notifikasi'] = $result['message'];
    header('Location: ' . BASE_URL . 'penyewa/controller/pembayaran_controller.php?kode_bayar=' . $kodeBayar);
    exit;
  }
}

// ==========================================
// 5. AMBIL DATA PEMBAYARAN
// ==========================================
$kodeSewa = $_GET['kode_sewa'] ?? $_GET['kode_bayar'] ?? '';

if (empty($kodeSewa)) {
  $_SESSION['notifikasi'] = 'Data pembayaran tidak ditemukan.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// Ambil data pembayaran
$pembayaran = $model->getPembayaranByKodeSewa($kodeSewa);

if (!$pembayaran) {
  $_SESSION['notifikasi'] = 'Data pembayaran tidak ditemukan.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// Cek apakah pembayaran sudah diverifikasi
if ($pembayaran['sttus_byr'] === 'valid') {
  $_SESSION['notifikasi'] = 'Pembayaran sudah diverifikasi.';
  header('Location: ' . BASE_URL . 'penyewa/controller/riwayat_controller.php');
  exit;
}

// Ambil daftar mobil
$daftarMobil = $model->getDaftarMobil($kodeSewa);

// Hitung total estimasi
$totalEstimasi = $model->hitungTotalEstimasi($kodeSewa);
$sisaPembayaran = $totalEstimasi - $pembayaran['nominal'];

// ==========================================
// 6. GABUNGKAN DATA
// ==========================================
$data_untuk_view = array_merge($headerData, [
  'judul_halaman' => 'Pembayaran DP',
  'pembayaran' => $pembayaran,
  'daftarMobil' => $daftarMobil,
  'totalEstimasi' => $totalEstimasi,
  'sisaPembayaran' => $sisaPembayaran,
  'notifikasi' => $notifikasi,
]);

extract($data_untuk_view);
require_once __DIR__ . '/../view/pembayaran_view.php';
