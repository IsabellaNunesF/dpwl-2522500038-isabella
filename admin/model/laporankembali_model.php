<?php
// admin/model/laporankembali_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class LaporanKembali
{
  private $conn;

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  /**
   * Mengambil laporan pengembalian mobil berdasarkan filter tanggal
   */
  public function getLaporan($filter = [])
  {
    // 1. Ambil semua data dari tabel terkait
    $kembali = $this->conn->select('t_kembali');
    $bayar   = $this->conn->select('t_bayar');
    $sewa    = $this->conn->select('t_sewa');
    $penyewa = $this->conn->select('t_penyewa');
    $pilih   = $this->conn->select('t_pilih');
    $mobil   = $this->conn->select('t_mobil');
    $denda   = $this->conn->select('t_denda');

    // 2. Mapping data ke array asosiatif untuk pencarian cepat
    $map_bayar = [];
    foreach ($bayar as $b) $map_bayar[$b['kode_bayar']] = $b;

    $map_sewa = [];
    foreach ($sewa as $s) $map_sewa[$s['kode_sewa']] = $s;

    $map_penyewa = [];
    foreach ($penyewa as $p) $map_penyewa[$p['user_plg']] = $p;

    $map_pilih = [];
    foreach ($pilih as $pl) {
      $map_pilih[$pl['kode_sewa']][] = $pl;
    }

    $map_mobil = [];
    foreach ($mobil as $m) $map_mobil[$m['no_plat']] = $m;

    $map_denda = [];
    foreach ($denda as $d) $map_denda[$d['kode_kembali']] = $d;

    // 3. Inisialisasi Rekapitulasi per Mobil
    $rekap_mobil = [];
    foreach ($mobil as $m) {
      $rekap_mobil[$m['no_plat']] = [
        'no_plat'            => $m['no_plat'],
        'nm_mobil'           => $m['nm_mobil'],
        'jns_mobil'          => $m['jns_mobil'],
        'total_dikembalikan' => 0,
        'total_denda'        => 0,
        'status_kondisi'     => [
          'normal'       => 0,
          'terlambat'    => 0
        ]
      ];
    }

    // 4. Proses Filtering dan Penggabungan Data
    $hasil = [];
    $total_pengembalian = 0;
    $total_denda = 0;
    $total_terlambat = 0;

    foreach ($kembali as $k) {
      // Filter Tanggal (berdasarkan tgl_wkt_blk)
      $tgl_blk = date('Y-m-d', strtotime($k['tgl_wkt_blk']));
      if (!empty($filter['tgl_mulai']) && $tgl_blk < $filter['tgl_mulai']) continue;
      if (!empty($filter['tgl_selesai']) && $tgl_blk > $filter['tgl_selesai']) continue;

      // Gabungkan dengan data Bayar -> Sewa -> Penyewa
      $b = $map_bayar[$k['kode_bayar']] ?? null;
      $s = $b ? ($map_sewa[$b['kode_sewa']] ?? null) : null;
      $p = $s ? ($map_penyewa[$s['user_plg']] ?? null) : null;

      // Gabungkan dengan data Denda
      $d = $map_denda[$k['kode_kembali']] ?? null;

      // Gabungkan dengan data Pilih dan Mobil
      $detail_mobil = [];
      $no_plat_list = [];
      $total_harga_sewa = 0;
      $total_hari = 0;

      if ($s && isset($map_pilih[$s['kode_sewa']])) {
        foreach ($map_pilih[$s['kode_sewa']] as $pl) {
          $m = $map_mobil[$pl['no_plat']] ?? null;
          if ($m) {
            $detail_mobil[] = $m['nm_mobil'] . ' (' . $m['no_plat'] . ')';
            $no_plat_list[] = $pl['no_plat'];
            $total_harga_sewa += $pl['hrg_sewa'];
            $total_hari = max($total_hari, $pl['n_hari']);
          }
        }
      }

      // Hitung denda
      $telat_jam = $d ? $d['telat_jam'] : 0;
      $tarif_per_jam = $d ? $d['tarif_per_jam'] : 0;
      $nominal_denda = $telat_jam * $tarif_per_jam;

      // Susun data hasil akhir untuk tabel detail
      $hasil[] = [
        'kode_kembali'     => $k['kode_kembali'],
        'tgl_wkt_blk'      => $k['tgl_wkt_blk'],
        'stts_blk'         => $k['stts_blk'],
        'ket_blk'          => $k['ket_blk'],
        'kode_bayar'       => $k['kode_bayar'],
        'kode_sewa'        => $s ? $s['kode_sewa'] : '-',
        'tgl_mulai'        => $s ? $s['tgl_mulai'] : '-',
        'tgl_selesai'      => $s ? $s['tgl_selesai'] : '-',
        'nm_lngkp'         => $p ? $p['nm_lngkp'] : '-',
        'nik'              => $p ? $p['nik'] : '-',
        'no_hp'            => $p ? $p['no_hp'] : '-',
        'detail_mobil'     => implode(', ', $detail_mobil) ?: '-',
        'total_harga_sewa' => $total_harga_sewa,
        'total_hari'       => $total_hari,
        'telat_jam'        => $telat_jam,
        'tarif_per_jam'    => $tarif_per_jam,
        'total_denda'      => $nominal_denda,
        'no_plat_list'     => $no_plat_list
      ];

      // Update Statistik Global
      $total_pengembalian++;
      $total_denda += $nominal_denda;
      if ($k['stts_blk'] === 'terlambat') $total_terlambat++;
      
      // Update Rekap per Mobil
      foreach ($no_plat_list as $plat) {
        if (isset($rekap_mobil[$plat])) {
          $rekap_mobil[$plat]['total_dikembalikan']++;
          $rekap_mobil[$plat]['total_denda'] += $nominal_denda;
          $stts = $k['stts_blk'];
          if (isset($rekap_mobil[$plat]['status_kondisi'][$stts])) {
            $rekap_mobil[$plat]['status_kondisi'][$stts]++;
          }
        }
      }
    }

    return [
      'data'               => $hasil,
      'rekap_mobil'        => $rekap_mobil,
      'total_pengembalian' => $total_pengembalian,
      'total_denda'        => $total_denda,
      'total_terlambat'    => $total_terlambat
    ];
  }
}
