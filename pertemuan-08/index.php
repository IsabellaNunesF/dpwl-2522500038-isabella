
<?php
session_start();

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', FCPATH . 'application' . DIRECTORY_SEPARATOR);
define('SYSPATH', FCPATH . 'system' . DIRECTORY_SEPARATOR);

require_once APPPATH . 'config/config.php';
require_once APPPATH . 'config/database.php';
require_once APPPATH . 'helpers/url_helper.php';
require_once SYSPATH . 'core/Controller.php';
require_once SYSPATH . 'core/Router.php';

$route = [];
require APPPATH . 'config/routes.php';

// P6: Menghilangkan index.php pada URL aplikasi
if (isset($_GET['route'])) {
  $uri = $_GET['route'];
} else {
  $requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
  ) ?? '/';

  // Menghilangkan folder project dari URI
  $basePath = str_replace(
    '\\',
    '/',
    dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')
  );

  $basePath = rtrim($basePath, '/');

  if (
    $basePath !== '' &&
    str_starts_with($requestPath, $basePath)
  ) {
    $requestPath = substr($requestPath, strlen($basePath));
  }

  // Menghilangkan index.php jika masih ada
  $requestPath = preg_replace(
    '#^/index\.php#',
    '',
    $requestPath
  );

  $uri = trim($requestPath, '/');
}

$router = new Router($route);
$router->dispatch($uri);
