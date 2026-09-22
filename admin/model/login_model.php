<?php
// admin/model/login_model.php

class LoginModel
{
  private $conn;

  // Menerima objek koneksi dari controller
  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Fungsi untuk mengambil data admin berdasarkan username
  public function getAdminByUsername($username)
  {
    // Menggunakan sintaks CRUD kustom Anda
    $hasil = $this->conn->select('t_admin', ['username' => $username]);

    // Mengembalikan 1 baris data (array) atau null jika tidak ada
    return !empty($hasil) ? $hasil[0] : null;
  }
}
