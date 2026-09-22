<?php
class BatalModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Ambil detail transaksi untuk ditampilkan di halaman konfirmasi
  public function getDetailSewa($kode_sewa, $user_plg)
  {
    $sql = "SELECT 
                    s.kode_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil, s.status_sewa,
                    m.nm_mobil, m.no_plat, m.ft_depan, 
                    p.hrg_sewa, p.n_hari
                FROM t_sewa s
                JOIN t_pilih p ON s.kode_sewa = p.kode_sewa
                JOIN t_mobil m ON p.no_plat = m.no_plat
                WHERE s.kode_sewa = ? AND s.user_plg = ?";

    $stmt = $this->conn->query($sql, [$kode_sewa, $user_plg]);
    return $stmt->fetchAll();
  }

  // Update status sewa menjadi batal
  public function batalkanSewa($kode_sewa, $user_plg)
  {
    $data = ['status_sewa' => 'batal'];

    // Kunci mencakup status_sewa = 'diajukan' untuk memastikan 
    // hanya transaksi yang masih berstatus 'diajukan' yang bisa dibatalkan
    $kunci = [
      'kode_sewa'   => $kode_sewa,
      'user_plg'    => $user_plg,
      'status_sewa' => 'diajukan'
    ];

    return $this->conn->update('t_sewa', $data, $kunci);
  }
}
