<?php
// ============================================
// admin/orders.php - Daftar Order dan Status Pembayaran
// ============================================

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/includes/common.php';

$msg = '';
$msg_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_payment' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']);
    $checkOrder = query($koneksi, "SELECT status, payment_proof FROM orders WHERE id = $orderId LIMIT 1");
    if ($checkOrder && mysqli_num_rows($checkOrder) === 1) {
        $orderData = mysqli_fetch_assoc($checkOrder);
        if (empty($orderData['payment_proof'])) {
            $msg = 'Order ini belum mengirim bukti pembayaran.';
            $msg_type = 'danger';
        } else {
            $newStatus = 'Pembayaran Diverifikasi';
            $update = query($koneksi, "UPDATE orders SET status = '$newStatus' WHERE id = $orderId");
            if ($update) {
                $msg = 'Status pembayaran berhasil diverifikasi.';
                $msg_type = 'success';
            } else {
                $msg = 'Gagal memperbarui status pembayaran: ' . mysqli_error($koneksi);
                $msg_type = 'danger';
            }
        }
    } else {
        $msg = 'Order tidak ditemukan.';
        $msg_type = 'danger';
    }
}

$orderResult = query($koneksi, "SELECT o.*, l.nama_layanan, loc.nama_lokasi, m.username AS member_username, m.name AS member_name FROM orders o LEFT JOIN layanan l ON o.layanan_id = l.id LEFT JOIN lokasi loc ON o.lokasi_id = loc.id LEFT JOIN members m ON o.member_id = m.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Order - Admin LaundryKoin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-sm" onclick="toggleSidebar()" style="background:none;border:none;font-size:1.3rem">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title">
                <i class="bi bi-card-list me-2 text-primary"></i>Kelola Order
            </div>
        </div>
        <div class="topbar-right">
            <a href="orders.php" class="btn btn-primary btn-sm rounded-pill px-3">
                Refresh
            </a>
        </div>
    </div>

    <div class="content-area">
        <div class="mb-4">
            <h5 class="fw-700 mb-2">Daftar Order</h5>
            <p class="text-muted mb-0">Lihat status pembayaran order beserta bukti pembayaran yang dikirim oleh pelanggan.</p>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show mb-4" role="alert" style="border-radius:12px">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="data-table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Layanan</th>
                            <th>Qty</th>
                            <th>Pengantaran</th>
                            <th>Metode Bayar</th>
                            <th>Bukti Pembayaran</th>
                            <th>Status Bukti</th>
                            <th>Status Order</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orderResult && mysqli_num_rows($orderResult) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($orderResult)): ?>
                                <?php
                                $paymentProofUrl = !empty($order['payment_proof']) ? '../' . $order['payment_proof'] : '';
                                $proofStatus = !empty($order['payment_proof']) ? 'Sudah Mengirim Bukti' : 'Belum Mengirim Bukti';
                                $paymentMethodLabel = $order['payment_method'] === 'branch' ? 'Bayar di Cabang' : ucfirst(htmlspecialchars($order['payment_method']));
                                $deliveryLabel = $order['delivery_type'] === 'pickup' ? 'Pickup' : ($order['delivery_type'] === 'selfservice' ? 'Self-Service' : 'Dropoff');
                                $memberLabel = htmlspecialchars($order['member_name'] ?: $order['member_username'] ?: 'Tidak Diketahui');
                                $serviceLabel = htmlspecialchars($order['nama_layanan'] ?: 'Tidak diketahui');
                                ?>
                                <tr>
                                    <td class="text-muted"><?= htmlspecialchars($order['id']) ?></td>
                                    <td><?= $memberLabel ?></td>
                                    <td><?= $serviceLabel ?></td>
                                    <td><?= htmlspecialchars($order['quantity']) ?></td>
                                    <td><?= $deliveryLabel ?></td>
                                    <td><?= $paymentMethodLabel ?></td>
                                    <td>
                                        <?php if ($paymentProofUrl): ?>
                                            <a href="<?= $paymentProofUrl ?>" target="_blank" class="text-decoration-none">Lihat Bukti</a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $paymentProofUrl ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($proofStatus) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($order['created_at']))) ?></td>
                                    <td>
                                        <?php if ($paymentProofUrl && $order['status'] !== 'Pembayaran Diverifikasi'): ?>
                                            <form method="POST" action="orders.php" class="d-inline">
                                                <input type="hidden" name="action" value="verify_payment">
                                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill">
                                                    <i class="bi bi-check2-circle me-1"></i>Verifikasi
                                                </button>
                                            </form>
                                        <?php elseif ($order['status'] === 'Pembayaran Diverifikasi'): ?>
                                            <span class="badge bg-success">Terverifikasi</span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada order.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
