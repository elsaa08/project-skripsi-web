<?php
$host = "192.168.214.176"; // IP server MySQL
$user = "root";  // Ganti dengan username MySQL yang sesuai
$pass = "";  // Isi dengan password MySQL (jika ada)
$dbname = "kuliah"; // Sesuai dengan nama database

$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
