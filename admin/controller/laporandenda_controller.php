<?php
// admin/controller/laporandenda_controller.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/laporandenda_model.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_username'])) {
  header('Location: ' . BASE_URL . 'admin/index.php');
  exit;
}

$model = new LaporanDenda();
$aksi  = $_GET['aksi'] ?? 'index'; // Ambil aksi dari URL

// Ambil filter dari GET (menggunakan GET agar URL laporan bisa di-bookmark/share)
$filter = [
  'tgl_mulai'   => $_GET['tgl_mulai'] ?? '',
  'tgl_selesai' => $_GET['tgl_selesai'] ?? ''
];

// Panggil model untuk mendapatkan data yang sudah difilter
$laporan = $model->getLaporan($filter);

// Ambil tanggal untuk tanda tangan (format: 27 Juni 2026)
$tgl_ttd = date('d/m/Y');
$bulan_indo = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember'
];
$tgl_parts = explode('/', $tgl_ttd);
$tgl_ttd = $tgl_parts[0] . ' ' . $bulan_indo[(int)$tgl_parts[1] - 1] . ' ' . $tgl_parts[2];

// Siapkan data untuk dikirim ke view
$data_untuk_view = [
  'judul_halaman'      => ($aksi === 'cetak') ? 'Cetak Laporan Pendapatan Denda' : 'Laporan Pendapatan Denda',
  'nama_admin'         => $_SESSION['admin_username'],
  'filter'             => $filter,
  'data_laporan'       => $laporan['data'],
  'total_pendapatan'   => $laporan['total'],
  'jumlah_transaksi'   => $laporan['jumlah_transaksi'],
  'tgl_ttd'            => $tgl_ttd,
  'aksi'               => $aksi // <--- TAMBAHKAN BARIS INI
];
extract($data_untuk_view);
require_once __DIR__ . '/../view/laporandenda_view.php';
