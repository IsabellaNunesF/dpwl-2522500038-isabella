<?php

// P9: Verifikasi Pembayaran DP oleh Admin
// P10: Pelunasan transaksi dan dokumen transaksi
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';
require_once APPPATH . 'models/Transaksi_model.php';
require_once APPPATH . 'models/Pembayaran_model.php';

class Pembayaran_controller extends Controller
{
  private Transaksi_model $transaksiModel;
  private Pembayaran_model $pembayaranModel;

  private const FILTER_STATUS = ['pending', 'valid', 'invalid'];

  // P10: ketentuan unggah bukti transfer pelunasan (sama dengan P9)
  private const BUKTI_MAX = 5242880; // 5 MB

  private const BUKTI_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
  ];

  public function __construct()
  {
    $this->transaksiModel = new Transaksi_model();
    $this->pembayaranModel = new Pembayaran_model();
  }

  // GET admin/pembayaran?status=pending|valid|invalid|semua
  public function index(): void
  {
    require_admin_login();

    $status = (string) ($_GET['status'] ?? 'pending');
    $filter = in_array($status, self::FILTER_STATUS, true) ? $status : null;
    $status = $filter ?? 'semua';

    try {
      $pembayaran = $this->transaksiModel->getDaftarPembayaran($filter);
    } catch (Throwable $e) {
      error_log('Daftar pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Daftar pembayaran belum dapat ditampilkan.');
    }

    $this->view('admin/pembayaran_view', [
      'username' => $_SESSION['admin_username'],
      'pembayaran' => $pembayaran,
      'status' => $status,
      'flash' => get_flash_message(),
      'csrf_token' => csrf_token()
    ]);
  }

  // GET admin/pembayaran/detail?kode_bayar=
  public function detail(): void
  {
    require_admin_login();

    $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));
    if ($kodeBayar === '') {
      set_flash_message('error', 'Kode pembayaran wajib diisi.');
      header('Location: ' . site_url('admin/pembayaran'));
      exit;
    }

    try {
      $pembayaran = $this->transaksiModel->getDetailPembayaran($kodeBayar);
    } catch (Throwable $e) {
      error_log('Detail pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Detail pembayaran belum dapat ditampilkan.');
    }

    if ($pembayaran === null) {
      http_response_code(404);
      exit('Pembayaran tidak ditemukan.');
    }

    $this->view('admin/pembayaran_detail', [
      'username' => $_SESSION['admin_username'],
      'pembayaran' => $pembayaran,
      'flash' => get_flash_message(),
      'csrf_token' => csrf_token()
    ]);
  }

  // POST admin/pembayaran/verifikasi (kode_bayar, aksi = valid|invalid)
  public function verifikasi(): void
  {
    require_admin_login();

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
      http_response_code(405);
      header('Allow: POST');
      exit('Method Not Allowed');
    }

    csrf_validate();

    $kodeBayar = trim((string) ($_POST['kode_bayar'] ?? ''));
    $aksi = (string) ($_POST['aksi'] ?? '');
    $kembali = (string) ($_POST['status_filter'] ?? 'pending');
    $kembali = in_array($kembali, [...self::FILTER_STATUS, 'semua'], true)
      ? $kembali
      : 'pending';

    $uri = 'admin/pembayaran?status=' . $kembali;

    if ($kodeBayar === '') {
      set_flash_message('error', 'Kode pembayaran wajib diisi.');
      header('Location: ' . site_url($uri));
      exit;
    }

    try {
      $this->transaksiModel->verifikasiPembayaran($kodeBayar, $aksi);

      set_flash_message(
        'success',
        $aksi === 'valid'
          ? 'Pembayaran ' . $kodeBayar . ' dinyatakan valid. Transaksi disetujui.'
          : 'Pembayaran ' . $kodeBayar . ' ditolak. Transaksi ditolak.'
      );
    } catch (InvalidArgumentException $e) {
      set_flash_message('error', $e->getMessage());
    } catch (Throwable $e) {
      error_log('Verifikasi pembayaran gagal: ' . $e->getMessage());
      set_flash_message(
        'error',
        'Verifikasi belum berhasil disimpan. Silakan coba lagi.'
      );
    }

    header('Location: ' . site_url($uri));
    exit;
  }

  /* ================= P10: PELUNASAN DAN DOKUMEN TRANSAKSI ================= */

  /**
   * P10: Menampilkan daftar transaksi yang masih memiliki kewajiban pelunasan.
   */
  // GET admin/pembayaran/pelunasan
  public function pelunasanIndex(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    try {
      $daftarPelunasan = $this->pembayaranModel
        ->getDaftarPelunasan();

      $this->view('admin/pelunasan', [
        'username' => $_SESSION['admin_username'],
        'daftarPelunasan' => $daftarPelunasan,
        'flash' => get_flash_message()
      ]);
    } catch (Throwable $e) {
      error_log(
        'Daftar pelunasan gagal ditampilkan: ' . $e->getMessage()
      );
      http_response_code(500);
      exit('Daftar transaksi pelunasan belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * P10: Menampilkan detail transaksi, riwayat pembayaran, dan sisa kewajiban.
   */
  // GET admin/pembayaran/pelunasan/{kode_sewa dengan ~ pengganti /}
  public function pelunasanDetail(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    try {
      $detailPelunasan = $this->pembayaranModel
        ->getDetailPelunasan($kodeSewa);

      if ($detailPelunasan === null) {
        http_response_code(404);
        exit('Transaksi sewa tidak ditemukan.');
      }

      $this->view('admin/pelunasan_detail', [
        'username' => $_SESSION['admin_username'],
        'detailPelunasan' => $detailPelunasan,
        'flash' => get_flash_message(),
        'csrf_token' => csrf_token()
      ]);
    } catch (Throwable $e) {
      error_log(
        'Detail pelunasan gagal ditampilkan: ' . $e->getMessage()
      );
      http_response_code(500);
      exit('Detail transaksi pelunasan belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * P10: Mencatat pelunasan yang diterima langsung oleh admin.
   *
   * Tunai: tidak memerlukan unggahan bukti.
   * Transfer: admin mengunggah bukti yang diterima dari penyewa.
   * Model menghitung nominal sisa kewajiban dan menyimpan pembayaran
   * dengan status valid dalam satu transaksi database.
   */
  // POST admin/pembayaran/pelunasan/proses/{kode_sewa dengan ~ pengganti /}
  public function prosesPelunasan(string $kodeSewa = ''): void
  {
    require_admin_login();
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

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));
    $metodeBayar = trim((string) ($_POST['mtode_byr'] ?? ''));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    $uriDetail = 'admin/pembayaran/pelunasan/'
      . str_replace('/', '~', $kodeSewa);

    if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
      $this->redirectDenganPesan(
        $uriDetail,
        'error',
        'Metode pembayaran harus tunai atau transfer.'
      );
    }

    $namaFileBukti = null;

    if ($metodeBayar === 'transfer') {
      $hasilUpload = $this->simpanFileBukti('ft_byr');

      if (!$hasilUpload['success']) {
        $this->redirectDenganPesan(
          $uriDetail,
          'error',
          (string) $hasilUpload['error']
        );
      }

      $namaFileBukti = (string) $hasilUpload['filename'];
    }

    try {
      $kodeBayar = $this->pembayaranModel->simpanPelunasan(
        $kodeSewa,
        $metodeBayar,
        $namaFileBukti
      );

      $this->redirectDenganPesan(
        $uriDetail,
        'success',
        'Pelunasan berhasil dicatat dengan kode pembayaran ' . $kodeBayar
          . '. Status pembayaran: valid; status transaksi: lunas.'
      );
    } catch (InvalidArgumentException $e) {
      if ($namaFileBukti !== null) {
        $this->hapusBuktiBaru($namaFileBukti);
      }

      $this->redirectDenganPesan($uriDetail, 'error', $e->getMessage());
    } catch (Throwable $e) {
      error_log(
        'Proses pelunasan gagal: ' . $e->getMessage()
      );

      if ($namaFileBukti !== null) {
        $this->hapusBuktiBaru($namaFileBukti);
      }

      $this->redirectDenganPesan(
        $uriDetail,
        'error',
        'Pelunasan belum berhasil dicatat. Pastikan transaksi masih memiliki '
          . 'sisa kewajiban dan belum pernah dilunasi.'
      );
    }
  }

  /**
   * P10: Menampilkan dokumen transaksi yang siap dicetak.
   * Data disusun Model dari t_sewa, t_penyewa, t_pilih, t_mobil,
   * dan t_bayar. Method ini hanya menyajikan data.
   */
  // GET admin/pembayaran/dokumen/{kode_sewa dengan ~ pengganti /}
  public function dokumenTransaksi(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    try {
      $detailPelunasan = $this->pembayaranModel
        ->getDetailPelunasan($kodeSewa);

      if ($detailPelunasan === null) {
        http_response_code(404);
        exit('Data transaksi tidak ditemukan.');
      }

      $this->view('admin/dokumen_transaksi', [
        'detailPelunasan' => $detailPelunasan
      ]);
    } catch (Throwable $e) {
      error_log(
        'Dokumen transaksi gagal ditampilkan: ' . $e->getMessage()
      );
      http_response_code(500);
      exit('Dokumen transaksi belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /* ---------------- Helper controller (P10) ---------------- */

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

  // Pola Post-Redirect-Get (PRG). Notifikasi memakai flash_helper P9.
  private function redirectDenganPesan(
    string $uri,
    string $jenis,
    string $pesan
  ): never {
    set_flash_message($jenis, $pesan);

    header('Location: ' . site_url($uri));
    exit;
  }

  private function folderBukti(): string
  {
    return dirname(APPPATH) . '/uploads/bayar/';
  }

  /**
   * Memvalidasi dan menyimpan bukti transfer dari field $_FILES[$namaField].
   * Validasi: ukuran maksimal 5 MB, MIME JPEG/PNG/WEBP, dan struktur gambar.
   *
   * @return array{success: bool, filename: ?string, error: ?string}
   */
  private function simpanFileBukti(string $namaField = 'ft_byr'): array
  {
    $gagal = static fn(string $pesan): array =>
    ['success' => false, 'filename' => null, 'error' => $pesan];

    $file = $_FILES[$namaField] ?? null;

    if (
      $file === null
      || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
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

    if (
      !isset(self::BUKTI_MIME[$mime])
      || @getimagesize($file['tmp_name']) === false
    ) {
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

    return ['success' => true, 'filename' => $nama, 'error' => null];
  }

  // Menghapus berkas bukti yang baru diunggah bila pencatatan gagal.
  private function hapusBuktiBaru(?string $nama): void
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
