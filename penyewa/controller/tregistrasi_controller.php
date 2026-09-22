<?php
session_start(); // WAJIB: agar header.php bisa mengecek status login
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tregistrasi_model.php';

$model   = new TRegistrasiModel($conn);
$pesan   = '';
$jenis   = ''; // 'sukses' atau 'error'

// ==========================================
// PROSES FORM (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil & bersihkan input
  $user_plg   = trim($_POST['user_plg'] ?? '');
  $sandi      = $_POST['sandi'] ?? '';
  $konfirmasi = $_POST['konfirmasi'] ?? '';
  $nik        = trim($_POST['nik'] ?? '');
  $nm_lngkp   = trim($_POST['nm_lngkp'] ?? '');
  $no_hp      = trim($_POST['no_hp'] ?? '');
  $almt_lngkp = trim($_POST['almt_lngkp'] ?? '');

  // ---- VALIDASI ----
  $error = [];
  if (empty($user_plg) || strlen($user_plg) > 15) {
    $error[] = 'Username wajib diisi dan maksimal 15 karakter.';
  }
  if (empty($sandi)) {
    $error[] = 'Password wajib diisi.';
  }
  if ($sandi !== $konfirmasi) {
    $error[] = 'Konfirmasi password tidak cocok.';
  }
  if (empty($nik) || strlen($nik) !== 16 || !ctype_digit($nik)) {
    $error[] = 'NIK harus 16 digit angka.';
  }
  if (empty($nm_lngkp)) {
    $error[] = 'Nama lengkap wajib diisi.';
  }
  if (empty($no_hp)) {
    $error[] = 'Nomor HP wajib diisi.';
  }
  if (empty($almt_lngkp)) {
    $error[] = 'Alamat lengkap wajib diisi.';
  }

  // Cek duplikasi username
  if (empty($error) && $model->cekUsername($user_plg)) {
    $error[] = 'Username sudah digunakan, silakan pilih yang lain.';
  }
  // Cek duplikasi NIK
  if (empty($error) && $model->cekNIK($nik)) {
    $error[] = 'NIK sudah terdaftar atas nama pengguna lain.';
  }

  // ---- SIMPAN ----
  if (empty($error)) {
    $data = [
      'user_plg'   => $user_plg,
      'sandi'      => $sandi,
      'nik'        => $nik,
      'nm_lngkp'   => $nm_lngkp,
      'no_hp'      => $no_hp,
      'almt_lngkp' => $almt_lngkp,
    ];

    if ($model->daftar($data)) {
      // Redirect ke login dengan pesan sukses
      header('Location: ' . BASE_URL . 'penyewa/controller/tlogin_controller.php?status=berhasil');
      exit;
    } else {
      $error[] = 'Gagal menyimpan data. Silakan coba lagi.';
    }
  }

  // Jika ada error, tampilkan di view
  if (!empty($error)) {
    $pesan = implode('<br>', $error);
    $jenis = 'error';
  }
}

// ==========================================
// DATA UNTUK HEADER (Mencegah error undefined variable)
// ==========================================
$judul_halaman = 'Registrasi Penyewa';
$isLoggedIn    = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userLogin     = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'] ?? '',
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
] : null;
$notifikasi    = ''; // Kosongkan, tidak ada notifikasi bar di halaman registrasi

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/tregistrasi_view.php';
