<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Card Tabel Data Penyewa -->
<div class="card">
  <div class="card-body">
    <a href="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php?aksi=tambah" class="btn btn-primary mb-3">
      <i class="fas fa-plus"></i> Tambah Penyewa
    </a>

    <!-- Tabel Data Penyewa -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover datatable">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>User</th>
            <th>NIK</th>
            <th>Nama Lengkap</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Foto KTP</th>
            <th>Foto SIM A</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_penyewa)): ?>
            <?php $no = 1;
            foreach ($data_penyewa as $penyewa): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($penyewa['user_plg']) ?></td>
                <td><?= htmlspecialchars($penyewa['nik']) ?></td>
                <td><?= htmlspecialchars($penyewa['nm_lngkp']) ?></td>
                <td><?= htmlspecialchars($penyewa['no_hp']) ?></td>
                <td><?= htmlspecialchars(substr($penyewa['almt_lngkp'], 0, 50)) ?><?= strlen($penyewa['almt_lngkp']) > 50 ? '...' : '' ?></td>
                <td>
                  <?php if (!empty($penyewa['ft_ktp'])): ?>
                    <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa['ft_ktp']) ?>" target="_blank">
                      <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa['ft_ktp']) ?>" width="60" class="rounded" alt="Foto KTP">
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($penyewa['ft_sim_a'])): ?>
                    <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa['ft_sim_a']) ?>" target="_blank">
                      <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa['ft_sim_a']) ?>" width="60" class="rounded" alt="Foto SIM A">
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php?aksi=ubah&user_plg=<?= urlencode($penyewa['user_plg']) ?>"
                    class="btn btn-sm btn-warning" title="Ubah">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php?aksi=hapus&user_plg=<?= urlencode($penyewa['user_plg']) ?>"
                    class="btn btn-sm btn-danger"
                    onclick="return confirm('Yakin ingin menghapus penyewa ini?')" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">Tidak ada data penyewa</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Form Tambah/Ubah -->
<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><?= $aksi === 'tambah' ? 'Tambah Penyewa Baru' : 'Ubah Data Penyewa' ?></h5>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data"
        action="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php?aksi=<?= $aksi ?><?= $aksi === 'ubah' ? '&user_plg=' . urlencode($penyewa_dipilih['user_plg']) : '' ?>">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Username *</label>
            <input type="text" name="user_plg" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($penyewa_dipilih['user_plg']) : '' ?>"
              maxlength="15" required <?= $aksi === 'ubah' ? 'readonly' : '' ?>>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Password (sandi) *</label>
            <input type="password" name="sandi" class="form-control"
              placeholder="<?= $aksi === 'ubah' ? 'Kosongkan jika tidak diubah' : 'Masukkan password' ?>"
              maxlength="60" <?= $aksi === 'tambah' ? 'required' : '' ?>>
            <?php if ($aksi === 'ubah'): ?>
              <small class="text-muted">Biarkan kosong jika password tidak ingin diubah.</small>
            <?php endif; ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">NIK *</label>
            <input type="text" name="nik" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($penyewa_dipilih['nik']) : '' ?>"
              maxlength="16" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">No HP *</label>
            <input type="text" name="no_hp" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($penyewa_dipilih['no_hp']) : '' ?>"
              maxlength="15" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nm_lngkp" class="form-control"
            value="<?= $aksi === 'ubah' ? htmlspecialchars($penyewa_dipilih['nm_lngkp']) : '' ?>"
            maxlength="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Alamat Lengkap *</label>
          <textarea name="almt_lngkp" class="form-control" rows="3"
            maxlength="300" required><?= $aksi === 'ubah' ? htmlspecialchars($penyewa_dipilih['almt_lngkp']) : '' ?></textarea>
        </div>

        <hr>
        <h5>Dokumen Identitas</h5>
        <div class="row">
          <!-- Foto KTP -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Foto KTP</label>
            <input type="file" name="ft_ktp" class="form-control" accept="image/*">
            <?php if ($aksi === 'ubah' && !empty($penyewa_dipilih['ft_ktp'])): ?>
              <div class="mt-2">
                <small class="text-muted">Foto saat ini:</small><br>
                <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa_dipilih['ft_ktp']) ?>" target="_blank">
                  <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa_dipilih['ft_ktp']) ?>"
                    width="100" class="rounded mt-1" alt="Foto KTP">
                </a>
              </div>
            <?php endif; ?>
          </div>
          <!-- Foto SIM A -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Foto SIM A</label>
            <input type="file" name="ft_sim_a" class="form-control" accept="image/*">
            <?php if ($aksi === 'ubah' && !empty($penyewa_dipilih['ft_sim_a'])): ?>
              <div class="mt-2">
                <small class="text-muted">Foto saat ini:</small><br>
                <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa_dipilih['ft_sim_a']) ?>" target="_blank">
                  <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($penyewa_dipilih['ft_sim_a']) ?>"
                    width="100" class="rounded mt-1" alt="Foto SIM A">
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan
        </button>
        <a href="<?= BASE_URL ?>admin/controller/mpenyewa_controller.php?aksi=index" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>