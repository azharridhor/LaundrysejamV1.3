<?php
// ============================================
// admin/promo.php - CRUD Promo
// ============================================

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/includes/common.php';

$action   = $_GET['action'] ?? 'list';
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg      = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul    = esc($koneksi, $_POST['judul_promo'] ?? '');
    $deskripsi = esc($koneksi, $_POST['deskripsi'] ?? '');

    if (empty($judul) || empty($deskripsi)) {
        $msg = 'Judul dan deskripsi promo wajib diisi!';
        $msg_type = 'danger';
    } else {
        if (isset($_POST['edit_id']) && $_POST['edit_id'] > 0) {
            $edit_id = (int)$_POST['edit_id'];
            $sql = "UPDATE promo SET judul_promo='$judul', deskripsi='$deskripsi' WHERE id=$edit_id";
            if (query($koneksi, $sql)) { $msg = '✅ Promo berhasil diperbarui!'; $action = 'list'; }
            else { $msg = '❌ Gagal memperbarui!'; $msg_type = 'danger'; }
        } else {
            $sql = "INSERT INTO promo (judul_promo, deskripsi) VALUES ('$judul', '$deskripsi')";
            if (query($koneksi, $sql)) { $msg = '✅ Promo baru berhasil ditambahkan!'; $action = 'list'; }
            else { $msg = '❌ Gagal menambahkan!'; $msg_type = 'danger'; }
        }
    }
}

if ($action === 'hapus' && $id > 0) {
    if (query($koneksi, "DELETE FROM promo WHERE id=$id")) { $msg = '🗑️ Promo berhasil dihapus!'; }
    else { $msg = '❌ Gagal menghapus!'; $msg_type = 'danger'; }
    $action = 'list';
}

$edit_data = null;
if ($action === 'edit' && $id > 0) {
    $result = query($koneksi, "SELECT * FROM promo WHERE id=$id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) $edit_data = mysqli_fetch_assoc($result);
    else { $msg = 'Data tidak ditemukan!'; $action = 'list'; }
}

$all_promo = query($koneksi, "SELECT * FROM promo ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Promo - Admin LaundryKoin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-md-none" onclick="toggleSidebar()" style="background:none;border:none;font-size:1.3rem">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <div class="topbar-title">
                    <i class="bi bi-tag-fill me-2 text-danger"></i>Kelola Promo
                </div>
                <div class="small text-danger">#pastiadapromo — pastikan promo selalu terlihat dan menarik.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="promo.php?action=tambah" class="btn btn-danger btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
        </div>
    </div>

    <div class="content-area">
        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show mb-4" role="alert" style="border-radius:12px">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form -->
            <div class="col-lg-4">
                <div class="form-card">
                    <h5 class="fw-700 mb-4">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <i class="bi bi-pencil-fill text-warning me-2"></i>Edit Promo
                        <?php else: ?>
                        <i class="bi bi-plus-circle-fill text-danger me-2"></i>Tambah Promo
                        <?php endif; ?>
                    </h5>

                    <form method="POST" action="promo.php">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Judul Promo <span class="text-danger">*</span></label>
                            <input type="text" name="judul_promo" class="form-control"
                                   placeholder="contoh: Diskon 20%"
                                   value="<?= htmlspecialchars($edit_data['judul_promo'] ?? '') ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Deskripsi Promo <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="5"
                                      placeholder="Jelaskan detail promo, syarat dan ketentuannya..." required><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger rounded-3 py-2 fw-700">
                                <i class="bi bi-<?= $action === 'edit' ? 'save' : 'plus-lg' ?> me-2"></i>
                                <?= $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Promo' ?>
                            </button>
                            <?php if ($action === 'edit'): ?>
                            <a href="promo.php" class="btn btn-outline-secondary rounded-3 py-2">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Info Tips -->
                <div class="mt-3 p-3 rounded-3" style="background:#fff5f5;border:1px dashed #dc3545">
                    <small class="text-danger">
                        <i class="bi bi-lightbulb-fill me-2"></i>
                        <strong>Tips:</strong> Promo yang menarik meningkatkan pelanggan! Gunakan kata-kata yang jelas dan tunjukkan nilai lebih.
                    </small>
                </div>
            </div>

            <!-- Tabel & Card Promo -->
            <div class="col-lg-8">
                <!-- Card Preview Promo -->
                <?php
                $result_preview = query($koneksi, "SELECT * FROM promo ORDER BY id ASC LIMIT 3");
                if ($result_preview && mysqli_num_rows($result_preview) > 0):
                ?>
                <div class="row g-3 mb-4">
                    <?php
                    $promo_icons = ['bi-tag-fill', 'bi-gift-fill', 'bi-wallet2'];
                    $pi = 0;
                    while ($p = mysqli_fetch_assoc($result_preview)):
                        $picon = $promo_icons[$pi % count($promo_icons)]; $pi++;
                    ?>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 text-white" style="background:linear-gradient(135deg,#c0392b,#e74c3c)">
                            <i class="bi <?= $picon ?> fs-4 mb-2 d-block opacity-75"></i>
                            <div class="fw-700 mb-1" style="font-size:0.9rem"><?= htmlspecialchars($p['judul_promo']) ?></div>
                            <div style="font-size:0.75rem;opacity:0.8"><?= htmlspecialchars(substr($p['deskripsi'], 0, 50)) ?>...</div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <!-- Tabel Data -->
                <div class="data-table-wrap">
                    <div class="table-header">
                        <h5><i class="bi bi-fire me-2 text-danger"></i>Semua Promo Aktif</h5>
                        <span class="badge bg-danger rounded-pill">
                            <?= $all_promo ? mysqli_num_rows($all_promo) : 0 ?> promo
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Promo</th>
                                    <th>Deskripsi</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($all_promo && mysqli_num_rows($all_promo) > 0):
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($all_promo)):
                                ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td>
                                        <span class="badge bg-danger me-1"><i class="bi bi-fire"></i></span>
                                        <strong><?= htmlspecialchars($row['judul_promo']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                            <?= htmlspecialchars($row['deskripsi']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="promo.php?action=edit&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-warning me-1" style="border-radius:8px">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="promo.php?action=hapus&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger" style="border-radius:8px"
                                           onclick="return confirm('Hapus promo \'<?= htmlspecialchars($row['judul_promo']) ?>\'?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-tag d-block mb-2 fs-2 opacity-50"></i>
                                        Belum ada promo aktif.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
