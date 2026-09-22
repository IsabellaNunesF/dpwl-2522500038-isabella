<?php
// admin/controller/tpembayaran_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/tpembayaran_model.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new TPembayaran();
$aksi  = $_GET['aksi'] ?? 'index';
$pesan = '';
$tipe_pesan = '';

// ==================== FUNGSI HELPER UPLOAD ====================
function uploadBukti($input_name, $folder = 'pembayaran')
{
  if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $filename = $_FILES[$input_name]['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), $allowed)) {
      return false;
    }
    $new_filename = $input_name . '_' . time() . '_' . uniqid() . '.' . $ext;
    $target_dir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0777, true);
    }
    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_dir . $new_filename)) {
      return $new_filename;
    }
  }
  return false;
}

function hapusBukti($nama_file, $folder = 'pembayaran')
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
    $data_bayar = $model->getAllWithDetail();
    break;

  case 'tambah':
    $data_sewa = $model->getSewa();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $jns_byr   = $_POST['jns_byr']   ?? '';
      $mtode_byr = $_POST['mtode_byr'] ?? '';

      if (!in_array($jns_byr, ['dp', 'pelunasan'])) {
        $pesan = 'Jenis pembayaran tidak valid!';
        $tipe_pesan = 'danger';
        header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=tambah&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      }

      if (!in_array($mtode_byr, ['tunai', 'transfer'])) {
        $pesan = 'Metode pembayaran tidak valid!';
        $tipe_pesan = 'danger';
        header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=tambah&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      }

      $data = [
        'kode_bayar'  => $model->generateKodeBayar(),
        'tgl_wkt_byr' => date('Y-m-d H:i:s'),
        'mtode_byr'   => $mtode_byr,
        'jns_byr'     => $jns_byr,
        'nominal'     => (int)$_POST['nominal'],
        'sttus_byr'   => $_POST['sttus_byr'] ?? 'pending',
        'kode_sewa'   => $_POST['kode_sewa']
      ];

      $data['tgl_wkt_ver'] = ($data['sttus_byr'] !== 'pending') ? date('Y-m-d H:i:s') : null;

      if ($mtode_byr === 'transfer') {
        $ft_byr = uploadBukti('ft_byr');
        if ($ft_byr) {
          $data['ft_byr'] = $ft_byr;
        } else {
          $pesan = 'Pembayaran transfer WAJIB menyertakan bukti!';
          $tipe_pesan = 'danger';
          header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=tambah&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
          exit;
        }
      } else {
        $data['ft_byr'] = null;
      }

      if ($model->tambah($data)) {
        // [TAMBAHAN] Trigger state transition jika pembayaran langsung valid (misal: walk-in tunai)
        $model->prosesValidasiPembayaran($data['kode_sewa']);
        $pesan = 'Data pembayaran berhasil ditambahkan!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal menambahkan data pembayaran!';
        $tipe_pesan = 'danger';
      }
    }
    break;

  case 'ubah':
    $kode_bayar_lama = $_GET['kode_bayar'] ?? '';
    $bayar_dipilih   = $model->getByKode($kode_bayar_lama);
    $bayar_dipilih   = !empty($bayar_dipilih) ? $bayar_dipilih[0] : null;
    $data_sewa       = $model->getSewa();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $jns_byr = $_POST['jns_byr'];
      $mtode_byr = $_POST['mtode_byr'];

      $data = [
        'mtode_byr'   => $mtode_byr,
        'jns_byr'     => $jns_byr,
        'nominal'     => $_POST['nominal'],
        'sttus_byr'   => $_POST['sttus_byr'],
        'kode_sewa'   => $_POST['kode_sewa']
      ];

      if ($data['sttus_byr'] !== 'pending') {
        if ($bayar_dipilih['sttus_byr'] === 'pending') {
          $data['tgl_wkt_ver'] = date('Y-m-d H:i:s');
        } else {
          $data['tgl_wkt_ver'] = $bayar_dipilih['tgl_wkt_ver'];
        }
      } else {
        $data['tgl_wkt_ver'] = null;
      }

      if ($mtode_byr === 'transfer') {
        if (!empty($_FILES['ft_byr']['name'])) {
          if (!empty($bayar_dipilih['ft_byr'])) {
            hapusBukti($bayar_dipilih['ft_byr']);
          }
          $ft_byr_baru = uploadBukti('ft_byr');
          if ($ft_byr_baru) {
            $data['ft_byr'] = $ft_byr_baru;
          }
        }
      } else {
        if (!empty($bayar_dipilih['ft_byr'])) {
          hapusBukti($bayar_dipilih['ft_byr']);
        }
        $data['ft_byr'] = null;
      }

      if ($model->ubah($data, $kode_bayar_lama)) {
        // [TAMBAHAN] Trigger state transition jika admin mengedit status menjadi valid
        $model->prosesValidasiPembayaran($data['kode_sewa']);
        $pesan = 'Data pembayaran berhasil diubah!';
        $tipe_pesan = 'success';
        header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
        exit;
      } else {
        $pesan = 'Gagal mengubah data pembayaran!';
        $tipe_pesan = 'danger';
      }
    }
    break;

  case 'hapus':
    $kode_bayar = $_GET['kode_bayar'] ?? '';
    if (!empty($kode_bayar)) {
      $bayar = $model->getByKode($kode_bayar);
      $bayar = !empty($bayar) ? $bayar[0] : null;
      if ($model->hapus($kode_bayar)) {
        if ($bayar && !empty($bayar['ft_byr'])) {
          hapusBukti($bayar['ft_byr']);
        }
        $pesan = 'Data pembayaran berhasil dihapus!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal menghapus data pembayaran!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  case 'verifikasi':
    $kode_bayar = $_GET['kode_bayar'] ?? '';
    $status     = $_GET['status'] ?? '';
    if (!empty($kode_bayar) && in_array($status, ['valid', 'invalid'])) {
      // [TAMBAHAN] Ambil kode_sewa dari data bayar
      $data_bayar = $model->getByKode($kode_bayar);
      $kode_sewa  = !empty($data_bayar) ? $data_bayar[0]['kode_sewa'] : '';

      if ($model->updateStatus($kode_bayar, $status)) {
        // [TAMBAHAN] Jika divalidasi, trigger state transition
        if ($status === 'valid' && !empty($kode_sewa)) {
          $model->prosesValidasiPembayaran($kode_sewa);
        }
        $pesan = 'Status pembayaran berhasil diverifikasi!';
        $tipe_pesan = 'success';
      } else {
        $pesan = 'Gagal memverifikasi pembayaran!';
        $tipe_pesan = 'danger';
      }
    }
    header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
    exit;

  case 'cetak':
    // Cetak bukti pembayaran berdasarkan kode_sewa
    $kode_sewa = $_GET['kode_sewa'] ?? '';
    if (empty($kode_sewa)) {
      $pesan = 'Kode sewa tidak valid!';
      $tipe_pesan = 'danger';
      header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
      exit;
    }

    // Cek apakah bisa dicetak
    $bisa_cetak = $model->cekBisaCetak($kode_sewa);
    if (!$bisa_cetak) {
      $pesan = 'Pembayaran belum lunas atau mobil belum dikembalikan! Tidak bisa cetak bukti.';
      $tipe_pesan = 'danger';
      header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
      exit;
    }

    $aksi = 'cetak';

    // Ambil data sewa
    $data_sewa = $model->getSewa();
    $penyewa_all = $model->getPenyewa();
    $mobil_all = $model->getMobil();
    $map_penyewa = [];
    foreach ($penyewa_all as $p) $map_penyewa[$p['user_plg']] = $p;
    $map_mobil = [];
    foreach ($mobil_all as $m) $map_mobil[$m['no_plat']] = $m;

    $sewa_data = null;
    foreach ($data_sewa as $s) {
      if ($s['kode_sewa'] === $kode_sewa) {
        $sewa_data = $s;
        $sewa_data['nm_lngkp'] = $map_penyewa[$s['user_plg']]['nm_lngkp'] ?? '-';
        $sewa_data['nik'] = $map_penyewa[$s['user_plg']]['nik'] ?? '-';
        $sewa_data['no_hp'] = $map_penyewa[$s['user_plg']]['no_hp'] ?? '-';
        $sewa_data['almt_lngkp'] = $map_penyewa[$s['user_plg']]['almt_lngkp'] ?? '-';
        $sewa_data['user_plg'] = $s['user_plg'];
        break;
      }
    }

    // Ambil data admin yang login
    $admin_username = $_SESSION['admin_username'] ?? 'admin';

    // Ambil list pembayaran
    $bayar_list = $model->getByKodeSewa($kode_sewa);

    // Pisahkan DP dan Pelunasan yang valid
    $dp_list = [];
    $pelunasan_list = [];
    $total_dp = 0;
    $total_pelunasan = 0;
    foreach ($bayar_list as $bl) {
      if ($bl['sttus_byr'] === 'valid') {
        if ($bl['jns_byr'] === 'dp') {
          $dp_list[] = $bl;
          $total_dp += $bl['nominal'];
        } elseif ($bl['jns_byr'] === 'pelunasan') {
          $pelunasan_list[] = $bl;
          $total_pelunasan += $bl['nominal'];
        }
      }
    }

    // Detail mobil
    $detail_mobil = [];
    $total_sewa = 0;
    $total_n_hari = 0;
    $pilih_list = $model->getPilihByKode($kode_sewa);
    foreach ($pilih_list as $pl) {
      $m = $map_mobil[$pl['no_plat']] ?? null;
      $subtotal = $pl['hrg_sewa'] * $pl['n_hari'];
      $detail_mobil[] = [
        'nm_mobil' => $m ? $m['nm_mobil'] : $pl['no_plat'],
        'no_plat'  => $pl['no_plat'],
        'hrg_sewa' => $pl['hrg_sewa'],
        'n_hari'   => $pl['n_hari'],
        'subtotal' => $subtotal
      ];
      $total_sewa += $subtotal;
      //$total_n_hari += $pl['n_hari'];
      $total_n_hari = $pl['n_hari'];
    }

    // Info pengembalian & denda
    $info_kembali = $model->getInfoPengembalian($kode_sewa);
    $denda = $info_kembali['denda'] ?? 0;
    $telat_jam = $info_kembali['telat_jam'] ?? 0;
    $tarif_per_jam = $info_kembali['tarif_per_jam'] ?? 0;

    // Total pembayaran (DP + Pelunasan + Denda)
    $total_pembayaran = $total_dp + $total_pelunasan + $denda;

    // ✅ Ambil kode denda untuk menentukan tanggal tanda tangan
    $kode_denda = $model->getKodeDendaTerakhir($kode_sewa);

    // Status pembayaran
    #$status_pembayaran = ($total_dp + $total_pelunasan >= $total_sewa + $denda) ? 'LUNAS' : 'BELUM LUNAS';

    $status_pembayaran = ($total_dp + $total_pelunasan >= $total_sewa) ? 'LUNAS' : 'BELUM LUNAS';

    // Format hari Indonesia
    $hari_indo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan_indo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $tgl_mulai_ts = strtotime($sewa_data['tgl_mulai']);
    $tgl_selesai_ts = strtotime($sewa_data['tgl_selesai']);

    $hari_mulai = $hari_indo[date('w', $tgl_mulai_ts)];
    $tgl_mulai_fmt = date('d', $tgl_mulai_ts) . ' ' . $bulan_indo[date('n', $tgl_mulai_ts) - 1] . ' ' . date('Y', $tgl_mulai_ts);

    $hari_selesai = $hari_indo[date('w', $tgl_selesai_ts)];
    $tgl_selesai_fmt = date('d', $tgl_selesai_ts) . ' ' . $bulan_indo[date('n', $tgl_selesai_ts) - 1] . ' ' . date('Y', $tgl_selesai_ts);

    // Ambil waktu pengembalian dari t_kembali (jika ada)
    // Ambil waktu pengembalian dari t_kembali
    $tgl_wkt_blk = $info_kembali['tgl_wkt_blk'] ?? null;

    // Validasi: jika tombol cetak muncul, maka tgl_wkt_blk HARUS ada
    if (!$tgl_wkt_blk) {
      $pesan = 'Data pengembalian tidak ditemukan! Hubungi administrator.';
      $tipe_pesan = 'danger';
      header('Location: ' . BASE_URL . 'admin/controller/tpembayaran_controller.php?aksi=index&pesan=' . urlencode($pesan) . '&tipe=' . $tipe_pesan);
      exit;
    }

    // Format tanggal dan waktu pengembalian dari database
    $blk_ts = strtotime($tgl_wkt_blk);
    $hari_blk = $hari_indo[date('w', $blk_ts)];
    $tgl_blk_fmt = date('d', $blk_ts) . ' ' . $bulan_indo[date('n', $blk_ts) - 1] . ' ' . date('Y', $blk_ts);
    $wkt_blk_fmt = date('H:i', $blk_ts);  // ✅ Baca dari database

    // Ambil tanggal dari kode denda (format: FINE/YYYYMMDD/9)
    $tgl_ttd = date('d/m/Y');
    if ($kode_denda) {
      // Ekstrak tanggal dari kode denda
      $parts = explode('/', $kode_denda);
      if (isset($parts[1]) && strlen($parts[1]) === 8) {
        $tgl_denda = substr($parts[1], 6, 2) . '/' .
          substr($parts[1], 4, 2) . '/' .
          substr($parts[1], 0, 4);
        $tgl_ttd = $tgl_denda;
      }
    } elseif ($tgl_wkt_blk) {
      // Jika tidak ada denda, gunakan tanggal pengembalian
      $tgl_ttd = date('d/m/Y', strtotime($tgl_wkt_blk));
    }

    // Konversi $tgl_ttd dari format 27/06/2026 menjadi 27 Juni 2026
    $tgl_parts = explode('/', $tgl_ttd);
    $tgl_ttd = $tgl_parts[0] . ' ' . $bulan_indo[(int)$tgl_parts[1] - 1] . ' ' . $tgl_parts[2];

    $data_untuk_view = [
      'judul_halaman'      => 'Cetak Bukti Pembayaran',
      'nama_admin'         => $admin_username,
      'aksi'               => $aksi,
      'sewa_data'          => $sewa_data,
      'detail_mobil'       => $detail_mobil,
      'dp_list'            => $dp_list,
      'pelunasan_list'     => $pelunasan_list,
      'total_sewa'         => $total_sewa,
      'total_dp'           => $total_dp,
      'total_pelunasan'    => $total_pelunasan,
      'denda'              => $denda,
      'telat_jam'          => $telat_jam,
      'tarif_per_jam'      => $tarif_per_jam,
      'total_pembayaran'   => $total_pembayaran,
      'status_pembayaran'  => $status_pembayaran,
      'hari_mulai'         => $hari_mulai,
      'tgl_mulai_fmt'      => $tgl_mulai_fmt,
      'wkt_ambil'          => $sewa_data['wkt_ambil'],
      'hari_selesai'       => $hari_selesai,
      'tgl_selesai_fmt'    => $tgl_selesai_fmt,
      'hari_blk'           => $hari_blk,
      'tgl_blk_fmt'        => $tgl_blk_fmt,
      'wkt_blk_fmt'        => $wkt_blk_fmt,
      'total_n_hari'       => $total_n_hari,
      'tgl_ttd'            => $tgl_ttd,
      'admin_username'     => $admin_username
    ];

    extract($data_untuk_view);
    require_once __DIR__ . '/../view/tpembayaran_view.php';
    exit;

  default:
    $data_bayar = $model->getAllWithDetail();
    break;
}

if (isset($_GET['pesan'])) {
  $pesan      = $_GET['pesan'];
  $tipe_pesan = $_GET['tipe'] ?? 'info';
}

// Ambil detail sewa untuk dropdown
$data_sewa_detail = [];
$sewa_all    = $model->getSewa();
$penyewa_all = $model->getPenyewa();
$mobil_all   = $model->getMobil();
$map_penyewa = [];
foreach ($penyewa_all as $p) $map_penyewa[$p['user_plg']] = $p;

foreach ($sewa_all as $s) {
  // ✅ SKIP sewa berstatus batal saat mode tambah
  if ($aksi === 'tambah' && $s['status_sewa'] === 'batal') {
    continue;
  }

  $pilih_list = $model->getPilihByKode($s['kode_sewa']);
  $total = 0;
  $nama_mobil = [];
  foreach ($pilih_list as $pl) {
    $total += ($pl['hrg_sewa'] * $pl['n_hari']);
    foreach ($mobil_all as $m) {
      if ($m['no_plat'] == $pl['no_plat']) {
        $nama_mobil[] = $m['nm_mobil'];
        break;
      }
    }
  }
  $p = $map_penyewa[$s['user_plg']] ?? null;
  $total_dp_paid = $model->getTotalDPPaid($s['kode_sewa']);
  $total_bayar_valid = $model->getTotalBayarValid($s['kode_sewa']);
  $sisa_tagihan = $total - $total_dp_paid;
  $is_lunas = ($total_bayar_valid >= $total) ? true : false;
  $data_sewa_detail[$s['kode_sewa']] = [
    'kode_sewa'          => $s['kode_sewa'],
    'nm_lngkp'           => $p ? $p['nm_lngkp'] : '-',
    'total_tagihan'      => $total,
    'nama_mobil'         => implode(', ', $nama_mobil),
    'total_dp_paid'      => $total_dp_paid,
    'total_bayar_valid'  => $total_bayar_valid,
    'sisa_tagihan'       => $sisa_tagihan,
    'is_lunas'           => $is_lunas,
    'status_sewa'        => $s['status_sewa'] // ✅ Tambahkan untuk referensi
  ];
}

$data_untuk_view = [
  'judul_halaman'    => 'Data Pembayaran',
  'nama_admin'       => $_SESSION['admin_username'],
  'data_bayar'       => $data_bayar ?? [],
  'data_sewa'        => $model->getSewa(),
  'data_sewa_detail' => $data_sewa_detail,
  'aksi'             => $aksi,
  'bayar_dipilih'    => $bayar_dipilih ?? null,
  'pesan'            => $pesan,
  'tipe_pesan'       => $tipe_pesan,
  // Untuk halaman cetak
  'sewa_data'        => $sewa_data ?? null,
  'detail_mobil'     => $detail_mobil ?? [],
  'bayar_list'       => $bayar_list ?? [],
  'total_sewa'       => $total_sewa ?? 0,
  'total_bayar_valid' => $total_bayar_valid ?? 0,
  'info_kembali'     => $info_kembali ?? null
];

extract($data_untuk_view);
require_once __DIR__ . '/../view/tpembayaran_view.php';
