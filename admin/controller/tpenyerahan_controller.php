<?php
// admin/controller/tpenyerahan_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tpenyerahan_model.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new TPenyerahan();
$aksi  = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';

switch ($aksi) {
  case 'index':
    $data_sewa   = $model->getSewaUntukDiserahkan();
    $penyewa_all = $model->getPenyewa();
    $mobil_all   = $model->getMobil();

    // Mapping data untuk efisiensi looping
    $map_penyewa = [];
    foreach ($penyewa_all as $p) $map_penyewa[$p['user_plg']] = $p;

    $map_mobil = [];
    foreach ($mobil_all as $m) $map_mobil[$m['no_plat']] = $m;

    // Gabungkan detail penyewa dan daftar mobil
    $data_detail = [];
    foreach ($data_sewa as $s) {
      $pilih_list = $model->getPilihByKode($s['kode_sewa']);
      $nama_mobil = [];

      foreach ($pilih_list as $pl) {
        if (isset($map_mobil[$pl['no_plat']])) {
          $nama_mobil[] = $map_mobil[$pl['no_plat']]['nm_mobil'] . ' (' . $pl['no_plat'] . ')';
        }
      }

      $p = $map_penyewa[$s['user_plg']] ?? null;
      /* $data_detail[$s['kode_sewa']] = [
        'nm_lngkp'   => $p ? $p['nm_lngkp'] : '-',
        'no_hp'      => $p ? $p['no_hp'] : '-',
        'nama_mobil' => implode(', ', $nama_mobil)
      ]; */

      // [TAMBAHAN] Cek status pembayaran untuk info di view
      $total_tagihan = 0;
      foreach ($pilih_list as $pl) {
        $total_tagihan += ($pl['hrg_sewa'] * $pl['n_hari']);
      }

      $bayar_list = $conn->select('t_bayar', ['kode_sewa' => $s['kode_sewa']]);
      $total_valid = 0;
      foreach ($bayar_list as $b) {
        if ($b['sttus_byr'] === 'valid') $total_valid += $b['nominal'];
      }
      $status_bayar_info = ($total_valid >= $total_tagihan && $total_tagihan > 0) ? 'LUNAS' : 'BELUM LUNAS (DP)';

      $data_detail[$s['kode_sewa']] = [
        'nm_lngkp'       => $p ? $p['nm_lngkp'] : '-',
        'no_hp'          => $p ? $p['no_hp'] : '-',
        'ft_ktp'          => $p ? $p['ft_ktp'] : '-',
        'ft_sim_a'          => $p ? $p['ft_sim_a'] : '-',
        'nama_mobil'     => implode(', ', $nama_mobil),
        'status_bayar'   => $status_bayar_info // [TAMBAHAN]
      ];
    }
    break;

  /* case 'proses':
    $kode_sewa = $_GET['kode_sewa'] ?? '';
    if (!empty($kode_sewa)) {
      if ($model->prosesPenyerahan($kode_sewa)) {
        $pesan = 'Mobil berhasil diserahkan! Status sewa kini AKTIF dan unit berstatus DISEWA.';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal memproses penyerahan mobil!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tpenyerahan_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit; */

  case 'form':
    $kode_sewa = $_GET['kode_sewa'] ?? '';
    $data_sewa_form = null;

    // ✅ TAMBAHAN: Load data sewa dan detail seperti di case 'index'
    $data_sewa   = $model->getSewaUntukDiserahkan();
    $penyewa_all = $model->getPenyewa();
    $mobil_all   = $model->getMobil();

    $map_penyewa = [];
    foreach ($penyewa_all as $p) $map_penyewa[$p['user_plg']] = $p;

    $map_mobil = [];
    foreach ($mobil_all as $m) $map_mobil[$m['no_plat']] = $m;

    $data_detail = [];
    foreach ($data_sewa as $s) {
      $pilih_list = $model->getPilihByKode($s['kode_sewa']);
      $nama_mobil = [];

      foreach ($pilih_list as $pl) {
        if (isset($map_mobil[$pl['no_plat']])) {
          $nama_mobil[] = $map_mobil[$pl['no_plat']]['nm_mobil'] . ' (' . $pl['no_plat'] . ')';
        }
      }

      $p = $map_penyewa[$s['user_plg']] ?? null;

      $total_tagihan = 0;
      foreach ($pilih_list as $pl) {
        $total_tagihan += ($pl['hrg_sewa'] * $pl['n_hari']);
      }

      $bayar_list = $conn->select('t_bayar', ['kode_sewa' => $s['kode_sewa']]);
      $total_valid = 0;
      foreach ($bayar_list as $b) {
        if ($b['sttus_byr'] === 'valid') $total_valid += $b['nominal'];
      }

      $status_bayar_info = ($total_valid >= $total_tagihan && $total_tagihan > 0) ? 'LUNAS' : 'BELUM LUNAS (DP)';

      $data_detail[$s['kode_sewa']] = [
        'nm_lngkp'     => $p ? $p['nm_lngkp'] : '-',
        'no_hp'        => $p ? $p['no_hp'] : '-',
        'ft_ktp'       => $p ? $p['ft_ktp'] : '-',
        'ft_sim_a'     => $p ? $p['ft_sim_a'] : '-',
        'nama_mobil'   => implode(', ', $nama_mobil),
        'status_bayar' => $status_bayar_info
      ];
    }

    // ✅ Ambil data spesifik untuk modal
    if (!empty($kode_sewa)) {
      foreach ($data_sewa as $s) {
        if ($s['kode_sewa'] === $kode_sewa && $s['status_sewa'] === 'disetujui') {
          $data_sewa_form = $s;
          break;
        }
      }
    }

    if (!$data_sewa_form) {
      $pesan = 'Data sewa tidak ditemukan atau sudah tidak bisa diserahkan!';
      $tipe_pesan = 'danger';
      header('Location: ' . BASE_URL . 'admin/controller/tpenyerahan_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
      exit;
    }
    break;

  case 'proses':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $kode_sewa       = $_POST['kode_sewa'] ?? '';
      $wkt_ambil_aktual = $_POST['wkt_ambil_aktual'] ?? date('H:i:s');

      if (!empty($kode_sewa)) {
        if ($model->prosesPenyerahan($kode_sewa, $wkt_ambil_aktual)) {
          $pesan = 'Mobil berhasil diserahkan! Status sewa kini AKTIF.';
          $tipe_pesan = 'success';
        } else {
          $pesan = 'Gagal memproses penyerahan mobil!';
          $tipe_pesan = 'danger';
        }
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tpenyerahan_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;
}

if (isset($_GET['pesan'])) {
  $pesan      = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

$data_untuk_view = [
  'judul_halaman' => 'Penyerahan Mobil',
  'nama_admin'    => $_SESSION['admin_username'],
  'data_sewa'     => $data_sewa ?? [],
  'data_detail'   => $data_detail ?? [],
  'aksi'          => $aksi,
  'pesan'         => $pesan,
  'tipe_pesan'    => $tipe_pesan
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/tpenyerahan_view.php';
