<?php
include 'db/db_conn.php';

// Contoh: ambil pesanan dengan status 'masuk'


$sql_select = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status, orders.status_design, orders.created_at,
           register.nama, register.brand_name
    FROM orders
    JOIN register ON orders.user_id = register.user_id
    WHERE orders.status = 'masuk' AND orders.want_design_service = 1
";

$result2 = $conn->query($sql_select);
$orders = [];
if ($result2 && $result2->num_rows > 0) {
    // Fetch all orders into an associative array
    while ($row = $result2->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    $orders = [];
}

$sql = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status,orders.created_at,
           register.nama, register.brand_name
    FROM orders
    JOIN register ON orders.user_id = register.user_id
    WHERE orders.status = 'belum_bayar' AND orders.want_design_service = 1
";
$result = $conn->query($sql);
$order_unpaid = [];
if ($result && $result->num_rows > 0) {
    // Fetch all orders into an associative array
    while ($row = $result->fetch_assoc()) {
        $order_unpaid[] = $row;
    }
} else {
    $order_unpaid = [];
}


$conn->close();
