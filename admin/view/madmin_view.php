<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Tombol Tambah -->
<div class="card">
  <div class="card-body">
    <a href="<?= BASE_URL ?>admin/controller/madmin_controller.php?aksi=tambah" class="btn btn-primary mb-3">
      <i class="fas fa-plus"></i> Tambah Admin
    </a>

    <!-- Tabel Data Admin -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Status Akun</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_admin)): ?>
            <?php $no = 1;
            foreach ($data_admin as $admin): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($admin['username']) ?></td>
                <td>
                  <span class="badge bg-<?= $admin['status_akun'] === 'aktif' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($admin['status_akun']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>admin/controller/madmin_controller.php?aksi=ubah&username=<?= urlencode($admin['username']) ?>"
                    class="btn btn-sm btn-warning" title="Ubah">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= BASE_URL ?>admin/controller/madmin_controller.php?aksi=hapus&username=<?= urlencode($admin['username']) ?>"
                    class="btn btn-sm btn-danger"
                    onclick="return confirm('Yakin ingin menghapus admin ini?')" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">Tidak ada data admin</td>
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
      <h5 class="mb-0"><?= $aksi === 'tambah' ? 'Tambah Admin Baru' : 'Ubah Data Admin' ?></h5>
    </div>
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>admin/controller/madmin_controller.php?aksi=<?= $aksi ?><?= $aksi === 'ubah' ? '&username=' . urlencode($admin_dipilih['username']) : '' ?>">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control"
            value="<?= $aksi === 'ubah' ? htmlspecialchars($admin_dipilih['username']) : '' ?>"
            maxlength="15" required <?= $aksi === 'ubah' ? ' readonly ' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <?= $aksi === 'ubah' ? '(Kosongkan jika tidak ingin mengubah)' : '' ?></label>
          <input type="password" name="password" class="form-control"
            <?= $aksi === 'tambah' ? 'required' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="form-label">Status Akun</label>
          <select name="status_akun" class="form-select" required>
            <option value="aktif" <?= ($aksi === 'ubah' && $admin_dipilih['status_akun'] === 'aktif') ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= ($aksi === 'ubah' && $admin_dipilih['status_akun'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan
        </button>
        <a href="<?= BASE_URL ?>admin/controller/madmin_controller.php?aksi=index" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>