<?php
// Memanggil Header (Berisi DOCTYPE, Navigasi, dll)
require_once __DIR__ . '/header.php';
?>

<!-- Konten Halaman Login -->
<main class="page-wrap">
  <div class="container">
    <div class="auth-card">
      <h2>Selamat Datang</h2>
      <p class="sub">Masuk untuk melanjutkan perjalanan mewah Anda.</p>

      <!-- Notifikasi Error / Sukses dari Controller -->
      <?php if (!empty($pesan)): ?>
        <div style="
                    padding: 12px 16px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    font-size: 14px;
                    background: <?= $jenis === 'sukses' ? '#d4edda' : '#f8d7da' ?>;
                    color: <?= $jenis === 'sukses' ? '#155724' : '#721c24' ?>;
                    border: 1px solid <?= $jenis === 'sukses' ? '#c3e6cb' : '#f5c6cb' ?>;
                ">
          <?= $pesan ?>
        </div>
      <?php endif; ?>

      <!-- Form Login -->
      <form action="" method="POST">
        <div class="form-group">
          <label for="user_plg">Username</label>
          <input type="text" id="user_plg" name="user_plg" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['user_plg'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="sandi">Kata Sandi</label>
          <input type="password" id="sandi" name="sandi" placeholder="Masukkan kata sandi" required>
        </div>

        <div class="form-row">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--gray-700);">
            <input type="checkbox" name="ingat" style="width: auto; accent-color: var(--gold);"> Ingat saya
          </label>
        </div>

        <button type="submit" class="btn btn-gold btn-block">Masuk Sekarang</button>
      </form>

      <div class="auth-footer">
        Belum punya akun? <a href="<?= BASE_URL ?>penyewa/controller/tregistrasi_controller.php">Registrasi di sini</a>
      </div>
    </div>
  </div>
</main>

<?php
// Memanggil Footer (Berisi Footer HTML, jQuery, dan Semua Script JS)
require_once __DIR__ . '/footer.php';
?>