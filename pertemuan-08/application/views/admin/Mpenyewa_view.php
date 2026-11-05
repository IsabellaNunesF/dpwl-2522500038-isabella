<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$penyewa         = $penyewa ?? [];
$flash           = $flash ?? null;
$flashClass      = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';
$error           = $error ?? null;
$mode            = $mode ?? null;
$penyewa_dipilih = $penyewa_dipilih ?? null;
$isForm          = in_array($mode, ['tambah', 'ubah'], true);
$isEdit          = $mode === 'ubah';
$userDipilih     = (string) ($penyewa_dipilih['user_plg'] ?? '');
$formAction      = $isEdit
  ? site_url('admin/mpenyewa/ubah/' . rawurlencode($userDipilih))
  : site_url('admin/mpenyewa/tambah');

$fotoUrl = fn(string $jenis, string $user) =>
site_url('penyewa/foto/' . $jenis . '/' . rawurlencode($user));
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Manajemen Penyewa
      <small>Kelola data penyewa</small>
    </h1>
    <ol class="breadcrumb">
      <li>
        <a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a>
      </li>
      <li class="active">Manajemen Penyewa</li>
    </ol>
  </section>

  <section class="content">

    <!-- Flash message hasil PRG -->
    <?php if (!empty($flash)): ?>
      <div class="alert <?= $flashClass ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i>
        <?= $e($flash['message'] ?? '') ?>
      </div>
    <?php endif; ?>

    <!-- Tabel data penyewa -->
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Data Penyewa</h3>
        <div class="box-tools">
          <a href="<?= $e(site_url('admin/mpenyewa/tambah')) ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Tambah Penyewa
          </a>
        </div>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table id="penyewaTable" class="table table-bordered table-striped js-data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Username</th>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>No. HP</th>
                <th>Alamat</th>
                <th class="no-sort">KTP</th>
                <th class="no-sort">SIM A</th>
                <th class="no-sort">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($penyewa as $no => $item): ?>
                <tr>
                  <td><?= (int) $no + 1 ?></td>
                  <td><?= $e($item['user_plg']) ?></td>
                  <td><?= $e($item['nik']) ?></td>
                  <td><?= $e($item['nm_lngkp']) ?></td>
                  <td><?= $e($item['no_hp']) ?></td>
                  <td><?= $e($item['almt_lngkp']) ?></td>
                  <td>
                    <?php if (!empty($item['ft_ktp'])): ?>
                      <a href="<?= $e($fotoUrl('ktp', $item['user_plg'])) ?>" target="_blank" rel="noopener">
                        <img src="<?= $e($fotoUrl('ktp', $item['user_plg'])) ?>"
                          alt="KTP <?= $e($item['nm_lngkp']) ?>"
                          style="width: 80px; height: 55px; object-fit: cover;">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">Belum ada</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($item['ft_sim_a'])): ?>
                      <a href="<?= $e($fotoUrl('sim', $item['user_plg'])) ?>" target="_blank" rel="noopener">
                        <img src="<?= $e($fotoUrl('sim', $item['user_plg'])) ?>"
                          alt="SIM A <?= $e($item['nm_lngkp']) ?>"
                          style="width: 80px; height: 55px; object-fit: cover;">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">Belum ada</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a class="btn btn-warning btn-xs"
                      href="<?= $e(site_url('admin/mpenyewa/ubah/' . rawurlencode($item['user_plg']))) ?>">
                      <i class="fa fa-edit"></i> Ubah
                    </a>
                    <form method="post" style="display: inline"
                      action="<?= $e(site_url('admin/mpenyewa/hapus/' . rawurlencode($item['user_plg']))) ?>"
                      onsubmit="return confirm('Hapus penyewa <?= $e($item['user_plg']) ?>? Data dan foto terkait akan dihapus.');">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-danger btn-xs">
                        <i class="fa fa-trash"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th>No</th>
                <th>Username</th>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>No. HP</th>
                <th>Alamat</th>
                <th>KTP</th>
                <th>SIM A</th>
                <th>Aksi</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <?php if ($error !== null): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-warning"></i>
        <?= $e($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($isForm): ?>
      <!-- Form Tambah/Ubah -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><?= $isEdit ? 'Ubah Data Penyewa' : 'Tambah Penyewa' ?></h3>
        </div>
        <form role="form" method="post" enctype="multipart/form-data" action="<?= $e($formAction) ?>">
          <?= csrf_field() ?>
          <div class="box-body">

            <div class="form-group">
              <label for="user_plg">Username</label>
              <input type="text" class="form-control" id="user_plg" name="user_plg"
                maxlength="15" pattern="[A-Za-z0-9._-]{3,15}"
                value="<?= $e($penyewa_dipilih['user_plg'] ?? '') ?>"
                <?= $isEdit ? 'readonly' : '' ?> required>
              <?php if (!$isEdit): ?>
                <p class="help-block">3-15 karakter: huruf, angka, titik, garis bawah, atau strip.</p>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="nik">NIK</label>
              <input type="text" class="form-control" id="nik" name="nik"
                maxlength="16" inputmode="numeric" pattern="[0-9]{16}"
                value="<?= $e($penyewa_dipilih['nik'] ?? '') ?>" required>
              <p class="help-block">16 digit angka.</p>
            </div>

            <div class="form-group">
              <label for="nm_lngkp">Nama Lengkap</label>
              <input type="text" class="form-control" id="nm_lngkp" name="nm_lngkp"
                maxlength="100" value="<?= $e($penyewa_dipilih['nm_lngkp'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="no_hp">Nomor HP</label>
              <input type="text" class="form-control" id="no_hp" name="no_hp"
                maxlength="15" pattern="[0-9+(). -]{8,15}"
                value="<?= $e($penyewa_dipilih['no_hp'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="almt_lngkp">Alamat Lengkap</label>
              <textarea class="form-control" id="almt_lngkp" name="almt_lngkp"
                maxlength="300" rows="3" required><?= $e($penyewa_dipilih['almt_lngkp'] ?? '') ?></textarea>
            </div>

            <hr>
            <h4>Password</h4>
            <?php if ($isEdit): ?>
              <p class="help-block">Kosongkan kedua kolom jika password tidak ingin diubah.</p>
            <?php endif; ?>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sandi"><?= $isEdit ? 'Password Baru' : 'Password' ?></label>
                  <input type="password" class="form-control" id="sandi" name="sandi"
                    minlength="8" maxlength="72" autocomplete="new-password"
                    <?= $isEdit ? '' : 'required' ?>>
                  <p class="help-block">Minimal 8 karakter. Disimpan dalam bentuk hash.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="konfirmasi_sandi">Konfirmasi Password</label>
                  <input type="password" class="form-control" id="konfirmasi_sandi" name="konfirmasi_sandi"
                    minlength="8" maxlength="72" autocomplete="new-password"
                    <?= $isEdit ? '' : 'required' ?>>
                </div>
              </div>
            </div>

            <hr>
            <h4>Foto Identitas</h4>
            <?php foreach (['ft_ktp' => ['Foto KTP', 'ktp'], 'ft_sim_a' => ['Foto SIM A', 'sim']] as $field => [$label, $jenis]): ?>
              <div class="form-group">
                <label for="<?= $e($field) ?>"><?= $e($label) ?></label>
                <input type="file" class="form-control" id="<?= $e($field) ?>" name="<?= $e($field) ?>"
                  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <p class="help-block">JPG, PNG, atau WEBP; maksimal 2 MB.</p>
                <div class="photo-preview" style="margin-top: 10px;">
                  <?php if ($isEdit && !empty($penyewa_dipilih[$field])): ?>
                    <p class="help-block">Foto saat ini:</p>
                    <img class="img-thumbnail"
                      src="<?= $e($fotoUrl($jenis, $userDipilih)) ?>"
                      alt="<?= $e($label) ?> saat ini"
                      style="width: 180px; height: 120px; object-fit: cover;">
                    <p class="help-block">Kosongkan jika tidak ingin menggantinya.</p>
                  <?php else: ?>
                    <p class="help-block">Belum ada foto tersimpan.</p>
                  <?php endif; ?>
                  <div class="new-photo-preview" style="display: none; margin-top: 8px;">
                    <p class="help-block">Pratinjau foto yang dipilih:</p>
                    <img class="img-thumbnail new-photo-image"
                      alt="Pratinjau <?= $e($label) ?>"
                      style="width: 180px; height: 120px; object-fit: cover;">
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

          </div>
          <div class="box-footer">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Simpan Penyewa
            </button>
            <a href="<?= $e(site_url('admin/mpenyewa')) ?>" class="btn btn-default">
              <i class="fa fa-times"></i> Batal
            </a>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>

<?php if ($isForm): ?>
  <script>
    (function() {
      'use strict';
      var MAX = 2 * 1024 * 1024;
      document.querySelectorAll('.form-group input[type="file"][name^="ft_"]').forEach(function(input) {
        var box = input.closest('.form-group');
        var preview = box.querySelector('.new-photo-preview');
        var image = box.querySelector('.new-photo-image');
        var previousUrl = null;

        input.addEventListener('change', function() {
          if (previousUrl) {
            URL.revokeObjectURL(previousUrl);
            previousUrl = null;
          }

          var file = input.files && input.files.length ? input.files[0] : null;

          if (!file) {
            preview.style.display = 'none';
            image.removeAttribute('src');
            return;
          }

          if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1 || file.size < 1 || file.size > MAX) {
            alert('Foto harus JPG, PNG, atau WEBP dan maksimal 2 MB.');
            input.value = '';
            preview.style.display = 'none';
            image.removeAttribute('src');
            return;
          }

          previousUrl = URL.createObjectURL(file);
          image.src = previousUrl;
          preview.style.display = 'block';
        });
      });
    })();
  </script>
<?php endif; ?>