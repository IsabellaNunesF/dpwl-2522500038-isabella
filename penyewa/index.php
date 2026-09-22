<?php
// ==========================================
// GUARD: Cegah akses langsung ke View
// Jika diakses langsung, redirect ke Controller
// ==========================================
if (!defined('BASE_URL')) {
  // Redirect ke controller menggunakan path absolut
  header('Location: /contohdpwl/penyewa/controller/tmobil_controller.php');
  exit;
}

require_once __DIR__ . '/view/header.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <span class="hero-tag">PREMIUM CAR RENTAL</span>
    <h1 class="hero-title">Berkendara dengan <span class="text-gold">Kecantikan</span></h1>
    <p class="hero-subtitle">Pilih armada terbaik Usaha — kualitas terjamin, pelayanan eksklusif, harga terbaik di kelasnya.</p>

    <!-- Form pencarian -->
    <form action="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" method="GET" class="search-bar">
      <div class="search-input-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="keyword" placeholder="Cari mobil..." value="<?= htmlspecialchars($filters['keyword']) ?>">
      </div>
      <button type="submit" class="btn btn-gold btn-search">Cari Sekarang</button>
    </form>

    <!-- Statistik dinamis -->
    <div class="hero-stats">
      <div class="stat">
        <strong><?= $statistik['armada'] ?>+</strong>
        <span>Armada</span>
      </div>
      <div class="stat">
        <strong><?= number_format($statistik['pelanggan'], 0, '', '') ?>+</strong>
        <span>Pelanggan</span>
      </div>
      <div class="stat">
        <strong>24/7</strong>
        <span>Layanan</span>
      </div>
    </div>
  </div>
</section>

