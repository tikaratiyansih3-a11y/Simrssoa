<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/helpers/auth_guard.php";

require_login();

// Ambil data dokter
$resDokter = call_api('dokter.php');
$daftarDokter = ($resDokter['status'] === 'success') ? $resDokter['data'] : [];

// Ambil jadwal praktik dokter di poliklinik (tabel asosiatif)
$resJadwal = call_api('dokter_poli.php');
$daftarJadwal = ($resJadwal['status'] === 'success') ? $resJadwal['data'] : [];

require_once __DIR__ . "/includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-heart-pulse text-danger me-2"></i>Data Dokter & Jadwal Praktik Poliklinik</h2>
        <p class="text-secondary mb-0">Informasi tenaga medis dan plotting jadwal poli dari Server B.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>Daftar Dokter Spesialis & Umum
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Kode</th>
                                <th>Nama Dokter</th>
                                <th>Spesialisasi</th>
                                <th>Nomor SIP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftarDokter as $doc): ?>
                                <tr>
                                    <td class="ps-3"><code><?= htmlspecialchars($doc['kode_dokter']) ?></code></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($doc['nama_dokter']) ?></td>
                                    <td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($doc['spesialisasi']) ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($doc['nomor_sip']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Praktik Poliklinik (Tabel Asosiatif) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-week text-success me-2"></i>Jadwal Praktik Dokter di Poliklinik
                </h5>
                <span class="badge bg-success-subtle text-success">Relasi M:N</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Poliklinik</th>
                                <th>Dokter</th>
                                <th>Hari</th>
                                <th>Jam Praktik</th>
                                <th>Kuota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftarJadwal)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada jadwal praktik terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftarJadwal as $j): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-primary"><?= htmlspecialchars($j['nama_poli']) ?></td>
                                        <td><?= htmlspecialchars($j['nama_dokter']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($j['hari_praktik']) ?></span></td>
                                        <td><small><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></small></td>
                                        <td><span class="badge bg-secondary"><?= $j['kuota_pasien'] ?> Pasien</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
