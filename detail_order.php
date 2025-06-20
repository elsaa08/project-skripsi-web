<?php
include 'db/db_conn.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

require 'libs/PHPMailer/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/PHPMailer/src/SMTP.php';
require 'libs/PHPMailer/PHPMailer/src/Exception.php';

if (!isset($_GET['id'])) {
    die("ID pesanan tidak ditemukan");
}

$id_pesanan = $_GET['id'];

// Ambil data pesanan
$query = "SELECT orders.*, register.nama, register.brand_name, register.email
          FROM orders 
          JOIN register ON orders.user_id = register.user_id 
          WHERE orders.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Pesanan tidak ditemukan.");
}
$order = $result->fetch_assoc();

// Auto update status berdasarkan status_bayar
// if ($order['status_bayar'] == 0 && $order['status'] == 'Diterima') {
//     $stmt = $conn->prepare("UPDATE orders SET status = 'belum_bayar' WHERE id = ?");
//     $stmt->bind_param("i", $id_pesanan);
//     $stmt->execute();
//     $order['status'] = 'belum_bayar';
// }

if ($order['status_bayar'] == 1 && $order['status'] == 'belum_bayar') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Menunggu Konfirmasi Pembayaran' WHERE id = ?");
    $stmt->bind_param("i", $id_pesanan);
    $stmt->execute();
    $order['status'] = 'Menunggu Konfirmasi Pembayaran';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nominal'])) {
    $total = str_replace(['.', ','], '', $_POST['nominal']);
    $total = intval($total);

    if ($total > 0) {
        // Update total & status
        $updateQuery = "UPDATE orders SET total = ?, status = 'belum_bayar' WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("ii", $total, $id_pesanan);

        if ($stmtUpdate->execute()) {
            // Generate PDF tagihan
            $url = "http://192.168.18.217/dwa/receipt.php?id=" . $id_pesanan;
            $html = file_get_contents($url);
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            $pdfPath = __DIR__ . "/temp_invoice_$id_pesanan.pdf";
            file_put_contents($pdfPath, $pdfOutput);

            // Kirim email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply.projectskripsi21@gmail.com';
                $mail->Password   = 'jmkt ujqc rpua ybuq'; // App password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply.projectskripsi21@gmail.com', 'Admin Pemesanan');
                $mail->addAddress($order['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Tagihan Telah Dibuat';
                $mail->Body    = "<p>Halo,</p>
                                  <p>Tagihan Anda dengan ID #$id_pesanan telah dibuat sebesar <strong>Rp " . number_format($total, 0, ',', '.') . "</strong>.</p>
                                  <p>Silakan lakukan pembayaran melalui aplikasi kami.</p>
                                  <p>Terima kasih.</p>";
                $mail->addAttachment($pdfPath, "Invoice-DWA-$id_pesanan.pdf");
                $mail->send();
                unlink($pdfPath); // Hapus PDF setelah dikirim
            } catch (Exception $e) {
                echo "Email gagal dikirim: {$mail->ErrorInfo}";
            }

            header("Location: detail_order.php?id=" . $id_pesanan);
            exit;
        } else {
            echo "<div class='alert error'>❌ Gagal update database: {$stmtUpdate->error}</div>";
        }
    } else {
        echo "<div class='alert error'>❌ Nominal tidak valid.</div>";
    }
}
?>
<?php
include 'db/db_conn.php';

// Logika status tombol
$isAccepted = isset($_POST['terima']) && $_POST['id'] == $order['id'];
$isRejected = isset($_POST['show_tolak']) && !isset($_POST['alasan-tolak']) && $_POST['id'] == $order['id'];

$hasTotal = !empty($order['total']) && $order['total'] > 0;

// Jika tombol TERIMA diklik
if ($isAccepted) {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Diterima' WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $order['status'] = 'Diterima';
}