<!-- Catalog Section -->
<section class="catalog-section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Armada Kami</span>
        <h2 class="section-title">Pilih Armada Impian Anda</h2>
      </div>
    </div>

    <div class="catalog-grid">
      <!-- Sidebar Filter -->
      <aside id="filterSidebar" class="filter-sidebar">
        <div class="filter-head">
          <h3><i class="fa-solid fa-sliders"></i> Filter</h3>
          <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="btn-reset">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        </div>

        <form action="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" method="GET" id="filterForm">
          <input type="hidden" name="keyword" value="<?= htmlspecialchars($filters['keyword']) ?>">

          <div class="filter-group">
            <h4 class="filter-title">Transmisi</h4>
            <label class="check-item">
              <input type="checkbox" name="transmisi[]" value="MT" <?= isChecked($filters['transmisi'], 'MT') ?>>
              <span class="check-box"></span> Manual
            </label>
            <label class="check-item">
              <input type="checkbox" name="transmisi[]" value="AT" <?= isChecked($filters['transmisi'], 'AT') ?>>
              <span class="check-box"></span> Automatic
            </label>
          </div>

          <div class="filter-group">
            <h4 class="filter-title">Kapasitas Penumpang</h4>
            <?php foreach ([2, 3, 4, 5, 7, 8] as $k): ?>
              <label class="check-item">
                <input type="checkbox" name="kapasitas[]" value="<?= $k ?>" <?= isChecked($filters['kapasitas'], $k) ?>>
                <span class="check-box"></span> <?= $k == 8 ? '8+ Penumpang' : "$k Penumpang" ?>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="filter-group">
            <h4 class="filter-title">Jumlah Bagasi</h4>
            <?php foreach ([1, 2, 3, 4] as $b): ?>
              <label class="check-item">
                <input type="checkbox" name="bagasi[]" value="<?= $b ?>" <?= isChecked($filters['bagasi'], $b) ?>>
                <span class="check-box"></span> <?= $b == 4 ? '4+ Bagasi' : "$b Bagasi" ?>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="filter-group">
            <h4 class="filter-title">Jenis Mobil</h4>
            <?php foreach (['MPV', 'SUV', 'City Car'] as $j): ?>
              <label class="check-item">
                <input type="checkbox" name="jenis[]" value="<?= $j ?>" <?= isChecked($filters['jenis'], $j) ?>>
                <span class="check-box"></span> <?= $j ?>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="filter-group">
            <h4 class="filter-title">Rentang Harga / Hari</h4>
            <div class="price-display">
              <span>Rp 0</span>
              <span>Rp <span id="priceMaxLabel"><?= number_format($filters['maxPrice'], 0, ',', '.') ?></span></span>
            </div>
            <input type="range" name="maxPrice" id="priceRange" class="range-slider" min="300000" max="5000000" step="100000" value="<?= $filters['maxPrice'] ?>">
            <div class="price-presets">
              <button type="button" class="preset-btn" data-max="500000">
                < 500rb</button>
                  <button type="button" class="preset-btn" data-max="1000000">
                    < 1jt</button>
                      <button type="button" class="preset-btn" data-max="2000000">
                        < 2jt</button>
                          <button type="button" class="preset-btn active" data-max="5000000">Semua</button>
            </div>
          </div>

          <button type="submit" class="btn btn-gold btn-block">Terapkan Filter</button>
        </form>
      </aside>

      <!-- Car Area -->
      <div class="car-area">
        <div class="toolbar">
          <p class="result-count">Menampilkan <span><?= $totalItems ?></span> mobil</p>
          <div class="sort-wrap">
            <label>Urutkan:</label>
            <select id="sortBy" onchange="window.location.href='<?= BASE_URL ?>penyewa/controller/tmobil_controller.php?sort='+this.value+'&<?= http_build_query(array_filter($filters)) ?>'">
              <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Default</option>
              <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Harga Terendah</option>
              <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
              <option value="name-asc" <?= $sort === 'name-asc' ? 'selected' : '' ?>>Nama A-Z</option>
            </select>
          </div>
        </div>

        <?php if (empty($data_mobil)): ?>
          <div class="empty-state">
            <i class="fa-solid fa-car-burst"></i>
            <h3>Mobil tidak ditemukan</h3>
            <p>Coba ubah filter atau kata kunci pencarian Anda.</p>
          </div>
        <?php else: ?>
          <div id="carGrid" class="car-grid">
            <?php foreach ($data_mobil as $mobil): ?>
              <article class="car-card">
                <div class="car-image">
                  <div class="gold-plate">
                    <span class="plate-text"><?= formatRupiah($mobil['hrg_hari']) ?></span>
                  </div>
                  <span class="car-type-badge"><?= htmlspecialchars($mobil['jns_mobil']) ?></span>
                  <button class="car-fav" aria-label="Favorit"><i class="fa-solid fa-heart"></i></button>
                  <img src="<?= getRandomImage($mobil) ?>" alt="<?= htmlspecialchars($mobil['nm_mobil']) ?>" loading="lazy" />
                </div>
                <div class="car-body">
                  <h3 class="car-name"><?= htmlspecialchars($mobil['nm_mobil']) ?></h3>
                  <p class="car-brand"><?= htmlspecialchars($mobil['jns_mobil']) ?> • <?= $mobil['thn_buat'] ?></p>
                  <div class="car-specs">
                    <div class="spec-item"><i class="fa-solid fa-gear"></i> <?= $mobil['transmisi'] == 'AT' ? 'Automatic' : 'Manual' ?></div>
                    <div class="spec-item"><i class="fa-solid fa-users"></i> <?= $mobil['nkursi'] ?> Kursi</div>
                    <div class="spec-item"><i class="fa-solid fa-suitcase"></i> <?= $mobil['nbagasi'] ?> Bagasi</div>
                    <div class="spec-item"><i class="fa-solid fa-car"></i> <?= htmlspecialchars($mobil['jns_mobil']) ?></div>
                  </div>
                  <div class="valet-ticket">
                    <div class="ticket-perforation"></div>
                    <div class="ticket-content">
                      <span class="ticket-label">TARIF/HARI</span>
                      <strong class="ticket-price"><?= formatRupiah($mobil['hrg_hari']) ?></strong>
                    </div>
                  </div>
                  <div class="car-actions">
                    <a href="<?= BASE_URL ?>penyewa/controller/detail_controller.php?plat=<?= urlencode($mobil['no_plat']) ?>" class="btn-detail">
                      <i class="fa-solid fa-eye"></i> Lihat Detail
                    </a>
                    <?php if (!empty($isLoggedIn)): ?>
                      <form action="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" method="POST" style="flex: 1;">
                        <input type="hidden" name="no_plat" value="<?= htmlspecialchars($mobil['no_plat']) ?>">
                        <input type="hidden" name="aksi" value="tambah">
                        <button type="submit" class="btn-add-cart" style="width: 100%;">
                          <i class="fa-solid fa-cart-plus"></i> Keranjang
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination">
              <?php if ($currentPage > 1): $queryParams['page'] = $currentPage - 1; ?>
                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
              <?php else: ?>
                <button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $totalPages; $i++): $queryParams['page'] = $i; ?>
                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
              <?php endfor; ?>

              <?php if ($currentPage < $totalPages): $queryParams['page'] = $currentPage + 1; ?>
                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
              <?php else: ?>
                <button class="page-btn" disabled><i class="fa-solid fa-chevron-right"></i></button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php
require_once __DIR__ . '/view/footer.php';
?>