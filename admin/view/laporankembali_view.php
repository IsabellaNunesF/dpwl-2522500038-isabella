<?php require_once __DIR__ . '/header.php'; ?>

<!-- Content Header (Page header) -->
<div class="content-header mb-3">
  <h1 class="m-0 text-dark"><i class="fas fa-undo-alt"></i> <?= $judul_halaman ?></h1>
</div>

<!-- Filter Card -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Periode Laporan</h5>
  </div>
  <div class="card-body">
    <form method="GET" action="<?= BASE_URL ?>admin/controller/laporankembali_controller.php">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($filter['tgl_mulai']) ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Tanggal Selesai</label>
          <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($filter['tgl_selesai']) ?>">
        </div>
        <div class="col-md-4 mb-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Tampilkan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Rekapitulasi Summary Cards -->
<div class="row mb-3">
  <div class="col-md-3">
    <div class="card bg-primary text-white shadow">
      <div class="card-body">
        <h5 class="card-title mb-1">Total Pengembalian</h5>
        <h3 class="mb-0"><?= $total_pengembalian ?> Kali</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-success text-white shadow">
      <div class="card-body">
        <h5 class="card-title mb-1">Total Denda</h5>
        <h3 class="mb-0">Rp <?= number_format($total_denda, 0, ',', '.') ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-warning text-white shadow">
      <div class="card-body">
        <h5 class="card-title mb-1">Total Terlambat</h5>
        <h3 class="mb-0"><?= $total_terlambat ?> Kali</h3>
      </div>
    </div>
  </div>
</div>

<!-- Tabel Detail Laporan -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-table"></i> Detail Laporan Pengembalian</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th class="text-center" width="40">No</th>
            <th>Kode Kembali</th>
            <th>Tgl/Waktu Kembali</th>
            <th>Kode Sewa</th>
            <th>Penyewa</th>
            <th>Detail Mobil</th>
            <th>Periode Sewa</th>
            <th class="text-center">Status Kembali</th>
            <th>Keterangan</th>
            <th class="text-center">Telat (Jam)</th>
            <th class="text-end">Denda</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_laporan)): ?>
            <?php $no = 1;
            foreach ($data_laporan as $row): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($row['kode_kembali']) ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tgl_wkt_blk'])) ?></td>
                <td><?= htmlspecialchars($row['kode_sewa']) ?></td>
                <td>
                  <?= htmlspecialchars($row['nm_lngkp']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($row['no_hp']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['detail_mobil']) ?></td>
                <td>
                  <?php if ($row['tgl_mulai'] !== '-' && $row['tgl_selesai'] !== '-'): ?>
                    <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?><br>
                    <small class="text-muted"><?= $row['total_hari'] ?> Hari</small>
                  <?php else: ?> - <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php
                  $badge_class = 'secondary';
                  if ($row['stts_blk'] == 'normal') $badge_class = 'success';
                  elseif ($row['stts_blk'] == 'terlambat') $badge_class = 'warning';
                  ?>
                  <span class="badge bg-<?= $badge_class ?>"><?= ucfirst(str_replace('_', ' ', $row['stts_blk'])) ?></span>
                </td>
                <td><small><?= htmlspecialchars($row['ket_blk'] ?: '-') ?></small></td>
                <td class="text-center"><?= $row['telat_jam'] ?></td>
                <td class="text-end">Rp <?= number_format($row['total_denda'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="11" class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                <p class="text-muted mb-0">Tidak ada data pengembalian pada periode ini.</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Tabel Rekapitulasi per Mobil -->
<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0"><i class="fas fa-car"></i> Rekapitulasi Pengembalian per Mobil</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-info text-dark">
          <tr>
            <th class="text-center" width="40">No</th>
            <th>No Plat</th>
            <th>Nama Mobil</th>
            <th class="text-center">Jenis</th>
            <th class="text-center">Total Dikembalikan</th>
            <th class="text-center">Kondisi Normal</th>
            <th class="text-center">Terlambat</th>
            <th class="text-end">Total Denda</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $ada_rekap = false;
          if (!empty($rekap_mobil)):
            $no = 1;
            foreach ($rekap_mobil as $rm):
              // Hanya tampilkan mobil yang pernah dikembalikan di periode filter ini
              if ($rm['total_dikembalikan'] > 0):
                $ada_rekap = true;
          ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><strong><?= htmlspecialchars($rm['no_plat']) ?></strong></td>
                  <td><?= htmlspecialchars($rm['nm_mobil']) ?></td>
                  <td class="text-center"><?= htmlspecialchars($rm['jns_mobil']) ?></td>
                  <td class="text-center"><span class="badge bg-primary"><?= $rm['total_dikembalikan'] ?></span></td>
                  <td class="text-center"><span class="badge bg-success"><?= $rm['status_kondisi']['normal'] ?></span></td>
                  <td class="text-center"><span class="badge bg-warning"><?= $rm['status_kondisi']['terlambat'] ?></span></td>
                  <td class="text-end">Rp <?= number_format($rm['total_denda'], 0, ',', '.') ?></td>
                </tr>
          <?php
              endif;
            endforeach;
          endif;
          ?>
          <?php if (!$ada_rekap): ?>
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">Tidak ada data rekapitulasi mobil untuk periode ini.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>