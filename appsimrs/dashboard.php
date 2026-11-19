<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/includes/header.php";

// Pastikan hanya user terotentikasi
require_login();
$user = current_user();

// Request statistik dari Server B via Web Service
$resStats = call_api('statistik.php');
$stats = ($resStats['status'] === 'success') ? $resStats['data'] : null;

// Request pendaftaran hari ini dari Server B
$resReg = call_api('pendaftaran.php', 'GET', ['tanggal' => date('Y-m-d')]);
$daftarKunjungan = ($resReg['status'] === 'success') ? $resReg['data'] : [];
?>

<!-- Alert jika Server B gagal dihubungi -->
<?php if ($resStats['status'] !== 'success'): ?>
<div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
    <div>
        <h5 class="alert-heading mb-1">Koneksi Web Service Terganggu</h5>
        <p class="mb-0 small"><?= htmlspecialchars($resStats['message']) ?></p>
        <span class="small text-muted">Pastikan Server B aktif pada URL: <code><?= htmlspecialchars(API_BASE_URL) ?></code></span>
    </div>
</div>
<?php endif; ?>

<!-- Header Dashboard Pengguna -->
<div class="card border-0 shadow-sm bg-white p-4 mb-4 rounded-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">Selamat Datang, <?= htmlspecialchars($user['nama_lengkap']) ?>!</h3>
                <span class="badge <?= ($user['peran'] == 'admin') ? 'bg-danger' : (($user['peran'] == 'dokter') ? 'bg-success' : 'bg-primary') ?> text-uppercase">
                    <?= htmlspecialchars($user['peran']) ?>
                </span>
            </div>
            <p class="text-secondary mb-0">Anda sedang login di <strong>Server A (Web Client)</strong> dan terhubung ke <strong>Server B (Web Service)</strong>.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (has_role(['admin', 'pendaftaran'])): ?>
                <a href="pendaftaran.php" class="btn btn-primary px-3 shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Pendaftaran Pasien
                </a>
            <?php endif; ?>
            <?php if (has_role('admin')): ?>
                <a href="pengguna.php" class="btn btn-outline-warning text-dark px-3 shadow-sm">
                    <i class="bi bi-shield-lock me-1"></i> Kelola RBAC
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Kartu Statistik Real-Time dari Server B -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold">Antrean Hari Ini</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $stats ? $stats['kunjungan_hari_ini'] : 0 ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-calendar2-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold">Total Pasien Terdaftar</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $stats ? $stats['total_pasien'] : 0 ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-people fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold">Dokter Aktif</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $stats ? $stats['total_dokter'] : 0 ?></h3>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-heart-pulse fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold">Poliklinik Buka</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $stats ? $stats['total_poliklinik'] : 0 ?></h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-hospital fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Antrean Kunjungan Pasien Hari Ini -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0 text-dark">
            <i class="bi bi-list-task text-primary me-2"></i>Daftar Antrean & Kunjungan Pasien Hari Ini (<?= date('d-m-Y') ?>)
        </h5>
        <span class="badge bg-light text-dark border">Data dari Server B via Web Service</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No. Antrean</th>
                        <th>No. Registrasi</th>
                        <th>No. Rekam Medis</th>
                        <th>Nama Pasien</th>
                        <th>Poliklinik Tujuan</th>
                        <th>Dokter Pemeriksa</th>
                        <th>Penjamin / Asuransi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarKunjungan)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                Belum ada pasien yang mendaftar hari ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarKunjungan as $kunjungan): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <?= str_pad($kunjungan['nomor_antrean'], 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </td>
                                <td><code class="fw-bold"><?= htmlspecialchars($kunjungan['no_registrasi']) ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($kunjungan['no_rkm_medis']) ?></span></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($kunjungan['nama_pasien']) ?></td>
                                <td><i class="bi bi-hospital text-muted me-1"></i><?= htmlspecialchars($kunjungan['nama_poli']) ?></td>
                                <td><?= htmlspecialchars($kunjungan['nama_dokter']) ?></td>
                                <td>
                                    <span class="badge <?= ($kunjungan['tipe_penjamin'] == 'BPJS') ? 'bg-success' : (($kunjungan['tipe_penjamin'] == 'SWASTA') ? 'bg-info' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($kunjungan['nama_asuransi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($kunjungan['status_pelayanan']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
