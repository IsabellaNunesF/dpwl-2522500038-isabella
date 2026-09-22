<?php

/**
 * Header Model
 * Menangani query untuk data header (keranjang, notifikasi, dll)
 */

class HeaderModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  /**
   * Hitung jumlah item di keranjang (status: keranjang)
   */
  public function getKeranjangCount($userPlg)
  {
    $sql = "
            SELECT COUNT(*) as jumlah 
            FROM t_pilih tp
            INNER JOIN t_sewa ts ON tp.kode_sewa = ts.kode_sewa
            WHERE ts.user_plg = ? 
            AND ts.status_sewa = 'keranjang'
        ";

    $stmt = $this->conn->query($sql, [$userPlg]);
    return (int)($stmt->fetch()['jumlah'] ?? 0);
  }

  /**
   * Ambil notifikasi untuk user (opsional - untuk fitur notifikasi real-time)
   */
  // public function getNotifikasiCount($userPlg)
  // {
  //   // Bisa dikembangkan untuk notifikasi sewa, pembayaran, dll
  //   return 0;
  // }
}
