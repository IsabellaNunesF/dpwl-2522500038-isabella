<?php
// admin/model/tpenyerahan_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class TPenyerahan
{
  private $conn;
  private $tabel_sewa    = 't_sewa';
  private $tabel_penyewa = 't_penyewa';
  private $tabel_pilih   = 't_pilih';
  private $tabel_mobil   = 't_mobil';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // ==================== AMBIL DATA SEWA ====================
  public function getSewaUntukDiserahkan()
  {
    // Ambil semua data sewa, lalu filter yang statusnya 'disetujui' atau 'aktif'
    $semua_sewa = $this->conn->select($this->tabel_sewa);
    $hasil = [];
    foreach ($semua_sewa as $s) {
      if (in_array($s['status_sewa'], ['disetujui', 'aktif'])) {
        $hasil[] = $s;
      }
    }
    return $hasil;
  }

  public function getPenyewa()
  {
    return $this->conn->select($this->tabel_penyewa);
  }

  public function getPilihByKode($kode_sewa)
  {
    return $this->conn->select($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
  }

  public function getMobil()
  {
    return $this->conn->select($this->tabel_mobil);
  }

  // ==================== PROSES PENYERAHAN ====================
  /* public function prosesPenyerahan($kode_sewa)
  {
    // Tugasnya hanya 1: mengubah t_sewa.status_sewa dari 'disetujui' ➔ 'aktif'
    // Status unit mobil sudah diubah menjadi 'disewa' sejak pembayaran divalidasi.
    $data_sewa  = ['status_sewa' => 'aktif'];
    $kunci_sewa = ['kode_sewa' => $kode_sewa];
    $update_sewa = $this->conn->update($this->tabel_sewa, $data_sewa, $kunci_sewa);

    if ($update_sewa) {
      // 2. Update status unit mobil menjadi 'disewa'
      $pilih_list = $this->getPilihByKode($kode_sewa);
      foreach ($pilih_list as $p) {
        $data_mobil  = ['status_unit' => 'disewa'];
        $kunci_mobil = ['no_plat' => $p['no_plat']];
        $this->conn->update($this->tabel_mobil, $data_mobil, $kunci_mobil);
      }
      return true;
    }
    return false;
  } */

  /* public function prosesPenyerahan($kode_sewa)
  {
    // Tugasnya hanya 1: mengubah t_sewa.status_sewa dari 'disetujui' ➔ 'aktif'
    // Status unit mobil sudah diubah menjadi 'disewa' sejak pembayaran divalidasi.
    $data_sewa  = ['status_sewa' => 'aktif'];
    $kunci_sewa = ['kode_sewa' => $kode_sewa];

    return $this->conn->update($this->tabel_sewa, $data_sewa, $kunci_sewa);
  } */

  // ==================== PROSES PENYERAHAN ====================
  // Tugas:
  //   1. Ubah t_sewa.status_sewa: 'disetujui' ➔ 'aktif'
  //   2. Catat waktu ambil aktual (wkt_ambil)
  //   3. JANGAN ubah tgl_mulai, tgl_selesai, dan n_hari (sudah fixed sesuai perjanjian)
  public function prosesPenyerahan($kode_sewa, $wkt_ambil_aktual)
  {
    $data_sewa = [
      'status_sewa' => 'aktif',
      'wkt_ambil'   => $wkt_ambil_aktual // Catat waktu ambil aktual
    ];
    $kunci_sewa = ['kode_sewa' => $kode_sewa];

    return $this->conn->update($this->tabel_sewa, $data_sewa, $kunci_sewa);
  }
}
