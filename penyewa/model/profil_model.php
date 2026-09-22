<?php
class ProfilModel
{
  private $conn;
  private $tabel = 't_penyewa';

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Ambil data penyewa berdasarkan user_plg
  public function getProfil($user_plg)
  {
    $stmt = $this->conn->query(
      "SELECT * FROM {$this->tabel} WHERE user_plg = ?",
      [$user_plg]
    );
    return $stmt->fetch();
  }

  // Update data profil (tanpa password)
  public function updateProfil($user_plg, $data)
  {
    return $this->conn->update($this->tabel, $data, ['user_plg' => $user_plg]);
  }

  // Update password saja
  public function updatePassword($user_plg, $sandi_baru_hash)
  {
    return $this->conn->update(
      $this->tabel,
      ['sandi' => $sandi_baru_hash],
      ['user_plg' => $user_plg]
    );
  }

  // Update foto (KTP / SIM)
  public function updateFoto($user_plg, $kolom, $nama_file)
  {
    return $this->conn->update(
      $this->tabel,
      [$kolom => $nama_file],
      ['user_plg' => $user_plg]
    );
  }

  // Verifikasi password lama
  public function cekPasswordLama($user_plg, $sandi_lama)
  {
    $user = $this->getProfil($user_plg);
    return $user && password_verify($sandi_lama, $user['sandi']);
  }
}
