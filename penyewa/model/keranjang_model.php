<?php
class KeranjangModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Method yang sudah ada
  public function getDetailMobil($noPlats)
  {
    if (empty($noPlats)) return [];
    $placeholders = implode(',', array_fill(0, count($noPlats), '?'));
    $sql = "SELECT * FROM t_mobil WHERE no_plat IN ($placeholders)";
    return $this->conn->query($sql, $noPlats)->fetchAll();
  }

  public function getPesananDiajukan($userPlg)
  {
    $sql = "SELECT kode_sewa, tgl_mulai, tgl_selesai, wkt_ambil FROM t_sewa
                WHERE user_plg = ? AND status_sewa = 'keranjang'
                ORDER BY tgl_mulai DESC LIMIT 1";
    $stmt = $this->conn->query($sql, [$userPlg]);
    return $stmt->fetch();
  }

  public function cekMobilDiKeranjang($kodeSewa, $noPlat)
  {
    $sql = "SELECT * FROM t_pilih WHERE kode_sewa = ? AND no_plat = ?";
    $stmt = $this->conn->query($sql, [$kodeSewa, $noPlat]);
    return $stmt->fetch();
  }

  public function insertSewa($data)
  {
    return $this->conn->insert('t_sewa', $data);
  }

  public function insertPilih($data)
  {
    return $this->conn->insert('t_pilih', $data);
  }

  public function generateKodeSewa()
  {
    $tanggal = date('Ymd');
    $sql = "SELECT MAX(kode_sewa) as terakhir FROM t_sewa WHERE kode_sewa LIKE 'INV/{$tanggal}/%'";
    $stmt = $this->conn->query($sql);
    $result = $stmt->fetch();
    if ($result['terakhir']) {
      $urutan = (int)substr($result['terakhir'], -2) + 1;
    } else {
      $urutan = 1;
    }
    return sprintf("INV/%s/%02d", $tanggal, $urutan);
  }

  public function getDetailMobilSingle($noPlat)
  {
    $sql = "SELECT * FROM t_mobil WHERE no_plat = ? AND status_unit = 'tersedia'";
    $stmt = $this->conn->query($sql, [$noPlat]);
    return $stmt->fetch();
  }

  public function buatPesananBaru($kode_sewa, $tgl_mulai, $tgl_selesai, $wkt_ambil, $userPlg)
  {
    $sql = "INSERT INTO t_sewa (kode_sewa, tgl_mulai, tgl_selesai, wkt_ambil, status_sewa, user_plg)
                VALUES (?, ?, ?, ?, 'keranjang', ?)";
    $this->conn->query($sql, [$kode_sewa, $tgl_mulai, $tgl_selesai, $wkt_ambil, $userPlg]);
  }

  public function tambahMobilKeKeranjang($kode_sewa, $no_plat)
  {
    $sqlMobil = "SELECT hrg_hari FROM t_mobil WHERE no_plat = ?";
    $stmtMobil = $this->conn->query($sqlMobil, [$no_plat]);
    $mobil = $stmtMobil->fetch();
    if ($mobil) {
      $hrg_sewa = $mobil['hrg_hari'];
      $n_hari = 1;
      $sql = "INSERT INTO t_pilih (kode_sewa, no_plat, hrg_sewa, n_hari) VALUES (?, ?, ?, ?)";
      $this->conn->query($sql, [$kode_sewa, $no_plat, $hrg_sewa, $n_hari]);
    }
  }

  public function hapusMobilDariKeranjang($kode_sewa, $no_plat)
  {
    $sql = "DELETE FROM t_pilih WHERE kode_sewa = ? AND no_plat = ?";
    $this->conn->query($sql, [$kode_sewa, $no_plat]);
  }

  public function getDetailMobilKeranjang($kode_sewa)
  {
    $sql = "SELECT m.*, p.hrg_sewa, p.n_hari
                FROM t_pilih p
                INNER JOIN t_mobil m ON p.no_plat = m.no_plat
                WHERE p.kode_sewa = ?";
    $stmt = $this->conn->query($sql, [$kode_sewa]);
    return $stmt->fetchAll();
  }

  // ==========================================
  // METHOD BARU: Update Periode Sewa
  // ==========================================
  public function updatePeriodeSewa($kode_sewa, $tgl_mulai, $tgl_selesai, $wkt_ambil, $n_hari)
  {
    // Update t_sewa
    $dataSewa = [
      'tgl_mulai'   => $tgl_mulai,
      'tgl_selesai' => $tgl_selesai,
      'wkt_ambil'   => $wkt_ambil
    ];
    $this->conn->update('t_sewa', $dataSewa, ['kode_sewa' => $kode_sewa]);

    // Update t_pilih.n_hari untuk semua mobil di transaksi ini
    $sql = "UPDATE t_pilih SET n_hari = ? WHERE kode_sewa = ?";
    $this->conn->query($sql, [$n_hari, $kode_sewa]);
  }
}
