<!doctype html>
<html lang="en">

<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>


<!--Content Start-->
<div class="content transition">
    <div class="container-fluid dashboard">
        <h3>List Order</h3>

        <div class="row">


            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Orders List</h4>
                    </div>
                    <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom: 15px;">
                        <input type="text" name="search" placeholder="Cari nama / brand..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control" style="max-width:200px;">

                        <select name="status" class="form-control" style="max-width:180px;">
                            <option value="">-- Semua Status --</option>
                            <option value="masuk" <?= (isset($_GET['status']) && trim($_GET['status']) === 'masuk') ? 'selected' : '' ?>>Masuk</option>
                            <option value="belum_bayar" <?= (isset($_GET['status']) && trim($_GET['status']) === 'belum_bayar') ? 'selected' : '' ?>>Belum Bayar</option>
                            <option value="Diterima" <?= (isset($_GET['status']) && trim($_GET['status']) === 'Diterima') ? 'selected' : '' ?>>Diterima</option>
                            <option value="Ditolak" <?= (isset($_GET['status']) && trim($_GET['status']) === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                            <option value="Menunggu Konfirmasi Pembayaran" <?= (isset($_GET['status']) && trim($_GET['status']) === 'Menunggu Konfirmasi Pembayaran') ? 'selected' : '' ?>>Konfirmasi Bukti Pembayaran</option>
                            <option value="paid" <?= (isset($_GET['status']) && trim($_GET['status']) === 'paid') ? 'selected' : '' ?>>Pembayaran Selesai</option>
                            <!-- Tambahkan sesuai status lain jika ada -->
                        </select>

                        <button class="btn btn-primary">🔍 Cari</button>
                    </form>

                    <?php
                    // Debugging: Tampilkan nilai status yang diterima
                    if (isset($_GET['status'])) {
                        echo "<p>Status yang dipilih: " . htmlspecialchars($_GET['status']) . "</p>";
                    }
                    ?>

                    <div class="card-content">
                        <div class="card-body">
                            <p class="card-text">List of orders from the database.</p>
                        </div>
                        <!-- table strip dark -->
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal Pemesanan</th>
                                        <th>ID Pesanan</th>
                                        <th>Brand/Pemesan</th>
                                        <th>Service</th>
                                        <th>Jenis Kain</th>
                                        <th>Status</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'get_order.php'; // Include the PHP file to fetch orders

                                    // Loop through the orders and create table rows
                                    if (empty($orders)) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada pesanan masuk.</td></tr>";
                                    } else {
                                        foreach ($orders as $order) {
                                            echo "<tr>";
                                            echo "<td class='text-bold-500'>" . date("d-m-Y H:i:s", strtotime($order['created_at'])) . "</td>";
                                            echo "<td class='text-bold-500'>{$order['id_pesanan']}</td>";
                                            if (isset($order['brand_status']) && $order['brand_status'] == 0) {
                                                echo "<td class='text-bold-500'>{$order['nama']}</td>";
                                            } else {
                                                echo "<td class='text-bold-500'>{$order['brand_name']}</td>";
                                            }
                                            echo "<td class='text-bold-500'>{$order['services']}</td>";
                                            echo "<td class='text-bold-500'>{$order['fabrics']}</td>";
                                            echo "<td class='text-bold-500'>{$order['status']}</td>";
                                            echo "<td><a href='detail_order.php?id={$order['id']}'><i class='badge-circle font-medium-1' data-feather='mail'>Lihat</i></a></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">Menampilkan halaman <?= $page ?> dari <?= $total_pages ?></div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">⟵ Prev</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next ⟶</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>


            </div>

        </div>

        <h3>Belum dibuat Tagihan</h3>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Orders List</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <p class="card-text">List of orders from the database.</p>
                        </div>
                        <!-- table strip dark -->
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal Pemesanan</th>
                                        <th>ID Pesanan</th>
                                        <th>Brand/Pemesan</th>
                                        <th>Service</th>
                                        <th>Jenis Kain</th>
                                        <th>Status</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'get_order.php'; // Include the PHP file to fetch orders

                                    // Loop through the orders and create table rows
                                    if (empty($order_unreceipt)) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada pesanan masuk.</td></tr>";
                                    } else {
                                        foreach ($order_unreceipt as $order) {
                                            echo "<tr>";
                                            echo "<td class='text-bold-500'>" . date("d-m-Y H:i:s", strtotime($order['created_at'])) . "</td>";
                                            echo "<td class='text-bold-500'>{$order['id_pesanan']}</td>";
                                            if (isset($order['brand_status']) && $order['brand_status'] == 0) {
                                                echo "<td class='text-bold-500'>{$order['nama']}</td>";
                                            } else {
                                                echo "<td class='text-bold-500'>{$order['brand_name']}</td>";
                                            }
                                            echo "<td class='text-bold-500'>{$order['services']}</td>";
                                            echo "<td class='text-bold-500'>{$order['fabrics']}</td>";
                                            echo "<td class='text-bold-500'>{$order['status']}</td>";
                                            echo "<td><a href='detail_order.php?id={$order['id']}'><i class='badge-circle font-medium-1' data-feather='mail'>Lihat</i></a></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <h3>Ditolak</h3>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Orders List</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <p class="card-text">List of orders from the database.</p>
                        </div>
                        <!-- table strip dark -->
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal Pemesanan</th>
                                        <th>ID Pesanan</th>
                                        <th>Brand/Pemesan</th>
                                        <th>Service</th>
                                        <th>Jenis Kain</th>
                                        <th>Status</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'get_order.php'; // Include the PHP file to fetch orders

                                    // Loop through the orders and create table rows
                                    if (empty($order_reject)) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada pesanan masuk.</td></tr>";
                                    } else {
                                        foreach ($order_reject as $order) {
                                            echo "<tr>";
                                            echo "<td class='text-bold-500'>" . date("d-m-Y H:i:s", strtotime($order['created_at'])) . "</td>";
                                            echo "<td class='text-bold-500'>{$order['id_pesanan']}</td>";
                                            if (isset($order['brand_status']) && $order['brand_status'] == 0) {
                                                echo "<td class='text-bold-500'>{$order['nama']}</td>";
                                            } else {
                                                echo "<td class='text-bold-500'>{$order['brand_name']}</td>";
                                            }
                                            echo "<td class='text-bold-500'>{$order['services']}</td>";
                                            echo "<td class='text-bold-500'>{$order['fabrics']}</td>";
                                            echo "<td class='text-bold-500'>{$order['status']}</td>";
                                            echo "<td><a href='detail_order.php?id={$order['id']}'><i class='badge-circle font-medium-1' data-feather='mail'>Lihat</i></a></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>
</div>

<?php include 'footer.php'; ?>

</html>