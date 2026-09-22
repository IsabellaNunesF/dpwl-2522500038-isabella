<?php
// admin/model/tdenda_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class TDenda
{
  private $conn;
  private $tabel_denda   = 't_denda';
  private $tabel_kembali = 't_kembali';
  private $tabel_sewa    = 't_sewa';
  private $tabel_penyewa = 't_penyewa';
  private $tabel_bayar   = 't_bayar';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  public function getPenyewa()
  {
    return $this->conn->select($this->tabel_penyewa);
  }

  // ✅ PERBAIKAN: Method baru untuk ambil data kembali + user_plg
  public function getKembaliTerlambat()
  {
    $sql = "SELECT 
                    k.kode_kembali,
                    k.tgl_wkt_blk,
                    k.stts_blk,
                    k.ket_blk,
                    k.kode_bayar,
                    b.kode_sewa,
                    s.user_plg
                FROM t_kembali k
                INNER JOIN t_bayar b ON k.kode_bayar = b.kode_bayar
                INNER JOIN t_sewa s ON b.kode_sewa = s.kode_sewa
                WHERE k.stts_blk = 'terlambat'
                ORDER BY k.tgl_wkt_blk DESC";

    $stmt = $this->conn->query($sql);
    return $stmt->fetchAll();
  }

  public function getDendaByKodeKembali($kode_kembali)
  {
    return $this->conn->select($this->tabel_denda, ['kode_kembali' => $kode_kembali]);
  }

  public function getSewaByKodeBayar($kode_bayar)
  {
    $bayar = $this->conn->select($this->tabel_bayar, ['kode_bayar' => $kode_bayar]);
    if (!empty($bayar)) {
      $sewa = $this->conn->select($this->tabel_sewa, ['kode_sewa' => $bayar[0]['kode_sewa']]);
      return !empty($sewa) ? $sewa[0] : null;
    }
    return null;
  }

  // ✅ BARU: Ambil data penyewa by user_plg
  public function getPenyewaByUser($user_plg)
  {
    $result = $this->conn->select($this->tabel_penyewa, ['user_plg' => $user_plg]);
    return !empty($result) ? $result[0] : null;
  }

  public function generateKodeDenda()
  {
    $tanggal = date('Ymd');
    $prefix = "FINE/$tanggal/";
    $all_denda = $this->conn->select($this->tabel_denda);
    $max_num = 0;

    foreach ($all_denda as $d) {
      if (strpos($d['kode_denda'], $prefix) === 0) {
        $num = (int)substr($d['kode_denda'], strlen($prefix));
        if ($num > $max_num) $max_num = $num;
      }
    }

    $next_num = str_pad($max_num + 1, 1, '0', STR_PAD_LEFT);
    return $prefix . $next_num;
  }

  public function prosesDenda($data)
  {
    return $this->conn->insert($this->tabel_denda, [
      'kode_denda'    => $data['kode_denda'],
      'telat_jam'     => $data['telat_jam'],
      'tarif_per_jam' => $data['tarif_per_jam'],
      'kode_kembali'  => $data['kode_kembali']
    ]);
  }
}
