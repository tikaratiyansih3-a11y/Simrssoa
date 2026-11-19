<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/helpers/auth_guard.php";

require_role(['admin', 'pendaftaran']);

$resPoli = call_api('poliklinik.php');
$daftarPoli = ($resPoli['status'] === 'success') ? $resPoli['data'] : [];

require_once __DIR__ . "/includes/header.php";
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="bi bi-building text-warning me-2"></i>Master Poliklinik</h2>
    <p class="text-secondary mb-0">Daftar poliklinik dan unit pelayanan rawat jalan dari Server B.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Kode Poliklinik</th>
                        <th>Nama Poliklinik</th>
                        <th>Gedung / Lokasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarPoli)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Data poliklinik tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftarPoli as $p): ?>
                            <tr>
                                <td class="ps-4"><?= $no++ ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['kode_poli']) ?></span></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_poli']) ?></td>
                                <td><i class="bi bi-geo-alt text-muted me-1"></i><?= htmlspecialchars($p['gedung'] ?: '-') ?></td>
                                <td><span class="badge bg-success">Aktif</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
