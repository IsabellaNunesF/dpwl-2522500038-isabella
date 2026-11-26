<?php 
 
class SerahTerima_model 
{ 
  private mysqli $db; 
 
  public function __construct() 
  { 
    global $db; 
 
    if (!$db instanceof mysqli) { 
      throw new RuntimeException( 
        'Koneksi basis data tidak tersedia.' 
      ); 
    } 
 
    $this->db = $db; 
  } 
} 
/** 
 * Mengambil transaksi yang telah disetujui dan pembayaran DP-nya valid. 
 * Transaksi yang sudah aktif tidak ditampilkan kembali. 
 */ 
public function getDaftarSerahTerima(): array 
{ 
  $sql = "SELECT 
            s.kode_sewa, 
            s.tgl_mulai, 
            s.tgl_selesai, 
            s.wkt_ambil, 
            s.status_sewa, 
            p.user_plg, 
  p.nm_lngkp, 
            p.no_hp, 
            p.ft_ktp, 
            p.ft_sim_a, 
            ( 
              SELECT COUNT(*) 
              FROM t_pilih tp 
              WHERE tp.kode_sewa = s.kode_sewa 
            ) AS jumlah_kendaraan 
          FROM t_sewa s 
          INNER JOIN t_penyewa p 
            ON p.user_plg = s.user_plg 
          WHERE s.status_sewa = 'disetujui' 
            AND EXISTS ( 
              SELECT 1 
              FROM t_bayar b 
              WHERE b.kode_sewa = s.kode_sewa 
                AND b.jns_byr = 'dp' 
                AND b.sttus_byr = 'valid' 
            ) 
          ORDER BY s.tgl_mulai ASC, s.wkt_ambil ASC"; 
 
  $stmt = $this->db->prepare($sql); 
 
  if (!$stmt) { 
    throw new RuntimeException( 
      'Query daftar serah terima gagal disiapkan.' 
    ); 
  } 
 
  if (!$stmt->execute()) { 
    $stmt->close(); 
 
    throw new RuntimeException( 
      'Daftar serah terima gagal diambil.' 
    ); 
  } 
 
  $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 
  $stmt->close(); 
 
  return $daftar; 
} 
/** 
 * Mengambil detail transaksi, penyewa, dan pembayaran DP. 
 */ 
public function getDetailSerahTerima(string $kodeSewa): ?array 
{ 
  $kodeSewa = trim($kodeSewa); 
 
  if ($kodeSewa === '') { 
    throw new InvalidArgumentException( 
      'Kode transaksi sewa wajib diisi.' 
    ); 
  } 
 
  $sql = "SELECT 
            s.kode_sewa, 
            s.tgl_mulai, 
            s.tgl_selesai,
              s.wkt_ambil, 
            s.status_sewa, 
            p.user_plg, 
            p.nm_lngkp, 
            p.nik, 
            p.no_hp, 
            p.almt_lngkp, 
            p.ft_ktp, 
            p.ft_sim_a, 
            b.kode_bayar, 
            b.tgl_wkt_byr, 
            b.nominal AS nominal_dp, 
            b.mtode_byr, 
            b.sttus_byr, 
            b.tgl_wkt_ver 
          FROM t_sewa s 
          INNER JOIN t_penyewa p 
            ON p.user_plg = s.user_plg 
          LEFT JOIN t_bayar b 
            ON b.kode_sewa = s.kode_sewa 
            AND b.jns_byr = 'dp' 
          WHERE s.kode_sewa = ? 
          ORDER BY b.tgl_wkt_byr DESC 
          LIMIT 1"; 
 
  $stmt = $this->db->prepare($sql); 
 
  if (!$stmt) { 
    throw new RuntimeException( 
      'Query detail serah terima gagal disiapkan.' 
    ); 
  } 
 
  $stmt->bind_param('s', $kodeSewa); 
 
  if (!$stmt->execute()) { 
    $stmt->close(); 
 
    throw new RuntimeException( 
      'Detail serah terima gagal diambil.' 
    ); 
  } 
 
  $detail = $stmt->get_result()->fetch_assoc(); 
  $stmt->close(); 
 
  if (!$detail) { 
    return null; 
  } 
 
  $detail['daftar_kendaraan'] = 
    $this->getDaftarKendaraan($kodeSewa); 
 
  return $detail; 
} 
/** 
 * Mengambil seluruh kendaraan yang tercatat pada transaksi. 
 */ 
