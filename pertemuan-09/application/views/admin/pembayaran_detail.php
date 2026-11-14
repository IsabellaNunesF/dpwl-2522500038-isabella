<?php
// P9: Detail Verifikasi Pembayaran DP (Admin)
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$rupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

$p = is_array($pembayaran ?? null) ? $pembayaran : [];
$flash = $flash ?? null;
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';

$st = (string) ($p['sttus_byr'] ?? '');
$labelStatus = [
  'pending' => ['label-warning', 'Menunggu verifikasi'],
  'valid'   => ['label-success', 'Valid'],
  'invalid' => ['label-danger', 'Ditolak']
];
$badge = $labelStatus[$st] ?? ['label-default', $st];

$adaBukti = !empty($p['ft_byr']);
$buktiUrl = site_url('transaksi/bukti?kode_bayar=' . rawurlencode((string) ($p['kode_bayar'] ?? '')));
$bolehVerifikasi = $st === 'pending'
  && ($p['status_sewa'] ?? '') === 'diajukan'
  && ($p['mtode_byr'] !== 'transfer' || $adaBukti);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Detail Verifikasi Pembayaran
      <small><?= $e($p['kode_bayar'] ?? '') ?></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="<?= $e(site_url('admin/pembayaran')) ?>">Verifikasi Pembayaran</a></li>
      <li class="active">Detail</li>
    </ol>
  </section>

  <section class="content">

    <?php if (!empty($flash)): ?>
      <div class="alert <?= $flashClass ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <?= $e($flash['message'] ?? '') ?>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Informasi Pembayaran dan Transaksi</h3>
          </div>
          <div class="box-body">
            <table class="table table-condensed">
              <tr>
                <th style="width: 40%">Kode pembayaran</th>
                <td><?= $e($p['kode_bayar'] ?? '') ?></td>
              </tr>
              <tr>
                <th>Kode transaksi</th>
                <td><?= $e($p['kode_sewa'] ?? '') ?></td>
              </tr>
              <tr>
                <th>Penyewa</th>
                <td><?= $e($p['nm_lngkp'] ?? '-') ?> (<?= $e($p['user_plg'] ?? '') ?>)</td>
              </tr>
              <tr>
                <th>Periode sewa</th>
                <td><?= $e($p['tgl_mulai'] ?? '') ?> s/d <?= $e($p['tgl_selesai'] ?? '') ?>,
                  ambil <?= $e(substr((string) ($p['wkt_ambil'] ?? ''), 0, 5)) ?> WIB</td>
              </tr>
              <tr>
                <th>Status sewa</th>
                <td><?= $e($p['status_sewa'] ?? '') ?></td>
              </tr>
              <tr>
                <th>Jenis / metode</th>
                <td><?= $e(strtoupper((string) ($p['jns_byr'] ?? ''))) ?> / <?= $e($p['mtode_byr'] ?? '') ?></td>
              </tr>
              <tr>
                <th>Total sewa</th>
                <td><?= $e($rupiah($p['total'] ?? 0)) ?></td>
              </tr>
              <tr>
                <th>Nominal pembayaran</th>
                <td><strong><?= $e($rupiah($p['nominal'] ?? 0)) ?></strong></td>
              </tr>
              <tr>
                <th>Waktu unggah bukti</th>
                <td><?= $e($p['tgl_wkt_byr'] ?? '') ?></td>
              </tr>
              <tr>
                <th>Status pembayaran</th>
                <td><span class="label <?= $e($badge[0]) ?>"><?= $e($badge[1]) ?></span></td>
              </tr>
              <tr>
                <th>Waktu verifikasi</th>
                <td><?= !empty($p['tgl_wkt_ver']) ? $e($p['tgl_wkt_ver']) : '-' ?></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title">Kendaraan yang Disewa</h3>
          </div>
          <div class="box-body no-padding">
            <table class="table table-condensed">
              <thead>
                <tr>
                  <th>Kendaraan</th>
                  <th class="text-right">Harga/hari</th>
                  <th class="text-center">Hari</th>
                  <th class="text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (($p['detail'] ?? []) as $item): ?>
                  <tr>
                    <td><?= $e($item['nm_mobil']) ?> <small class="text-muted">(<?= $e($item['no_plat']) ?>)</small></td>
                    <td class="text-right"><?= $e($rupiah($item['hrg_sewa'])) ?></td>
                    <td class="text-center"><?= (int) $item['n_hari'] ?></td>
                    <td class="text-right"><?= $e($rupiah((int) $item['hrg_sewa'] * (int) $item['n_hari'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Bukti Transfer</h3>
          </div>
          <div class="box-body text-center">
            <?php if ($adaBukti): ?>
              <a href="<?= $e($buktiUrl) ?>" target="_blank" rel="noopener">
                <img src="<?= $e($buktiUrl) ?>" alt="Bukti <?= $e($p['kode_bayar'] ?? '') ?>"
                  class="img-responsive center-block" style="max-height: 480px;">
              </a>
              <p class="text-muted" style="margin-top: 8px">Klik gambar untuk membuka ukuran penuh.</p>
            <?php else: ?>
              <p class="text-muted">Penyewa belum mengunggah bukti transfer.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title">Keputusan Verifikasi</h3>
          </div>
          <div class="box-body">
            <?php if ($bolehVerifikasi): ?>
              <p>Periksa bukti dan nominal <strong><?= $e($rupiah($p['nominal'] ?? 0)) ?></strong>, lalu tentukan hasilnya.</p>
              <form method="post" style="display: inline"
                action="<?= $e(site_url('admin/pembayaran/verifikasi')) ?>"
                onsubmit="return confirm('Nyatakan pembayaran ini VALID? Transaksi akan disetujui.');">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_bayar" value="<?= $e($p['kode_bayar']) ?>">
                <input type="hidden" name="aksi" value="valid">
                <input type="hidden" name="status_filter" value="pending">
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Terima</button>
              </form>
              <form method="post" style="display: inline"
                action="<?= $e(site_url('admin/pembayaran/verifikasi')) ?>"
                onsubmit="return confirm('Tolak pembayaran ini? Transaksi akan ditolak.');">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_bayar" value="<?= $e($p['kode_bayar']) ?>">
                <input type="hidden" name="aksi" value="invalid">
                <input type="hidden" name="status_filter" value="pending">
                <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Tolak</button>
              </form>
            <?php elseif ($st === 'pending' && !$adaBukti): ?>
              <p class="text-muted">Verifikasi belum dapat dilakukan karena bukti transfer belum diunggah.</p>
            <?php else: ?>
              <p class="text-muted">Pembayaran ini sudah diverifikasi dan tidak dapat diproses kembali.</p>
            <?php endif; ?>
            <hr>
            <a href="<?= $e(site_url('admin/pembayaran')) ?>" class="btn btn-default">
              <i class="fa fa-arrow-left"></i> Kembali ke daftar
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>
