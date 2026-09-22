<?php
// admin/model/laporandenda_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class LaporanDenda
{
  private $conn;

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  /**
   * Mengambil laporan pendapatan denda berdasarkan filter tanggal pengembalian
   */
  public function getLaporan($filter = [])
  {
    // 1. Ambil SEMUA denda
    $all_denda = $this->conn->select('t_denda');

    // 2. Ambil data dari tabel relasi
    $kembali = $this->conn->select('t_kembali');
    $bayar   = $this->conn->select('t_bayar');
    $sewa    = $this->conn->select('t_sewa');
    $penyewa = $this->conn->select('t_penyewa');
    $pilih   = $this->conn->select('t_pilih');
    $mobil   = $this->conn->select('t_mobil');

    // 3. Mapping data ke dalam array asosiatif untuk pencarian cepat (O(1))
    $map_kembali = [];
    foreach ($kembali as $k) $map_kembali[$k['kode_kembali']] = $k;

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

    // 4. Proses Filtering dan Penggabungan Data
    $hasil = [];
    $total_pendapatan_denda = 0;

    foreach ($all_denda as $d) {
      // Gabungkan dengan data Kembali
      $k = $map_kembali[$d['kode_kembali']] ?? null;

      // Filter Tanggal (berdasarkan tgl_wkt_blk dari t_kembali)
      if ($k) {
        $tgl_blk = date('Y-m-d', strtotime($k['tgl_wkt_blk']));
        if (!empty($filter['tgl_mulai']) && $tgl_blk < $filter['tgl_mulai']) continue;
        if (!empty($filter['tgl_selesai']) && $tgl_blk > $filter['tgl_selesai']) continue;
      } else {
        // Jika tidak ada data kembali, skip jika ada filter tanggal
        if (!empty($filter['tgl_mulai']) || !empty($filter['tgl_selesai'])) continue;
      }

      // Gabungkan dengan data Bayar
      $b = ($k && isset($map_bayar[$k['kode_bayar']])) ? $map_bayar[$k['kode_bayar']] : null;

      // Gabungkan dengan data Sewa
      $s = ($b && isset($map_sewa[$b['kode_sewa']])) ? $map_sewa[$b['kode_sewa']] : null;

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

      // Hitung total denda
      $total_denda = $d['telat_jam'] * $d['tarif_per_jam'];

      // Susun data hasil akhir
      $hasil[] = [
        'kode_denda'    => $d['kode_denda'],
        'tgl_wkt_blk' => $k ? $k['tgl_wkt_blk'] : null,
        'ket_blk'       => $k ? $k['ket_blk'] : '-',
        'kode_kembali'  => $d['kode_kembali'],
        'kode_bayar'    => $b ? $b['kode_bayar'] : '-',
        'mtode_byr'     => $b ? $b['mtode_byr'] : '-',
        'jns_byr'       => $b ? $b['jns_byr'] : '-',
        'nominal_bayar' => $b ? $b['nominal'] : 0,
        'kode_sewa'     => $s ? $s['kode_sewa'] : '-',
        'nm_lngkp'      => $p ? $p['nm_lngkp'] : '-',
        'nik'           => $p ? $p['nik'] : '-',
        'no_hp'         => $p ? $p['no_hp'] : '-',
        'detail_mobil'  => implode(', ', $detail_mobil) ?: '-',
        'tgl_mulai'     => $s ? $s['tgl_mulai'] : null,
        'tgl_selesai'   => $s ? $s['tgl_selesai'] : null,
        'wkt_ambil'     => $s ? $s['wkt_ambil'] : null,
        'telat_jam'     => $d['telat_jam'],
        'tarif_per_jam' => $d['tarif_per_jam'],
        'total_denda'   => $total_denda
      ];

      $total_pendapatan_denda += $total_denda;
    }

    return [
      'data'             => $hasil,
      'total'            => $total_pendapatan_denda,
      'jumlah_transaksi' => count($hasil)
    ];
  }
}
