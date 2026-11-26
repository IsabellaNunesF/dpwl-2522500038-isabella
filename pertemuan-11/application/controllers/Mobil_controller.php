<?php

// P5: Manajemen Kendaraan (Admin) - CRUD kendaraan beserta 3 foto
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'models/Mobil_model.php';

class Mobil_controller extends Controller
{
  private Mobil_model $mobilModel;

  private const MAX_FILE_SIZE = 5242880; // 5 MB per foto (sesuai teks di mobil_view)

  private const ALLOWED_MIME = [
    'image/jpeg'  => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png'   => 'png',
    'image/webp'  => 'webp'
  ];

  private const FOTO_FIELD = [
    'ft_depan'    => 'Foto Depan',
    'ft_blkg'     => 'Foto Belakang',
    'ft_interior' => 'Foto Interior'
  ];

  private const JENIS_MOBIL = ['SUV', 'MPV', 'City Car'];
  private const TRANSMISI   = ['AT', 'MT'];
  private const STATUS_UNIT = ['tersedia', 'disewa', 'tidaktersedia'];

  public function __construct()
  {
    $this->mobilModel = new Mobil_model();
  }

  /* ================= HELPER PRIVAT ================= */

  private function folderFoto(): string
  {
    return dirname(APPPATH) . '/uploads/mobil/';
  }

  private function uploadFoto(string $field): array
  {
    $label = self::FOTO_FIELD[$field] ?? 'Foto';

    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return ['success' => true, 'filename' => null, 'message' => null];
    }

