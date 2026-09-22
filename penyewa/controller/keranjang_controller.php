<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/header_controller.php';
require_once __DIR__ . '/../model/keranjang_model.php';

// ==========================================
// 1. AMBIL NOTIFIKASI SEBELUM HEADERCONTROLLER
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

$model   = new KeranjangModel($conn);
$userPlg = $_SESSION['user_plg'];

// ==========================================
// 4. HANDLE POST: Tambah Mobil ke Keranjang
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
  $no_plat = trim($_POST['no_plat'] ?? '');
  if (empty($no_plat)) {
    $_SESSION['notifikasi'] = 'Data mobil tidak valid.';
    header('Location: ' . BASE_URL . 'penyewa/controller/tmobil_controller.php');
    exit;
  }

  $mobil = $model->getDetailMobilSingle($no_plat);
  if (!$mobil) {
    $_SESSION['notifikasi'] = 'Mobil tidak tersedia.';
    header('Location: ' . BASE_URL . 'penyewa/controller/tmobil_controller.php');
    exit;
  }

  $pesanan = $model->getPesananDiajukan($userPlg);
  if (!$pesanan) {
    $kode_sewa   = $model->generateKodeSewa();
    $tgl_mulai   = date('Y-m-d');
    $tgl_selesai = date('Y-m-d', strtotime('+1 day'));
    $wkt_ambil   = date('H:i:s');
    $model->buatPesananBaru($kode_sewa, $tgl_mulai, $tgl_selesai, $wkt_ambil, $userPlg);
    $model->tambahMobilKeKeranjang($kode_sewa, $no_plat);
    $_SESSION['notifikasi'] = 'Keranjang dibuat dan mobil berhasil ditambahkan!';
  } else {
    $kode_sewa = $pesanan['kode_sewa'];
    $sudahAda = $model->cekMobilDiKeranjang($kode_sewa, $no_plat);
    if ($sudahAda) {
      $_SESSION['notifikasi'] = 'Mobil ini sudah ada di keranjang Anda.';
    } else {
      $model->tambahMobilKeKeranjang($kode_sewa, $no_plat);
      $_SESSION['notifikasi'] = 'Mobil berhasil ditambahkan ke keranjang!';
    }
  }
  header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
  exit;
}

// ==========================================
// 5. HANDLE POST: Update Periode Keranjang
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update') {
  $tgl_mulai   = $_POST['tgl_mulai'] ?? '';
  $tgl_selesai = $_POST['tgl_selesai'] ?? '';
  $wkt_ambil   = $_POST['wkt_ambil'] ?? '';

  if (empty($tgl_mulai) || empty($tgl_selesai) || empty($wkt_ambil)) {
    $_SESSION['notifikasi'] = 'Semua field periode sewa harus diisi.';
    header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
    exit;
  }

  // Validasi tanggal
  $dateMulai   = new DateTime($tgl_mulai);
  $dateSelesai = new DateTime($tgl_selesai);
  $dateNow     = new DateTime(date('Y-m-d'));

  if ($dateMulai < $dateNow) {
    $_SESSION['notifikasi'] = 'Tanggal mulai tidak boleh di masa lalu.';
    header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
    exit;
  }

  if ($dateSelesai <= $dateMulai) {
    $_SESSION['notifikasi'] = 'Tanggal selesai harus lebih besar dari tanggal mulai.';
    header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
    exit;
  }

  // Hitung n_hari
  $diff = $dateMulai->diff($dateSelesai);
  $n_hari = $diff->days;
  if ($n_hari < 1) $n_hari = 1;

  // Update ke database
  $pesanan = $model->getPesananDiajukan($userPlg);
  if ($pesanan) {
    $model->updatePeriodeSewa($pesanan['kode_sewa'], $tgl_mulai, $tgl_selesai, $wkt_ambil, $n_hari);
    $_SESSION['notifikasi'] = "Periode sewa berhasil diperbarui! Durasi: {$n_hari} hari.";
  }

  header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
  exit;
}

// ==========================================
// 6. HANDLE GET: Hapus dari Keranjang
// ==========================================
if (isset($_GET['hapus'])) {
  $no_plat = $_GET['hapus'];
  $pesanan = $model->getPesananDiajukan($userPlg);
  if ($pesanan) {
    $model->hapusMobilDariKeranjang($pesanan['kode_sewa'], $no_plat);
    $_SESSION['notifikasi'] = 'Mobil berhasil dihapus dari keranjang.';
  }
  header('Location: ' . BASE_URL . 'penyewa/controller/keranjang_controller.php');
  exit;
}

// ==========================================
// 7. AMBIL DATA KERANJANG DARI DATABASE
// ==========================================
$pesananDiajukan = $model->getPesananDiajukan($userPlg);

// Data default untuk periode
$periodeData = [
  'tgl_mulai'   => date('Y-m-d'),
  'tgl_selesai' => date('Y-m-d', strtotime('+1 day')),
  'wkt_ambil'   => '08:00',
  'n_hari'      => 1
];

if ($pesananDiajukan) {
  $kodeSewa     = $pesananDiajukan['kode_sewa'];
  $data_mobil   = $model->getDetailMobilKeranjang($kodeSewa);
  $totalPerHari = array_sum(array_column($data_mobil, 'hrg_hari'));

  // Ambil periode dari database
  $periodeData = [
    'tgl_mulai'   => $pesananDiajukan['tgl_mulai'] ?? $periodeData['tgl_mulai'],
    'tgl_selesai' => $pesananDiajukan['tgl_selesai'] ?? $periodeData['tgl_selesai'],
    'wkt_ambil'   => substr($pesananDiajukan['wkt_ambil'] ?? '08:00:00', 0, 5),
    'n_hari'      => 1
  ];

  // Hitung n_hari dari database
  if (!empty($periodeData['tgl_mulai']) && !empty($periodeData['tgl_selesai'])) {
    $d1 = new DateTime($periodeData['tgl_mulai']);
    $d2 = new DateTime($periodeData['tgl_selesai']);
    $diff = $d1->diff($d2);
    $periodeData['n_hari'] = max(1, $diff->days);
  }
} else {
  $data_mobil   = [];
  $totalPerHari = 0;
}

// ==========================================
// 8. GABUNGKAN DATA
// ==========================================
$data_untuk_view = array_merge($headerData, [
  'judul_halaman' => 'Keranjang Sewa',
  'data_mobil'    => $data_mobil,
  'totalPerHari'  => $totalPerHari,
  'periodeData'   => $periodeData,
  'notifikasi'    => $notifikasi,
]);

extract($data_untuk_view);
require_once __DIR__ . '/../view/keranjang_view.php';
