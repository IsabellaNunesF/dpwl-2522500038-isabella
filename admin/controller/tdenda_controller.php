<?php
// admin/controller/tdenda_controller.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tdenda_model.php';

if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new TDenda();
$aksi  = $_GET['aksi'] ?? 'index';
$pesan = $_GET['pesan'] ?? '';
$tipe_pesan = $_GET['tipe'] ?? '';

switch ($aksi) {
  case 'index':
    // ✅ PERBAIKAN: Ambil data penyewa semua untuk mapping
    $penyewa_all = $model->getPenyewa();
    $map_penyewa = [];
    foreach ($penyewa_all as $p) {
      $map_penyewa[$p['user_plg']] = $p;
    }

    // ✅ PERBAIKAN: getKembaliTerlambat() sekarang sudah include user_plg
    $data_kembali = $model->getKembaliTerlambat();
    $data_detail = [];

    foreach ($data_kembali as $k) {
      $denda = $model->getDendaByKodeKembali($k['kode_kembali']);

      // ✅ PERBAIKAN: Ambil data sewa untuk tgl_selesai
      $sewa = $model->getSewaByKodeBayar($k['kode_bayar']);

      // ✅ PERBAIKAN: Sekarang $k['user_plg'] tersedia dari query JOIN
      $p = $map_penyewa[$k['user_plg']] ?? null;

      $data_detail[$k['kode_kembali']] = [
        'kode_sewa'   => $sewa ? $sewa['kode_sewa'] : '-',
        'tgl_selesai' => $sewa ? $sewa['tgl_selesai'] : '-',
        'wkt_ambil' => $sewa ? $sewa['wkt_ambil'] : '-',
        'nm_lngkp'    => $p ? $p['nm_lngkp'] : '-',
        'no_hp'       => $p ? $p['no_hp'] : '-',
        'sudah_denda' => !empty($denda)
      ];
    }
    break;

  case 'form':
    $penyewa_all = $model->getPenyewa();
    $map_penyewa = [];
    foreach ($penyewa_all as $p) {
      $map_penyewa[$p['user_plg']] = $p;
    }

    $data_kembali = $model->getKembaliTerlambat();
    $data_detail = [];

    foreach ($data_kembali as $k) {
      $denda = $model->getDendaByKodeKembali($k['kode_kembali']);
      $sewa  = $model->getSewaByKodeBayar($k['kode_bayar']);

      $data_detail[$k['kode_kembali']] = [
        'kode_sewa'   => $sewa ? $sewa['kode_sewa'] : '-',
        'tgl_selesai' => $sewa ? $sewa['tgl_selesai'] : '-',
        'wkt_ambil' => $sewa ? $sewa['wkt_ambil'] : '-',
        'sudah_denda' => !empty($denda)
      ];
    }

    $kode_kembali = $_GET['kode_kembali'] ?? '';
    $data_kembali_form = null;
    $data_sewa_form = null;

    if (!empty($kode_kembali)) {
      $kembali_list = $model->getKembaliTerlambat();
      foreach ($kembali_list as $k) {
        if ($k['kode_kembali'] === $kode_kembali) {
          $data_kembali_form = $k;
          break;
        }
      }

      if ($data_kembali_form) {
        $data_sewa_form = $model->getSewaByKodeBayar($data_kembali_form['kode_bayar']);
      }
    }

    if (!$data_kembali_form) {
      header('Location: ' . BASE_URL . 'admin/controller/tdenda_controller.php?aksi=index&pesan=Data+tidak+ditemukan&tipe=danger');
      exit;
    }
    break;

  case 'proses':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $kode_kembali  = $_POST['kode_kembali'] ?? '';
      $telat_jam     = $_POST['telat_jam'] ?? 0;
      $tarif_per_jam = $_POST['tarif_per_jam'] ?? 50000;

      $kode_denda = $model->generateKodeDenda();

      $data_proses = [
        'kode_denda'    => $kode_denda,
        'telat_jam'     => $telat_jam,
        'tarif_per_jam' => $tarif_per_jam,
        'kode_kembali'  => $kode_kembali
      ];

      if ($model->prosesDenda($data_proses)) {
        $pesan = 'Denda keterlambatan berhasil dicatat!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal mencatat denda!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tdenda_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;
}

$data_untuk_view = [
  'judul_halaman'       => 'Denda Keterlambatan',
  'nama_admin'          => $_SESSION['admin_username'],
  'data_kembali'        => $data_kembali ?? [],
  'data_detail'         => $data_detail ?? [],
  'aksi'                => $aksi,
  'pesan'               => $pesan,
  'tipe_pesan'          => $tipe_pesan,
  'data_kembali_form'   => $data_kembali_form ?? null,
  'data_sewa_form'      => $data_sewa_form ?? null
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/tdenda_view.php';
