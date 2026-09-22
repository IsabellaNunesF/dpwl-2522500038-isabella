<?php
// admin/controller/mmobil_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/mmobil_model.php';

// Proteksi
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new MMobil();
$aksi = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';

// ==================== FUNGSI HELPER UPLOAD ====================
function uploadFoto($input_name, $folder = 'mobil')
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

function hapusFoto($nama_file, $folder = 'mobil')
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
    $data_mobil = $model->getAll();
    break;

  case 'tambah':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'no_plat'     => strtoupper(trim($_POST['no_plat'])),
        'nm_mobil'    => trim($_POST['nm_mobil']),
        'nkursi'      => $_POST['nkursi'],
        'nbagasi'     => $_POST['nbagasi'],
        'jns_mobil'   => $_POST['jns_mobil'],
        'transmisi'   => $_POST['transmisi'],
        'thn_buat'    => $_POST['thn_buat'],
        'hrg_hari'    => $_POST['hrg_hari'],
        'cttn_unit'   => trim($_POST['cttn_unit']),
        'status_unit' => $_POST['status_unit']
      ];

      // Cek no_plat sudah ada atau belum
      $cek = $model->getByPlat($data['no_plat']);
      if (!empty($cek)) {
        $pesan = 'No Plat sudah terdaftar!';
        $tipe_pesan = 'danger';
      } else {
        // Upload foto
        $ft_depan = uploadFoto('ft_depan');
        if ($ft_depan) $data['ft_depan'] = $ft_depan;

        $ft_blkg = uploadFoto('ft_blkg');
        if ($ft_blkg) $data['ft_blkg'] = $ft_blkg;

        $ft_interior = uploadFoto('ft_interior');
        if ($ft_interior) $data['ft_interior'] = $ft_interior;

        if ($model->tambah($data)) {
          $pesan = 'Data mobil berhasil ditambahkan!';
          $tipe_pesan = 'success';
          header('Location: ' . BASE_URL . 'admin/controller/mmobil_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
          exit;
        } else {
          $pesan = 'Gagal menambahkan data mobil!';
          $tipe_pesan = 'danger';
        }
      }
    }
    $data_mobil = $model->getAll();
    break;

  case 'ubah':
    $no_plat_lama = $_GET['no_plat'] ?? '';
    $mobil_dipilih = $model->getByPlat($no_plat_lama);
    $mobil_dipilih = !empty($mobil_dipilih) ? $mobil_dipilih[0] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'no_plat'     => strtoupper(trim($_POST['no_plat'])),
        'nm_mobil'    => trim($_POST['nm_mobil']),
        'nkursi'      => $_POST['nkursi'],
        'nbagasi'     => $_POST['nbagasi'],
        'jns_mobil'   => $_POST['jns_mobil'],
        'transmisi'   => $_POST['transmisi'],
        'thn_buat'    => $_POST['thn_buat'],
        'hrg_hari'    => $_POST['hrg_hari'],
        'cttn_unit'   => trim($_POST['cttn_unit']),
        'status_unit' => $_POST['status_unit']
      ];

      // Upload foto baru jika ada
      if (!empty($_FILES['ft_depan']['name'])) {
        $ft_depan_baru = uploadFoto('ft_depan');
        if ($ft_depan_baru) {
          hapusFoto($mobil_dipilih['ft_depan']); // hapus foto lama
          $data['ft_depan'] = $ft_depan_baru;
        }
      }
      if (!empty($_FILES['ft_blkg']['name'])) {
        $ft_blkg_baru = uploadFoto('ft_blkg');
        if ($ft_blkg_baru) {
          hapusFoto($mobil_dipilih['ft_blkg']);
          $data['ft_blkg'] = $ft_blkg_baru;
        }
      }
      if (!empty($_FILES['ft_interior']['name'])) {
        $ft_interior_baru = uploadFoto('ft_interior');
        if ($ft_interior_baru) {
          hapusFoto($mobil_dipilih['ft_interior']);
          $data['ft_interior'] = $ft_interior_baru;
        }
      }

      if ($model->ubah($data, $no_plat_lama)) {
        $pesan = 'Data mobil berhasil diubah!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/mmobil_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal mengubah data mobil!';
        $tipe_pesan = 'danger';
      }
    }
    $data_mobil = $model->getAll();
    break;

  case 'hapus':
    $no_plat = $_GET['no_plat'] ?? '';
    if (!empty($no_plat)) {
      // Ambil data mobil untuk hapus file foto
      $mobil = $model->getByPlat($no_plat);
      $mobil = !empty($mobil) ? $mobil[0] : null;

      if ($model->hapus($no_plat)) {
        // Hapus file foto dari server
        if ($mobil) {
          hapusFoto($mobil['ft_depan']);
          hapusFoto($mobil['ft_blkg']);
          hapusFoto($mobil['ft_interior']);
        }
        $pesan = 'Data mobil berhasil dihapus!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal menghapus data mobil!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/mmobil_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  default:
    $data_mobil = $model->getAll();
    break;
}

// Ambil pesan dari URL jika ada
if (isset($_GET['pesan'])) {
  $pesan = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

$data_untuk_view = [
  'judul_halaman' => 'Data Mobil',
  'nama_admin'    => $_SESSION['admin_username'],
  'data_mobil'    => $data_mobil ?? [],
  'aksi'          => $aksi,
  'mobil_dipilih' => $mobil_dipilih ?? null,
  'pesan'         => $pesan,
  'tipe_pesan'    => $tipe_pesan
];
extract($data_untuk_view);
require_once __DIR__ . '/../view/mmobil_view.php';
