<?php
require_once 'includes/koneksi.php';

$serviceList = query($koneksi, "SELECT * FROM layanan ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur Laundry KOIN Sejam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Laundry KOIN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="fitur.php">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#locations">Lokasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#promo">Promo</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#testimonials">Testimoni</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="admin/login.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-home bg-primary text-white">
        <div class="container py-6">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <p class="eyebrow text-uppercase text-white opacity-75 mb-3">Fitur Unggulan</p>
                    <h1 class="display-5 fw-bold">Fitur laundry modern yang memudahkan hidupmu.</h1>
                    <p class="lead text-white-75 mt-4">Laundry KOIN Sejam hadir dengan solusi cuci-kering cepat, praktis, dan hemat bagi mahasiswa, pekerja, dan anak kost.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="login.php?tab=register" class="btn btn-light btn-lg">Gabung Sekarang</a>
                        <a href="https://wa.me/+6282147189896" target="_blank" class="btn btn-outline-light btn-lg">Chat WhatsApp</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-glass p-5 shadow-lg">
                        <h4 class="mb-4">Fitur yang membuat Laundry KOIN berbeda</h4>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="feature-card p-4 text-start">
                                    <h5><i class="bi bi-lightning-charge-fill text-primary me-2"></i> Laundry Express 60 Menit</h5>
                                    <p class="text-muted mb-0">Cucianmu cepat selesai dengan mesin berkinerja tinggi dan proses yang efisien.</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="feature-card p-4 text-start">
                                    <h5><i class="bi bi-shield-lock-fill text-primary me-2"></i> Privasi dan Higienis</h5>
                                    <p class="text-muted mb-0">Setiap pelanggan mendapatkan mesin yang terpisah agar cucian tetap bersih dan terjaga.</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="feature-card p-4 text-start">
                                    <h5><i class="bi bi-cash-stack text-primary me-2"></i> Harga Hemat</h5>
                                    <p class="text-muted mb-0">Paket mulai 10 ribuan dengan promo member dan diskon loyalitas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container py-5">
        <section class="mb-5">
            <div class="text-center mb-5">
                <p class="text-uppercase text-primary fw-bold mb-2">Apa yang kamu dapatkan</p>
                <h2 class="fw-bold">Fitur Layanan Laundry Kami</h2>
                <p class="text-muted mx-auto" style="max-width: 680px;">Dari self service sampai promo eksklusif, Laundry KOIN Sejam memberikan pengalaman laundry yang cepat dan praktis.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 h-100 text-center">
                        <div class="feature-icon mb-3"><i class="bi bi-gear-fill"></i></div>
                        <h5>Self-Service</h5>
                        <p class="text-muted">Kontrol penuh atas cucianmu dengan mesin mandiri yang mudah digunakan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 h-100 text-center">
                        <div class="feature-icon mb-3"><i class="bi bi-speedometer2"></i></div>
                        <h5>Express 1 Jam</h5>
                        <p class="text-muted">Pilih paket ekspres untuk cucian siap dalam waktu singkat.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 h-100 text-center">
                        <div class="feature-icon mb-3"><i class="bi bi-droplet-half"></i></div>
                        <h5>Kebersihan Maksimal</h5>
                        <p class="text-muted">Mesin yang selalu bersih dan terawat untuk hasil cucian terbaik.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 h-100 text-center">
                        <div class="feature-icon mb-3"><i class="bi bi-wallet2"></i></div>
                        <h5>Biaya Ekonomis</h5>
                        <p class="text-muted">Banyak pilihan paket dengan harga terjangkau untuk semua kebutuhan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-primary fw-bold mb-2">Daftar Layanan</p>
                    <h2 class="fw-bold">Pilihan Paket Laundry</h2>
                </div>
            </div>
            <div class="row g-4">
                <?php if ($serviceList && mysqli_num_rows($serviceList) > 0): ?>
                    <?php while ($service = mysqli_fetch_assoc($serviceList)): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="service-card p-4 h-100 shadow-sm">
                                <h5><?= htmlspecialchars($service['nama_layanan']) ?></h5>
                                <p class="price mb-2"><?= formatRupiah($service['harga']) ?></p>
                                <p class="text-muted mb-2">Durasi <?= htmlspecialchars($service['durasi']) ?></p>
                                <p class="mb-0">Kami menjamin pelayanan cepat dan cuci yang bersih untuk setiap paket.</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-secondary">Belum ada layanan terdaftar.</div></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="contact-card text-center p-5 mb-5 shadow-sm rounded-4">
            <h2 class="fw-bold mb-3">Siap coba Laundry KOIN Sejam?</h2>
            <p class="text-muted mb-4">Daftar member dan nikmati promo eksklusif serta layanan laundry yang lebih mudah.</p>
            <a href="login.php?tab=register" class="btn btn-primary btn-lg">Daftar Member</a>
        </section>
    </main>

    <footer class="footer bg-dark text-white py-5">
        <div class="container text-center">
            <p class="mb-1">Laundry KOIN Sejam</p>
            <p class="mb-0">© 2026 Laundry KOIN Sejam. Semua hak cipta dilindungi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
