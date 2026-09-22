<?php
require_once __DIR__ . '/header.php';
?>

<!-- Konten Halaman Keranjang -->
<main class="page-wrap">
  <div class="container">
    <div class="section-head" style="margin-bottom: 30px;">
      <div>
        <span class="eyebrow">Langkah 1 dari 3</span>
        <h2 class="section-title">Keranjang Sewa Anda</h2>
      </div>
      <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="btn btn-outline">
        <i class="fa-solid fa-plus"></i> Tambah Mobil Lagi
      </a>
    </div>

    <?php if (empty($data_mobil)): ?>
      <!-- Empty State -->
      <div class="empty-state">
        <i class="fa-solid fa-cart-shopping"></i>
        <h3>Keranjang Kosong</h3>
        <p>Anda belum memilih mobil untuk disewa. Yuk, jelajahi armada premium kami!</p>
        <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="btn btn-gold" style="margin-top: 24px;">
          <i class="fa-solid fa-car"></i> Pilih Mobil Sekarang
        </a>
      </div>
    <?php else: ?>

      <!-- ========================================== -->
      <!-- PERIODE SEWA (Form Input)                  -->
      <!-- ========================================== -->
      <div class="periode-card">
        <div class="periode-header">
          <h3><i class="fa-regular fa-calendar-days"></i> Periode Sewa</h3>
          <span class="periode-badge" id="durasiBadge"><?= $periodeData['n_hari'] ?> Hari</span>
        </div>
        <form id="formPeriode" action="<?= BASE_URL ?>penyewa/controller/keranjang_controller.php" method="POST">
          <input type="hidden" name="aksi" value="update">
          <div class="periode-grid">
            <div class="form-group">
              <label for="tgl_mulai">
                <i class="fa-solid fa-calendar-plus"></i> Tanggal Mulai
              </label>
              <input type="date" id="tgl_mulai" name="tgl_mulai"
                value="<?= htmlspecialchars($periodeData['tgl_mulai']) ?>"
                min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
              <label for="tgl_selesai">
                <i class="fa-solid fa-calendar-check"></i> Tanggal Selesai
              </label>
              <input type="date" id="tgl_selesai" name="tgl_selesai"
                value="<?= htmlspecialchars($periodeData['tgl_selesai']) ?>"
                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>
            <div class="form-group">
              <label for="wkt_ambil">
                <i class="fa-regular fa-clock"></i> Waktu Pengambilan
              </label>
              <input type="time" id="wkt_ambil" name="wkt_ambil"
                value="<?= htmlspecialchars($periodeData['wkt_ambil']) ?>" required>
            </div>
            <div class="form-group form-group-btn">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-gold btn-block">
                <i class="fa-solid fa-rotate"></i> Update Keranjang
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Cart Wrapper -->
      <div class="cart-wrapper">
        <!-- Daftar Mobil di Keranjang -->
        <div class="cart-items">
          <div class="cart-items-header">
            <h4><i class="fa-solid fa-car-side"></i> Daftar Mobil Dipilih</h4>
            <span class="cart-count"><?= count($data_mobil) ?> Unit</span>
          </div>

          <?php foreach ($data_mobil as $mobil): ?>
            <div class="cart-item">
              <img src="<?= getRandomImage($mobil) ?>" alt="<?= htmlspecialchars($mobil['nm_mobil']) ?>">
              <div class="cart-info">
                <h4><?= htmlspecialchars($mobil['nm_mobil']) ?></h4>
                <p style="font-size: 13px; color: var(--gray-500); margin-bottom: 6px;">
                  <?= htmlspecialchars($mobil['jns_mobil']) ?> •
                  <?= $mobil['transmisi'] == 'AT' ? 'Automatic' : 'Manual' ?> •
                  <?= $mobil['thn_buat'] ?>
                </p>
                <p class="price">
                  <?= formatRupiah($mobil['hrg_hari']) ?>
                  <span style="font-weight: 400; color: var(--gray-500); font-size: 13px;">/ hari</span>
                </p>
              </div>
              <a href="?hapus=<?= urlencode($mobil['no_plat']) ?>"
                class="cart-remove"
                onclick="return confirm('Yakin ingin menghapus mobil ini dari keranjang?');"
                title="Hapus dari keranjang">
                <i class="fa-solid fa-trash-can"></i>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Ringkasan Sewa (Sidebar Kanan) -->
        <div class="cart-summary">
          <h3><i class="fa-solid fa-receipt"></i> Ringkasan Sewa</h3>

          <div class="summary-row">
            <span>Jumlah Mobil</span>
            <strong><?= count($data_mobil) ?> Unit</strong>
          </div>

          <div class="summary-row">
            <span>Tarif / Hari</span>
            <span class="amount"><?= formatRupiah($totalPerHari) ?></span>
          </div>

          <div class="summary-row">
            <span>Durasi Sewa</span>
            <strong id="summaryDurasi"><?= $periodeData['n_hari'] ?> Hari</strong>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row total">
            <span>Total Estimasi Pembayaran</span>
            <span class="amount" id="totalEstimasi"><?= formatRupiah($totalPerHari * $periodeData['n_hari']) ?></span>
          </div>

          <p style="font-size: 12px; color: var(--gray-300); margin-top: 16px; line-height: 1.6; opacity: 0.8;">
            *Total akhir akan dihitung ulang pada halaman checkout.
            Periode sewa: <strong id="periodeText"><?= date('d M Y', strtotime($periodeData['tgl_mulai'])) ?> - <?= date('d M Y', strtotime($periodeData['tgl_selesai'])) ?></strong>
          </p>


          <a href="<?= BASE_URL ?>penyewa/controller/checkout_controller.php" class="btn btn-gold btn-block" style="margin-top: 24px;">
            Lanjut ke Checkout <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<!-- ========================================== -->
