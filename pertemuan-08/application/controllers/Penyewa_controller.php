<?php
require_once APPPATH . 'models/Penyewa_model.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Penyewa_controller extends Controller
{
  private Penyewa_model $penyewaModel;
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    $this->penyewaModel = new Penyewa_model();
    $this->transaksiModel = new Transaksi_model();
  }

  public function profil(): void
  {
    $userPlg = $this->wajibLoginPenyewa();

    $profil = $this->penyewaModel->getProfil($userPlg);

    if ($profil === null) {
      show_404();
      return;
    }

    // Menghitung jumlah keranjang penyewa aktif
    $jumlahKeranjang = $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

    $this->view('penyewa/profil', [
      'profil'          => $profil,
      'jumlahKeranjang' => $jumlahKeranjang
    ]);
  }

  private function wajibLoginPenyewa(): string
  {
    $userPlg = trim((string) ($_SESSION['penyewa_user_plg'] ?? ''));
    if ($userPlg === '') {
      $_SESSION['flash_penyewa_nav'] = 'Silakan login terlebih dahulu untuk mengakses profil.';
      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }
    return $userPlg;
  }
}
