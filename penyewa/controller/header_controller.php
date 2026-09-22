<?php

/**
 * Header Controller
 * Digunakan oleh semua halaman untuk menyiapkan data header
 */

require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/header_model.php';

class HeaderController
{
  private $model;

  public function __construct()
  {
    global $conn;
    $this->model = new HeaderModel($conn);
  }

  /**
   * Siapkan semua data untuk header
   */
  public function getHeaderData()
  {
    // Cek status login
    $isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;

    $data = [
      'isLoggedIn'     => $isLoggedIn,
      'userLogin'      => null,
      'keranjangCount' => 0,
      'notifikasi'     => ''
    ];

    if ($isLoggedIn) {
      // Data user
      $data['userLogin'] = [
        'user_plg' => $_SESSION['user_plg'] ?? '',
        'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa'
      ];

      // Hitung jumlah keranjang dari database
      $data['keranjangCount'] = $this->model->getKeranjangCount($_SESSION['user_plg']);
    }

    // Ambil notifikasi jika ada
    if (isset($_SESSION['notifikasi'])) {
      $data['notifikasi'] = $_SESSION['notifikasi'];
      unset($_SESSION['notifikasi']);
    } elseif (isset($_GET['status'])) {
      if ($_GET['status'] === 'login_berhasil') {
        $data['notifikasi'] = 'Login berhasil! Selamat datang kembali.';
      } elseif ($_GET['status'] === 'logout_berhasil') {
        $data['notifikasi'] = 'Anda telah berhasil logout.';
      }
    }

    return $data;
  }
}