private function getDaftarKendaraan(string $kodeSewa): array 
{ 
  $sql = "SELECT 
            m.no_plat, 
            m.nm_mobil, 
 m.status_unit, 
            p.hrg_sewa, 
            p.n_hari 
          FROM t_pilih p 
          INNER JOIN t_mobil m 
            ON m.no_plat = p.no_plat 
          WHERE p.kode_sewa = ? 
          ORDER BY m.nm_mobil ASC, m.no_plat ASC"; 
 
  $stmt = $this->db->prepare($sql); 
 
  if (!$stmt) { 
    throw new RuntimeException( 
      'Query kendaraan transaksi gagal disiapkan.' 
    ); 
  } 
 
  $stmt->bind_param('s', $kodeSewa); 
 
  if (!$stmt->execute()) { 
    $stmt->close(); 
 
    throw new RuntimeException( 
      'Daftar kendaraan transaksi gagal diambil.' 
    ); 
  } 
 
  $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 
  $stmt->close(); 
 
  return $daftar; 
} 
/** 
 * Memproses serah terima seluruh kendaraan dalam satu transaksi. 
 * 
 * Nama file dokumen boleh null jika dokumen sebelumnya sudah tersedia. 
 * Status kendaraan tidak diubah karena telah menjadi "disewa" 
 * ketika pembayaran DP disetujui pada P10. 
 */ 
