<?php

// Start session dengan aman (cek apakah session sudah aktif)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Memuat konfigurasi (untuk mendapatkan BASE_URL)
require_once __DIR__ . '/../konfig/koneksi.php';

// CEK STATUS LOGIN (INI KUNCI JAWABAN DARI PERTANYAAN ANDA)
if (isset($_SESSION['admin_username'])) {
  // Jika SUDAH login, panggil controller dashboard
  require_once __DIR__ . '/controller/dashboard_controller.php';
} else {
  // Jika BELUM login, panggil controller login
  require_once __DIR__ . '/controller/login_controller.php';
}
