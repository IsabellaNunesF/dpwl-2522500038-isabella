<?php require_once __DIR__ . '/header.php'; ?>

<main class="page-wrap">
  <div class="container">
    <!-- Header Halaman -->
    <div class="section-head" style="margin-bottom: 30px;">
      <div>
        <span class="eyebrow">Pembayaran</span>
        <h2 class="section-title">Upload Bukti Pembayaran DP</h2>
      </div>
      <a href="<?= BASE_URL ?>penyewa/controller/riwayat_controller.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
      </a>
    </div>

    <div class="checkout-wrapper">
      <!-- Kolom Kiri: Info Pembayaran -->
      <div class="checkout-main">
        <!-- Status Pembayaran -->
        <div class="checkout-card">
          <h3><i class="fa-solid fa-circle-info"></i> Status Pembayaran</h3>
          <div class="info-box" style="background: <?= $pembayaran['sttus_byr'] === 'pending' ? '#fff3cd' : '#d1ecf1' ?>; border-left: 4px solid <?= $pembayaran['sttus_byr'] === 'pending' ? '#f39c12' : '#17a2b8' ?>;">
            <?php if ($pembayaran['sttus_byr'] === 'pending' && empty($pembayaran['ft_byr'])): ?>
              <p style="margin: 0;"><i class="fa-solid fa-clock"></i> <strong>Menunggu Upload Bukti</strong></p>
              <p style="margin: 8px 0 0 0; font-size: 14px;">Silakan upload bukti transfer DP Anda di bawah ini.</p>
            <?php elseif ($pembayaran['sttus_byr'] === 'pending' && !empty($pembayaran['ft_byr'])): ?>
              <p style="margin: 0;"><i class="fa-solid fa-hourglass-half"></i> <strong>Menunggu Verifikasi Admin</strong></p>
              <p style="margin: 8px 0 0 0; font-size: 14px;">Bukti pembayaran Anda sedang diverifikasi oleh admin.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Info Pembayaran -->
        <div class="checkout-card">
          <h3><i class="fa-solid fa-receipt"></i> Detail Pembayaran</h3>
          <div class="checkout-info-grid">
            <div class="info-item">
              <span class="info-label">Kode Pembayaran</span>
              <strong class="info-value"><?= htmlspecialchars($pembayaran['kode_bayar']) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Kode Sewa</span>
              <strong class="info-value"><?= htmlspecialchars($pembayaran['kode_sewa']) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Jenis Pembayaran</span>
              <strong class="info-value">DP (Down Payment)</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Nominal DP</span>
              <strong class="info-value text-gold"><?= formatRupiah($pembayaran['nominal']) ?></strong>
            </div>
            <div class="info-item">
              <span class="info-label">Tanggal Dibuat</span>
              <strong class="info-value"><?= date('d M Y, H:i', strtotime($pembayaran['tgl_wkt_byr'])) ?> WIB</strong>
            </div>
          </div>
        </div>

        <!-- Info Rekening -->
        <div class="checkout-card info-card">
          <h3><i class="fa-solid fa-university"></i> Informasi Rekening</h3>
          <div class="info-box" style="background: #e8f5e9; border-left: 4px solid #28a745;">
            <p style="margin: 0 0 12px 0;"><strong>Transfer ke rekening berikut:</strong></p>
            <div style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
              <p style="margin: 0 0 4px 0; font-size: 13px; color: #666;">Bank BCA</p>
              <p style="margin: 0; font-size: 18px; font-weight: bold; color: #28a745;">1234567890</p>
              <p style="margin: 4px 0 0 0; font-size: 13px; color: #666;">a.n. Usaha</p>
            </div>
            <p style="margin: 0; font-size: 13px; color: #666;">
              <i class="fa-solid fa-info-circle"></i> Pastikan nominal transfer sesuai dengan DP yang harus dibayar.
            </p>
          </div>
        </div>

        <!-- Form Upload Bukti -->
        <?php if ($pembayaran['sttus_byr'] === 'pending' && empty($pembayaran['ft_byr'])): ?>
          <div class="checkout-card">
            <h3><i class="fa-solid fa-upload"></i> Upload Bukti Transfer</h3>
            <form action="<?= BASE_URL ?>penyewa/controller/pembayaran_controller.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="aksi" value="upload_bukti">
              <input type="hidden" name="kode_bayar" value="<?= htmlspecialchars($pembayaran['kode_bayar']) ?>">

              <div class="form-group">
                <label for="bukti_bayar">Pilih File Bukti Transfer</label>
                <input type="file" name="bukti_bayar" id="bukti_bayar" accept="image/jpeg,image/png,image/jpg" required style="padding: 12px; border: 2px dashed #D4AF37; border-radius: 8px; width: 100%; background: #fffbea;">
                <small style="color: #666; font-size: 12px; margin-top: 6px; display: block;">
                  Format: JPG, PNG | Maksimal: 5MB
                </small>
              </div>

              <!-- Preview Gambar -->
              <div id="preview-container" style="display: none; margin-top: 16px;">
                <img id="preview-image" src="" alt="Preview" style="max-width: 100%; border-radius: 8px; border: 2px solid #D4AF37;">
              </div>

              <button type="submit" class="btn btn-gold btn-block" style="margin-top: 20px;" onclick="return confirm('Yakin ingin mengupload bukti pembayaran?');">
                <i class="fa-solid fa-paper-plane"></i> Upload & Konfirmasi Pembayaran
              </button>
            </form>
          </div>
        <?php endif; ?>

        <!-- Tampilan Bukti yang Sudah Diupload -->
        <?php if (!empty($pembayaran['ft_byr'])): ?>
          <div class="checkout-card">
            <h3><i class="fa-solid fa-image"></i> Bukti Pembayaran</h3>
            <div style="text-align: center;">
              <img src="<?= BASE_URL ?>uploads/pembayaran/<?= htmlspecialchars($pembayaran['ft_byr']) ?>"
                alt="Bukti Pembayaran"
                style="max-width: 100%; border-radius: 8px; border: 2px solid #D4AF37; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Kolom Kanan: Ringkasan -->
      <div class="checkout-sidebar">
        <div class="checkout-summary">
          <h3><i class="fa-solid fa-car-side"></i> Detail Sewa</h3>

          <div class="summary-row">
            <span>Kode Sewa</span>
            <strong><?= htmlspecialchars($pembayaran['kode_sewa']) ?></strong>
          </div>

          <div class="summary-row">
            <span>Tanggal Mulai</span>
            <strong><?= date('d M Y', strtotime($pembayaran['tgl_mulai'])) ?></strong>
          </div>

          <div class="summary-row">
            <span>Tanggal Selesai</span>
            <strong><?= date('d M Y', strtotime($pembayaran['tgl_selesai'])) ?></strong>
          </div>

          <div class="summary-row">
            <span>Waktu Ambil</span>
            <strong><?= date('H:i', strtotime($pembayaran['wkt_ambil'])) ?> WIB</strong>
          </div>

          <div class="summary-divider"></div>

          <h4 style="color: var(--gold); font-size: 14px; margin: 16px 0 12px 0;">Daftar Mobil</h4>

          <?php foreach ($daftarMobil as $mobil): ?>
            <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; margin-bottom: 10px;">
              <p style="margin: 0 0 4px 0; font-weight: 600; font-size: 14px;"><?= htmlspecialchars($mobil['nm_mobil']) ?></p>
              <p style="margin: 0; font-size: 12px; color: #999;">
                <?= htmlspecialchars($mobil['jns_mobil']) ?> • <?= $mobil['hrg_sewa'] ?>/hari
              </p>
            </div>
          <?php endforeach; ?>

          <div class="summary-divider"></div>

          <div class="summary-row">
            <span>Total Estimasi</span>
            <span><?= formatRupiah($totalEstimasi) ?></span>
          </div>

          <div class="summary-row">
            <span>DP (30%)</span>
            <strong class="text-gold"><?= formatRupiah($pembayaran['nominal']) ?></strong>
          </div>

          <div class="summary-row">
            <span>Sisa Pelunasan</span>
            <span><?= formatRupiah($sisaPembayaran) ?></span>
          </div>

          <div class="summary-divider"></div>

          <div class="info-box" style="background: rgba(212, 175, 55, 0.1); border-left: 3px solid var(--gold); padding: 12px; border-radius: 6px; margin-top: 16px;">
            <p style="margin: 0; font-size: 12px; color: #ccc;">
              <i class="fa-solid fa-lightbulb" style="color: var(--gold);"></i>
              <strong>Info:</strong> Sisa pembayaran akan ditagihkan saat pengambilan mobil.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  // Preview gambar sebelum upload
  document.getElementById('bukti_bayar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('preview-image').src = e.target.result;
        document.getElementById('preview-container').style.display = 'block';
      }
      reader.readAsDataURL(file);
    }
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>