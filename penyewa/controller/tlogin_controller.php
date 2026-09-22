<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tlogin_model.php';

$model = new TLoginModel($conn);

$pesan = '';
$jenis = ''; // 'sukses' atau 'error'

// Cek jika baru saja selesai registrasi (redirect dari tregistrasi_controller)
if (isset($_GET['status']) && $_GET['status'] === 'berhasil') {
  $pesan = 'Registrasi berhasil! Silakan masuk dengan akun Anda.';
  $jenis = 'sukses';
}

// ==========================================
// PROSES FORM (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_plg = trim($_POST['user_plg'] ?? '');
  $sandi    = $_POST['sandi'] ?? '';
  $ingat    = isset($_POST['ingat']);

  // ---- VALIDASI DASAR ----
  $error = [];
  if (empty($user_plg)) $error[] = 'Username wajib diisi.';
  if (empty($sandi)) $error[] = 'Password wajib diisi.';

  if (empty($error)) {
    $user = $model->getUserByUsername($user_plg);

    // Cek keberadaan user dan kecocokan password
    if ($user && password_verify($sandi, $user['sandi'])) {
      // Login berhasil, simpan data penting ke session
      $_SESSION['user_plg'] = $user['user_plg'];
      $_SESSION['nm_lngkp'] = $user['nm_lngkp'];
      $_SESSION['nik']      = $user['nik'];
      $_SESSION['no_hp']    = $user['no_hp'];
      $_SESSION['login']    = true;

      // Handle fitur "Ingat saya"
      if ($ingat) {
        $lifetime = 30 * 24 * 60 * 60;
        setcookie(session_name(), session_id(), time() + $lifetime, "/");
      }

      // Redirect ke katalog dengan notifikasi sukses
      header('Location: ' . BASE_URL . 'penyewa/controller/tmobil_controller.php?status=login_berhasil');
      exit;
    } else {
      $error[] = 'Username atau Password salah.';
    }
  }

  if (!empty($error)) {
    $pesan = implode('<br>', $error);
    $jenis = 'error';
  }
}

// ==========================================
// DATA UNTUK HEADER (Mencegah error undefined variable)
// ==========================================
$judul_halaman = 'Masuk';
$isLoggedIn    = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userLogin     = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'] ?? '',
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
] : null;
$notifikasi    = ''; // Kosongkan, karena notifikasi bar hanya untuk halaman katalog

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/tlogin_view.php';
