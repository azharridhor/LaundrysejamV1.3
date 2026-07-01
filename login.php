<?php
session_start();
require_once 'includes/koneksi.php';

if (isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in'] === true) {
    header('Location: member.php');
    exit;
}

$error = '';
$success = '';
$activeTab = $_GET['tab'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $username = esc($koneksi, $_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            $sql = "SELECT * FROM members WHERE username = '$username' LIMIT 1";
            $result = query($koneksi, $sql);

            if ($result && mysqli_num_rows($result) === 1) {
                $member = mysqli_fetch_assoc($result);
                $valid = false;

                if (password_verify($password, $member['password'])) {
                    $valid = true;
                } elseif (md5($password) === $member['password']) {
                    $valid = true;
                } elseif ($password === $member['password']) {
                    $valid = true;
                }

                if ($valid) {
                    $_SESSION['member_logged_in'] = true;
                    $_SESSION['member_id'] = $member['id'];
                    $_SESSION['member_username'] = $member['username'];
                    $_SESSION['member_name'] = $member['name'];
                    header('Location: member.php');
                    exit;
                }
            }

            $error = 'Username atau password salah.';
        }

        $activeTab = 'login';
    }

    if ($action === 'register') {
        $username = esc($koneksi, $_POST['username'] ?? '');
        $name = esc($koneksi, $_POST['name'] ?? '');
        $email = esc($koneksi, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($name) || empty($password) || empty($confirmPassword)) {
            $error = 'Semua field wajib diisi.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password dan konfirmasi password tidak cocok.';
        } else {
            $check = query($koneksi, "SELECT id FROM members WHERE username = '$username' LIMIT 1");
            if ($check && mysqli_num_rows($check) > 0) {
                $error = 'Username sudah terdaftar. Silakan pilih yang lain.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO members (username, password, name, email) VALUES ('$username', '$hashedPassword', '$name', '$email')";
                if (query($koneksi, $sql)) {
                    $success = 'Akun berhasil dibuat. Silakan login sekarang.';
                    $activeTab = 'login';
                } else {
                    $error = 'Gagal membuat akun. Silakan coba lagi.';
                    $activeTab = 'register';
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
    <title>Login - LaundryKoin Sejam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-shell py-5">
        <div class="auth-card mx-auto shadow-sm">
            <div class="text-center mb-4">
                <img src="assets/img/logo.svg" alt="Laundry Sejam" class="logo-img mb-3">
                <h1 class="h4 mb-2">Login Member LaundryKoin</h1>
                <p class="text-muted mb-0">Masuk atau daftar untuk memesan layanan laundry dengan cepat.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <ul class="nav nav-tabs nav-fill mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'login' ? 'active' : '' ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Login</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'register' ? 'active' : '' ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Daftar</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade <?= $activeTab === 'login' ? 'show active' : '' ?>" id="login" role="tabpanel">
                    <form method="POST" action="login.php#login">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
                <div class="tab-pane fade <?= $activeTab === 'register' ? 'show active' : '' ?>" id="register" role="tabpanel">
                    <form method="POST" action="login.php#register">
                        <input type="hidden" name="action" value="register">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Pilih username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (opsional)</label>
                            <input type="email" name="email" class="form-control" placeholder="contoh@domain.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Buat password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    </form>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="index.php" class="text-decoration-none me-3">Kembali ke beranda</a>
                <a href="admin/login.php" class="text-decoration-none">Login Admin</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
