<?php
class RiwayatModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Ambil riwayat sewa dengan grouping per kode_sewa
  public function getRiwayatSewa($user_plg, $filter_status = 'semua')
  {
    $where = "s.user_plg = ? AND s.status_sewa != 'keranjang'";
    $params = [$user_plg];

    // Logika Filter Tab berdasarkan state transition
    if ($filter_status === 'diajukan') {
      $where .= " AND s.status_sewa = 'diajukan' AND s.kode_sewa NOT IN (SELECT kode_sewa FROM t_bayar)";
    } elseif ($filter_status === 'menunggu_pembayaran') {
      $where .= " AND s.status_sewa = 'diajukan' AND s.kode_sewa IN (SELECT kode_sewa FROM t_bayar)";
    } elseif ($filter_status === 'disetujui') {
      $where .= " AND s.status_sewa = 'disetujui'";
    } elseif ($filter_status === 'aktif') {
      $where .= " AND s.status_sewa = 'aktif'";
    } elseif ($filter_status === 'selesai') {
      $where .= " AND s.status_sewa = 'selesai'";
    } elseif ($filter_status === 'batal') {
      $where .= " AND s.status_sewa = 'batal'";
    } elseif ($filter_status === 'ditolak') {
      $where .= " AND s.status_sewa = 'ditolak'";
    }

    $sql = "SELECT
                    s.kode_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil, s.status_sewa,
                    p.no_plat, p.hrg_sewa, p.n_hari,
                    m.nm_mobil, m.jns_mobil, m.ft_depan, m.ft_blkg, m.ft_interior,
                    b.kode_bayar, b.nominal, b.sttus_byr, b.mtode_byr, b.jns_byr,
                    k.kode_kembali, k.stts_blk, k.tgl_wkt_blk,
                    d.kode_denda, d.telat_jam, d.tarif_per_jam
                FROM t_sewa s
                LEFT JOIN t_pilih p ON s.kode_sewa = p.kode_sewa
                LEFT JOIN t_mobil m ON p.no_plat = m.no_plat
                LEFT JOIN t_bayar b ON s.kode_sewa = b.kode_sewa
                LEFT JOIN t_kembali k ON b.kode_bayar = k.kode_bayar
                LEFT JOIN t_denda d ON k.kode_kembali = d.kode_kembali
                WHERE $where
                ORDER BY s.tgl_mulai DESC, s.kode_sewa DESC, p.no_plat";

    $stmt = $this->conn->query($sql, $params);
    $rows = $stmt->fetchAll();

    // Grouping data berdasarkan kode_sewa
    $grouped = [];
    foreach ($rows as $row) {
      $kode = $row['kode_sewa'];
      if (!isset($grouped[$kode])) {
        $grouped[$kode] = [
          'kode_sewa'       => $row['kode_sewa'],
          'tgl_mulai'       => $row['tgl_mulai'],
          'tgl_selesai'     => $row['tgl_selesai'],
          'wkt_ambil'       => $row['wkt_ambil'],
          'status_sewa'     => $row['status_sewa'],
          'mobil'           => [],
          'pembayaran'      => [],
          'pengembalian'    => null,
          'denda'           => null,
          'total_harga_sewa' => 0,
          'total_sudah_bayar' => 0,
        ];
      }

      // Kumpulkan data mobil (hindari duplikasi)
      $plat = $row['no_plat'];
      if ($plat && !isset($grouped[$kode]['mobil'][$plat])) {
        $grouped[$kode]['mobil'][$plat] = [
          'no_plat'    => $plat,
          'nm_mobil'   => $row['nm_mobil'],
          'jns_mobil'  => $row['jns_mobil'],
          'hrg_sewa'   => $row['hrg_sewa'],
          'n_hari'     => $row['n_hari'],
          'ft_depan'   => $row['ft_depan'],
          'ft_blkg'    => $row['ft_blkg'],
          'ft_interior' => $row['ft_interior'],
        ];
        $grouped[$kode]['total_harga_sewa'] += ($row['hrg_sewa'] * $row['n_hari']);
      }

      // Kumpulkan data pembayaran (hindari duplikasi)
      $kode_bayar = $row['kode_bayar'];
      if ($kode_bayar && !isset($grouped[$kode]['pembayaran'][$kode_bayar])) {
        $grouped[$kode]['pembayaran'][$kode_bayar] = [
          'kode_bayar' => $kode_bayar,
          'nominal'    => $row['nominal'],
          'sttus_byr'  => $row['sttus_byr'],
          'mtode_byr'  => $row['mtode_byr'],
          'jns_byr'    => $row['jns_byr'],
        ];
        if ($row['sttus_byr'] === 'valid') {
          $grouped[$kode]['total_sudah_bayar'] += $row['nominal'];
        }
      }

      // Data pengembalian & denda (ambil yang pertama saja)
      if ($row['kode_kembali'] && !$grouped[$kode]['pengembalian']) {
        $grouped[$kode]['pengembalian'] = [
          'kode_kembali' => $row['kode_kembali'],
          'stts_blk'     => $row['stts_blk'],
          'tgl_wkt_blk'  => $row['tgl_wkt_blk'],
        ];
      }

      if ($row['kode_denda'] && !$grouped[$kode]['denda']) {
        $grouped[$kode]['denda'] = [
          'kode_denda'    => $row['kode_denda'],
          'telat_jam'     => $row['telat_jam'],
          'tarif_per_jam' => $row['tarif_per_jam'],
        ];
      }
    }

    // Hitung total tagihan, denda, dan sisa bayar
    // foreach ($grouped as &$g) {
    //   $g['total_denda'] = $g['denda'] ? ($g['denda']['telat_jam'] * $g['denda']['tarif_per_jam']) : 0;
    //   $g['total_tagihan'] = $g['total_harga_sewa'] + $g['total_denda'];
    //   $g['sisa_tagihan'] = $g['total_tagihan'] - $g['total_sudah_bayar'];
    //   $g['status_pembayaran'] = $g['sisa_tagihan'] <= 0 ? 'Lunas' : 'Belum Lunas';

    //   // Ubah array asosiatif menjadi indexed array agar mudah di-loop di view
    //   $g['mobil'] = array_values($g['mobil']);
    //   $g['pembayaran'] = array_values($g['pembayaran']);
    // }

    // Hitung total tagihan, denda, dan sisa bayar
    foreach ($grouped as &$g) {
      $g['total_denda'] = $g['denda'] ? ($g['denda']['telat_jam'] * $g['denda']['tarif_per_jam']) : 0;
      // TOTAL TAGIHAN SEWA (tanpa denda) - untuk menentukan status pembayaran
      $g['total_tagihan_sewa'] = $g['total_harga_sewa'];
      // TOTAL TAGIHAN FINAL (dengan denda) - untuk ditampilkan di UI
      $g['total_tagihan'] = $g['total_harga_sewa'] + $g['total_denda'];
      // SISA TAGIHAN (hanya menghitung sewa, tanpa denda)
      $g['sisa_tagihan'] = $g['total_tagihan_sewa'] - $g['total_sudah_bayar'];
      // STATUS PEMBAYARAN (hanya berdasarkan pembayaran sewa)
      $g['status_pembayaran'] = $g['sisa_tagihan'] <= 0 ? 'Lunas' : 'Belum Lunas';
      // Ubah array asosiatif menjadi indexed array agar mudah di-loop di view
      $g['mobil'] = array_values($g['mobil']);
      $g['pembayaran'] = array_values($g['pembayaran']);
    }

    return array_values($grouped);
  }

  // Hitung total sewa (transaksi unik)
  public function getTotalSewa($user_plg)
  {
    $sql = "SELECT COUNT(DISTINCT kode_sewa) as total FROM t_sewa WHERE user_plg = ? AND status_sewa != 'keranjang'";
    $stmt = $this->conn->query($sql, [$user_plg]);
    return (int) $stmt->fetch()['total'];
  }

  // Hitung total pengeluaran (hanya yang valid/lunas)
  public function getTotalPengeluaran($user_plg)
  {
    $sql = "SELECT COALESCE(SUM(b.nominal), 0) as total
                FROM t_bayar b
                JOIN t_sewa s ON b.kode_sewa = s.kode_sewa
                WHERE s.user_plg = ? AND b.sttus_byr = 'valid'";
    $stmt = $this->conn->query($sql, [$user_plg]);
    return (int) $stmt->fetch()['total'];
  }
}
