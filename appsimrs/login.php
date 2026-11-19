<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/helpers/api_client.php";

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$errorMsg = null;
$successMsg = null;

if (isset($_GET['msg']) && $_GET['msg'] === 'need_login') {
    $errorMsg = "Silakan login terlebih dahulu untuk mengakses menu tersebut.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $successMsg = "Anda telah berhasil keluar (logout).";
}

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $errorMsg = "Username dan password tidak boleh kosong.";
    } else {
        // Panggil Web Service Server B /api/auth.php
        $res = call_api('auth.php', 'POST', [
            'username' => $username,
            'password' => $password
        ]);

        if ($res['status'] === 'success') {
            $_SESSION['user'] = $res['data'];
            header("Location: dashboard.php");
            exit;
        } else {
            $errorMsg = $res['message'] ?? "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas & Administrator - SIMRS SOA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
            color: white;
            padding: 35px 25px 25px;
        }
        .role-btn {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .role-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card login-card bg-white">
                <div class="login-header text-center position-relative">
                    <div class="bg-white text-primary rounded-circle d-inline-flex p-3 mb-3 shadow">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">PORTAL LOGIN SIMRS SOA</h4>
                    <p class="small text-white-50 mb-0">Role-Based Access Control (Admin & Staf)</p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errorMsg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMsg): ?>
                        <div class="alert alert-success alert-dismissible fade show small" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($successMsg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Login Utama -->
                    <form method="POST" id="formLogin">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">USERNAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" id="inputUsername" class="form-control border-start-0" placeholder="Masukkan username" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" id="inputPassword" class="form-control border-start-0" placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang (Server B)
                        </button>
                    </form>

                    <!-- Tombol 1-Klik Isi Cepat Akun (Khusus Demo / Penguji) -->
                    <div class="mt-4 pt-3 border-top">
                        <span class="d-block fw-bold small text-muted text-uppercase mb-2 text-center">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Klik Untuk Otomatis Mengisi Akun:
                        </span>
                        <div class="d-grid gap-2">
                            <!-- Tombol Khusus Admin -->
                            <button type="button" class="btn btn-outline-danger btn-sm text-start role-btn p-2" onclick="setAccount('admin', 'password123')">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger me-2"><i class="bi bi-shield-fill"></i> ADMIN</span>
                                    <div>
                                        <strong class="d-block text-dark">Akun Administrator</strong>
                                        <small class="text-muted">Username: <code>admin</code> | Password: <code>password123</code></small>
                                    </div>
                                </div>
                            </button>

                            <!-- Tombol Petugas Loket -->
                            <button type="button" class="btn btn-outline-primary btn-sm text-start role-btn p-2" onclick="setAccount('petugas1', 'password123')">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2"><i class="bi bi-person-badge"></i> LOKET</span>
                                    <div>
                                        <strong class="d-block text-dark">Petugas Pendaftaran</strong>
                                        <small class="text-muted">Username: <code>petugas1</code> | Password: <code>password123</code></small>
                                    </div>
                                </div>
                            </button>

                            <!-- Tombol Dokter -->
                            <button type="button" class="btn btn-outline-success btn-sm text-start role-btn p-2" onclick="setAccount('dokter1', 'password123')">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2"><i class="bi bi-heart-pulse"></i> DOKTER</span>
                                    <div>
                                        <strong class="d-block text-dark">Tenaga Medis (Dokter)</strong>
                                        <small class="text-muted">Username: <code>dokter1</code> | Password: <code>password123</code></small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="text-decoration-none small text-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Landing Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setAccount(user, pass) {
    document.getElementById('inputUsername').value = user;
    document.getElementById('inputPassword').value = pass;
    document.getElementById('inputPassword').focus();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
