<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/helpers/auth_guard.php";

require_role(['admin', 'pendaftaran']);

$resAsr = call_api('asuransi.php');
$daftarAsuransi = ($resAsr['status'] === 'success') ? $resAsr['data'] : [];

require_once __DIR__ . "/includes/header.php";
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="bi bi-shield-check text-info me-2"></i>Master Asuransi & Penjamin Biaya</h2>
    <p class="text-secondary mb-0">Daftar mitra penjamin pembiayaan kesehatan pasien dari Server B.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Kode Asuransi</th>
                        <th>Nama Asuransi / Penjamin</th>
                        <th>Kategori / Tipe</th>
                        <th>Status Kerjasama</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarAsuransi)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Data asuransi tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftarAsuransi as $a): ?>
                            <tr>
                                <td class="ps-4"><?= $no++ ?></td>
                                <td><code><?= htmlspecialchars($a['kode_asuransi']) ?></code></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($a['nama_asuransi']) ?></td>
                                <td>
                                    <?php if ($a['tipe_penjamin'] === 'BPJS'): ?>
                                        <span class="badge bg-success">BPJS Kesehatan (Pemerintah)</span>
                                    <?php elseif ($a['tipe_penjamin'] === 'SWASTA'): ?>
                                        <span class="badge bg-info text-dark">Asuransi Swasta</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Umum / Tunai Mandiri</span>
                                    <?php endif; ?>
                                </td>
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
