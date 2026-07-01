<?php
// ============================================
// admin/login.php - Halaman Login Admin
// ============================================

session_start();
require_once __DIR__ . '/../includes/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error   = '';
$success = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = esc($koneksi, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong!';
    } else {
        // Cek admin di database
        $sql = "SELECT * FROM admin WHERE username = '$username' LIMIT 1";
        $result = query($koneksi, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);

            // Cek password (support both plain text dan hashed untuk dev)
            $valid = false;
            if (password_verify($password, $admin['password'])) {
                $valid = true; // password_hash format
            } elseif (md5($password) === $admin['password']) {
                $valid = true; // md5 format
            } elseif ($password === $admin['password']) {
                $valid = true; // plain text (dev only)
            } elseif ($password === 'admin123' && $username === 'admin') {
                $valid = true; // default credential
            }

            if ($valid) {
                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['id'];
                $_SESSION['admin_username']  = $admin['username'];
                header("Location: index.php");
                exit;
            } else {
                $error = 'Password yang kamu masukkan salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - LaundryKoin Sejam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #e20513 0%, #ffd500 40%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.15);
        }
        .brand-logo {
            width: 64px;
            height: 64px;
            background: var(--bs-red, #e20513);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(226,5,19,0.25);
            overflow: hidden;
        }
        .brand-logo img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e8ecf0;
            padding: 12px 16px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #e20513;
            box-shadow: 0 0 0 3px rgba(226,5,19,0.12);
        }
        .btn-login {
            background: linear-gradient(135deg, #e20513, #b1000f);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
            width: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #b1000f, #88020c);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(226,5,19,0.35);
            color: white;
        }
        .input-group-text {
            background: #f8faff;
            border: 2px solid #e8ecf0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #6c757d;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #e20513;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo -->
        <div class="brand-logo">
            <img src="../assets/img/logo.svg" alt="Laundry Sejam" class="logo-img">
        </div>

        <h4 class="text-center fw-800 mb-1" style="font-weight:800;color:#e20513">Admin Panel</h4>
        <p class="text-center text-muted mb-4" style="font-size:0.85rem">LaundryKoin Sejam</p>

        <!-- Alert Error -->
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-4" style="border-radius:10px;font-size:0.875rem">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-600 small">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text"
                           name="username"
                           class="form-control"
                           placeholder="Masukkan username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required
                           autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-600 small">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Masukkan password"
                           required
                           autocomplete="current-password"
                           id="passInput">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            style="border-radius:0 12px 12px 0;border:2px solid #e8ecf0;border-left:none"
                            onclick="togglePass()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
            </button>
        </form>

        <div class="mt-4 p-3 rounded-3 text-center" style="background:#f8faff;font-size:0.8rem;color:#6c757d">
            <i class="bi bi-info-circle me-1"></i>
            Default: <strong>admin</strong> / <strong>admin123</strong>
        </div>

        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted" style="font-size:0.82rem">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Website
            </a>
        </div>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('passInput');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>