public function prosesSerahTerima( 
  string $kodeSewa, 
  string $waktuAktual, 
  ?string $fileKtpBaru = null, 
  ?string $fileSimBaru = null 
): void { 
  $kodeSewa = trim($kodeSewa); 
 
  if ($kodeSewa === '') { 
    throw new InvalidArgumentException( 
      'Kode transaksi sewa wajib diisi.' 
    ); 
  } 
 
  $waktu = \DateTimeImmutable::createFromFormat( 
    '!H:i:s', 
    $waktuAktual 
  ); 
 
  $errorWaktu = \DateTimeImmutable::getLastErrors(); 
 
  if ( 
    $waktu === false || 
    ( 
      $errorWaktu !== false && 
      ( 
        $errorWaktu['warning_count'] > 0 || 
        $errorWaktu['error_count'] > 0 
      ) 
    ) || 
    $waktu->format('H:i:s') !== $waktuAktual 
  ) { 
    throw new InvalidArgumentException( 
      'Waktu aktual serah terima tidak valid.' 
    ); 
  } 
 
  foreach ([$fileKtpBaru, $fileSimBaru] as $namaFile) { 
    if ( 
      $namaFile !== null && 
      ( 
        $namaFile === '' || 
        basename($namaFile) !== $namaFile || 
        strlen($namaFile) > 100 
      ) 
    ) { 
      throw new InvalidArgumentException( 
        'Nama file dokumen tidak valid.' 
      ); 
    } 
  } 
 
  $this->db->begin_transaction(); 
 
  try { 
    /* 
      * 1. Kunci transaksi agar dua admin tidak memproses 
      *    serah terima yang sama secara bersamaan. 
      */ 
    $stmtSewa = $this->db->prepare( 
      "SELECT 
          kode_sewa, 
          status_sewa, 
          user_plg 
        FROM t_sewa 
        WHERE kode_sewa = ? 
        FOR UPDATE"
           ); 
 
    if (!$stmtSewa) { 
      throw new RuntimeException( 
        'Pemeriksaan transaksi gagal disiapkan.' 
      ); 
    } 
 
    $stmtSewa->bind_param('s', $kodeSewa); 
 
    if (!$stmtSewa->execute()) { 
      $stmtSewa->close(); 
 
      throw new RuntimeException( 
        'Pemeriksaan transaksi gagal dijalankan.' 
      ); 
    } 
 
    $transaksi = $stmtSewa->get_result()->fetch_assoc(); 
    $stmtSewa->close(); 
 
    if (!$transaksi) { 
      throw new DomainException( 
        'Transaksi sewa tidak ditemukan.' 
      ); 
    } 
 
    if ($transaksi['status_sewa'] !== 'disetujui') { 
      throw new DomainException( 
        'Transaksi tidak berada pada status disetujui ' 
          . 'atau telah diproses sebelumnya.' 
      ); 
    } 
 
    /* 
      * 2. Kunci dan periksa pembayaran DP. 
      *    Satu transaksi harus memiliki tepat satu record DP 
      *    yang telah diverifikasi valid. 
      */ 
    $stmtBayar = $this->db->prepare( 
      "SELECT kode_bayar, sttus_byr 
        FROM t_bayar 
        WHERE kode_sewa = ? 
          AND jns_byr = 'dp' 
        FOR UPDATE" 
    ); 
 
    if (!$stmtBayar) { 
      throw new RuntimeException( 
        'Pemeriksaan pembayaran DP gagal disiapkan.' 
      ); 
    } 
 
    $stmtBayar->bind_param('s', $kodeSewa); 
 
    if (!$stmtBayar->execute()) { 
      $stmtBayar->close(); 
 
      throw new RuntimeException( 
        'Pemeriksaan pembayaran DP gagal dijalankan.' 
      ); 
    } 
 
    $resultBayar = $stmtBayar->get_result(); 
    $daftarDp = $resultBayar->fetch_all(MYSQLI_ASSOC); 
    $stmtBayar->close(); 
 
    if (count($daftarDp) !== 1) { 
      throw new DomainException( 
        'Transaksi harus memiliki tepat satu pembayaran DP.' 
      ); 
    } 
 
    if ($daftarDp[0]['sttus_byr'] !== 'valid') {
         throw new DomainException( 
        'Pembayaran DP belum diverifikasi valid.' 
      ); 
    } 
 
    /* 
      * 3. Kunci dan periksa dokumen penyewa. 
      */ 
    $userPlg = (string) $transaksi['user_plg']; 
 
    $stmtPenyewa = $this->db->prepare( 
      "SELECT ft_ktp, ft_sim_a 
        FROM t_penyewa 
        WHERE user_plg = ? 
        FOR UPDATE" 
    ); 
 
    if (!$stmtPenyewa) { 
      throw new RuntimeException( 
        'Pemeriksaan dokumen penyewa gagal disiapkan.' 
      ); 
    } 
 
    $stmtPenyewa->bind_param('s', $userPlg); 
 
    if (!$stmtPenyewa->execute()) { 
      $stmtPenyewa->close(); 
 
      throw new RuntimeException( 
        'Pemeriksaan dokumen penyewa gagal dijalankan.' 
      ); 
    } 
 
    $dokumen = $stmtPenyewa->get_result()->fetch_assoc(); 
    $stmtPenyewa->close(); 
 
    if (!$dokumen) { 
      throw new DomainException( 
        'Data penyewa tidak ditemukan.' 
      ); 
    } 
 
    $ftKtp = $fileKtpBaru ?? $dokumen['ft_ktp']; 
    $ftSim = $fileSimBaru ?? $dokumen['ft_sim_a']; 
 
    if (empty($ftKtp) || empty($ftSim)) { 
      throw new DomainException( 
        'Dokumen KTP dan SIM A wajib tersedia sebelum serah terima.' 
      ); 
    } 
 
    /* 
      * 4. Pastikan transaksi memiliki minimal satu kendaraan. 
      */ 
    $stmtJumlah = $this->db->prepare( 
      "SELECT COUNT(*) AS jumlah 
        FROM t_pilih 
        WHERE kode_sewa = ?" 
    ); 
 
    if (!$stmtJumlah) { 
      throw new RuntimeException( 
        'Pemeriksaan kendaraan transaksi gagal disiapkan.' 
      ); 
    } 
 
    $stmtJumlah->bind_param('s', $kodeSewa); 
 
    if (!$stmtJumlah->execute()) { 
      $stmtJumlah->close(); 
 
      throw new RuntimeException( 
        'Pemeriksaan kendaraan transaksi gagal dijalankan.' 
      ); 
 } 
 
    $hasilJumlah = $stmtJumlah->get_result()->fetch_assoc(); 
    $stmtJumlah->close(); 
 
    if ((int) ($hasilJumlah['jumlah'] ?? 0) < 1) { 
      throw new DomainException( 
        'Transaksi tidak memiliki kendaraan untuk diserahterimakan.' 
      ); 
    } 
 
    /* 
      * 5. Simpan dokumen baru jika admin mengunggahnya. 
      */ 
    if ($fileKtpBaru !== null || $fileSimBaru !== null) { 
      $stmtUpdateDokumen = $this->db->prepare( 
        "UPDATE t_penyewa 
          SET ft_ktp = ?, 
              ft_sim_a = ? 
          WHERE user_plg = ?" 
      ); 
 
      if (!$stmtUpdateDokumen) { 
        throw new RuntimeException( 
          'Pembaruan dokumen penyewa gagal disiapkan.' 
        ); 
      } 
 
      $stmtUpdateDokumen->bind_param( 
        'sss', 
        $ftKtp, 
        $ftSim, 
        $userPlg 
      ); 
 
      if (!$stmtUpdateDokumen->execute()) { 
        $stmtUpdateDokumen->close(); 
 
        throw new RuntimeException( 
          'Pembaruan dokumen penyewa gagal disimpan.' 
        ); 
      } 
 
      $stmtUpdateDokumen->close(); 
    } 
 
    /* 
      * 6. Catat waktu aktual dan ubah status transaksi. 
      *    Kondisi status menjadi pengaman tambahan terhadap 
      *    pemrosesan ganda. 
      */ 
    $stmtUpdateSewa = $this->db->prepare( 
      "UPDATE t_sewa 
        SET wkt_ambil = ?, 
            status_sewa = 'aktif' 
        WHERE kode_sewa = ? 
          AND status_sewa = 'disetujui'" 
    ); 
 
    if (!$stmtUpdateSewa) { 
      throw new RuntimeException( 
        'Pembaruan status serah terima gagal disiapkan.' 
      ); 
    } 
 
    $stmtUpdateSewa->bind_param( 
      'ss', 
      $waktuAktual, 
      $kodeSewa 
    ); 
 
    if (!$stmtUpdateSewa->execute()) { 
      $stmtUpdateSewa->close(); 
      throw new RuntimeException( 
'Pembaruan status serah terima gagal disimpan.' 
); 
} 
$jumlahBerubah = $stmtUpdateSewa->affected_rows; 
$stmtUpdateSewa->close(); 
if ($jumlahBerubah !== 1) { 
throw new DomainException( 
'Serah terima gagal atau transaksi telah diproses.' 
); 
} 
$this->db->commit(); 
} catch (Throwable $e) { 
$this->db->rollback(); 
throw $e; 
} 
} 