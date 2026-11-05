
<?php

if (!function_exists('is_admin_logged_in')) {
  function is_admin_logged_in(): bool
  {
    return isset($_SESSION['admin_username'])
      && ($_SESSION['admin_status'] ?? '') === 'aktif';
  }
}

if (!function_exists('require_admin_login')) {
  function require_admin_login(): void
  {
    if (!is_admin_logged_in()) {
      header('Location: ' . site_url('auth/login'));
      exit;
    }
  }
}
