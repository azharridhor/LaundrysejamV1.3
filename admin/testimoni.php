<?php
// ============================================
// admin/testimoni.php - CRUD Testimoni
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
    $nama    = esc($koneksi, $_POST['nama_pelanggan'] ?? '');
    $isi     = esc($koneksi, $_POST['isi_testimoni'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 5);

    if (empty($nama) || empty($isi) || $rating < 1 || $rating > 5) {
        $msg = 'Semua field wajib diisi dengan benar!';
        $msg_type = 'danger';
    } else {
        if (isset($_POST['edit_id']) && $_POST['edit_id'] > 0) {
            $edit_id = (int)$_POST['edit_id'];
            $sql = "UPDATE testimoni SET nama_pelanggan='$nama', isi_testimoni='$isi', rating=$rating WHERE id=$edit_id";
            if (query($koneksi, $sql)) { $msg = '✅ Testimoni berhasil diperbarui!'; $action = 'list'; }
            else { $msg = '❌ Gagal memperbarui!'; $msg_type = 'danger'; }
        } else {
            $sql = "INSERT INTO testimoni (nama_pelanggan, isi_testimoni, rating) VALUES ('$nama', '$isi', $rating)";
            if (query($koneksi, $sql)) { $msg = '✅ Testimoni berhasil ditambahkan!'; $action = 'list'; }
            else { $msg = '❌ Gagal menambahkan!'; $msg_type = 'danger'; }
        }
    }
}

if ($action === 'hapus' && $id > 0) {
    if (query($koneksi, "DELETE FROM testimoni WHERE id=$id")) { $msg = '🗑️ Testimoni berhasil dihapus!'; }
    else { $msg = '❌ Gagal menghapus!'; $msg_type = 'danger'; }
    $action = 'list';
}

$edit_data = null;
if ($action === 'edit' && $id > 0) {
    $result = query($koneksi, "SELECT * FROM testimoni WHERE id=$id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) $edit_data = mysqli_fetch_assoc($result);
    else { $msg = 'Data tidak ditemukan!'; $action = 'list'; }
}

$all_testi = query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Testimoni - Admin LaundryKoin</title>
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
            <div class="topbar-title">
                <i class="bi bi-chat-quote-fill me-2 text-warning"></i>Kelola Testimoni
            </div>
        </div>
        <div class="topbar-right">
            <a href="testimoni.php?action=tambah" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-600">
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
                        <i class="bi bi-pencil-fill text-warning me-2"></i>Edit Testimoni
                        <?php else: ?>
                        <i class="bi bi-plus-circle-fill text-warning me-2"></i>Tambah Testimoni
                        <?php endif; ?>
                    </h5>

                    <form method="POST" action="testimoni.php">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control"
                                   placeholder="contoh: Budi Santoso"
                                   value="<?= htmlspecialchars($edit_data['nama_pelanggan'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Testimoni <span class="text-danger">*</span></label>
                            <textarea name="isi_testimoni" class="form-control" rows="4"
                                      placeholder="Tulis ulasan pelanggan..." required><?= htmlspecialchars($edit_data['isi_testimoni'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php for ($i = 1; $i <= 5; $i++): 
                                    $checked = (isset($edit_data['rating']) ? $edit_data['rating'] == $i : $i == 5) ? 'checked' : '';
                                ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="rating" id="rating<?= $i ?>"
                                           value="<?= $i ?>" <?= $checked ?>>
                                    <label class="form-check-label" for="rating<?= $i ?>">
                                        <?= str_repeat('⭐', $i) ?>
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning rounded-3 py-2 fw-700 text-dark">
                                <i class="bi bi-<?= $action === 'edit' ? 'save' : 'plus-lg' ?> me-2"></i>
                                <?= $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Testimoni' ?>
                            </button>
                            <?php if ($action === 'edit'): ?>
                            <a href="testimoni.php" class="btn btn-outline-secondary rounded-3 py-2">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="col-lg-8">
                <div class="data-table-wrap">
                    <div class="table-header">
                        <h5><i class="bi bi-star-fill me-2 text-warning"></i>Daftar Testimoni</h5>
                        <span class="badge bg-warning text-dark rounded-pill">
                            <?= $all_testi ? mysqli_num_rows($all_testi) : 0 ?> testimoni
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th>Testimoni</th>
                                    <th>Rating</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($all_testi && mysqli_num_rows($all_testi) > 0):
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($all_testi)):
                                ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:34px;height:34px;background:linear-gradient(135deg,#e20513,#ffd500);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.85rem;flex-shrink:0">
                                                <?= strtoupper(substr($row['nama_pelanggan'], 0, 1)) ?>
                                            </div>
                                            <strong style="font-size:0.875rem"><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                            "<?= htmlspecialchars($row['isi_testimoni']) ?>"
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge-rating">
                                            <?= str_repeat('⭐', $row['rating']) ?>
                                            (<?= $row['rating'] ?>)
                                        </span>
                                    </td>
                                    <td>
                                        <a href="testimoni.php?action=edit&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-warning me-1" style="border-radius:8px">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="testimoni.php?action=hapus&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger" style="border-radius:8px"
                                           onclick="return confirm('Hapus testimoni ini?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-chat-dots d-block mb-2 fs-2 opacity-50"></i>
                                        Belum ada data testimoni.
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
