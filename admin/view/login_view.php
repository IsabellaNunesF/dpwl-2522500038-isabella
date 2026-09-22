<!-- admin/view/login_view.php -->
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($judul_halaman) ?></title>

  <!-- Memanggil Aset (Bootstrap, CSS Custom, dll) -->
  <!-- GANTI SEMUA path aset yang lama seperti ../../aset/... menjadi seperti ini: -->
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/bootstrap-5/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>aset/style-admin.css">


</head>

<body class="bg-light">

  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="text-center mb-4">
              <img width="50px" src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo Usaha"> Login Admin
            </h3>

            <!-- Tampilan Error (Murni Presentasi) -->
            <?php if (!empty($pesan_error)): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Masuk
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Memanggil JS Aset -->
  <script src="<?= BASE_URL ?>aset/jquery/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>aset/bootstrap-5/js/bootstrap.bundle.min.js"></script>
</body>

</html>