<?php
require_once __DIR__ . '/header.php';
?>

<main class="page-wrap">
  <div class="container">
    <!-- Header Halaman -->
    <div class="section-head" style="margin-bottom: 30px;">
      <div>
        <span class="eyebrow">Langkah 2 dari 3</span>
        <h2 class="section-title">Konfirmasi Checkout</h2>
      </div>
      <a href="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang
      </a>
    </div>

    <div class="checkout-wrapper">
      <!-- Kolom Kiri: Ringkasan Pesanan -->
      <div class="checkout-main">
        <!-- Info Periode Sewa -->
        <div class="checkout-card">
          <h3><i class="fa-regular fa-calendar-days"></i> Periode Sewa</h3>
          <div class="checkout-info-grid">
            <div class="info-item">
              <span class="info-label">Kode Sewa</span>
              <strong class="info-value"><?= htmlspecialchars($checkoutData['pesanan']['kode_sewa']) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Tanggal Mulai</span>
              <strong class="info-value"><?= date('d M Y', strtotime($checkoutData['pesanan']['tgl_mulai'])) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Tanggal Selesai</span>
              <strong class="info-value"><?= date('d M Y', strtotime($checkoutData['pesanan']['tgl_selesai'])) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Waktu Pengambilan</span>
              <strong class="info-value"><?= date('H:i', strtotime($checkoutData['pesanan']['wkt_ambil'])) ?> WIB</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Durasi Sewa</span>
              <strong class="info-value"><?= $checkoutData['nHari'] ?> Hari</strong>
            </div>
          </div>
        </div>

        <!-- Daftar Mobil -->
        <div class="checkout-card">
          <h3><i class="fa-solid fa-car-side"></i> Daftar Mobil Dipilih</h3>
          <div class="checkout-mobil-list">
            <?php foreach ($checkoutData['daftarMobil'] as $mobil): ?>
              <div class="checkout-mobil-item">
                <div class="mobil-info">
                  <h4><?= htmlspecialchars($mobil['nm_mobil']) ?></h4>
                  <p class="mobil-meta">
                    <?= htmlspecialchars($mobil['jns_mobil']) ?> •
                    <?= $mobil['transmisi'] == 'AT' ? 'Automatic' : 'Manual' ?> •
                    <?= $mobil['thn_buat'] ?>
                  </p>
                </div>
                <div class="mobil-harga">
                  <span class="harga-label">Harga/Hari</span>
                  <strong><?= formatRupiah($mobil['hrg_sewa']) ?></strong>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="checkout-card info-card">
          <h3><i class="fa-solid fa-circle-info"></i> Informasi Pembayaran</h3>
          <div class="info-box">
            <p style="display: block;"><i class="fa-solid fa-check-circle"></i> Setelah Anda mengkonfirmasi checkout, pesanan akan menunggu pembayaran DP (Down Payment) sebesar <strong>30%</strong> dari total estimasi.</p>
            <p><i class="fa-solid fa-clock"></i> Pembayaran dapat dilakukan melalui fitur Pembayaran setelah checkout berhasil.</p>
            <p><i class="fa-solid fa-shield-halved"></i> Pesanan akan diproses setelah pembayaran DP diverifikasi oleh admin.</p>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Ringkasan Pembayaran -->
      <div class="checkout-sidebar">
        <div class="checkout-summary">
          <h3><i class="fa-solid fa-receipt"></i> Ringkasan Pembayaran</h3>

          <div class="summary-row">
            <span>Jumlah Mobil</span>
            <strong><?= count($checkoutData['daftarMobil']) ?> Unit</strong>
          </div>

          <div class="summary-row">
            <span>Tarif / Hari</span>
            <span><?= formatRupiah($checkoutData['totalPerHari']) ?></span>
          </div>

          <div class="summary-row">
            <span>Durasi Sewa</span>
            <strong><?= $checkoutData['nHari'] ?> Hari</strong>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row total">
            <span>Total Estimasi</span>
            <span class="amount"><?= formatRupiah($checkoutData['totalEstimasi']) ?></span>
          </div>

          <div class="summary-row dp-row">
            <span>DP Harus Dibayar (30%)</span>
            <span class="amount dp-amount"><?= formatRupiah($checkoutData['dp']) ?></span>
          </div>

          <div class="summary-row">
            <span>Sisa Pembayaran</span>
            <span class="amount"><?= formatRupiah($checkoutData['totalEstimasi'] - $checkoutData['dp']) ?></span>
          </div>

          <div class="summary-divider"></div>

          <!-- Form Konfirmasi -->
          <form action="<?= BASE_URL ?>penyewa/controller/checkout_controller.php" method="POST">
            <input type="hidden" name="aksi" value="konfirmasi">
            <button type="submit" class="btn btn-gold btn-block" onclick="return confirm('Yakin ingin melakukan checkout? DP sebesar <?= formatRupiah($checkoutData['dp']) ?> akan ditagihkan.');">
              <i class="fa-solid fa-check"></i> Konfirmasi Checkout
            </button>
          </form>

          <a href="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" class="btn btn-outline btn-block" style="margin-top: 12px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang
          </a>

          <p class="summary-note">
            *Dengan mengkonfirmasi checkout, Anda menyetujui syarat dan ketentuan rental Usaha.
          </p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>