// Jika tombol TOLAK diklik
if (isset($_POST['show-tolak']) && $_POST['id'] == $order['id']) {
    $alasan = trim($_POST['alasan']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'Ditolak', alasan_penolakan = ? WHERE id = ?");
    $stmt->bind_param("si", $alasan, $_POST['id']);
    $stmt->execute();
    $order['status'] = 'Ditolak';
    $order['alasan_penolakan'] = $alasan;
    echo '<div style="background:#f8d7da; padding: 15px; border-radius: 8px; color: #721c24; margin-bottom: 15px;">
        Pesanan ditolak dengan alasan: <strong>' . htmlspecialchars($alasan) . '</strong>
    </div>';
}

// Jika tagihan diinput

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Jika bukti dikonfirmasi
    if (isset($_POST['konfirmasi_bukti']) && $_POST['id'] == $order['id']) {
        $stmt = $conn->prepare("UPDATE orders SET status = 'paid', status_bayar = 2 WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $order['status'] = 'paid';
        $order['status_bayar'] = 2;
    }

    // Jika bukti ditolak
    if (isset($_POST['alasan-tolak']) && $_POST['id'] == $order['id']) {
        $alasan_penolakan_bayar = trim($_POST['alasan_penolakan_bayar']);
        $stmt = $conn->prepare("UPDATE orders SET status = 'reject_paid', status_bayar = 3, alasan_penolakan_bayar = ? WHERE id = ?");
        $stmt->bind_param("si", $alasan_penolakan_bayar, $_POST['id']);
        $stmt->execute();
        $order['status'] = 'reject_paid';
        $order['status_bayar'] = 3;
        $order['alasan_penolakan_bayar'] = $alasan_penolakan_bayar;
    }
}
$stepIndexMap = [
    'masuk' =>  0,
    'Diterima' => 1,
    'belum_bayar' => 2,
    'Menunggu Pembayaran' => 3,
    'Menunggu Konfirmasi Pembayaran' => 3,
    'paid' => 4,
    'Diproses' => 5,
    'reject_paid' => 4 // tetap tunjukkan sampai step 2 (bayar), tapi tanpa lanjut
];

$currentStepIndex = isset($stepIndexMap[$order['status']]) ? $stepIndexMap[$order['status']] : 0;


?>





<!DOCTYPE html>
<html lang="id">
<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>



<style>
    html,
    body {
        height: 100%;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(rgb(184, 185, 187));
        justify-content: center;
        align-items: center;
        /* margin-bottom: 250px; */
    }

    .page-wrapper {
        /* display: flex; */
        flex-direction: column;
        min-height: 100vh;
        margin-top: 10px;
        /* margin-bottom: 1000px; */


    }

    .main-content {

        height: 100%;

        justify-content: center;
        align-items: center;
        /* margin-top: 100px; */
        margin-bottom: 100px;

    }

    .container {
        max-width: 780px;
        width: 100%;
        background: #ffffff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
    }

    .design-preview {
        width: 30%;
        height: 30%;
        border-radius: 16px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }

    .tag {
        display: inline-block;
        padding: 6px 12px;
        background-color: #e0f0ff;
        color: #007bff;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
    }

    .description {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .info-box {
        display: flex;
        justify-content: space-between;
        padding: 16px 20px;
        background: #f8f9fb;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .info-label {
        font-weight: 500;
        color: #444;
    }

    .info-value {
        font-weight: 600;
        font-size: 16px;
    }

    .form-group {
        position: relative;
        margin-bottom: 25px;
    }

    .form-group input {
        width: 100%;
        padding: 18px 16px 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background: transparent;
    }

    .form-group label {
        position: absolute;
        top: 14px;
        left: 16px;
        color: #999;
        font-size: 14px;
        transition: 0.2s ease all;
        pointer-events: none;
    }

    .form-group input:focus~label,
    .form-group input:not(:placeholder-shown)~label {
        top: 6px;
        left: 14px;
        font-size: 12px;
        color: #007bff;
        background: white;
        padding: 0 4px;
    }

    .btn {
        display: block;
        width: 100%;
        padding: 16px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        background: #007bff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn:hover {
        background: #0056b3;
    }

    .btn-conf {
        display: block;
        width: 30%;
        padding: 10px;
        font-size: 13px;
        font-weight: 600;
        color: white;
        background: #007bff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-bottom: 10px;
    }

    .btn-conf:hover {
        background: #0056b3;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-item {
        background: #f1f5f9;
        border: 1px solid #dce3eb;
        border-radius: 12px;
        padding: 16px 20px;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: #e6f0ff;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.1);
    }

    .info-label {
        font-weight: 500;
        font-size: 14px;
        color: #555;
        margin-bottom: 6px;
    }

    .info-value {
        font-weight: 600;
        font-size: 16px;
        color: #222;
    }

    .badge-method {
        display: inline-block;
        padding: 4px 10px;
        background-color: #cce5ff;
        color: #004085;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0 0 30px 0;
        border-top: 1px solid #e0e0e0;
    }

    .info-list li {
        padding: 14px 0;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .info-list .label {
        font-weight: 500;
        color: #555;
        font-size: 15px;
        flex: 1;
    }

    .info-list .value {
        font-weight: 600;
        font-size: 15px;
        color: #222;
        text-align: left;
        flex: 1;
    }

    .step-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 25px 0;
        position: relative;
    }

    .step-line {
        position: absolute;
        top: 50%;
        left: 10%;
        right: 10%;
        height: 5px;
        background: #ccc;
        z-index: 1;
    }

    .step-circle {
        z-index: 2;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #ccc;
    }

    .step-active {
        border-color: #007bff;
        color: #007bff;
    }

    .step-done {
        background: green;
        color: white;
        border-color: green;
    }

    .step-label {
        text-align: center;
        margin-top: 5px;
        font-size: 0.85rem;
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin: 30px 0;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 16px;
        left: 0;
        right: 0;
        height: 4px;
        background-color: #ddd;
        z-index: 0;
    }

    .step {
        z-index: 1;
        text-align: center;
        flex: 1;
        position: relative;
    }

    .step .circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: white;
        border: 3px solid #ccc;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #ccc;
    }

    .step.active .circle {
        border-color: #007bff;
        color: #007bff;
    }

    .step.done .circle {
        background-color: green;
        border-color: green;
        color: white;
    }

    .step .label {
        margin-top: 8px;
        font-size: 13px;
        color: #555;
    }

    .step.active .label {
        font-weight: bold;
        color: #007bff;
    }

    .step.rejected .circle {
        background-color: #dc3545;
        /* merah */
        color: white;
        font-weight: bold;
    }

    .step.rejected {
        border-color: #dc3545;
    }

    .step.rejected .label {
        color: #dc3545;
        font-weight: bold;
    }
</style>
</head>

<body>
    <div class="content transition">
        <div class="page-wrapper">
            <div class="main-content">
                <div class="container">


                    <div class="section-title">Detail Pesanan #<?= htmlspecialchars($order['id_pesanan']) ?></div>
                    <?php if ($order['has_design'] == 1): ?>
                        <a href="<?= htmlspecialchars($order['design']) ?>" target="_blank" class="tag" style="text-decoration: none;">
                            📁 Memiliki Desain
                        </a>
                    <?php elseif ($order['want_design_service'] == 1): ?>
                        <div class="tag" style="cursor: pointer;" onclick="alert('Permintaan desain diajukan oleh pengguna.')">
                            🎨 Mengajukan Permintaan Desain
                        </div>
                    <?php elseif ($order['want_design_service'] == 0): ?>
                        <div class="tag" style="cursor: pointer;" onclick="alert('Permintaan desain diajukan oleh pengguna.')">
                            ⚠️ Tidak Membutuhkan Desain
                        </div>
                    <?php endif; ?>


                    <ul class="info-list">
                        <li>
                            <span class="label">👚 Dipesan oleh:</span>
                            <span class="value"><strong><?= htmlspecialchars($order['brand_name']) ?></strong></span>
                        </li>
                        <li>
                            <span class="label">🧩 Service:</span>
                            <span class="value"><?= htmlspecialchars($order['services']) ?></span>
                        </li>
                        <li>
                            <span class="label">🧵 Jenis Kain:</span>
                            <span class="value"><?= htmlspecialchars($order['fabrics']) ?></span>
                        </li>
                        <li>
                            <span class="label">📅 Tanggal Pemesanan:</span>
                            <span class="value"><?= htmlspecialchars(date("d F Y, H:i", strtotime($order['created_at']))) ?> WIB</span>
                        </li>
                        <li>
                            <span class="label">🚚 Pengiriman:</span>
                            <span class="value">Diambil / Dikirim ke Alamat</span>
                        </li>
                        <li>
                            <span class="label">📦 Jumlah Dipesan:</span>
                            <span class="value">30 pcs</span>

                        </li>
                        <li>
                            <span class="label">📌 Status:</span>
                            <span class="value"><?= htmlspecialchars($order['status']) ?></span>
                        </li>

                        <li>
                            <span class="label">💰 Status Bayar:</span>
                            <span class="value"><?= $order['status_bayar'] == 1 ? 'Sudah Bayar' : 'Belum Bayar' ?></span>
                        </li>

                    </ul>
                    <?php
                    $design = $order['design'] ?? '';
                    if ($design) {
                        // Gabungkan URL jika bukan URL penuh
                        if (!str_starts_with($design, 'http')) {
                            $design = 'https://a945-2404-8000-1024-8ac-8d8d-6745-cd7a-56fa.ngrok-free.app/uploads/design-1749662408850-575123855.jpg' . $design;
                        }

                        $ext = strtolower(pathinfo($design, PATHINFO_EXTENSION));

                        echo '<div class="section-title" style="margin-top: 30px;">Preview Desain</div>';

                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            echo '<a href="' . htmlspecialchars($design) . '" target="_blank" title="Klik untuk perbesar">';
                            echo '<img src="' . htmlspecialchars($design) . '" class="design-preview" alt="Preview Desain">';
                            echo '</a>';
                        } elseif ($ext === 'pdf') {
                            echo '<iframe src="' . htmlspecialchars($design) . '" width="100%" height="480" style="border:1px solid #ccc; border-radius:12px;"></iframe>';
                        } else {
                            echo '<p><a href="' . htmlspecialchars($design) . '" target="_blank">Lihat Desain (file)</a></p>';
                        }
                    } else {
                        echo '<p style="color:#999;">Tidak ada desain yang diunggah.</p>';
                    }
                    ?>



                    <div class="info-box">
                        <div class="info-label">Jumlah Dipesan</div>
                        <div class="info-value">30 pcs</div>
                    </div>




                    <?php
                    $steps = ["Diterima", "Menunggu Pembayaran", "Menunggu Konfirmasi Pembayaran", "Diproses"];
                    $currentStep = array_search($order['status'], $steps);
                    ?>

                    <div class="progress-steps">

                        <!-- STEP 1 -->
                        <div class="step <?= $currentStepIndex >= 1 ? 'done' : '' ?>">
                            <div class="circle"><?= $currentStepIndex >= 1 ? '✓' : '1' ?></div>
                            <div class="label">Pesanan Diterima</div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="step <?= $currentStepIndex >= 2 ? 'done' : '' ?>">
                            <div class="circle"><?= $currentStepIndex >= 2 ? '✓' : '2' ?></div>
                            <div class="label">
                                <?= empty($order['total']) ? 'Menunggu Tagihan Dibuat' : 'Tagihan Telah Dikirim' ?>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <!-- STEP 3 -->
                        <?php
                        $isRejected2 = $order['status_bayar'] == 3;
                        $step3Class = $isRejected2 ? 'rejected' : ($currentStepIndex >= 3 ? 'done' : '');
                        $step3Icon = $isRejected2 ? '✘' : ($currentStepIndex >= 3 ? '✓' : '3');
                        ?>
                        <div class="step <?= $step3Class ?>">
                            <div class="circle"><?= $step3Icon ?></div>
                            <div class="label">Pembayaran</div>

                            <?php if ($order['status_bayar'] == 1): ?>
                                <div style="font-size: 13px; color: #555; margin-top: 5px;">
                                    ✅ Pengguna telah membayar <br>
                                    ⏳ Pembayaran belum dikonfirmasi
                                </div>
                            <?php elseif ($order['status_bayar'] == 2): ?>
                                <div style="font-size: 13px; color: green; margin-top: 5px;">
                                    ✅ Pembayaran telah dikonfirmasi
                                </div>
                            <?php elseif ($order['status_bayar'] == 3): ?>
                                <div style="font-size: 13px; color: red; margin-top: 5px;">
                                    ❌ Bukti pembayaran ditolak<br>
                                    <em>Alasan: <?= htmlspecialchars($order['alasan_penolakan_bayar'] ?? '-') ?></em>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 13px; color: #555; margin-top: 5px;">
                                    ⏳ Pengguna belum bayar
                                </div>
                            <?php endif; ?>
                        </div>


                        <!-- STEP 4 -->
                        <div class="step <?= $order['status'] == 'Diproses' ? 'active' : '' ?>">
                            <div class="circle"><?= $order['status'] == 'Diproses' ? '4' : '4' ?></div>
                            <div class="label">Proses Produksi</div>
                        </div>

                    </div>


                    <?php if ($order['status'] === 'Menunggu Konfirmasi Pembayaran') : ?>
                        <div class="section-title" style="margin-top: 30px;">Konfirmasi Bukti Pembayaran</div>

                        <div style="text-align:center; margin-bottom:20px;">
                            <img src="https://a945-2404-8000-1024-8ac-8d8d-6745-cd7a-56fa.ngrok-free.app/uploads/design-1750338865897-635863434.jpg"
                                alt="Bukti Pembayaran"
                                style="max-width:50%; border-radius:10px; border:1px solid #ccc;" />
                        </div>

                        <form method="POST" action="" style="display:flex; flex-direction:column; gap: 15px; max-width:600px; margin:auto;">

                            <input type="hidden" name="id" value="<?= $order['id'] ?>">

                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <button type="submit" name="konfirmasi_bukti" class="btn-conf" style="background-color: #28a745; flex:1;">
                                    ✅ Bukti Terkonfirmasi
                                </button>

                                <button type="button" onclick="document.getElementById('alasan-tolak').style.display='block'" class="btn-conf" style="background-color: #dc3545; flex:1;">
                                    ❌ Bukti Ditolak
                                </button>
                            </div>

                            <div id="alasan-tolak" style="display: none;">
                                <textarea name="alasan_penolakan_bayar" rows="4" placeholder="Tuliskan alasan."
                                    style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc;"></textarea>

                                <button type="submit" name="alasan-tolak" class="btn-conf" style="background-color: #dc3545; margin-top:10px;">
                                    🚫 Kirim Penolakan Bukti
                                </button>
                            </div>

                        </form>
                    <?php endif; ?>



                    <!-- Tombol Terima/Tolak -->
                    <?php if (!$hasTotal && $order['status'] !== 'Ditolak' && $order['status'] !== 'Diterima'): ?>
                        <form method="POST" action="" style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <button class="btn" type="submit" name="terima" style="background-color: #28a745;">✅ Terima</button>
                            <button class="btn" type="submit" name="show_tolak" style="background-color: #dc3545;">❌ Tolak</button>
                        </form>
                    <?php endif; ?>

                    <!-- Form Tolak -->
                    <?php if ($isRejected): ?>
                        <form method="POST" action="" style="margin-bottom: 25px;">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <div class="form-group">

                                <textarea id="alasan" name="alasan" required placeholder="Tuliskan alasan penolakan..." style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc;"></textarea>
                            </div>
                            <button class="btn" type="submit" name="tolak" style="background-color: #dc3545;">🚫 Kirim Penolakan</button>
                        </form>
                    <?php endif; ?>

                    <!-- Form Tagihan -->
                    <?php if (!empty($order['total']) && $order['total'] > 0): ?>
                        <ul class="info-list">
                            <li>
                                <span class="label">📦 Total:</span>
                                <span class="value">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                            </li>
                            <li>
                                <span class="label">📌 Status:</span>
                                <span class="value"><?= htmlspecialchars($order['status']) ?></span>
                            </li>
                        </ul>
                        <form method="GET" action="receipt.php" target="_blank">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <button class="btn" type="submit">🧾 Lihat/Print Receipt</button>
                        </form>
                    <?php elseif ($order['status'] === 'Diterima'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <div class="form-group">
                                <input type="text" id="nominal" name="nominal" placeholder=" " required oninput="formatRupiah(this)" />
                                <script>
                                    function formatRupiah(el) {
                                        let angka = el.value.replace(/[^\d]/g, "");
                                        el.value = new Intl.NumberFormat('id-ID').format(angka);
                                    }
                                </script>
                                <label for="nominal">Masukkan Nominal Tagihan (Rp)</label>
                            </div>
                            <button class="btn" type="submit">💳 Buat Tagihan</button>
                        </form>
                    <?php endif; ?>


                </div>
            </div>

        </div>
    </div>

</body>

<?php include 'footer.php'; ?>

</html>