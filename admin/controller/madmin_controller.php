<?php
// admin/controller/madmin_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/madmin_model.php';

// Proteksi
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new MAdmin();
$aksi = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';

switch ($aksi) {
  case 'index':
    $data_admin = $model->getAll();
    break;

  case 'tambah':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = trim($_POST['username']);
      $password = $_POST['password'];
      $status = $_POST['status_akun'];

      // Cek username sudah ada atau belum
      $cek = $model->getByUsername($username);
      if (!empty($cek)) {
        $pesan = 'Username sudah terdaftar!';
        $tipe_pesan = 'danger';
      } else {
        $data = [
          'username' => $username,
          'password' => $password,
          'status_akun' => $status
        ];
        if ($model->tambah($data)) {
          $pesan = 'Data admin berhasil ditambahkan!';
          $tipe_pesan = 'success';
          header('Location: ' . BASE_URL . 'admin/controller/madmin_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
          exit;
        } else {
          $pesan = 'Gagal menambahkan data admin!';
          $tipe_pesan = 'danger';
        }
      }
    }
    $data_admin = $model->getAll();
    break;

  case 'ubah':
    $username_lama = $_GET['username'] ?? '';
    $admin_dipilih = $model->getByUsername($username_lama);
    $admin_dipilih = !empty($admin_dipilih) ? $admin_dipilih[0] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username_baru = trim($_POST['username']);
      $password_baru = $_POST['password'];
      $status_baru = $_POST['status_akun'];

      $data = [
        'username' => $username_baru,
        'status_akun' => $status_baru
      ];
      if (!empty($password_baru)) {
        $data['password'] = $password_baru;
      }

      if ($model->ubah($data, $username_lama)) {
        $pesan = 'Data admin berhasil diubah!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/madmin_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal mengubah data admin!';
        $tipe_pesan = 'danger';
      }
    }
    $data_admin = $model->getAll();
    break;

  case 'hapus':
    $username = $_GET['username'] ?? '';
    if (!empty($username)) {
      if ($model->hapus($username)) {
        $pesan = 'Data admin berhasil dihapus!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal menghapus data admin!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/madmin_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  default:
    $data_admin = $model->getAll();
    break;
}

// Ambil pesan dari URL jika ada
if (isset($_GET['pesan'])) {
  $pesan = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

$data_untuk_view = [
  'judul_halaman' => 'Data Admin',
  'nama_admin'    => $_SESSION['admin_username'],
  'data_admin'    => $data_admin ?? [],
  'aksi'          => $aksi,
  'admin_dipilih' => $admin_dipilih ?? null,
  'pesan'         => $pesan,
  'tipe_pesan'    => $tipe_pesan
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/madmin_view.php';
