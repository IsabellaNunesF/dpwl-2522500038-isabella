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

<!-- Start Header -->
<section class="bg-success py-5">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h1 class="h2 text-white mt-3">Profil Penyewa</h1>
        <p class="text-white">Perbarui data diri, foto identitas, dan password Anda.</p>
      </div>
    </div>
  </div>
</section>
<!-- End Header -->

<section class="container py-5">
  <div class="row py-3">
    <div class="col-lg-8 m-auto">
      <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">

          <?php if (!empty($flash)): ?>
            <div class="alert alert-success" role="alert"><?= $e($flash) ?></div>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?= $e($error) ?></div>
          <?php endif; ?>

          <form action="<?= $e(site_url('penyewa/profil')) ?>" method="post" enctype="multipart/form-data" autocomplete="off">
            <?= csrf_field() ?>

            <h2 class="h4 text-success mb-3">Informasi Akun</h2>

            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="<?= $e($profil['user_plg'] ?? '') ?>" readonly>
            </div>

            <div class="mb-3">
              <label for="nik" class="form-label">NIK</label>
              <input type="text" id="nik" name="nik" class="form-control" value="<?= $e($nik) ?>" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" required>
            </div>

            <div class="mb-3">
              <label for="nm_lngkp" class="form-label">Nama Lengkap</label>
              <input type="text" id="nm_lngkp" name="nm_lngkp" class="form-control" value="<?= $e($nama) ?>" maxlength="100" required>
            </div>

            <div class="mb-3">
              <label for="no_hp" class="form-label">Nomor HP</label>
              <input type="text" id="no_hp" name="no_hp" class="form-control" value="<?= $e($noHp) ?>" maxlength="15" pattern="[0-9+(). -]{8,15}" required>
            </div>

            <div class="mb-4">
              <label for="almt_lngkp" class="form-label">Alamat Lengkap</label>
              <textarea id="almt_lngkp" name="almt_lngkp" class="form-control" rows="3" maxlength="300" required><?= $e($alamat) ?></textarea>
            </div>

            <h2 class="h4 text-success mb-3">Foto Identitas</h2>

            <div class="row">
              <div class="col-md-6 mb-4">
                <label for="ft_ktp" class="form-label">Foto KTP</label>
                <?php if ($fotoKtp): ?>
                  <div class="mb-2">
                    <img src="<?= $e(site_url('penyewa/foto/ktp/' . rawurlencode($profil['user_plg'] ?? ''))) ?>" alt="Foto KTP" class="img-thumbnail d-block" style="max-width:100%;max-height:200px"
                      onerror="this.outerHTML='<div class=&quot;alert alert-warning small&quot;>Foto KTP tersimpan tetapi gagal dimuat.</div>'">
                  </div>
                <?php else: ?>
                  <p class="small text-muted mb-2">Belum ada foto KTP.</p>
                <?php endif; ?>
                <input type="file" id="ft_ktp" name="ft_ktp" class="form-control" accept=".jpg,.jpeg,.jfif,.png,.webp">
                <div class="form-text">JPG, PNG, atau WEBP. Maksimal 5 MB.</div>
              </div>

              <div class="col-md-6 mb-4">
                <label for="ft_sim_a" class="form-label">Foto SIM A</label>
                <?php if ($fotoSim): ?>
                  <div class="mb-2">
                    <img src="<?= $e(site_url('penyewa/foto/sim/' . rawurlencode($profil['user_plg'] ?? ''))) ?>" alt="Foto SIM A" class="img-thumbnail d-block" style="max-width:100%;max-height:200px"
                      onerror="this.outerHTML='<div class=&quot;alert alert-warning small&quot;>Foto SIM A tersimpan tetapi gagal dimuat.</div>'">
                  </div>
                <?php else: ?>
                  <p class="small text-muted mb-2">Belum ada foto SIM A.</p>
                <?php endif; ?>
                <input type="file" id="ft_sim_a" name="ft_sim_a" class="form-control" accept=".jpg,.jpeg,.jfif,.png,.webp">
                <div class="form-text">JPG, PNG, atau WEBP. Maksimal 5 MB.</div>
              </div>
            </div>

            <h2 class="h4 text-success mb-3">Ubah Password</h2>
            <div class="alert alert-info small">
              Kosongkan kolom password baru jika tidak ingin mengganti password.
            </div>

            <div class="mb-3">
              <label for="password_lama" class="form-label">Password Lama</label>
              <input type="password" id="password_lama" name="password_lama" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')">
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="password_baru" class="form-label">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" class="form-control" minlength="8" autocomplete="new-password">
              </div>
              <div class="col-md-6 mb-3">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" minlength="8" autocomplete="new-password">
              </div>
            </div>

            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-success btn-lg">Simpan Perubahan</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../katalog/footer.php'; ?>