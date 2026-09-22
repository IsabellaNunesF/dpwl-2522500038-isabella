<?php
// Memanggil Header
require_once __DIR__ . '/header.php';

// Ambil ft_depan dari mobil pertama yang ada di dalam transaksi untuk gambar utama
$gambar_mobil = !empty($detail[0]['ft_depan'])
  ? BASE_URL . 'uploads/mobil/' . $detail[0]['ft_depan']
  : BASE_URL . 'aset/images/no-image.png';
?>

<!-- Konten Halaman Konfirmasi Pembatalan -->
<main class="page-wrap">
  <div class="container" style="max-width: 700px;">
    <div class="auth-card" style="text-align: center; border-top-color: #e74c3c;">
      <!-- Ikon Peringatan -->
      <div style="width: 70px; height: 70px; margin: 0 auto 20px; background: rgba(231, 76, 60, 0.1); border-radius: 50%; display: grid; place-items: center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 32px; color: #e74c3c;"></i>
      </div>

      <h2 style="color: #e74c3c; font-size: 24px; margin-bottom: 8px;">Batalkan Transaksi?</h2>
      <p class="sub" style="margin-bottom: 24px; color: var(--gray-500);">Anda akan membatalkan transaksi rental mobil. Tindakan ini tidak dapat dibatalkan.</p>

      <!-- Box Detail Transaksi -->
      <div style="background: var(--gray-100); border-radius: 12px; padding: 20px; text-align: left; margin-bottom: 24px;">
        <!-- Header Detail -->
        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-200);">
          <img src="<?= $gambar_mobil ?>" alt="Mobil" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">
          <div>
            <strong style="display: block; font-size: 16px; color: var(--black);">Transaksi #<?= htmlspecialchars($kode_sewa) ?></strong>
            <small style="color: var(--gray-500);"><?= count($detail) ?> Mobil Disewa</small>
          </div>
        </div>

        <!-- Tanggal Sewa -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px;">
          <div>
            <small style="color: var(--gray-500); display: block;">Tanggal Mulai</small>
            <strong><?= date('d M Y', strtotime($detail[0]['tgl_mulai'])) ?></strong>
          </div>
          <div>
            <small style="color: var(--gray-500); display: block;">Tanggal Selesai</small>
            <strong><?= date('d M Y', strtotime($detail[0]['tgl_selesai'])) ?></strong>
          </div>
        </div>

        <!-- Daftar Mobil -->
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--gray-200);">
          <small style="color: var(--gray-500); display: block; margin-bottom: 8px;">Daftar Mobil</small>
          <?php foreach ($detail as $d): ?>
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
              <span><?= htmlspecialchars($d['nm_mobil']) ?> <small style="color:var(--gray-500)">(<?= htmlspecialchars($d['no_plat']) ?>)</small></span>
              <span style="color: var(--gold-dark); font-weight: 600;"><?= formatRupiah($d['hrg_sewa'] * $d['n_hari']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Total Harga -->
        <div style="margin-top: 16px; padding-top: 16px; border-top: 2px solid var(--gray-200); display: flex; justify-content: space-between; font-size: 16px; font-weight: 700;">
          <span>Total Tagihan</span>
          <span style="color: var(--gold-dark);"><?= formatRupiah($total_harga) ?></span>
        </div>
      </div>

      <!-- Form Konfirmasi Pembatalan -->
      <form method="POST" action="">
        <input type="hidden" name="kode_sewa" value="<?= htmlspecialchars($kode_sewa) ?>">
        <div style="display: flex; gap: 12px; justify-content: center;">
          <a href="<?= BASE_URL ?>penyewa/controller/riwayat_controller.php" class="btn" style="background: transparent; border: 1.5px solid var(--gray-300); color: var(--gray-700); flex: 1;">
            <i class="fa-solid fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn" style="background: #e74c3c; color: #fff; flex: 1; box-shadow: 0 10px 30px rgba(231, 76, 60, 0.25);">
            <i class="fa-solid fa-ban"></i> Ya, Batalkan
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<?php
// Memanggil Footer
require_once __DIR__ . '/footer.php';
?>