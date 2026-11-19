<?php
// ========================================================
// SERVER A: DIAGNOSTIC & HEALTH CHECK TOOL
// Buka di browser: http://localhost/appsimrs/cek_koneksi.php
// ========================================================

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/helpers/api_client.php";

$tests = [];

// 1. Cek Ekstensi cURL
$tests['curl'] = [
    'title' => 'Ekstensi PHP cURL',
    'pass'  => function_exists('curl_init'),
    'detail'=> function_exists('curl_init') ? 'Aktif (Enabled)' : 'Non-aktif! Aktifkan extension=curl di php.ini.'
];

// 2. Cek Endpoint Base URL
$tests['endpoint'] = [
    'title' => 'Target Web Service Server B',
    'pass'  => true,
    'detail'=> API_BASE_URL
];

// 3. Test HTTP Ping ke Server B
$resStats = call_api('statistik.php');
$isServerBConnected = ($resStats['status'] === 'success');
$tests['server_b'] = [
    'title' => 'Koneksi ke Server B (/api/statistik.php)',
    'pass'  => $isServerBConnected,
    'detail'=> $isServerBConnected 
        ? 'Terhubung dengan sukses! Respons Server B normal.' 
        : 'Gagal: ' . htmlspecialchars($resStats['message'])
];

// 4. Test Autentikasi API
$resAuth = call_api('auth.php', 'POST', ['username' => 'test_ping', 'password' => 'test']);
$isApiRouteWorking = ($resAuth['code'] === 401 || $resAuth['status'] === 'success');
$tests['auth_endpoint'] = [
    'title' => 'Rute Endpoint API (/api/auth.php)',
    'pass'  => $isApiRouteWorking,
    'detail'=> $isApiRouteWorking ? 'Endpoint Auth merespon dengan benar.' : 'Gagal mengakses endpoint auth: ' . htmlspecialchars($resAuth['message'])
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostik Koneksi SOA SIMRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-heart-pulse-fill fs-2 text-primary me-3"></i>
                        <div>
                            <h4 class="fw-bold mb-0">DIAGNOSTIK KONEKSI SOA (SERVER A ➔ SERVER B)</h4>
                            <small class="text-secondary">Pengecekan otomatis integrasi Web Service dan Database</small>
                        </div>
                    </div>

                    <div class="list-group mb-4">
                        <?php foreach ($tests as $key => $t): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-1 fw-bold"><?= $t['title'] ?></h6>
                                    <small class="text-muted"><?= $t['detail'] ?></small>
                                </div>
                                <div>
                                    <?php if ($t['pass']): ?>
                                        <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> NORMAL</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i> MASALAH</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isServerBConnected): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                            <div>
                                <strong>SELURUH KONEKSI NORMAL!</strong>
                                <p class="mb-0 small">Server A berhasil terhubung dan mengonsumsi data dari Server B.</p>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="index.php" class="btn btn-primary px-4 me-2"><i class="bi bi-house me-1"></i> Buka Landing Page</a>
                            <a href="login.php" class="btn btn-warning px-4 fw-semibold"><i class="bi bi-shield-lock me-1"></i> Buka Halaman Login</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Panduan Memperbaiki Masalah:</h5>
                            <ol class="mb-0 small ps-3">
                                <li class="mb-1"><strong>Pastikan database 'simrssoa' sudah diimport di phpMyAdmin:</strong> Buka <code>http://localhost/phpmyadmin/</code>, klik tab SQL, copy-paste file <code>simrssoa/database/simrssoa.sql</code> dan klik Kirim.</li>
                                <li class="mb-1"><strong>Pastikan folder 'simrssoa' ada di htdocs:</strong> Cek folder <code>C:\xampp\htdocs\simrssoa</code> harus ada berdampingan dengan <code>C:\xampp\htdocs\appsimrs</code>.</li>
                                <li class="mb-1"><strong>Cek langsung browser:</strong> Buka <code><?= API_BASE_URL ?>/statistik.php</code> di tab baru untuk membaca pesan kesalahan langsung dari Server B.</li>
                            </ol>
                        </div>
                        <div class="text-center mt-3">
                            <a href="cek_koneksi.php" class="btn btn-outline-primary px-4"><i class="bi bi-arrow-clockwise me-1"></i> Cek Ulang Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