    $file = $_FILES[$field];

    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        return ['success' => false, 'filename' => null, 'message' => $label . ' melebihi batas ukuran file (maksimal 5 MB, dan tidak boleh melebihi upload_max_filesize di php.ini).'];
      case UPLOAD_ERR_PARTIAL:
        return ['success' => false, 'filename' => null, 'message' => $label . ' hanya terunggah sebagian. Silakan ulangi.'];
      default:
        return ['success' => false, 'filename' => null, 'message' => $label . ' gagal diunggah (kesalahan konfigurasi server).'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' tidak valid.'];
    }

    if ((int) $file['size'] < 1 || (int) $file['size'] > self::MAX_FILE_SIZE) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' harus berukuran lebih dari 0 byte dan maksimal 5 MB.'];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!isset(self::ALLOWED_MIME[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' harus berupa gambar JPG, PNG, atau WEBP yang valid.'];
    }

    $folder = $this->folderFoto();

    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
      return ['success' => false, 'filename' => null, 'message' => 'Folder penyimpanan foto tidak dapat dibuat.'];
    }

    if (!is_writable($folder)) {
      return ['success' => false, 'filename' => null, 'message' => 'Folder penyimpanan foto tidak dapat ditulis.'];
    }

    $filename = $field . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(8))
      . '.' . self::ALLOWED_MIME[$mime];

    if (!move_uploaded_file($file['tmp_name'], $folder . $filename)) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' gagal disimpan ke server.'];
    }

    return ['success' => true, 'filename' => $filename, 'message' => null];
  }

  // Mengunggah ketiga foto sekaligus; bila salah satu gagal, yang sudah
  // tersimpan pada putaran ini dibersihkan.
  private function uploadSemuaFoto(): array
  {
    $baru = [];

    foreach (array_keys(self::FOTO_FIELD) as $field) {
      $hasil = $this->uploadFoto($field);

      if (!$hasil['success']) {
        foreach ($baru as $nama) {
          $this->hapusFoto($nama);
        }
        return ['error' => $hasil['message'], 'files' => []];
      }

      if ($hasil['filename'] !== null) {
        $baru[$field] = $hasil['filename'];
      }
    }

    return ['error' => null, 'files' => $baru];
  }

  private function hapusFoto(?string $filename): void
  {
    if (!$filename) {
      return;
    }

    $filename = basename($filename);

    if (!preg_match('/^[A-Za-z0-9_]{8,80}\.(jpg|png|webp)$/', $filename)) {
      return;
    }

    $path = $this->folderFoto() . $filename;

    if (is_file($path)) {
      @unlink($path);
    }
  }

  private function ambilInput(): array
  {
    $noPlat = trim((string) ($_POST['no_plat'] ?? ''));
    $noPlat = strtoupper((string) preg_replace('/\s+/', ' ', $noPlat));

    return [
      'no_plat'     => $noPlat,
      'nm_mobil'    => trim((string) ($_POST['nm_mobil'] ?? '')),
      'nkursi'      => trim((string) ($_POST['nkursi'] ?? '')),
      'nbagasi'     => trim((string) ($_POST['nbagasi'] ?? '')),
      'jns_mobil'   => trim((string) ($_POST['jns_mobil'] ?? '')),
      'transmisi'   => trim((string) ($_POST['transmisi'] ?? '')),
      'thn_buat'    => trim((string) ($_POST['thn_buat'] ?? '')),
      'hrg_hari'    => trim((string) ($_POST['hrg_hari'] ?? '')),
      'cttn_unit'   => trim((string) ($_POST['cttn_unit'] ?? '')),
      'status_unit' => trim((string) ($_POST['status_unit'] ?? ''))
    ];
  }

  private function validasiData(array $data, bool $cekPlat): ?string
  {
    if ($cekPlat && !preg_match('/\A[A-Z]{1,2} ?[0-9]{1,4} ?[A-Z]{0,3}\z/', $data['no_plat'])) {
      return 'Nomor plat tidak valid. Contoh: BN 1234 AB.';
    }

    if ($data['nm_mobil'] === '' || mb_strlen($data['nm_mobil']) > 40) {
      return 'Nama mobil wajib diisi, maksimal 40 karakter.';
    }

    if (!preg_match('/\A[0-9]{1,2}\z/', $data['nkursi']) || (int) $data['nkursi'] < 1 || (int) $data['nkursi'] > 25) {
      return 'Jumlah kursi harus berupa angka 1 sampai 25.';
    }

    if (!preg_match('/\A[0-9]{1,2}\z/', $data['nbagasi']) || (int) $data['nbagasi'] > 30) {
      return 'Kapasitas bagasi harus berupa angka 0 sampai 30.';
    }

    if (!in_array($data['jns_mobil'], self::JENIS_MOBIL, true)) {
      return 'Jenis mobil tidak valid.';
    }

    if (!in_array($data['transmisi'], self::TRANSMISI, true)) {
      return 'Transmisi tidak valid.';
    }

    $tahunIni = (int) date('Y');
    if (!preg_match('/\A[0-9]{4}\z/', $data['thn_buat']) || (int) $data['thn_buat'] < 1901 || (int) $data['thn_buat'] > $tahunIni) {
      return 'Tahun pembuatan harus berupa angka 1901 sampai ' . $tahunIni . '.';
    }

    if (!preg_match('/\A[0-9]{1,8}\z/', $data['hrg_hari']) || (int) $data['hrg_hari'] < 1 || (int) $data['hrg_hari'] > 16777215) {
      return 'Harga per hari harus berupa angka 1 sampai 16.777.215.';
    }

    if ($data['cttn_unit'] === '' || mb_strlen($data['cttn_unit']) > 500) {
      return 'Catatan unit wajib diisi, maksimal 500 karakter.';
    }

    if (!in_array($data['status_unit'], self::STATUS_UNIT, true)) {
      return 'Status kendaraan tidak valid.';
    }

    return null;
  }

  // Mengubah angka dari string ke int agar sesuai dengan bind_param model.
  private function keTipeDb(array $data): array
  {
    foreach (['nkursi', 'nbagasi', 'thn_buat', 'hrg_hari'] as $kolom) {
      $data[$kolom] = (int) $data[$kolom];
    }

    return $data;
  }

  private function render(string $mode, array $data = [], ?string $error = null): void
  {
    $this->view('admin/mobil_view', [
      'username'      => $_SESSION['admin_username'] ?? '',
      'mobil'         => $this->mobilModel->getAll(),
      'flash'         => null,
      'mode'          => $mode,
      'mobil_dipilih' => $data ?: null,
      'error'         => $error
    ]);
  }

  /* ================= READ ================= */

  public function index(): void
  {
    require_admin_login();

    $this->view('admin/mobil_view', [
      'username'      => $_SESSION['admin_username'],
      'mobil'         => $this->mobilModel->getAll(),
      'flash'         => get_flash_message(),
      'mode'          => null,
      'mobil_dipilih' => null,
      'error'         => null
    ]);
  }

  /* ================= CREATE ================= */

  public function tambah(): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('tambah', ['status_unit' => 'tersedia']);
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $data = $this->ambilInput();
    $error = $this->validasiData($data, true);

    if ($error === null) {
      try {
        if ($this->mobilModel->findByNoPlat($data['no_plat']) !== null) {
          $error = 'Nomor plat sudah terdaftar.';
        }
      } catch (Throwable $e) {
        error_log('Cek nomor plat gagal: ' . $e->getMessage());
        $error = 'Pemeriksaan nomor plat gagal. Coba lagi.';
      }
    }

    if ($error !== null) {
      $this->render('tambah', $data, $error);
      return;
    }

    $foto = $this->uploadSemuaFoto();
    if ($foto['error'] !== null) {
      $this->render('tambah', $data, $foto['error']);
      return;
    }

    $data = $this->keTipeDb($data);
    foreach (array_keys(self::FOTO_FIELD) as $field) {
      $data[$field] = $foto['files'][$field] ?? '';
    }

    try {
      $berhasil = $this->mobilModel->create($data);
    } catch (Throwable $e) {
      error_log('Tambah kendaraan gagal: ' . $e->getMessage());
      $berhasil = false;
    }

    if (!$berhasil) {
      foreach ($foto['files'] as $nama) {
        $this->hapusFoto($nama);
      }
      $this->render('tambah', $this->ambilInput(), 'Data kendaraan gagal disimpan. Periksa kembali data yang dimasukkan.');
      return;
    }

    // PRG: redirect setelah POST berhasil
    set_flash_message('success', 'Data kendaraan berhasil ditambahkan.');
    header('Location: ' . site_url('admin/mobil'));
    exit;
  }

  /* ================= UPDATE ================= */

  public function ubah(string $noPlat = ''): void
  {
    require_admin_login();

    $lama = $this->mobilModel->findByNoPlat($noPlat);

    if ($lama === null) {
      set_flash_message('danger', 'Data kendaraan tidak ditemukan.');
      header('Location: ' . site_url('admin/mobil'));
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('ubah', $lama);
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $data = $this->ambilInput();
    $data['no_plat'] = $lama['no_plat']; // nomor plat tidak dapat diubah
    $error = $this->validasiData($data, false);

    if ($error !== null) {
      $this->render('ubah', array_merge($lama, $data), $error);
      return;
    }

    $foto = $this->uploadSemuaFoto();
    if ($foto['error'] !== null) {
      $this->render('ubah', array_merge($lama, $data), $foto['error']);
      return;
    }

    $data = $this->keTipeDb($data);
    foreach (array_keys(self::FOTO_FIELD) as $field) {
      $data[$field] = $foto['files'][$field] ?? $lama[$field];
    }

    try {
      $berhasil = $this->mobilModel->update($lama['no_plat'], $data);
    } catch (Throwable $e) {
      error_log('Ubah kendaraan gagal: ' . $e->getMessage());
      $berhasil = false;
    }

    if (!$berhasil) {
      foreach ($foto['files'] as $nama) {
        $this->hapusFoto($nama);
      }
      $this->render('ubah', $lama, 'Data kendaraan gagal diperbarui. Periksa kembali data yang dimasukkan.');
      return;
    }

    // Foto lama baru dihapus setelah database berhasil diperbarui
    foreach ($foto['files'] as $field => $nama) {
      $this->hapusFoto($lama[$field] ?? null);
    }

    set_flash_message('success', 'Data kendaraan berhasil diperbarui.');
    header('Location: ' . site_url('admin/mobil'));
    exit;
  }

  /* ================= DELETE ================= */

  public function hapus(string $noPlat = ''): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $mobil = $this->mobilModel->findByNoPlat($noPlat);

    if ($mobil === null) {
      set_flash_message('danger', 'Data kendaraan tidak ditemukan.');
      header('Location: ' . site_url('admin/mobil'));
      exit;
    }

    $pesanGagal = 'Data kendaraan gagal dihapus.';

    try {
      $berhasil = $this->mobilModel->delete($mobil['no_plat']);
    } catch (mysqli_sql_exception $e) {
      $berhasil = false;
      // 1451 = masih direferensikan tabel lain (mis. t_pilih / transaksi sewa)
      if ((int) $e->getCode() === 1451) {
        $pesanGagal = 'Kendaraan tidak dapat dihapus karena sudah dipakai pada data transaksi sewa.';
      }
    } catch (Throwable $e) {
      $berhasil = false;
    }

    if ($berhasil) {
      foreach (array_keys(self::FOTO_FIELD) as $field) {
        $this->hapusFoto($mobil[$field] ?? null);
      }
      set_flash_message('success', 'Data kendaraan berhasil dihapus.');
    } else {
      set_flash_message('danger', $pesanGagal);
    }

    header('Location: ' . site_url('admin/mobil'));
    exit;
  }
}
