<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$mode = $mode ?? null;
$penyewa = $penyewa ?? [];
$penyewa_dipilih = $penyewa_dipilih ?? null;
$flash = $flash ?? null;
$error = $error ?? null;

$isForm = in_array($mode, ['tambah', 'ubah'], true);
$isEdit = $mode === 'ubah';

$dataForm = $penyewa_dipilih ?? [];

$userPlgDipilih = $dataForm['user_plg'] ?? '';

$formAction = $isEdit
  ? site_url('admin/mpenyewa/ubah/' . rawurlencode((string) $userPlgDipilih))
  : site_url('admin/mpenyewa/tambah');

function e($value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<div class="content-wrapper">

  <!-- Header -->
  <section class="content-header">
    <h1>
      Manajemen Penyewa
      <small>Kelola data penyewa</small>
    </h1>

    <ol class="breadcrumb">
      <li>
        <a href="<?= e(site_url('admin')) ?>">
          <i class="fa fa-dashboard"></i> Dashboard
        </a>
      </li>
      <li class="active">Manajemen Penyewa</li>
    </ol>
  </section>

  <section class="content">

    <!-- Flash Message -->
    <?php if (!empty($flash)): ?>
      <?php
      $flashClass = (($flash['type'] ?? '') === 'success')
        ? 'alert-success'
        : 'alert-danger';
      ?>

      <div class="alert <?= $flashClass ?> alert-dismissible">
        <button type="button"
          class="close"
          data-dismiss="alert"
          aria-hidden="true">&times;</button>

        <i class="fa <?= (($flash['type'] ?? '') === 'success')
                        ? 'fa-check'
                        : 'fa-warning' ?>"></i>

        <?= e($flash['message'] ?? '') ?>
      </div>
    <?php endif; ?>

    <!-- Error -->
    <?php if ($error !== null): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button"
          class="close"
          data-dismiss="alert"
          aria-hidden="true">&times;</button>

        <i class="fa fa-warning"></i>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!$isForm): ?>

      <!-- Tabel Data Penyewa -->
      <div class="box">

        <div class="box-header">
          <h3 class="box-title">Data Penyewa</h3>

          <div class="box-tools">
            <a href="<?= e(site_url('admin/mpenyewa/tambah')) ?>"
              class="btn btn-primary btn-sm">
              <i class="fa fa-plus"></i> Tambah Penyewa
            </a>
          </div>
        </div>

        <div class="box-body">
          <div class="table-responsive">

            <table id="penyewaTable"
              class="table table-bordered table-striped js-data-table"
              style="width:100%">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Username</th>
                  <th>NIK</th>
                  <th>Nama Lengkap</th>
                  <th>No. HP</th>
                  <th>Alamat</th>
                  <th>Aksi</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($penyewa as $no => $item): ?>
                  <tr>
                    <td><?= (int) $no + 1 ?></td>

                    <td><?= e($item['user_plg'] ?? '') ?></td>

                    <td><?= e($item['nik'] ?? '') ?></td>

                    <td><?= e($item['nm_lngkp'] ?? '') ?></td>

                    <td><?= e($item['no_hp'] ?? '') ?></td>

                    <td><?= e($item['almt_lngkp'] ?? '') ?></td>

                    <td>
                      <a class="btn btn-warning btn-xs"
                        href="<?= e(site_url(
                                'admin/mpenyewa/ubah/' .
                                  rawurlencode((string) ($item['user_plg'] ?? ''))
                              )) ?>">
                        <i class="fa fa-edit"></i> Ubah
                      </a>

                      <form method="post"
                        style="display:inline"
                        action="<?= e(site_url(
                                  'admin/mpenyewa/hapus/' .
                                    rawurlencode((string) ($item['user_plg'] ?? ''))
                                )) ?>"
                        onsubmit="return confirm('Yakin ingin menghapus penyewa ini?');">

                        <button type="submit"
                          class="btn btn-danger btn-xs">
                          <i class="fa fa-trash"></i> Hapus
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>


            </table>

          </div>
        </div>
      </div>

    <?php else: ?>

      <!-- Form Tambah / Ubah Penyewa -->
      <div class="box box-primary">

        <div class="box-header with-border">
          <h3 class="box-title">
            <?= $isEdit ? 'Ubah Data Penyewa' : 'Tambah Penyewa' ?>
          </h3>
        </div>

        <form role="form"
          method="post"
          action="<?= e($formAction) ?>">

          <div class="box-body">

            <?php if (!$isEdit): ?>

              <div class="form-group">
                <label for="user_plg">Username</label>

                <input type="text"
                  class="form-control"
                  id="user_plg"
                  name="user_plg"
                  required>
              </div>

            <?php else: ?>

              <div class="form-group">
                <label>Username</label>

                <input type="text"
                  class="form-control"
                  value="<?= e($dataForm['user_plg'] ?? '') ?>"
                  readonly>
              </div>

            <?php endif; ?>

            <div class="form-group">
              <label for="sandi">
                Password
                <?= $isEdit ? '(Kosongkan jika tidak diubah)' : '' ?>
              </label>

              <input type="password"
                class="form-control"
                id="sandi"
                name="sandi"
                <?= $isEdit ? '' : 'required' ?>>
            </div>

            <div class="form-group">
              <label for="nik">NIK</label>

              <input type="text"
                class="form-control"
                id="nik"
                name="nik"
                value="<?= e($dataForm['nik'] ?? '') ?>"
                required>
            </div>

            <div class="form-group">
              <label for="nm_lngkp">Nama Lengkap</label>

              <input type="text"
                class="form-control"
                id="nm_lngkp"
                name="nm_lngkp"
                value="<?= e($dataForm['nm_lngkp'] ?? '') ?>"
                required>
            </div>

            <div class="form-group">
              <label for="no_hp">No. HP</label>

              <input type="text"
                class="form-control"
                id="no_hp"
                name="no_hp"
                value="<?= e($dataForm['no_hp'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="almt_lngkp">Alamat Lengkap</label>

              <textarea class="form-control"
                id="almt_lngkp"
                name="almt_lngkp"
                rows="3"><?= e($dataForm['almt_lngkp'] ?? '') ?></textarea>
            </div>

          </div>

          <div class="box-footer">

            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Simpan Penyewa
            </button>

            <a href="<?= e(site_url('admin/mpenyewa')) ?>"
              class="btn btn-default">
              <i class="fa fa-times"></i> Batal
            </a>

          </div>

        </form>
      </div>

    <?php endif; ?>

  </section>
</div>
<style>
  #penyewaTable {
    width: 100% !important;
  }

  #penyewaTable th {
    white-space: nowrap;
    vertical-align: middle;
  }

  #penyewaTable td {
    vertical-align: middle;
  }

  #penyewaTable th:nth-child(1),
  #penyewaTable td:nth-child(1) {
    width: 40px;
  }

  #penyewaTable th:nth-child(2),
  #penyewaTable td:nth-child(2) {
    width: 90px;
  }

  #penyewaTable th:nth-child(3),
  #penyewaTable td:nth-child(3) {
    width: 150px;
  }

  #penyewaTable th:nth-child(4),
  #penyewaTable td:nth-child(4) {
    width: 150px;
  }

  #penyewaTable th:nth-child(5),
  #penyewaTable td:nth-child(5) {
    width: 110px;
  }

  #penyewaTable th:nth-child(7),
  #penyewaTable td:nth-child(7) {
    width: 100px;
    white-space: nowrap;
  }
</style>
<?php require APPPATH . 'views/templates/footer.php'; ?>