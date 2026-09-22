<?php require_once __DIR__ . '/header.php'; ?>

<!-- Content Header (Page header) -->
<div class="content-header mb-3">
  <!-- <h1 class="m-0 text-dark"><i class="fas fa-chart-line"></i> <?= $judul_halaman ?></h1> -->
</div>

<!-- ==================== HALAMAN INDEX (Tampilan Biasa) ==================== -->
<?php if ($aksi === 'index'): ?>
  <!-- Filter Card -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Periode Laporan</h5>
    </div>
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>admin/controller/laporansewa_controller.php">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($filter['tgl_mulai']) ?>">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($filter['tgl_selesai']) ?>">
          </div>
          <div class="col-md-2 mb-3">
            <label class="form-label">Metode Bayar</label>
            <select name="mtode_byr" class="form-select">
              <option value="">Semua</option>
              <option value="tunai" <?= $filter['mtode_byr'] === 'tunai' ? 'selected' : '' ?>>Tunai</option>
              <option value="transfer" <?= $filter['mtode_byr'] === 'transfer' ? 'selected' : '' ?>>Transfer</option>
            </select>
          </div>
          <div class="col-md-2 mb-3">
            <label class="form-label">Jenis Bayar</label>
            <select name="jns_byr" class="form-select">
              <option value="">Semua</option>
              <option value="dp" <?= $filter['jns_byr'] === 'dp' ? 'selected' : '' ?>>DP</option>
              <option value="pelunasan" <?= $filter['jns_byr'] === 'pelunasan' ? 'selected' : '' ?>>Pelunasan</option>
            </select>
          </div>
          <div class="col-md-2 mb-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-search"></i> Tampilkan
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Rekapitulasi Card -->
  <div class="row mb-3">
    <div class="col-md-6">
      <div class="card bg-success text-white shadow">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Total Pendapatan Sewa</h5>
              <p class="mb-0 small">Berdasarkan filter yang dipilih</p>
            </div>
            <h3 class="mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card bg-info text-white shadow">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Jumlah Transaksi</h5>
              <p class="mb-0 small">Total pembayaran valid</p>
            </div>
            <h3 class="mb-0"><?= $jumlah_transaksi ?> Transaksi</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel Laporan Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-table"></i> Detail Laporan Pendapatan</h5>
      <!-- TOMBOL CETAK MUNCUL DI SINI -->
      <?php if (!empty($data_laporan)): ?>
        <a href="<?= BASE_URL ?>admin/controller/laporansewa_controller.php?aksi=cetak&<?= http_build_query($filter) ?>" class="btn btn-success btn-sm" target="_blank">
          <i class="fas fa-print"></i> Cetak Laporan
        </a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th class="text-center" width="40">No</th>
              <th>Kode Bayar</th>
              <th>Tgl/Waktu Bayar</th>
              <th>Kode Sewa</th>
              <th>Nama Penyewa</th>
              <th>No HP</th>
              <th>Detail Mobil</th>
              <th>Periode Sewa</th>
              <th class="text-center">Metode</th>
              <th class="text-center">Jenis</th>
              <th class="text-end">Nominal</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($data_laporan)): ?>
              <?php $no = 1;
              foreach ($data_laporan as $row): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><strong><?= htmlspecialchars($row['kode_bayar']) ?></strong></td>
                  <td><?= date('d/m/Y H:i', strtotime($row['tgl_wkt_byr'])) ?></td>
                  <td><?= htmlspecialchars($row['kode_sewa']) ?></td>
                  <td><?= htmlspecialchars($row['nm_lngkp']) ?></td>
                  <td><?= htmlspecialchars($row['no_hp']) ?></td>
                  <td><?= htmlspecialchars($row['detail_mobil']) ?></td>
                  <td>
                    <?php if (!empty($row['tgl_mulai']) && !empty($row['tgl_selesai'])): ?>
                      <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> -
                      <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-<?= $row['mtode_byr'] == 'tunai' ? 'secondary' : 'primary' ?>">
                      <?= ucfirst($row['mtode_byr']) ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-<?= $row['jns_byr'] == 'dp' ? 'info' : 'success' ?>">
                      <?= strtoupper($row['jns_byr']) ?>
                    </span>
                  </td>
                  <td class="text-end">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="11" class="text-center py-4">
                  <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                  <p class="text-muted mb-0">Tidak ada data yang sesuai dengan filter.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
          <?php if (!empty($data_laporan)): ?>
            <tfoot>
              <tr class="table-secondary">
                <th colspan="10" class="text-end">TOTAL PENDAPATAN:</th>
                <th class="text-end">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></th>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- ==================== HALAMAN CETAK LAPORAN (Print-Friendly Landscape) ==================== -->
