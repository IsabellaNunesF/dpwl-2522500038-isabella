<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$detail = $detail ?? [];
$daftarKendaraan = $daftarKendaraan ?? [];
$flash = $flash ?? null;

$e = static fn($value): string => htmlspecialchars(
  (string) $value,
  ENT_QUOTES,
  'UTF-8'
);

$kodeSewa = (string) ($detail['kode_sewa'] ?? '');
$statusSewa = (string) ($detail['status_sewa'] ?? '');

$ktp = trim((string) ($detail['ft_ktp'] ?? ''));
$simA = trim((string) ($detail['ft_sim_a'] ?? ''));

$ktpTersedia = $ktp !== '';
$simTersedia = $simA !== '';

$dpValid = ($detail['sttus_byr'] ?? '') === 'valid';

$bolehSerahTerima = $statusSewa === 'disetujui'
  && $dpValid
  && $ktpTersedia
  && $simTersedia
  && count($daftarKendaraan) > 0;

$urlProses = site_url(
  'admin/serah-terima/proses/'
    . str_replace('/', '~', $kodeSewa)
);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Detail Serah Terima
      <small>Informasi transaksi dan penyerahan kendaraan</small>
    </h1>

    <ol class="breadcrumb">
      <li>
        <a href="<?= $e(site_url('admin')) ?>">Dashboard</a>
      </li>
      <li>
        <a href="<?= $e(site_url('admin/serah-terima')) ?>">
          Serah Terima Kendaraan
        </a>
      </li>
      <li class="active">Detail</li>
    </ol>
  </section>

  <section class="content">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <?php $jenisFlash = ($flash['type'] ?? '') === 'success'
        ? 'success'
        : (($flash['type'] ?? '') === 'info' ? 'info' : 'danger'); ?>
      <div class="alert alert-<?= $e($jenisFlash) ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">
          &times;
        </button>
        <?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Informasi Transaksi dan Penyewa</h3>
      </div>

      <div class="box-body">
        <dl class="dl-horizontal">
          <dt>Kode transaksi</dt>
          <dd><?= $e($kodeSewa) ?></dd>

          <dt>Nama penyewa</dt>
          <dd><?= $e($detail['nm_lngkp'] ?? '') ?></dd>

          <dt>Username</dt>
          <dd><?= $e($detail['user_plg'] ?? '') ?></dd>

          <dt>NIK</dt>
          <dd><?= $e($detail['nik'] ?? '') ?></dd>

          <dt>Nomor HP</dt>
          <dd><?= $e($detail['no_hp'] ?? '') ?></dd>

          <dt>Alamat</dt>
          <dd><?= nl2br($e($detail['almt_lngkp'] ?? '')) ?></dd>

          <dt>Periode sewa</dt>
          <dd>
            <?= $e($detail['tgl_mulai'] ?? '') ?>
            s.d.
            <?= $e($detail['tgl_selesai'] ?? '') ?>
          </dd>

          <dt>Tanggal pengambilan</dt>
          <dd><?= $e($detail['tgl_mulai'] ?? '') ?></dd>

          <dt>Waktu pengambilan</dt>
          <dd><?= $e($detail['wkt_ambil'] ?? '') ?></dd>

          <dt>Status transaksi</dt>
          <dd>
            <span class="label label-<?= $statusSewa === 'aktif' ? 'success' : 'info' ?>">
              <?= $e($statusSewa) ?>
            </span>
          </dd>
        </dl>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Informasi Pembayaran DP</h3>
      </div>

      <div class="box-body">
        <dl class="dl-horizontal">
          <dt>Kode pembayaran</dt>
          <dd><?= $e($detail['kode_bayar'] ?? '-') ?></dd>

          <dt>Tanggal pembayaran</dt>
          <dd><?= $e($detail['tgl_wkt_byr'] ?? '-') ?></dd>
          <dt>Nominal DP</dt>
          <dd>
            Rp <?= number_format(
                  (int) ($detail['nominal_dp'] ?? 0),
                  0,
                  ',',
                  '.'
                ) ?>
          </dd>

          <dt>Metode pembayaran</dt>
          <dd><?= $e($detail['mtode_byr'] ?? '-') ?></dd>

          <dt>Status pembayaran</dt>
          <dd>
            <span class="label label-<?= $dpValid ? 'success' : 'danger' ?>">
              <?= $e($detail['sttus_byr'] ?? 'Tidak tersedia') ?>
            </span>
          </dd>

          <dt>Waktu verifikasi</dt>
          <dd><?= $e($detail['tgl_wkt_ver'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Daftar Kendaraan yang Diserahterimakan</h3>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>No. Polisi</th>
              <th>Nama Kendaraan</th>
              <th>Tarif Snapshot / Hari</th>
              <th>Lama Sewa</th>
              <th>Status Kendaraan</th>
            </tr>
          </thead>

          <tbody>
            <?php if (!$daftarKendaraan): ?>
              <tr>
                <td colspan="6" class="text-center">
                  Data kendaraan tidak ditemukan.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarKendaraan as $i => $kendaraan): ?>
                <tr>
                  <td><?= (int) $i + 1 ?></td>
                  <td><?= $e($kendaraan['no_plat'] ?? '') ?></td>
                  <td><?= $e($kendaraan['nm_mobil'] ?? '') ?></td>
                  <td>
                    Rp <?= number_format(
                          (int) ($kendaraan['hrg_sewa'] ?? 0),
                          0,
                          ',',
                          '.'
                        ) ?>
                  </td>
                  <td>
                    <?= (int) ($kendaraan['n_hari'] ?? 0) ?> hari
                  </td>
                  <td><?= $e($kendaraan['status_unit'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="alert alert-info">
          Seluruh kendaraan dalam satu transaksi diserahterimakan
          secara bersamaan dan menjadi tanggung jawab penyewa yang
          tercatat pada transaksi.
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Dokumen Wajib Penyewa</h3>
      </div>

      <div class="box-body">
        <div class="row">
          <div class="col-md-6">
            <h4>Kartu Tanda Penduduk (KTP)</h4>

            <?php if ($ktpTersedia): ?>
              <p>
                <span class="label label-success">Tersedia</span>
              </p>
              <p><?= $e($ktp) ?></p>
              <a
                href="<?= $e(base_url(
                        'uploads/penyewa/' . rawurlencode($ktp)
                      )) ?>"
                target="_blank"
                rel="noopener">
                Lihat dokumen KTP
              </a>
            <?php else: ?>
              <div class="alert alert-warning">
                Dokumen KTP belum tersedia.
              </div>
            <?php endif; ?>
          </div>

          <div class="col-md-6">
            <h4>Surat Izin Mengemudi (SIM A)</h4>

            <?php if ($simTersedia): ?>
              <p>
                <span class="label label-success">Tersedia</span>
              </p>
              <p><?= $e($simA) ?></p>
              <a
                href="<?= $e(base_url(
                        'uploads/penyewa/' . rawurlencode($simA)
                      )) ?>"
                target="_blank"
                rel="noopener">
                Lihat dokumen SIM A
              </a>
            <?php else: ?>
              <div class="alert alert-warning">
                Dokumen SIM A belum tersedia.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Pencatatan Serah Terima Kendaraan</h3>
      </div>

      <div class="box-body">
        <?php if ($statusSewa === 'aktif'): ?>
          <div class="alert alert-success">
            Transaksi ini sudah berstatus <strong>aktif</strong>.
            Proses serah terima telah dicatat dan tidak dapat
            dilakukan kembali.
          </div>

        <?php elseif ($statusSewa !== 'disetujui'): ?>
          <div class="alert alert-warning">
            Status transaksi tidak memenuhi syarat serah terima.
            Transaksi harus berstatus <strong>disetujui</strong>.
          </div>

        <?php elseif (!$dpValid): ?>
          <div class="alert alert-warning">
            Pembayaran DP belum berstatus valid. Serah terima
            belum dapat dilakukan.
          </div>

        <?php elseif (!$daftarKendaraan): ?>
          <div class="alert alert-warning">
            Transaksi tidak memiliki data kendaraan.
          </div>

        <?php else: ?>
          <div class="alert alert-info">
            Sebelum serah terima, pastikan pembayaran DP telah valid,
            dokumen KTP dan SIM A tersedia, perjanjian sewa telah
            disampaikan, serta seluruh kendaraan, kunci, dan dokumen
            pendukung telah diserahkan kepada penyewa.
          </div>

          <form
            method="post"
            action="<?= $e($urlProses) ?>"
            enctype="multipart/form-data"
            id="form-serah-terima">

            <div class="form-group">
              <label for="ft_ktp">
                Unggah KTP
                <?= $ktpTersedia ? '(opsional, jika ingin mengganti)' : '(wajib)' ?>
              </label>

              <input
                type="file"
                name="ft_ktp"
                id="ft_ktp"
                class="form-control"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                <?= $ktpTersedia ? '' : 'required' ?>>

              <p class="help-block">
                Format JPEG, PNG, atau WEBP. Maksimal 5 MB.
              </p>
            </div>

            <div class="form-group">
              <label for="ft_sim_a">
                Unggah SIM A
                <?= $simTersedia ? '(opsional, jika ingin mengganti)' : '(wajib)' ?>
              </label>

              <input
                type="file"
                name="ft_sim_a"
                id="ft_sim_a"
                class="form-control"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                <?= $simTersedia ? '' : 'required' ?>>

              <p class="help-block">
                Format JPEG, PNG, atau WEBP. Maksimal 5 MB.
              </p>
            </div>

            <div class="alert alert-warning">
              Setelah dikonfirmasi, sistem akan mencatat waktu aktual
              pengambilan pada <strong>wkt_ambil</strong> dan mengubah
              status transaksi menjadi <strong>aktif</strong>.
              Pastikan seluruh kendaraan telah diserahkan secara fisik.
            </div>

            <button
              type="submit"
              class="btn btn-success"
              onclick="return confirm('Apakah seluruh kendaraan dan dokumen pendukung telah diserahkan 
kepada penyewa? Proses ini tidak dapat diulang.')">
              <i class="fa fa-check"></i>
              Konfirmasi Serah Terima
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <a
      href="<?= $e(site_url('admin/serah-terima')) ?>"
      class="btn btn-default">
      <i class="fa fa-arrow-left"></i>
      Kembali ke daftar serah terima
    </a>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>