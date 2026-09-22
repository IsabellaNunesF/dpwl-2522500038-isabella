<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php';
// PERBAIKAN: Path yang benar (header_controller.php ada di folder yang sama)
require_once __DIR__ . '/header_controller.php';
require_once __DIR__ . '/../model/tmobil_model.php';

// ==========================================
// 1. INISIALISASI HEADER (WAJIB DI SEMUA CONTROLLER)
// ==========================================
$headerCtrl = new HeaderController();
$headerData = $headerCtrl->getHeaderData();

// ==========================================
// 2. LOGIKA KONTROLLER (Mobil, Filter, dll)
// ==========================================
$model = new TMobilModel($conn);

// Tangkap parameter GET
$filters = [
  'transmisi' => isset($_GET['transmisi']) ?
    (is_array($_GET['transmisi']) ? implode(',', $_GET['transmisi']) : $_GET['transmisi']) : '',
  'kapasitas' => isset($_GET['kapasitas']) ?
    (is_array($_GET['kapasitas']) ? implode(',', $_GET['kapasitas']) : $_GET['kapasitas']) : '',
  'bagasi'    => isset($_GET['bagasi']) ?
    (is_array($_GET['bagasi']) ? implode(',', $_GET['bagasi']) : $_GET['bagasi']) : '',
  'jenis'     => isset($_GET['jenis']) ?
    (is_array($_GET['jenis']) ? implode(',', $_GET['jenis']) : $_GET['jenis']) : '',
  'maxPrice'  => $_GET['maxPrice'] ?? 5000000,
  'keyword'   => trim($_GET['keyword'] ?? '')
];

$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$sort    = $_GET['sort'] ?? 'default';

// Ambil data mobil dengan filter
$result = $model->getMobil($filters, $page, $perPage, $sort);

// Ambil statistik dari database
$totalMobil   = $model->getTotalMobil();
$totalPenyewa = $model->getTotalPenyewa();

$statistik = [
  'armada'    => $totalMobil,
  'pelanggan' => $totalPenyewa
];

// ==========================================
// 3. GABUNGKAN DATA (Header + Kontroller)
// ==========================================
$data_untuk_view = array_merge($headerData, [
  'judul_halaman' => 'Katalog Mobil',
  'data_mobil'    => $result['data'],
  'totalItems'    => $result['totalItems'],
  'totalPages'    => $result['totalPages'],
  'currentPage'   => $result['currentPage'],
  'filters'       => $filters,
  'sort'          => $sort,
  'statistik'     => $statistik,
]);

extract($data_untuk_view);
require_once __DIR__ . '/../index.php';