<?php elseif ($aksi === 'cetak'): ?>

  <!-- Tombol Aksi (tidak ikut tercetak) -->
  <div class="card no-print">
    <div class="card-body">
      <button onclick="window.print()" class="btn btn-primary mb-3 me-2">
        <i class="fas fa-print"></i> Cetak / Simpan PDF
      </button>
      <a href="<?= BASE_URL ?>admin/controller/laporansewa_controller.php?<?= http_build_query($filter) ?>" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>
  </div>

  <!-- Area Cetak -->
  <div class="card print-area">
    <div class="card-body surat-body">

      <!-- Header Surat (Mirip Bukti Pembayaran) -->
      <div class="mb-3" style="display: flex; align-items: center; gap: 15px;">
        <div style="flex-shrink: 0;">
          <img src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo MJT" style="height: 80px; width: 80px; object-fit: contain;">
        </div>
        <div style="flex-grow: 1; text-align: center; line-height: 1.3;">
          <h6 class="mb-0 fw-bold" style="letter-spacing: 1px; font-size: 15px;">LAPORAN PENDAPATAN SEWA MOBIL</h6>
          <h5 class="mb-0 fw-bold" style="font-size: 17px;">Usaha</h5>
          <p class="mb-0" style="font-size: 13px;">Jalan Senang Kode, Provinsi Kepulauan Bangka Belitung</p>
          <p class="mb-0" style="font-size: 13px;">HP/WA: 0852 6852 1825</p>
        </div>
      </div>
      <hr style="border-top: 2px solid #000; margin-top: 10px;">

      <!-- Info Filter Periode -->
      <div class="mb-3" style="font-size: 13px;">
        <table class="table-borderless table-sm mb-0" style="line-height: 1.4;">
          <tr>
            <td style="width: 130px;"><strong>Periode Laporan</strong></td>
            <td style="width: 10px;">:</td>
            <td>
              <?php if (!empty($filter['tgl_mulai']) || !empty($filter['tgl_selesai'])): ?>
                <?= !empty($filter['tgl_mulai']) ? date('d/m/Y', strtotime($filter['tgl_mulai'])) : 'Awal' ?>
                s/d
                <?= !empty($filter['tgl_selesai']) ? date('d/m/Y', strtotime($filter['tgl_selesai'])) : 'Sekarang' ?>
              <?php else: ?>
                Semua Periode
              <?php endif; ?>
            </td>
            <td style="width: 130px; padding-left: 30px;"><strong>Metode Bayar</strong></td>
            <td style="width: 10px;">:</td>
            <td><?= !empty($filter['mtode_byr']) ? ucfirst($filter['mtode_byr']) : 'Semua' ?></td>

            <td style="width: 130px; padding-left: 30px;"><strong>Jenis Bayar</strong></td>
            <td style="width: 10px;">:</td>
            <td><?= !empty($filter['jns_byr']) ? strtoupper($filter['jns_byr']) : 'Semua' ?></td>
          </tr>
        </table>
      </div>

      <!-- Tabel Detail Laporan -->
      <table class="table table-bordered table-sm" style="font-size: 11px;">
        <thead style="background-color: #e9ecef;">
          <tr>
            <th style="width: 30px; text-align: center;">No</th>
            <th>Kode Bayar</th>
            <th>Tgl/Waktu Bayar</th>
            <th>Kode Sewa</th>
            <th>Nama Penyewa</th>
            <th>No HP</th>
            <th>Detail Mobil</th>
            <th>Periode Sewa</th>
            <th style="text-align: center; width: 70px;">Metode</th>
            <th style="text-align: center; width: 70px;">Jenis</th>
            <th style="width: 110px; text-align: right;">Nominal</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_laporan)): ?>
            <?php $no = 1;
            foreach ($data_laporan as $row): ?>
              <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['kode_bayar']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tgl_wkt_byr'])) ?></td>
                <td><?= htmlspecialchars($row['kode_sewa']) ?></td>
                <td><?= htmlspecialchars($row['nm_lngkp']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td><?= htmlspecialchars($row['detail_mobil']) ?></td>
                <td>
                  <?php if (!empty($row['tgl_mulai']) && !empty($row['tgl_selesai'])): ?>
                    <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td style="text-align: center;"><?= ucfirst($row['mtode_byr']) ?></td>
                <td style="text-align: center;"><?= strtoupper($row['jns_byr']) ?></td>
                <td style="text-align: right;">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="11" style="text-align: center;">Tidak ada data yang sesuai dengan filter.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($data_laporan)): ?>
          <tfoot>
            <tr>
              <td colspan="10" style="text-align: right; font-weight: bold; background-color: #f8f9fa;">TOTAL PENDAPATAN:</td>
              <td style="text-align: right; font-weight: bold; background-color: #f8f9fa;">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
            </tr>
            <tr>
              <td colspan="11" style="text-align: right; font-size: 10px; font-style: italic; background-color: #f8f9fa;">
                (Terdiri dari <?= $jumlah_transaksi ?> Transaksi Pembayaran)
              </td>
            </tr>
          </tfoot>
        <?php endif; ?>
      </table>

      <!-- Tanda Tangan Admin -->
      <div class="row mt-4" style="font-size: 12px;">
        <div class="col-6"></div>
        <div class="col-6 text-center">
          <p class="mb-0">Pangkalpinang, <?= htmlspecialchars($tgl_ttd) ?></p>
          <div style="height: 60px;"></div>
          <p class="mb-0 text-decoration-underline"><strong><?= htmlspecialchars($nama_admin) ?></strong></p>
        </div>
      </div>

      <!-- Footer Cetak -->
      <div class="text-center mt-3 pt-2" style="font-size: 10px; color: #666; border-top: 1px dashed #ccc;">
        Dokumen ini dicetak secara otomatis oleh sistem pada <?= date('d/m/Y H:i:s') ?> WIB
      </div>

    </div>
  </div>

  <!-- CSS Khusus Print-Friendly (Landscape) -->
  <style>
    /* Aturan Kertas Landscape */
    @media print {
      @page {
        size: A4 landscape;
        margin: 1.5cm;
      }

      /* Sembunyikan semua elemen kecuali area cetak */
      body * {
        visibility: hidden !important;
      }

      .print-area,
      .print-area * {
        visibility: visible !important;
      }

      /* Reset posisi area cetak */
      .print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: white !important;
      }

      .surat-body {
        font-family: 'Arial', 'Verdana', sans-serif !important;
        color: #000;
        padding: 0 !important;
      }

      /* Sembunyikan elemen navigasi & tombol */
      .no-print,
      .main-header,
      .main-sidebar,
      .sidebar,
      .navbar,
      nav,
      footer,
      .footer,
      .alert,
      .btn,
      .card-header,
      .content-header {
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

      /* Styling Tabel saat Print */
      table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11px !important;
      }

      table th,
      table td {
        border: 1px solid #333 !important;
        padding: 4px 6px !important;
        text-align: left !important;
      }

      table th {
        background-color: #e9ecef !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        text-align: center !important;
      }

      tfoot tr {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }

    /* Tampilan di Layar Monitor (Preview) */
    @media screen {
      .print-area {
        background: white;
        padding: 20px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        max-width: 1100px;
        margin: 20px auto;
      }

      .surat-body table th {
        background-color: #e9ecef;
      }
    }
  </style>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>