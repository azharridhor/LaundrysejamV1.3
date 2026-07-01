<?php
// ============================================
// admin/sidebar.php - Komponen Sidebar Admin
// ============================================

// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-wrap">
                <i class="bi bi-droplet-fill"></i>
            </div>
            <div class="brand">
                LaundryKoin
                <small>Admin Panel</small>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-section" style="flex:1">
        <div class="sidebar-label">Menu Utama</div>

        <a href="index.php" class="sidebar-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-label mt-2">Kelola Data</div>

        <a href="orders.php" class="sidebar-link <?= $current_page === 'orders.php' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Kelola Order</span>
        </a>

        <a href="layanan.php" class="sidebar-link <?= $current_page === 'layanan.php' ? 'active' : '' ?>">
            <i class="bi bi-washing-machine"></i>
            <span>Kelola Layanan</span>
        </a>

        <a href="lokasi.php" class="sidebar-link <?= $current_page === 'lokasi.php' ? 'active' : '' ?>">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Kelola Lokasi</span>
        </a>

        <a href="testimoni.php" class="sidebar-link <?= $current_page === 'testimoni.php' ? 'active' : '' ?>">
            <i class="bi bi-chat-quote-fill"></i>
            <span>Kelola Testimoni</span>
        </a>

        <a href="promo.php" class="sidebar-link <?= $current_page === 'promo.php' ? 'active' : '' ?>">
            <i class="bi bi-tag-fill"></i>
            <span>Kelola Promo</span>
        </a>
    </div>

    <!-- Footer Sidebar -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <div style="font-size:0.85rem;font-weight:600;color:white;">
                    <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                </div>
                <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">Administrator</div>
            </div>
        </div>
        <a href="logout.php" class="sidebar-link" style="padding:10px 12px;border-radius:10px;background:rgba(220,53,69,0.15);color:#ff6b6b;border-left:none;">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar</span>
        </a>
        <a href="../index.php" class="sidebar-link mt-1" style="padding:10px 12px;border-radius:10px;background:rgba(255,255,255,0.06);border-left:none;">
            <i class="bi bi-arrow-up-right-square"></i>
            <span>Lihat Website</span>
        </a>
    </div>
</aside>
