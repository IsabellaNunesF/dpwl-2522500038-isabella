<?php require_once __DIR__ . '/header.php'; ?>

<!-- Alert Pesan -->
<?php if (!empty($pesan)): ?>
  <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show">
    <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- ==================== HALAMAN INDEX ==================== -->
<?php if ($aksi === 'index'): ?>
  <div class="card">
    <div class="card-body">
      <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=tambah" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Pembayaran
      </a>

      <div class="table-responsive">
        <table class="table table-bordered table-hover datatable">
          <thead class="table-dark">
            <tr>
              <th>No</th>
              <th>Kode Bayar</th>
              <th>Kode Sewa</th>
              <th>Penyewa</th>
              <th>Tgl Bayar</th>
              <th>Metode</th>
              <th>Jenis</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Bukti</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($data_bayar)): ?>
              <?php $no = 1;
              foreach ($data_bayar as $bayar): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><strong><?= htmlspecialchars($bayar['kode_bayar']) ?></strong></td>
                  <td><?= htmlspecialchars($bayar['kode_sewa']) ?></td>
                  <td><?= htmlspecialchars($bayar['nm_lngkp'] ?? '-') ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($bayar['tgl_wkt_byr'])) ?></td>
                  <td><?= ucfirst($bayar['mtode_byr']) ?></td>
                  <td>
                    <span class="badge bg-<?= $bayar['jns_byr'] === 'dp' ? 'info' : 'success' ?>">
                      <?= strtoupper($bayar['jns_byr']) ?>
                    </span>
                  </td>
                  <td>Rp <?= number_format($bayar['nominal'], 0, ',', '.') ?></td>
                  <td>
                    <?php
                    $warna_status = [
                      'pending' => 'warning',
                      'valid'   => 'success',
                      'invalid' => 'danger'
                    ];
                    $warna = $warna_status[$bayar['sttus_byr']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $warna ?>">
                      <?= ucfirst($bayar['sttus_byr']) ?>
                    </span>
                    <?php if ($bayar['sttus_byr'] !== 'pending' && !empty($bayar['tgl_wkt_ver'])): ?>
                      <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($bayar['tgl_wkt_ver'])) ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($bayar['ft_byr'])): ?>
                      <a href="<?= BASE_URL ?>uploads/pembayaran/<?= htmlspecialchars($bayar['ft_byr']) ?>" target="_blank">
                        <img src="<?= BASE_URL ?>uploads/pembayaran/<?= htmlspecialchars($bayar['ft_byr']) ?>" width="50" class="rounded" alt="Bukti">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=ubah&kode_bayar=<?= urlencode($bayar['kode_bayar']) ?>" class="btn btn-sm btn-warning" title="Ubah">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($bayar['sttus_byr'] === 'pending'): ?>
                      <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=verifikasi&kode_bayar=<?= urlencode($bayar['kode_bayar']) ?>&status=valid" class="btn btn-sm btn-success" title="Validasi" onclick="return confirm('Validasi pembayaran ini?')">
                        <i class="fas fa-check"></i>
                      </a>
                      <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=verifikasi&kode_bayar=<?= urlencode($bayar['kode_bayar']) ?>&status=invalid" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak pembayaran ini?')">
                        <i class="fas fa-times"></i>
                      </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=hapus&kode_bayar=<?= urlencode($bayar['kode_bayar']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data pembayaran ini?')" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </a>
                    <?php
                    // Cek apakah bisa cetak untuk kode_sewa ini
                    $bisa_cetak = $model->cekBisaCetak($bayar['kode_sewa']);
                    ?>
                    <?php if ($bisa_cetak): ?>
                      <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=cetak&kode_sewa=<?= urlencode($bayar['kode_sewa']) ?>" class="btn btn-sm btn-info" title="Cetak Bukti" target="_blank">
                        <i class="fas fa-print"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="11" class="text-center">Tidak ada data pembayaran</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ==================== FORM TAMBAH/UBAH ==================== -->
<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><?= $aksi === 'tambah' ? 'Tambah Data Pembayaran' : 'Ubah Data Pembayaran' ?></h5>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=<?= $aksi ?><?= $aksi === 'ubah' ? '&kode_bayar=' . urlencode($bayar_dipilih['kode_bayar']) : '' ?>">

        <!-- 1. Kode Sewa -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Kode Sewa *</label>
            <select name="kode_sewa" class="form-select" id="kode_sewa" required>
              <option value="">Pilih Kode Sewa...</option>
              <?php foreach ($data_sewa as $sewa):
                $detail = $data_sewa_detail[$sewa['kode_sewa']] ?? null;

                // ✅ SKIP jika status sewa = batal
                if ($sewa['status_sewa'] === 'batal') {
                  // Kecuali mode ubah dan ini adalah kode sewa yang sedang diedit
                  if (!($aksi === 'ubah' && $bayar_dipilih['kode_sewa'] === $sewa['kode_sewa'])) {
                    continue;
                  }
                }

                $skip = false;
                if ($detail && $detail['is_lunas']) {
                  if ($aksi === 'tambah') {
                    $skip = true;
                  } elseif ($aksi === 'ubah' && $bayar_dipilih['kode_sewa'] !== $sewa['kode_sewa']) {
                    $skip = true;
                  }
                }
                if ($skip) continue;
              ?>
                <option value="<?= $sewa['kode_sewa'] ?>"
                  data-total="<?= $detail['total_tagihan'] ?? 0 ?>"
                  data-dp-paid="<?= $detail['total_dp_paid'] ?? 0 ?>"
                  data-sisa="<?= $detail['sisa_tagihan'] ?? 0 ?>"
                  data-bayar-valid="<?= $detail['total_bayar_valid'] ?? 0 ?>"
                  data-is-lunas="<?= $detail['is_lunas'] ? '1' : '0' ?>"
                  <?= ($aksi === 'ubah' && $bayar_dipilih['kode_sewa'] === $sewa['kode_sewa']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($sewa['kode_sewa']) ?> - <?= htmlspecialchars($detail['nm_lngkp'] ?? '-') ?>
                  <?php if ($detail && $detail['is_lunas']): ?>
                    (LUNAS)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div id="info_sewa" class="mt-2" style="display:none;">
              <small class="text-muted">Total Tagihan: <strong id="infoTotalTagihan" class="text-primary">Rp 0</strong></small><br>
              <small class="text-muted">Total Pembayaran (Valid): <strong id="infoTotalBayar" class="text-success">Rp 0</strong></small><br>
              <small class="text-muted">Pembayaran DP: <strong id="infoDPPaid" class="text-info">Rp 0</strong></small><br>
              <small class="text-muted">Pembayaran Pelunasan: <strong id="infoSisa" class="text-danger">Rp 0</strong></small>
            </div>
          </div>
        </div>

        <!-- 2. Jenis Pembayaran -->
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Jenis Pembayaran *</label>
            <select name="jns_byr" class="form-select" id="jns_byr" required>
              <option value="">Pilih...</option>
              <option value="dp" <?= ($aksi === 'ubah' && $bayar_dipilih['jns_byr'] === 'dp') ? 'selected' : '' ?>>DP (Uang Muka)</option>
              <option value="pelunasan" <?= ($aksi === 'ubah' && $bayar_dipilih['jns_byr'] === 'pelunasan') ? 'selected' : '' ?>>Pelunasan</option>
            </select>
          </div>

          <!-- 3. Input DP Percentage (hanya muncul jika DP) -->
          <div class="col-md-4 mb-3" id="div_dp_percentage" style="display:none;">
            <label class="form-label">Persentase DP (%) *</label>
            <input type="number" name="dp_percentage" id="dp_percentage" class="form-control"
              value="30" min="1" max="100" step="0.01">
            <small class="text-muted">Default 30%, dapat disesuaikan</small>
          </div>

          <!-- 4. Metode Pembayaran -->
          <div class="col-md-4 mb-3">
            <label class="form-label">Metode Pembayaran *</label>
            <select name="mtode_byr" class="form-select" id="mtode_byr" required>
              <option value="">Pilih...</option>
              <option value="tunai" <?= ($aksi === 'ubah' && $bayar_dipilih['mtode_byr'] === 'tunai') ? 'selected' : '' ?>>Tunai</option>
              <option value="transfer" <?= ($aksi === 'ubah' && $bayar_dipilih['mtode_byr'] === 'transfer') ? 'selected' : '' ?>>Transfer</option>
            </select>
          </div>
        </div>

        <!-- 5. Nominal -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Nominal (Rp) *</label>
            <input type="number" name="nominal" class="form-control" id="nominal"
              value="<?= $aksi === 'ubah' ? htmlspecialchars($bayar_dipilih['nominal']) : '' ?>"
              min="0" required>
            <small class="text-muted" id="info_nominal">Akan terisi otomatis</small>
          </div>

          <!-- 6. Status Pembayaran -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Status Pembayaran *</label>
            <select name="sttus_byr" class="form-select" required>
              <option value="">Pilih...</option>
              <option value="pending" <?= ($aksi === 'ubah' && $bayar_dipilih['sttus_byr'] === 'pending') ? 'selected' : ($aksi === 'tambah' ? 'selected' : '') ?>>Pending</option>
              <option value="valid" <?= ($aksi === 'ubah' && $bayar_dipilih['sttus_byr'] === 'valid') ? 'selected' : '' ?>>Valid</option>
              <option value="invalid" <?= ($aksi === 'ubah' && $bayar_dipilih['sttus_byr'] === 'invalid') ? 'selected' : '' ?>>Invalid</option>
            </select>
          </div>
        </div>

        <!-- 7. Bukti Pembayaran (hanya untuk transfer) -->
        <div class="mb-3" id="div_bukti_bayar">
          <label class="form-label">Bukti Pembayaran <span id="req_bukti"></span></label>
          <input type="file" name="ft_byr" class="form-control" id="ft_byr" accept="image/*">
          <small class="text-muted" id="info_bukti">Upload bukti transfer (jpg, jpeg, png, webp)</small>
          <?php if ($aksi === 'ubah' && !empty($bayar_dipilih['ft_byr'])): ?>
            <div class="mt-2">
              <small class="text-muted">Bukti saat ini:</small><br>
              <a href="<?= BASE_URL ?>uploads/pembayaran/<?= htmlspecialchars($bayar_dipilih['ft_byr']) ?>" target="_blank">
                <img src="<?= BASE_URL ?>uploads/pembayaran/<?= htmlspecialchars($bayar_dipilih['ft_byr']) ?>" width="150" class="rounded mt-1" alt="Bukti">
              </a>
            </div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan
        </button>
        <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=index" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </form>
    </div>
  </div>

  <script>
    // Data dari PHP
    const AKSI = '<?= $aksi ?>';
    const JNS_BYR_AWAL = '<?= $aksi === 'ubah' ? $bayar_dipilih['jns_byr'] : '' ?>';
    const MTODE_BYR_AWAL = '<?= $aksi === 'ubah' ? $bayar_dipilih['mtode_byr'] : '' ?>';
    const NOMINAL_AWAL = <?= $aksi === 'ubah' ? $bayar_dipilih['nominal'] : 0 ?>;

    // ==================== EVENT: Kode Sewa Dipilih ====================
    document.getElementById('kode_sewa').addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const totalTagihan = parseInt(selectedOption.getAttribute('data-total')) || 0;
      const totalDPPaid = parseInt(selectedOption.getAttribute('data-dp-paid')) || 0;
      const sisaTagihan = parseInt(selectedOption.getAttribute('data-sisa')) || 0;
      const totalBayarValid = parseInt(selectedOption.getAttribute('data-bayar-valid')) || 0;

      document.getElementById('info_sewa').style.display = 'block';
      document.getElementById('infoTotalTagihan').textContent = 'Rp ' + totalTagihan.toLocaleString('id-ID');
      document.getElementById('infoTotalBayar').textContent = 'Rp ' + totalBayarValid.toLocaleString('id-ID');
      document.getElementById('infoDPPaid').textContent = 'Rp ' + totalDPPaid.toLocaleString('id-ID');
      document.getElementById('infoSisa').textContent = 'Rp ' + sisaTagihan.toLocaleString('id-ID');

      const jnsByr = document.getElementById('jns_byr').value;
      if (jnsByr && AKSI === 'tambah') {
        hitungNominal(jnsByr, totalTagihan, totalDPPaid, sisaTagihan);
      }
    });

    // ==================== EVENT: Jenis Pembayaran Dipilih ====================
    document.getElementById('jns_byr').addEventListener('change', function() {
      const jnsByr = this.value;
      const divDPPerc = document.getElementById('div_dp_percentage');

      if (jnsByr === 'dp') {
        divDPPerc.style.display = 'block';
        document.getElementById('dp_percentage').required = true;
      } else {
        divDPPerc.style.display = 'none';
        document.getElementById('dp_percentage').required = false;
      }

      if (AKSI === 'tambah') {
        const selectedOption = document.getElementById('kode_sewa').options[document.getElementById('kode_sewa').selectedIndex];
        const totalTagihan = parseInt(selectedOption.getAttribute('data-total')) || 0;
        const totalDPPaid = parseInt(selectedOption.getAttribute('data-dp-paid')) || 0;
        const sisaTagihan = parseInt(selectedOption.getAttribute('data-sisa')) || 0;
        hitungNominal(jnsByr, totalTagihan, totalDPPaid, sisaTagihan);
      }
    });

    // ==================== EVENT: DP Percentage Berubah ====================
    document.getElementById('dp_percentage').addEventListener('input', function() {
      const selectedOption = document.getElementById('kode_sewa').options[document.getElementById('kode_sewa').selectedIndex];
      const totalTagihan = parseInt(selectedOption.getAttribute('data-total')) || 0;
      const dpPercentage = parseFloat(this.value) || 0;
      const nominalDP = Math.round((dpPercentage / 100) * totalTagihan);
      document.getElementById('nominal').value = nominalDP;
    });

    // ==================== EVENT: Metode Pembayaran Dipilih ====================
    document.getElementById('mtode_byr').addEventListener('change', function() {
      const mtodeByr = this.value;
      const divBukti = document.getElementById('div_bukti_bayar');
      const inputBukti = document.getElementById('ft_byr');
      const reqBukti = document.getElementById('req_bukti');
      const infoBukti = document.getElementById('info_bukti');

      if (mtodeByr === 'transfer') {
        divBukti.style.display = 'block';
        if (AKSI === 'tambah' || !document.querySelector('img[alt="Bukti"]')) {
          inputBukti.required = true;
          reqBukti.textContent = '*';
        }
        infoBukti.textContent = 'Upload bukti transfer (jpg, jpeg, png, webp)';
      } else {
        divBukti.style.display = 'block';
        inputBukti.required = false;
        reqBukti.textContent = '';
        infoBukti.textContent = 'Pembayaran tunai tidak memerlukan bukti';
      }
    });

    // ==================== FUNGSI: Hitung Nominal ====================
    function hitungNominal(jnsByr, totalTagihan, totalDPPaid, sisaTagihan) {
      const nominalInput = document.getElementById('nominal');
      const infoNominal = document.getElementById('info_nominal');

      if (jnsByr === 'dp') {
        const dpPercentage = parseFloat(document.getElementById('dp_percentage').value) || 30;
        const nominalDP = Math.round((dpPercentage / 100) * totalTagihan);
        nominalInput.value = nominalDP;
        infoNominal.textContent = 'DP ' + dpPercentage + '% dari total tagihan (dapat disesuaikan)';
      } else if (jnsByr === 'pelunasan') {
        nominalInput.value = sisaTagihan;
        infoNominal.textContent = 'Sisa tagihan yang harus dibayar';
      }
    }

    // ==================== VALIDASI SEBELUM SUBMIT ====================
    document.querySelector('form').addEventListener('submit', function(e) {
      const jnsByr = document.getElementById('jns_byr').value;
      const mtodeByr = document.getElementById('mtode_byr').value;
      const kodeSewa = document.getElementById('kode_sewa').value;

      if (!kodeSewa) {
        e.preventDefault();
        alert('Pilih Kode Sewa terlebih dahulu!');
        return false;
      }

      if (!jnsByr) {
        e.preventDefault();
        alert('Pilih Jenis Pembayaran (DP atau Pelunasan)!');
        return false;
      }

      if (!mtodeByr) {
        e.preventDefault();
        alert('Pilih Metode Pembayaran!');
        return false;
      }

      if (mtodeByr === 'transfer' && AKSI === 'tambah') {
        const ftByr = document.getElementById('ft_byr').files.length;
        if (ftByr === 0) {
          e.preventDefault();
          alert('Pembayaran transfer WAJIB menyertakan bukti!');
          return false;
        }
      }
    });

    // ==================== INIT SAAT LOAD ====================
    document.addEventListener('DOMContentLoaded', function() {
      const kodeSewaSelect = document.getElementById('kode_sewa');
      if (kodeSewaSelect.value) {
        kodeSewaSelect.dispatchEvent(new Event('change'));
      }

      if (JNS_BYR_AWAL) {
        document.getElementById('jns_byr').value = JNS_BYR_AWAL;
        document.getElementById('jns_byr').dispatchEvent(new Event('change'));
      }

      if (MTODE_BYR_AWAL) {
        document.getElementById('mtode_byr').value = MTODE_BYR_AWAL;
        document.getElementById('mtode_byr').dispatchEvent(new Event('change'));
      }

      // MODE EDIT: Kembalikan nominal ke nilai asli dari database
      if (AKSI === 'ubah' && NOMINAL_AWAL > 0) {
        document.getElementById('nominal').value = NOMINAL_AWAL;
      }
    });
  </script>
