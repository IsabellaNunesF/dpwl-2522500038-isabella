<?php
// P9: Daftar transaksi milik penyewa
require APPPATH . 'views/katalog/header.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};

require APPPATH . 'views/katalog/navbar.php';

$daftar = is_array($daftar ?? null) ? $daftar : [];

$flashType = (string) ($flash['type'] ?? '');
$flashMessage = (string) ($flash['message'] ?? '');
$flashClass = $flashType === 'success' ? 'alert-success' : ($flashType === 'info' ? 'alert-info' : 'alert-danger');

$badgeSewa = [
  'diajukan'  => ['bg-warning text-dark', 'Diajukan'],
  'disetujui' => ['bg-success', 'Disetujui'],
  'ditolak'   => ['bg-danger', 'Ditolak'],
  'aktif'     => ['bg-primary', 'Aktif'],
  'selesai'   => ['bg-secondary', 'Selesai'],
  'batal'     => ['bg-dark', 'Batal']
];
$badgeDp = [
  'pending' => ['bg-warning text-dark', 'Menunggu verifikasi'],
  'valid'   => ['bg-success', 'Valid'],
  'invalid' => ['bg-danger', 'Ditolak']
];
?>
<section class="bg-success py-4">
  <div class="container">
    <h1 class="h2 text-white mt-3">Transaksi Saya</h1>
    <p class="text-white mb-0">Pantau status sewa dan pembayaran uang muka (DP) Anda.</p>
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

    <?php if (count($daftar) === 0): ?>
      <div class="text-center text-muted py-5">
        <p class="mb-3">Anda belum memiliki transaksi.</p>
        <a class="btn btn-success" href="<?= $e(site_url('katalog')) ?>">Lihat Katalog</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
          <thead class="table-light">
            <tr>
              <th scope="col">Kode Transaksi</th>
              <th scope="col">Periode</th>
              <th scope="col" class="text-end">Total</th>
              <th scope="col" class="text-end">DP</th>
              <th scope="col">Status Sewa</th>
              <th scope="col">Status DP</th>
              <th scope="col" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daftar as $row): ?>
              <?php
              $bs = $badgeSewa[$row['status_sewa']] ?? ['bg-secondary', $row['status_sewa']];
              $bd = $badgeDp[(string) ($row['status_dp'] ?? '')] ?? null;
              $perluBukti = $row['status_sewa'] === 'diajukan'
                && (string) $row['status_dp'] === 'pending'
                && empty($row['ft_byr']);
              ?>
              <tr>
                <td><?= $e($row['kode_sewa']) ?></td>
                <td class="text-nowrap">
                  <?= $e($row['tgl_mulai']) ?> s/d <?= $e($row['tgl_selesai']) ?><br>
                  <small class="text-muted">Ambil <?= $e(substr((string) $row['wkt_ambil'], 0, 5)) ?> WIB</small>
                </td>
                <td class="text-end text-nowrap"><?= $e($formatRupiah($row['total'])) ?></td>
                <td class="text-end text-nowrap">
                  <?= $row['nominal_dp'] !== null ? $e($formatRupiah($row['nominal_dp'])) : '-' ?>
                </td>
                <td><span class="badge <?= $e($bs[0]) ?>"><?= $e($bs[1]) ?></span></td>
                <td>
                  <?php if ($bd !== null): ?>
                    <span class="badge <?= $e($bd[0]) ?>"><?= $e($bd[1]) ?></span>
                    <?php if ($perluBukti): ?>
                      <br><small class="text-danger">Bukti transfer perlu diunggah</small>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a class="btn btn-sm btn-outline-success"
                    href="<?= $e(site_url('transaksi/pembayaran?kode_sewa=' . rawurlencode($row['kode_sewa']))) ?>">
                    <?= $perluBukti ? 'Bayar DP' : 'Detail' ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>