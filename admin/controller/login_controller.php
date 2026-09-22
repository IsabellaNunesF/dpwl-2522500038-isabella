<?php


if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// PROTEKSI: Jika user sudah login, tendang langsung ke dashboard
if (isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

// Gunakan __DIR__ agar path file selalu benar, tidak peduli dari mana file ini dipanggil
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/login_model.php';

$model = new LoginModel($conn);

$data_untuk_view = [
  'judul_halaman' => 'Login Admin',
  'pesan_error'   => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $admin = $model->getAdminByUsername($username);

  if ($admin) {
    if (password_verify($password, $admin['password'])) {
      if ($admin['status_akun'] === 'aktif') {
        $_SESSION['admin_username'] = $admin['username'];

        // PERBAIKAN: Redirect menggunakan BASE_URL agar selalu benar
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
      } else {
        $data_untuk_view['pesan_error'] = 'Akun Anda sedang dinonaktifkan.';
      }
    } else {
      $data_untuk_view['pesan_error'] = 'Password yang Anda masukkan salah.';
    }
  } else {
    $data_untuk_view['pesan_error'] = 'Login tidak ditemukan.';
  }
}

extract($data_untuk_view);
require_once __DIR__ . '/../view/login_view.php';
