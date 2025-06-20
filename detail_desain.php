<?php
include 'db/db_conn.php';
require 'vendor/autoload.php'; // ← ini betul setelah pakai Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/PHPMailer/src/SMTP.php';
require 'libs/PHPMailer/PHPMailer/src/Exception.php';



use Dompdf\Dompdf;


if (isset($_GET['id'])) {
    $id_pesanan = $_GET['id'];

    // Query gabung dengan register
    $query = "SELECT orders.*, 
    register.nama, 
    register.brand_name
              FROM orders 
              JOIN register ON orders.user_id = register.user_id 
              WHERE orders.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pesanan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        echo "Order tidak ditemukan.";
        exit;
    }
} else {
    echo "ID pesanan tidak valid.";
    exit;
}
$designJson = $order['cd_reference_file'] ?? '';
$designList = [];

if ($designJson) {
    $decoded = json_decode($designJson, true);

    // Jika hasilnya masih string JSON, decode lagi
    if (is_string($decoded)) {
        $decoded = json_decode($decoded, true);
    }

    if (is_array($decoded)) {
        $designList = $decoded;
    } else {
        $designList[] = $designJson;
    }
}

?>
<?php
if (isset($_GET['id'])) {
    $id_pesanan = $_GET['id'];
} else {
    die("ID pesanan tidak ditemukan");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nominal'])) {
    $total = str_replace(['.', ','], '', $_POST['nominal']);

    $update = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
    if (!$update) {
        die("Prepare gagal: " . $conn->error);
    }

    $update->bind_param("di", $total, $id_pesanan);
    if ($update->execute()) {
        // Ambil email pelanggan
        $stmt = $conn->prepare("SELECT register.email FROM orders JOIN register ON orders.user_id = register.user_id WHERE orders.id = ?");
        $stmt->bind_param("i", $id_pesanan);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $email_pelanggan = $row['email'];
            $no_pesanan = $row['id_pesanan'];
            // Kirim email via PHPMailer

            $url = "http://192.168.18.217/dwa/receipt.php?id=" . $id_pesanan; // Ganti path ini
            $html = file_get_contents($url);
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            $pdfPath = __DIR__ . "/temp_invoice_$id_pesanan.pdf";
            file_put_contents($pdfPath, $pdfOutput);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply.projectskripsi21@gmail.com'; // Ganti
                $mail->Password   = 'jmkt ujqc rpua ybuq'; // Ganti dengan App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply.projectskripsi21@gmail.com', 'Admin Pemesanan');
                $mail->addAddress($email_pelanggan);
                $mail->isHTML(true);
                $mail->Subject = 'Tagihan Telah Dibuat';
                $mail->Body    = "<p>Halo,</p><p>Tagihan Anda dengan ID #$id_pesanan telah dibuat sebesar <strong>Rp " . number_format($total, 0, ',', '.') . "</strong>.</p><p>Silakan lakukan pembayaran melalui aplikasi kami.</p><p>Terima kasih.</p>";
                $mail->addAttachment($pdfPath, "Invoice-DWA-$id_pesanan.pdf");
                $mail->send();
                unlink($pdfPath);
            } catch (Exception $e) {
                echo "Email gagal dikirim: {$mail->ErrorInfo}";
            }
        }

        header("Location: detail_order.php?id=" . $id_pesanan);
        exit;
    }
}

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
        width: 50%;
        height: 50%;
        border-radius: 16px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);

    }

    .design-previewd-container {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 25px;
        justify-content: flex-start;
    }

    .design-previewd {
        width: calc(25% - 12px);
        /* 4 gambar per baris, dikurangi gap */
        aspect-ratio: 1/1;
        border-radius: 16px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
                        <?php if (!empty($designList)): ?>
                            <div class="section-title" style="margin-top: 30px;">Preview Desain</div>
                            <div class="design-preview-container">
                                <?php foreach ($designList as $design): ?>
                                    <?php
                                    if (!str_starts_with($design, 'http')) {
                                        $design = 'https://a945-2404-8000-1024-8ac-8d8d-6745-cd7a-56fa.ngrok-free.app/uploads/' . $design;
                                    }
                                    $ext = strtolower(pathinfo($design, PATHINFO_EXTENSION));
                                    ?>

                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                        <a href="<?= htmlspecialchars($design) ?>" target="_blank" title="Klik untuk perbesar">
                                            <img src="<?= htmlspecialchars($design) ?>" class="design-previewd" alt="Preview Desain">
                                        </a>
                                    <?php elseif ($ext === 'pdf'): ?>
                                        <a href="<?= htmlspecialchars($design) ?>" target="_blank">
                                            <div class="design-previewd" style="display:flex;align-items:center;justify-content:center;background:#eee;">
                                                📄 PDF File
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <p><a href="<?= htmlspecialchars($design) ?>" target="_blank">Lihat Desain (file)</a></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <li>
                            <span class="label">🚚 Logo URL:</span>
                            <span class="value">
                                <ul>
                                    <?php
                                    $logoUrls = json_decode($order['url_design'] ?? '[""]', true);
                                    if (is_array($logoUrls) && !empty($logoUrls)) {
                                        foreach ($logoUrls as $url) {
                                            $safeUrl = htmlspecialchars($url);
                                            echo "<li><a href=\"$safeUrl\" target=\"_blank\">$safeUrl</a></li>";
                                        }
                                    } else {
                                        echo '<li>-</li>';
                                    }
                                    ?>
                                </ul>
                            </span>
                        </li>

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
                            <span class="label">🚚 Konsep:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_concept']) ?></span>
                        </li>
                        <li>
                            <span class="label">🚚 Tema</span>
                            <span class="value"><?= htmlspecialchars($order['cd_theme']) ?></span>
                        </li>
                        <li>
                            <span class="label">🚚 Tema Lainnya:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_another_theme']) ?></span>
                        </li>
                        <li>
                            <span class="label">🚚 Logo teks:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_logo_teks']) ?></span>
                        </li>
                        <!-- <li>
                            <span class="label">🚚 Logo:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_logo_file']) ?></span>
                        </li> -->
                        <li>
                            <span class="label">🚚 Logo URL:</span>
                            <span class="value">
                                <ul>
                                    <?php
                                    $logoUrls = json_decode($order['cd_logo_url'] ?? '[]', true);
                                    if (is_array($logoUrls) && !empty($logoUrls)) {
                                        foreach ($logoUrls as $url) {
                                            $safeUrl = htmlspecialchars($url);
                                            echo "<li><a href=\"$safeUrl\" target=\"_blank\">$safeUrl</a></li>";
                                        }
                                    } else {
                                        echo '<li>-</li>';
                                    }
                                    ?>
                                </ul>
                            </span>
                        </li>

                        <li>
                            <span class="label">🚚 Slogan/brand:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_slogan_brand']) ?></span>
                        </li>
                        <li>
                            <span class="label">🚚 Pallete:</span>
                            <span class="value">
                                <?php
                                $cd_pallete_raw = $order['cd_pallete'] ?? null;
                                $palleteList = $cd_pallete_raw ? json_decode($cd_pallete_raw, true) : [];

                                echo !empty($palleteList) ? htmlspecialchars(implode(', ', $palleteList)) : '-';

                                ?>
                            </span>
                        </li>

                        <li>
                            <span class="label">🚚 Pallete Lainnya:</span>
                            <span class="value">
                                <?php
                                // $anotherPalleteList = json_decode($order['cd_another_pallete'], true);
                                // echo !empty($anotherPalleteList) ? htmlspecialchars(implode(', ', $anotherPalleteList)) : '-';

                                $anotherPalleteList = $order['cd_pallete'] ?? null;
                                $palleteListAll = $anotherPalleteList ? json_decode($cd_pallete_raw, true) : [];

                                echo !empty($palleteListAll) ? htmlspecialchars(implode(', ', $palleteListAll)) : '-';
                                ?>
                            </span>
                        </li>


                        <li>
                            <span class="label">🚚 Target Audiens:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_audiens']) ?></span>
                        </li>
                        <li>
                            <span class="label">🚚 Catatan:</span>
                            <span class="value"><?= htmlspecialchars($order['cd_notes']) ?></span>
                        </li>
                        <li>
                            <span class="label">📦 Jumlah Dipesan:</span>
                            <span class="value">30 pcs</span>

                        </li>
                    </ul>







                    <div class="info-box">
                        <div class="info-label">Jumlah Dipesan</div>
                        <div class="info-value">30 pcs</div>
                    </div>


                    <?php if (empty($order['total']) || $order['total'] == 0): ?>
                        <form method="POST" action="">
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
                            <button class="btn" type="submit" id="btn-submit-tagihan">💳 Buat Tagihan</button>
                        </form>
                    <?php else: ?>
                        <ul class="info-list">
                            <li>
                                <span class="label">📦 Total:</span>
                                <span class="value">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                            </li>
                        </ul>
                        <form method="GET" action="receipt.php" target="_blank">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <button class="btn" type="submit">🧾 Lihat/Print Receipt</button>
                        </form>
                    <?php endif; ?>


                </div>
            </div>

        </div>
    </div>

</body>

<?php include 'footer.php'; ?>

</html>