<?php
session_start();
require_once 'includes/koneksi.php';

if (!isset($_SESSION['member_logged_in']) || $_SESSION['member_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = intval($_SESSION['member_id']);
$memberName = htmlspecialchars($_SESSION['member_name'] ?? $_SESSION['member_username'] ?? 'Member');
$serviceList = query($koneksi, "SELECT * FROM layanan ORDER BY id ASC");
$categoryResult = query($koneksi, "SELECT DISTINCT category FROM layanan WHERE category <> '' ORDER BY category ASC");
$categoryList = [];
if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoryList[] = $row['category'];
    }
}
$promoList = query($koneksi, "SELECT * FROM promo ORDER BY id DESC LIMIT 4");
$orderHistory = query($koneksi, "SELECT o.*, l.nama_layanan, loc.nama_lokasi FROM orders o LEFT JOIN layanan l ON o.layanan_id = l.id LEFT JOIN lokasi loc ON o.lokasi_id = loc.id WHERE o.member_id = $memberId ORDER BY o.created_at DESC");

$locationResult = query($koneksi, "SELECT * FROM lokasi ORDER BY id ASC");
$locationList = [];
if ($locationResult && mysqli_num_rows($locationResult) > 0) {
    while ($row = mysqli_fetch_assoc($locationResult)) {
        $locationList[] = $row;
    }
}

$noLocations = count($locationList) === 0;

// Handler to insert sample lokasi data when table is empty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_sample_locations') {
    // sample entries include latitude/longitude so distance can be calculated
    $samples = [
        // coordinates near Yogyakarta as realistic examples
        ['Cabang Sample A', 'Jl. Contoh No.1, Kota', '', 'https://www.google.com/maps', -7.782885, 110.360776],
        ['Cabang Sample B', 'Jl. Contoh No.2, Kota', '', 'https://www.google.com/maps', -7.795582, 110.369489]
    ];
    foreach ($samples as $s) {
        $name = esc($koneksi, $s[0]);
        $alamat = esc($koneksi, $s[1]);
        $image = esc($koneksi, $s[2]);
        $maps = esc($koneksi, $s[3]);
        $lat = floatval($s[4]);
        $lng = floatval($s[5]);
        query($koneksi, "INSERT IGNORE INTO lokasi (nama_lokasi, alamat, image_url, maps_url, latitude, longitude) VALUES ('$name', '$alamat', '$image', '$maps', $lat, $lng)");
    }
    header('Location: member.php');
    exit;
}

