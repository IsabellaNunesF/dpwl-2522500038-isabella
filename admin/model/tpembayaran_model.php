<?php
// admin/model/tpembayaran_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class TPembayaran
{
  private $conn;
  private $tabel_bayar   = 't_bayar';
  private $tabel_sewa    = 't_sewa';
  private $tabel_penyewa = 't_penyewa';
  private $tabel_pilih   = 't_pilih';
  private $tabel_mobil   = 't_mobil';
  private $tabel_kembali = 't_kembali';
  private $tabel_denda   = 't_denda';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // ==================== STATE TRANSITION: DIAJUKAN → DISETUJUI ====================
  // Dipanggil saat pembayaran (DP/Pelunasan) berstatus VALID.
  // Tugas:
  //   1. Ubah t_sewa.status_sewa: 'diajukan' ➔ 'disetujui'
  //   2. Ubah t_mobil.status_unit: 'tersedia' ➔ 'disewa' (untuk semua mobil di t_pilih)
  public function prosesValidasiPembayaran($kode_sewa)
  {
    // 1. Ambil data sewa
    $sewa = $this->conn->select($this->tabel_sewa, ['kode_sewa' => $kode_sewa]);
    if (empty($sewa)) return false;
    $status_sewa = $sewa[0]['status_sewa'];

    // 2. Cek apakah ada pembayaran DP atau Pelunasan yang VALID untuk kode_sewa ini
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
    $ada_pembayaran_valid = false;
    foreach ($bayar_list as $b) {
      if ($b['sttus_byr'] === 'valid' && in_array($b['jns_byr'], ['dp', 'pelunasan'])) {
        $ada_pembayaran_valid = true;
        break;
      }
    }

    // Jika tidak ada pembayaran valid sama sekali, tidak perlu proses apa-apa
    if (!$ada_pembayaran_valid) {
      return true;
    }

    // 3. Update status sewa: 'diajukan' ➔ 'disetujui'
    //    Hanya dijalankan jika status masih 'diajukan' (hindari overwrite status aktif/selesai)
    if ($status_sewa === 'diajukan') {
      $data  = ['status_sewa' => 'disetujui'];
      $kunci = ['kode_sewa' => $kode_sewa];
      $this->conn->update($this->tabel_sewa, $data, $kunci);
    }

    // 4. Update status unit mobil: 'tersedia' ➔ 'disewa'
    //    Dilakukan untuk SEMUA mobil yang terdaftar di t_pilih untuk kode_sewa ini
    $pilih_list = $this->conn->select($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
    foreach ($pilih_list as $p) {
      // Ambil data mobil untuk cek status terkini
      $mobil = $this->conn->select($this->tabel_mobil, ['no_plat' => $p['no_plat']]);
      // Hanya ubah jika status_unit masih 'tersedia'
      // (hindari overwrite jika mobil sedang disewa transaksi lain)
      if (!empty($mobil) && $mobil[0]['status_unit'] === 'tersedia') {
        $data_mobil  = ['status_unit' => 'disewa'];
        $kunci_mobil = ['no_plat' => $p['no_plat']];
        $this->conn->update($this->tabel_mobil, $data_mobil, $kunci_mobil);
      }
    }

    return true;
  }

  // ==================== AMBIL DATA DENDA BERDASARKAN KODE SEWA ====================
  public function getDendaByKodeSewa($kode_sewa)
  {
    // Cari kode_bayar yang terkait dengan kode_sewa
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);

    foreach ($bayar_list as $b) {
      // Cari kode_kembali yang terkait dengan kode_bayar
      $kembali_list = $this->conn->select($this->tabel_kembali, ['kode_bayar' => $b['kode_bayar']]);

      foreach ($kembali_list as $k) {
        // Cari denda berdasarkan kode_kembali
        $denda_list = $this->conn->select($this->tabel_denda, ['kode_kembali' => $k['kode_kembali']]);

        if (!empty($denda_list)) {
          return $denda_list[0]; // Kembalikan data denda pertama yang ditemukan
        }
      }
    }

    return null; // Tidak ada denda
  }

  // ==================== AMBIL KODE DENDA TERAKHIR ====================
  public function getKodeDendaTerakhir($kode_sewa)
  {
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);

    foreach ($bayar_list as $b) {
      $kembali_list = $this->conn->select($this->tabel_kembali, ['kode_bayar' => $b['kode_bayar']]);

      foreach ($kembali_list as $k) {
        $denda_list = $this->conn->select($this->tabel_denda, ['kode_kembali' => $k['kode_kembali']]);

        if (!empty($denda_list)) {
          return $denda_list[0]['kode_denda'];
        }
      }
    }

    return null;
  }

  // ==================== CEK APABILA BISA CETAK ====================
  // Syarat:
  // 1. Pembayaran valid dan total sudah sesuai dengan total sewa
  // 2. Mobil sudah dikembalikan (ada di t_kembali)
  // 3. Jika terlambat, harus ada record di t_denda
  public function cekBisaCetak($kode_sewa)
  {
    // 1. Cek total tagihan
    $total_sewa = $this->getTotalTagihan($kode_sewa);

    // 2. Cek total pembayaran valid
    $total_bayar = $this->getTotalBayarValid($kode_sewa);

    // 3. Cek apakah sudah lunas
    if ($total_bayar < $total_sewa) {
      return false;
    }

    // 4. Cek apakah sudah dikembalikan
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
    $ada_kembali = false;
    $ada_denda_diperlukan = false;
    $ada_denda_record = false;

    foreach ($bayar_list as $b) {
      $kembali_list = $this->conn->select($this->tabel_kembali, ['kode_bayar' => $b['kode_bayar']]);

      foreach ($kembali_list as $k) {
        $ada_kembali = true;

        // Jika terlambat, cek apakah ada denda
        if ($k['stts_blk'] === 'terlambat') {
          $ada_denda_diperlukan = true;

          // Cek di tabel denda
          $denda_list = $this->conn->select($this->tabel_denda, ['kode_kembali' => $k['kode_kembali']]);
          if (!empty($denda_list)) {
            $ada_denda_record = true;
          }
        }
      }
    }

    // Jika belum ada pengembalian, tidak bisa cetak
    if (!$ada_kembali) {
      return false;
    }

    // Jika terlambat tapi tidak ada record denda, tidak bisa cetak
    if ($ada_denda_diperlukan && !$ada_denda_record) {
      return false;
    }

    return true;
  }

  // ==================== GENERATE KODE BAYAR ====================
  public function generateKodeBayar()
  {
    $tgl    = date('Ymd');
    $prefix = 'PAY/' . $tgl . '/';
    $semua  = $this->conn->select($this->tabel_bayar);
    $nomor  = 1;

    foreach ($semua as $row) {
      if (strpos($row['kode_bayar'], $prefix) === 0) {
        $parts = explode('/', $row['kode_bayar']);
        $urut  = (int)$parts[2];
        if ($urut >= $nomor) {
          $nomor = $urut + 1;
        }
      }
    }
    return $prefix . str_pad($nomor, 2, '0', STR_PAD_LEFT);
  }

  // ==================== CRUD t_bayar ====================
  public function getAll()
  {
    return $this->conn->select($this->tabel_bayar);
  }

  public function getByKode($kode_bayar)
  {
    return $this->conn->select($this->tabel_bayar, ['kode_bayar' => $kode_bayar]);
  }

  public function getByKodeSewa($kode_sewa)
  {
    return $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
  }

  public function tambah($data)
  {
    return $this->conn->insert($this->tabel_bayar, $data);
  }

  public function ubah($data, $kode_bayar_lama)
  {
    $kunci = ['kode_bayar' => $kode_bayar_lama];
    return $this->conn->update($this->tabel_bayar, $data, $kunci);
  }

  public function hapus($kode_bayar)
  {
    $kunci = ['kode_bayar' => $kode_bayar];
    return $this->conn->delete($this->tabel_bayar, $kunci);
  }

  public function updateStatus($kode_bayar, $status)
  {
    $data = [
      'sttus_byr'   => $status,
      'tgl_wkt_ver' => date('Y-m-d H:i:s')
    ];
    $kunci = ['kode_bayar' => $kode_bayar];
    return $this->conn->update($this->tabel_bayar, $data, $kunci);
  }

  // ==================== DATA RELASI ====================
  public function getSewa()
  {
    return $this->conn->select($this->tabel_sewa);
  }

  public function getPenyewa()
  {
    return $this->conn->select($this->tabel_penyewa);
  }

  public function getPilihByKode($kode_sewa)
  {
    return $this->conn->select($this->tabel_pilih, ['kode_sewa' => $kode_sewa]);
  }

  public function getMobil()
  {
    return $this->conn->select($this->tabel_mobil);
  }

  // ==================== HELPER: HITUNG TOTAL TAGIHAN SEWA ====================
  public function getTotalTagihan($kode_sewa)
  {
    $pilih_list = $this->getPilihByKode($kode_sewa);
    $total = 0;
    foreach ($pilih_list as $pl) {
      $total += ($pl['hrg_sewa'] * $pl['n_hari']);
    }
    return $total;
  }

  // ==================== HELPER: HITUNG TOTAL DP YANG SUDAH DIBAYAR ====================
  public function getTotalDPPaid($kode_sewa)
  {
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
    $total_dp = 0;
    foreach ($bayar_list as $b) {
      if ($b['jns_byr'] === 'dp' && $b['sttus_byr'] === 'valid') {
        $total_dp += $b['nominal'];
      }
    }
    return $total_dp;
  }

  // ==================== HELPER: HITUNG TOTAL PEMBAYARAN VALID ====================
  public function getTotalBayarValid($kode_sewa)
  {
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);
    $total = 0;
    foreach ($bayar_list as $b) {
      if ($b['sttus_byr'] === 'valid') {
        $total += $b['nominal'];
      }
    }
    return $total;
  }

  // ==================== HELPER: CEK PENGEMBALIAN & DENDA ====================
  public function getInfoPengembalian($kode_sewa)
  {
    $bayar_list = $this->conn->select($this->tabel_bayar, ['kode_sewa' => $kode_sewa]);

    $info = [
      'sudah_kembali' => false,
      'stts_blk'      => null,
      'ket_blk'       => null,
      'tgl_wkt_blk'   => null,
      'ada_denda'     => false,
      'denda'         => 0,
      'telat_jam'     => 0,
      'tarif_per_jam' => 0
    ];

    foreach ($bayar_list as $b) {
      $kembali_list = $this->conn->select($this->tabel_kembali, ['kode_bayar' => $b['kode_bayar']]);

      foreach ($kembali_list as $k) {
        $info['sudah_kembali'] = true;
        $info['stts_blk']      = $k['stts_blk'];
        $info['ket_blk']       = $k['ket_blk'];
        $info['tgl_wkt_blk']   = $k['tgl_wkt_blk'];

        if ($k['stts_blk'] === 'terlambat') {
          $denda_list = $this->conn->select($this->tabel_denda, ['kode_kembali' => $k['kode_kembali']]);

          foreach ($denda_list as $d) {
            $info['ada_denda']     = true;
            $info['telat_jam']     = $d['telat_jam'];
            $info['tarif_per_jam'] = $d['tarif_per_jam'];
            $info['denda']         = $d['telat_jam'] * $d['tarif_per_jam'];
          }
        }
      }
    }

    return $info;
  }

  // ==================== HELPER: GABUNG DATA ====================
  public function getAllWithDetail()
  {
    $bayar   = $this->getAll();
    $sewa    = $this->getSewa();
    $penyewa = $this->getPenyewa();

    $map_penyewa = [];
    foreach ($penyewa as $p) {
      $map_penyewa[$p['user_plg']] = $p;
    }

    $map_sewa = [];
    foreach ($sewa as $s) {
      $map_sewa[$s['kode_sewa']] = $s;
    }

    $hasil = [];
    foreach ($bayar as $b) {
      $s = $map_sewa[$b['kode_sewa']] ?? null;

      // ✅ FILTER: Skip pembayaran pending untuk sewa yang dibatalkan
      if ($s && $s['status_sewa'] === 'batal' && $b['sttus_byr'] === 'pending') {
        continue; // Lewati baris ini, jangan masukkan ke hasil
      }

      if ($s) {
        $p = $map_penyewa[$s['user_plg']] ?? null;
        $b['nm_lngkp']    = $p ? $p['nm_lngkp'] : '-';
        $b['nik']         = $p ? $p['nik'] : '-';
        $b['tgl_mulai']   = $s['tgl_mulai'];
        $b['tgl_selesai'] = $s['tgl_selesai'];
        $b['total_sewa']  = $this->getTotalTagihan($s['kode_sewa']);
      } else {
        $b['nm_lngkp']    = '-';
        $b['nik']         = '-';
        $b['tgl_mulai']   = '-';
        $b['tgl_selesai'] = '-';
        $b['total_sewa']  = 0;
      }
      $hasil[] = $b;
    }
    return $hasil;
  }
}
