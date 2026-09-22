<?php
class TMobilModel
{
  private $conn;
  private $tabel = 't_mobil';

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Method baru: Hitung total mobil
  public function getTotalMobil()
  {
    $sql = "SELECT COUNT(*) as total FROM {$this->tabel}";
    $stmt = $this->conn->query($sql);
    return (int)$stmt->fetch()['total'];
  }

  // Method baru: Hitung total penyewa
  public function getTotalPenyewa()
  {
    $sql = "SELECT COUNT(*) as total FROM t_penyewa";
    $stmt = $this->conn->query($sql);
    return (int)$stmt->fetch()['total'];
  }

  public function getMobil($filters = [], $page = 1, $perPage = 12, $sort = 'default')
  {
    $where = ["status_unit = 'tersedia'"];
    $params = [];

    // Filter Transmisi
    if (!empty($filters['transmisi'])) {
      $transmisiList = array_map('trim', explode(',', $filters['transmisi']));
      $placeholders = implode(',', array_fill(0, count($transmisiList), '?'));
      $where[] = "transmisi IN ($placeholders)";
      $params = array_merge($params, $transmisiList);
    }

    // Filter Kapasitas
    if (!empty($filters['kapasitas'])) {
      $kapList = array_map('intval', explode(',', $filters['kapasitas']));
      $conditions = [];
      foreach ($kapList as $k) {
        if ($k == 8) $conditions[] = "nkursi >= 8";
        else {
          $conditions[] = "nkursi = ?";
          $params[] = $k;
        }
      }
      if (!empty($conditions)) $where[] = "(" . implode(" OR ", $conditions) . ")";
    }

    // Filter Bagasi
    if (!empty($filters['bagasi'])) {
      $bagList = array_map('intval', explode(',', $filters['bagasi']));
      $conditions = [];
      foreach ($bagList as $b) {
        if ($b == 4) $conditions[] = "nbagasi >= 4";
        else {
          $conditions[] = "nbagasi = ?";
          $params[] = $b;
        }
      }
      if (!empty($conditions)) $where[] = "(" . implode(" OR ", $conditions) . ")";
    }

    // Filter Jenis Mobil
    if (!empty($filters['jenis'])) {
      $jenisList = array_map('trim', explode(',', $filters['jenis']));
      $placeholders = implode(',', array_fill(0, count($jenisList), '?'));
      $where[] = "jns_mobil IN ($placeholders)";
      $params = array_merge($params, $jenisList);
    }

    // Filter Harga Maksimal
    if (!empty($filters['maxPrice']) && $filters['maxPrice'] < 5000000) {
      $where[] = "hrg_hari <= ?";
      $params[] = (int)$filters['maxPrice'];
    }

    // Filter Keyword (Pencarian) - PERBAIKAN: Gunakan UPPER untuk case-insensitive
    if (!empty($filters['keyword'])) {
      $where[] = "UPPER(nm_mobil) LIKE ?";
      $keyword = '%' . strtoupper(trim($filters['keyword'])) . '%';
      $params[] = $keyword;
    }

    $whereClause = implode(" AND ", $where);

    // Hitung Total Data
    $countSql = "SELECT COUNT(*) as total FROM {$this->tabel} WHERE $whereClause";
    $countStmt = $this->conn->query($countSql, $params);
    $totalItems = $countStmt->fetch()['total'];
    $totalPages = ceil($totalItems / $perPage) ?: 1;

    // Sorting
    $orderBy = "nm_mobil ASC";
    if ($sort === 'price-asc') $orderBy = "hrg_hari ASC";
    elseif ($sort === 'price-desc') $orderBy = "hrg_hari DESC";
    elseif ($sort === 'name-asc') $orderBy = "nm_mobil ASC";

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM {$this->tabel} WHERE $whereClause ORDER BY $orderBy LIMIT $offset, $perPage";
    $stmt = $this->conn->query($sql, $params);

    return [
      'data'        => $stmt->fetchAll(),
      'totalItems'  => (int)$totalItems,
      'totalPages'  => (int)$totalPages,
      'currentPage' => (int)$page
    ];
  }
}
