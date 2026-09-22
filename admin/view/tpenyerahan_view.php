<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Card Tabel Penyerahan Mobil -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Daftar Penyerahan Mobil</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Kode Sewa</th>
            <th>Penyewa</th>
            <th>No. HP</th>
            <th>Foto KTP</th>
            <th>Foto SIM A</th>
            <th>Tgl Mulai</th>
            <th>Tgl Selesai</th>
            <th>Wkt Ambil</th>
            <th>Daftar Mobil Dipesan</th>
            <th>Status</th>
            <th>Status Bayar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_sewa)): ?>
            <?php $no = 1;
            foreach ($data_sewa as $sewa):
              $detail = $data_detail[$sewa['kode_sewa']] ?? null;
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($sewa['kode_sewa']) ?></strong></td>
                <td><?= htmlspecialchars($detail['nm_lngkp'] ?? '-') ?></td>
                <td><?= htmlspecialchars($detail['no_hp'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($detail['ft_ktp'])): ?>
                    <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($detail['ft_ktp']) ?>" target="_blank">
                      <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($detail['ft_ktp']) ?>" width="60" class="rounded" alt="Foto KTP">
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($detail['ft_sim_a'])): ?>
                    <a href="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($detail['ft_sim_a']) ?>" target="_blank">
                      <img src="<?= BASE_URL ?>uploads/penyewa/<?= htmlspecialchars($detail['ft_sim_a']) ?>" width="60" class="rounded" alt="Foto SIM A">
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($sewa['tgl_mulai'])) ?></td>
                <td><?= date('d/m/Y', strtotime($sewa['tgl_selesai'])) ?></td>
                <td><?= date('H:i', strtotime($sewa['wkt_ambil'])) ?></td>
                <td><?= htmlspecialchars($detail['nama_mobil'] ?? '-') ?></td>
                <td>
                  <?php if ($sewa['status_sewa'] === 'disetujui'): ?>
                    <span class="badge bg-warning text-dark">Menunggu Diserahkan</span>
                  <?php elseif ($sewa['status_sewa'] === 'aktif'): ?>
                    <span class="badge bg-success">Sedang Berjalan (Aktif)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($detail['status_bayar'] === 'LUNAS'): ?>
                    <span class="badge bg-success">Lunas</span>
                  <?php else: ?>
                    <span class="badge bg-info text-dark">Baru DP</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($sewa['status_sewa'] === 'disetujui'): ?>
                    <!-- tpenyerahan_controller.php?aksi=proses -->
                    <a href="<?= BASE_URL ?>admin/controller/tpenyerahan_controller.php?aksi=form&kode_sewa=<?= urlencode($sewa['kode_sewa']) ?>"
                      class="btn btn-sm btn-primary"
                      onclick="return confirm('Yakin akan menyerahkan mobil untuk kode sewa <?= $sewa['kode_sewa'] ?>?\n\nStatus sewa akan berubah menjadi AKTIF dan unit mobil berstatus DISEWA.')">
                      <i class="fas fa-key"></i> Serahkan Mobil
                    </a>
                  <?php else: ?>
                    <span class="text-muted fst-italic">Telah Diserahkan</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center">Tidak ada data sewa yang menunggu penyerahan atau sedang aktif.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Form Penyerahan Mobil -->
<?php if ($aksi === 'form' && !empty($data_sewa_form)): ?>
  <div class="modal fade" id="modalPenyerahan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>admin/controller/tpenyerahan_controller.php?aksi=proses">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-key"></i> Penyerahan Mobil
            </h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="kode_sewa" value="<?= htmlspecialchars($data_sewa_form['kode_sewa']) ?>">

            <div class="alert alert-info">
              <strong>Kode Sewa:</strong> <?= htmlspecialchars($data_sewa_form['kode_sewa']) ?><br>
              <strong>Tanggal Mulai:</strong> <?= date('d/m/Y', strtotime($data_sewa_form['tgl_mulai'])) ?><br>
              <strong>Tanggal Selesai:</strong> <?= date('d/m/Y', strtotime($data_sewa_form['tgl_selesai'])) ?><br>
              <strong>Waktu Ambil Terjadwal:</strong> <?= date('H:i', strtotime($data_sewa_form['wkt_ambil'])) ?>
            </div>

            <div class="mb-3">
              <label class="form-label">
                <strong>Waktu Ambil Aktual</strong> <span class="text-danger">*</span>
              </label>
              <input type="time" name="wkt_ambil_aktual" class="form-control"
                value="<?= date('H:i') ?>" required>
              <small class="text-muted">
                Catat waktu mobil benar-benar diserahkan ke penyewa.
                Tanggal mulai dan durasi sewa tetap sesuai perjanjian awal.
              </small>
            </div>

            <div class="alert alert-warning">
              <i class="fas fa-info-circle"></i>
              <strong>Perhatian:</strong><br>
              • Tanggal mulai dan selesai <strong>TIDAK BERUBAH</strong><br>
              • Durasi sewa <strong>TETAP</strong> sesuai perjanjian<br>
              • Status sewa akan berubah menjadi <strong>AKTIF</strong>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-primary"
              onclick="return confirm('Yakin akan menyerahkan mobil?\n\nWaktu ambil akan dicatat dan status sewa berubah menjadi AKTIF.')">
              <i class="fas fa-check"></i> Serahkan Mobil
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var modal = new bootstrap.Modal(document.getElementById('modalPenyerahan'));
      modal.show();
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>