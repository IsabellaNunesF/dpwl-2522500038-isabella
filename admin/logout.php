<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../konfig/koneksi.php';

// Hapus semua data session
session_destroy();

// Arahkan kembali ke gerbang utama admin
header('Location: ' . BASE_URL . 'admin/index.php');
exit;
