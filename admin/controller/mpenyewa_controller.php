<?php
// admin/controller/mpenyewa_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/mpenyewa_model.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new MPenyewa();
$aksi = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';
$penyewa_dipilih = null;

// ==================== FUNGSI HELPER UPLOAD ====================
function uploadFotoPenyewa($input_name, $folder = 'penyewa')
{
  if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $filename = $_FILES[$input_name]['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if (!in_array(strtolower($ext), $allowed)) {
      return false;
    }

    // Nama file unik
    $new_filename = $input_name . '_' . time() . '_' . uniqid() . '.' . $ext;
    $target_dir = UPLOAD_DIR . $folder . '/';

    // Pastikan folder ada
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_dir . $new_filename)) {
      return $new_filename;
    }
  }
  return false;
}

function hapusFotoPenyewa($nama_file, $folder = 'penyewa')
{
  if (!empty($nama_file)) {
    $path = UPLOAD_DIR . $folder . '/' . $nama_file;
    if (file_exists($path)) {
      unlink($path);
    }
  }
}
// =================================================================

switch ($aksi) {
  case 'index':
    $data_penyewa = $model->getAll();
    break;

  case 'tambah':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'user_plg'   => trim($_POST['user_plg']),
        'sandi'      => password_hash($_POST['sandi'], PASSWORD_BCRYPT),
        'nik'        => trim($_POST['nik']),
        'nm_lngkp'   => trim($_POST['nm_lngkp']),
        'no_hp'      => trim($_POST['no_hp']),
        'almt_lngkp' => trim($_POST['almt_lngkp'])
      ];

      // Cek user_plg sudah ada atau belum
      $cek = $model->getByUser($data['user_plg']);
      if (!empty($cek)) {
        $pesan = 'Username Pelanggan sudah terdaftar!';
        $tipe_pesan = 'danger';
      } else {
        // Upload foto
        $ft_ktp = uploadFotoPenyewa('ft_ktp');
        if ($ft_ktp) $data['ft_ktp'] = $ft_ktp;

        $ft_sim_a = uploadFotoPenyewa('ft_sim_a');
        if ($ft_sim_a) $data['ft_sim_a'] = $ft_sim_a;

        if ($model->tambah($data)) {
          $pesan = 'Data penyewa berhasil ditambahkan!';
          $tipe_pesan = 'success';
          header('Location: ' . BASE_URL . 'admin/controller/mpenyewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
          exit;
        } else {
          $pesan = 'Gagal menambahkan data penyewa!';
          $tipe_pesan = 'danger';
        }
      }
    }
    $data_penyewa = $model->getAll();
    break;

  case 'ubah':
    $user_plg_lama = $_GET['user_plg'] ?? '';
    $ambil_data = $model->getByUser($user_plg_lama);
    $penyewa_dipilih = !empty($ambil_data) ? $ambil_data[0] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'user_plg'   => trim($_POST['user_plg']),
        'nik'        => trim($_POST['nik']),
        'nm_lngkp'   => trim($_POST['nm_lngkp']),
        'no_hp'      => trim($_POST['no_hp']),
        'almt_lngkp' => trim($_POST['almt_lngkp'])
      ];

      // Jika sandi diisi, update sandi dengan hash baru
      if (!empty($_POST['sandi'])) {
        $data['sandi'] = password_hash($_POST['sandi'], PASSWORD_BCRYPT);
      }

      // Upload foto baru jika ada
      if (!empty($_FILES['ft_ktp']['name'])) {
        $ft_ktp_baru = uploadFotoPenyewa('ft_ktp');
        if ($ft_ktp_baru) {
          hapusFotoPenyewa($penyewa_dipilih['ft_ktp']); // hapus foto lama
          $data['ft_ktp'] = $ft_ktp_baru;
        }
      }

      if (!empty($_FILES['ft_sim_a']['name'])) {
        $ft_sim_a_baru = uploadFotoPenyewa('ft_sim_a');
        if ($ft_sim_a_baru) {
          hapusFotoPenyewa($penyewa_dipilih['ft_sim_a']);
          $data['ft_sim_a'] = $ft_sim_a_baru;
        }
      }

      if ($model->ubah($data, $user_plg_lama)) {
        $pesan = 'Data penyewa berhasil diubah!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/mpenyewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal mengubah data penyewa!';
        $tipe_pesan = 'danger';
      }
    }
    $data_penyewa = $model->getAll();
    break;

  case 'hapus':
    $user_plg = $_GET['user_plg'] ?? '';
    if (!empty($user_plg)) {
      // Ambil data penyewa untuk hapus file foto
      $ambil_data = $model->getByUser($user_plg);
      $penyewa = !empty($ambil_data) ? $ambil_data[0] : null;

      if ($model->hapus($user_plg)) {
        // Hapus file foto dari server
        if ($penyewa) {
          hapusFotoPenyewa($penyewa['ft_ktp']);
          hapusFotoPenyewa($penyewa['ft_sim_a']);
        }
        $pesan = 'Data penyewa berhasil dihapus!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal menghapus data penyewa!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/mpenyewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  default:
    $data_penyewa = $model->getAll();
    break;
}

// Ambil pesan dari URL jika ada (setelah redirect)
if (isset($_GET['pesan'])) {
  $pesan = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

$data_untuk_view = [
  'judul_halaman'   => 'Data Penyewa',
  'nama_admin'      => $_SESSION['admin_username'],
  'data_penyewa'    => $data_penyewa ?? [],
  'aksi'            => $aksi,
  'penyewa_dipilih' => $penyewa_dipilih ?? null,
  'pesan'           => $pesan,
  'tipe_pesan'      => $tipe_pesan
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/mpenyewa_view.php';
