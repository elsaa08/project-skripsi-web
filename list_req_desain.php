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
                                        <th>Status_design</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'get_req_desain.php'; // Include the PHP file to fetch orders

                                    // Loop through the orders and create table rows
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
                                        echo "<td class='text-bold-500'>{$order['status_design']}</td>";
                                        echo "<td><a href='detail_desain.php?id={$order['id']}'><i class='badge-circle font-medium-1' data-feather='mail'>Lihat</i></a></td>";

                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3>Belum Bayar</h3>
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
                                        <th>ID Pesanan</th>
                                        <th>Service</th>
                                        <th>Fabrics</th>
                                        <th>Status</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'get_order.php'; // Include the PHP file to fetch orders

                                    // Loop through the orders and create table rows
                                    foreach ($order_unpaid as $order) {
                                        echo "<tr>";
                                        echo "<td class='text-bold-500'>{$order['id_pesanan']}</td>";
                                        echo "<td class='text-bold-500'>{$order['services']}</td>";
                                        echo "<td class='text-bold-500'>{$order['fabrics']}</td>";
                                        echo "<td class='text-bold-500'>{$order['status']}</td>";
                                        echo "<td><a href='#'><i class='badge-circle font-medium-1' data-feather='mail'></i></a></td>";
                                        echo "</tr>";
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