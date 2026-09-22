<?php
// admin/model/tsewa_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class TSewa
{
  private $conn;
  private $tabel_sewa    = 't_sewa';
  private $tabel_pilih   = 't_pilih';
  private $tabel_penyewa = 't_penyewa';
  private $tabel_mobil   = 't_mobil';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // ==================== GENERATE KODE SEWA ====================
  public function generateKodeSewa()
  {
    $tgl    = date('Ymd');
    $prefix = 'INV/' . $tgl . '/';

    $semua = $this->conn->select($this->tabel_sewa);
    $nomor = 1;

    foreach ($semua as $row) {
      if (strpos($row['kode_sewa'], $prefix) === 0) {
        $parts = explode('/', $row['kode_sewa']);
        $urut  = (int)$parts[2];
        if ($urut >= $nomor) {
          $nomor = $urut + 1;
        }
      }
    }

    return $prefix . str_pad($nomor, 2, '0', STR_PAD_LEFT);
  }

  // ==================== CRUD t_sewa ====================
  public function getAll()
  {
    return $this->conn->select($this->tabel_sewa);
  }

  public function getByKode($kode_sewa)
  {
    return $this->conn->select($this->tabel_sewa, ['kode_sewa' => $kode_sewa]);
  }

  public function tambah($data)
  {
    return $this->conn->insert($this->tabel_sewa, $data);
  }

  public function ubah($data, $kode_sewa_lama)
  {
    $kunci = ['kode_sewa' => $kode_sewa_lama];
    return $this->conn->update($this->tabel_sewa, $data, $kunci);
  }

  public function hapus($kode_sewa)
  {
    $kunci = ['kode_sewa' => $kode_sewa];
    return $this->conn->delete($this->tabel_sewa, $kunci);
  }

  public function updateStatus($kode_sewa, $status)
  {
    $data  = ['status_sewa' => $status];
    $kunci = ['kode_sewa' => $kode_sewa];
    return $this->conn->update($this->tabel_sewa, $data, $kunci);
  }

  // ==================== CRUD t_pilih ====================
  public function getPilihByKode($kode_sewa)
  {
    return $this->conn->select($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
  }

  public function tambahPilih($data)
  {
    return $this->conn->insert($this->tabel_pilih, $data);
  }

  public function hapusPilihByKode($kode_sewa)
  {
    return $this->conn->delete($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
  }

  // ==================== DATA RELASI ====================
  public function getPenyewa()
  {
    return $this->conn->select($this->tabel_penyewa);
  }

  public function getMobilTersedia()
  {
    $semua = $this->conn->select($this->tabel_mobil);
    $hasil = [];
    foreach ($semua as $m) {
      if ($m['status_unit'] === 'tersedia') {
        $hasil[] = $m;
      }
    }
    return $hasil;
  }

  public function getMobil()
  {
    return $this->conn->select($this->tabel_mobil);
  }

  public function getMobilByPlat($no_plat)
  {
    $result = $this->conn->select($this->tabel_mobil, ['no_plat' => $no_plat]);
    return !empty($result) ? $result[0] : null;
  }

  // ==================== HELPER: GABUNG DATA ====================
  public function getAllWithDetail()
  {
    $sewa    = $this->getAll();
    $penyewa = $this->getPenyewa();
    $mobil   = $this->getMobil();

    $map_penyewa = [];
    foreach ($penyewa as $p) {
      $map_penyewa[$p['user_plg']] = $p;
    }

    $map_mobil = [];
    foreach ($mobil as $m) {
      $map_mobil[$m['no_plat']] = $m;
    }

    $hasil = [];
    foreach ($sewa as $s) {
      $p = $map_penyewa[$s['user_plg']] ?? null;

      $pilih_list = $this->getPilihByKode($s['kode_sewa']);
      $nama_mobil_arr = [];
      $total_bayar    = 0;

      foreach ($pilih_list as $pl) {
        $m = $map_mobil[$pl['no_plat']] ?? null;
        $nama_mobil_arr[] = $m ? $m['nm_mobil'] . " (" . $pl['no_plat'] . ")" : $pl['no_plat'];
        $total_bayar += ($pl['hrg_sewa'] * $pl['n_hari']);
      }

      $s['nm_lngkp']     = $p ? $p['nm_lngkp'] : '-';
      $s['nik']          = $p ? $p['nik'] : '-';
      $s['nama_mobil']   = implode(', ', $nama_mobil_arr);
      $s['total_bayar']  = $total_bayar;
      $s['detail_pilih'] = $pilih_list;

      $hasil[] = $s;
    }

    return $hasil;
  }
}
