<?php

// P10: Model Pelunasan Transaksi.
// Fungsi pembayaran DP/verifikasi P9 tetap berada di Transaksi_model.
class Pembayaran_model
{
  private mysqli $db;

  public function __construct()
  {
    global $db;
    $this->db = $db;
  }

  /**
   * P10: Mengambil daftar transaksi yang dapat diproses pelunasannya.
   *
   * Total transaksi menggunakan snapshot tarif pada t_pilih.
   * Pembayaran yang mengurangi kewajiban hanya yang berstatus valid.
   */
  public function getDaftarPelunasan(): array
  {
    $stmt = $this->db->prepare(
      "SELECT
         s.kode_sewa,
         s.tgl_mulai,
         s.tgl_selesai,
         s.status_sewa,
         p.user_plg,
         p.nm_lngkp,
         p.no_hp,
         COALESCE((
           SELECT SUM(tp.hrg_sewa * tp.n_hari)
           FROM t_pilih tp
           WHERE tp.kode_sewa = s.kode_sewa
         ), 0) AS total_transaksi,
         COALESCE((
           SELECT SUM(b.nominal)
           FROM t_bayar b
           WHERE b.kode_sewa = s.kode_sewa
             AND b.sttus_byr = 'valid'
         ), 0) AS total_terbayar
       FROM t_sewa s
       INNER JOIN t_penyewa p
         ON p.user_plg = s.user_plg
       WHERE s.status_sewa IN ('disetujui', 'aktif', 'lunas')
         AND EXISTS (
           SELECT 1
           FROM t_pilih tp
           WHERE tp.kode_sewa = s.kode_sewa
         )
       ORDER BY s.tgl_mulai DESC, s.kode_sewa DESC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query daftar pelunasan gagal disiapkan.'
      );
    }

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Daftar pelunasan gagal diambil.'
      );
    }

    $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($daftar as &$item) {
      $item['total_transaksi'] = (int) $item['total_transaksi'];
      $item['total_terbayar'] = (int) $item['total_terbayar'];
      $item['sisa_kewajiban'] = max(
        0,
        $item['total_transaksi'] - $item['total_terbayar']
      );
    }
    unset($item);

    return array_values(array_filter(
      $daftar,
      static fn(array $item): bool =>
      $item['sisa_kewajiban'] > 0 ||
        $item['status_sewa'] === 'lunas'
    ));
  }

  /**
   * P10: Mengambil detail transaksi, penyewa, dan riwayat pembayaran
   * untuk halaman pelunasan.
   */
  public function getDetailPelunasan(string $kodeSewa): ?array
  {
    $kodeSewa = trim($kodeSewa);

    if ($kodeSewa === '') {
      throw new InvalidArgumentException(
        'Kode transaksi sewa wajib diisi.'
      );
    }

    $stmt = $this->db->prepare(
      "SELECT
         s.kode_sewa,
         s.tgl_mulai,
         s.tgl_selesai,
         s.wkt_ambil,
         s.status_sewa,
         p.user_plg,
         p.nm_lngkp,
         p.no_hp,
         p.nik,
         p.almt_lngkp,
         COALESCE((
           SELECT SUM(tp.hrg_sewa * tp.n_hari)
           FROM t_pilih tp
           WHERE tp.kode_sewa = s.kode_sewa
         ), 0) AS total_transaksi,
         COALESCE((
           SELECT SUM(b.nominal)
           FROM t_bayar b
           WHERE b.kode_sewa = s.kode_sewa
             AND b.sttus_byr = 'valid'
         ), 0) AS total_terbayar
       FROM t_sewa s
       INNER JOIN t_penyewa p
         ON p.user_plg = s.user_plg
       WHERE s.kode_sewa = ?
       LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query detail pelunasan gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Detail transaksi pelunasan gagal diambil.'
      );
    }

    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($detail === null) {
      return null;
    }

    $detail['total_transaksi'] = (int) $detail['total_transaksi'];
    $detail['total_terbayar'] = (int) $detail['total_terbayar'];
    $detail['sisa_kewajiban'] = max(
      0,
      $detail['total_transaksi'] - $detail['total_terbayar']
    );

    $stmt = $this->db->prepare(
      "SELECT
         kode_bayar,
         tgl_wkt_byr,
         mtode_byr,
         jns_byr,
         nominal,
         sttus_byr,
         ft_byr,
         tgl_wkt_ver
       FROM t_bayar
       WHERE kode_sewa = ?
       ORDER BY tgl_wkt_byr ASC, kode_bayar ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query riwayat pembayaran gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Riwayat pembayaran gagal diambil.'
      );
    }

    $detail['riwayat_pembayaran'] =
      $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    $stmt = $this->db->prepare(
      "SELECT
         m.no_plat,
         m.nm_mobil,
         tp.hrg_sewa,
         tp.n_hari,
         (tp.hrg_sewa * tp.n_hari) AS subtotal
       FROM t_pilih tp
       INNER JOIN t_mobil m
         ON m.no_plat = tp.no_plat
       WHERE tp.kode_sewa = ?
       ORDER BY m.nm_mobil ASC, m.no_plat ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query detail kendaraan pelunasan gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Detail kendaraan pelunasan gagal diambil.'
      );
    }

    $detail['daftar_kendaraan'] =
      $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $detail;
  }

  /**
   * P10: Membuat kode pembayaran dengan format PAY/YYYYMMDD/NN.
   * Pemanggil harus memegang named lock pembayaran harian.
   */
  private function buatKodeBayarPelunasan(string $tanggalKode): string
  {
    $pola = 'PAY/' . $tanggalKode . '/%';

    $stmt = $this->db->prepare(
      "SELECT kode_bayar
       FROM t_bayar
       WHERE kode_bayar LIKE ?
       ORDER BY kode_bayar DESC
       LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Pembuatan kode pembayaran gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $pola);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Pembuatan kode pembayaran gagal dijalankan.'
      );
    }

    $terakhir = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $nomorBerikutnya = 1;

    if ($terakhir !== null) {
      $bagianKode = explode('/', (string) $terakhir['kode_bayar']);
      $nomorBerikutnya = ((int) end($bagianKode)) + 1;
    }

    if ($nomorBerikutnya > 99) {
      throw new RuntimeException(
        'Nomor pembayaran harian telah mencapai batas 99.'
      );
    }

    return sprintf('PAY/%s/%02d', $tanggalKode, $nomorBerikutnya);
  }

  /**
   * P10: Mencatat pelunasan langsung oleh admin.
   *
   * Pembayaran langsung dicatat sebagai valid.
   * Status transaksi diperbarui menjadi lunas dalam transaksi database
   * yang sama. Status kendaraan tidak diubah pada P10.
   */
  public function simpanPelunasan(
    string $kodeSewa,
    string $metodeBayar,
    ?string $namaFileBukti = null
  ): string {
    $kodeSewa = trim($kodeSewa);
    $metodeBayar = trim($metodeBayar);

    if ($kodeSewa === '') {
      throw new InvalidArgumentException(
        'Kode transaksi sewa wajib diisi.'
      );
    }

    if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
      throw new InvalidArgumentException(
        'Metode pembayaran tidak valid.'
      );
    }

    if ($metodeBayar === 'transfer') {
      $namaFileBukti = trim((string) $namaFileBukti);

      if ($namaFileBukti === '') {
        throw new InvalidArgumentException(
          'Bukti transfer wajib tersedia.'
        );
      }
    } else {
      $namaFileBukti = null;
    }

    // Kunci yang sama dipakai untuk seluruh pembuatan kode pembayaran
    // pelunasan agar nomor urut harian tidak berbenturan.
    $namaKunci = 'dpwl_payment_code_sequence';

    $kunciDiperoleh = false;
    $transaksiDimulai = false;

    try {
      $stmt = $this->db->prepare(
        'SELECT GET_LOCK(?, 10) AS lock_status'
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Kunci pembuatan kode pembayaran gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $namaKunci);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Kunci pembuatan kode pembayaran gagal dijalankan.'
        );
      }

      $hasilKunci = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ((int) ($hasilKunci['lock_status'] ?? 0) !== 1) {
        throw new RuntimeException(
          'Proses pembayaran sedang digunakan. Silakan coba kembali.'
        );
      }

      $kunciDiperoleh = true;

      $this->db->begin_transaction();
      $transaksiDimulai = true;

      // Kunci transaksi agar dua permintaan pelunasan tidak berjalan
      // bersamaan untuk transaksi yang sama.
      $stmt = $this->db->prepare(
        "SELECT
           s.kode_sewa,
           s.status_sewa,
           COALESCE((
             SELECT SUM(tp.hrg_sewa * tp.n_hari)
             FROM t_pilih tp
             WHERE tp.kode_sewa = s.kode_sewa
           ), 0) AS total_transaksi,
           COALESCE((
             SELECT SUM(b.nominal)
             FROM t_bayar b
             WHERE b.kode_sewa = s.kode_sewa
               AND b.sttus_byr = 'valid'
           ), 0) AS total_terbayar
         FROM t_sewa s
         WHERE s.kode_sewa = ?
         LIMIT 1
         FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pemeriksaan transaksi pelunasan gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pemeriksaan transaksi pelunasan gagal dijalankan.'
        );
      }

      $transaksi = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($transaksi === null) {
        throw new RuntimeException(
          'Transaksi sewa tidak ditemukan.'
        );
      }

      if (!in_array(
        $transaksi['status_sewa'],
        ['disetujui', 'aktif', 'lunas'],
        true
      )) {
        throw new RuntimeException(
          'Status transaksi belum memenuhi syarat pelunasan.'
        );
      }

      $totalTransaksi = (int) $transaksi['total_transaksi'];
      $totalTerbayar = (int) $transaksi['total_terbayar'];
      $sisaKewajiban = $totalTransaksi - $totalTerbayar;

      if ($totalTransaksi <= 0 || $sisaKewajiban <= 0) {
        throw new RuntimeException(
          'Transaksi tidak memiliki sisa kewajiban untuk dilunasi.'
        );
      }

      if ($sisaKewajiban > 8388607) {
        throw new RuntimeException(
          'Sisa kewajiban melebihi kapasitas kolom nominal pada database.'
        );
      }

      // Pastikan transaksi belum memiliki record pelunasan.
      $stmt = $this->db->prepare(
        "SELECT kode_bayar
         FROM t_bayar
         WHERE kode_sewa = ?
           AND jns_byr = 'pelunasan'
         LIMIT 1
         FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pemeriksaan pelunasan sebelumnya gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pemeriksaan pelunasan sebelumnya gagal dijalankan.'
        );
      }

      $pelunasanAda = $stmt->get_result()->fetch_assoc() !== null;
      $stmt->close();

      if ($pelunasanAda) {
        throw new RuntimeException(
          'Transaksi ini sudah memiliki record pelunasan.'
        );
      }

      $hasilWaktu = $this->db->query(
        "SELECT
           DATE_FORMAT(NOW(), '%Y%m%d') AS tanggal_kode,
           NOW() AS waktu_pembayaran"
      );

      if (!$hasilWaktu) {
        throw new RuntimeException(
          'Waktu pembayaran gagal dibaca.'
        );
      }

      $waktu = $hasilWaktu->fetch_assoc();
      $hasilWaktu->free();

      $tanggalKode = (string) ($waktu['tanggal_kode'] ?? '');
      $waktuPembayaran = (string) ($waktu['waktu_pembayaran'] ?? '');

      if (
        !preg_match('/^\d{8}$/', $tanggalKode) ||
        $waktuPembayaran === ''
      ) {
        throw new RuntimeException(
          'Waktu pembayaran tidak valid.'
        );
      }

      $kodeBayar = $this->buatKodeBayarPelunasan($tanggalKode);

      $stmt = $this->db->prepare(
        "INSERT INTO t_bayar (
           kode_bayar,
           tgl_wkt_byr,
           ft_byr,
           mtode_byr,
           jns_byr,
           nominal,
           sttus_byr,
           tgl_wkt_ver,
           kode_sewa
         ) VALUES (?, ?, ?, ?, 'pelunasan', ?, 'valid', NOW(), ?)"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pencatatan pelunasan gagal disiapkan.'
        );
      }

      $stmt->bind_param(
        'ssssis',
        $kodeBayar,
        $waktuPembayaran,
        $namaFileBukti,
        $metodeBayar,
        $sisaKewajiban,
        $kodeSewa
      );

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pelunasan gagal disimpan.'
        );
      }

      $stmt->close();

      // Pelunasan berhasil dicatat. Status menjadi lunas hanya jika
      // transaksi belum aktif; transaksi aktif tetap berstatus aktif.
      if ($transaksi['status_sewa'] !== 'aktif') {
        $stmt = $this->db->prepare(
          "UPDATE t_sewa
           SET status_sewa = 'lunas'
           WHERE kode_sewa = ?
             AND status_sewa IN ('disetujui', 'lunas')"
        );

        if (!$stmt) {
          throw new RuntimeException(
            'Pembaruan status transaksi menjadi lunas gagal disiapkan.'
          );
        }

        $stmt->bind_param('s', $kodeSewa);

        if (!$stmt->execute()) {
          $stmt->close();
          throw new RuntimeException(
            'Status transaksi gagal diperbarui menjadi lunas.'
          );
        }

        if ($stmt->affected_rows !== 1) {
          $stmt->close();
          throw new RuntimeException(
            'Status transaksi tidak berubah menjadi lunas. '
              . 'Periksa kembali status transaksi.'
          );
        }

        $stmt->close();
      }

      $this->db->commit();
      $transaksiDimulai = false;

      return $kodeBayar;
    } catch (Throwable $e) {
      if ($transaksiDimulai) {
        $this->db->rollback();
      }

      throw $e;
    } finally {
      if ($kunciDiperoleh) {
        $stmt = $this->db->prepare(
          'SELECT RELEASE_LOCK(?)'
        );

        if ($stmt) {
          $stmt->bind_param('s', $namaKunci);
          $stmt->execute();
          $stmt->close();
        }
      }
    }
  }
}
