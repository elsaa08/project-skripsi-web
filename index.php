<?php
include 'db/db_conn.php';
// Ambil data trend
$query = "
    SELECT 
        CASE 
            WHEN r.brand_status = 1 THEN r.brand_name
            ELSE r.nama
        END AS pemesan,
        COUNT(o.id) AS total_order,
        (SELECT COUNT(*) FROM orders) AS total_semua
    FROM orders o
    JOIN register r ON o.user_id = r.user_id
    GROUP BY pemesan
    ORDER BY total_order DESC
";

$result = $conn->query($query);

$trendData = [];
while ($row = $result->fetch_assoc()) {
	$persen = ($row['total_order'] / $row['total_semua']) * 100;
	$trendData[] = [
		'pemesan' => $row['pemesan'],
		'total_order' => $row['total_order'],
		'persen' => round($persen, 2)
	];
}

$queryKain = "
    SELECT fabrics, COUNT(*) as jumlah 
    FROM orders 
    GROUP BY fabrics 
    ORDER BY jumlah DESC 
    LIMIT 5
";
$resKain = $conn->query($queryKain);
$kainFavorit = [];
while ($row = $resKain->fetch_assoc()) {
	$kainFavorit[] = $row;
}

$queryBulan = "
    SELECT DATE_FORMAT(created_at, '%M %Y') AS bulan, COUNT(*) AS jumlah 
    FROM orders 
    GROUP BY bulan 
    ORDER BY MIN(created_at) DESC 
    LIMIT 6
";
$resBulan = $conn->query($queryBulan);
$statBulan = [];
while ($row = $resBulan->fetch_assoc()) {
	$statBulan[] = $row;
}

$queryStatus = "
    SELECT status, COUNT(*) AS jumlah 
    FROM orders 
    GROUP BY status
";
$resStatus = $conn->query($queryStatus);
$statStatus = [];
while ($row = $resStatus->fetch_assoc()) {
	$statStatus[] = $row;
}



?>

<!doctype html>
<html lang="en">

<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!--Content Start-->
<div class="content transition">
	<div class="container-fluid dashboard">
		<h3>Dashboard</h3>

		<div class="row">

			<div class="col-md-6 col-lg-3">
				<a href="list_order.php" style="text-decoration: none; color: inherit;">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-4 d-flex align-items-center">
									<i class="las la-inbox icon-home bg-primary text-light"></i>
								</div>
								<div class="col-8">
									<p>Pesanan Masuk</p>
									<h5> <?php
											include 'get_order_count.php'; // akan echo langsung jumlah order
											?></h5>
								</div>
							</div>
						</div>
					</div>
				</a>
			</div>

			<div class="col-md-6 col-lg-3">
				<a href="list_req_desain.php" style="text-decoration: none; color: inherit;">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-4 d-flex align-items-center">
									<i class="las la-receipt icon-home bg-success text-light"></i>
								</div>
								<div class="col-8">
									<p>Cek Pembayaran</p>
									<h5>3000</h5>
								</div>
							</div>
						</div>
					</div>
				</a>
			</div>

			<div class="col-md-6 col-lg-3">
				<a href="list_req_desain.php" style="text-decoration: none; color: inherit;">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-4 d-flex align-items-center">
									<i class="las la-palette  icon-home bg-info text-light"></i>
								</div>
								<div class="col-8">
									<p>Permintaan Desain</p>
									<h5> <?php
											include 'get_req_desain_count.php'; // akan echo langsung jumlah order
											?></h5>
								</div>
							</div>
						</div>
					</div>
				</a>
			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-4 d-flex align-items-center">
								<i class="las la-brush  icon-home bg-warning text-light"></i>
							</div>
							<div class="col-8">
								<p>Pesanan Proses Desain</p>
								<h5>256</h5>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-3">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-4 d-flex align-items-center">
								<i class="las la-ban  icon-home bg-warning text-light"></i>
							</div>
							<div class="col-8">
								<p>Pesanan Ditolak</p>
								<h5>256</h5>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-4 d-flex align-items-center">
								<i class="las la-print  icon-home bg-warning text-light"></i>
							</div>
							<div class="col-8">
								<p>Pesanan Diproses</p>
								<h5>256</h5>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-4 d-flex align-items-center">
								<i class="las la-search-minus icon-home bg-warning text-light"></i>
							</div>
							<div class="col-8">
								<p>Pesanan Quality Check</p>
								<h5>256</h5>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-4 d-flex align-items-center">
								<i class="las la-clipboard-check  icon-home bg-warning text-light"></i>
							</div>
							<div class="col-8">
								<p>Pesanan Selesai</p>
								<h5>256</h5>
							</div>
						</div>
					</div>
				</div>

			</div>



			<div class="card mt-4">
				<h5 class="card-header">Trend Pemesanan</h5>
				<div class="card-body">
					<?php foreach ($trendData as $item): ?>
						<div class="row mb-1">
							<div class="col-6"><?= htmlspecialchars($item['pemesan']) ?></div>
							<div class="col-6 text-right"><?= $item['persen'] ?>%</div>
						</div>
						<div class="progress mb-3">
							<div class="progress-bar bg-primary" role="progressbar" style="width: <?= $item['persen'] ?>%"
								aria-valuenow="<?= $item['persen'] ?>" aria-valuemin="0" aria-valuemax="100">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="col-sm-12 col-md-6 col-lg-3 mb-4">

				<div class="card mt-4">
					<h5 class="card-header">Jenis Kain Terfavorit</h5>
					<div class="card-body">
						<?php foreach ($kainFavorit as $kain): ?>
							<div class="row mb-1">
								<div class="col-6"><?= htmlspecialchars($kain['fabrics']) ?></div>
								<div class="col-6 text-right"><?= $kain['jumlah'] ?> order</div>
							</div>
							<div class="progress mb-3">
								<div class="progress-bar bg-success" role="progressbar" style="width: <?= $kain['jumlah'] * 10 ?>%"
									aria-valuenow="<?= $kain['jumlah'] ?>" aria-valuemin="0" aria-valuemax="100">
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-md-6 col-lg-3 mb-4">

				<div class="card mt-4">
					<h5 class="card-header">Statistik Pemesanan Bulanan</h5>
					<div class="card-body">
						<?php foreach ($statBulan as $bulan): ?>
							<div class="row mb-1">
								<div class="col-6"><?= $bulan['bulan'] ?></div>
								<div class="col-6 text-right"><?= $bulan['jumlah'] ?> order</div>
							</div>
							<div class="progress mb-3">
								<div class="progress-bar bg-warning" role="progressbar" style="width: <?= $bulan['jumlah'] * 10 ?>%"
									aria-valuenow="<?= $bulan['jumlah'] ?>" aria-valuemin="0" aria-valuemax="100">
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-md-6 col-lg-3 mb-4">

				<div class="card mt-4">
					<h5 class="card-header">Distribusi Status Pesanan</h5>
					<div class="card-body">
						<?php foreach ($statStatus as $status): ?>
							<div class="row mb-1">
								<div class="col-6"><?= ucfirst($status['status']) ?></div>
								<div class="col-6 text-right"><?= $status['jumlah'] ?> order</div>
							</div>
							<div class="progress mb-3">
								<div class="progress-bar bg-info" role="progressbar" style="width: <?= $status['jumlah'] * 5 ?>%"
									aria-valuenow="<?= $status['jumlah'] ?>" aria-valuemin="0" aria-valuemax="100">
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>






		</div>
	</div>
</div>

</div>

</div>

<?php include 'footer.php'; ?>

</html>