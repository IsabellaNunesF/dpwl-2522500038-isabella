<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Card Tabel Data Sewa -->
<?php if ($aksi === 'index'): ?>
  <div class="card">
    <div class="card-body">
      <a href="<?= BASE_URL ?>admin/controller/tsewa_controller.php?aksi=tambah" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Sewa
      </a>

      <div class="table-responsive">
        <table class="table table-bordered table-hover datatable">
          <thead class="table-dark">
            <tr>
              <th>No</th>
              <th>Kode Sewa</th>
              <th>Penyewa</th>
              <th>Mobil</th>
              <th>Tgl Mulai</th>
              <th>Tgl Selesai</th>
              <th>Wkt Ambil</th>
              <th>Total</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($data_sewa)): ?>
              <?php $no = 1;
              foreach ($data_sewa as $sewa): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><strong><?= htmlspecialchars($sewa['kode_sewa']) ?></strong></td>
                  <td><?= htmlspecialchars($sewa['nm_lngkp'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($sewa['nama_mobil'] ?? '-') ?></td>
                  <td><?= date('d/m/Y', strtotime($sewa['tgl_mulai'])) ?></td>
                  <td><?= date('d/m/Y', strtotime($sewa['tgl_selesai'])) ?></td>
                  <td><?= date('H:i', strtotime($sewa['wkt_ambil'])) ?></td>
                  <td>Rp <?= number_format($sewa['total_bayar'] ?? 0, 0, ',', '.') ?></td>
                  <td>
                    <?php
                    $warna_status = [
                      'diajukan'  => 'info',
                      'disetujui' => 'primary',
                      'ditolak'   => 'danger',
                      'aktif'     => 'success',
                      'selesai'   => 'secondary',
                      'batal'     => 'dark'
                    ];
                    $warna = $warna_status[$sewa['status_sewa']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $warna ?>">
                      <?= ucfirst($sewa['status_sewa']) ?>
                    </span>
                  </td>
                  <td>
                    <a href="<?= BASE_URL ?>admin/controller/tsewa_controller.php?aksi=ubah&kode_sewa=<?= urlencode($sewa['kode_sewa']) ?>"
                      class="btn btn-sm btn-warning" title="Ubah">
                      <i class="fas fa-edit"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center">Tidak ada data sewa</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Form Tambah/Ubah -->
<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><?= $aksi === 'tambah' ? 'Tambah Transaksi Sewa Baru (Walk-in)' : 'Ubah Transaksi Sewa' ?></h5>
    </div>
    <div class="card-body">
      <form method="POST" id="formSewa"
        action="<?= BASE_URL ?>admin/controller/tsewa_controller.php?aksi=<?= $aksi ?><?= $aksi === 'ubah' ? '&kode_sewa=' . urlencode($sewa_dipilih['kode_sewa']) : '' ?>">

        <?php
        if ($aksi === 'ubah'):
        ?>
          <input type="hidden" name="user_plg" value="<?= $sewa_dipilih['user_plg'] ?>">
        <?php
        endif;
        ?>

        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i>
          <strong>Catatan:</strong> Semua mobil dalam 1 transaksi sewa memiliki durasi yang sama
          (Tanggal Mulai s/d Tanggal Selesai). Jika ingin durasi berbeda, buat transaksi sewa baru.
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Penyewa *</label>
            <select name="user_plg" class="form-select select2" required <?= ($aksi === 'ubah') ? 'disabled' : '' ?>>
              <option value="">Pilih Penyewa...</option>
              <?php foreach ($data_penyewa as $penyewa): ?>
                <option value="<?= $penyewa['user_plg'] ?>"
                  <?= ($aksi === 'ubah' && $sewa_dipilih['user_plg'] === $penyewa['user_plg']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($penyewa['nm_lngkp']) ?> - <?= htmlspecialchars($penyewa['nik']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Status Sewa *</label>
            <select name="status_sewa" class="form-select" required>
              <option value="">Pilih Status...</option>
              <option value="diajukan" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'diajukan')  ? 'selected' : '' ?>>Diajukan</option>
              <option value="disetujui" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'disetujui') ? 'selected' : '' ?>>Disetujui</option>
              <option value="ditolak" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'ditolak')   ? 'selected' : '' ?>>Ditolak</option>
              <option value="aktif" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'aktif')     ? 'selected' : '' ?>>Aktif</option>
              <option value="selesai" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'selesai')   ? 'selected' : '' ?>>Selesai</option>
              <option value="batal" <?= ($aksi === 'ubah' && $sewa_dipilih['status_sewa'] === 'batal')     ? 'selected' : '' ?>>Batal</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Tanggal Mulai *</label>
            <input type="date" name="tgl_mulai" class="form-control" id="tgl_mulai"
              value="<?= $aksi === 'ubah' ? $sewa_dipilih['tgl_mulai'] : date('Y-m-d') ?>" required>
            <small class="text-danger" id="err_tgl" style="display:none;">Tanggal mulai harus sebelum tanggal selesai!</small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Tanggal Selesai *</label>
            <input type="date" name="tgl_selesai" class="form-control" id="tgl_selesai"
              value="<?= $aksi === 'ubah' ? $sewa_dipilih['tgl_selesai'] : date('Y-m-d', strtotime('+1 day')) ?>" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Waktu Ambil *</label>
            <input type="time" name="wkt_ambil" class="form-control"
              value="<?= $aksi === 'ubah' ? $sewa_dipilih['wkt_ambil'] : '08:00' ?>" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <div class="border rounded p-2 bg-light">
              <strong>Durasi Sewa:</strong>
              <span id="infoDurasi" class="text-primary ms-2">0 hari</span>
            </div>
          </div>
        </div>

        <hr>
        <h5>Mobil yang Disewa</h5>

        <div id="containerMobil">
          <?php if ($aksi === 'ubah' && !empty($detail_pilih)): ?>
            <?php foreach ($detail_pilih as $index => $detail): ?>
              <div class="row mobil-row mb-3 p-3 border rounded bg-white" data-index="<?= $index ?>">
                <div class="col-md-1 d-flex align-items-center">
                  <span class="badge bg-secondary">Mobil #<?= $index + 1 ?></span>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label">Pilih Mobil *</label>
                  <select name="no_plat[]" class="form-select select-mobil select2" required>
                    <option value="">Pilih Mobil...</option>
                    <?php foreach ($data_mobil as $mobil): ?>
                      <option value="<?= $mobil['no_plat'] ?>"
                        data-harga="<?= $mobil['hrg_hari'] ?>"
                        <?= $detail['no_plat'] === $mobil['no_plat'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mobil['nm_mobil']) ?> - <?= htmlspecialchars($mobil['no_plat']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2 mb-2">
                  <label class="form-label">Harga/Hari (Rp)</label>
                  <input type="number" name="hrg_sewa[]" class="form-control input-harga"
                    value="<?= $detail['hrg_sewa'] ?>" min="0" readonly>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label">Total Harga (X hari)</label>
                  <input type="text" class="form-control input-subtotal"
                    value="Rp <?= number_format($detail['hrg_sewa'] * $detail['n_hari'], 0, ',', '.') ?>" readonly>
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                  <button type="button" class="btn btn-danger btn-sm btn-hapus-mobil" title="Hapus">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="row mobil-row mb-3 p-3 border rounded bg-white" data-index="0">
              <div class="col-md-1 d-flex align-items-center">
                <span class="badge bg-secondary">Mobil #1</span>
              </div>
              <div class="col-md-4 mb-2">
                <label class="form-label">Pilih Mobil *</label>
                <select name="no_plat[]" class="form-select select-mobil select2" required>
                  <option value="">Pilih Mobil...</option>
                  <?php foreach ($data_mobil as $mobil): ?>
                    <option value="<?= $mobil['no_plat'] ?>"
                      data-harga="<?= $mobil['hrg_hari'] ?>">
                      <?= htmlspecialchars($mobil['nm_mobil']) ?> - <?= htmlspecialchars($mobil['no_plat']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2 mb-2">
                <label class="form-label">Harga/Hari (Rp)</label>
                <input type="number" name="hrg_sewa[]" class="form-control input-harga"
                  value="0" min="0" readonly>
              </div>
              <div class="col-md-3 mb-2">
                <label class="form-label">Total Harga (X hari)</label>
                <input type="text" class="form-control input-subtotal" value="Rp 0" readonly>
              </div>
              <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm btn-hapus-mobil" title="Hapus">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <button type="button" class="btn btn-success btn-sm mb-3" id="btnTambahMobil">
          <i class="fas fa-plus"></i> Tambah Mobil Lain
        </button>
        <span id="infoBatasMobil" class="text-muted ms-2"></span>

        <div class="mb-3">
          <h5>Grand Total: <span id="totalBayar" class="text-primary">Rp 0</span></h5>
        </div>

        <button type="submit" class="btn btn-primary" id="btnSubmit">
          <i class="fas fa-save"></i> Simpan
        </button>
        <a href="<?= BASE_URL ?>admin/controller/tsewa_controller.php?aksi=index" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </form>
    </div>
  </div>

  <script>
    // ==================== KONFIGURASI ====================
    const TOTAL_MOBIL_TERSEDIA = <?= $total_mobil_tersedia ?>;
    const DATA_MOBIL = <?= json_encode($data_mobil) ?>;

    // ==================== HITUNG DURASI OTOMATIS ====================
    function hitungDurasi() {
      const tglMulai = document.getElementById('tgl_mulai').value;
      const tglSelesai = document.getElementById('tgl_selesai').value;
      const errTgl = document.getElementById('err_tgl');
      const infoDurasi = document.getElementById('infoDurasi');

      if (tglMulai && tglSelesai) {
        const mulai = new Date(tglMulai);
        const selesai = new Date(tglSelesai);
        const diffTime = selesai - mulai;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays <= 0) {
          errTgl.style.display = 'block';
          document.getElementById('tgl_mulai').classList.add('is-invalid');
          document.getElementById('tgl_selesai').classList.add('is-invalid');
          infoDurasi.textContent = '0 hari';
          hitungTotal();
          return 0;
        } else {
          errTgl.style.display = 'none';
          document.getElementById('tgl_mulai').classList.remove('is-invalid');
          document.getElementById('tgl_selesai').classList.remove('is-invalid');
        }

        infoDurasi.textContent = diffDays + ' hari';
        hitungTotal();
        return diffDays;
      }
      infoDurasi.textContent = '0 hari';
      hitungTotal();
      return 0;
    }

    // ==================== HITUNG TOTAL (Grand Total) ====================
    function hitungTotal() {
      const infoDurasi = document.getElementById('infoDurasi').textContent;
      const match = infoDurasi.match(/(\d+)/);
      const durasi = match ? parseInt(match[1]) : 0;

      let grandTotal = 0;

      document.querySelectorAll('.mobil-row').forEach(row => {
        const harga = parseInt(row.querySelector('.input-harga').value) || 0;
        const subtotal = harga * durasi;

        // Update label kolom "Total Harga (X hari)"
        const labelSubtotal = row.querySelector('.input-subtotal');
        if (labelSubtotal) {
          labelSubtotal.value = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        grandTotal += subtotal;
      });

      document.getElementById('totalBayar').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    // ==================== AUTO-FILL HARGA SAAT PILIH MOBIL ====================
    function handleSelectMobil(selectEl) {
      const $select = $(selectEl);
      const selectedOption = $select.find(':selected');
      const harga = selectedOption.data('harga') || 0;
      const row = selectEl.closest('.mobil-row');
      row.querySelector('.input-harga').value = harga;
      hitungTotal();
      updateOpsiMobil();
    }

    // ==================== UPDATE OPSI MOBIL ====================
    function updateOpsiMobil() {
      const selectedPlats = [];
      document.querySelectorAll('.select-mobil').forEach(sel => {
        // Ambil value dari Select2 (bukan dari select asli)
        const $sel = $(sel);
        const val = $sel.val();
        if (val) selectedPlats.push(val);
      });

      document.querySelectorAll('.select-mobil').forEach(sel => {
        const $sel = $(sel);
        const currentValue = $sel.val();
        const selectEl = sel; // elemen asli

        selectEl.querySelectorAll('option').forEach(opt => {
          const val = opt.value;
          if (val === '') return;

          if (selectedPlats.includes(val) && val !== currentValue) {
            opt.disabled = true;
            opt.textContent = opt.textContent.replace(' (sudah dipilih)', '') + ' (sudah dipilih)';
          } else {
            opt.disabled = false;
            opt.textContent = opt.textContent.replace(' (sudah dipilih)', '');
          }
        });

        // ✅ PENTING: Trigger Select2 untuk refresh UI
        $sel.trigger('change.select2');
      });

      updateTombolTambah();
    }

    // ==================== UPDATE TOMBOL TAMBAH ====================
    function updateTombolTambah() {
      const btnTambah = document.getElementById('btnTambahMobil');
      const infoBatas = document.getElementById('infoBatasMobil');
      const jumlahDipilih = document.querySelectorAll('.mobil-row').length;
      const sisa = TOTAL_MOBIL_TERSEDIA - jumlahDipilih;

      infoBatas.textContent = `(Sisa mobil tersedia: ${sisa} unit)`;

      if (sisa <= 0) {
        btnTambah.disabled = true;
        btnTambah.classList.add('disabled');
        infoBatas.textContent = '(Semua mobil sudah dipilih)';
      } else {
        btnTambah.disabled = false;
        btnTambah.classList.remove('disabled');
      }
    }

    // ==================== TAMBAH MOBIL BARU ====================
    // ==================== TAMBAH MOBIL BARU ====================
    document.getElementById('btnTambahMobil').addEventListener('click', function() {
      const container = document.getElementById('containerMobil');
      const rows = container.querySelectorAll('.mobil-row');
      const newIndex = rows.length + 1;

      const newRow = document.createElement('div');
      newRow.className = 'row mobil-row mb-3 p-3 border rounded bg-white';
      newRow.setAttribute('data-index', newIndex);

      let opsiMobil = '<option value="">Pilih Mobil...</option>';
      DATA_MOBIL.forEach(m => {
        opsiMobil += `<option value="${m.no_plat}" data-harga="${m.hrg_hari}">${m.nm_mobil} - ${m.no_plat}</option>`;
      });

      newRow.innerHTML = `
        <div class="col-md-1 d-flex align-items-center">
            <span class="badge bg-secondary">Mobil #${newIndex}</span>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">Pilih Mobil *</label>
            <select name="no_plat[]" class="form-select select2 select-mobil" 
                    data-placeholder="Pilih Mobil..." required>
                ${opsiMobil}
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Harga/Hari (Rp)</label>
            <input type="number" name="hrg_sewa[]" class="form-control input-harga" 
                   value="0" min="0" readonly>
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label">Total Harga (X hari)</label>
            <input type="text" class="form-control input-subtotal" value="Rp 0" readonly>
        </div>
        <div class="col-md-1 mb-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm btn-hapus-mobil" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
        </div>
      `;

      container.appendChild(newRow);

      // ✅ Inisialisasi Select2 untuk dropdown baru
      const $newSelect = $(newRow).find('.select-mobil');
      if (typeof initSelectMobil === 'function') {
        initSelectMobil($newSelect);
      }

      updateOpsiMobil();
      hitungTotal();
    });

    // ==================== HAPUS MOBIL ====================
    document.addEventListener('click', function(e) {
      if (e.target.closest('.btn-hapus-mobil')) {
        const rows = document.querySelectorAll('.mobil-row');
        if (rows.length > 1) {
          e.target.closest('.mobil-row').remove();
          document.querySelectorAll('.mobil-row').forEach((row, idx) => {
            row.querySelector('.badge').textContent = 'Mobil #' + (idx + 1);
          });
          updateOpsiMobil();
          hitungTotal();
        } else {
          alert('Minimal harus ada 1 mobil!');
        }
      }
    });

    // ==================== EVENT LISTENER ====================
    document.getElementById('tgl_mulai').addEventListener('change', hitungDurasi);
    document.getElementById('tgl_selesai').addEventListener('change', hitungDurasi);

    // document.addEventListener('change', function(e) {
    //   if (e.target.classList.contains('select-mobil')) {
    //     handleSelectMobil(e.target);
    //   }
    // });

    // ==================== VALIDASI SUBMIT ====================
    document.getElementById('formSewa').addEventListener('submit', function(e) {
      const tglMulai = document.getElementById('tgl_mulai').value;
      const tglSelesai = document.getElementById('tgl_selesai').value;

      if (tglMulai >= tglSelesai) {
        e.preventDefault();
        alert('Tanggal mulai harus sebelum tanggal selesai!');
        document.getElementById('tgl_mulai').focus();
        return false;
      }

      const selectedPlats = [];
      let valid = true;
      document.querySelectorAll('.select-mobil').forEach(sel => {
        if (!sel.value) valid = false;
        else selectedPlats.push(sel.value);
      });

      if (!valid) {
        e.preventDefault();
        alert('Pilih mobil untuk semua baris!');
        return false;
      }

      if (selectedPlats.length !== new Set(selectedPlats).size) {
        e.preventDefault();
        alert('Mobil tidak boleh dipilih lebih dari 1 kali!');
        return false;
      }
    });

    // ==================== INIT ====================
    hitungDurasi();
    updateOpsiMobil();
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>