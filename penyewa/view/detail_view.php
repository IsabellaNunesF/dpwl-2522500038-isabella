<?php
// Memanggil Header
require_once __DIR__ . '/header.php';
?>

<!-- Konten Halaman Detail -->
<main class="page-wrap">
  <div class="container">
    <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="btn btn-ghost" style="background-color: #133a89; margin-bottom: 24px; display: inline-flex; align-items: center; gap: 8px;">
      <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
    </a>

    <?php
    // Susun array gambar beserta labelnya
    $slides = [
      ['src' => $mobil['ft_depan'],    'label' => 'Tampak Depan',   'icon' => 'fa-car-front'],
      ['src' => $mobil['ft_blkg'],     'label' => 'Tampak Belakang', 'icon' => 'fa-car-rear'],
      ['src' => $mobil['ft_interior'], 'label' => 'Interior',       'icon' => 'fa-couch'],
    ];

    $validSlides = array_filter($slides, fn($s) => !empty($s['src']));
    if (empty($validSlides)) {
      $validSlides = [['src' => 'no-image.png', 'label' => 'Tidak ada foto', 'icon' => 'fa-image']];
    }
    $totalSlides = count($validSlides);
    ?>

    <div class="detail-wrapper">
      <!-- Gallery Slider -->
      <div class="detail-gallery">
        <div class="slider-main">
          <div class="slider-counter">
            <span id="currentSlide">1</span> / <?= $totalSlides ?>
          </div>

          <div class="slider-track" id="sliderTrack">
            <?php foreach ($validSlides as $i => $slide): ?>
              <div class="slider-slide">
                <img src="<?= BASE_URL . 'uploads/mobil/' . $slide['src'] ?>" alt="<?= $slide['label'] ?>">
                <div class="slide-label">
                  <i class="fa-solid <?= $slide['icon'] ?>"></i>
                  <?= $slide['label'] ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($totalSlides > 1): ?>
            <button class="slider-btn prev" id="btnPrev" aria-label="Sebelumnya">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="slider-btn next" id="btnNext" aria-label="Selanjutnya">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          <?php endif; ?>
        </div>

        <?php if ($totalSlides > 1): ?>
          <div class="slider-thumbs" id="sliderThumbs">
            <?php foreach ($validSlides as $i => $slide): ?>
              <div class="thumb-item <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
                <img src="<?= BASE_URL . 'uploads/mobil/' . $slide['src'] ?>" alt="<?= $slide['label'] ?>">
                <div class="thumb-label"><?= $slide['label'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div class="detail-info">
        <span class="status-badge-detail <?= $mobil['status_unit'] == 'tersedia' ? 'status-tersedia' : 'status-disewa' ?>">
          <i class="fa-solid fa-circle" style="font-size: 8px; vertical-align: middle; margin-right: 5px;"></i>
          <?= ucfirst($mobil['status_unit']) ?>
        </span>

        <h1><?= htmlspecialchars($mobil['nm_mobil']) ?></h1>
        <p class="car-meta">
          <?= htmlspecialchars($mobil['jns_mobil']) ?> •
          Tahun <?= $mobil['thn_buat'] ?> •
          Plat: <?= htmlspecialchars($mobil['no_plat']) ?>
        </p>

        <div class="detail-price">
          <?= formatRupiah($mobil['hrg_hari']) ?> <span>/ hari</span>
        </div>

        <div class="spec-grid">
          <div class="spec-box">
            <i class="fa-solid fa-users"></i>
            <div><strong><?= $mobil['nkursi'] ?></strong> Kursi</div>
          </div>
          <div class="spec-box">
            <i class="fa-solid fa-suitcase"></i>
            <div><strong><?= $mobil['nbagasi'] ?></strong> Bagasi</div>
          </div>
          <div class="spec-box">
            <i class="fa-solid fa-gear"></i>
            <div><strong><?= $mobil['transmisi'] == 'AT' ? 'Automatic' : 'Manual' ?></strong></div>
          </div>
          <div class="spec-box">
            <i class="fa-solid fa-car"></i>
            <div><strong><?= htmlspecialchars($mobil['jns_mobil']) ?></strong></div>
          </div>
        </div>

        <?php if (!empty($mobil['cttn_unit'])): ?>
          <div class="catatan-box">
            <h4><i class="fa-solid fa-circle-info"></i> Catatan Unit</h4>
            <p><?= htmlspecialchars($mobil['cttn_unit']) ?></p>
          </div>
        <?php endif; ?>

        <!-- Tombol Aksi -->
        <div style="margin-top: 30px;">
          <?php if (!$isLoggedIn): ?>
            <div class="login-prompt-box">
              <div class="prompt-icon">
                <i class="fa-solid fa-lock"></i>
              </div>
              <h4>Masuk untuk Menyewa</h4>
              <p>Anda harus login terlebih dahulu sebelum dapat menyewa mobil.</p>
              <div class="prompt-actions">
                <a href="<?= BASE_URL ?>penyewa/controller/tlogin_controller.php?redirect=detail&plat=<?= urlencode($mobil['no_plat']) ?>" class="btn btn-gold">
                  <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </a>
                <a href="<?= BASE_URL ?>penyewa/controller/tregistrasi_controller.php" class="btn btn-outline">
                  <i class="fa-solid fa-user-plus"></i> Registrasi
                </a>
              </div>
            </div>
            <<?php elseif ($mobil['status_unit'] === 'tersedia'): ?>
              <form method="POST" action="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" style="display: flex; gap: 15px;">
              <input type="hidden" name="no_plat" value="<?= htmlspecialchars($mobil['no_plat']) ?>">
              <input type="hidden" name="aksi" value="tambah">
              <button type="submit" class="btn btn-gold" style="flex: 1; font-size: 1.1rem; padding: 15px;">
                <i class="fa-solid fa-cart-plus"></i> Sewa Sekarang
              </button>
              </form>
            <?php else: ?>
              <button class="btn btn-ghost" style="flex: 1; font-size: 1.1rem; padding: 15px; cursor: not-allowed; opacity: 0.6; width: 100%;" disabled>
                <i class="fa-solid fa-ban"></i> Mobil Sedang Disewa
              </button>
            <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php
// Memanggil Footer
require_once __DIR__ . '/footer.php';
?>