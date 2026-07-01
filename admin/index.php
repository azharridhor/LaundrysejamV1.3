<?php
// ============================================
// admin/index.php - Dashboard Admin
// ============================================

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/includes/common.php';

// Hitung total data
function countTable(mysqli $koneksi, string $table): int {
    $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $table");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return (int) $row['total'];
    }
    return 0;
}

$total_layanan  = countTable($koneksi, 'layanan');
$total_lokasi   = countTable($koneksi, 'lokasi');
$total_testi    = countTable($koneksi, 'testimoni');
$total_promo    = countTable($koneksi, 'promo');

// Data terbaru
$latest_testi  = query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC LIMIT 3");
$latest_layanan = query($koneksi, "SELECT * FROM layanan ORDER BY id ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin LaundryKoin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <!-- Topbar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-md-none" onclick="toggleSidebar()" style="background:none;border:none;font-size:1.3rem;">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
            </div>
        </div>
        <div class="topbar-right">
            <div class="admin-badge">
                <i class="bi bi-circle-fill text-success" style="font-size:0.5rem"></i>
                <?= htmlspecialchars($_SESSION['admin_username']) ?>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-area">
        <!-- Welcome -->
        <div class="p-4 rounded-4 mb-4 text-white"
             style="background: linear-gradient(135deg, #e20513, #ffd500);">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="fw-800 mb-1">
                        👋 Selamat datang, <?= htmlspecialchars($_SESSION['admin_username']) ?>!
                    </h5>
                    <p class="mb-0 opacity-75" style="font-size:0.9rem">
                        Kelola semua data LaundryKoin Sejam dari sini.
                    </p>
                </div>
                <div class="col-auto d-none d-md-block">
                    <i class="bi bi-washing-machine" style="font-size:3rem;opacity:0.3"></i>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <?php
            $stats = [
                ['label' => 'Total Layanan', 'value' => $total_layanan, 'icon' => 'bi-washing-machine', 'color' => 'primary', 'bg' => '#e8f0fe', 'link' => 'layanan.php'],
                ['label' => 'Total Lokasi',  'value' => $total_lokasi,  'icon' => 'bi-geo-alt-fill',    'color' => 'success', 'bg' => '#e6f4ea', 'link' => 'lokasi.php'],
                ['label' => 'Testimoni',      'value' => $total_testi,   'icon' => 'bi-chat-quote-fill', 'color' => 'warning', 'bg' => '#fff8e7', 'link' => 'testimoni.php'],
                ['label' => 'Promo Aktif',    'value' => $total_promo,   'icon' => 'bi-tag-fill',        'color' => 'danger',  'bg' => '#fce8e6', 'link' => 'promo.php'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="col-6 col-lg-3">
                <a href="<?= $s['link'] ?>" class="text-decoration-none">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon" style="background:<?= $s['bg'] ?>;color:var(--bs-<?= $s['color'] ?>)">
                                <i class="bi <?= $s['icon'] ?>"></i>
                            </div>
                            <i class="bi bi-arrow-up-right text-muted"></i>
                        </div>
                        <div class="stat-number"><?= $s['value'] ?></div>
                        <div class="stat-label mt-1"><?= $s['label'] ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Quick Actions + Tabel -->
        <div class="row g-4">
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="data-table-wrap h-100">
                    <h5 class="fw-700 mb-4">⚡ Aksi Cepat</h5>
                    <?php
                    $actions = [
                        ['href' => 'layanan.php?action=tambah', 'icon' => 'bi-plus-circle-fill', 'color' => 'primary', 'label' => 'Tambah Layanan Baru'],
                        ['href' => 'lokasi.php?action=tambah',  'icon' => 'bi-plus-circle-fill', 'color' => 'success', 'label' => 'Tambah Lokasi Baru'],
                        ['href' => 'testimoni.php?action=tambah','icon' => 'bi-plus-circle-fill','color' => 'warning', 'label' => 'Tambah Testimoni'],
                        ['href' => 'promo.php?action=tambah',   'icon' => 'bi-plus-circle-fill', 'color' => 'danger',  'label' => 'Tambah Promo Baru'],
                    ];
                    foreach ($actions as $a):
                    ?>
                    <a href="<?= $a['href'] ?>"
                       class="d-flex align-items-center gap-3 p-3 mb-2 rounded-3 text-decoration-none"
                       style="background:#f8faff;transition:all 0.2s;"
                       onmouseover="this.style.background='#e8f0fe'"
                       onmouseout="this.style.background='#f8faff'">
                        <i class="bi <?= $a['icon'] ?> text-<?= $a['color'] ?> fs-5"></i>
                        <span style="font-size:0.875rem;font-weight:600;color:#1a1a2e">
                            <?= $a['label'] ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Layanan Terbaru -->
            <div class="col-lg-8">
                <div class="data-table-wrap">
                    <div class="table-header">
                        <h5>📋 Data Layanan</h5>
                        <a href="layanan.php" class="btn btn-primary btn-sm rounded-pill px-3">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Layanan</th>
                                    <th>Harga</th>
                                    <th>Durasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($latest_layanan && mysqli_num_rows($latest_layanan) > 0):
                                    $no = 1;
                                    while ($l = mysqli_fetch_assoc($latest_layanan)):
                                ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td class="fw-600"><?= htmlspecialchars($l['nama_layanan']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= formatRupiah($l['harga']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($l['durasi']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="layanan.php?action=edit&id=<?= $l['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1" style="border-radius:8px">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada data layanan
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Testimoni Terbaru -->
            <div class="col-12">
                <div class="data-table-wrap">
                    <div class="table-header">
                        <h5>💬 Testimoni Terbaru</h5>
                        <a href="testimoni.php" class="btn btn-warning btn-sm rounded-pill px-3">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="row g-3">
                        <?php
                        if ($latest_testi && mysqli_num_rows($latest_testi) > 0):
                            while ($t = mysqli_fetch_assoc($latest_testi)):
                        ?>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8faff;border:1px solid #e8ecf0">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#e20513,#ffd500);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.9rem">
                                        <?= strtoupper(substr($t['nama_pelanggan'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-size:0.875rem;font-weight:700"><?= htmlspecialchars($t['nama_pelanggan']) ?></div>
                                        <div><?= tampilkanBintang($t['rating']) ?></div>
                                    </div>
                                </div>
                                <p class="text-muted mb-0" style="font-size:0.82rem;line-height:1.5">
                                    "<?= htmlspecialchars(substr($t['isi_testimoni'], 0, 80)) ?>..."
                                </p>
                            </div>
                        </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <div class="col-12">
                            <p class="text-center text-muted py-3">Belum ada testimoni</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
