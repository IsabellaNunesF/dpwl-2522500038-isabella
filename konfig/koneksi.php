<?php
// konfig/koneksi.php

class Database
{
  private $pdo;

  public function __construct($host, $dbname, $username, $password)
  {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $this->pdo = new PDO($dsn, $username, $password, $options);
  }

  // 1. INSERT
  public function insert($tabel, $data)
  {
    $kolom = implode(", ", array_keys($data));
    $placeholder = implode(", ", array_fill(0, count($data), '?'));
    $sql = "INSERT INTO $tabel ($kolom) VALUES ($placeholder)";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute(array_values($data));
  }

  // 2. UPDATE
  public function update($tabel, $data, $kunci)
  {
    $set = [];
    $values = [];
    foreach ($data as $key => $val) {
      $set[] = "$key = ?";
      $values[] = $val;
    }
    $where = [];
    foreach ($kunci as $key => $val) {
      $where[] = "$key = ?";
      $values[] = $val;
    }
    $sql = "UPDATE $tabel SET " . implode(", ", $set) . " WHERE " . implode(" AND ", $where);
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($values);
  }

  // 3. DELETE
  public function delete($tabel, $kunci)
  {
    $where = [];
    $values = [];
    foreach ($kunci as $key => $val) {
      $where[] = "$key = ?";
      $values[] = $val;
    }
    $sql = "DELETE FROM $tabel WHERE " . implode(" AND ", $where);
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($values);
  }

  // 4. SELECT
  // $data berisi kondisi WHERE (array asosiatif). Jika kosong, ambil semua.
  public function select($tabel, $data = [])
  {
    $sql = "SELECT * FROM $tabel";
    $values = [];
    if (!empty($data)) {
      $where = [];
      foreach ($data as $key => $val) {
        $where[] = "$key = ?";
        $values[] = $val;
      }
      $sql .= " WHERE " . implode(" AND ", $where);
    }
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);
    return $stmt->fetchAll();
  }

  // 5. QUERY CUSTOM (Untuk SQL kompleks: COUNT, SUM, CASE, JOIN, dll)
  public function query($sql, $params = [])
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }
}

// Inisialisasi Koneksi
$host = 'localhost';
$db   = 'db_dpwl_rental'; // Sesuaikan dengan nama database Anda
$user = 'root';
$pass = '';

$conn = new Database($host, $db, $user, $pass);

// ==================== SET TIMEZONE ====================
date_default_timezone_set('Asia/Jakarta');

// Definisikan Base URL aplikasi (sesuaikan dengan folder project Anda di htdocs)
// Tanda slash di akhir WAJIB ada.
// Di konfig/koneksi.php
define('BASE_URL', 'http://localhost/contohdpwl/');
define('BASE_PATH', 'C:/laragon/www/contohdpwl/');
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
