<?php
include 'db/db_conn.php';

// Contoh: ambil pesanan dengan status 'masuk'


$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';


// Query dasar
$sql_base = "FROM orders 
             JOIN register ON orders.user_id = register.user_id 
             WHERE 1=1";

// Tambahkan filter pencarian
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $sql_base .= " AND (register.nama LIKE '%$safe_search%' OR register.brand_name LIKE '%$safe_search%')";
}

// Tambahkan filter status
if (!empty($status_filter)) {
    $safe_status = $conn->real_escape_string($status_filter);
    $sql_base .= " AND orders.status = '$safe_status'";
}

// Hitung total data
$total_result = $conn->query("SELECT COUNT(*) AS total " . $sql_base);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil data orders sesuai page
$sql_select = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status, orders.created_at,
                      register.nama, register.brand_name, register.brand_status
                     
               $sql_base
               ORDER BY orders.created_at DESC
               LIMIT $limit OFFSET $offset  ";

$result_select = $conn->query($sql_select);
$orders = [];
if ($result_select && $result_select->num_rows > 0) {
    while ($row = $result_select->fetch_assoc()) {
        $orders[] = $row;
    }
}

$sql2 = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status, orders.created_at,
               register.nama, register.brand_name
               FROM orders
               JOIN register ON orders.user_id = register.user_id
               WHERE orders.status = 'Diterima'";
$result2 = $conn->query($sql2);
$order_unreceipt = [];
if ($result2 && $result2->num_rows > 0) {
    // Fetch all orders into an associative array
    while ($row = $result2->fetch_assoc()) {
        $order_unreceipt[] = $row;
    }
} else {
    $order_unreceipt = [];
}

$sql = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status, orders.created_at,
               register.nama, register.brand_name
               FROM orders
               JOIN register ON orders.user_id = register.user_id
               WHERE orders.status_bayar = 1 ";
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

$sql_reject = "SELECT orders.id, orders.id_pesanan, orders.services, orders.fabrics, orders.status, orders.created_at,
               register.nama, register.brand_name
               FROM orders
               JOIN register ON orders.user_id = register.user_id
               WHERE orders.status = 'ditolak'";
$result_reject = $conn->query($sql_reject);
$order_reject = [];
if ($result_reject && $result_reject->num_rows > 0) {
    // Fetch all orders into an associative array
    while ($row = $result_reject->fetch_assoc()) {
        $order_reject[] = $row;
    }
} else {
    $order_reject = [];
}


$conn->close();
