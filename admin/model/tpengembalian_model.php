<?php
// admin/model/tpengembalian_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class TPengembalian
{
  private $conn;
  private $tabel_sewa    = 't_sewa';
  private $tabel_penyewa = 't_penyewa';
  private $tabel_pilih   = 't_pilih';
  private $tabel_mobil   = 't_mobil';
  private $tabel_bayar   = 't_bayar';
  private $tabel_kembali = 't_kembali';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  public function getSewaAktif()
  {
    return $this->conn->select($this->tabel_sewa, ['status_sewa' => 'aktif']);
  }

  public function getPenyewa()
  {
    return $this->conn->select($this->tabel_penyewa);
  }

  public function getMobil()
  {
    return $this->conn->select($this->tabel_mobil);
  }

  public function getPilihByKode($kode_sewa)
  {
    return $this->conn->select($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
  }

  public function getBayarByKodeSewa($kode_sewa)
  {
    return $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
  }

  public function generateKodeKembali()
  {
    $tanggal = date('Ymd');
    $prefix = "RET/$tanggal/";

    $all_kembali = $this->conn->select($this->tabel_kembali);
    $max_num = 0;
    foreach ($all_kembali as $k) {
      if (strpos($k['kode_kembali'], $prefix) === 0) {
        $num = (int)substr($k['kode_kembali'], strlen($prefix));
        if ($num > $max_num) $max_num = $num;
      }
    }
    $next_num = str_pad($max_num + 1, 2, '0', STR_PAD_LEFT);
    return $prefix . $next_num;
  }

  public function prosesPengembalian($data)
  {
    // 1. Insert ke t_kembali
    $insert_kembali = $this->conn->insert($this->tabel_kembali, [
      'kode_kembali' => $data['kode_kembali'],
      'tgl_wkt_blk'  => $data['tgl_wkt_blk'],
      'stts_blk'     => $data['stts_blk'],
      'ket_blk'      => $data['ket_blk'],
      'kode_bayar'   => $data['kode_bayar']
    ]);

    if ($insert_kembali) {
      // 2. Update status sewa menjadi 'selesai'
      $this->conn->update($this->tabel_sewa, ['status_sewa' => 'selesai'], ['kode_sewa' => $data['kode_sewa']]);

      // 3. Update status unit mobil
			$status_unit = 'tersedia';

      $pilih_list = $this->getPilihByKode($data['kode_sewa']);
      foreach ($pilih_list as $p) {
        $this->conn->update($this->tabel_mobil, ['status_unit' => $status_unit], ['no_plat' => $p['no_plat']]);
      }
      return true;
    }
    return false;
  }
}
