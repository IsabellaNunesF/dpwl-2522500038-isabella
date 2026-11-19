<?php

class Controller
{
  protected function view(string $view, array $data = []): void
  {
    $view = str_replace('\\', '/', trim($view, '/'));

    $file = APPPATH . 'views/' . $view . '.php';

    if (!is_file($file)) {
      http_response_code(500);

      exit('View tidak ditemukan: ' .
        htmlspecialchars($file, ENT_QUOTES, 'UTF-8'));
    }

    extract($data, EXTR_SKIP);

    require $file;
  }
}
