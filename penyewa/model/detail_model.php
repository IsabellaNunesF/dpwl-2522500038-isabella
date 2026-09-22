<?php
class DetailModel
{
  private $conn;
  private $tabel = 't_mobil';

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Ambil data mobil berdasarkan no_plat
  public function getMobilByPlat($plat)
  {
    $stmt = $this->conn->query(
      "SELECT * FROM {$this->tabel} WHERE no_plat = ?",
      [$plat]
    );
    return $stmt->fetch();
  }
}
