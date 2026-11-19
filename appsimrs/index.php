<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/helpers/api_client.php";

// Ambil data publik dari Server B via Web Service
$resPoli = call_api('poliklinik.php');
$daftarPoli = ($resPoli['status'] === 'success') ? $resPoli['data'] : [];

$resDokter = call_api('dokter.php');
$daftarDokter = ($resDokter['status'] === 'success') ? $resDokter['data'] : [];

$resStats = call_api('statistik.php');
$stats = ($resStats['status'] === 'success') ? $resStats['data'] : null;

$isLoggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - RS Sehat Sejahtera (SIMRS SOA)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0a369d 100%);
            color: white;
            padding: 80px 0 100px;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
        }
        .feature-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            transition: all 0.25s ease;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.12);
        }
        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Top Navbar Publik -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-hospital fs-3 me-2 text-danger"></i>
                <div>
                    <span class="fw-bold tracking-tight">RS SEHAT SEJAHTERA</span>
                    <small class="d-block text-secondary" style="font-size: 0.72rem;">Sistem Informasi Manajemen Rumah Sakit</small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navPublic">
                <ul class="navbar-nav me-auto ms-lg-4 mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#layanan">Poliklinik</a></li>
                    <li class="nav-item"><a class="nav-link" href="#dokter">Dokter Spesialis</a></li>
                    <li class="nav-item"><a class="nav-link" href="#alur">Alur Pendaftaran</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Buka Dashboard (<?= htmlspecialchars($_SESSION['user']['username']) ?>)
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-warning px-4 rounded-pill fw-semibold text-dark shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login Portal SIMRS
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center" id="beranda">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fs-6 mb-3 shadow-sm">
                        <i class="bi bi-diagram-3-fill me-1"></i> Service-Oriented Architecture (SOA) & RBAC Ready
                    </span>
                    <h1 class="display-4 fw-bold mb-3">Layanan Kesehatan Terpadu, Cepat, dan Akurat</h1>
                    <p class="lead text-white-50 mb-4 px-md-5">
                        Menghadirkan pelayanan kesehatan prima dengan arsitektur terintegrasi Web Service REST API. Memberikan kemudahan pendaftaran rawat jalan dan rekam medis terpadu.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="login.php" class="btn btn-light btn-lg px-4 py-3 rounded-pill fw-bold text-primary shadow">
                            <i class="bi bi-shield-lock-fill me-2"></i> Masuk ke SIMRS (Petugas)
                        </a>
                        <a href="#layanan" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                            <i class="bi bi-hospital me-2"></i> Lihat Poliklinik
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Banner Info Statistik Real-Time -->
    <div class="container" style="margin-top: -50px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <h2 class="fw-bold text-primary mb-1"><?= $stats ? $stats['total_pasien'] : 0 ?>+</h2>
                    <span class="text-secondary small text-uppercase">Pasien Terdaftar</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="fw-bold text-success mb-1"><?= $stats ? $stats['total_dokter'] : 0 ?></h2>
                    <span class="text-secondary small text-uppercase">Dokter Spesialis</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="fw-bold text-warning mb-1"><?= $stats ? $stats['total_poliklinik'] : 0 ?></h2>
                    <span class="text-secondary small text-uppercase">Layanan Poliklinik</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="fw-bold text-info mb-1"><?= $stats ? $stats['kunjungan_hari_ini'] : 0 ?></h2>
                    <span class="text-secondary small text-uppercase">Kunjungan Hari Ini</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Layanan Poliklinik -->
    <section class="py-5 mt-4" id="layanan">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">RAWAT JALAN</span>
                <h2 class="fw-bold">Poliklinik Spesialis & Rawat Jalan</h2>
                <p class="text-secondary">Daftar poliklinik aktif yang didukung oleh tenaga medis berpengalaman.</p>
            </div>

            <div class="row g-4">
                <?php if (empty($daftarPoli)): ?>
                    <div class="col-12 text-center py-4 text-muted">
                        Sedang menghubungkan ke Server B... Pastikan Web Service aktif.
                    </div>
                <?php else: ?>
                    <?php foreach ($daftarPoli as $p): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card feature-card p-4 h-100 bg-white">
                                <div class="icon-box bg-primary-subtle text-primary mb-3">
                                    <i class="bi bi-hospital"></i>
                                </div>
                                <span class="badge bg-light text-dark border align-self-start mb-2"><?= htmlspecialchars($p['kode_poli']) ?></span>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama_poli']) ?></h5>
                                <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= htmlspecialchars($p['gedung'] ?: 'Gedung Rawat Jalan') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Bagian Dokter Kami -->
    <section class="py-5 bg-white border-top border-bottom" id="dokter">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-2">TIM MEDIS</span>
                <h2 class="fw-bold">Dokter Spesialis Kami</h2>
                <p class="text-secondary">Pelayanan medis profesional dengan sertifikasi dan SIP resmi.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($daftarDokter as $d): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card feature-card text-center p-4 h-100">
                            <div class="mx-auto icon-box bg-success-subtle text-success mb-3">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($d['nama_dokter']) ?></h6>
                            <span class="badge bg-info-subtle text-info mb-2"><?= htmlspecialchars($d['spesialisasi']) ?></span>
                            <small class="text-muted d-block"><?= htmlspecialchars($d['nomor_sip']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Bagian Alur Pendaftaran & RBAC -->
    <section class="py-5" id="alur">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-warning-subtle text-dark px-3 py-2 rounded-pill fw-bold mb-2">PROSEDUR</span>
                <h2 class="fw-bold">Alur Pendaftaran & Peran Pengguna (RBAC)</h2>
                <p class="text-secondary">Sistem hak akses bertingkat untuk menjaga integritas data rumah sakit.</p>
            </div>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <div class="icon-box bg-primary-subtle text-primary mx-auto mb-3">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <h5 class="fw-bold">1. Petugas Loket (Pendaftaran)</h5>
                        <p class="text-secondary small">Mendaftarkan pasien baru, memilih poli tujuan, mencetak nomor antrean, dan validasi asuransi/BPJS.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <div class="icon-box bg-success-subtle text-success mx-auto mb-3">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h5 class="fw-bold">2. Tenaga Medis (Dokter)</h5>
                        <p class="text-secondary small">Melihat antrean pasien di polikliniknya, jadwal praktik, dan memeriksa riwayat rekam medis pasien.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <div class="icon-box bg-dark-subtle text-dark mx-auto mb-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5 class="fw-bold">3. Administrator SIMRS</h5>
                        <p class="text-secondary small">Akses menyeluruh: mengelola akun staf, master dokter, jadwal poli, penjamin, dan laporan operasional.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Publik -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1 fw-bold">RS SEHAT SEJAHTERA - SISTEM INFORMASI MANAJEMEN RUMAH SAKIT</p>
            <p class="text-secondary small mb-2">Arsitektur: SOA 2-Tier (Server A: Presentation UI & Server B: Web Service / REST API Database)</p>
            <div class="badge bg-secondary">Versi 1.0.0 (PHP Native & REST API)</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
