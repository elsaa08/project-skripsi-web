<?php

require_once 'db/db_conn.php';


include "phpqrcode/qrlib.php";
if (!isset($_GET['id'])) {
    die("ID pesanan tidak ditemukan.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT orders.*, register.nama, register.brand_name, register.email FROM orders JOIN register ON orders.user_id = register.user_id WHERE orders.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data pesanan tidak ditemukan.");
}

$order = $result->fetch_assoc();
$penyimpanan = "temp/";
if (!file_exists($penyimpanan))
    mkdir($penyimpanan);
// Buat link status pesanan
$barcode_data = 'http://192.168.1.5/dwa/status_order.php?id=' . $order['id'];

QRcode::png($barcode_data, $penyimpanan . "qrcode_saya.png");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['id_pesanan'] ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 40px;
            position: relative;
        }

        .print-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #1e88e5;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .print-btn:hover {
            background-color: #1565c0;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top .logo {
            font-weight: bold;
            font-size: 20px;
        }

        .top .invoice-info {
            text-align: right;
        }

        .section {
            margin: 30px 0;
        }

        .section-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th {
            background: linear-gradient(to right, #157f8f, #4ac0b6);
            color: white;
            padding: 12px;
            text-align: left;
            border-radius: 5px 5px 0 0;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .summary {
            margin-top: 30px;
            float: right;
            text-align: right;
        }

        .summary p {
            margin: 5px 0;
        }

        .notes {
            margin-top: 50px;
            font-size: 13px;
            color: #666;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }

        .highlight {
            font-weight: bold;
            font-size: 16px;
            color: #222;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    <button class="print-btn" onclick="window.print()">🖨️ Print</button>

    <div class="invoice-box">
        <div class="top">
            <div class="logo">🧾 DWA FASHION</div>
            <div class="invoice-info">
                <p><strong>INVOICE</strong></p>
                <p>No: <?= htmlspecialchars($order['id_pesanan']) ?></p>
                <p>Tanggal: <?= date("d/m/Y", strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <div class="section">
            <div class="section-title">PAYABLE TO</div>
            <p><?= htmlspecialchars($order['nama']) ?><br>
                <?= htmlspecialchars($order['brand_name']) ?><br>
                <?= htmlspecialchars($order['email']) ?>
            </p>
        </div>

        <div class="section">
            <div class="section-title">BANK DETAILS</div>
            <p>BCA - DWA FASHION<br>
                0123 4567 8901 2345</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ITEM DESCRIPTION</th>
                    <th>QTY</th>
                    <th>PRICE</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Paket Produksi</td>
                    <td>1</td>
                    <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                </tr>
                <!-- Tambahkan item lain jika kamu punya detailnya -->
            </tbody>
        </table>

        <div class="summary">
            <p>SUB TOTAL: Rp <?= number_format($order['total'], 0, ',', '.') ?></p>
            <p>PAJAK (10%): Rp <?= number_format($order['total'] * 0.1, 0, ',', '.') ?></p>
            <p class="highlight">GRAND TOTAL: Rp <?= number_format($order['total'] * 1.1, 0, ',', '.') ?></p>
        </div>

        <div class="notes">
            <strong>NOTES:</strong><br>
            Terima kasih telah melakukan pemesanan. Kami berkomitmen untuk memberikan layanan terbaik dan produk berkualitas.
        </div>

        <div class="footer">
            www.dwafashion.com | 0812-3456-7890 | admin@dwafashion.com
        </div>
    </div>

</body>

</html>