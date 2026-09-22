<?php
// admin/model/mpenyewa_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class MPenyewa
{
  private $conn;
  private $tabel = 't_penyewa';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // Ambil semua data penyewa
  public function getAll()
  {
    return $this->conn->select($this->tabel);
  }

  // Cari penyewa berdasarkan user_plg
  public function getByUser($user_plg)
  {
    return $this->conn->select($this->tabel, ['user_plg' => $user_plg]);
  }

  // Tambah penyewa baru
  public function tambah($data)
  {
    return $this->conn->insert($this->tabel, $data);
  }

  // Update penyewa
  public function ubah($data, $user_plg_lama)
  {
    $kunci = ['user_plg' => $user_plg_lama];
    return $this->conn->update($this->tabel, $data, $kunci);
  }

  // Hapus penyewa
  public function hapus($user_plg)
  {
    $kunci = ['user_plg' => $user_plg];
    return $this->conn->delete($this->tabel, $kunci);
  }
}
