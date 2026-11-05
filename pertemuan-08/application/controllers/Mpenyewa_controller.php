<?php

// P8: Manajemen Data Penyewa (Admin) + profil penyewa
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';
require_once APPPATH . 'models/Mpenyewa_model.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Mpenyewa_controller extends Controller
{
  private Mpenyewa_model $penyewaModel;
  private Transaksi_model $transaksiModel;

  private const MAX_FILE_SIZE = 2097152; // 2 MB

  private const ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
  ];

  // jenis di URL => kolom di tabel
  private const FOTO_FIELD = [
    'ktp' => 'ft_ktp',
    'sim' => 'ft_sim_a'
  ];

  public function __construct()
  {
    $this->penyewaModel = new Mpenyewa_model();
    $this->transaksiModel = new Transaksi_model();
  }

  /* ================= HELPER PRIVAT ================= */

  private function wajibLoginPenyewa(): string
  {
    if (empty($_SESSION['penyewa_user_plg'])) {
      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    return $_SESSION['penyewa_user_plg'];
  }

  private function validasiCsrf(): void
  {
    // Jika total unggahan melebihi post_max_size, PHP mengosongkan
    // $_POST dan $_FILES sehingga token CSRF ikut hilang.
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      empty($_POST) &&
      empty($_FILES) &&
      (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
    ) {
      http_response_code(413);
      exit('Ukuran total unggahan melebihi batas server (post_max_size).');
    }

    csrf_validate();
  }

  private function uploadFoto(string $field): array
  {
    if (!in_array($field, ['ft_ktp', 'ft_sim_a'], true)) {
      return ['success' => false, 'filename' => null, 'message' => 'Field foto tidak valid.'];
    }

    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return ['success' => true, 'filename' => null, 'message' => null];
    }

    $file = $_FILES[$field];
    $label = $field === 'ft_ktp' ? 'Foto KTP' : 'Foto SIM A';

    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        return ['success' => false, 'filename' => null, 'message' => $label . ' melebihi batas ukuran file (maksimal 2 MB).'];
      case UPLOAD_ERR_PARTIAL:
        return ['success' => false, 'filename' => null, 'message' => $label . ' hanya terunggah sebagian. Silakan ulangi.'];
      default:
        return ['success' => false, 'filename' => null, 'message' => $label . ' gagal diunggah (kesalahan konfigurasi server).'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' tidak valid.'];
    }

    if ((int) $file['size'] < 1 || (int) $file['size'] > self::MAX_FILE_SIZE) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' harus berukuran lebih dari 0 byte dan maksimal 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset(self::ALLOWED_MIME[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return ['success' => false, 'filename' => null, 'message' => $label . ' harus berupa gambar JPG, PNG, atau WEBP yang valid.'];
    }

    $folder = dirname(APPPATH) . '/uploads/penyewa/';

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

  private function hapusFoto(?string $filename): void
  {
    if (!$filename) {
      return;
    }

    $filename = basename($filename);

    if (!preg_match('/^[A-Za-z0-9_]{8,80}\.(jpg|png|webp)$/', $filename)) {
      return;
    }

    $path = dirname(APPPATH) . '/uploads/penyewa/' . $filename;

    if (is_file($path)) {
      @unlink($path);
    }
  }

  private function ambilInput(): array
  {
    return [
      'nik'        => trim((string) ($_POST['nik'] ?? '')),
      'nm_lngkp'   => trim((string) ($_POST['nm_lngkp'] ?? '')),
      'no_hp'      => trim((string) ($_POST['no_hp'] ?? '')),
      'almt_lngkp' => trim((string) ($_POST['almt_lngkp'] ?? '')),
    ];
  }

  private function validasiData(array $data): ?string
  {
    if (!preg_match('/\A[0-9]{16}\z/', $data['nik'])) {
      return 'NIK harus terdiri dari 16 digit angka.';
    }

    if ($data['nm_lngkp'] === '' || mb_strlen($data['nm_lngkp']) > 100) {
      return 'Nama wajib diisi, maksimal 100 karakter.';
    }

    if (!preg_match('/\A[0-9+(). -]{8,15}\z/', $data['no_hp'])) {
      return 'Nomor HP tidak valid (8-15 karakter: angka, +, (, ), titik, spasi, atau strip).';
    }

    if ($data['almt_lngkp'] === '' || mb_strlen($data['almt_lngkp']) > 300) {
      return 'Alamat wajib diisi, maksimal 300 karakter.';
    }

    return null;
  }

  private function validasiPassword(string $sandi, string $konfirmasi): ?string
  {
    if (strlen($sandi) < 8) {
      return 'Password minimal 8 karakter.';
    }

    if (strlen($sandi) > 72) {
      return 'Password maksimal 72 karakter.';
    }

    if ($sandi !== $konfirmasi) {
      return 'Konfirmasi password tidak sama.';
    }

    return null;
  }

  private function render(string $mode, array $data = [], ?string $error = null): void
  {
    $this->view('admin/Mpenyewa_view', [
      'username'       => $_SESSION['admin_username'] ?? '',
      'penyewa'        => $this->penyewaModel->getAll(),
      'flash'          => null,
      'mode'           => $mode,
      'penyewa_dipilih' => $data ?: null,
      'error'          => $error,
      'csrf_token'     => csrf_token()
    ]);
  }

  /* ================= FOTO (dilindungi) ================= */

  // Foto KTP/SIM bersifat sensitif: tidak diakses langsung lewat URL folder,
  // tetapi lewat method ini (hanya admin atau pemilik foto).
  public function foto(string $jenis = '', string $userPlg = ''): void
  {
    if (!isset(self::FOTO_FIELD[$jenis])) {
      http_response_code(404);
      exit('Foto tidak ditemukan.');
    }

    $adminOk = is_admin_logged_in();
    $pemilikOk = !empty($_SESSION['penyewa_user_plg'])
      && hash_equals((string) $_SESSION['penyewa_user_plg'], $userPlg);

    if (!$adminOk && !$pemilikOk) {
      http_response_code(403);
      exit('Akses ditolak.');
    }

    $row = $this->penyewaModel->findByUserPlg($userPlg);
    $nama = $row[self::FOTO_FIELD[$jenis]] ?? null;

    if (!$nama || !preg_match('/^[A-Za-z0-9_]{8,80}\.(jpg|png|webp)$/', $nama)) {
      http_response_code(404);
      exit('Foto belum diunggah.');
    }

    $path = dirname(APPPATH) . '/uploads/penyewa/' . $nama;

    if (!is_file($path)) {
      http_response_code(404);
      exit('Berkas foto tidak ditemukan.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);

    if (!isset(self::ALLOWED_MIME[$mime])) {
      http_response_code(415);
      exit('Tipe berkas tidak didukung.');
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=0, must-revalidate');
    readfile($path);
    exit;
  }

  /* ================= PROFIL PENYEWA ================= */

  public function profil(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $profil = $this->penyewaModel->findByUserPlg($userPlg);

    if (!$profil) {
      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    $error = null;
    $old = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->validasiCsrf();

      $old = $this->ambilInput();
      $error = $this->validasiData($old);

      $passwordLama = (string) ($_POST['password_lama'] ?? '');
      $passwordBaru = (string) ($_POST['password_baru'] ?? '');
      $konfirmasi = (string) ($_POST['konfirmasi_password'] ?? '');

      $ubahPassword = ($passwordLama !== '' || $passwordBaru !== '' || $konfirmasi !== '');

      if (!$error && $ubahPassword) {
        if ($passwordLama === '' || $passwordBaru === '' || $konfirmasi === '') {
          $error = 'Semua kolom password harus diisi.';
        } elseif (!password_verify($passwordLama, $profil['sandi'])) {
          $error = 'Password lama tidak sesuai.';
        } else {
          $error = $this->validasiPassword($passwordBaru, $konfirmasi);
        }
      }

      $ktpBaru = null;
      $simBaru = null;

      if (!$error) {
        $ktp = $this->uploadFoto('ft_ktp');
        if (!$ktp['success']) {
          $error = $ktp['message'];
        } else {
          $ktpBaru = $ktp['filename'];
        }
      }

      if (!$error) {
        $sim = $this->uploadFoto('ft_sim_a');
        if (!$sim['success']) {
          $error = $sim['message'];
        } else {
          $simBaru = $sim['filename'];
        }
      }

      if (!$error) {
        $data = $old;
        $data['ft_ktp'] = $ktpBaru ?? $profil['ft_ktp'];
        $data['ft_sim_a'] = $simBaru ?? $profil['ft_sim_a'];

        $hash = $ubahPassword ? password_hash($passwordBaru, PASSWORD_DEFAULT) : null;

        try {
          $berhasil = $this->penyewaModel->update($userPlg, $data, $hash);
        } catch (Throwable $e) {
          error_log('Update profil gagal: ' . $e->getMessage());
          $berhasil = false;
        }

        if ($berhasil) {
          if ($ktpBaru) {
            $this->hapusFoto($profil['ft_ktp']);
          }
          if ($simBaru) {
            $this->hapusFoto($profil['ft_sim_a']);
          }

          $_SESSION['flash_profil'] = 'Profil berhasil diperbarui.';
          header('Location: ' . site_url('penyewa/profil'));
          exit;
        }

        $error = 'Profil gagal diperbarui.';
      }

      // Ada kegagalan: foto baru yang sempat tersimpan dibersihkan
      $this->hapusFoto($ktpBaru);
      $this->hapusFoto($simBaru);
    }

    $profil = $this->penyewaModel->findByUserPlg($userPlg);

    $jumlahKeranjang = $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

    $flash = $_SESSION['flash_profil'] ?? null;
    unset($_SESSION['flash_profil']);

    unset($profil['sandi']);

    $this->view('penyewa/profil', [
      'profil'          => $profil,
      'old'             => $old,
      'error'           => $error,
      'flash'           => $flash,
      'csrf_token'      => csrf_token(),
      'jumlahKeranjang' => $jumlahKeranjang
    ]);
  }

  /* ================= ADMIN: READ ================= */

  public function index(): void
  {
    require_admin_login();

    $this->view('admin/Mpenyewa_view', [
      'username'        => $_SESSION['admin_username'],
      'penyewa'         => $this->penyewaModel->getAll(),
      'flash'           => get_flash_message(),
      'mode'            => null,
      'penyewa_dipilih' => null,
      'error'           => null,
      'csrf_token'      => csrf_token()
    ]);
  }

  /* ================= ADMIN: CREATE ================= */

  public function tambah(): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('tambah');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $this->validasiCsrf();

    $data = $this->ambilInput();
    $data['user_plg'] = trim((string) ($_POST['user_plg'] ?? ''));
    $data['ft_ktp'] = null;
    $data['ft_sim_a'] = null;

    $sandi = (string) ($_POST['sandi'] ?? '');
    $konfirmasi = (string) ($_POST['konfirmasi_sandi'] ?? '');

    $error = null;

    if (!preg_match('/\A[A-Za-z0-9._-]{3,15}\z/', $data['user_plg'])) {
      $error = 'Username harus 3-15 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
    }

    $error = $error ?? $this->validasiPassword($sandi, $konfirmasi);
    $error = $error ?? $this->validasiData($data);

    if ($error === null) {
      try {
        if ($this->penyewaModel->findByUserPlg($data['user_plg']) !== null) {
          $error = 'Username sudah digunakan.';
        }
      } catch (Throwable $e) {
        $error = 'Pemeriksaan username gagal. Coba lagi.';
      }
    }

    if ($error !== null) {
      $this->render('tambah', $data, $error);
      return;
    }

    $ktp = $this->uploadFoto('ft_ktp');
    if (!$ktp['success']) {
      $this->render('tambah', $data, $ktp['message']);
      return;
    }

    $sim = $this->uploadFoto('ft_sim_a');
    if (!$sim['success']) {
      $this->hapusFoto($ktp['filename']);
      $this->render('tambah', $data, $sim['message']);
      return;
    }

    $data['ft_ktp'] = $ktp['filename'];
    $data['ft_sim_a'] = $sim['filename'];
    $data['sandi'] = password_hash($sandi, PASSWORD_DEFAULT);

    try {
      $berhasil = $this->penyewaModel->create($data);
    } catch (Throwable $e) {
      error_log('Tambah penyewa gagal: ' . $e->getMessage());
      $berhasil = false;
    }

    if (!$berhasil) {
      $this->hapusFoto($data['ft_ktp']);
      $this->hapusFoto($data['ft_sim_a']);
      $data['ft_ktp'] = null;
      $data['ft_sim_a'] = null;
      $this->render('tambah', $data, 'Data penyewa gagal disimpan. Periksa kembali data yang dimasukkan.');
      return;
    }

    // PRG: redirect setelah POST berhasil
    set_flash_message('success', 'Data penyewa berhasil ditambahkan.');
    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }

  /* ================= ADMIN: UPDATE ================= */

  public function ubah(string $userPlg = ''): void
  {
    require_admin_login();

    $userPlg = rawurldecode($userPlg);
    $lama = $this->penyewaModel->findByUserPlg($userPlg);

    if ($lama === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/mpenyewa'));
      exit;
    }

    unset($lama['sandi']); // hash tidak perlu dikirim ke View

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('ubah', $lama);
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $this->validasiCsrf();

    $data = $this->ambilInput();
    $data['ft_ktp'] = $lama['ft_ktp'];
    $data['ft_sim_a'] = $lama['ft_sim_a'];

    $sandi = (string) ($_POST['sandi'] ?? '');
    $konfirmasi = (string) ($_POST['konfirmasi_sandi'] ?? '');
    $ubahPassword = ($sandi !== '' || $konfirmasi !== '');

    $error = $this->validasiData($data);

    if ($error === null && $ubahPassword) {
      $error = $this->validasiPassword($sandi, $konfirmasi);
    }

    if ($error !== null) {
      $this->render('ubah', array_merge($lama, $data), $error);
      return;
    }

    $ktp = $this->uploadFoto('ft_ktp');
    if (!$ktp['success']) {
      $this->render('ubah', array_merge($lama, $data), $ktp['message']);
      return;
    }

    $sim = $this->uploadFoto('ft_sim_a');
    if (!$sim['success']) {
      $this->hapusFoto($ktp['filename']);
      $this->render('ubah', array_merge($lama, $data), $sim['message']);
      return;
    }

    $data['ft_ktp'] = $ktp['filename'] ?? $lama['ft_ktp'];
    $data['ft_sim_a'] = $sim['filename'] ?? $lama['ft_sim_a'];

    $hash = $ubahPassword ? password_hash($sandi, PASSWORD_DEFAULT) : null;

    try {
      $berhasil = $this->penyewaModel->update($userPlg, $data, $hash);
    } catch (Throwable $e) {
      error_log('Ubah penyewa gagal: ' . $e->getMessage());
      $berhasil = false;
    }

    if (!$berhasil) {
      $this->hapusFoto($ktp['filename']);
      $this->hapusFoto($sim['filename']);
      $this->render('ubah', $lama, 'Data penyewa gagal diperbarui. Periksa kembali data yang dimasukkan.');
      return;
    }

    // Foto lama baru dihapus setelah database berhasil diperbarui
    if ($ktp['filename']) {
      $this->hapusFoto($lama['ft_ktp']);
    }
    if ($sim['filename']) {
      $this->hapusFoto($lama['ft_sim_a']);
    }

    set_flash_message('success', 'Data penyewa berhasil diperbarui.');
    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }

  /* ================= ADMIN: DELETE ================= */

  public function hapus(string $userPlg = ''): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $this->validasiCsrf();

    $userPlg = rawurldecode($userPlg);
    $penyewa = $this->penyewaModel->findByUserPlg($userPlg);

    if ($penyewa === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/mpenyewa'));
      exit;
    }

    try {
      $berhasil = $this->penyewaModel->delete($userPlg);
      $pesanGagal = 'Data penyewa gagal dihapus.';
    } catch (mysqli_sql_exception $e) {
      $berhasil = false;
      // 1451 = masih direferensikan tabel lain (mis. t_sewa)
      $pesanGagal = ((int) $e->getCode() === 1451)
        ? 'Penyewa tidak dapat dihapus karena masih memiliki data transaksi sewa.'
        : 'Data penyewa gagal dihapus.';
    } catch (Throwable $e) {
      $berhasil = false;
      $pesanGagal = 'Data penyewa gagal dihapus.';
    }

    if ($berhasil) {
      $this->hapusFoto($penyewa['ft_ktp']);
      $this->hapusFoto($penyewa['ft_sim_a']);
      set_flash_message('success', 'Data penyewa berhasil dihapus.');
    } else {
      set_flash_message('danger', $pesanGagal);
    }

    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }
}
