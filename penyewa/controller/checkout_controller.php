<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/header_controller.php';
require_once __DIR__ . '/../model/checkout_model.php';

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

$model = new CheckoutModel($conn);
$userPlg = $_SESSION['user_plg'];

// ==========================================
// 4. HANDLE POST: Konfirmasi Checkout
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'konfirmasi') {
  // Ambil data checkout
  $checkoutData = $model->getCheckoutData($userPlg);

  if (!$checkoutData) {
    $_SESSION['notifikasi'] = 'Tidak ada pesanan yang bisa di-checkout.';
    header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
    exit;
  }

  $kodeSewa = $checkoutData['pesanan']['kode_sewa'];
  $nominalDP = $checkoutData['dp'];

  // PERBAIKAN: Buat record pembayaran DP DAN update status sewa
  $pembayaranBerhasil = $model->buatPembayaranDP($kodeSewa, $nominalDP);
  $statusBerhasil = $model->updateStatusSewa($kodeSewa, 'diajukan');

  if ($pembayaranBerhasil && $statusBerhasil) {
    $_SESSION['notifikasi'] = 'Checkout berhasil! Silakan lakukan pembayaran DP.';
    // Redirect ke halaman pembayaran
    header('Location: ' . BASE_URL . 'penyewa/controller/pembayaran_controller.php?kode_sewa=' . $kodeSewa);
    exit;
  } else {
    $_SESSION['notifikasi'] = 'Gagal memproses checkout. Silakan coba lagi.';
    header('Location: ' . BASE_URL . 'penyewa/controller/checkout_controller.php');
    exit;
  }
}

// ==========================================
// 5. AMBIL DATA CHECKOUT
// ==========================================
$checkoutData = $model->getCheckoutData($userPlg);

if (!$checkoutData) {
  $_SESSION['notifikasi'] = 'Keranjang Anda kosong. Silakan pilih mobil terlebih dahulu.';
  header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
  exit;
}

// ==========================================
// 6. GABUNGKAN DATA
// ==========================================
$data_untuk_view = array_merge($headerData, [
  'judul_halaman' => 'Konfirmasi Checkout',
  'checkoutData' => $checkoutData,
  'notifikasi' => $notifikasi,
]);

extract($data_untuk_view);
require_once __DIR__ . '/../view/checkout_view.php';
