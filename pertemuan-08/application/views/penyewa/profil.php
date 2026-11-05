<?php
$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$profil = $profil ?? [];
$old = $old ?? [];

$nik = $old['nik'] ?? $profil['nik'] ?? '';
$nama = $old['nm_lngkp'] ?? $profil['nm_lngkp'] ?? '';
$noHp = $old['no_hp'] ?? $profil['no_hp'] ?? '';
$alamat = $old['almt_lngkp'] ?? $profil['almt_lngkp'] ?? '';

$fotoKtp = $profil['ft_ktp'] ?? '';
$fotoSim = $profil['ft_sim_a'] ?? '';

require __DIR__ . '/../katalog/header.php';
require __DIR__ . '/../katalog/navbar.php';
?>

<div class="container" style="margin-top:30px;margin-bottom:50px">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <div class="panel panel-default">

        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-user"></i> Profil Penyewa</h3>
        </div>

        <div class="panel-body">

          <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= $e($flash) ?></div>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $e($error) ?></div>
          <?php endif; ?>

          <form action="<?= site_url('penyewa/profil') ?>" method="POST" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <h4>Informasi Akun</h4>
            <hr>

            <div class="form-group">
              <label>Username</label>
              <input type="text" class="form-control" value="<?= $e($profil['user_plg'] ?? '') ?>" readonly>
            </div>

            <div class="form-group">
              <label>NIK</label>
              <input type="text" name="nik" class="form-control" value="<?= $e($nik) ?>" maxlength="16" pattern="[0-9]{16}" required>
            </div>

            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="nm_lngkp" class="form-control" value="<?= $e($nama) ?>" maxlength="100" required>
            </div>

            <div class="form-group">
              <label>Nomor HP</label>
              <input type="text" name="no_hp" class="form-control" value="<?= $e($noHp) ?>" maxlength="15" pattern="[0-9+(). -]{8,15}" required>
            </div>

            <div class="form-group">
              <label>Alamat Lengkap</label>
              <textarea name="almt_lngkp" class="form-control" rows="3" maxlength="300" required><?= $e($alamat) ?></textarea>
            </div>

            <h4>Foto Identitas</h4>
            <hr>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Foto KTP</label>

                  <?php if ($fotoKtp): ?>
                    <img src="<?= site_url('penyewa/foto/ktp/' . rawurlencode($profil['user_plg'] ?? '')) ?>" class="img-thumbnail" style="max-width:100%;max-height:200px">
                  <?php endif; ?>

                  <input type="file" name="ft_ktp" class="form-control" accept=".jpg,.jpeg,.jfif,.pjpeg,.pjp,.png,.webp">
                  <small class="text-muted">JPG/JPEG/JFIF, PNG, WEBP. Maksimal 5 MB.</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Foto SIM A</label>

                  <?php if ($fotoSim): ?>
                    <img src="<?= site_url('penyewa/foto/sim/' . rawurlencode($profil['user_plg'] ?? '')) ?>" class="img-thumbnail" style="max-width:100%;max-height:200px">
                  <?php endif; ?>

                  <input type="file" name="ft_sim_a" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                  <small class="text-muted">JPG, PNG, WEBP. Maksimal 2 MB.</small>
                </div>
              </div>
            </div>

            <h4>Ubah Password</h4>
            <hr>

            <div class="alert alert-info">
              Kosongkan semua kolom password jika tidak ingin mengganti password.
            </div>

            <div class="form-group">
              <label>Password Lama</label>
              <input type="password" name="password_lama" class="form-control">
            </div>

            <div class="form-group">
              <label>Password Baru</label>
              <input type="password" name="password_baru" class="form-control" minlength="8">
            </div>

            <div class="form-group">
              <label>Konfirmasi Password Baru</label>
              <input type="password" name="konfirmasi_password" class="form-control" minlength="8">
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Simpan Perubahan
            </button>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../katalog/footer.php'; ?>