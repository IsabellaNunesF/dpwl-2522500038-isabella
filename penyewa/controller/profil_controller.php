<?php
session_start();
// ==========================================
// GUARD: Wajib login
// ==========================================
if (empty($_SESSION['login'])) {
  header('Location: ' . BASE_URL . 'penyewa/controller/tlogin_controller.php');
  exit;
}

require_once __DIR__ . '/../../konfig/koneksi.php';
require_once __DIR__ . '/../model/profil_model.php';

$model    = new ProfilModel($conn);
$user_plg = $_SESSION['user_plg'];
$pesan    = '';
$jenis    = '';

// Ambil data profil saat ini
$profil = $model->getProfil($user_plg);

// ==========================================
// PROSES UPDATE PROFIL (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $aksi = $_POST['aksi'] ?? '';

  // ---- AKSI 1: UPDATE DATA DIRI ----
  if ($aksi === 'update_data') {
    $nm_lngkp   = trim($_POST['nm_lngkp'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $almt_lngkp = trim($_POST['almt_lngkp'] ?? '');

    $error = [];
    if (empty($nm_lngkp))   $error[] = 'Nama lengkap wajib diisi.';
    if (empty($no_hp))      $error[] = 'Nomor HP wajib diisi.';
    if (empty($almt_lngkp)) $error[] = 'Alamat wajib diisi.';

    if (empty($error)) {
      $data = [
        'nm_lngkp'   => $nm_lngkp,
        'no_hp'      => $no_hp,
        'almt_lngkp' => $almt_lngkp,
      ];
      if ($model->updateProfil($user_plg, $data)) {
        $_SESSION['nm_lngkp'] = $nm_lngkp;
        $_SESSION['no_hp']    = $no_hp;
        $pesan = 'Data profil berhasil diperbarui.';
        $jenis = 'sukses';
        $profil = $model->getProfil($user_plg); // Refresh data
      } else {
        $error[] = 'Gagal memperbarui data.';
      }
    }
    if (!empty($error)) {
      $pesan = implode('<br>', $error);
      $jenis = 'error';
    }
  }
  // ---- AKSI 2: GANTI PASSWORD ----
  elseif ($aksi === 'ganti_password') {
    $sandi_lama = $_POST['sandi_lama'] ?? '';
    $sandi_baru = $_POST['sandi_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    $error = [];
    if (empty($sandi_lama)) $error[] = 'Password lama wajib diisi.';
    if (empty($sandi_baru) || strlen($sandi_baru) < 6) $error[] = 'Password baru minimal 6 karakter.';
    if ($sandi_baru !== $konfirmasi) $error[] = 'Konfirmasi password tidak cocok.';

    if (empty($error)) {
      if (!$model->cekPasswordLama($user_plg, $sandi_lama)) {
        $error[] = 'Password lama salah.';
      } else {
        $hash_baru = password_hash($sandi_baru, PASSWORD_BCRYPT);
        if ($model->updatePassword($user_plg, $hash_baru)) {
          $pesan = 'Password berhasil diubah.';
          $jenis = 'sukses';
        } else {
          $error[] = 'Gagal mengubah password.';
        }
      }
    }
    if (!empty($error)) {
      $pesan = implode('<br>', $error);
      $jenis = 'error';
    }
  }
  // ---- AKSI 3: UPLOAD FOTO KTP ----
  elseif ($aksi === 'upload_ktp' && !empty($_FILES['ft_ktp']['name'])) {
    $file = $_FILES['ft_ktp'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allow = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allow)) {
      $pesan = 'Format file harus JPG, JPEG, PNG, atau WEBP.';
      $jenis = 'error';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
      $pesan = 'Ukuran file maksimal 2 MB.';
      $jenis = 'error';
    } else {
      $nama_file = 'ft_ktp_' . time() . '_' . uniqid() . '.' . $ext;
      $tujuan    = UPLOAD_DIR . 'penyewa/' . $nama_file;

      if (!empty($profil['ft_ktp']) && file_exists(UPLOAD_DIR . 'penyewa/' . $profil['ft_ktp'])) {
        unlink(UPLOAD_DIR . 'penyewa/' . $profil['ft_ktp']);
      }

      if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        $model->updateFoto($user_plg, 'ft_ktp', $nama_file);
        $pesan = 'Foto KTP berhasil diupload.';
        $jenis = 'sukses';
        $profil = $model->getProfil($user_plg);
      } else {
        $pesan = 'Gagal mengupload file.';
        $jenis = 'error';
      }
    }
  }
  // ---- AKSI 4: UPLOAD FOTO SIM ----
  elseif ($aksi === 'upload_sim' && !empty($_FILES['ft_sim_a']['name'])) {
    $file = $_FILES['ft_sim_a'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allow = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allow)) {
      $pesan = 'Format file harus JPG, JPEG, PNG, atau WEBP.';
      $jenis = 'error';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
      $pesan = 'Ukuran file maksimal 2 MB.';
      $jenis = 'error';
    } else {
      $nama_file = 'ft_sim_a_' . time() . '_' . uniqid() . '.' . $ext;
      $tujuan    = UPLOAD_DIR . 'penyewa/' . $nama_file;

      if (!empty($profil['ft_sim_a']) && file_exists(UPLOAD_DIR . 'penyewa/' . $profil['ft_sim_a'])) {
        unlink(UPLOAD_DIR . 'penyewa/' . $profil['ft_sim_a']);
      }

      if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        $model->updateFoto($user_plg, 'ft_sim_a', $nama_file);
        $pesan = 'Foto SIM berhasil diupload.';
        $jenis = 'sukses';
        $profil = $model->getProfil($user_plg);
      } else {
        $pesan = 'Gagal mengupload file.';
        $jenis = 'error';
      }
    }
  }
}

// ==========================================
// DATA UNTUK HEADER (Mencegah error undefined variable)
// ==========================================
$judul_halaman = 'Profil Saya';
$isLoggedIn    = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userLogin     = $isLoggedIn ? [
  'user_plg' => $_SESSION['user_plg'] ?? '',
  'nm_lngkp' => $_SESSION['nm_lngkp'] ?? 'Penyewa',
] : null;
$notifikasi    = $_SESSION['notifikasi'] ?? '';
unset($_SESSION['notifikasi']);

// ==========================================
// LOAD VIEW
// ==========================================
require_once __DIR__ . '/../view/profil_view.php';
