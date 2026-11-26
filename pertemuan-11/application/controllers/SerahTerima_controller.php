<?php

require_once APPPATH . 'models/SerahTerima_model.php';

class SerahTerima_controller extends Controller
{
  private SerahTerima_model $model;

  public function __construct()
  {
    require_once APPPATH . 'helpers/auth_helper.php';

    $this->model = new SerahTerima_model();
  }

  /** 
   * Menampilkan daftar transaksi yang siap diserahterimakan. 
   */
  public function index(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    try {
      $daftarSerahTerima = $this->model
        ->getDaftarSerahTerima();

      $flash = $_SESSION['flash_serah_terima'] ?? null;
      unset($_SESSION['flash_serah_terima']);

      $this->view('admin/serah_terima', [
        'username' => $_SESSION['admin_username'],
        'daftarSerahTerima' => $daftarSerahTerima,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Daftar serah terima gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Daftar serah terima belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }
  /** 
   * Menampilkan detail transaksi yang akan diserahterimakan. 
   */
  public function detail(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    try {
      $detail = $this->model
        ->getDetailSerahTerima($kodeSewa);

      if ($detail === null) {
        http_response_code(404);
        exit('Data transaksi tidak ditemukan.');
      }

      $flash = $_SESSION['flash_serah_terima'] ?? null;
      unset($_SESSION['flash_serah_terima']);

      $this->view('admin/serah_terima_detail', [
        'username' => $_SESSION['admin_username'],
        'detail' => $detail,
        'daftarKendaraan' => $detail['daftar_kendaraan'],
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Detail serah terima gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Detail serah terima belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }
  /** 
   * Memproses serah terima seluruh kendaraan dalam satu transaksi. 
   * KTP dan SIM A wajib tersedia sebelum proses dilanjutkan. 
   */
  public function proses(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('POST');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }
    $detailUrl = 'admin/serah-terima/detail/'
      . str_replace('/', '~', $kodeSewa);

    $detailSebelum = null;
    $fileBaru = [];

    try {
      $detailSebelum = $this->model
        ->getDetailSerahTerima($kodeSewa);

      if ($detailSebelum === null) {
        throw new DomainException(
          'Data transaksi tidak ditemukan.'
        );
      }

      $uploadKtp = $this->uploadDokumenPenyewa('ft_ktp');

      if (!$uploadKtp['success']) {
        $this->redirectDenganPesan(
          $detailUrl,
          'error',
          $uploadKtp['error']
        );
      }

      if ($uploadKtp['filename'] !== null) {
        $fileBaru['ft_ktp'] = $uploadKtp['filename'];
      }

      $uploadSim = $this->uploadDokumenPenyewa('ft_sim_a');

      if (!$uploadSim['success']) {
        $this->hapusFileBaru($fileBaru);

        $this->redirectDenganPesan(
          $detailUrl,
          'error',
          $uploadSim['error']
        );
      }

      if ($uploadSim['filename'] !== null) {
        $fileBaru['ft_sim_a'] = $uploadSim['filename'];
      }

      $waktuAktual = (new DateTimeImmutable(
        'now',
        new DateTimeZone('Asia/Jakarta')
      ))->format('H:i:s');

      $this->model->prosesSerahTerima(
        $kodeSewa,
        $waktuAktual,
        $fileBaru['ft_ktp'] ?? null,
        $fileBaru['ft_sim_a'] ?? null
      );

      $fileLama = [];

      foreach (['ft_ktp', 'ft_sim_a'] as $field) {
        $namaBaru = $fileBaru[$field] ?? null;
        $namaLama = $detailSebelum[$field] ?? null;

        if (
          $namaBaru !== null &&
          is_string($namaLama) &&
          $namaLama !== '' &&
          $namaLama !== $namaBaru
        ) {
          $fileLama[$field] = $namaLama;
        }
      }
      $this->hapusFileLama($fileLama);

      $this->redirectDenganPesan(
        'admin/serah-terima',
        'success',
        'Serah terima transaksi '
          . $kodeSewa
          . ' berhasil dicatat. Status transaksi menjadi aktif.'
      );
    } catch (Throwable $e) {
      error_log(
        'Proses serah terima gagal untuk '
          . $kodeSewa . ': ' . $e->getMessage()
      );

      $this->hapusFileBaru($fileBaru);

      $pesan = $e instanceof DomainException
        ? $e->getMessage()
        : 'Serah terima gagal diproses. '
        . 'Periksa kembali persyaratan transaksi dan coba lagi.';

      $this->redirectDenganPesan(
        $detailUrl,
        'error',
        $pesan
      );
    }
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
  /** 
   * Mengunggah dokumen KTP atau SIM A. 
   * Format yang diterima: JPEG, PNG, dan WEBP. 
   * Ukuran maksimum: 5 MB. 
   */
  private function uploadDokumenPenyewa(string $field): array
  {
    if (!in_array($field, ['ft_ktp', 'ft_sim_a'], true)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Jenis dokumen tidak valid.'
      ];
    }

    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE)
      === UPLOAD_ERR_NO_FILE
    ) {
      return [
        'success' => true,
        'filename' => null,
        'error' => null
      ];
    }

    $file = $_FILES[$field];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Unggah dokumen gagal. Periksa ukuran file dan konfigurasi PHP.'
      ];
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $ukuran = (int) ($file['size'] ?? 0);

    if (
      $tmpName === '' ||
      !is_uploaded_file($tmpName) ||
      $ukuran < 1 ||
      $ukuran > 5 * 1024 * 1024
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Dokumen harus berukuran lebih dari 0 byte dan maksimal 5 MB.'
      ];
    }

    if (!class_exists('finfo')) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Ekstensi Fileinfo PHP belum tersedia pada server.'
      ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $formatDiizinkan = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp'
    ];

    if (
      !isset($formatDiizinkan[$mime]) ||
      @getimagesize($tmpName) === false
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Format dokumen harus JPEG, PNG, atau WEBP yang valid.'
      ];
    }

