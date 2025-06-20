<?php
session_start();
include 'db/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nip = $_POST['nip'];
    $kode_akses = $_POST['kode_akses'];
    $password = $_POST['password'];

    // Cek login di tabel MANAGER
    $stmt = $conn->prepare("SELECT manager_id, nama, password_hash FROM manager WHERE nip = ? AND no_akses = ?");
    $stmt->bind_param("ss", $nip, $kode_akses);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Cek apakah password masih pakai SHA-256
        if (hash('sha256', $password) === $row['password_hash']) {
            // 🚀 Upgrade password ke format password_hash()
            $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan password baru ke database
            $update_stmt = $conn->prepare("UPDATE manager SET password_hash = ? WHERE manager_id = ?");
            $update_stmt->bind_param("si", $new_hashed_password, $row['manager_id']);
            $update_stmt->execute();

            // Lanjutkan proses login
            $_SESSION['user_id'] = $row['manager_id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = "manager";
            header("Location: index.php");
            exit();
        }

        // Jika password sudah dalam format password_hash()
        elseif (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['manager_id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = "manager";
            header("Location: index.php");
            exit();
        }
    }


    // Cek login di tabel ADMIN
    $stmt = $conn->prepare("SELECT admin_id, nama, password_hash FROM admin WHERE nip = ? AND no_akses = ?");
    $stmt->bind_param("ss", $nip, $kode_akses);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {  // GUNAKAN password_verify()
            $_SESSION['user_id'] = $row['admin_id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = "admin";
            header("Location: index.php");
            exit();
        }
    }

    // Jika gagal login
    $_SESSION['error'] = "NIP, kode akses, atau password salah!";
    header("Location: index.php");
    exit();
}
