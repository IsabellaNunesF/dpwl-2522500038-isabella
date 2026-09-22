<?php
// admin/model/dashboard_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class DashboardModel
{
  private $conn;

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  public function getStatMobil()
  {
    $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_unit = 'tersedia' THEN 1 ELSE 0 END) as tersedia,
                    SUM(CASE WHEN status_unit = 'tidaktersedia' THEN 1 ELSE 0 END) as tidaktersedia,
                    SUM(CASE WHEN status_unit = 'disewa' THEN 1 ELSE 0 END) as disewa
                FROM t_mobil";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getJumlahPenyewa()
  {
    $sql = "SELECT COUNT(*) as total FROM t_penyewa";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
  }

  public function getStatSewa()
  {
    $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_sewa = 'aktif' THEN 1 ELSE 0 END) as aktif,
                    SUM(CASE WHEN status_sewa = 'diajukan' THEN 1 ELSE 0 END) as diajukan,
                    SUM(CASE WHEN status_sewa = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status_sewa IN ('ditolak', 'batal') THEN 1 ELSE 0 END) as dibatalkan
                FROM t_sewa";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getPendapatanSewa()
  {
    $sql = "SELECT COALESCE(SUM(nominal), 0) as total 
                FROM t_bayar 
                WHERE jns_byr = 'pelunasan' AND sttus_byr = 'valid'";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
  }

  public function getPendapatanDenda()
  {
    $sql = "SELECT COALESCE(SUM(telat_jam * tarif_per_jam), 0) as total FROM t_denda";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
  }

  public function getPembayaranPending()
  {
    $sql = "SELECT COUNT(*) as total FROM t_bayar WHERE sttus_byr = 'pending'";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
  }

  public function getPengembalianTerlambat()
  {
    $sql = "SELECT COUNT(*) as total FROM t_kembali WHERE stts_blk = 'terlambat'";
    $stmt = $this->conn->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
  }


  public function getTotalPendapatan()
  {
    return $this->getPendapatanSewa() + $this->getPendapatanDenda();
  }
}
