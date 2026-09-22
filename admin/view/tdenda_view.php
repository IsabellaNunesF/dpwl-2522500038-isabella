<?php require_once __DIR__ . '/header.php'; ?>

<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Daftar Denda Keterlambatan</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover datatable">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Kode Kembali</th>
            <th>Kode Sewa</th>
            <th>Penyewa</th>
            <th>No. HP</th>
            <th>Tgl Selesai Sewa</th>
            <th>Tgl/Waktu Balik Aktual</th>
            <th>Status Denda</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_kembali)): ?>
            <?php $no = 1;
            foreach ($data_kembali as $k):
              $detail = $data_detail[$k['kode_kembali']] ?? null;
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($k['kode_kembali']) ?></strong></td>
                <td><?= htmlspecialchars($detail['kode_sewa'] ?? '-') ?></td>
                <td><?= htmlspecialchars($detail['nm_lngkp'] ?? '-') ?></td>
                <td><?= htmlspecialchars($detail['no_hp'] ?? '-') ?></td>
                <td><?= !empty($detail['tgl_selesai']) ? date('d/m/Y', strtotime($detail['tgl_selesai'])) . " " .   date('H:i', strtotime($detail['wkt_ambil'])) : '-' ?></td>
                <td><?= date('d/m/Y H:i', strtotime($k['tgl_wkt_blk'])) ?></td>
                <td>
                  <?php if ($detail['sudah_denda']): ?>
                    <span class="badge bg-success">Sudah Didenda</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Belum Didenda</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!$detail['sudah_denda']): ?>
                    <a href="<?= BASE_URL ?>admin/controller/tdenda_controller.php?aksi=form&kode_kembali=<?= urlencode($k['kode_kembali']) ?>"
                      class="btn btn-sm btn-danger">
                      <i class="fas fa-gavel"></i> Input Denda
                    </a>
                  <?php else: ?>
                    <span class="text-muted fst-italic">Selesai</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Tidak ada pengembalian terlambat yang perlu didenda.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Form Input Denda -->
<?php if ($aksi === 'form' && !empty($data_kembali_form)): ?>
  <div class="modal fade" id="modalDenda" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>admin/controller/tdenda_controller.php?aksi=proses">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="fas fa-gavel"></i> Input Denda Keterlambatan</h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="kode_kembali" value="<?= htmlspecialchars($data_kembali_form['kode_kembali']) ?>">

            <div class="alert alert-light border">
              <strong>Kode Kembali:</strong> <?= htmlspecialchars($data_kembali_form['kode_kembali']) ?><br>
              <strong>Kode Sewa:</strong> <?= htmlspecialchars($data_sewa_form['kode_sewa'] ?? '-') ?><br>
              <strong>Batas Selesai:</strong> <?= !empty($data_sewa_form['tgl_selesai']) ? date('d/m/Y', strtotime($data_sewa_form['tgl_selesai'])) . " " . date('H:i', strtotime($data_sewa_form['wkt_ambil'])) : '-' ?><br>
              <strong>Balik Aktual:</strong> <?= date('d/m/Y H:i', strtotime($data_kembali_form['tgl_wkt_blk'])) ?>
            </div>

            <div class="mb-3">
              <label class="form-label"><strong>Total Jam Terlambat</strong> <span class="text-danger">*</span></label>
              <input type="number" name="telat_jam" class="form-control" min="1" value="1" required>
              <small class="text-muted">Kebijakan denda bisa disesuaikan oleh admin.</small>
            </div>

            <div class="mb-3">
              <label class="form-label"><strong>Tarif Per Jam (Rp)</strong> <span class="text-danger">*</span></label>
              <input type="number" name="tarif_per_jam" class="form-control" value="50000" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin akan mencatat denda ini?')">
              <i class="fas fa-save"></i> Simpan Denda
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var modal = new bootstrap.Modal(document.getElementById('modalDenda'));
      modal.show();
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>