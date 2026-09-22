<?php
class PembayaranModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  /**
   * Ambil data pembayaran berdasarkan kode_sewa
   */
  public function getPembayaranByKodeSewa($kodeSewa)
  {
    $sql = "SELECT b.*, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil, s.status_sewa,
                       p.nm_lngkp, p.no_hp
                FROM t_bayar b
                INNER JOIN t_sewa s ON b.kode_sewa = s.kode_sewa
                INNER JOIN t_penyewa p ON s.user_plg = p.user_plg
                WHERE b.kode_sewa = ? AND b.jns_byr = 'dp'
                ORDER BY b.tgl_wkt_byr DESC LIMIT 1";

    $stmt = $this->conn->query($sql, [$kodeSewa]);
    return $stmt->fetch();
  }

  /**
   * Ambil daftar mobil yang disewa
   */
  public function getDaftarMobil($kodeSewa)
  {
    $sql = "SELECT m.no_plat, m.nm_mobil, m.jns_mobil, m.ft_depan,
                       p.hrg_sewa, p.n_hari
                FROM t_pilih p
                INNER JOIN t_mobil m ON p.no_plat = m.no_plat
                WHERE p.kode_sewa = ?";

    $stmt = $this->conn->query($sql, [$kodeSewa]);
    return $stmt->fetchAll();
  }

  /**
   * Upload bukti pembayaran
   */
  public function uploadBuktiBayar($kodeBayar, $file)
  {
    $uploadDir = UPLOAD_DIR . 'pembayaran/';

    // Pastikan direktori ada
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    // Validasi file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
      return ['success' => false, 'message' => 'Format file harus JPG atau PNG'];
    }

    if ($file['size'] > $maxSize) {
      return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
    }

    // Generate nama file
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $namaFile = 'ft_byr_' . time() . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $namaFile;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
      // Update database
      $kunci = ['kode_bayar' => $kodeBayar];
      $data = ['ft_byr' => $namaFile];

      $update = $this->conn->update('t_bayar', $data, $kunci);

      if ($update) {
        return ['success' => true, 'message' => 'Bukti pembayaran berhasil diupload'];
      } else {
        // Hapus file jika update gagal
        unlink($targetPath);
        return ['success' => false, 'message' => 'Gagal menyimpan ke database'];
      }
    } else {
      return ['success' => false, 'message' => 'Gagal upload file'];
    }
  }

  /**
   * Hitung total estimasi sewa
   */
  public function hitungTotalEstimasi($kodeSewa)
  {
    $sql = "SELECT SUM(p.hrg_sewa * p.n_hari) as total
                FROM t_pilih p
                WHERE p.kode_sewa = ?";

    $stmt = $this->conn->query($sql, [$kodeSewa]);
    $result = $stmt->fetch();

    return $result['total'] ?? 0;
  }
}
