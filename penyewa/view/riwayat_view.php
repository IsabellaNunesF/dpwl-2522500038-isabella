<?php
// Memanggil Header
require_once __DIR__ . '/header.php';
?>
<!-- Konten Halaman Riwayat -->
<main class="page-wrap">
  <div class="container">
    <div class="riwayat-header">
      <span class="eyebrow">Transaksi Saya</span>
      <h1>Riwayat Sewa</h1>
      <p style="color: var(--gray-500); font-size: 14px; margin-top: 4px;">
        Pantau semua transaksi rental mobil Anda di sini.
      </p>
    </div>

    <!-- Statistik Ringkas -->
    <div class="stat-ringkas">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
        <div class="stat-info">
          <strong><?= $totalSewa ?></strong>
          <span>Total Transaksi</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        <div class="stat-info">
          <strong><?= formatRupiah($totalPengeluaran) ?></strong>
          <span>Total Pengeluaran</span>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <a href="?status=semua" class="tab <?= $filter_status === 'semua' ? 'active' : '' ?>">Semua</a>
      <a href="?status=diajukan" class="tab <?= $filter_status === 'diajukan' ? 'active' : '' ?>">Diajukan</a>
      <a href="?status=menunggu_pembayaran" class="tab <?= $filter_status === 'menunggu_pembayaran' ? 'active' : '' ?>">Menunggu Bayar</a>
      <a href="?status=disetujui" class="tab <?= $filter_status === 'disetujui' ? 'active' : '' ?>">Disetujui</a>
      <a href="?status=aktif" class="tab <?= $filter_status === 'aktif' ? 'active' : '' ?>">Sedang Berjalan</a>
      <a href="?status=selesai" class="tab <?= $filter_status === 'selesai' ? 'active' : '' ?>">Selesai</a>
      <a href="?status=batal" class="tab <?= $filter_status === 'batal' ? 'active' : '' ?>">Dibatalkan</a>
      <a href="?status=ditolak" class="tab <?= $filter_status === 'ditolak' ? 'active' : '' ?>">Ditolak</a>
    </div>

    <!-- Daftar Riwayat -->
    <?php if (empty($riwayat)): ?>
      <div class="empty-state">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <h3>Tidak ada riwayat</h3>
        <p>Belum ada transaksi dengan status ini.</p>
        <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="btn btn-gold" style="margin-top: 24px;">
          <i class="fa-solid fa-car"></i> Lihat Katalog
        </a>
      </div>
    <?php else: ?>
      <div class="riwayat-list">
        <?php foreach ($riwayat as $r):
          $statusInfo = labelStatusSewa($r['status_sewa']);
          $durasi = (strtotime($r['tgl_selesai']) - strtotime($r['tgl_mulai'])) / (60 * 60 * 24) + 1;
        ?>

          <?php
          // Ambil ft_depan dari mobil pertama yang ada di dalam transaksi sewa ini
          $gambar_mobil = !empty($r['mobil'][0]['ft_depan'])
            ? BASE_URL . 'uploads/mobil/' . $r['mobil'][0]['ft_depan']
            : BASE_URL . 'aset/images/no-image.png';
          ?>



          <div class="riwayat-card">
            <div class="riwayat-image">
              <img src="<?= $gambar_mobil ?>" alt="Mobil">
              <div class="status-badge" style="background: <?= $statusInfo[1] ?>;">
                <i class="fa-solid <?= $statusInfo[2] ?>"></i>
                <?= $statusInfo[0] ?>
              </div>
            </div>
            <div class="riwayat-body">
              <!-- Header Info Transaksi -->
              <div class="riwayat-top">
                <div>
                  <h3>Transaksi #<?= htmlspecialchars($r['kode_sewa']) ?></h3>
                  <p class="meta"><?= count($r['mobil']) ?> Mobil Disewa</p>
                </div>
                <div class="riwayat-kode">
                  <small>Status Pembayaran</small>
                  <strong style="color: <?= $r['status_pembayaran'] == 'Lunas' ? '#27ae60' : '#e74c3c' ?>">
                    <?= $r['status_pembayaran'] ?>
                  </strong>
                </div>
              </div>

              <!-- Detail Utama -->
              <div class="riwayat-detail">
                <div class="detail-item">
                  <i class="fa-solid fa-calendar-day"></i>
                  <div>
                    <small>Tanggal Mulai</small>
                    <strong><?= date('d M Y', strtotime($r['tgl_mulai'])) ?></strong>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="fa-solid fa-calendar-check"></i>
                  <div>
                    <small>Tanggal Selesai</small>
                    <strong><?= date('d M Y', strtotime($r['tgl_selesai'])) ?></strong>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="fa-solid fa-hourglass-half"></i>
                  <div>
                    <small>Durasi</small>
                    <strong><?= $durasi ?> Hari</strong>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="fa-solid fa-money-bill"></i>
                  <div>
                    <small>Total Tagihan</small>
                    <strong><?= formatRupiah($r['total_tagihan_sewa']) ?></strong>
                  </div>
                </div>
              </div>

              <!-- Daftar Mobil yang Disewa (Looping di dalam Card) -->
              <div class="daftar-mobil">
                <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--gray-700);">
                  <i class="fa-solid fa-car"></i> Daftar Mobil
                </h4>
                <div class="mobil-list">
                  <?php foreach ($r['mobil'] as $m): ?>
                    <div class="mobil-item">
                      <img src="<?= BASE_URL . 'uploads/mobil/' . ($m['ft_depan'] ?: 'no-image.png') ?>" alt="<?= htmlspecialchars($m['nm_mobil']) ?>">
                      <div class="mobil-info">
                        <strong><?= htmlspecialchars($m['nm_mobil']) ?></strong>
                        <small><?= htmlspecialchars($m['jns_mobil']) ?> • <?= htmlspecialchars($m['no_plat']) ?></small>
                      </div>
                      <div class="mobil-harga">
                        <?= formatRupiah($m['hrg_sewa']) ?> <span>/hari</span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Info Denda (Jika ada) -->
              <?php if (!empty($r['denda'])): ?>
                <div class="denda-info">
                  <i class="fa-solid fa-triangle-exclamation"></i>
                  Terlambat <?= $r['denda']['telat_jam'] ?> jam
                  (Denda: <?= formatRupiah($r['total_denda']) ?>). Total Pembayaran Tagihan: <strong><?= formatRupiah($r['total_tagihan']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Info Sisa Tagihan -->
              <?php if ($r['sisa_tagihan'] > 0 && in_array($r['status_sewa'], ['diajukan', 'disetujui', 'aktif'])): ?>
                <div class="sisa-tagihan-info">
                  <i class="fa-solid fa-file-invoice-dollar"></i>
                  Sisa Tagihan: <strong><?= formatRupiah($r['sisa_tagihan']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Tombol Aksi -->
              <div class="riwayat-actions">

                <?php if ($r['status_sewa'] === 'diajukan' && $r['sisa_tagihan'] > 0): ?>
                  <!-- Tombol Bayar / Upload Bukti (Untuk tab Menunggu Pembayaran / Diajukan) -->
                  <a href="<?= BASE_URL ?>penyewa/controller/pembayaran_controller.php?kode_sewa=<?= urlencode($r['kode_sewa']) ?>" class="btn btn-gold btn-sm">
                    <i class="fa-solid fa-credit-card"></i> Bayar / Upload Bukti
                  </a>
                <?php endif; ?>

                <?php if ($r['status_sewa'] === 'diajukan'): ?>
                  <!-- Tombol Batalkan (Jawaban untuk pertanyaan g) -->
                  <a href="<?= BASE_URL ?>penyewa/controller/batal_controller.php?kode_sewa=<?= urlencode($r['kode_sewa']) ?>"
                    class="btn btn-sm"
                    style="background: #e74c3c; color: #fff;"
                    onclick="return confirm('Yakin ingin membatalkan transaksi ini?')">
                    <i class="fa-solid fa-ban"></i> Batalkan
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php
// Memanggil Footer
require_once __DIR__ . '/footer.php';
?>