<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/includes/header.php";

// RBAC Guard: Laporan hanya dapat diakses oleh Admin dan Petugas Pendaftaran
require_role(['admin', 'pendaftaran']);

$tipe        = $_GET['tipe'] ?? 'kunjungan';
$tgl_mulai   = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
$id_poli     = $_GET['id_poliklinik'] ?? '';

// Panggil Web Service Laporan di Server B
$params = [
    'tipe'        => $tipe,
    'tgl_mulai'   => $tgl_mulai,
    'tgl_selesai' => $tgl_selesai
];
if (!empty($id_poli)) {
    $params['id_poliklinik'] = $id_poli;
}

$resLaporan = call_api('laporan.php', 'GET', $params);
$dataLaporan = ($resLaporan['status'] === 'success') ? $resLaporan['data'] : null;

// Ambil data poliklinik untuk dropdown filter
$daftarPoli = call_api('poliklinik.php')['data'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan & Rekapitulasi SIMRS</h2>
        <p class="text-secondary mb-0">Layanan data analitik dan pelaporan yang disajikan langsung dari Server B via Web Service.</p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Periode & Jenis Laporan -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Jenis Laporan</label>
                <select name="tipe" class="form-select">
                    <option value="kunjungan" <?= ($tipe == 'kunjungan') ? 'selected' : '' ?>>Detail Kunjungan Pasien</option>
                    <option value="rekap_poli" <?= ($tipe == 'rekap_poli') ? 'selected' : '' ?>>Rekap Pasien per Poliklinik</option>
                    <option value="rekap_asuransi" <?= ($tipe == 'rekap_asuransi') ? 'selected' : '' ?>>Rekap Pasien per Penjamin/Asuransi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Filter Poliklinik</label>
                <select name="id_poliklinik" class="form-select">
                    <option value="">Semua Poliklinik</option>
                    <?php foreach ($daftarPoli as $p): ?>
                        <option value="<?= $p['id_poliklinik'] ?>" <?= ($id_poli == $p['id_poliklinik']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama_poli']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Tampilan Hasil Laporan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-table text-primary me-2"></i>Hasil Laporan: <?= strtoupper(str_replace('_', ' ', $tipe)) ?>
        </h5>
        <span class="badge bg-light text-dark border">Periode: <?= $tgl_mulai ?> s/d <?= $tgl_selesai ?></span>
    </div>
    <div class="card-body p-0">
        <?php if ($tipe === 'kunjungan'): ?>
            <!-- Tabel Detail Kunjungan Pasien -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>No. Reg</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Poli & Dokter</th>
                            <th>Penjamin</th>
                            <th>Keluhan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $items = $dataLaporan['items'] ?? [];
                        if (empty($items)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi pada periode ini.</td></tr>
                        <?php else: foreach ($items as $row): ?>
                            <tr>
                                <td class="ps-3"><?= date('d/m/Y', strtotime($row['tanggal_kunjungan'])) ?></td>
                                <td><code><?= htmlspecialchars($row['no_registrasi']) ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['no_rkm_medis']) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                <td><?= htmlspecialchars($row['nama_poli']) ?><br><small class="text-muted"><?= htmlspecialchars($row['nama_dokter']) ?></small></td>
                                <td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($row['nama_asuransi']) ?></span></td>
                                <td><small><?= htmlspecialchars($row['keluhan_awal'] ?: '-') ?></small></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status_pelayanan']) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tipe === 'rekap_poli'): ?>
            <!-- Rekapitulasi per Poliklinik -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Kode Poli</th>
                            <th>Nama Poliklinik</th>
                            <th>Jumlah Kunjungan Pasien</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($dataLaporan ?? []) as $row): ?>
                            <tr>
                                <td class="ps-4"><code><?= htmlspecialchars($row['kode_poli']) ?></code></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_poli']) ?></td>
                                <td><span class="badge bg-primary fs-6 px-3"><?= $row['jumlah_pasien'] ?> Pasien</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tipe === 'rekap_asuransi'): ?>
            <!-- Rekapitulasi per Asuransi -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Kategori Penjamin</th>
                            <th>Nama Asuransi / Rekanan</th>
                            <th>Jumlah Pasien Terlayani</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($dataLaporan ?? []) as $row): ?>
                            <tr>
                                <td class="ps-4"><span class="badge bg-secondary"><?= htmlspecialchars($row['tipe_penjamin']) ?></span></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_asuransi']) ?></td>
                                <td><span class="badge bg-success fs-6 px-3"><?= $row['jumlah_pasien'] ?> Pasien</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
