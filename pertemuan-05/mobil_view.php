<?php
...
$mode = $mode ?? null;
$mobil_dipilih = $mobil_dipilih ?? null;
$isForm = in_array($mode, ['tambah', 'ubah'], true);
$isEdit = $mode === 'ubah';
$no_platDipilih = $mobil_dipilih['no_plat'] ?? '';
$formAction = $isEdit
? site_url('admin/mobil/ubah/' . rawurlencode((string) $no_platDipilih))
: site_url('admin/mobil/tambah');
?>
<!-- Content Wrapper -->
<div class="content-wrapper">
...
<section class="content">
...
<?php if ($isForm): ?>
<!-- Form Tambah/Ubah: tetap satu file dengan tabel. -->
<div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title"><?= $isEdit ? 'Ubah Data Kendaraan' : 'Tambah Kendaraan' ?></h3>
</div>
<form role="form" method="post" enctype="multipart/form-data"
action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
<div class="box-body">
<div class="form-group">
<label for="no_plat">Nomor Plat</label>
<input type="text" class="form-control" id="no_plat" name="no_plat"
maxlength="15" value="<?= htmlspecialchars($mobil_dipilih['no_plat'] ?? '',
ENT_QUOTES, 'UTF-8') ?>"
<?= $isEdit ? 'readonly' : '' ?> required>
</div>
<div class="form-group">
<label for="nm_mobil">Nama Mobil</label>
<input type="text" class="form-control" id="nm_mobil" name="nm_mobil"
maxlength="40" value="<?= htmlspecialchars($mobil_dipilih['nm_mobil'] ?? '',
ENT_QUOTES, 'UTF-8') ?>" required>
</div>
<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="nkursi">Jumlah Kursi</label>
<input type="number" class="form-control" id="nkursi" name="nkursi"
min="1" max="255" value="<?= htmlspecialchars($mobil_dipilih['nkursi'] ?? '',
ENT_QUOTES, 'UTF-8') ?>" required>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="nbagasi">Jumlah Bagasi</label>
<input type="number" class="form-control" id="nbagasi" name="nbagasi"
min="0" max="255" value="<?= htmlspecialchars($mobil_dipilih['nbagasi'] ?? '',
ENT_QUOTES, 'UTF-8') ?>" required>
</div>
</div>
</div>
<div class="form-group">
<label for="jns_mobil">Jenis Mobil</label>
<select class="form-control" id="jns_mobil" name="jns_mobil" required>
<option value="">Pilih jenis mobil</option>
<?php foreach (['SUV', 'MPV', 'City Car'] as $jenis): ?>
<option value="<?= htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8') ?>" <?=
(($mobil_dipilih['jns_mobil'] ?? '') === $jenis) ? 'selected' : '' ?>><?= htmlspecialchars($jenis,
ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label for="transmisi">Transmisi</label>
<select class="form-control" id="transmisi" name="transmisi" required>
<option value="">Pilih transmisi</option>
<?php foreach (['AT' => 'Automatic (AT)', 'MT' => 'Manual (MT)'] as $kode =>
$label): ?>
<option value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>" <?=
(($mobil_dipilih['transmisi'] ?? '') === $kode) ? 'selected' : '' ?>><?= htmlspecialchars($label,
ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label for="thn_buat">Tahun Pembuatan</label>
<input type="number" class="form-control" id="thn_buat" name="thn_buat"
min="1901" max="<?= date('Y') ?>" value="<?=
htmlspecialchars($mobil_dipilih['thn_buat'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
</div>
<div class="form-group">
<label for="hrg_hari">Harga Sewa per Hari (Rp)</label>
<input type="number" class="form-control" id="hrg_hari" name="hrg_hari"
min="1" max="16777215" value="<?= htmlspecialchars($mobil_dipilih['hrg_hari'] ?? '',
ENT_QUOTES, 'UTF-8') ?>" required>
</div>
<div class="form-group">
<label for="cttn_unit">Catatan Unit</label>
<textarea class="form-control" id="cttn_unit" name="cttn_unit"
maxlength="500" rows="3" required><?= htmlspecialchars($mobil_dipilih['cttn_unit']
?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
</div>
<div class="form-group">
<label for="status_unit">Status Kendaraan</label>
<select class="form-control" id="status_unit" name="status_unit" required>
<?php foreach (['tersedia' => 'Tersedia', 'disewa' => 'Disewa', 'tidaktersedia' =>
'Tidak Tersedia'] as $kode => $label): ?>
<option value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>" <?=
(($mobil_dipilih['status_unit'] ?? 'tersedia') === $kode) ? 'selected' : '' ?>><?=
htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>
<hr>
<h4>Foto Kendaraan</h4>
<?php foreach (['ft_depan' => 'Foto Depan', 'ft_blkg' => 'Foto Belakang', 'ft_interior'
=> 'Foto Interior'] as $field => $label): ?>
<div class="form-group">
<label for="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"><?=
htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
<input type="file" class="form-control" id="<?= htmlspecialchars($field, ENT_QUOTES,
'UTF-8') ?>" name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"
accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
<p class="help-block">JPG, JPEG, PNG, atau WEBP; maksimal 5 MB per foto.</p>
<div class="photo-preview" style="margin-top: 10px;">
<?php if (!empty($mobil_dipilih[$field]) && is_string($mobil_dipilih[$field]) &&
$mobil_dipilih[$field] !== '0'): ?>
<p class="help-block current-photo-label">Foto saat ini:</p>
<img
class="img-thumbnail current-photo"
src="<?= htmlspecialchars(base_url('uploads/mobil/' .
rawurlencode($mobil_dipilih[$field])), ENT_QUOTES, 'UTF-8') ?>"
alt="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> saat ini"
style="width: 180px; height: 120px; object-fit: cover;">
<p class="help-block current-photo-name"><?=
htmlspecialchars($mobil_dipilih[$field], ENT_QUOTES, 'UTF-8') ?>. Kosongkan jika tidak ingin
menggantinya.</p>
<?php else: ?>
<p class="help-block current-photo-label">Belum ada foto tersimpan.</p>
<?php endif; ?>
<div class="new-photo-preview" style="display: none; margin-top: 8px;">
<p class="help-block">Pratinjau foto yang dipilih:</p>
<img
class="img-thumbnail new-photo-image"
alt="Pratinjau <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> yang
dipilih"
style="width: 180px; height: 120px; object-fit: cover;">
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="box-footer">
<button type="submit" class="btn btn-primary">
<i class="fa fa-save"></i> Simpan Kendaraan
</button>
<a href="<?= htmlspecialchars(site_url('admin/mobil'), ENT_QUOTES, 'UTF-8') ?>"
class="btn btn-default">
<i class="fa fa-times"></i> Batal
</a>
</div>
</form>
</div>
<?php endif; ?>
</section>
</div>
<?php
require APPPATH . '/views/templates/footer.php';
?>
<?php if ($isForm): ?>
<script>
(function() {
'use strict';
document.querySelectorAll('.form-group
input[type="file"][name^="ft_"]').forEach(function(input) {
var container = input.closest('.form-group');
var preview = container ? container.querySelector('.new-photo-preview') : null;
var image = preview ? preview.querySelector('.new-photo-image') : null;
var previousUrl = null;
if (!preview || !image) {
return;
}
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
if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size < 1 ||
file.size > 5 * 1024 * 1024) {
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