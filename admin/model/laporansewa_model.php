<?php
// admin/model/laporansewa_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class LaporanSewa
{
  private $conn;

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  /**
   * Mengambil laporan pendapatan sewa berdasarkan filter
   */
  public function getLaporan($filter = [])
  {
    // 1. Ambil SEMUA pembayaran yang berstatus VALID
    $all_bayar = $this->conn->select('t_bayar', ['sttus_byr' => 'valid']);

    // 2. Ambil data dari tabel relasi
    $sewa    = $this->conn->select('t_sewa');
    $penyewa = $this->conn->select('t_penyewa');
    $pilih   = $this->conn->select('t_pilih');
    $mobil   = $this->conn->select('t_mobil');

    // 3. Mapping data ke dalam array asosiatif untuk pencarian cepat (O(1))
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

    // 4. Proses Filtering dan Penggabungan Data
    $hasil = [];
    $total_pendapatan = 0;

    foreach ($all_bayar as $b) {
      // Filter Tanggal (berdasarkan tgl_wkt_byr)
      $tgl_bayar = date('Y-m-d', strtotime($b['tgl_wkt_byr']));
      if (!empty($filter['tgl_mulai']) && $tgl_bayar < $filter['tgl_mulai']) continue;
      if (!empty($filter['tgl_selesai']) && $tgl_bayar > $filter['tgl_selesai']) continue;

      // Filter Metode Pembayaran
      if (!empty($filter['mtode_byr']) && $b['mtode_byr'] !== $filter['mtode_byr']) continue;

      // Filter Jenis Pembayaran
      if (!empty($filter['jns_byr']) && $b['jns_byr'] !== $filter['jns_byr']) continue;

      // Gabungkan dengan data Sewa
      $s = $map_sewa[$b['kode_sewa']] ?? null;

      // Gabungkan dengan data Penyewa
      $p = ($s && isset($map_penyewa[$s['user_plg']])) ? $map_penyewa[$s['user_plg']] : null;

      // Gabungkan dengan data Pilih dan Mobil
      $detail_mobil = [];
      if ($s && isset($map_pilih[$s['kode_sewa']])) {
        foreach ($map_pilih[$s['kode_sewa']] as $pl) {
          $m = $map_mobil[$pl['no_plat']] ?? null;
          if ($m) {
            $detail_mobil[] = $m['nm_mobil'] . ' (' . $m['no_plat'] . ')';
          }
        }
      }

      // Susun data hasil akhir
      $hasil[] = [
        'kode_bayar'    => $b['kode_bayar'],
        'tgl_wkt_byr'   => $b['tgl_wkt_byr'],
        'kode_sewa'     => $b['kode_sewa'],
        'nm_lngkp'      => $p ? $p['nm_lngkp'] : '-',
        'nik'           => $p ? $p['nik'] : '-',
        'no_hp'         => $p ? $p['no_hp'] : '-',
        'detail_mobil'  => implode(', ', $detail_mobil) ?: '-',
        'tgl_mulai'     => $s ? $s['tgl_mulai'] : null,
        'tgl_selesai'   => $s ? $s['tgl_selesai'] : null,
        'mtode_byr'     => $b['mtode_byr'],
        'jns_byr'       => $b['jns_byr'],
        'nominal'       => $b['nominal']
      ];

      $total_pendapatan += $b['nominal'];
    }

    return [
      'data'             => $hasil,
      'total'            => $total_pendapatan,
      'jumlah_transaksi' => count($hasil)
    ];
  }
}
