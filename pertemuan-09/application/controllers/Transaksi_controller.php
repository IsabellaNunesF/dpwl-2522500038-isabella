
<?php

require_once APPPATH . 'models/Transaksi_model.php';
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';

class Transaksi_controller extends Controller
{
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    $this->transaksiModel = new Transaksi_model();
  }

  public function tambah(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    $noPlat = trim((string) ($_POST['no_plat'] ?? ''));
    $asal = (string) ($_POST['return_to'] ?? 'katalog');
    $asal = $asal === 'detail' ? 'detail' : 'katalog';

    if ($noPlat === '') {
      $this->redirectTambahDenganPesan(
        $asal,
        $noPlat,
        'error',
        'Kendaraan wajib dipilih terlebih dahulu.'
      );
    }

    try {
      $this->transaksiModel->tambahKeKeranjang(
        $userPlg,
        $noPlat
      );

      $this->redirectTambahDenganPesan(
        $asal,
        $noPlat,
        'success',
        'Mobil berhasil ditambahkan ke keranjang.'
      );
    } catch (InvalidArgumentException $e) {
      $this->redirectTambahDenganPesan(
        $asal,
        $noPlat,
        'error',
        $e->getMessage()
      );
    } catch (Throwable $e) {
      error_log('Tambah keranjang gagal: ' . $e->getMessage());

      $this->redirectTambahDenganPesan(
        $asal,
        $noPlat,
        'error',
        $this->pesanBisnisAman($e->getMessage())
      );
    }
  }

  private function wajibLoginPenyewa(): string
  {
    $userPlg = trim(
      (string) ($_SESSION['penyewa_user_plg'] ?? '')
    );

    if ($userPlg === '') {
      $_SESSION['flash_penyewa_nav'] =
        'Silakan login terlebih dahulu untuk mengakses transaksi.';

      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    return $userPlg;
  }

  private function wajibMetode(string $metodeDiizinkan): void
  {
    $metodeAktif = strtoupper(
      (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
    );

    if ($metodeAktif !== $metodeDiizinkan) {
      http_response_code(405);
      header('Allow: ' . $metodeDiizinkan);
      exit('Method Not Allowed');
    }
  }

  private function redirectTambahDenganPesan(
    string $asal,
    string $noPlat,
    string $jenis,
    string $pesan
  ): never {
    $_SESSION['flash_transaksi'] = [
      'type' => $jenis,
      'message' => $pesan
    ];

    if ($asal === 'detail' && $noPlat !== '') {
      $uri = 'katalog/detail/' . rawurlencode($noPlat);
    } else {
      $uri = 'katalog';
    }

    header('Location: ' . site_url($uri));
    exit;
  }

  private function pesanBisnisAman(string $pesan): string
  {
    $pesanDikenal = [
      'Kendaraan tidak berstatus tersedia.',
      'Kendaraan sudah ada dalam keranjang ini.',
      'Kendaraan tidak ditemukan.',
      'Nomor transaksi harian telah mencapai batas 99.',
      'Kunci proses tidak diperoleh. Silakan coba lagi.'
    ];

    return in_array($pesan, $pesanDikenal, true)
      ? $pesan
      : 'Permintaan transaksi belum berhasil. Periksa data dan coba kembali.';
  }

  public function keranjang(string $kodeSewa = ''): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    $kodeSewa = trim(
      $kodeSewa !== ''
        ? $kodeSewa
        : (string) ($_GET['kode_sewa'] ?? '')
    );

    if ($kodeSewa === '') {
      try {
        $kodeSewa = $this->transaksiModel
          ->getKodeKeranjangAktif($userPlg);

        if ($kodeSewa === null || $kodeSewa === '') {
          $_SESSION['flash_transaksi'] = [
            'type' => 'info',
            'message' => 'Keranjang Anda masih kosong.'
          ];

          header('Location: ' . site_url('katalog'));
          exit;
        }
      } catch (Throwable $e) {
        die('ERROR: ' . $e->getMessage() .
          '<br>FILE: ' . $e->getFile() .
          '<br>LINE: ' . $e->getLine());
      }
    }

    try {
      $keranjang = $this->transaksiModel->getKeranjang(
        $kodeSewa,
        $userPlg
      );

      if ($keranjang === null) {
        http_response_code(404);
        exit('Keranjang tidak ditemukan atau bukan milik akun ini.');
      }

      $flash = $_SESSION['flash_transaksi'] ?? null;
      unset($_SESSION['flash_transaksi']);

      $jumlahKeranjang =
        $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

      $this->view('transaksi/keranjang', [
        'keranjang' => $keranjang,
        'flash' => $flash,
        'jumlahKeranjang' => $jumlahKeranjang
      ]);
    } catch (Throwable $e) {
      die('ERROR: ' . $e->getMessage() .
        '<br>FILE: ' . $e->getFile() .
        '<br>LINE: ' . $e->getLine());
    }
  }

  public function perbaruiPeriode(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    $kodeSewa = trim((string) ($_POST['kode_sewa'] ?? ''));
    $tglMulai = trim((string) ($_POST['tgl_mulai'] ?? ''));
    $tglSelesai = trim((string) ($_POST['tgl_selesai'] ?? ''));
    $wktAmbil = trim((string) ($_POST['wkt_ambil'] ?? ''));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi wajib diisi.');
    }

    try {
      $this->transaksiModel->perbaruiPeriodeKeranjang(
        $kodeSewa,
        $userPlg,
        $tglMulai,
        $tglSelesai,
        $wktAmbil
      );

      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        'success',
        'Periode dan waktu pengambilan berhasil diperbarui.'
      );
    } catch (InvalidArgumentException $e) {
      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        'error',
        $e->getMessage()
      );
    } catch (Throwable $e) {
      error_log('Pembaruan periode keranjang gagal: ' . $e->getMessage());

      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        'error',
        $this->pesanBisnisAman($e->getMessage())
      );
    }
  }

  public function hapus(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    $kodeSewa = trim((string) ($_POST['kode_sewa'] ?? ''));
    $noPlat = trim((string) ($_POST['no_plat'] ?? ''));

    if ($kodeSewa === '' || $noPlat === '') {
      http_response_code(400);
      exit('Kode transaksi dan kendaraan wajib diisi.');
    }

    try {
      $berhasil = $this->transaksiModel->hapusDariKeranjang(
        $kodeSewa,
        $userPlg,
        $noPlat
      );

      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        $berhasil ? 'success' : 'error',
        $berhasil
          ? 'Kendaraan berhasil dihapus dari keranjang.'
          : 'Kendaraan tidak ditemukan dalam keranjang atau keranjang tidak dapat diubah.'
      );
    } catch (Throwable $e) {
      error_log('Hapus item keranjang gagal: ' . $e->getMessage());

      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        'error',
        'Kendaraan belum berhasil dihapus. Silakan coba kembali.'
      );
    }
  }

  public function checkout(): void
  {
    $userPlg = $this->wajibLoginPenyewa();

    $metode = strtoupper(
      (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
    );

    if ($metode === 'GET') {
      $this->wajibMetode('GET');

      $kodeSewa = trim(
        (string) ($_GET['kode_sewa'] ?? '')
      );

      if ($kodeSewa === '') {
        $this->redirectDenganPesan(
          'transaksi/keranjang',
          'error',
          'Kode transaksi tidak ditemukan. Periksa kembali keranjang Anda.'
        );
      }

      try {
        $keranjang = $this->transaksiModel->getKeranjang(
          $kodeSewa,
          $userPlg
        );

        if ($keranjang === null) {
          http_response_code(404);
          exit('Keranjang tidak ditemukan, bukan milik akun ini, atau tidak lagi berstatus keranjang.');
        }

        if (empty($keranjang['detail'])) {
          $this->redirectDenganPesan(
            'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
            'error',
            'Keranjang kosong. Tambahkan kendaraan sebelum checkout.'
          );
        }

        $total = (int) ($keranjang['total'] ?? 0);
        $dp = (int) round($total * 30 / 100);
        $sisa = $total - $dp;

        $jumlahKeranjang =
          $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

        $this->view('transaksi/konfirmasi', [
          'keranjang' => $keranjang,
          'total' => $total,
          'dp' => $dp,
          'sisa' => $sisa,
          'jumlahKeranjang' => $jumlahKeranjang
        ]);

        return;
      } catch (Throwable $e) {
        die('ERROR: ' . $e->getMessage() .
          '<br>FILE: ' . $e->getFile() .
          '<br>LINE: ' . $e->getLine());
      }
    }

    $this->wajibMetode('POST');

    $kodeSewa = trim(
      (string) ($_POST['kode_sewa'] ?? '')
    );

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi wajib diisi.');
    }

    try {
      $berhasil = $this->transaksiModel->checkout(
        $kodeSewa,
        $userPlg
      );

      if (!$berhasil) {
        $this->redirectDenganPesan(
          'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
          'error',
          'Checkout belum berhasil. Periksa kembali keranjang Anda.'
        );
      }

      // P9: setelah checkout, penyewa langsung diarahkan untuk membayar DP.
      $this->redirectDenganPesan(
        'transaksi/pembayaran?kode_sewa=' . rawurlencode($kodeSewa),
        'success',
        'Transaksi berhasil diajukan. Silakan transfer DP dan unggah bukti pembayaran.'
      );
    } catch (Throwable $e) {
      error_log('Checkout transaksi gagal: ' . $e->getMessage());

      $this->redirectDenganPesan(
        'transaksi/keranjang?kode_sewa=' . rawurlencode($kodeSewa),
        'error',
        $this->pesanBisnisAman($e->getMessage())
      );
    }
  }

  private function redirectDenganPesan(
    string $uri,
    string $jenis,
    string $pesan
  ): never {
    $_SESSION['flash_transaksi'] = [
      'type' => $jenis,
      'message' => $pesan
    ];

    header('Location: ' . site_url($uri));
    exit;
  }
  /* ================= P9: PEMBAYARAN AWAL (DP) ================= */

  private const BUKTI_MAX = 5242880; // 5 MB

  private const BUKTI_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
  ];

  // Daftar transaksi milik penyewa.
  public function saya(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    try {
      $daftar = $this->transaksiModel->getTransaksiPenyewa($userPlg);
      $flash = $_SESSION['flash_transaksi'] ?? null;
      unset($_SESSION['flash_transaksi']);

      $this->view('transaksi/saya', [
        'daftar' => $daftar,
        'flash' => $flash,
        'jumlahKeranjang' =>
        $this->transaksiModel->getJumlahMobilKeranjang($userPlg)
      ]);
    } catch (Throwable $e) {
      error_log('Daftar transaksi gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Daftar transaksi belum dapat ditampilkan.');
    }
  }

  // Halaman pembayaran DP: ringkasan + status + form unggah bukti.
  public function pembayaran(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    $kodeSewa = trim((string) ($_GET['kode_sewa'] ?? ''));
    if ($kodeSewa === '') {
      $this->redirectDenganPesan(
        'transaksi/saya',
        'error',
        'Kode transaksi tidak ditemukan.'
      );
    }

    try {
      $transaksi = $this->transaksiModel
        ->getTransaksiDenganDp($kodeSewa, $userPlg);

      if ($transaksi === null) {
        http_response_code(404);
        exit('Transaksi tidak ditemukan atau bukan milik akun ini.');
      }

      $flash = $_SESSION['flash_transaksi'] ?? null;
      unset($_SESSION['flash_transaksi']);

      $this->view('transaksi/pembayaran', [
        'transaksi' => $transaksi,
        'flash' => $flash,
        'jumlahKeranjang' =>
        $this->transaksiModel->getJumlahMobilKeranjang($userPlg)
      ]);
    } catch (Throwable $e) {
      error_log('Halaman pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Halaman pembayaran belum dapat ditampilkan.');
    }
  }

  // Proses unggah bukti transfer DP.
  public function unggahBukti(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    // Total unggahan melebihi post_max_size -> $_POST & $_FILES kosong.
    if (
      empty($_POST) && empty($_FILES)
      && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
    ) {
      http_response_code(413);
      exit('Ukuran unggahan melebihi batas server (post_max_size).');
    }
    csrf_validate();

    $kodeSewa = trim((string) ($_POST['kode_sewa'] ?? ''));
    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi wajib diisi.');
    }

    $uri = 'transaksi/pembayaran?kode_sewa=' . rawurlencode($kodeSewa);

    $hasil = $this->simpanFileBukti();
    if (!$hasil['success']) {
      $this->redirectDenganPesan($uri, 'error', (string) $hasil['message']);
    }

    $namaBaru = (string) $hasil['filename'];

    try {
      $namaLama = $this->transaksiModel
        ->simpanBuktiDp($kodeSewa, $userPlg, $namaBaru);

      $this->hapusFileBukti($namaLama);

      $this->redirectDenganPesan(
        $uri,
        'success',
        'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.'
      );
    } catch (InvalidArgumentException $e) {
      $this->hapusFileBukti($namaBaru);
      $this->redirectDenganPesan($uri, 'error', $e->getMessage());
    } catch (Throwable $e) {
      $this->hapusFileBukti($namaBaru);
      error_log('Unggah bukti DP gagal: ' . $e->getMessage());
      $this->redirectDenganPesan(
        $uri,
        'error',
        'Bukti pembayaran belum berhasil disimpan. Silakan coba lagi.'
      );
    }
  }

  // Menyajikan foto bukti (dilindungi: hanya admin atau pemilik transaksi).
  public function bukti(): void
  {
    $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));

    $adminOk = is_admin_logged_in();
    $userPlg = (string) ($_SESSION['penyewa_user_plg'] ?? '');

    if (!$adminOk && $userPlg === '') {
      http_response_code(403);
      exit('Akses ditolak.');
    }

    try {
      $row = $kodeBayar === ''
        ? null
        : $this->transaksiModel->getPembayaranUntukBukti($kodeBayar);
    } catch (Throwable $e) {
      error_log('Bukti pembayaran gagal dibaca: ' . $e->getMessage());
      http_response_code(500);
      exit('Bukti pembayaran belum dapat ditampilkan.');
    }
    if ($row === null) {
      http_response_code(404);
      exit('Pembayaran tidak ditemukan.');
    }

    if (!$adminOk && !hash_equals((string) $row['user_plg'], $userPlg)) {
      http_response_code(403);
      exit('Akses ditolak.');
    }

    $nama = (string) ($row['ft_byr'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_]{8,80}\.(jpg|png|webp)$/', $nama)) {
      http_response_code(404);
      exit('Bukti pembayaran belum diunggah.');
    }

    $path = $this->folderBukti() . $nama;
    if (!is_file($path)) {
      http_response_code(404);
      exit('Berkas bukti tidak ditemukan.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (!isset(self::BUKTI_MIME[$mime])) {
      http_response_code(415);
      exit('Tipe berkas tidak didukung.');
    }

    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=0, must-revalidate');
    readfile($path);
    exit;
  }

  private function folderBukti(): string
  {
    return dirname(APPPATH) . '/uploads/bayar/';
  }

  private function simpanFileBukti(): array
  {
    $gagal = static fn(string $pesan): array =>
    ['success' => false, 'filename' => null, 'message' => $pesan];

    $file = $_FILES['ft_byr'] ?? null;

    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return $gagal('Pilih foto bukti transfer terlebih dahulu.');
    }

    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        return $gagal('Bukti transfer melebihi batas ukuran file (maksimal 5 MB).');
      case UPLOAD_ERR_PARTIAL:
        return $gagal('Bukti transfer hanya terunggah sebagian. Silakan ulangi.');
      default:
        return $gagal('Bukti transfer gagal diunggah (kesalahan konfigurasi server).');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
      return $gagal('Berkas bukti transfer tidak valid.');
    }

    if ((int) $file['size'] < 1 || (int) $file['size'] > self::BUKTI_MAX) {
      return $gagal('Bukti transfer harus berukuran lebih dari 0 byte dan maksimal 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(self::BUKTI_MIME[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return $gagal('Bukti transfer harus berupa gambar JPG, PNG, atau WEBP yang valid.');
    }

    $folder = $this->folderBukti();
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
      return $gagal('Folder penyimpanan bukti tidak dapat dibuat.');
    }
    if (!is_writable($folder)) {
      return $gagal('Folder penyimpanan bukti tidak dapat ditulis.');
    }

    $nama = 'ft_byr_' . date('YmdHis') . '_' . bin2hex(random_bytes(8))
      . '.' . self::BUKTI_MIME[$mime];

    if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
      return $gagal('Bukti transfer gagal disimpan ke server.');
    }

    return ['success' => true, 'filename' => $nama, 'message' => null];
  }

  private function hapusFileBukti(?string $nama): void
  {
    if (!$nama) {
      return;
    }

    $nama = basename($nama);
    if (!preg_match('/^[A-Za-z0-9_]{8,80}\.(jpg|png|webp)$/', $nama)) {
      return;
    }

    $path = $this->folderBukti() . $nama;
    if (is_file($path)) {
      @unlink($path);
    }
  }
}
