<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Card Tabel Data Mobil -->
<div class="card">
  <div class="card-body">
    <a href="<?= BASE_URL ?>admin/controller/mmobil_controller.php?aksi=tambah" class="btn btn-primary mb-3">
      <i class="fas fa-plus"></i> Tambah Mobil
    </a>

    <!-- Tabel Data Mobil -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover datatable">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>No Plat</th>
            <th>Nama Mobil</th>
            <th>Jenis</th>
            <th>Transmisi</th>
            <th>Harga/Hari</th>
            <th>Status</th>
            <th>Foto</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_mobil)): ?>
            <?php $no = 1;
            foreach ($data_mobil as $mobil): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($mobil['no_plat']) ?></td>
                <td><?= htmlspecialchars($mobil['nm_mobil']) ?></td>
                <td><?= htmlspecialchars($mobil['jns_mobil']) ?></td>
                <td><?= htmlspecialchars($mobil['transmisi']) ?></td>
                <td>Rp <?= number_format($mobil['hrg_hari'], 0, ',', '.') ?></td>
                <td>
                  <span class="badge bg-<?= $mobil['status_unit'] === 'tersedia' ? 'success' : ($mobil['status_unit'] === 'disewa' ? 'warning' : 'danger') ?>">
                    <?= ucfirst($mobil['status_unit']) ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($mobil['ft_depan'])): ?>
                    <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($mobil['ft_depan']) ?>"
                      width="60" class="rounded" alt="Foto Mobil">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>admin/controller/mmobil_controller.php?aksi=ubah&no_plat=<?= urlencode($mobil['no_plat']) ?>"
                    class="btn btn-sm btn-warning" title="Ubah">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= BASE_URL ?>admin/controller/mmobil_controller.php?aksi=hapus&no_plat=<?= urlencode($mobil['no_plat']) ?>"
                    class="btn btn-sm btn-danger"
                    onclick="return confirm('Yakin ingin menghapus mobil ini?')" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">Tidak ada data mobil</td>
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
      <h5 class="mb-0"><?= $aksi === 'tambah' ? 'Tambah Mobil Baru' : 'Ubah Data Mobil' ?></h5>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data"
        action="<?= BASE_URL ?>admin/controller/mmobil_controller.php?aksi=<?= $aksi ?><?= $aksi === 'ubah' ? '&no_plat=' . urlencode($mobil_dipilih['no_plat']) : '' ?>">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">No Plat *</label>
            <input type="text" name="no_plat" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['no_plat']) : '' ?>"
              maxlength="15" required <?= $aksi === 'ubah' ? 'readonly' : '' ?>>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Nama Mobil *</label>
            <input type="text" name="nm_mobil" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['nm_mobil']) : '' ?>"
              maxlength="40" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Jumlah Kursi *</label>
            <input type="number" name="nkursi" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['nkursi']) : '' ?>"
              min="1" required>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Jumlah Bagasi *</label>
            <input type="number" name="nbagasi" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['nbagasi']) : '' ?>"
              min="0" required>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Jenis Mobil *</label>
            <select name="jns_mobil" class="form-select" required>
              <option value="">Pilih...</option>
              <option value="SUV" <?= ($aksi === 'ubah' && $mobil_dipilih['jns_mobil'] === 'SUV') ? 'selected' : '' ?>>SUV</option>
              <option value="MPV" <?= ($aksi === 'ubah' && $mobil_dipilih['jns_mobil'] === 'MPV') ? 'selected' : '' ?>>MPV</option>
              <option value="City Car" <?= ($aksi === 'ubah' && $mobil_dipilih['jns_mobil'] === 'City Car') ? 'selected' : '' ?>>City Car</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Transmisi *</label>
            <select name="transmisi" class="form-select" required>
              <option value="">Pilih...</option>
              <option value="AT" <?= ($aksi === 'ubah' && $mobil_dipilih['transmisi'] === 'AT') ? 'selected' : '' ?>>Automatic (AT)</option>
              <option value="MT" <?= ($aksi === 'ubah' && $mobil_dipilih['transmisi'] === 'MT') ? 'selected' : '' ?>>Manual (MT)</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Tahun Buat *</label>
            <input type="number" name="thn_buat" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['thn_buat']) : date('Y') ?>"
              min="1900" max="<?= date('Y') ?>" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Harga/Hari (Rp) *</label>
            <input type="number" name="hrg_hari" class="form-control"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['hrg_hari']) : '' ?>"
              min="0" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Status Unit *</label>
            <select name="status_unit" class="form-select" required>
              <option value="tersedia" <?= ($aksi === 'ubah' && $mobil_dipilih['status_unit'] === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
              <option value="disewa" <?= ($aksi === 'ubah' && $mobil_dipilih['status_unit'] === 'disewa') ? 'selected' : '' ?>>Disewa</option>
              <option value="tidaktersedia" <?= ($aksi === 'ubah' && $mobil_dipilih['status_unit'] === 'tidaktersedia') ? 'selected' : '' ?>>Tidak Tersedia</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Catatan Unit</label>
          <textarea name="cttn_unit" class="form-control" rows="2"
            maxlength="500"><?= $aksi === 'ubah' ? htmlspecialchars($mobil_dipilih['cttn_unit']) : '' ?></textarea>
        </div>

        <hr>
        <h5>Foto Mobil</h5>

        <div class="row">
          <!-- Foto Depan -->
          <div class="col-md-4 mb-3">
            <label class="form-label">Foto Depan</label>
            <input type="file" name="ft_depan" class="form-control" accept="image/*">
            <?php if ($aksi === 'ubah' && !empty($mobil_dipilih['ft_depan'])): ?>
              <div class="mt-2">
                <small class="text-muted">Foto saat ini:</small><br>
                <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($mobil_dipilih['ft_depan']) ?>"
                  width="100" class="rounded mt-1" alt="Foto Depan">
              </div>
            <?php endif; ?>
          </div>

          <!-- Foto Belakang -->
          <div class="col-md-4 mb-3">
            <label class="form-label">Foto Belakang</label>
            <input type="file" name="ft_blkg" class="form-control" accept="image/*">
            <?php if ($aksi === 'ubah' && !empty($mobil_dipilih['ft_blkg'])): ?>
              <div class="mt-2">
                <small class="text-muted">Foto saat ini:</small><br>
                <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($mobil_dipilih['ft_blkg']) ?>"
                  width="100" class="rounded mt-1" alt="Foto Belakang">
              </div>
            <?php endif; ?>
          </div>

          <!-- Foto Interior -->
          <div class="col-md-4 mb-3">
            <label class="form-label">Foto Interior</label>
            <input type="file" name="ft_interior" class="form-control" accept="image/*">
            <?php if ($aksi === 'ubah' && !empty($mobil_dipilih['ft_interior'])): ?>
              <div class="mt-2">
                <small class="text-muted">Foto saat ini:</small><br>
                <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($mobil_dipilih['ft_interior']) ?>"
                  width="100" class="rounded mt-1" alt="Foto Interior">
              </div>
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan
        </button>
        <a href="<?= BASE_URL ?>admin/controller/mmobil_controller.php?aksi=index" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>