<!-- SCRIPT KALKULASI DINAMIS PERIODE SEWA      -->
<!-- ========================================== -->
<script>
  (function() {
    'use strict';

    // Format Rupiah
    function formatRupiah(angka) {
      return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Format tanggal Indonesia
    function formatTanggalIndo(dateStr) {
      if (!dateStr) return '-';
      const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
      ];
      const d = new Date(dateStr);
      return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    // Hitung durasi dan update UI
    function hitungDurasi() {
      const tglMulai = document.getElementById('tgl_mulai');
      const tglSelesai = document.getElementById('tgl_selesai');

      if (!tglMulai || !tglSelesai) return;

      const valMulai = tglMulai.value;
      const valSelesai = tglSelesai.value;

      if (!valMulai || !valSelesai) return;

      const dateMulai = new Date(valMulai);
      const dateSelesai = new Date(valSelesai);

      // Validasi: tanggal selesai harus > tanggal mulai
      if (dateSelesai <= dateMulai) {
        // Auto adjust: tanggal selesai = tanggal mulai + 1 hari
        const nextDay = new Date(dateMulai);
        nextDay.setDate(nextDay.getDate() + 1);
        tglSelesai.value = nextDay.toISOString().split('T')[0];
        tglSelesai.min = tglMulai.value;
      }

      // Hitung selisih hari
      const diffTime = Math.abs(dateSelesai - dateMulai);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      const nHari = diffDays < 1 ? 1 : diffDays;

      // Update badge durasi
      const durasiBadge = document.getElementById('durasiBadge');
      if (durasiBadge) durasiBadge.textContent = nHari + ' Hari';

      // Update summary durasi
      const summaryDurasi = document.getElementById('summaryDurasi');
      if (summaryDurasi) summaryDurasi.textContent = nHari + ' Hari';

      // Update total estimasi
      const totalPerHari = <?= (int)$totalPerHari ?>;
      const totalEstimasi = totalPerHari * nHari;
      const elTotal = document.getElementById('totalEstimasi');
      if (elTotal) elTotal.textContent = formatRupiah(totalEstimasi);

      // Update teks periode
      const periodeText = document.getElementById('periodeText');
      if (periodeText) {
        periodeText.textContent = formatTanggalIndo(valMulai) + ' - ' + formatTanggalIndo(valSelesai);
      }
    }

    // Event listener
    document.addEventListener('DOMContentLoaded', function() {
      const tglMulai = document.getElementById('tgl_mulai');
      const tglSelesai = document.getElementById('tgl_selesai');

      if (tglMulai) {
        tglMulai.addEventListener('change', function() {
          // Set min tanggal selesai = tanggal mulai + 1 hari
          if (tglSelesai) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            tglSelesai.min = nextDay.toISOString().split('T')[0];
          }
          hitungDurasi();
        });
      }

      if (tglSelesai) {
        tglSelesai.addEventListener('change', hitungDurasi);
      }

      // Hitung awal saat halaman load
      hitungDurasi();
    });
  })();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>