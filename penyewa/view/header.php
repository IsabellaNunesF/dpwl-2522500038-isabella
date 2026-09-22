<?php
// ==========================================
// GUARD: Cegah akses langsung ke View
// ==========================================
if (!defined('BASE_URL')) {
  // PERBAIKAN: Gunakan path absolut karena BASE_URL belum terdefinisi
  header('Location: /contohdpwl/penyewa/controller/tmobil_controller.php');
  exit;
}

// Fungsi helper untuk memilih gambar acak dari 3 kolom
function getRandomImage($mobil)
{
  $images = array_filter([$mobil['ft_depan'], $mobil['ft_blkg'], $mobil['ft_interior']]);
  if (empty($images)) return BASE_URL . 'aset/images/no-image.png';
  return BASE_URL . 'uploads/mobil/' . $images[array_rand($images)];
}

function formatRupiah($angka)
{
  return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper untuk mengecek checkbox
function isChecked($filterStr, $value)
{
  if (empty($filterStr)) return '';
  return in_array($value, explode(',', $filterStr)) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $judul_halaman ?? 'Katalog Mobil' ?> — Usaha</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/style-penyewa.css">
</head>

<body>
  <!-- Loading Overlay -->
  <div id="loader" class="loader-overlay">
    <div class="loader-content">
      <div class="loader-crown">
        <img src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo Usaha">
      </div>
      <div class="loader-spinner"></div>
      <p class="loader-text">Memuat...</p>
    </div>
  </div>

  <!-- Header -->
  <header id="siteHeader" class="site-header">
    <div class="container header-inner">
      <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="brand">
        <div class="brand-icon">
          <img src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo Usaha">
        </div>
        <div class="brand-text">
          <strong>Usaha</strong>
          <small>Rental</small>
        </div>
      </a>

      <nav class="main-nav">
        <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="nav-link active">Katalog</a>
        <a href="#tentang" class="nav-link">Tentang</a>
        <a href="#layanan" class="nav-link">Layanan</a>
        <a href="#kontak" class="nav-link">Kontak</a>
      </nav>

      <!-- Header Actions Dinamis -->
      <div class="header-actions">
        <?php if (!empty($isLoggedIn)): ?>
          <!-- === SUDAH LOGIN === -->
          <a href="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" class="icon-btn" title="Keranjang">
            <i class="fa-solid fa-cart-shopping"></i>
            <span id="cartCount" class="badge"><?= $keranjangCount ?? 0 ?></span>
          </a>

          <!-- Dropdown Profil -->
          <div class="user-dropdown">
            <button class="user-toggle" id="userToggle">
              <div class="user-avatar">
                <?= strtoupper(substr($userLogin['nm_lngkp'] ?? 'P', 0, 1)) ?>
              </div>
              <span class="user-name"><?= htmlspecialchars($userLogin['nm_lngkp'] ?? 'Penyewa') ?></span>
              <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
            </button>

            <div class="user-menu" id="userMenu">
              <div class="user-menu-header">
                <strong><?= htmlspecialchars($userLogin['nm_lngkp']) ?></strong>
                <small>@<?= htmlspecialchars($userLogin['user_plg']) ?></small>
              </div>
              <a href="<?= BASE_URL ?>penyewa/controller/profil_controller.php" class="user-menu-item">
                <i class="fa-solid fa-user"></i> Profil Saya
              </a>
              <a href="<?= BASE_URL ?>penyewa/controller/riwayat_controller.php" class="user-menu-item">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Sewa
              </a>
              <div class="user-menu-divider"></div>
              <a href="<?= BASE_URL ?>penyewa/controller/logout_controller.php" class="user-menu-item logout"
                onclick="return confirm('Yakin ingin keluar?');">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
              </a>
            </div>
          </div>

        <?php else: ?>
          <!-- === BELUM LOGIN === -->
          <a href="<?= BASE_URL ?>penyewa/controller/tlogin_controller.php" class="btn btn-ghost">Masuk</a>
          <a href="<?= BASE_URL ?>penyewa/controller/tregistrasi_controller.php" class="btn btn-gold">Registrasi</a>
        <?php endif; ?>

        <button id="menuToggle" class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
      </div>
    </div>
  </header>

  <!-- Notifikasi Login/Logout Berhasil -->
  <?php if (!empty($notifikasi)): ?>
    <div class="notification-bar" id="notifBar">
      <div class="container">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= $notifikasi ?></span>
        <button class="notif-close" onclick="document.getElementById('notifBar').remove();">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
  <?php endif; ?>