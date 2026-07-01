<?php
// ============================================
// admin/lokasi.php - CRUD Lokasi
// ============================================

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/includes/common.php';

$action   = $_GET['action'] ?? 'list';
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg      = '';
$msg_type = 'success';

// ---- PROSES POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lokasi = esc($koneksi, $_POST['nama_lokasi'] ?? '');
    $alamat      = esc($koneksi, $_POST['alamat'] ?? '');
    $image_url   = esc($koneksi, $_POST['image_url'] ?? '');
    $maps_url    = esc($koneksi, $_POST['maps_url'] ?? '');

    if (empty($nama_lokasi) || empty($alamat)) {
        $msg      = 'Nama lokasi dan alamat wajib diisi!';
        $msg_type = 'danger';
    } else {
        if (isset($_POST['edit_id']) && $_POST['edit_id'] > 0) {
            $edit_id = (int)$_POST['edit_id'];
            $sql = "UPDATE lokasi SET nama_lokasi='$nama_lokasi', alamat='$alamat', image_url='$image_url', maps_url='$maps_url' WHERE id=$edit_id";
            if (query($koneksi, $sql)) {
                $msg = '✅ Data lokasi berhasil diperbarui!';
                $action = 'list';
            } else {
                $msg = '❌ Gagal memperbarui data!';
                $msg_type = 'danger';
            }
        } else {
            $sql = "INSERT INTO lokasi (nama_lokasi, alamat, image_url, maps_url) VALUES ('$nama_lokasi', '$alamat', '$image_url', '$maps_url')";
            if (query($koneksi, $sql)) {
                $msg = '✅ Data lokasi berhasil ditambahkan!';
                $action = 'list';
            } else {
                $msg = '❌ Gagal menambah data!';
                $msg_type = 'danger';
            }
        }
    }
}

// ---- HAPUS ----
if ($action === 'hapus' && $id > 0) {
    if (query($koneksi, "DELETE FROM lokasi WHERE id=$id")) {
        $msg    = '🗑️ Data lokasi berhasil dihapus!';
        $action = 'list';
    } else {
        $msg      = '❌ Gagal menghapus data!';
        $msg_type = 'danger';
        $action   = 'list';
    }
}

// ---- EDIT ----
$edit_data = null;
if ($action === 'edit' && $id > 0) {
    $result = query($koneksi, "SELECT * FROM lokasi WHERE id=$id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_data = mysqli_fetch_assoc($result);
    } else {
        $msg = 'Data tidak ditemukan!';
        $action = 'list';
    }
}

$all_lokasi = query($koneksi, "SELECT * FROM lokasi ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lokasi - Admin LaundryKoin</title>
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
                <i class="bi bi-geo-alt-fill me-2 text-success"></i>Kelola Lokasi
            </div>
        </div>
        <div class="topbar-right">
            <a href="lokasi.php?action=tambah" class="btn btn-success btn-sm rounded-pill px-3">
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
                        <i class="bi bi-pencil-fill text-warning me-2"></i>Edit Lokasi
                        <?php else: ?>
                        <i class="bi bi-plus-circle-fill text-success me-2"></i>Tambah Lokasi
                        <?php endif; ?>
                    </h5>

                    <form method="POST" action="lokasi.php">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="nama_lokasi"
                                   class="form-control"
                                   placeholder="contoh: UMY, Nologaten"
                                   value="<?= htmlspecialchars($edit_data['nama_lokasi'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Masukkan alamat lengkap cabang..."
                                      required><?= htmlspecialchars($edit_data['alamat'] ?? '') ?></textarea>
                            <small class="text-muted">Gunakan alamat yang mudah ditemukan di Maps.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL Gambar Lokasi</label>
                            <input type="url"
                                   name="image_url"
                                   class="form-control"
                                   placeholder="https://.../image.jpg"
                                   value="<?= htmlspecialchars($edit_data['image_url'] ?? '') ?>">
                            <small class="text-muted">Opsional, tambahkan foto atau peta singkat untuk lokasi ini.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">URL Google Maps</label>
                            <input type="url"
                                   name="maps_url"
                                   class="form-control"
                                   placeholder="https://www.google.com/maps/..."
                                   value="<?= htmlspecialchars($edit_data['maps_url'] ?? '') ?>">
                            <small class="text-muted">Opsional, tambahkan link Google Maps agar pelanggan bisa langsung membuka peta.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success rounded-3 py-2 fw-600">
                                <?php if ($action === 'edit'): ?>
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                                <?php else: ?>
                                <i class="bi bi-plus-lg me-2"></i>Tambah Lokasi
                                <?php endif; ?>
                            </button>
                            <?php if ($action === 'edit'): ?>
                            <a href="lokasi.php" class="btn btn-outline-secondary rounded-3 py-2">
                                <i class="bi bi-x-lg me-2"></i>Batal
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="col-lg-8">
                <div class="data-table-wrap">
                    <div class="table-header">
                        <h5><i class="bi bi-pin-map-fill me-2 text-success"></i>Daftar Lokasi Cabang</h5>
                        <span class="badge bg-success rounded-pill">
                            <?= $all_lokasi ? mysqli_num_rows($all_lokasi) : 0 ?> cabang
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Cabang</th>
                                    <th>Alamat</th>
                                    <th>Maps</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($all_lokasi && mysqli_num_rows($all_lokasi) > 0):
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($all_lokasi)):
                                ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:32px;height:32px;background:#e6f4ea;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#0d7a3c;font-size:0.9rem">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </div>
                                            <strong><?= htmlspecialchars($row['nama_lokasi']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($row['alamat']) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['maps_url'])): ?>
                                            <a href="<?= htmlspecialchars($row['maps_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-geo-alt-fill me-1"></i>Maps
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="lokasi.php?action=edit&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-warning me-1" style="border-radius:8px">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="lokasi.php?action=hapus&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger" style="border-radius:8px"
                                           onclick="return confirm('Hapus lokasi \'<?= htmlspecialchars($row['nama_lokasi']) ?>\'?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-geo d-block mb-2 fs-2 opacity-50"></i>
                                        Belum ada data lokasi.
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
