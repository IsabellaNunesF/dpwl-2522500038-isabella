<?php
session_start();
require_once __DIR__ . '/../../konfig/koneksi.php'; // ← WAJIB: agar BASE_URL tersedia

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman katalog dengan notifikasi logout berhasil
header('Location: ' . BASE_URL . 'penyewa/controller/tmobil_controller.php?status=logout_berhasil');
exit;
