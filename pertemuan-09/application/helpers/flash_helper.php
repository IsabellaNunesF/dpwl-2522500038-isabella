
<?php

if (!function_exists('set_flash_message')) {
  function set_flash_message(string $type, string $message): void
  {
    $_SESSION['_flash'] = [
      'type' => $type,
      'message' => $message
    ];
  }
}

if (!function_exists('get_flash_message')) {
  function get_flash_message(): ?array
  {
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);

    return is_array($flash) ? $flash : null;
  }
}