    $uploadDir = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa'
      . DIRECTORY_SEPARATOR;

    if (
      !is_dir($uploadDir) &&
      !mkdir($uploadDir, 0755, true) &&
      !is_dir($uploadDir)
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan dokumen penyewa tidak dapat dibuat.'
      ];
    }

    if (!is_writable($uploadDir)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan dokumen penyewa tidak dapat ditulis.'
      ];
    }

    try {
      $namaFile = $field . '_' . date('YmdHis')
        . '_' . bin2hex(random_bytes(8))
        . '.' . $formatDiizinkan[$mime];
    } catch (Throwable $e) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Nama file dokumen gagal dibuat.'
      ];
    }

    if (!move_uploaded_file($tmpName, $uploadDir . $namaFile)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Dokumen gagal dipindahkan ke folder penyimpanan.'
      ];
    }

    return [
      'success' => true,
      'filename' => $namaFile,
      'error' => null
    ];
  }
  /** 
   * Menghapus file baru jika proses database gagal. 
   */
  private function hapusFileBaru(array $files): void
  {
    foreach ($files as $field => $filename) {
      $this->hapusSatuFile($field, $filename);
    }
  }

  /** 
   * Menghapus file lama setelah file baru berhasil disimpan. 
   */
  private function hapusFileLama(array $files): void
  {
    foreach ($files as $field => $filename) {
      $this->hapusSatuFile($field, $filename);
    }
  }

  /** 
   * Membatasi penghapusan hanya pada nama file yang dibuat 
   * oleh mekanisme unggah dokumen P11. 
   */
  private function hapusSatuFile(
    string $field,
    string $filename
  ): void {
    if (!in_array($field, ['ft_ktp', 'ft_sim_a'], true)) {
      return;
    }

    $pola = '/^' . preg_quote($field, '/')
      . '_\d{14}_[a-f0-9]{16}\.(jpg|png|webp)$/';

    if (!preg_match($pola, $filename)) {
      return;
    }

    $path = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa'
      . DIRECTORY_SEPARATOR . $filename;

    if (is_file($path)) {
      @unlink($path);
    }
  }
  /** 
   * Menyimpan flash message dan menerapkan PRG. 
   */
  private function redirectDenganPesan(
    string $uri,
    string $jenis,
    string $pesan
  ): never {
    $_SESSION['flash_serah_terima'] = [
      'type' => $jenis,
      'message' => $pesan
    ];

    header('Location: ' . site_url($uri));
    exit;
  }
}
