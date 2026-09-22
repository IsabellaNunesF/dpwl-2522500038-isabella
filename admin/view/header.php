<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $judul_halaman ?> - Rental Mobil</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/bootstrap-5/css/bootstrap.min.css">
  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/datatables/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/style-dashboard.css">

  <!-- Select2 CSS -->
  <link href="<?= BASE_URL ?>aset/select2/css/select2.min.css" rel="stylesheet">

  <style>
    /* Fix Select2 agar lebar mengikuti container Bootstrap */
    .select2-container {
      width: 100% !important;
    }

    .select2-container .select2-selection--single {
      height: 38px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 4px 12px;
      background-color: #f7fafc;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
      line-height: 30px;
      color: #4a5568;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
      height: 36px;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
  </style>
</head>

<body class="hold-transition sidebar-mini">

  <div class="wrapper">

    <!-- Top Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link text-dark fw-bold">Halo, <?= htmlspecialchars($nama_admin) ?></span>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="<?= BASE_URL ?>admin/index.php" class="brand-link">
        <img width="50px" src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo Usaha">
        <span class="brand-text font-weight-light">Rental Mobil</span>
      </a>
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>admin/controller/dashboard_controller.php" class="nav-link <?= ($judul_halaman === 'Dashboard Admin') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item has-treeview <?= in_array($judul_halaman, ['Data Admin', 'Data Mobil', 'Data Penyewa']) ? 'menu-open' : '' ?>">
              <a href="#" class="nav-link <?= in_array($judul_halaman, ['Data Admin', 'Data Mobil', 'Data Penyewa']) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-database"></i>
                <p>Master<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="<?= in_array($judul_halaman, ['Data Admin', 'Data Mobil', 'Data Penyewa']) ? 'display: block;' : '' ?>">
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>admin/controller/madmin_controller.php" class="nav-link <?= ($judul_halaman === 'Data Admin') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Data Admin</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>admin/controller/mmobil_controller.php" class="nav-link <?= ($judul_halaman === 'Data Mobil') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Data Mobil</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php" class="nav-link <?= ($judul_halaman === 'Data Penyewa') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Data Penyewa</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item has-treeview <?= in_array($judul_halaman, ['Data Sewa', 'Data Pembayaran', 'Cetak Bukti Pembayaran', 'Penyerahan Mobil', 'Pengembalian Mobil', 'Denda Keterlambatan']) ? 'menu-open' : '' ?>">
              <a href="#" class="nav-link <?= in_array($judul_halaman, ['Data Sewa', 'Data Pembayaran', 'Cetak Bukti Pembayaran', 'Penyerahan Mobil', 'Pengembalian Mobil', 'Denda Keterlambatan']) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-exchange-alt"></i>
                <p>Transaksi<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="<?= in_array($judul_halaman, ['Data Sewa', 'Data Pembayaran', 'Cetak Bukti Pembayaran', 'Penyerahan Mobil', 'Pengembalian Mobil', 'Denda Keterlambatan']) ? 'display: block;' : '' ?>">
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>admin/controller/tsewa_controller.php" class="nav-link <?= ($judul_halaman === 'Data Sewa') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Data Sewa</p>
                  </a>
                </li>
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php" class="nav-link <?= ($judul_halaman === 'Data Pembayaran' || $judul_halaman === 'Cetak Bukti Pembayaran') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Data Pembayaran</p>
                  </a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/tpenyerahan_controller.php" class="nav-link <?= ($judul_halaman === 'Penyerahan Mobil') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Penyerahan Mobil</p>
                  </a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/tpengembalian_controller.php" class="nav-link <?= ($judul_halaman === 'Pengembalian Mobil') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Pengembalian Mobil</p>
                  </a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/tdenda_controller.php" class="nav-link <?= ($judul_halaman === 'Denda Keterlambatan') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Denda Keterlambatan</p>
                  </a></li>
              </ul>
            </li>

            <li class="nav-item has-treeview  <?= in_array($judul_halaman, ['Laporan Pendapatan Sewa', 'Cetak Laporan Pendapatan Sewa', 'Laporan Pendapatan Denda', 'Cetak Laporan Pendapatan Denda']) ? 'menu-open' : '' ?>">
              <a href="#" class="nav-link <?= in_array($judul_halaman, ['Laporan Pendapatan Sewa', 'Cetak Laporan Pendapatan Sewa', 'Laporan Pendapatan Denda', 'Cetak Laporan Pendapatan Denda']) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Laporan<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="<?= in_array($judul_halaman, ['Laporan Pendapatan Sewa', 'Cetak Laporan Pendapatan Sewa', 'Laporan Pendapatan Denda', 'Cetak Laporan Pendapatan Denda']) ? 'display: block;' : '' ?>">
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/laporansewa_controller.php" class="nav-link <?= ($judul_halaman === 'Laporan Pendapatan Sewa' || $judul_halaman === 'Cetak Laporan Pendapatan Sewa') ? 'active' : '' ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Pendapatan Sewa</p>
                  </a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>admin/controller/laporandenda_controller.php" class="nav-link <?= ($judul_halaman === 'Laporan Pendapatan Denda' || $judul_halaman === 'Cetak Laporan Pendapatan Denda') ? 'active' : '' ?>" class="nav-link"><i class="far fa-circle nav-icon"></i>
                    <p>Pendapatan Denda</p>
                  </a></li>
              </ul>
            </li>
            <li class="nav-item mt-3">
              <a href="<?= BASE_URL ?>admin/logout.php" class="nav-link text-danger">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Logout</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 text-dark"><?= $judul_halaman ?></h1>
            </div>
          </div>
        </div>
      </div>

      <div class="content">
        <div class="container-fluid">