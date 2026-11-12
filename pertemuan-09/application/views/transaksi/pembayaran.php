<?php
// P9: Halaman Pembayaran Awal (DP) penyewa
require APPPATH . 'views/katalog/header.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};

require APPPATH . 'views/katalog/navbar.php';

$kodeSewa = (string) ($transaksi['kode_sewa'] ?? '');
$detail = is_array($transaksi['detail'] ?? null) ? $transaksi['detail'] : [];
$dp = is_array($transaksi['dp'] ?? null) ? $transaksi['dp'] : null;
$total = (int) ($transaksi['total'] ?? 0);
$nominalDp = $dp !== null ? (int) $dp['nominal'] : (int) round($total * 30 / 100);
$sisa = $total - $nominalDp;

$statusDp = $dp['sttus_byr'] ?? null;
$adaBukti = $dp !== null && !empty($dp['ft_byr']);
$bolehUnggah = $dp !== null
  && ($transaksi['status_sewa'] ?? '') === 'diajukan'
  && in_array($statusDp, ['pending', 'invalid'], true);

$flashType = (string) ($flash['type'] ?? '');
$flashMessage = (string) ($flash['message'] ?? '');
$flashClass = $flashType === 'success' ? 'alert-success' : 'alert-danger';

$badgeDp = [
  'pending' => ['bg-warning text-dark', 'Menunggu verifikasi admin'],
  'valid'   => ['bg-success', 'Valid'],
  'invalid' => ['bg-danger', 'Ditolak admin']
];
$bd = $badgeDp[(string) $statusDp] ?? ['bg-secondary', '-'];

$buktiUrl = $dp !== null
  ? site_url('transaksi/bukti?kode_bayar=' . rawurlencode($dp['kode_bayar']))
  : '';
?>
<section class="bg-success py-4">
  <div class="container">
    <h1 class="h2 text-white mt-3">Pembayaran Uang Muka (DP)</h1>
    <p class="text-white mb-0">Transfer DP sesuai nominal, lalu unggah bukti pembayaran untuk diverifikasi admin.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if ($flashMessage !== ''): ?>
      <div class="alert <?= $flashClass ?> alert-dismissible fade show" role="alert">
        <?= $e($flashMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-light">
            <h2 class="h5 mb-0">Ringkasan Sewa</h2>
          </div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-4">Kode transaksi</dt>
              <dd class="col-sm-8"><?= $e($kodeSewa) ?></dd>
              <dt class="col-sm-4">Periode</dt>
              <dd class="col-sm-8"><?= $e($transaksi['tgl_mulai']) ?> s/d <?= $e($transaksi['tgl_selesai']) ?></dd>
              <dt class="col-sm-4">Waktu pengambilan</dt>
              <dd class="col-sm-8"><?= $e(substr((string) $transaksi['wkt_ambil'], 0, 5)) ?> WIB</dd>
              <dt class="col-sm-4">Status sewa</dt>
              <dd class="col-sm-8"><?= $e(ucfirst((string) $transaksi['status_sewa'])) ?></dd>
            </dl>
          </div>
        </div>

        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle bg-white mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Kendaraan</th>
                <th scope="col" class="text-end">Harga/Hari</th>
                <th scope="col" class="text-center">Durasi</th>
                <th scope="col" class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($detail as $item): ?>
                <tr>
                  <td><?= $e($item['nm_mobil']) ?><br><small class="text-muted"><?= $e($item['no_plat']) ?></small></td>
                  <td class="text-end text-nowrap"><?= $e($formatRupiah($item['hrg_sewa'])) ?></td>
                  <td class="text-center"><?= (int) $item['n_hari'] ?> hari</td>
                  <td class="text-end text-nowrap">
                    <?= $e($formatRupiah((int) $item['hrg_sewa'] * (int) $item['n_hari'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-success mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Total sewa</span>
              <strong><?= $e($formatRupiah($total)) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>DP yang harus dibayar (30%)</span>
              <strong class="text-success"><?= $e($formatRupiah($nominalDp)) ?></strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Sisa dibayar saat pelunasan</span>
              <strong><?= $e($formatRupiah($sisa)) ?></strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
              <span>Status pembayaran DP</span>
              <span class="badge <?= $e($bd[0]) ?>"><?= $e($bd[1]) ?></span>
            </div>
            <?php if ($dp !== null): ?>
              <p class="small text-muted mt-2 mb-0">
                Kode pembayaran: <?= $e($dp['kode_bayar']) ?>
                <?php if (!empty($dp['tgl_wkt_ver'])): ?>
                  <br>Diverifikasi: <?= $e($dp['tgl_wkt_ver']) ?>
                <?php endif; ?>
              </p>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($statusDp === 'invalid'): ?>
          <div class="alert alert-danger">
            Pembayaran DP ditolak oleh admin. Silakan unggah ulang bukti transfer yang jelas dan sesuai nominal.
          </div>
        <?php elseif ($statusDp === 'valid'): ?>
          <div class="alert alert-success">
            DP sudah diverifikasi. Transaksi Anda disetujui; silakan datang pada waktu pengambilan.
          </div>
        <?php elseif ($statusDp === 'pending' && $adaBukti): ?>
          <div class="alert alert-info">
            Bukti pembayaran sudah diterima dan sedang menunggu verifikasi admin.
          </div>
        <?php endif; ?>

        <?php if ($adaBukti): ?>
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
              <h2 class="h6 mb-0">Bukti Transfer Terunggah</h2>
            </div>
            <div class="card-body text-center">
              <a href="<?= $e($buktiUrl) ?>" target="_blank" rel="noopener">
                <img src="<?= $e($buktiUrl) ?>" alt="Bukti transfer <?= $e($dp['kode_bayar']) ?>"
                  class="img-fluid rounded" style="max-height: 260px; object-fit: contain;">
              </a>
              <p class="small text-muted mt-2 mb-0">Diunggah: <?= $e($dp['tgl_wkt_byr']) ?></p>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($bolehUnggah): ?>
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
              <h2 class="h6 mb-0"><?= $adaBukti ? 'Unggah Ulang Bukti Transfer' : 'Unggah Bukti Transfer' ?></h2>
            </div>
            <div class="card-body">
              <form method="post" enctype="multipart/form-data"
                action="<?= $e(site_url('transaksi/unggah-bukti')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_sewa" value="<?= $e($kodeSewa) ?>">
                <div class="mb-3">
                  <label for="ft_byr" class="form-label">Foto bukti transfer (JPG/PNG/WEBP, maks. 5 MB)</label>
                  <input type="file" class="form-control" id="ft_byr" name="ft_byr"
                    accept="image/jpeg,image/png,image/webp" required>
                </div>
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-upload me-1" aria-hidden="true"></i> Kirim Bukti Pembayaran
                </button>
              </form>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-4">
      <a class="btn btn-outline-secondary" href="<?= $e(site_url('transaksi/saya')) ?>">
        <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Kembali ke Transaksi Saya
      </a>
    </div>
  </div>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>