<?php
include 'db/db_conn.php';

// Contoh: ambil pesanan dengan status 'masuk'
$sql = "SELECT COUNT(*) AS total_order FROM orders WHERE status = 'masuk' AND want_design_service = 1";
$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);
echo $row['total_order'];