$error = '';
$success = '';
$orderSuccess = false;
$whatsappLink = '';
$sql = '';
$orderLocationId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    $layananId = intval($_POST['layanan_id'] ?? 0);
    $deliveryType = $_POST['delivery_type'] ?? '';
    $paymentMethodOverride = trim($_POST['payment_method_override'] ?? '');
    $paymentMethod = $paymentMethodOverride !== '' ? $paymentMethodOverride : ($_POST['payment_method'] ?? '');
    $locationId = intval($_POST['location_id'] ?? 0);
    $pickupBranchId = intval($_POST['pickup_branch_id'] ?? 0);
    $pickupDistance = floatval($_POST['pickup_distance'] ?? 0);
    $weightKg = floatval($_POST['weight_kg'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $soapAddonQty = max(0, min(10, intval($_POST['soap_addon_qty'] ?? 0)));
    $fragranceAddonQty = max(0, min(10, intval($_POST['fragrance_addon_qty'] ?? 0)));
    $soapAddon = $soapAddonQty > 0 ? 1 : 0;
    $fragranceAddon = $fragranceAddonQty > 0 ? 1 : 0;

    if ($layananId <= 0) {
        $error = 'Pilih layanan laundry terlebih dahulu.';
    } elseif (!in_array($deliveryType, ['dropoff', 'pickup', 'selfservice'], true)) {
        $error = 'Pilih metode pengantaran.';
    } elseif ($deliveryType === 'selfservice') {
        $paymentMethod = 'branch';
    } elseif (!in_array($paymentMethod, ['qris', 'transfer', 'cash'], true)) {
        $error = 'Pilih metode pembayaran.';
    } elseif ($deliveryType === 'dropoff' && $locationId <= 0) {
        $error = 'Pilih cabang tujuan dropoff.';
    } elseif ($deliveryType === 'selfservice' && $locationId <= 0) {
        $error = 'Pilih cabang tujuan self-service.';
    } elseif ($deliveryType === 'pickup' && $pickupBranchId <= 0) {
        $error = 'Pilih cabang pickup yang akan digunakan.';
    } elseif ($deliveryType === 'pickup' && $pickupDistance <= 0) {
        $error = 'Jarak pickup harus terisi otomatis.';
    } elseif ($deliveryType === 'pickup' && $paymentMethod === 'cash') {
        $error = 'Pembayaran tunai hanya tersedia untuk dropoff sendiri ke cabang.';
    } elseif (in_array($paymentMethod, ['qris', 'transfer'], true) && (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE)) {
        $error = 'Unggah bukti pembayaran saat memilih QRIS atau Transfer Bank.';
    } else {
        $serviceResult = query($koneksi, "SELECT * FROM layanan WHERE id = $layananId LIMIT 1");
        if (!$serviceResult || mysqli_num_rows($serviceResult) !== 1) {
            $error = 'Layanan tidak ditemukan.';
        } else {
            $service = mysqli_fetch_assoc($serviceResult);
            $serviceIsSelfService = stripos($service['nama_layanan'], 'self service') !== false;
            if ($serviceIsSelfService) {
                $deliveryType = 'selfservice';
                $paymentMethod = 'branch';
            }

            $serviceCategory = !empty($service['category']) ? $service['category'] : 'lainnya';
            if ($serviceCategory === 'cuci_sepatu_tas_helm') {
                $soapAddonQty = 0;
                $fragranceAddonQty = 0;
                $soapAddon = 0;
                $fragranceAddon = 0;
            }

            $isSetrika = stripos($service['nama_layanan'], 'setrika') !== false;
            if ($isSetrika && $weightKg <= 0) {
                $error = 'Masukkan berat pakaian untuk paket setrika.';
            }

            if ($error === '' && in_array($paymentMethod, ['qris', 'transfer'], true)) {
                $uploadError = $_FILES['payment_proof']['error'] ?? UPLOAD_ERR_NO_FILE;
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $error = 'Terjadi kesalahan saat mengunggah bukti pembayaran.';
                } else {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                    $originalName = $_FILES['payment_proof']['name'];
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions, true)) {
                        $error = 'Tipe file bukti pembayaran harus JPG, PNG, atau PDF.';
                    } else {
                        $uploadDir = __DIR__ . '/uploads/payment_proof';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $safeName = 'payment_' . $memberId . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
                        $targetPath = $uploadDir . '/' . $safeName;
                        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
                            $error = 'Gagal menyimpan bukti pembayaran. Silakan coba lagi.';
                        } else {
                            $paymentProofPath = 'uploads/payment_proof/' . $safeName;
                        }
                    }
                }
            }

            if ($error === '') {
                $pickupDistance = round($pickupDistance, 1);
                $deliveryFee = $deliveryType === 'pickup' ? ($pickupDistance * 4000) : 0;
                $extraFee = ($soapAddonQty * 2000) + ($fragranceAddonQty * 1000);

                if ($isSetrika) {
                    // Harga layanan setrika dianggap per-kg. Untuk dropoff, minimal dihitung 3kg.
                    $billedWeight = $weightKg;
                    if ($deliveryType === 'dropoff') {
                        $billedWeight = max(3.0, $billedWeight);
                    }
                    $serviceTotal = intval(round(intval($service['harga']) * $billedWeight)) * $quantity;
                } else {
                    $serviceTotal = intval($service['harga']) * $quantity;
                }

                $totalPrice = $serviceTotal + $deliveryFee + $extraFee;

                if (in_array($paymentMethod, ['qris', 'transfer'], true)) {
                    $status = 'Menunggu Verifikasi Pembayaran';
                } else {
                    $status = $deliveryType === 'pickup' ? 'Menunggu Pembayaran' : 'Menunggu Pembayaran di Cabang';
                }
                $createdAt = date('Y-m-d H:i:s');
                $orderLocationId = $deliveryType === 'pickup' ? $pickupBranchId : $locationId;
                $paymentProofSql = $paymentProofPath ? "'" . esc($koneksi, $paymentProofPath) . "'" : 'NULL';

                $sql = "INSERT INTO orders (member_id, layanan_id, quantity, lokasi_id, delivery_type, pickup_distance, payment_method, delivery_fee, total_price, status, soap_addon, fragrance_addon, soap_addon_qty, fragrance_addon_qty, payment_proof, created_at) VALUES ($memberId, $layananId, $quantity, $orderLocationId, '$deliveryType', $pickupDistance, '$paymentMethod', $deliveryFee, $totalPrice, '$status', $soapAddon, $fragranceAddon, $soapAddonQty, $fragranceAddonQty, $paymentProofSql, '$createdAt')";
            }
            if ($error === '') {
                if (query($koneksi, $sql)) {
                    $orderSuccess = true;
                    if (in_array($paymentMethod, ['qris', 'transfer'], true)) {
                        $success = 'Pesanan Anda berhasil dibuat. Bukti pembayaran telah diterima, menunggu verifikasi.';
                    } else {
                        $success = 'Pesanan Anda berhasil dibuat. Silakan mampir ke cabang jika pembayaran dilakukan di lokasi.';
                    }
                    $orderId = mysqli_insert_id($koneksi);

                    $branchResult = query($koneksi, "SELECT nama_lokasi FROM lokasi WHERE id = $orderLocationId LIMIT 1");
                    $branchName = ($branchResult && mysqli_num_rows($branchResult) === 1) ? mysqli_fetch_assoc($branchResult)['nama_lokasi'] : 'Cabang';

                    $deliveryLabel = $deliveryType === 'pickup' ? "Pickup ke $branchName ($pickupDistance km)" : ($deliveryType === 'selfservice' ? "Self-Service ke $branchName" : "Dropoff ke $branchName");
                    $paymentLabel = $paymentMethod === 'branch' ? 'Bayar di Cabang' : strtoupper($paymentMethod);
                    $messageText = "Halo, saya telah melakukan order laundry dengan nomor pesanan #$orderId. Layanan: {$service['nama_layanan']}. Metode pengantaran: $deliveryLabel. Pembayaran: $paymentLabel. Total: Rp " . number_format($totalPrice, 0, ',', '.');
                    $message = rawurlencode($messageText);
                    $whatsappLink = "https://wa.me/+6282147189896?text=$message";

                    if ($deliveryType === 'selfservice') {
                        $mapsUrl = '';
                        $mapsResult = query($koneksi, "SELECT maps_url, latitude, longitude FROM lokasi WHERE id = $orderLocationId LIMIT 1");
                        if ($mapsResult && mysqli_num_rows($mapsResult) === 1) {
                            $mapsRow = mysqli_fetch_assoc($mapsResult);
                            $mapsUrl = $mapsRow['maps_url'];
                            if (empty($mapsUrl) && floatval($mapsRow['latitude']) !== 0 && floatval($mapsRow['longitude']) !== 0) {
                                $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapsRow['latitude'] . ',' . $mapsRow['longitude'];
                            }
                        }
                        if ($mapsUrl) {
                            header('Location: ' . $mapsUrl);
                            exit;
                        }
                    }

                    $orderHistory = query($koneksi, "SELECT o.*, l.nama_layanan, loc.nama_lokasi FROM orders o LEFT JOIN layanan l ON o.layanan_id = l.id LEFT JOIN lokasi loc ON o.lokasi_id = loc.id WHERE o.member_id = $memberId ORDER BY o.created_at DESC");
                } else {
                    $error = 'Gagal membuat pesanan. Silakan coba lagi.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Area - LaundryKoin Sejam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo.svg" alt="Laundry Sejam" class="logo-img me-2">
                LaundryKoin
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Halo, <?= $memberName ?></span>
                <a href="member_logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row gy-4">
            <div class="col-lg-8">
                <div class="card p-4 shadow-sm">
                    <h2 class="h4 mb-3">Selamat datang, <?= $memberName ?>!</h2>
                    <p class="text-muted">Pesan layanan laundry dengan mudah, pilih cabang atau pickup, lalu pilih metode pembayaran.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4 shadow-sm bg-primary text-white">
                    <h3 class="h5">Keuntungan Member</h3>
                    <ul class="mb-0 member-benefits">
                        <li>Diskon promo eksklusif</li>
                        <li>Akses cepat ke order dan histori</li>
                        <li>Notifikasi kondisi antrian cabang</li>
                    </ul>
                </div>
            </div>
        </div>

        <section class="mt-5">
            <form id="order-form" method="POST" action="member.php" enctype="multipart/form-data">
            <div class="row gy-4">
                <div class="col-lg-7">
                    <div class="card p-4 shadow-sm mb-4">
                        <h4 class="mb-3">Order Laundry Baru</h4>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                            <input type="hidden" name="action" value="place_order">
                            <input type="hidden" name="payment_method_override" id="payment_method_override" value="<?= htmlspecialchars($_POST['payment_method_override'] ?? '') ?>">
                            <input type="hidden" name="quantity" id="order_quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? 1) ?>">
                            <div class="mb-3">
                                <label class="form-label">Pilih Kategori Layanan</label>
                                <div class="btn-group btn-group-sm d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Kategori layanan">
                                    <button type="button" class="btn btn-outline-secondary active service-category-btn" data-category="">Semua</button>
                                    <?php foreach ($categoryList as $category):
                                        $label = ucfirst(str_replace(['_', '-'], ' ', $category));
                                    ?>
                                        <button type="button" class="btn btn-outline-secondary service-category-btn" data-category="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($label) ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="service-options">
                                    <?php if ($serviceList && mysqli_num_rows($serviceList) > 0): ?>
                                        <?php mysqli_data_seek($serviceList, 0); ?>
                                        <?php while ($service = mysqli_fetch_assoc($serviceList)): ?>
                                            <?php $isSelfService = preg_match('/self[-\s]?service/i', $service['nama_layanan']) ? '1' : '0';
                                            $isSetrika = preg_match('/setrika/i', $service['nama_layanan']) ? '1' : '0';
                                            $serviceCategory = !empty($service['category']) ? $service['category'] : 'lainnya';
                                            $checked = (isset($_POST['layanan_id']) && intval($_POST['layanan_id']) === intval($service['id'])); ?>
                                            <label class="service-card<?= $checked ? ' active' : '' ?>" data-category="<?= htmlspecialchars($serviceCategory) ?>">
                                                <input type="radio" name="layanan_id" value="<?= $service['id'] ?>" data-selfservice="<?= $isSelfService ?>" data-is-setrika="<?= $isSetrika ?>" data-price="<?= $service['harga'] ?>" data-category="<?= htmlspecialchars($serviceCategory) ?>" <?= $checked ? 'checked' : '' ?> required onchange="updateDelivery()">
                                                <div class="service-card-header">
                                                    <div>
                                                        <div class="service-card-title"><?= htmlspecialchars($service['nama_layanan']) ?></div>
                                                        <div class="service-card-category badge bg-light text-dark mt-1"><?= htmlspecialchars(ucfirst(str_replace(['_', '-'], ' ', $serviceCategory))) ?></div>
                                                    </div>
                                                    <div class="service-card-badge badge bg-secondary text-white"><?= htmlspecialchars($service['durasi']) ?></div>
                                                </div>
                                                <div class="service-card-body">
                                                    <div class="service-card-price fw-semibold"><?= formatRupiah($service['harga']) ?></div>
                                                    <div class="service-card-meta mt-2">
                                                        <?php if ($isSelfService): ?><span class="badge bg-warning text-dark">Self-Service</span><?php endif; ?>
                                                        <?php if ($isSetrika): ?><span class="badge bg-info text-dark">Setrika</span><?php endif; ?>
                                                    </div>
                                                </div>
                                            </label>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-muted">Belum ada layanan tersedia.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3" id="delivery-choice-section">
                                <label class="form-label">Metode Pengantaran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_type" id="dropoff" value="dropoff" <?= (isset($_POST['delivery_type']) && $_POST['delivery_type'] === 'dropoff') ? 'checked' : '' ?> onchange="updateDelivery()" required>
                                    <label class="form-check-label" for="dropoff">Dropoff - Antar sendiri ke cabang (gratis)</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="delivery_type" id="pickup" value="pickup" <?= (isset($_POST['delivery_type']) && $_POST['delivery_type'] === 'pickup') ? 'checked' : '' ?> onchange="updateDelivery()">
                                    <label class="form-check-label" for="pickup">Pickup - Ambil pakaian di lokasi Anda (Rp 4.000 / km)</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="delivery_type" id="selfservice" value="selfservice" <?= (isset($_POST['delivery_type']) && $_POST['delivery_type'] === 'selfservice') ? 'checked' : '' ?> onchange="updateDelivery()">
                                    <label class="form-check-label" for="selfservice">Self-Service - Bawa sendiri ke cabang dan bayar di lokasi</label>
                                </div>
                            </div>

                            <div class="mb-3 delivery-field" id="dropoff-fields" style="display: none;">
                                <label class="form-label">Cabang Tujuan Dropoff</label>
                                <?php if ($noLocations): ?>
                                    <div class="alert alert-warning">Belum ada cabang yang terdaftar.</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="submitSampleLocationsForm()">Tambahkan contoh cabang</button>
                                <?php else: ?>
                                    <select name="location_id" class="form-select">
                                        <option value="">-- Pilih cabang --</option>
                                        <?php if (count($locationList) > 0): ?>
                                            <?php foreach ($locationList as $location): ?>
                                                <option value="<?= $location['id'] ?>" <?= (isset($_POST['location_id']) && intval($_POST['location_id']) === intval($location['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($location['nama_lokasi']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 delivery-field" id="selfservice-fields" style="display: none;">
                                <label class="form-label">Cabang Self-Service</label>
                                <?php if ($noLocations): ?>
                                    <div class="alert alert-warning">Belum ada cabang yang terdaftar.</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="submitSampleLocationsForm()">Tambahkan contoh cabang</button>
                                <?php else: ?>
                                    <select name="location_id" class="form-select">
                                        <option value="">-- Pilih cabang --</option>
                                        <?php if (count($locationList) > 0): ?>
                                            <?php foreach ($locationList as $location): ?>
                                                <option value="<?= $location['id'] ?>" <?= (isset($_POST['location_id']) && intval($_POST['location_id']) === intval($location['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($location['nama_lokasi']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Pilih cabang yang ingin Anda gunakan untuk self-service. Pembayaran dilakukan langsung di lokasi.</div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 delivery-field" id="pickup-fields" style="display: none;">
                                <label class="form-label">Cabang Pickup</label>
                                <?php if ($noLocations): ?>
                                    <div class="alert alert-warning">Belum ada cabang pickup terdaftar.</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="submitSampleLocationsForm()">Tambahkan contoh cabang</button>
                                <?php else: ?>
                                    <select name="pickup_branch_id" id="pickup_branch_id" class="form-select mb-3" onchange="updatePickupDistance()">
                                        <option value="">-- Pilih cabang pickup --</option>
                                        <?php if (count($locationList) > 0): ?>
                                            <?php foreach ($locationList as $location): ?>
                                                <option value="<?= $location['id'] ?>" data-lat="<?= htmlspecialchars($location['latitude'] ?? '0') ?>" data-lng="<?= htmlspecialchars($location['longitude'] ?? '0') ?>" <?= (isset($_POST['pickup_branch_id']) && intval($_POST['pickup_branch_id']) === intval($location['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($location['nama_lokasi']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                <?php endif; ?>

                                <div class="input-group mb-3">
                                    <button type="button" class="btn btn-outline-secondary" onclick="detectLocation()">Gunakan Lokasi Saya</button>
                                    <span class="input-group-text">Jarak</span>
                                    <input type="text" readonly id="pickup_distance" name="pickup_distance" class="form-control" placeholder="Jarak otomatis" value="<?= htmlspecialchars($_POST['pickup_distance'] ?? '') ?>">
                                </div>
                                <div id="pickup_distance_help" class="form-text">Tekan tombol untuk mengisi jarak otomatis berdasarkan cabang pickup dan lokasi Anda.</div>
                            </div>

                            <div class="mb-3" id="addon-quantity-section">
                                <label class="form-label">Tambahan Produk (opsional)</label>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="soap_addon_qty">Sabun Liquid 80ml</label>
                                        <input class="form-control" type="number" name="soap_addon_qty" id="soap_addon_qty" min="0" max="10" step="1" value="<?= htmlspecialchars($_POST['soap_addon_qty'] ?? '0') ?>">
                                        <div class="form-text">0 untuk tidak ada, 1–10 item jika ingin menambahkan.</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="fragrance_addon_qty">Pewangi</label>
                                        <input class="form-control" type="number" name="fragrance_addon_qty" id="fragrance_addon_qty" min="0" max="10" step="1" value="<?= htmlspecialchars($_POST['fragrance_addon_qty'] ?? '0') ?>">
                                        <div class="form-text">0 untuk tidak ada, 1–10 item jika ingin menambahkan.</div>
                                    </div>
                                </div>
                                <div class="form-text">Tambahan produk hanya tersedia untuk layanan selain Cuci Sepatu / Tas / Helm.</div>
                            </div>
                            <div class="alert alert-warning d-none" id="addon-disabled-message">
                                Tambahan produk tidak tersedia untuk kategori Cuci Sepatu / Tas / Helm.
                            </div>

                            <div class="mb-3" id="setrika-weight-field" style="display: none;">
                                <label class="form-label">Berat (kg)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0.1" name="weight_kg" id="weight_kg" class="form-control" placeholder="Masukkan berat pakaian (kg)" value="<?= htmlspecialchars($_POST['weight_kg'] ?? '') ?>">
                                    <span class="input-group-text">kg</span>
                                </div>
                                <div class="form-text">Untuk paket setrika, minimal perhitungan pembayaran adalah 3kg pada metode Dropoff.</div>
                            </div>

                            <div class="alert alert-info">
                                <p class="mb-1"><strong>Catatan penting:</strong></p>
                                <p class="mb-0">Untuk layanan Dropoff, Dropoff Lipat, dan Self-Service, pemberitahuan pakaian selesai disesuaikan dengan antrian cabang yang Anda pilih.</p>
                                <p class="mb-0"><strong>Self-Service:</strong> Pembayaran dilakukan langsung di cabang saat Anda datang.</p>
                            </div>

                            <!-- Pesan Sekarang button kini ada di panel ringkasan pesanan -->
                        </div>
                    </div>

                <div class="col-lg-5">
                    <div class="card p-4 shadow-sm mb-4 order-panel">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="mb-1">Pesanan Saat Ini</h4>
                                <p class="text-muted small mb-0">Lihat ringkasan order sebelum checkout.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateOrderSummary()">Refresh</button>
                        </div>
                        <div class="order-item-card p-3 mb-4 rounded-4 shadow-sm bg-white">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div>
                                    <div class="fw-semibold" id="summary_service">-</div>
                                    <div class="text-muted small" id="summary_service_desc">Pilih layanan untuk melihat detail pesanan.</div>
                                    <div class="mt-2 d-flex gap-2 align-items-center flex-wrap">
                                        <span class="badge bg-light text-dark" id="summary_delivery_badge">Belum dipilih</span>
                                        <span class="badge bg-light text-dark" id="summary_payment_method_badge">Menunggu metode</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="h6 text-primary mb-1" id="summary_price">Rp 0</div>
                                    <div class="small text-muted" id="summary_weight_row" style="display:none;"><span id="summary_weight">0 kg</span></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Harga / paket</span>
                                <span class="fw-semibold" id="summary_unit_price">Rp 0</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3 summary-quantity-row">
                                <div class="text-muted">Jumlah Paket</div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Quantity controls">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeOrderQuantity(-1)">-</button>
                                    <button type="button" class="btn btn-outline-secondary disabled" type="button"><span id="summary_quantity">1</span></button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeOrderQuantity(1)">+</button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Biaya Pickup</span><span id="summary_pickup_fee">Rp 0</span></div>
                            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Tambahan Produk</span><span id="summary_addons">Rp 0</span></div>
                            <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Total Estimasi</div>
                                    <div class="fw-semibold" id="summary_total">Rp 0</div>
                                </div>
                                <span class="badge bg-light text-dark" id="summary_payment_badge">Menunggu metode</span>
                            </div>
                            <div class="text-muted small mt-3" id="summary_note">Pilih layanan dan pengantaran untuk melihat total order.</div>
                        </div>
                        <div class="card p-3 border rounded-4" id="payment-method-section">
                            <div class="mb-2"><strong>Metode Pembayaran</strong></div>
                            <div class="d-flex flex-wrap gap-2" id="payment-method-badges">
                                <span class="badge rounded-pill bg-light text-dark" data-method="qris">QRIS</span>
                                <span class="badge rounded-pill bg-light text-dark" data-method="transfer">Transfer Bank</span>
                                <span class="badge rounded-pill bg-light text-dark" data-method="cash">Tunai</span>
                                <span class="badge rounded-pill bg-light text-dark" data-method="branch">Bayar di Cabang</span>
                            </div>
                            <div class="mt-3" id="payment-method-controls">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="qris" value="qris" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'qris') ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="qris">QRIS</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="transfer" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'transfer') ? 'checked' : '' ?> >
                                    <label class="form-check-label" for="transfer">Transfer Bank</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash') ? 'checked' : '' ?> >
                                    <label class="form-check-label" for="cash">Tunai (hanya untuk dropoff sendiri)</label>
                                </div>
                            </div>
                            <div class="mb-3 mt-3" id="payment-proof-section" style="display:none;">
                                <label class="form-label">Unggah Bukti Pembayaran</label>
                                <input class="form-control" type="file" name="payment_proof" id="payment_proof" accept=".jpg,.jpeg,.png,.pdf">
                                <div class="form-text">Unggah bukti transfer atau QRIS agar pesanan dapat diproses.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Pesan Sekarang</button>
                            <?php if ($orderSuccess && $whatsappLink && (($deliveryType ?? '') === 'pickup') && in_array(($paymentMethod ?? ''), ['qris', 'transfer'], true)): ?>
                                <a href="<?= $whatsappLink ?>" target="_blank" class="btn btn-success w-100 mt-3">
                                    <i class="bi bi-whatsapp me-2"></i>Notifikasi Pengambilan via WhatsApp
                                </a>
                            <?php endif; ?>
                            <div class="mt-3 text-muted small">Metode pembayaran akan disesuaikan saat Anda memilih opsi di form.</div>
                        </div>
                    </div>
                    </form>
                    <form id="sample-location-form" method="POST" action="member.php" style="display:none;">
                        <input type="hidden" name="action" value="add_sample_locations">
                    </form>
                    <div class="card p-4 shadow-sm">
                        <h4 class="mb-3">Panduan Pembayaran</h4>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3"><strong>QRIS:</strong> Scan kode QR pada kasir atau screenshot untuk pembayaran digital.</li>
                            <li class="mb-3"><strong>Transfer Bank:</strong> Gunakan rekening BCA 123-456-7890 a.n. Laundry KOIN Sejam.</li>
                            <li class="mb-0"><strong>Tunai:</strong> Hanya valid untuk dropoff sendiri ke cabang.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Riwayat Pesanan</h3>
                <a href="index.php#services" class="text-primary">Lihat layanan</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Layanan</th>
                            <th>Qty</th>
                            <th>Pengantaran</th>
                            <th>Pembayaran</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orderHistory && mysqli_num_rows($orderHistory) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($orderHistory)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                    <td>
                                        <?php
                                        $extras = [];
                                        if (!empty($order['soap_addon_qty'])) $extras[] = 'Sabun x' . intval($order['soap_addon_qty']);
                                        if (!empty($order['fragrance_addon_qty'])) $extras[] = 'Pewangi x' . intval($order['fragrance_addon_qty']);
                                        echo htmlspecialchars($order['nama_layanan'] ?? 'Tidak diketahui');
                                        if (!empty($extras)) {
                                            echo ' + ' . htmlspecialchars(implode(', ', $extras));
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($order['quantity'] ?? 1) ?></td>
                                    <td>
                                        <?php
                                        if ($order['delivery_type'] === 'pickup') {
                                            echo 'Pickup (' . htmlspecialchars($order['pickup_distance']) . ' km)';
                                        } elseif ($order['delivery_type'] === 'selfservice') {
                                            echo 'Self-Service ke ' . htmlspecialchars($order['nama_lokasi'] ?: 'Cabang');
                                        } else {
                                            echo 'Dropoff ke ' . htmlspecialchars($order['nama_lokasi'] ?: 'Cabang');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($order['payment_method'] === 'branch'): ?>
                                            Bayar di Cabang
                                        <?php else: ?>
                                            <?= ucfirst(htmlspecialchars($order['payment_method'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatRupiah($order['total_price']) ?></td>
                                    <td><?= htmlspecialchars($order['status']) ?></td>
                                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($order['created_at']))) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let customerLatitude = null;
        let customerLongitude = null;

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function getSelectedServiceRadio() {
            let selected = document.querySelector('input[name="layanan_id"]:checked');
            if (selected && selected.closest('.service-card')?.style.display === 'none') {
                selected.checked = false;
                selected = null;
            }
            if (!selected) {
                const firstVisibleCard = Array.from(document.querySelectorAll('.service-card')).find((card) => card.offsetParent !== null);
                if (firstVisibleCard) {
                    const radio = firstVisibleCard.querySelector('input[name="layanan_id"]');
                    if (radio) {
                        radio.checked = true;
                        document.querySelectorAll('.service-card').forEach((card) => card.classList.remove('active'));
                        firstVisibleCard.classList.add('active');
                        selected = radio;
                    }
                }
            }
            return selected;
        }

        function updateOrderSummary() {
            const selectedRadio = getSelectedServiceRadio();
            const summaryService = document.getElementById('summary_service');
            const summaryServiceDesc = document.getElementById('summary_service_desc');
            const summaryPrice = document.getElementById('summary_price');
            const summaryWeightRow = document.getElementById('summary_weight_row');
            const summaryWeight = document.getElementById('summary_weight');
            const summaryUnitPrice = document.getElementById('summary_unit_price');
            const summaryPickupFee = document.getElementById('summary_pickup_fee');
            const summaryAddons = document.getElementById('summary_addons');
            const summaryTotal = document.getElementById('summary_total');
            const summaryNote = document.getElementById('summary_note');
            const paymentBadge = document.getElementById('summary_payment_badge');
            const deliveryBadge = document.getElementById('summary_delivery_badge');
            const paymentMethodBadge = document.getElementById('summary_payment_method_badge');

            const selfserviceSelected = document.getElementById('selfservice')?.checked;
            const paymentMethodInput = document.querySelector('input[name="payment_method"]:checked')?.value || '';
            const paymentMethod = selfserviceSelected ? 'branch' : paymentMethodInput;
            const badges = document.querySelectorAll('#payment-method-badges [data-method]');
            badges.forEach((badge) => {
                badge.classList.toggle('bg-primary', badge.dataset.method === paymentMethod);
                badge.classList.toggle('text-white', badge.dataset.method === paymentMethod);
                badge.classList.toggle('bg-light', badge.dataset.method !== paymentMethod);
                badge.classList.toggle('text-dark', badge.dataset.method !== paymentMethod);
            });

            const quantity = parseInt(document.getElementById('summary_quantity').textContent || '1', 10);
            document.getElementById('order_quantity').value = quantity;
            const deliveryType = document.getElementById('dropoff').checked ? 'Dropoff' : document.getElementById('pickup').checked ? 'Pickup' : document.getElementById('selfservice').checked ? 'Self-Service' : 'Belum dipilih';

            if (!selectedRadio || !selectedRadio.value) {
                summaryService.textContent = '-';
                summaryServiceDesc.textContent = 'Pilih layanan untuk melihat detail pesanan.';
                summaryUnitPrice.textContent = 'Rp 0';
                summaryPrice.textContent = 'Rp 0';
                summaryPickupFee.textContent = 'Rp 0';
                summaryAddons.textContent = 'Rp 0';
                summaryTotal.textContent = 'Rp 0';
                summaryWeightRow.style.display = 'none';
                summaryNote.textContent = 'Pilih layanan dan pengantaran untuk melihat total order.';
                deliveryBadge.textContent = 'Belum dipilih';
                paymentMethodBadge.textContent = 'Menunggu metode';
                paymentBadge.textContent = 'Menunggu metode';
                return;
            }

            const serviceName = selectedRadio.closest('label')?.querySelector('.service-card-title')?.textContent.trim() || '';
            const basePrice = parseInt(selectedRadio.dataset.price || '0', 10);
            const isSetrika = selectedRadio.dataset.isSetrika === '1';
            const weightKg = parseFloat(document.getElementById('weight_kg').value || '0');
            const soapQty = parseInt(document.getElementById('soap_addon_qty')?.value || '0', 10) || 0;
            const fragranceQty = parseInt(document.getElementById('fragrance_addon_qty')?.value || '0', 10) || 0;
            const pickupFee = document.getElementById('pickup').checked ? (parseFloat(document.getElementById('pickup_distance').value || '0') * 4000) : 0;
            const addonTotal = (soapQty * 2000) + (fragranceQty * 1000);

            let serviceTotal = basePrice * quantity;
            if (isSetrika) {
                summaryWeightRow.style.display = 'flex';
                const billedWeight = document.getElementById('dropoff').checked ? Math.max(3, isNaN(weightKg) ? 0 : weightKg) : (isNaN(weightKg) ? 0 : weightKg);
                summaryWeight.textContent = billedWeight.toFixed(1) + ' kg';
                serviceTotal = basePrice * billedWeight * quantity;
                summaryServiceDesc.textContent = `Setrika per ${quantity} paket`;
            } else {
                summaryWeightRow.style.display = 'none';
                summaryServiceDesc.textContent = `${quantity} paket layanan`;
            }

            const total = serviceTotal + pickupFee + addonTotal;
            summaryService.textContent = serviceName;
            summaryUnitPrice.textContent = formatRupiah(basePrice);
            summaryPrice.textContent = formatRupiah(serviceTotal || 0);
            summaryPickupFee.textContent = formatRupiah(pickupFee || 0);
            summaryAddons.textContent = formatRupiah(addonTotal || 0);
            summaryTotal.textContent = formatRupiah(total || 0);
            deliveryBadge.textContent = deliveryType;
            paymentMethodBadge.textContent = paymentMethod
                ? (paymentMethod === 'cash' ? 'Tunai' : (paymentMethod === 'branch' ? 'Bayar di Cabang' : paymentMethod.toUpperCase()))
                : 'Menunggu metode';
            summaryNote.textContent = paymentMethod
                ? (paymentMethod === 'branch' ? 'Pembayaran dilakukan di cabang ketika layanan selesai.' : `Metode pembayaran: ${paymentMethod === 'cash' ? 'Tunai' : paymentMethod.toUpperCase()}`)
                : 'Pilih metode pembayaran untuk melanjutkan.';
            paymentBadge.textContent = paymentMethod
                ? (paymentMethod === 'cash' ? 'Tunai' : (paymentMethod === 'branch' ? 'Bayar di Cabang' : paymentMethod.toUpperCase()))
                : 'Menunggu metode';
        }

        function changeOrderQuantity(delta) {
            const quantityEl = document.getElementById('summary_quantity');
            let quantity = parseInt(quantityEl.textContent || '1', 10);
            quantity = Math.max(1, quantity + delta);
            quantityEl.textContent = quantity;
            document.getElementById('order_quantity').value = quantity;
            updateOrderSummary();
        }

        function updateDelivery() {
            const selectedRadio = document.querySelector('input[name="layanan_id"]:checked');
            const serviceIsSelf = selectedRadio?.dataset.selfservice === '1';
            const serviceIsSetrika = selectedRadio?.dataset.isSetrika === '1';
            const dropoffChecked = document.getElementById('dropoff').checked;
            const pickupChecked = document.getElementById('pickup').checked;
            const selfserviceChecked = document.getElementById('selfservice').checked;
            const deliverySection = document.getElementById('delivery-choice-section');
            const paymentSection = document.getElementById('payment-method-section');
            const cashInput = document.getElementById('cash');
            const qrisInput = document.getElementById('qris');
            const transferInput = document.getElementById('transfer');
            const paymentOverride = document.getElementById('payment_method_override');

            if (serviceIsSelf) {
                deliverySection.style.display = 'none';
                document.getElementById('dropoff').checked = false;
                document.getElementById('pickup').checked = false;
                document.getElementById('selfservice').checked = true;
                document.getElementById('dropoff').disabled = true;
                document.getElementById('pickup').disabled = true;
                document.getElementById('selfservice').disabled = false;
                document.getElementById('dropoff-fields').style.display = 'none';
                document.getElementById('pickup-fields').style.display = 'none';
                document.getElementById('selfservice-fields').style.display = 'block';
                paymentSection.style.display = 'none';
                qrisInput.disabled = true;
                transferInput.disabled = true;
                cashInput.disabled = true;
                qrisInput.checked = false;
                transferInput.checked = false;
                cashInput.checked = false;
                if (paymentOverride) {
                    paymentOverride.value = 'branch';
                }
                const weightField = document.getElementById('setrika-weight-field');
                if (weightField) weightField.style.display = 'none';
            } else {
                deliverySection.style.display = 'block';
                document.getElementById('dropoff').disabled = false;
                document.getElementById('pickup').disabled = false;
                document.getElementById('selfservice').disabled = false;
                document.getElementById('dropoff-fields').style.display = dropoffChecked ? 'block' : 'none';
                document.getElementById('pickup-fields').style.display = pickupChecked ? 'block' : 'none';
                document.getElementById('selfservice-fields').style.display = selfserviceChecked ? 'block' : 'none';
                paymentSection.style.display = 'block';
                qrisInput.disabled = false;
                transferInput.disabled = false;
                cashInput.disabled = !dropoffChecked;
                if (!dropoffChecked && cashInput.checked) {
                    cashInput.checked = false;
                }
                if (paymentOverride) {
                    paymentOverride.value = '';
                }
                const weightField = document.getElementById('setrika-weight-field');
                if (weightField) weightField.style.display = serviceIsSetrika ? 'block' : 'none';
                if (pickupChecked) {
                    updatePickupDistance();
                }
            }
            updateAddOnAvailability();
            updateOrderSummary();
        }

        function detectLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation tidak didukung di browser ini.');
                return;
            }
            navigator.geolocation.getCurrentPosition((position) => {
                customerLatitude = position.coords.latitude;
                customerLongitude = position.coords.longitude;
                console.log('detectLocation: customerLatitude=', customerLatitude, 'customerLongitude=', customerLongitude);
                updatePickupDistance();
                const helpEl = document.getElementById('pickup_distance_help');
                if (helpEl) helpEl.textContent = 'Lokasi berhasil terdeteksi. Jarak otomatis telah diperbarui.';
                updateOrderSummary();
            }, (err) => {
                alert('Tidak dapat mendeteksi lokasi Anda: ' + err.message);
            });
        }

        function updatePickupDistance() {
            const branchSelect = document.getElementById('pickup_branch_id');
            const distanceInput = document.getElementById('pickup_distance');
            const helpText = document.getElementById('pickup_distance_help');

            if (!branchSelect) {
                if (distanceInput) distanceInput.value = '';
                if (helpText) helpText.textContent = 'Cabang pickup belum tersedia.';
                return;
            }

            const selectedOption = branchSelect.options[branchSelect.selectedIndex];
            const branchLat = parseFloat(selectedOption.dataset.lat || '0');
            const branchLng = parseFloat(selectedOption.dataset.lng || '0');

            console.log('updatePickupDistance: selectedOption=', selectedOption ? selectedOption.value : null, 'branchLat=', branchLat, 'branchLng=', branchLng, 'customerLat=', customerLatitude, 'customerLng=', customerLongitude);
            if (!selectedOption || !selectedOption.value) {
                if (distanceInput) distanceInput.value = '';
                if (helpText) helpText.textContent = 'Pilih cabang pickup terlebih dahulu.';
                return;
            }

            if (!branchLat || !branchLng) {
                if (distanceInput) distanceInput.value = '';
                if (helpText) helpText.textContent = 'Koordinat cabang belum tersedia. Silakan hubungi admin untuk memperbarui lokasi cabang.';
                return;
            }

            if (customerLatitude !== null && customerLongitude !== null) {
                const distance = calculateDistance(customerLatitude, customerLongitude, branchLat, branchLng);
                distanceInput.value = distance.toFixed(1);
                helpText.textContent = 'Jarak otomatis dihitung dari lokasi Anda ke cabang pickup.';
                updateOrderSummary();
            } else {
                distanceInput.value = '';
                helpText.textContent = 'Gunakan tombol Lokasi Saya untuk menghitung jarak pickup otomatis.';
            }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // km
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function toRad(value) {
            return value * Math.PI / 180;
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateDelivery();
            updateOrderSummary();

            document.querySelectorAll('input[name="layanan_id"]').forEach((input) => {
                input.addEventListener('change', () => {
                    document.querySelectorAll('.service-card').forEach((card) => card.classList.remove('active'));
                    input.closest('.service-card')?.classList.add('active');
                    updateDelivery();
                    updateAddOnAvailability();
                    updateOrderSummary();
                });
            });
            document.querySelectorAll('.service-category-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.service-category-btn').forEach((btn) => btn.classList.remove('active'));
                    button.classList.add('active');
                    filterServiceCategory(button.dataset.category);
                });
            });
            document.getElementById('weight_kg')?.addEventListener('input', updateOrderSummary);
            document.getElementById('soap_addon_qty')?.addEventListener('input', updateOrderSummary);
            document.getElementById('fragrance_addon_qty')?.addEventListener('input', updateOrderSummary);
            document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
                input.addEventListener('change', () => {
                    togglePaymentProofField();
                    updateOrderSummary();
                });
            });
            document.getElementById('pickup_branch_id')?.addEventListener('change', updateOrderSummary);
            document.getElementById('pickup_distance')?.addEventListener('input', updateOrderSummary);
            togglePaymentProofField();
            updateAddOnAvailability();
        });

        function togglePaymentProofField() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            const proofSection = document.getElementById('payment-proof-section');
            const proofInput = document.getElementById('payment_proof');
            if (!proofSection || !proofInput) {
                return;
            }
            if (paymentMethod === 'qris' || paymentMethod === 'transfer') {
                proofSection.style.display = 'block';
                proofInput.required = true;
            } else {
                proofSection.style.display = 'none';
                proofInput.required = false;
                proofInput.value = '';
            }
        }

        function updateAddOnAvailability() {
            const category = document.querySelector('input[name="layanan_id"]:checked')?.dataset.category || '';
            const addonSection = document.getElementById('addon-quantity-section');
            const addonMessage = document.getElementById('addon-disabled-message');
            const soapInput = document.getElementById('soap_addon_qty');
            const fragranceInput = document.getElementById('fragrance_addon_qty');

            if (!addonSection || !soapInput || !fragranceInput || !addonMessage) {
                return;
            }

            if (category === 'cuci_sepatu_tas_helm') {
                addonSection.style.display = 'none';
                addonMessage.classList.remove('d-none');
                soapInput.value = '0';
                fragranceInput.value = '0';
            } else {
                addonSection.style.display = '';
                addonMessage.classList.add('d-none');
            }
        }

        function submitSampleLocationsForm() {
            const form = document.getElementById('sample-location-form');
            if (form) {
                form.submit();
            }
        }

        function filterServiceCategory(category) {
            const cards = document.querySelectorAll('.service-card');
            cards.forEach((card) => {
                card.style.display = (!category || card.dataset.category === category) ? '' : 'none';
            });

            let selectedRadio = document.querySelector('input[name="layanan_id"]:checked');
            if (!selectedRadio || selectedRadio.closest('.service-card')?.offsetParent === null) {
                selectedRadio = null;
            }
            if (!selectedRadio) {
                const firstVisibleCard = Array.from(cards).find((card) => card.offsetParent !== null);
                if (firstVisibleCard) {
                    const radio = firstVisibleCard.querySelector('input[name="layanan_id"]');
                    if (radio) {
                        radio.checked = true;
                        document.querySelectorAll('.service-card').forEach((card) => card.classList.remove('active'));
                        firstVisibleCard.classList.add('active');
                        updateDelivery();
                        selectedRadio = radio;
                    }
                }
            }

            updateAddOnAvailability();
            updateOrderSummary();
        }
    </script>
</body>
</html>
