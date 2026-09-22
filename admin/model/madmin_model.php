<?php
// admin/model/madmin_model.php
require_once __DIR__ . '/../../konfig/koneksi.php';

class MAdmin
{
  private $conn;
  private $tabel = 't_admin';

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  // Ambil semua data admin
  public function getAll()
  {
    return $this->conn->select($this->tabel);
  }

  // Cari admin berdasarkan username
  public function getByUsername($username)
  {
    return $this->conn->select($this->tabel, ['username' => $username]);
  }

  // Tambah admin baru
  public function tambah($data)
  {
    // Hash password dengan bcrypt
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    return $this->conn->insert($this->tabel, $data);
  }

  // Update admin
  public function ubah($data, $username_lama)
  {
    // Jika password diisi, hash dulu
    if (!empty($data['password'])) {
      $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    } else {
      unset($data['password']); // Jangan update password jika kosong
    }

    $kunci = ['username' => $username_lama];
    return $this->conn->update($this->tabel, $data, $kunci);
  }

  // Hapus admin
  public function hapus($username)
  {
    $kunci = ['username' => $username];
    return $this->conn->delete($this->tabel, $kunci);
  }

  // Verifikasi login
  public function verifikasi($username, $password)
  {
    $admin = $this->getByUsername($username);
    if (!empty($admin)) {
      $admin = $admin[0];
      if (password_verify($password, $admin['password'])) {
        if ($admin['status_akun'] === 'aktif') {
          return $admin;
        }
      }
    }
    return false;
  }
}
