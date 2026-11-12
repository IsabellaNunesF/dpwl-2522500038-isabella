<?php
// P9: Verifikasi Pembayaran DP (Admin)
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$rupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

$pembayaran = $pembayaran ?? [];
$status     = $status ?? 'pending';
$flash      = $flash ?? null;
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';

$labelStatus = [
  'pending' => ['label-warning', 'Menunggu verifikasi'],
  'valid'   => ['label-success', 'Valid'],
  'invalid' => ['label-danger', 'Ditolak']
];

$tab = [
  'pending' => 'Menunggu',
  'valid'   => 'Valid',
  'invalid' => 'Ditolak',
  'semua'   => 'Semua'
];
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Verifikasi Pembayaran
      <small>Pembayaran awal (DP) penyewa</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Verifikasi Pembayaran</li>
    </ol>
  </section>

  <section class="content">

    <?php if (!empty($flash)): ?>
      <div class="alert <?= $flashClass ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i>
        <?= $e($flash['message'] ?? '') ?>
      </div>
    <?php endif; ?>

    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Data Pembayaran</h3>
        <div class="box-tools">
          <div class="btn-group">
            <?php foreach ($tab as $kunci => $nama): ?>
              <a href="<?= $e(site_url('admin/pembayaran?status=' . $kunci)) ?>"
                class="btn btn-sm <?= $status === $kunci ? 'btn-primary' : 'btn-default' ?>">
                <?= $e($nama) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped js-data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Kode Bayar</th>
                <th>Kode Sewa</th>
                <th>Penyewa</th>
                <th>Jenis</th>
                <th>Nominal</th>
                <th>Waktu Bayar</th>
                <th class="no-sort">Bukti</th>
                <th>Status</th>
                <th class="no-sort">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pembayaran as $no => $item): ?>
                <?php
                $st = (string) $item['sttus_byr'];
                $badge = $labelStatus[$st] ?? ['label-default', $st];
                $buktiUrl = site_url('transaksi/bukti?kode_bayar=' . rawurlencode($item['kode_bayar']));
                $adaBukti = !empty($item['ft_byr']);
                $bolehValid = $st === 'pending'
                  && $item['status_sewa'] === 'diajukan'
                  && ($item['mtode_byr'] !== 'transfer' || $adaBukti);
                $bolehTolak = $st === 'pending' && $item['status_sewa'] === 'diajukan';
                ?>
                <tr>
                  <td><?= (int) $no + 1 ?></td>
                  <td><?= $e($item['kode_bayar']) ?></td>
                  <td>
                    <?= $e($item['kode_sewa']) ?><br>
                    <small class="text-muted">
                      <?= $e($item['tgl_mulai']) ?> s/d <?= $e($item['tgl_selesai']) ?>
                      &middot; <?= $e($item['status_sewa']) ?>
                    </small>
                  </td>
                  <td>
                    <?= $e($item['nm_lngkp'] ?? '-') ?><br>
                    <small class="text-muted"><?= $e($item['user_plg']) ?></small>
                  </td>
                  <td>
                    <?= $e(strtoupper((string) $item['jns_byr'])) ?><br>
                    <small class="text-muted"><?= $e($item['mtode_byr']) ?></small>
                  </td>
                  <td class="text-right">
                    <?= $e($rupiah($item['nominal'])) ?><br>
                    <small class="text-muted">dari <?= $e($rupiah($item['total'])) ?></small>
                  </td>
                  <td><?= $e($item['tgl_wkt_byr']) ?></td>
                  <td>
                    <?php if ($adaBukti): ?>
                      <a href="<?= $e($buktiUrl) ?>" target="_blank" rel="noopener">
                        <img src="<?= $e($buktiUrl) ?>" alt="Bukti <?= $e($item['kode_bayar']) ?>"
                          style="width: 80px; height: 55px; object-fit: cover;">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">Belum ada</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="label <?= $e($badge[0]) ?>"><?= $e($badge[1]) ?></span>
                    <?php if (!empty($item['tgl_wkt_ver'])): ?>
                      <br><small class="text-muted"><?= $e($item['tgl_wkt_ver']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($st === 'pending'): ?>
                      <form method="post" style="display: inline"
                        action="<?= $e(site_url('admin/pembayaran/verifikasi')) ?>"
                        onsubmit="return confirm('Nyatakan pembayaran <?= $e($item['kode_bayar']) ?> VALID? Transaksi akan disetujui.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="kode_bayar" value="<?= $e($item['kode_bayar']) ?>">
                        <input type="hidden" name="aksi" value="valid">
                        <input type="hidden" name="status_filter" value="<?= $e($status) ?>">
                        <button type="submit" class="btn btn-success btn-xs"
                          <?= $bolehValid ? '' : 'disabled title="Bukti transfer belum tersedia"' ?>>
                          <i class="fa fa-check"></i> Valid
                        </button>
                      </form>
                      <form method="post" style="display: inline"
                        action="<?= $e(site_url('admin/pembayaran/verifikasi')) ?>"
                        onsubmit="return confirm('Tolak pembayaran <?= $e($item['kode_bayar']) ?>? Penyewa dapat mengunggah ulang bukti.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="kode_bayar" value="<?= $e($item['kode_bayar']) ?>">
                        <input type="hidden" name="aksi" value="invalid">
                        <input type="hidden" name="status_filter" value="<?= $e($status) ?>">
                        <button type="submit" class="btn btn-danger btn-xs" <?= $bolehTolak ? '' : 'disabled' ?>>
                          <i class="fa fa-times"></i> Tolak
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted">&mdash;</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>