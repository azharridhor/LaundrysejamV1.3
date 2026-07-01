<?php
// ============================================
// admin/layanan.php - CRUD Layanan
// ============================================

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/includes/common.php';

// Pastikan kolom category ada di tabel layanan (varchar kode kategori)
$koneksi->query("ALTER TABLE layanan ADD COLUMN IF NOT EXISTS category VARCHAR(100) NOT NULL DEFAULT ''");
$koneksi->query("ALTER TABLE layanan ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NOT NULL DEFAULT ''");

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg     = '';
$msg_type = 'success';

// ---- PROSES POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_layanan = esc($koneksi, $_POST['nama_layanan'] ?? '');
    $harga        = (int)($_POST['harga'] ?? 0);
    $durasi       = esc($koneksi, $_POST['durasi'] ?? '');
    $category     = esc($koneksi, $_POST['category'] ?? '');
    $image_url    = esc($koneksi, $_POST['image_url'] ?? '');

    if (empty($nama_layanan) || $harga <= 0 || empty($durasi)) {
        $msg = 'Semua field wajib diisi dengan benar!';
        $msg_type = 'danger';
    } else {
            if (isset($_POST['edit_id']) && $_POST['edit_id'] > 0) {
            // UPDATE
            $edit_id = (int)$_POST['edit_id'];
            $sql = "UPDATE layanan SET nama_layanan='$nama_layanan', harga=$harga, durasi='$durasi', category='$category', image_url='$image_url' WHERE id=$edit_id";
            if (query($koneksi, $sql)) {
                $msg = '✅ Data layanan berhasil diperbarui!';
                $action = 'list';
            } else {
                $msg = '❌ Gagal memperbarui data: ' . mysqli_error($koneksi);
                $msg_type = 'danger';
            }
        } else {
            // INSERT
            $sql = "INSERT INTO layanan (nama_layanan, harga, durasi, category, image_url) VALUES ('$nama_layanan', $harga, '$durasi', '$category', '$image_url')";
            if (query($koneksi, $sql)) {
                $msg = '✅ Layanan baru berhasil ditambahkan!';
                $action = 'list';
            } else {
                $msg = '❌ Gagal menambah data: ' . mysqli_error($koneksi);
                $msg_type = 'danger';
            }
        }
    }
}

// ---- SEED DEFAULT SERVICES (idempotent) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'seed_defaults') {
    $defaults = [
        // Cuci Pakaian - Self Service
        ['nama' => 'Cuci Kering Self-Service', 'harga' => 15000, 'durasi' => '45 Menit', 'category' => 'cuci_pakaian'],
        ['nama' => 'Cuci Self-Service (Cuci saja)', 'harga' => 12000, 'durasi' => '45 Menit', 'category' => 'cuci_pakaian'],

        // Cuci Pakaian - Dropoff
        ['nama' => 'Cuci Kering Dropoff - Tidak Lipat (8kg)', 'harga' => 4000, 'durasi' => '2 Hari', 'category' => 'cuci_pakaian'],
        ['nama' => 'Cuci Kering Dropoff - Lipat (8kg)', 'harga' => 10000, 'durasi' => '2 Hari', 'category' => 'cuci_pakaian'],

        // Cuci Sepatu / Tas / Helm - Deep / Fast
        ['nama' => 'Cuci Sepatu - Deep Clean', 'harga' => 50000, 'durasi' => '2 Hari', 'category' => 'cuci_sepatu_tas_helm'],
        ['nama' => 'Cuci Sepatu - Fast Clean', 'harga' => 30000, 'durasi' => '1 Hari', 'category' => 'cuci_sepatu_tas_helm'],
        ['nama' => 'Cuci Tas - Deep Clean', 'harga' => 60000, 'durasi' => '2 Hari', 'category' => 'cuci_sepatu_tas_helm'],
        ['nama' => 'Cuci Tas - Fast Clean', 'harga' => 35000, 'durasi' => '1 Hari', 'category' => 'cuci_sepatu_tas_helm'],
        ['nama' => 'Cuci Helm - Deep Clean', 'harga' => 45000, 'durasi' => '2 Hari', 'category' => 'cuci_sepatu_tas_helm'],
        ['nama' => 'Cuci Helm - Fast Clean', 'harga' => 25000, 'durasi' => '1 Hari', 'category' => 'cuci_sepatu_tas_helm'],

        // Paket Setrika (per kg) - note: min 3kg for dropoff
        ['nama' => 'Setrika Ekspress (Termasuk Cuci Kering, 1 Sabun, 1 Pewangi) - 20rb/kg', 'harga' => 20000, 'durasi' => 'Ekspress', 'category' => 'setrika'],
        ['nama' => 'Setrika 1 Hari - 8rb/kg (tidak termasuk cuci kering dropoff)', 'harga' => 8000, 'durasi' => '1 Hari', 'category' => 'setrika'],
        ['nama' => 'Setrika 2 Hari - 6rb/kg (tidak termasuk cuci kering dropoff)', 'harga' => 6000, 'durasi' => '2 Hari', 'category' => 'setrika'],
        ['nama' => 'Setrika 3 Hari - 4rb/kg (tidak termasuk cuci kering dropoff)', 'harga' => 4000, 'durasi' => '3 Hari', 'category' => 'setrika'],
    ];

    $inserted = 0;
    foreach ($defaults as $d) {
        $name = esc($koneksi, $d['nama']);
        $price = intval($d['harga']);
        $dur = esc($koneksi, $d['durasi']);
        $check = query($koneksi, "SELECT id FROM layanan WHERE nama_layanan = '$name' LIMIT 1");
        if (!$check || mysqli_num_rows($check) === 0) {
            $cat = esc($koneksi, $d['category'] ?? '');
            query($koneksi, "INSERT INTO layanan (nama_layanan, harga, durasi, category) VALUES ('$name', $price, '$dur', '$cat')");
            $inserted++;
        }
    }

    if ($inserted > 0) {
        $msg = "✅ $inserted layanan default berhasil ditambahkan.";
    } else {
        $msg = 'ℹ️ Semua layanan default sudah ada.';
        $msg_type = 'info';
    }
    $action = 'list';
}

