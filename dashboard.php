<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

echo "Selamat datang, " . $_SESSION['nama'] . " (" . $_SESSION['role'] . ")";
echo "<br><a href='logout.php'>Logout</a>";
