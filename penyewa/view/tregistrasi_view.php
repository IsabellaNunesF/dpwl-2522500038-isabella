<?php
// Helper untuk menampilkan value lama saat validasi gagal
function old($field)
{
  return htmlspecialchars($_POST[$field] ?? '');
}

// Memanggil Header (Berisi DOCTYPE, Navigasi, dll)
require_once __DIR__ . '/header.php';
?>

<!-- Konten Halaman Registrasi -->
<main class="page-wrap">
  <div class="container">
    <div class="auth-card">
      <h2><i class="fa-solid fa-user-plus text-gold"></i> Registrasi Akun</h2>
      <p class="sub">Buat akun untuk mulai menyewa armada premium kami.</p>

      <!-- Notifikasi Error dari Controller -->
      <?php if (!empty($pesan)): ?>
        <div style="
                    padding: 12px 16px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    font-size: 14px;
                    background: <?= $jenis === 'sukses' ? '#d4edda' : '#f8d7da' ?>;
                    color: <?= $jenis === 'sukses' ? '#155724' : '#721c24' ?>;
                    border-left: 4px solid <?= $jenis === 'sukses' ? '#28a745' : '#dc3545' ?>;
                ">
          <?= $pesan ?>
        </div>
      <?php endif; ?>

      <!-- Form Registrasi -->
      <form method="POST" action="" autocomplete="off">
        <div class="form-group">
          <label><i class="fa-solid fa-user"></i> Username</label>
          <input type="text" name="user_plg" maxlength="15" required
            value="<?= old('user_plg') ?>" placeholder="Maksimal 15 karakter">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-id-card"></i> NIK (16 digit)</label>
          <input type="text" name="nik" maxlength="16" pattern="\d{16}" required
            value="<?= old('nik') ?>" placeholder="Contoh: 3201xxxxxxxxxxxx">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-signature"></i> Nama Lengkap</label>
          <input type="text" name="nm_lngkp" required
            value="<?= old('nm_lngkp') ?>" placeholder="Sesuai KTP">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-phone"></i> Nomor HP</label>
          <input type="text" name="no_hp" required
            value="<?= old('no_hp') ?>" placeholder="08xxxxxxxxxx">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-location-dot"></i> Alamat Lengkap</label>
          <input type="text" name="almt_lngkp" required
            value="<?= old('almt_lngkp') ?>" placeholder="Jalan, RT/RW, Kelurahan, Kota">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Password</label>
          <input type="password" name="sandi" required placeholder="Masukkan password">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Konfirmasi Password</label>
          <input type="password" name="konfirmasi" required placeholder="Ulangi password">
        </div>

        <button type="submit" class="btn btn-gold btn-block" style="margin-top: 10px;">
          <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
        </button>
      </form>

      <p class="auth-footer">
        Sudah punya akun? <a href="<?= BASE_URL ?>penyewa/controller/tlogin_controller.php">Masuk di sini</a>
      </p>
    </div>
  </div>
</main>

<?php
// Memanggil Footer (Berisi Footer HTML, jQuery, dan Semua Script JS)
require_once __DIR__ . '/footer.php';
?>