<?php require_once __DIR__ . '/header.php'; ?>

<!-- Baris 1: Total Pendapatan -->
<div class="row">
  <div class="col-md-4">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Total Pendapatan</h6>
            <h3 class="mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
          </div>
          <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Pendapatan Sewa</h6>
            <h3 class="mb-0">Rp <?= number_format($pendapatan_sewa, 0, ',', '.') ?></h3>
          </div>
          <i class="fas fa-car fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-info text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Pendapatan Denda</h6>
            <h3 class="mb-0">Rp <?= number_format($pendapatan_denda, 0, ',', '.') ?></h3>
          </div>
          <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Baris 2: Status Mobil -->
<div class="row mt-3">
  <div class="col-md-3">
    <div class="card bg-secondary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Total Mobil</h6>
            <h3 class="mb-0"><?= $stat_mobil['total'] ?></h3>
          </div>
          <i class="fas fa-warehouse fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Tersedia</h6>
            <h3 class="mb-0"><?= $stat_mobil['tersedia'] ?></h3>
          </div>
          <i class="fas fa-check-circle fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Disewa</h6>
            <h3 class="mb-0"><?= $stat_mobil['disewa'] ?></h3>
          </div>
          <i class="fas fa-key fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
	<div class="col-md-3">
    <div class="card bg-danger text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Tidak Tersedia</h6>
            <h3 class="mb-0"><?= $stat_mobil['tidaktersedia'] ?></h3>
          </div>
          <i class="fas fa-circle-stop fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Baris 3: Status Sewa -->
<div class="row mt-3">
  <div class="col-md-3">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Total Sewa</h6>
            <h3 class="mb-0"><?= $stat_sewa['total'] ?></h3>
          </div>
          <i class="fas fa-file-invoice fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Sewa Aktif</h6>
            <h3 class="mb-0"><?= $stat_sewa['aktif'] ?></h3>
          </div>
          <i class="fas fa-play-circle fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Diajukan</h6>
            <h3 class="mb-0"><?= $stat_sewa['diajukan'] ?></h3>
          </div>
          <i class="fas fa-clock fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-info text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Selesai</h6>
            <h3 class="mb-0"><?= $stat_sewa['selesai'] ?></h3>
          </div>
          <i class="fas fa-check-double fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Baris 4: Informasi Lainnya -->
<div class="row mt-3">
  <div class="col-md-3">
    <div class="card bg-secondary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Penyewa Terdaftar</h6>
            <h3 class="mb-0"><?= $jumlah_penyewa ?></h3>
          </div>
          <i class="fas fa-users fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-danger text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Pembayaran Pending</h6>
            <h3 class="mb-0"><?= $pembayaran_pending ?></h3>
          </div>
          <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-1">Pengembalian Terlambat</h6>
            <h3 class="mb-0"><?= $pengembalian_terlambat ?></h3>
          </div>
          <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>