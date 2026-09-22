<?php require_once __DIR__ . '/header.php'; ?>

<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="alert alert-info">
  <i class="fas fa-info-circle"></i>
  <strong>Informasi:</strong> Admin dapat menginput pengembalian mobil jika pembayaran atas transaksi sewa telah lunas.
</div>

<!-- ✅ TABEL DITAMPILKAN LANGSUNG, TANPA KONDISIONAL -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Daftar Mobil Aktif untuk Dikembalikan</h5>
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
            <th>Tgl Mulai</th>
            <th>Tgl Selesai</th>
            <th>Wkt Ambil</th>
            <th>Daftar Mobil</th>
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
                <td><?= date('d/m/Y', strtotime($sewa['tgl_mulai'])) ?></td>
                <td><?= date('d/m/Y', strtotime($sewa['tgl_selesai'])) ?></td>
                <td><?= date('H:i', strtotime($sewa['wkt_ambil'])) ?></td>
                <td><?= htmlspecialchars($detail['nama_mobil'] ?? '-') ?></td>
                <td>
                  <?php if ($detail['status_bayar'] === 'LUNAS'): ?>
                    <span class="badge bg-success">Lunas</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Belum Lunas</span>
                    <br><small class="text-danger">Sisa: Rp <?= number_format($detail['sisa_tagihan'], 0, ',', '.') ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($detail['status_bayar'] === 'LUNAS'): ?>
                    <a href="<?= BASE_URL ?>admin/controller/tpengembalian_controller.php?aksi=form&kode_sewa=<?= urlencode($sewa['kode_sewa']) ?>"
                      class="btn btn-sm btn-primary">
                      <i class="fas fa-undo"></i> Input Pengembalian
                    </a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary" disabled>
                      <i class="fas fa-lock"></i> Input Pengembalian
                    </button>
                    <br><small class="text-muted fst-italic">* Lunasi tagihan terlebih dahulu</small>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">Tidak ada mobil yang sedang disewa (aktif).</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Form Pengembalian Mobil -->
<?php if ($aksi === 'form' && !empty($data_sewa_form)): ?>
  <div class="modal fade" id="modalPengembalian" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>admin/controller/tpengembalian_controller.php?aksi=proses">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-undo"></i> Form Pengembalian Mobil</h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="kode_sewa" value="<?= htmlspecialchars($data_sewa_form['kode_sewa']) ?>">
            <input type="hidden" name="kode_bayar" value="<?= htmlspecialchars($detail_form['kode_bayar']) ?>">

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label"><strong>Kode Sewa</strong></label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data_sewa_form['kode_sewa']) ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label"><strong>Tanggal & Waktu Balik Aktual</strong> <span class="text-danger">*</span></label>
                <input type="datetime-local" name="tgl_wkt_blk" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label"><strong>Kondisi Pengembalian</strong> <span class="text-danger">*</span></label>
              <select name="stts_blk" class="form-select" required>
                <option value="normal">Normal (Tidak ada masalah)</option>
                <option value="terlambat">Terlambat (Akan diarahkan ke input denda)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label"><strong>Keterangan / Catatan Admin</strong></label>
              <textarea name="ket_blk" class="form-control" rows="3" placeholder="Contoh: Mobil dikembalikan dalam keadaan bersih, bensin full, dsb."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin akan memproses pengembalian ini?')">
              <i class="fas fa-save"></i> Proses Pengembalian
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var modal = new bootstrap.Modal(document.getElementById('modalPengembalian'));
      modal.show();
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>