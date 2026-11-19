<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server B - Web Service SOA SIMRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary fs-6 me-2 p-2 px-3"><i class="bi bi-hdd-network"></i> SERVER B</span>
                    <span class="badge bg-success fs-6 p-2 px-3"><i class="bi bi-check-circle"></i> Web Service / REST API Provider</span>
                </div>
                <h1 class="display-6 fw-bold text-dark">Backend Service & Database SIMRS (simrssoa)</h1>
                <p class="text-secondary lead">
                    Ini adalah <strong>Server B</strong> dalam arsitektur Service-Oriented Architecture (SOA).
                    Server ini bertanggung jawab atas koneksi langsung ke database <code>simrssoa</code> dan menyediakan RESTful Web Services untuk dikonsumsi oleh <strong>Server A (appsimrs)</strong>.
                </p>
                <hr>

                <?php
                require_once __DIR__ . "/config/database.php";
                $dbStatus = false;
                $dbMessage = "";
                try {
                    $database = new Database();
                    $db = $database->getConnection();
                    if ($db) {
                        $dbStatus = true;
                        $dbMessage = "Koneksi ke database <code>simrssoa</code> Berhasil (Connected)!";
                    }
                } catch (Exception $e) {
                    $dbMessage = "Koneksi Gagal: " . $e->getMessage();
                }
                ?>

                <div class="alert <?= $dbStatus ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center" role="alert">
                    <i class="bi <?= $dbStatus ? 'bi-database-check' : 'bi-database-exclamation' ?> fs-4 me-3"></i>
                    <div>
                        <strong>Status Database (Server B):</strong> <?= $dbMessage ?>
                    </div>
                </div>

                <h4 class="mt-4 mb-3 fw-bold text-secondary"><i class="bi bi-code-slash"></i> Daftar Endpoint Web Service (JSON):</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Endpoint</th>
                                <th>Method</th>
                                <th>Fungsi</th>
                                <th>Aksi Test</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/api/statistik.php</code></td>
                                <td><span class="badge bg-success">GET</span></td>
                                <td>Statistik ringkasan (Total Pasien, Dokter, Poli, Kunjungan)</td>
                                <td><a href="api/statistik.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/pasien.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span></td>
                                <td>Ambil daftar pasien / Tambah data rekam medis pasien baru</td>
                                <td><a href="api/pasien.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/dokter.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span></td>
                                <td>Ambil daftar dokter aktif / Tambah master dokter</td>
                                <td><a href="api/dokter.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/poliklinik.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span></td>
                                <td>Ambil master poliklinik / Tambah poliklinik</td>
                                <td><a href="api/poliklinik.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/asuransi.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span></td>
                                <td>Ambil master penjamin/asuransi (BPJS, Swasta, Umum)</td>
                                <td><a href="api/asuransi.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/dokter_poli.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span></td>
                                <td>Jadwal praktik dokter per poliklinik (Tabel Asosiatif 1)</td>
                                <td><a href="api/dokter_poli.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/pendaftaran.php</code></td>
                                <td><span class="badge bg-success">GET</span> <span class="badge bg-primary">POST</span> <span class="badge bg-warning text-dark">PUT</span> <span class="badge bg-danger">DELETE</span></td>
                                <td>Transaksi pendaftaran berobat pasien (Tabel Asosiatif 2)</td>
                                <td><a href="api/pendaftaran.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                            <tr>
                                <td><code>/api/laporan.php</code></td>
                                <td><span class="badge bg-success">GET</span></td>
                                <td>Laporan kunjungan pasien & rekapitulasi per poli / asuransi</td>
                                <td><a href="api/laporan.php" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Test JSON</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-white border rounded-3">
                    <h5 class="fw-bold"><i class="bi bi-info-circle text-primary"></i> Cara Akses dari Server A:</h5>
                    <p class="mb-0">
                        Atur konstanta <code>API_BASE_URL</code> pada <code>appsimrs/config/app.php</code> mengarah ke endpoint Server B ini:
                        <br>
                        <code>define('API_BASE_URL', 'http://<?= $_SERVER['HTTP_HOST'] ?>/simrssoa/api');</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
