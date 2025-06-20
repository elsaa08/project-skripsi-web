<?php
session_start();
include 'db/db_conn.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
echo "Selamat datang, " . $_SESSION['user_id'] . " (" . $_SESSION['role'] . ")";

// Pastikan user yang login adalah manager
if (!isset($_SESSION['user_id'])) {
    die("Error: Anda harus login sebagai manager untuk mendaftarkan admin.");
}

$created_by = $_SESSION['user_id']; // Ambil manager_id dari session

// Proses form saat tombol diklik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan semua field diisi
    if (empty($_POST['nama']) || empty($_POST['nip']) || empty($_POST['email']) || empty($_POST['no_akses']) || empty($_POST['password']) || empty($_POST['conf_password'])) {
        die("Error: Semua field harus diisi!");
    }

    // Ambil data dari form
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_akses = mysqli_real_escape_string($conn, $_POST['no_akses']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $conf_password = mysqli_real_escape_string($conn, $_POST['conf_password']);

    // Validasi password
    if ($password !== $conf_password) {
        die("Error: Konfirmasi password tidak cocok!");
    }

    // Enkripsi password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query insert ke tabel admin
    $query = "INSERT INTO admin (admin_id, nama, email, nip, no_akses, password_hash, created_by, created_at) 
              VALUES (UUID(), '$nama', '$email', '$nip', '$no_akses', '$hashed_password', '$created_by', NOW())";

    if (mysqli_query($conn, $query)) {
        echo "Pendaftaran berhasil! Admin telah ditambahkan.";
    } else {
        die("Gagal mendaftar: " . mysqli_error($conn));
    }
}

// Tutup koneksi database
mysqli_close($conn);
