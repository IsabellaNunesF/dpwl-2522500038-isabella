<?php
// admin/controller/tpengembalian_controller.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tpengembalian_model.php';

if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new TPengembalian();
$aksi  = $_GET['aksi'] ?? 'index';
$pesan = $_GET['pesan'] ?? '';
$tipe_pesan = $_GET['tipe'] ?? '';

switch ($aksi) {
  case 'index':
    $data_sewa   = $model->getSewaAktif();
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

      $bayar_list = $model->getBayarByKodeSewa($s['kode_sewa']);
      $total_valid = 0;
      $kode_bayar_terakhir = null;
      foreach ($bayar_list as $b) {
        if ($b['sttus_byr'] === 'valid') {
          $total_valid += $b['nominal'];
          $kode_bayar_terakhir = $b['kode_bayar'];
        }
      }

      $status_bayar = ($total_valid >= $total_tagihan && $total_tagihan > 0) ? 'LUNAS' : 'BELUM LUNAS';
      $sisa_tagihan = $total_tagihan - $total_valid;

      $data_detail[$s['kode_sewa']] = [
        'nm_lngkp'     => $p ? $p['nm_lngkp'] : '-',
        'no_hp'        => $p ? $p['no_hp'] : '-',
        'nama_mobil'   => implode(', ', $nama_mobil),
        'status_bayar' => $status_bayar,
        'sisa_tagihan' => $sisa_tagihan > 0 ? $sisa_tagihan : 0,
        'kode_bayar'   => $kode_bayar_terakhir
      ];
    }
    break;

  case 'form':
    $kode_sewa = $_GET['kode_sewa'] ?? '';
    $data_sewa_form = null;
    $detail_form = null;

    // ✅ TAMBAHKAN: Ambil data untuk tabel juga
    $data_sewa   = $model->getSewaAktif();
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

      $bayar_list = $model->getBayarByKodeSewa($s['kode_sewa']);
      $total_valid = 0;
      $kode_bayar_terakhir = null;
      foreach ($bayar_list as $b) {
        if ($b['sttus_byr'] === 'valid') {
          $total_valid += $b['nominal'];
          $kode_bayar_terakhir = $b['kode_bayar'];
        }
      }

      $status_bayar = ($total_valid >= $total_tagihan && $total_tagihan > 0) ? 'LUNAS' : 'BELUM LUNAS';
      $sisa_tagihan = $total_tagihan - $total_valid;

      $data_detail[$s['kode_sewa']] = [
        'nm_lngkp'     => $p ? $p['nm_lngkp'] : '-',
        'no_hp'        => $p ? $p['no_hp'] : '-',
        'nama_mobil'   => implode(', ', $nama_mobil),
        'status_bayar' => $status_bayar,
        'sisa_tagihan' => $sisa_tagihan > 0 ? $sisa_tagihan : 0,
        'kode_bayar'   => $kode_bayar_terakhir
      ];
    }
    // ✅ SELESAI TAMBAHAN

    if (!empty($kode_sewa)) {
      foreach ($data_sewa as $s) {
        if ($s['kode_sewa'] === $kode_sewa) {
          $data_sewa_form = $s;
          break;
        }
      }

      if ($data_sewa_form) {
        $bayar_list = $model->getBayarByKodeSewa($kode_sewa);
        $kode_bayar = null;
        foreach ($bayar_list as $b) {
          if ($b['sttus_byr'] === 'valid') $kode_bayar = $b['kode_bayar'];
        }
        $detail_form = ['kode_bayar' => $kode_bayar];
      }
    }

    if (!$data_sewa_form) {
      header('Location: ' . BASE_URL . 'admin/controller/tpengembalian_controller.php?aksi=index&pesan=Data+tidak+ditemukan&tipe=danger');
      exit;
    }
    break;

  case 'proses':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $kode_sewa   = $_POST['kode_sewa'] ?? '';
      $tgl_wkt_blk = $_POST['tgl_wkt_blk'] ?? date('Y-m-d H:i:s');
      $stts_blk    = $_POST['stts_blk'] ?? 'normal';
      $ket_blk     = $_POST['ket_blk'] ?? '';
      $kode_bayar  = $_POST['kode_bayar'] ?? '';

      $kode_kembali = $model->generateKodeKembali();

      $data_proses = [
        'kode_kembali' => $kode_kembali,
        'tgl_wkt_blk'  => $tgl_wkt_blk,
        'stts_blk'     => $stts_blk,
        'ket_blk'      => $ket_blk,
        'kode_bayar'   => $kode_bayar,
        'kode_sewa'    => $kode_sewa
      ];

      if ($model->prosesPengembalian($data_proses)) {
        $pesan = 'Pengembalian mobil berhasil diproses!';
        $tipe_pesan = 'success';

        // Redirect ke denda jika terlambat
        if ($stts_blk === 'terlambat') {
          header('Location: ' . BASE_URL . 'admin/controller/tdenda_controller.php?aksi=form&kode_kembali=' . urlencode($kode_kembali));
          exit;
        }
      } else {
        $pesan = 'Gagal memproses pengembalian mobil!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tpengembalian_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;
}

$data_untuk_view = [
  'judul_halaman'    => 'Pengembalian Mobil',
  'nama_admin'       => $_SESSION['admin_username'],
  'data_sewa'        => $data_sewa ?? [],
  'data_detail'      => $data_detail ?? [],
  'aksi'             => $aksi,
  'pesan'            => $pesan,
  'tipe_pesan'       => $tipe_pesan,
  'data_sewa_form'   => $data_sewa_form ?? null,
  'detail_form'      => $detail_form ?? null
];
extract($data_untuk_view);
require_once __DIR__ . '/../view/tpengembalian_view.php';
