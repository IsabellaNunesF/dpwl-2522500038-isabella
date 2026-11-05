
<?php

class Penyewa_model
{
  private mysqli $db;

  public function __construct()
  {
    global $db;
    $this->db = $db;
  }

  // P8: membaca seluruh data penyewa untuk halaman manajemen (READ)
  public function getAll(): array
  {
    $sql = "SELECT
                    user_plg,
                    nik,
                    nm_lngkp,
                    no_hp,
                    almt_lngkp,
                    ft_ktp,
                    ft_sim_a
                FROM t_penyewa
                ORDER BY user_plg ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
  }

  // P8: menyimpan data penyewa (INSERT)
  public function create(array $data): bool
  {
    $sql = "INSERT INTO t_penyewa (
                    user_plg,
                    sandi,
                    nik,
                    nm_lngkp,
                    no_hp,
                    almt_lngkp,
                    ft_ktp,
                    ft_sim_a
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);

    $stmt->bind_param(
      'ssssssss',
      $data['user_plg'],
      $data['sandi'],
      $data['nik'],
      $data['nm_lngkp'],
      $data['no_hp'],
      $data['almt_lngkp'],
      $data['ft_ktp'],
      $data['ft_sim_a']
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
  }

  // P8: mengambil satu penyewa berdasarkan username untuk proses update (UPDATE - GET)
  public function findByUserPlg(string $userPlg): ?array
  {
    $sql = "SELECT
                    user_plg,
                    sandi,
                    nik,
                    nm_lngkp,
                    no_hp,
                    almt_lngkp,
                    ft_ktp,
                    ft_sim_a
                FROM t_penyewa
                WHERE user_plg = ?
                LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('s', $userPlg);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return $row ?: null;
  }

  // P8: memperbarui data penyewa berdasarkan username (UPDATE - POST)
  public function update(string $userPlg, array $data): bool
  {
    $sql = "UPDATE t_penyewa SET
                    nik = ?,
                    nm_lngkp = ?,
                    no_hp = ?,
                    almt_lngkp = ?,
                    ft_ktp = ?,
                    ft_sim_a = ?
                WHERE user_plg = ?";

    $stmt = $this->db->prepare($sql);

    $stmt->bind_param(
      'sssssss',
      $data['nik'],
      $data['nm_lngkp'],
      $data['no_hp'],
      $data['almt_lngkp'],
      $data['ft_ktp'],
      $data['ft_sim_a'],
      $userPlg
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
  }

  // P8: menghapus data penyewa berdasarkan username (DELETE)
  public function delete(string $userPlg): bool
  {
    $sql = "DELETE FROM t_penyewa WHERE user_plg = ?";

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('s', $userPlg);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
  }
}
