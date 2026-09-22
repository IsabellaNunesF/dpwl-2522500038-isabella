<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/detail_model.php';

// ==========================================
// CEK STATUS LOGIN
// ==========================================
$isLoggedIn = !empty($_SESSION['login']) && !empty($_SESSION['user_plg']);
$userLogin  = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'],
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
  'nik'      => $_SESSION['nik'] ?? '',
  'no_hp'    => $_SESSION['no_hp'] ?? '',
] : null;

// Notifikasi untuk header
$notifikasi = $_SESSION['notifikasi'] ?? '';
unset($_SESSION['notifikasi']);

// ==========================================
// AMBIL DATA MOBIL
// ==========================================
$plat  = $_GET['plat'] ?? '';
$model = new DetailModel($conn);
$mobil = $model->getMobilByPlat($plat);

if (empty($plat) || !$mobil) {
  header('Location: ' . BASE_URL . 'penyewa/controller/tmobil_controller.php');
  exit;
}

// ==========================================
// DATA UNTUK HEADER
// ==========================================
$judul_halaman = 'Detail ' . $mobil['nm_mobil'];

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/detail_view.php';
