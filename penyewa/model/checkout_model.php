<?php
class CheckoutModel
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  /**
   * Ambil data pesanan yang akan di-checkout (status masih 'keranjang')
   */
  public function getCheckoutData($userPlg)
  {
    // PERBAIKAN: Ubah dari 'diajukan' menjadi 'keranjang'
    $sql = "SELECT kode_sewa, tgl_mulai, tgl_selesai, wkt_ambil, status_sewa
                FROM t_sewa
                WHERE user_plg = ? AND status_sewa = 'keranjang'
                ORDER BY tgl_mulai DESC LIMIT 1";
    $stmt = $this->conn->query($sql, [$userPlg]);
    $pesanan = $stmt->fetch();

    if (!$pesanan) {
      return null;
    }

    $kodeSewa = $pesanan['kode_sewa'];

    // Ambil daftar mobil di pesanan ini
    $sqlMobil = "SELECT m.no_plat, m.nm_mobil, m.jns_mobil, m.transmisi, m.thn_buat,
                            p.hrg_sewa, p.n_hari
                     FROM t_pilih p
                     INNER JOIN t_mobil m ON p.no_plat = m.no_plat
                     WHERE p.kode_sewa = ?";
    $stmtMobil = $this->conn->query($sqlMobil, [$kodeSewa]);
    $daftarMobil = $stmtMobil->fetchAll();

    // Hitung durasi
    $tglMulai = new DateTime($pesanan['tgl_mulai']);
    $tglSelesai = new DateTime($pesanan['tgl_selesai']);
    $diff = $tglMulai->diff($tglSelesai);
    $nHari = max(1, $diff->days);

    // Hitung total
    $totalPerHari = array_sum(array_column($daftarMobil, 'hrg_sewa'));
    $totalEstimasi = $totalPerHari * $nHari;
    $dp = ceil($totalEstimasi * 0.30); // DP 30%

    return [
      'pesanan' => $pesanan,
      'daftarMobil' => $daftarMobil,
      'nHari' => $nHari,
      'totalPerHari' => $totalPerHari,
      'totalEstimasi' => $totalEstimasi,
      'dp' => $dp
    ];
  }

  /**
   * Generate kode pembayaran
   * Format: PAY/YYYYMMDD/99
   */
  public function generateKodeBayar()
  {
    $tanggal = date('Ymd');
    $sql = "SELECT MAX(kode_bayar) as terakhir FROM t_bayar WHERE kode_bayar LIKE 'PAY/{$tanggal}/%'";
    $stmt = $this->conn->query($sql);
    $result = $stmt->fetch();

    if ($result['terakhir']) {
      $urutan = (int)substr($result['terakhir'], -2) + 1;
    } else {
      $urutan = 1;
    }

    return sprintf("PAY/%s/%02d", $tanggal, $urutan);
  }

  /**
   * Buat record pembayaran DP
   */
  public function buatPembayaranDP($kodeSewa, $nominalDP)
  {
    $kodeBayar = $this->generateKodeBayar();
    $tglWktByr = date('Y-m-d H:i:s');

    $data = [
      'kode_bayar' => $kodeBayar,
      'tgl_wkt_byr' => $tglWktByr,
      'ft_byr' => null,
      'mtode_byr' => 'transfer',
      'jns_byr' => 'dp',
      'nominal' => $nominalDP,
      'sttus_byr' => 'pending',
      'tgl_wkt_ver' => null,
      'kode_sewa' => $kodeSewa
    ];

    return $this->conn->insert('t_bayar', $data);
  }

  /**
   * BARU: Update status sewa dari 'keranjang' menjadi 'diajukan'
   */
  public function updateStatusSewa($kodeSewa, $statusBaru = 'diajukan')
  {
    $kunci = ['kode_sewa' => $kodeSewa];
    $data = ['status_sewa' => $statusBaru];

    return $this->conn->update('t_sewa', $data, $kunci);
  }
}
