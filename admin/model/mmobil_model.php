<?php
// admin/model/mmobil_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class MMobil
{
  private $conn;
  private $tabel = 't_mobil';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // Ambil semua data mobil
  public function getAll()
  {
    return $this->conn->select($this->tabel);
  }

  // Cari mobil berdasarkan no_plat
  public function getByPlat($no_plat)
  {
    return $this->conn->select($this->tabel, ['no_plat' => $no_plat]);
  }

  // Tambah mobil baru
  public function tambah($data)
  {
    return $this->conn->insert($this->tabel, $data);
  }

  // Update mobil
  public function ubah($data, $no_plat_lama)
  {
    $kunci = ['no_plat' => $no_plat_lama];
    return $this->conn->update($this->tabel, $data, $kunci);
  }

  // Hapus mobil
  public function hapus($no_plat)
  {
    $kunci = ['no_plat' => $no_plat];
    return $this->conn->delete($this->tabel, $kunci);
  }
}
