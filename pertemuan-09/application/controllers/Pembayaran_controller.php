<?php

// P9: Verifikasi Pembayaran DP oleh Admin
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Pembayaran_controller extends Controller
{
  private Transaksi_model $transaksiModel;

  private const FILTER_STATUS = ['pending', 'valid', 'invalid'];

  public function __construct()
  {
    $this->transaksiModel = new Transaksi_model();
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
}