// ---- HAPUS ----
if ($action === 'hapus' && $id > 0) {
    if (query($koneksi, "DELETE FROM layanan WHERE id=$id")) {
        $msg    = '🗑️ Data layanan berhasil dihapus!';
        $action = 'list';
    } else {
        $msg      = '❌ Gagal menghapus data!';
        $msg_type = 'danger';
        $action   = 'list';
    }
}

// ---- AMBIL DATA EDIT ----
$edit_data = null;
if ($action === 'edit' && $id > 0) {
    $result = query($koneksi, "SELECT * FROM layanan WHERE id=$id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_data = mysqli_fetch_assoc($result);
    } else {
        $msg    = 'Data tidak ditemukan!';
        $action = 'list';
    }
}

// ---- AMBIL SEMUA LAYANAN ----
$filterCategory = $_GET['category'] ?? '';
$filterQ = esc($koneksi, $_GET['q'] ?? '');
$where = [];
if ($filterQ !== '') {
    $where[] = "nama_layanan LIKE '%" . $filterQ . "%'";
}
if ($filterCategory !== '') {
    switch ($filterCategory) {
        case 'cuci_pakaian':
            $where[] = "(LOWER(nama_layanan) LIKE '%cuci%')";
            break;
        case 'cuci_sepatu_tas_helm':
            $where[] = "(LOWER(nama_layanan) LIKE '%sepatu%' OR LOWER(nama_layanan) LIKE '%tas%' OR LOWER(nama_layanan) LIKE '%helm%')";
            break;
        case 'setrika':
            $where[] = "(LOWER(nama_layanan) LIKE '%setrika%')";
            break;
        default:
            break;
    }
}
$sqlAll = 'SELECT * FROM layanan' . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id ASC';
$all_layanan = query($koneksi, $sqlAll);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - Admin LaundryKoin</title>
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
            <button type="button" class="btn btn-sm" onclick="toggleSidebar()" style="background:none;border:none;font-size:1.3rem">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title">
                <i class="bi bi-washing-machine me-2 text-primary"></i>Kelola Layanan
            </div>
        </div>
        <div class="topbar-right">
            <a href="layanan.php?action=tambah" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
            <form method="POST" class="d-inline ms-2">
                <input type="hidden" name="action" value="seed_defaults">
                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-box-seam me-1"></i>Seed Default
                </button>
            </form>
        </div>
    </div>

    <div class="content-area">
        <!-- Alert -->
        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show mb-4" role="alert" style="border-radius:12px">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Tambah/Edit -->
            <div class="col-lg-4">
                <div class="form-card">
                    <h5 class="fw-700 mb-4">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <i class="bi bi-pencil-fill text-warning me-2"></i>Edit Layanan
                        <?php else: ?>
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Layanan
                        <?php endif; ?>
                    </h5>

                    <form method="POST" action="layanan.php">
                        <?php if ($action === 'edit' && $edit_data): ?>
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="nama_layanan"
                                   class="form-control"
                                   placeholder="contoh: Cuci + Kering"
                                   value="<?= htmlspecialchars($edit_data['nama_layanan'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-radius:10px 0 0 10px">Rp</span>
                                <input type="number"
                                       name="harga"
                                       class="form-control"
                                       placeholder="10000"
                                       min="1000"
                                       value="<?= $edit_data['harga'] ?? '' ?>"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="" <?= (isset($edit_data['category']) && $edit_data['category']==='') ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                                <option value="cuci_pakaian" <?= (isset($edit_data['category']) && $edit_data['category']==='cuci_pakaian') ? 'selected' : '' ?>>Cuci Pakaian</option>
                                <option value="cuci_sepatu_tas_helm" <?= (isset($edit_data['category']) && $edit_data['category']==='cuci_sepatu_tas_helm') ? 'selected' : '' ?>>Cuci Sepatu / Tas / Helm</option>
                                <option value="setrika" <?= (isset($edit_data['category']) && $edit_data['category']==='setrika') ? 'selected' : '' ?>>Setrika</option>
                                <option value="lainnya" <?= (isset($edit_data['category']) && $edit_data['category']==='lainnya') ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Layanan</label>
                            <input type="text"
                                   name="image_url"
                                   class="form-control"
                                   placeholder="https://.../foto-layanan.jpg"
                                   value="<?= htmlspecialchars($edit_data['image_url'] ?? '') ?>">
                            <div class="form-text">URL gambar akan ditampilkan di daftar layanan.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Durasi <span class="text-danger">*</span></label>
                            <select name="durasi" class="form-select" required>
                                <option value="">-- Pilih Durasi --</option>
                                <?php
                                $durasi_options = ['15 Menit', '30 Menit', '45 Menit', '60 Menit', '90 Menit', '1 Hari', '2 Hari', '3 Hari'];
                                foreach ($durasi_options as $dur):
                                    $selected = (isset($edit_data['durasi']) && $edit_data['durasi'] === $dur) ? 'selected' : '';
                                ?>
                                <option value="<?= $dur ?>" <?= $selected ?>><?= $dur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-3 py-2 fw-600">
                                <?php if ($action === 'edit'): ?>
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                                <?php else: ?>
                                <i class="bi bi-plus-lg me-2"></i>Tambah Layanan
                                <?php endif; ?>
                            </button>
                            <?php if ($action === 'edit'): ?>
                            <a href="layanan.php" class="btn btn-outline-secondary rounded-3 py-2">
                                <i class="bi bi-x-lg me-2"></i>Batal
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="col-lg-8">
                <div class="data-table-wrap">
                    <div class="table-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5><i class="bi bi-table me-2 text-primary"></i>Data Layanan</h5>
                            <span class="badge bg-primary rounded-pill">
                                <?= $all_layanan ? mysqli_num_rows($all_layanan) : 0 ?> data
                            </span>
                        </div>
                        <form method="GET" class="d-flex align-items-center" style="gap:.5rem">
                            <input type="hidden" name="action" value="list">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama layanan" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">Semua Kategori</option>
                                <option value="cuci_pakaian" <?= (isset($_GET['category']) && $_GET['category']==='cuci_pakaian') ? 'selected' : '' ?>>Cuci Pakaian</option>
                                <option value="cuci_sepatu_tas_helm" <?= (isset($_GET['category']) && $_GET['category']==='cuci_sepatu_tas_helm') ? 'selected' : '' ?>>Cuci Sepatu / Tas / Helm</option>
                                <option value="setrika" <?= (isset($_GET['category']) && $_GET['category']==='setrika') ? 'selected' : '' ?>>Setrika</option>
                            </select>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
                            <a href="layanan.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Gambar</th>
                                    <th>Nama Layanan</th>
                                    <th>Harga</th>
                                    <th>Durasi</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($all_layanan && mysqli_num_rows($all_layanan) > 0):
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($all_layanan)):
                                ?>
                                <tr>
                                    <td class="text-muted fw-500"><?= $no++ ?></td>
                                    <td>
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['nama_layanan']) ?>" class="img-thumbnail" style="width:70px;height:50px;object-fit:cover;border-radius:12px">
                                        <?php else: ?>
                                            <div class="bg-light text-center text-muted" style="width:70px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:0.8rem;">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-700"><?= htmlspecialchars($row['nama_layanan']) ?></span>
                                        <?php if (!empty($row['category'])): ?>
                                            <?php
                                                $labels = [
                                                    'cuci_pakaian' => 'Cuci Pakaian',
                                                    'cuci_sepatu_tas_helm' => 'Cuci Sepatu/Tas/Helm',
                                                    'setrika' => 'Setrika',
                                                    'lainnya' => 'Lainnya'
                                                ];
                                                $catLabel = $labels[$row['category']] ?? ucfirst($row['category']);
                                            ?>
                                            <div><small class="text-muted">Kategori: <span class="badge bg-secondary"><?= htmlspecialchars($catLabel) ?></span></small></div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['category']) && $row['category'] === 'setrika'): ?>
                                            <div><small class="text-muted">Min. 3kg untuk paket setrika (jika dropoff dihitung minimal 3kg)</small></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6 fw-600">
                                            <?= formatRupiah($row['harga']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= htmlspecialchars($row['durasi']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="layanan.php?action=edit&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-warning me-1"
                                           style="border-radius:8px"
                                           title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="layanan.php?action=hapus&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           style="border-radius:8px"
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus layanan \'<?= htmlspecialchars($row['nama_layanan']) ?>\'?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                        Belum ada data layanan.<br>
                                        <small>Tambahkan layanan pertama kamu!</small>
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
