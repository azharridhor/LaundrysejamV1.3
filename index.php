<?php
require_once 'includes/koneksi.php';

$serviceList = query($koneksi, "SELECT * FROM layanan ORDER BY id ASC LIMIT 6");
$locationList = query($koneksi, "SELECT * FROM lokasi ORDER BY id ASC LIMIT 4");
$promoList = query($koneksi, "SELECT * FROM promo ORDER BY id DESC LIMIT 3");
$testimonialList = query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry KOIN Sejam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger" href="#home">Laundry KOIN Sejam</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="fitur.php">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#locations">Lokasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#promo">Promo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Testimoni</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary px-4" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-home">
        <div class="container py-7">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="eyebrow text-uppercase opacity-85 mb-3 d-inline-block">Laundry KOIN Sejam</span>
                    <h1 class="display-5 fw-bold mb-4">Laundry selesai 1 jam, cuma 10 ribuan. Mudah, cepat, dan selalu ada promo.</h1>
                    <p class="lead mb-4">Layanan laundry self-service modern untuk mahasiswa, anak kost, dan pekerja di Jogja. Higienis, transparan, dan siap pakai kapan saja.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#promo" class="btn btn-light btn-lg">Lihat Promo</a>
                        <a href="https://wa.me/+6282147189896" target="_blank" class="btn btn-outline-light btn-lg">Hubungi WhatsApp</a>
                        <a href="login.php?tab=register" class="btn btn-secondary btn-lg">Daftar Member</a>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-2 text-white-75 small">
                        <span><i class="bi bi-check-circle-fill me-1"></i>#pastiadapromo</span>
                        <span><i class="bi bi-check-circle-fill me-1"></i>Gratis parkir motor</span>
                        <span><i class="bi bi-check-circle-fill me-1"></i>Bayar pakai saldo KOIN</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-glass p-5 shadow-lg">
                        <div class="promo-panel p-4 rounded-4 mb-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <p class="text-uppercase text-warning small mb-2">Promo Terbaru</p>
                                    <h4 class="fw-bold mb-1">Diskon sampai 20% setiap minggu</h4>
                                    <p class="mb-0">#pastiadapromo hadir untuk bikin laundrymu lebih hemat.</p>
                                </div>
                                <span class="badge bg-warning text-dark py-2 px-3 rounded-pill">HOT</span>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-4">
                                    <h5>1 Jam</h5>
                                    <p class="mb-0">Laundry selesai cepat.</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-4">
                                    <h5>10 Ribuan</h5>
                                    <p class="mb-0">Harga mulai terjangkau.</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-4">
                                    <h5>24 Jam</h5>
                                    <p class="mb-0">Buka setiap hari.</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-4">
                                    <h5>Cabang Strategis</h5>
                                    <p class="mb-0">Dekat kampus dan kost.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container py-5">
        <section id="features" class="mb-5">
            <div class="text-center mb-5">
                <p class="text-uppercase text-primary fw-bold mb-2">Kenapa Kami</p>
                <h2 class="fw-bold">Pengalaman Laundry Terbaik untuk Mahasiswa & Pekerja</h2>
                <p class="text-muted mx-auto" style="max-width: 680px;">Laundry KOIN Sejam menjamin kebersihan, kecepatan, dan harga terjangkau dengan teknologi mesin terbaru dan layanan self-service modern.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 text-center h-100">
                        <div class="feature-icon mb-3"><i class="bi bi-person-circle"></i></div>
                        <h5>Self Service</h5>
                        <p class="text-muted">Cuci sendiri dengan privasi penuh dan kontrol penuh atas cucianmu.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 text-center h-100">
                        <div class="feature-icon mb-3"><i class="bi bi-clock-history"></i></div>
                        <h5>Selesai 1 Jam</h5>
                        <p class="text-muted">Teknologi mesin cepat putar membuat cucian siap dalam 60 menit.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 text-center h-100">
                        <div class="feature-icon mb-3"><i class="bi bi-cash-stack"></i></div>
                        <h5>Harga Terjangkau</h5>
                        <p class="text-muted">Mulai 10 ribuan, sangat ramah untuk kantong mahasiswa dan anak kost.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card p-4 text-center h-100">
                        <div class="feature-icon mb-3"><i class="bi bi-droplet-half"></i></div>
                        <h5>Higiene Terjamin</h5>
                        <p class="text-muted">1 mesin untuk 1 pelanggan, jadi tidak tercampur dengan cucian lain.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="promo" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
                <div>
                    <p class="section-label text-primary mb-2">Promo Spesial</p>
                    <h2 class="fw-bold">Promo Terbaru</h2>
                    <p class="text-muted mb-0">#pastiadapromo hadir di setiap musim. Cek promo terbaru dan hemat lebih banyak saat laundry.</p>
                </div>
                <a href="admin/promo.php" class="link-primary">Kelola promo</a>
            </div>
            <div class="row g-4">
                <?php if ($promoList && mysqli_num_rows($promoList) > 0): ?>
                    <?php while ($promo = mysqli_fetch_assoc($promoList)): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="promo-card p-4 h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="mb-0"><?= htmlspecialchars($promo['judul_promo']) ?></h5>
                                    <span class="badge bg-primary">Promo</span>
                                </div>
                                <p class="text-muted mb-0"><?= htmlspecialchars($promo['deskripsi']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-secondary">Belum ada promo aktif.</div></div>
                <?php endif; ?>
            </div>
        </section>

        <section id="services" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-primary fw-bold mb-2">Fitur & Layanan</p>
                    <h2 class="fw-bold">Layanan Laundry Kami</h2>
                </div>
                <a href="admin/layanan.php" class="link-primary">Kelola layanan</a>
            </div>
            <div class="row g-4">
                <?php if ($serviceList && mysqli_num_rows($serviceList) > 0): ?>
                    <?php while ($service = mysqli_fetch_assoc($serviceList)): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="service-card p-4 h-100 shadow-sm">
                                <h5><?= htmlspecialchars($service['nama_layanan']) ?></h5>
                                <p class="price mb-2"><?= formatRupiah($service['harga']) ?></p>
                                <p class="text-muted mb-0">Durasi <?= htmlspecialchars($service['durasi']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-secondary">Belum ada layanan yang tersedia.</div></div>
                <?php endif; ?>
            </div>
        </section>

        <section id="locations" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-primary fw-bold mb-2">Lokasi Kami</p>
                    <h2 class="fw-bold">Temukan Outlet Terdekat</h2>
                </div>
                <a href="admin/lokasi.php" class="link-primary">Kelola lokasi</a>
            </div>
            <div class="row g-4">
                <?php if ($locationList && mysqli_num_rows($locationList) > 0): ?>
                    <?php while ($location = mysqli_fetch_assoc($locationList)): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="location-card p-4 h-100 shadow-sm">
                                <?php if (!empty($location['image_url'])): ?>
                                    <div class="location-image mb-3" style="background-image:url('<?= htmlspecialchars($location['image_url']) ?>');"></div>
                                <?php endif; ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="location-dot"></div>
                                    <h5 class="mb-0 ms-3"><?= htmlspecialchars($location['nama_lokasi']) ?></h5>
                                </div>
                                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($location['alamat'])) ?></p>
                                <?php if (!empty($location['maps_url'])): ?>
                                    <a href="<?= htmlspecialchars($location['maps_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="bi bi-geo-alt-fill me-1"></i>Buka di Google Maps
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-secondary">Belum ada lokasi cabang.</div></div>
                <?php endif; ?>
            </div>
        </section>

        <section id="testimonials" class="mb-5">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-bold mb-2">Apa Kata Mereka</p>
                <h2 class="fw-bold">Testimoni Pelanggan</h2>
            </div>
            <div class="row g-4">
                <?php if ($testimonialList && mysqli_num_rows($testimonialList) > 0): ?>
                    <?php while ($testi = mysqli_fetch_assoc($testimonialList)): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="testimonial-card p-4 h-100 shadow-sm">
                                <p class="mb-3">"<?= htmlspecialchars($testi['isi_testimoni']) ?>"</p>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="testimonial-avatar"><?= strtoupper(substr($testi['nama_pelanggan'], 0, 1)) ?></div>
                                    <div>
                                        <strong><?= htmlspecialchars($testi['nama_pelanggan']) ?></strong>
                                        <div class="text-warning small"><?= str_repeat('★', max(1, min(5, (int)$testi['rating']))) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-secondary">Belum ada testimoni.</div></div>
                <?php endif; ?>
            </div>
        </section>

        <section id="contact" class="mb-5">
            <div class="contact-card p-5 shadow-sm rounded-4 text-center">
                <h2 class="fw-bold mb-3">Butuh bantuan atau ingin pesan sekarang?</h2>
                <p class="text-muted mb-4">Hubungi WhatsApp kami untuk informasi cepat atau langsung datang ke cabang terdekat.</p>
                <a href="https://wa.me/+6282147189896" target="_blank" class="btn btn-primary btn-lg">Hubungi WhatsApp</a>
            </div>
        </section>
    </main>

    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-white">Laundry KOIN Sejam</h5>
                    <p class="text-muted">Solusi laundry self-service modern, cepat, higienis, dan terjangkau di Yogyakarta.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1">Hubungi: <a href="https://wa.me/+6282147189896" class="text-white text-decoration-none">+62 812-3456-7890</a></p>
                    <p class="mb-0">Email: <a href="mailto:halo@laundrykoinsejam.id" class="text-white text-decoration-none">halo@laundrykoinsejam.id</a></p>
                </div>
            </div>
            <div class="text-center text-muted mt-4">© 2026 Laundry KOIN Sejam. All rights reserved.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
