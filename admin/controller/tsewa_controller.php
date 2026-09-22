<?php
// admin/controller/tsewa_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tsewa_model.php';

// Proteksi
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new TSewa();
$aksi  = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';

switch ($aksi) {
  case 'index':
    $data_sewa = $model->getAllWithDetail();
    break;

  case 'tambah':
    $data_penyewa = $model->getPenyewa();
    $data_mobil   = $model->getMobilTersedia();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Validasi tanggal
      $tgl_mulai   = $_POST['tgl_mulai'];
      $tgl_selesai = $_POST['tgl_selesai'];
      if ($tgl_mulai >= $tgl_selesai) {
        $pesan = 'Tanggal mulai harus sebelum tanggal selesai!';
        $tipe_pesan = 'danger';
        break;
      }

      // Validasi mobil tidak duplikat
      $no_plat_list = $_POST['no_plat'] ?? [];
      $no_plat_list = array_filter($no_plat_list);
      if (count($no_plat_list) !== count(array_unique($no_plat_list))) {
        $pesan = 'Mobil tidak boleh dipilih lebih dari 1 kali!';
        $tipe_pesan = 'danger';
        break;
      }

      if (empty($no_plat_list)) {
        $pesan = 'Pilih minimal 1 mobil!';
        $tipe_pesan = 'danger';
        break;
      }

      // ✅ HITUNG DURASI DI SERVER (pasti konsisten)
      $tgl1 = new DateTime($tgl_mulai);
      $tgl2 = new DateTime($tgl_selesai);
      #$durasi = $tgl1->diff($tgl2)->days + 1; // inklusif
      $durasi = $tgl1->diff($tgl2)->days; // inklusif

      // var_dump($tgl_mulai);
      // var_dump($tgl_selesai);

      // $tgl1 = new DateTime($tgl_mulai);
      // $tgl2 = new DateTime($tgl_selesai);

      // echo $tgl1->format('Y-m-d H:i:s');
      // echo '<br>';
      // echo $tgl2->format('Y-m-d H:i:s');
      // echo '<br>';

      // var_dump($tgl1->diff($tgl2)->days);
      // exit;

      $kode_sewa = $model->generateKodeSewa();

      $data_sewa = [
        'kode_sewa'   => $kode_sewa,
        'tgl_mulai'   => $tgl_mulai,
        'tgl_selesai' => $tgl_selesai,
        'wkt_ambil'   => $_POST['wkt_ambil'],
        'status_sewa' => $_POST['status_sewa'],
        'user_plg'    => $_POST['user_plg']
      ];

      if ($model->tambah($data_sewa)) {
        // Insert detail mobil dengan durasi yang sama
        for ($i = 0; $i < count($no_plat_list); $i++) {
          $model->tambahPilih([
            'kode_sewa' => $kode_sewa,
            'no_plat'   => $no_plat_list[$i],
            'hrg_sewa'  => $_POST['hrg_sewa'][$i],
            'n_hari'    => $durasi
          ]);
        }

        $pesan = 'Data sewa berhasil ditambahkan!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/tsewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal menambahkan data sewa!';
        $tipe_pesan = 'danger';
      }
    }
    break;

  case 'ubah':
    $kode_sewa_lama = $_GET['kode_sewa'] ?? '';
    $sewa_dipilih   = $model->getByKode($kode_sewa_lama);
    $sewa_dipilih   = !empty($sewa_dipilih) ? $sewa_dipilih[0] : null;
    $detail_pilih   = $model->getPilihByKode($kode_sewa_lama);

    $data_penyewa = $model->getPenyewa();
    $data_mobil   = $model->getMobilTersedia();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $tgl_mulai   = $_POST['tgl_mulai'];
      $tgl_selesai = $_POST['tgl_selesai'];
      if ($tgl_mulai >= $tgl_selesai) {
        $pesan = 'Tanggal mulai harus sebelum tanggal selesai!';
        $tipe_pesan = 'danger';
        break;
      }

      $no_plat_list = $_POST['no_plat'] ?? [];
      $no_plat_list = array_filter($no_plat_list);
      if (count($no_plat_list) !== count(array_unique($no_plat_list))) {
        $pesan = 'Mobil tidak boleh dipilih lebih dari 1 kali!';
        $tipe_pesan = 'danger';
        break;
      }

      if (empty($no_plat_list)) {
        $pesan = 'Pilih minimal 1 mobil!';
        $tipe_pesan = 'danger';
        break;
      }

      // ✅ HITUNG DURASI DI SERVER
      $tgl1 = new DateTime($tgl_mulai);
      $tgl2 = new DateTime($tgl_selesai);
      #$durasi = $tgl1->diff($tgl2)->days + 1;
      $durasi = $tgl1->diff($tgl2)->days;

      $data_sewa = [
        'tgl_mulai'   => $tgl_mulai,
        'tgl_selesai' => $tgl_selesai,
        'wkt_ambil'   => $_POST['wkt_ambil'],
        'status_sewa' => $_POST['status_sewa'],
        'user_plg'    => $_POST['user_plg']
      ];

      if ($model->ubah($data_sewa, $kode_sewa_lama)) {
        $model->hapusPilihByKode($kode_sewa_lama);

        for ($i = 0; $i < count($no_plat_list); $i++) {
          $model->tambahPilih([
            'kode_sewa' => $kode_sewa_lama,
            'no_plat'   => $no_plat_list[$i],
            'hrg_sewa'  => $_POST['hrg_sewa'][$i],
            'n_hari'    => $durasi
          ]);
        }

        $pesan = 'Data sewa berhasil diubah!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/tsewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal mengubah data sewa!';
        $tipe_pesan = 'danger';
      }
    }
    break;

  case 'hapus':
    $kode_sewa = $_GET['kode_sewa'] ?? '';
    if (!empty($kode_sewa)) {
      $model->hapusPilihByKode($kode_sewa);
      if ($model->hapus($kode_sewa)) {
        $pesan = 'Data sewa berhasil dihapus!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal menghapus data sewa!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tsewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  case 'ubah_status':
    $kode_sewa = $_POST['kode_sewa'] ?? '';
    $status    = $_POST['status_sewa'] ?? '';
    if (!empty($kode_sewa) && !empty($status)) {
      if ($model->updateStatus($kode_sewa, $status)) {
        $pesan = 'Status berhasil diubah!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal mengubah status!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tsewa_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  default:
    $data_sewa = $model->getAllWithDetail();
    break;
}

if (isset($_GET['pesan'])) {
  $pesan      = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

$total_mobil_tersedia = count($data_mobil ?? []);

$data_untuk_view = [
  'judul_halaman'        => 'Data Sewa',
  'nama_admin'           => $_SESSION['admin_username'],
  'data_sewa'            => $data_sewa ?? [],
  'data_penyewa'         => $data_penyewa ?? [],
  'data_mobil'           => $data_mobil ?? [],
  'aksi'                 => $aksi,
  'sewa_dipilih'         => $sewa_dipilih ?? null,
  'detail_pilih'         => $detail_pilih ?? [],
  'pesan'                => $pesan,
  'tipe_pesan'           => $tipe_pesan,
  'total_mobil_tersedia' => $total_mobil_tersedia
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/tsewa_view.php';
