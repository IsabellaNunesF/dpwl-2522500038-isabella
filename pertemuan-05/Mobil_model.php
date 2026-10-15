<?php
class Mobil_model
{
  private mysqli $db;
  public function __construct()
  {
    global $db;
    $this->db = $db;
  }
  // P5: membaca seluruh data mobil untuk halaman daftar
  public function getAll(): array
  {
    $sql = "SELECT
no_plat,
nm_mobil,
nkursi,
nbagasi,
jns_mobil,
transmisi,
thn_buat,
ft_depan,
ft_blkg,
ft_interior,
hrg_hari,
cttn_unit,
status_unit
FROM t_mobil
ORDER BY no_plat ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
  }
}
