<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$daftarSerahTerima = $daftarSerahTerima ?? [];
$flash = $flash ?? null;

$e = static fn($value): string => htmlspecialchars(
  (string) $value,
  ENT_QUOTES,
  'UTF-8'
);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Serah Terima Kendaraan
      <small>Daftar transaksi yang siap diserahterimakan</small>
    </h1>

    <ol class="breadcrumb">
      <li>
        <a href="<?= $e(site_url('admin')) ?>">
          <i class="fa fa-dashboard"></i> Dashboard
        </a>
      </li>
      <li class="active">Serah Terima Kendaraan</li>
    </ol>
  </section>
  <section class="content">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <?php
      $jenisFlash = ($flash['type'] ?? '') === 'success'
        ? 'success'
        : (($flash['type'] ?? '') === 'info' ? 'info' : 'danger');
      ?>
      <div class="alert alert-<?= $e($jenisFlash) ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">
          &times;
        </button>
        <?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">
          Transaksi yang Memenuhi Syarat Serah Terima
        </h3>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>Kode Sewa</th>
              <th>Penyewa</th>
              <th>No. HP</th>
              <th>Tanggal Pengambilan</th>
              <th>Waktu Rencana</th>
              <th>Jumlah Kendaraan</th>
              <th>Dokumen</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php if (!$daftarSerahTerima): ?>
              <tr>
                <td colspan="10" class="text-center">
                  Tidak ada transaksi yang siap diserahterimakan.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarSerahTerima as $i => $item): ?>
                <?php
                $ktpTersedia = !empty($item['ft_ktp']);
                $simTersedia = !empty($item['ft_sim_a']);
                ?>
                <tr>
                  <td><?= (int) $i + 1 ?></td>

                  <td>
                    <?= $e($item['kode_sewa'] ?? '') ?>
                  </td>

                  <td>
                    <?= $e($item['nm_lngkp'] ?? '') ?>
                    <br>
                    <small><?= $e($item['user_plg'] ?? '') ?></small>
                  </td>

                  <td><?= $e($item['no_hp'] ?? '') ?></td>

                  <td><?= $e($item['tgl_mulai'] ?? '') ?></td>

                  <td><?= $e($item['wkt_ambil'] ?? '') ?></td>

                  <td>
                    <?= (int) ($item['jumlah_kendaraan'] ?? 0) ?>
                    kendaraan
                  </td>

                  <td>
                    <span class="label label-<?= $ktpTersedia ? 'success' : 'danger' ?>">
                      KTP: <?= $ktpTersedia ? 'Ada' : 'Belum ada' ?>
                    </span>
                    <br><br>
                    <span class="label label-<?= $simTersedia ? 'success' : 'danger' ?>">
                      SIM A: <?= $simTersedia ? 'Ada' : 'Belum ada' ?>
                    </span>
                  </td>

                  <td>
                    <span class="label label-success">
                      <?= $e($item['status_sewa'] ?? '') ?>
                    </span>
                  </td>

                  <td>
                    <a
                      class="btn btn-primary btn-xs"
                      href="<?= $e(site_url(
                              'admin/serah-terima/detail/'
                                . str_replace(
                                  '/',
                                  '~',
                                  (string) ($item['kode_sewa'] ?? '')
                                )
                            )) ?>">
                      <i class="fa fa-search"></i>
                      Detail / Proses
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>