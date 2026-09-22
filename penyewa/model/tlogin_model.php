<?php
class TLoginModel
{
  private $conn;
  private $tabel = 't_penyewa';

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Ambil data user berdasarkan username
  public function getUserByUsername($user_plg)
  {
    $stmt = $this->conn->query(
      "SELECT * FROM {$this->tabel} WHERE user_plg = ?",
      [$user_plg]
    );
    return $stmt->fetch();
  }
}
