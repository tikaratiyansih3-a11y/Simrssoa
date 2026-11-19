<?php
// ========================================================
// SERVER A: RBAC (ROLE-BASED ACCESS CONTROL) GUARD
// ========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login. Jika belum, lempar ke login.php
 */
function require_login() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['id_pengguna'])) {
        header("Location: login.php?msg=need_login");
        exit;
    }
}

/**
 * Cek apakah role pengguna memiliki izin mengakses halaman.
 * Jika tidak berhak, tampilkan peringatan 403 Forbidden.
 *
 * @param array|string $allowed_roles Contoh: ['admin'] atau ['admin', 'pendaftaran']
 */
function require_role($allowed_roles) {
    require_login();

    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    $user_role = strtolower($_SESSION['user']['peran'] ?? '');

    if (!in_array($user_role, array_map('strtolower', $allowed_roles))) {
        http_response_code(403);
        render_forbidden_page($allowed_roles);
        exit;
    }
}

/**
 * Mengecek role untuk menampilkan/menyembunyikan tombol/menu tertentu
 */
function has_role($roles) {
    if (!isset($_SESSION['user']['peran'])) {
        return false;
    }
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return in_array(strtolower($_SESSION['user']['peran']), array_map('strtolower', $roles));
}

/**
 * Mengambil data pengguna yang sedang login
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Render Halaman 403 Forbidden jika role tidak sesuai
 */
function render_forbidden_page($allowed_roles) {
    $roles_str = implode(', ', array_map('strtoupper', $allowed_roles));
    $user = current_user();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 - Akses Ditolak (RBAC)</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="card border-0 shadow-lg mx-auto p-4 p-md-5 rounded-4" style="max-width: 550px;">
                <div class="text-danger mb-3">
                    <i class="bi bi-shield-lock-fill" style="font-size: 4rem;"></i>
                </div>
                <h2 class="fw-bold text-dark">403 - Akses Ditolak</h2>
                <p class="text-secondary mt-2">
                    Akun Anda sebagai <strong class="badge bg-warning text-dark text-uppercase"><?= htmlspecialchars($user['peran'] ?? 'Guest') ?></strong> 
                    tidak memiliki hak akses ke modul ini.
                </p>
                <div class="alert alert-secondary text-start small mb-4">
                    <i class="bi bi-info-circle me-1"></i> Modul ini hanya dapat diakses oleh peran: <strong><?= $roles_str ?></strong>.
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-primary px-4 me-2">
                        <i class="bi bi-house-door me-1"></i> Kembali ke Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
