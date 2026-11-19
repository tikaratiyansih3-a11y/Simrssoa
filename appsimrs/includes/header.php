<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../helpers/auth_guard.php";

// Pastikan halaman yang menyertakan header ini sudah login
require_login();

$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= HOSPITAL_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .table thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<!-- Navbar Utama Terproteksi RBAC -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <i class="bi bi-hospital fs-4 me-2 text-danger"></i>
            <div>
                <span class="text-white"><?= APP_NAME ?></span>
                <small class="d-block text-secondary" style="font-size: 0.7rem;"><?= HOSPITAL_NAME ?></small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <!-- Menu Dashboard (Semua Role) -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active fw-bold text-white' : '' ?>" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Menu Pendaftaran (Admin & Petugas Pendaftaran) -->
                <?php if (has_role(['admin', 'pendaftaran'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'pendaftaran.php') ? 'active fw-bold text-white' : '' ?>" href="pendaftaran.php">
                        <i class="bi bi-clipboard-pulse me-1"></i> Pendaftaran
                    </a>
                </li>
                <?php endif; ?>

                <!-- Menu Pasien (Admin & Petugas Pendaftaran) -->
                <?php if (has_role(['admin', 'pendaftaran'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'pasien.php') ? 'active fw-bold text-white' : '' ?>" href="pasien.php">
                        <i class="bi bi-people me-1"></i> Pasien
                    </a>
                </li>
                <?php endif; ?>

                <!-- Menu Dokter & Jadwal (Semua Role) -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'dokter.php') ? 'active fw-bold text-white' : '' ?>" href="dokter.php">
                        <i class="bi bi-heart-pulse me-1"></i> Dokter & Jadwal
                    </a>
                </li>

                <!-- Menu Poliklinik (Admin & Pendaftaran) -->
                <?php if (has_role(['admin', 'pendaftaran'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'poliklinik.php') ? 'active fw-bold text-white' : '' ?>" href="poliklinik.php">
                        <i class="bi bi-building me-1"></i> Poliklinik
                    </a>
                </li>
                <?php endif; ?>

                <!-- Menu Asuransi (Admin & Pendaftaran) -->
                <?php if (has_role(['admin', 'pendaftaran'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'asuransi.php') ? 'active fw-bold text-white' : '' ?>" href="asuransi.php">
                        <i class="bi bi-shield-check me-1"></i> Asuransi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'laporan.php') ? 'active fw-bold text-white' : '' ?>" href="laporan.php">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan
                    </a>
                </li>
                <?php endif; ?>

                <!-- Menu Pengguna / RBAC User Management (Khusus ADMIN) -->
                <?php if (has_role('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'pengguna.php') ? 'active fw-bold text-warning' : 'text-warning' ?>" href="pengguna.php">
                        <i class="bi bi-shield-lock me-1"></i> Pengguna (RBAC)
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Info Profil User & Logout -->
            <div class="d-flex align-items-center gap-2">
                <div class="text-end d-none d-md-block me-2">
                    <small class="text-white d-block fw-semibold"><?= htmlspecialchars($user['nama_lengkap']) ?></small>
                    <span class="badge <?= ($user['peran'] == 'admin') ? 'bg-danger' : (($user['peran'] == 'dokter') ? 'bg-success' : 'bg-primary') ?> text-uppercase" style="font-size: 0.65rem;">
                        Peran: <?= htmlspecialchars($user['peran']) ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-sm btn-outline-light" title="Keluar">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container py-4">
