
<?php

require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'models/Mpenyewa_model.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Mpenyewa_controller extends Controller
{
  private Mpenyewa_model $penyewaModel;
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    $this->penyewaModel = new Mpenyewa_model();
    $this->transaksiModel = new Transaksi_model();
  }

  // =========================
  // PROFIL PENYEWA
  // =========================
  public function profil(): void
  {
    $userPlg = $this->wajibLoginPenyewa();

    $profil = $this->penyewaModel->findByUserPlg($userPlg);

    if ($profil === null) {
      show_404();
      return;
    }

    unset($profil['sandi']);

    $jumlahKeranjang =
      $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

    $this->view('penyewa/profil', [
      'profil' => $profil,
      'jumlahKeranjang' => $jumlahKeranjang
    ]);
  }

  private function wajibLoginPenyewa(): string
  {
    $userPlg = trim(
      (string) ($_SESSION['penyewa_user_plg'] ?? '')
    );

    if ($userPlg === '') {
      $_SESSION['flash_penyewa_nav'] =
        'Silakan login terlebih dahulu untuk mengakses profil.';

      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    return $userPlg;
  }

  // =========================
  // INDEX ADMIN
  // =========================
  public function index(): void
  {
    require_admin_login();

    $this->view('admin/Mpenyewa_view', [
      'username' => $_SESSION['admin_username'] ?? '',
      'penyewa' => $this->penyewaModel->getAll(),
      'flash' => get_flash_message(),
      'mode' => null,
      'penyewa_dipilih' => null,
      'error' => null
    ]);
  }

  // =========================
  // TAMBAH PENYEWA
  // =========================
  public function tambah(): void
  {
    require_admin_login();

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $userPlg = trim($_POST['user_plg'] ?? '');
      $sandi = $_POST['sandi'] ?? '';

      $data = [
        'user_plg' => $userPlg,
        'sandi' => password_hash($sandi, PASSWORD_DEFAULT),
        'nik' => trim($_POST['nik'] ?? ''),
        'nm_lngkp' => trim($_POST['nm_lngkp'] ?? ''),
        'no_hp' => trim($_POST['no_hp'] ?? ''),
        'almt_lngkp' => trim($_POST['almt_lngkp'] ?? ''),
        'ft_ktp' => '',
        'ft_sim_a' => ''
      ];

      if (
        $userPlg === '' ||
        $sandi === '' ||
        $data['nik'] === '' ||
        $data['nm_lngkp'] === ''
      ) {
        $error = 'Username, password, NIK, dan nama wajib diisi.';
      } elseif (
        $this->penyewaModel->findByUserPlg($userPlg) !== null
      ) {
        $error = 'Username penyewa sudah digunakan.';
      } elseif ($this->penyewaModel->create($data)) {
        set_flash_message(
          'success',
          'Data penyewa berhasil ditambahkan.'
        );

        header('Location: ' . site_url('admin/mpenyewa'));
        exit;
      } else {
        $error = 'Data penyewa gagal ditambahkan.';
      }
    }

    $this->view('admin/Mpenyewa_view', [
      'username' => $_SESSION['admin_username'] ?? '',
      'penyewa' => $this->penyewaModel->getAll(),
      'flash' => get_flash_message(),
      'mode' => 'tambah',
      'penyewa_dipilih' => null,
      'error' => $error
    ]);
  }

  // =========================
  // UBAH PENYEWA
  // =========================
  public function ubah(string $userPlg): void
  {
    require_admin_login();

    $penyewa = $this->penyewaModel->findByUserPlg($userPlg);

    if ($penyewa === null) {
      show_404();
      return;
    }

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'nik' => trim($_POST['nik'] ?? ''),
        'nm_lngkp' => trim($_POST['nm_lngkp'] ?? ''),
        'no_hp' => trim($_POST['no_hp'] ?? ''),
        'almt_lngkp' => trim($_POST['almt_lngkp'] ?? ''),
        'ft_ktp' => $penyewa['ft_ktp'] ?? '',
        'ft_sim_a' => $penyewa['ft_sim_a'] ?? ''
      ];

      $passwordBaru = trim($_POST['sandi'] ?? '');

      $berhasil = $this->penyewaModel->update(
        $userPlg,
        $data
      );

      if ($berhasil && $passwordBaru !== '') {
        $berhasil = $this->penyewaModel->updatePassword(
          $userPlg,
          password_hash($passwordBaru, PASSWORD_DEFAULT)
        );
      }

      if ($berhasil) {
        set_flash_message(
          'success',
          'Data penyewa berhasil diperbarui.'
        );

        header('Location: ' . site_url('admin/mpenyewa'));
        exit;
      }

      $error = 'Data penyewa gagal diperbarui.';
      $penyewa = array_merge($penyewa, $data);
    }

    unset($penyewa['sandi']);

    $this->view('admin/Mpenyewa_view', [
      'username' => $_SESSION['admin_username'] ?? '',
      'penyewa' => $this->penyewaModel->getAll(),
      'flash' => get_flash_message(),
      'mode' => 'ubah',
      'penyewa_dipilih' => $penyewa,
      'error' => $error
    ]);
  }

  // =========================
  // HAPUS PENYEWA
  // =========================
  public function hapus(string $userPlg): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Method tidak diizinkan.');
    }

    $berhasil = $this->penyewaModel->delete($userPlg);

    if ($berhasil) {
      set_flash_message(
        'success',
        'Data penyewa berhasil dihapus.'
      );
    } else {
      set_flash_message(
        'error',
        'Data penyewa gagal dihapus.'
      );
    }

    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }
}
