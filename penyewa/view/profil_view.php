<?php
// Memanggil Header (Berisi DOCTYPE, Navigasi, Loader, dll)
require_once __DIR__ . '/header.php';
?>

<!-- Konten Halaman Profil -->
<main class="page-wrap">
  <div class="container">
    <div class="section-head" style="margin-bottom: 30px;">
      <div>
        <span class="eyebrow">Akun Saya</span>
        <h2 class="section-title">Profil Penyewa</h2>
      </div>
    </div>

    <!-- Notifikasi Error / Sukses dari Controller -->
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

    <div class="profil-wrapper">
      <!-- Sidebar Profil -->
      <aside class="profil-sidebar">
        <div class="profil-card">
          <div class="profil-avatar">
            <?= strtoupper(substr($profil['nm_lngkp'] ?? 'P', 0, 1)) ?>
          </div>
          <h3><?= htmlspecialchars($profil['nm_lngkp'] ?? 'Penyewa') ?></h3>
          <p class="username">@<?= htmlspecialchars($profil['user_plg']) ?></p>

          <?php if (!empty($profil['ft_ktp']) && !empty($profil['ft_sim_a'])): ?>
            <span class="badge-verified"><i class="fa-solid fa-circle-check"></i> Terverifikasi</span>
          <?php else: ?>
            <span class="badge-pending"><i class="fa-solid fa-clock"></i> Belum Verifikasi</span>
          <?php endif; ?>
        </div>

        <nav class="profil-menu">
          <a href="#data-diri" class="menu-item active" data-target="data-diri">
            <i class="fa-solid fa-user"></i> Data Diri
          </a>
          <a href="#keamanan" class="menu-item" data-target="keamanan">
            <i class="fa-solid fa-shield-halved"></i> Keamanan
          </a>
          <a href="#dokumen" class="menu-item" data-target="dokumen">
            <i class="fa-solid fa-id-card"></i> Dokumen
          </a>
        </nav>
      </aside>

      <!-- Konten Profil -->
      <div class="profil-content">

        <!-- Section 1: Data Diri -->
        <div id="data-diri" class="profil-section active">
          <h2>Data Diri</h2>
          <p class="section-sub">Informasi pribadi Anda yang terdaftar di sistem.</p>

          <form method="POST" action="">
            <input type="hidden" name="aksi" value="update_data">
            <div class="form-row-2">
              <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($profil['user_plg']) ?>" disabled style="background: var(--gray-200); cursor: not-allowed;">
                <small class="form-hint">Username tidak dapat diubah.</small>
              </div>
              <div class="form-group">
                <label>NIK</label>
                <input type="text" value="<?= htmlspecialchars($profil['nik']) ?>" disabled style="background: var(--gray-200); cursor: not-allowed;">
                <small class="form-hint">NIK tidak dapat diubah.</small>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="nm_lngkp" value="<?= htmlspecialchars($profil['nm_lngkp']) ?>" required>
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Nomor HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($profil['no_hp']) ?>" required>
              </div>
              <div class="form-group">
                <label>Alamat Lengkap</label>
                <input type="text" name="almt_lngkp" value="<?= htmlspecialchars($profil['almt_lngkp']) ?>" required>
              </div>
            </div>
            <button type="submit" class="btn btn-gold" style="margin-top: 10px;">
              <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
          </form>
        </div>

        <!-- Section 2: Keamanan -->
        <div id="keamanan" class="profil-section">
          <h2>Keamanan Akun</h2>
          <p class="section-sub">Ganti password secara berkala untuk menjaga keamanan akun Anda.</p>

          <form method="POST" action="">
            <input type="hidden" name="aksi" value="ganti_password">
            <div class="form-group">
              <label>Password Lama</label>
              <input type="password" name="sandi_lama" required placeholder="Masukkan password lama">
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="sandi_baru" required placeholder="Minimal 6 karakter">
              </div>
              <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi" required placeholder="Ulangi password baru">
              </div>
            </div>
            <button type="submit" class="btn btn-gold" style="margin-top: 10px;">
              <i class="fa-solid fa-key"></i> Ubah Password
            </button>
          </form>
        </div>

        <!-- Section 3: Dokumen -->
        <div id="dokumen" class="profil-section">
          <h2>Dokumen Identitas</h2>
          <p class="section-sub">Upload foto KTP dan SIM untuk verifikasi akun Anda.</p>

          <div class="dokumen-grid">
            <!-- Upload KTP -->
            <div class="dokumen-card">
              <div class="dokumen-preview">
                <?php if (!empty($profil['ft_ktp'])): ?>
                  <img src="<?= BASE_URL ?>uploads/penyewa/<?= $profil['ft_ktp'] ?>" alt="Foto KTP">
                <?php else: ?>
                  <div class="dokumen-placeholder">
                    <i class="fa-solid fa-id-card"></i>
                    <p>Belum ada foto KTP</p>
                  </div>
                <?php endif; ?>
              </div>
              <h4>Foto KTP</h4>
              <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="upload_ktp">
                <input type="file" name="ft_ktp" class="file-input" accept="image/*" required>
                <button type="submit" class="btn btn-outline btn-sm btn-block">
                  <i class="fa-solid fa-upload"></i> Upload KTP
                </button>
              </form>
            </div>

            <!-- Upload SIM -->
            <div class="dokumen-card">
              <div class="dokumen-preview">
                <?php if (!empty($profil['ft_sim_a'])): ?>
                  <img src="<?= BASE_URL ?>uploads/penyewa/<?= $profil['ft_sim_a'] ?>" alt="Foto SIM">
                <?php else: ?>
                  <div class="dokumen-placeholder">
                    <i class="fa-solid fa-id-card"></i>
                    <p>Belum ada foto SIM</p>
                  </div>
                <?php endif; ?>
              </div>
              <h4>Foto SIM A</h4>
              <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="upload_sim">
                <input type="file" name="ft_sim_a" class="file-input" accept="image/*" required>
                <button type="submit" class="btn btn-outline btn-sm btn-block">
                  <i class="fa-solid fa-upload"></i> Upload SIM
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<?php
// Memanggil Footer (Berisi Footer HTML, jQuery, dan Semua Script JS)
require_once __DIR__ . '/footer.php';
?>