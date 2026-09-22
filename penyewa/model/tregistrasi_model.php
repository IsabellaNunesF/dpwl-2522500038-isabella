<?php
class TRegistrasiModel
{
  private $conn;
  private $tabel = 't_penyewa';

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Cek apakah username sudah dipakai
  public function cekUsername($user_plg)
  {
    $stmt = $this->conn->query(
      "SELECT COUNT(*) as total FROM {$this->tabel} WHERE user_plg = ?",
      [$user_plg]
    );
    $row = $stmt->fetch();
    return $row['total'] > 0;
  }

  // Cek apakah NIK sudah terdaftar
  public function cekNIK($nik)
  {
    $stmt = $this->conn->query(
      "SELECT COUNT(*) as total FROM {$this->tabel} WHERE nik = ?",
      [$nik]
    );
    $row = $stmt->fetch();
    return $row['total'] > 0;
  }

  // Proses registrasi
  public function daftar($data)
  {
    // Hash password dengan bcrypt
    $data['sandi'] = password_hash($data['sandi'], PASSWORD_BCRYPT);

    // ft_ktp dan ft_sim_a dikosongkan dulu (upload nanti)
    $data['ft_ktp']  = null;
    $data['ft_sim_a'] = null;

    return $this->conn->insert($this->tabel, $data);
  }
}