<?php endif; ?>

<!-- ==================== HALAMAN CETAK BUKTI PEMBAYARAN ==================== -->
<!-- ==================== HALAMAN CETAK BUKTI PEMBAYARAN ==================== -->
<?php if ($aksi === 'cetak'): ?>
  <!-- Tombol Aksi (tidak ikut tercetak) -->
  <div class="card no-print">
    <div class="card-body">
      <button onclick="window.print()" class="btn btn-primary mb-3 me-2">
        <i class="fas fa-print"></i> Cetak / Simpan PDF
      </button>
      <a href="<?= BASE_URL ?>admin/controller/tpembayaran_controller.php?aksi=index" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>
  </div>

  <!-- Area Cetak -->
  <div class="card print-area">
    <div class="card-body surat-body">

      <!-- Header Surat -->
      <div class="mb-3" style="display: flex; align-items: center; gap: 15px;">
        <!-- Logo di Kiri -->
        <div style="flex-shrink: 0;">
          <img src="<?= BASE_URL ?>aset/images/logo-mjt.png"
            alt="Logo MJT"
            style="height: 90px; width: 90px; object-fit: contain;">
        </div>

        <!-- Teks di Kanan -->
        <div style="flex-grow: 1; text-align: center; line-height: 1.3;">
          <h6 class="mb-0 fw-bold" style="letter-spacing: 1px; font-size: 15px;">SURAT BUKTI PEMBAYARAN SEWA MOBIL</h6>
          <h5 class="mb-0 fw-bold" style="font-size: 17px;">Usaha</h5>
          <p class="mb-0" style="font-size: 13px;">Jalan Senang Kode, Provinsi Kepulauan Bangka Belitung</p>
          <p class="mb-0" style="font-size: 13px;">HP/WA: 0852 6852 1825</p>
        </div>
      </div>
      <hr style="border-top: 2px solid #000; margin-top: 10px;">

      <!-- Info Sewa & Penyewa -->
      <div class="row mb-3" style="font-size: 14px;">
        <div class="col-md-6">
          <table class="table table-borderless table-sm mb-0" style="line-height: 1.0; margin-bottom: 0;">
            <tr>
              <td style="width: 130px;"><strong>Kode Sewa</strong></td>
              <td>:</td>
              <td><?= htmlspecialchars($sewa_data['kode_sewa'] ?? '') ?></td>
            </tr>
            <tr>
              <td><strong>Nama Penyewa</strong></td>
              <td>:</td>
              <td><?= htmlspecialchars($sewa_data['nm_lngkp'] ?? '') ?></td>
            </tr>
            <tr>
              <td><strong>No. HP</strong></td>
              <td>:</td>
              <td><?= htmlspecialchars($sewa_data['no_hp'] ?? '') ?></td>
            </tr>
            <tr>
              <td><strong>Alamat</strong></td>
              <td>:</td>
              <td><?= htmlspecialchars($sewa_data['almt_lngkp'] ?? '') ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Paragraf Narasi -->
      <p style="font-size: 14px; text-align: justify; margin-bottom: 5px;">
        Pada hari <strong><?= htmlspecialchars($hari_mulai) ?></strong> tanggal
        <strong><?= htmlspecialchars($tgl_mulai_fmt) ?></strong> pukul
        <strong><?= htmlspecialchars($wkt_ambil) ?></strong> WIB sampai dengan hari
        <strong><?= htmlspecialchars($hari_blk) ?></strong> tanggal
        <strong><?= htmlspecialchars($tgl_blk_fmt) ?></strong> pukul
        <strong><?= htmlspecialchars($wkt_blk_fmt) ?></strong> WIB
        (durasi sewa <strong><?= $total_n_hari ?> hari</strong>), telah dilakukan transaksi sewa mobil sebagai berikut:
      </p>

      <!-- Tabel Detail Mobil -->
      <table class="table table-bordered table-sm" style="font-size: 13px;">
        <thead class="table-light" style="background-color: #676969;">
          <tr>
            <th style="width: 40px;">No</th>
            <th>Nama Mobil</th>
            <th>No Plat</th>
            <th style="width: 130px;">Harga/Hari</th>
            <th style="width: 130px;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($detail_mobil as $dm): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($dm['nm_mobil']) ?></td>
              <td><?= htmlspecialchars($dm['no_plat']) ?></td>
              <td>Rp. <?= number_format($dm['hrg_sewa'], 0, ',', '.') ?></td>
              <td>Rp. <?= number_format($dm['subtotal'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="text-end"><strong>Total Sewa</strong></td>
            <td><strong>Rp. <?= number_format($total_sewa, 0, ',', '.') ?></strong></td>
          </tr>
          <tr>
            <td colspan="4" class="text-end">
              <strong>Jumlah Pembayaran DP</strong>
              <?php foreach ($dp_list as $dp): ?>
                <small>(<?= htmlspecialchars($dp['kode_bayar']) ?>) <?= date('d/m/Y H:i', strtotime($dp['tgl_wkt_byr'])) ?></small>
              <?php endforeach; ?>
              <?php if (empty($dp_list)): ?>
              <?php endif; ?>
            </td>
            <td><strong>Rp. <?= number_format($total_dp, 0, ',', '.') ?></strong></td>
          </tr>
          <tr>
            <td colspan="4" class="text-end">
              <strong>Jumlah Pembayaran Pelunasan</strong>
              <?php foreach ($pelunasan_list as $pel): ?>
                <small>(<?= htmlspecialchars($pel['kode_bayar']) ?>) <?= date('d/m/Y H:i', strtotime($pel['tgl_wkt_byr'])) ?></small>
              <?php endforeach; ?>
              <?php if (empty($pelunasan_list)): ?>
              <?php endif; ?>
            </td>
            <td><strong>Rp. <?= number_format($total_pelunasan, 0, ',', '.') ?></strong></td>
          </tr>

          <?php if ($denda > 0): ?>
            <tr>
              <td colspan="4" class="text-end">
                <strong>Denda Keterlambatan</strong>
                <small>(<?= $telat_jam ?> jam x Rp.<?= number_format($tarif_per_jam, 0, ',', '.') ?>/jam)</small>
              </td>
              <td><strong>Rp. <?= number_format($denda, 0, ',', '.') ?></strong></td>
            </tr>
          <?php endif; ?>

          <tr>
            <td colspan="4" class="text-end">
              <strong>Total Pembayaran</strong>
            </td>
            <td><strong>Rp. <?= number_format($total_pembayaran, 0, ',', '.') ?></strong></td>
          </tr>

          <tr>
            <td colspan="4" class="text-end">
              <strong>Status Pembayaran</strong>
            </td>
            <td><strong><?= htmlspecialchars($status_pembayaran) ?></strong></td>
          </tr>

        </tfoot>
      </table>

      <!-- Catatan & Kebijakan -->
      <div style="font-size: 12px; margin-top: 15px; margin-bottom: 15px;">
        <p class="mb-1"><strong>Catatan:</strong></p>
        <ul class="mb-2 ps-3">
          <li>Semakin lengkap data semakin mudah kami ACC.</li>
          <li>Penyewa diwajibkan menitipkan KTP sebagai jaminan.</li>
        </ul>
        <p class="mb-1"><strong>Kebijakan Pengguna:</strong></p>
        <ol class="mb-0 ps-3">
          <li>Penyewa tetap mematuhi peraturan tata cara berlalu lintas.</li>
          <li>Biaya tambahan akan dikenakan untuk penjemputan dan pengembalian di luar zona.</li>
          <li>BBM (Bahan Bakar Minyak) Kendaraan R4 ditanggung oleh penyewa.</li>
          <li>Penyewa tidak boleh mengalihkan, memberikan/menjadikan mobil sebagai jaminan kepada pihak lain, apabila penyewa melanggar dari ketentuan ini, maka si penyewa siap dipidanakan dengan hukum yang berlaku di Indonesia.</li>
          <li>Sewa 1 hari hanya area Bangka Belitung.</li>
        </ol>
      </div>

      <!-- Tanda Tangan -->
      <div class="row mt-4" style="font-size: 13px;">
        <div class="col-6 text-center">
          <p class="mb-0"><strong>Penyewa,</strong></p>
          <div style="height: 70px;"></div>
          <p class="mb-0 text-decoration-underline"><strong><?= htmlspecialchars($sewa_data['user_plg'] ?? '') ?></strong></p>
        </div>
        <div class="col-6 text-center">
          <p class="mb-0">Pangkalpinang, <?= htmlspecialchars($tgl_ttd) ?></p>
          <div style="height: 70px;"></div>
          <p class="mb-0 text-decoration-underline"><strong><?= htmlspecialchars($admin_username) ?></strong></p>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center mt-4 pt-2" style="font-size: 11px; color: #666;">
        Dokumen ini dicetak secara otomatis pada <?= date('d/m/Y H:i:s') ?>
      </div>

    </div>
  </div>

  <!-- CSS Print-Friendly -->
  <style>
    .surat-body {
      font-family: 'Arial', 'Verdana', sans-serif !important;
      color: #000;
      padding: 20px 30px !important;
    }

    .surat-body h4,
    .surat-body h5 {
      font-family: 'Arial', 'Verdana', sans-serif !important;
    }

    .surat-body table {
      margin-bottom: 8px;
    }

    .surat-body table td,
    .surat-body table th {
      padding: 4px 6px;
      vertical-align: top;
    }

    @media print {
      body * {
        visibility: hidden !important;
      }

      .print-area,
      .print-area * {
        visibility: visible !important;
      }

      .print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 5px 10px !important;
        border: none !important;
        box-shadow: none !important;
        background: white !important;
      }

      .main-header,
      .main-sidebar,
      .sidebar,
      .navbar,
      nav,
      .no-print,
      .content-header,
      .brand-link,
      .nav-sidebar,
      .nav-treeview,
      .sidebar-collapse,
      footer,
      .footer,
      .alert,
      .btn,
      .card-header {
        display: none !important;
      }

      .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
      }

      body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
      }

      .card-body {
        padding: 0 !important;
      }

      table {
        page-break-inside: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }
    }

    @media screen {
      .print-area {
        background: white;
        padding: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: 0 auto;
      }
    }
  </style>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>