<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/helpers/auth_guard.php";

// RBAC Guard: Hanya Admin dan Staf Pendaftaran yang boleh memproses pendaftaran pasien
require_role(['admin', 'pendaftaran']);

$message = null;
$messageType = null;
$lastRegistration = null;

// Proses submit pendaftaran ke Server B via POST /api/pendaftaran.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'daftar') {
    $payload = [
        "id_pasien"         => (int)$_POST['id_pasien'],
        "id_poliklinik"     => (int)$_POST['id_poliklinik'],
        "id_dokter"         => (int)$_POST['id_dokter'],
        "id_asuransi"       => (int)$_POST['id_asuransi'],
        "tanggal_kunjungan" => $_POST['tanggal_kunjungan'] ?? date('Y-m-d'),
        "keluhan_awal"      => trim($_POST['keluhan_awal']),
        "id_petugas"        => 1 // Default petugas login
    ];

    $res = call_api('pendaftaran.php', 'POST', $payload);
    if ($res['status'] === 'success') {
        $message = "Pendaftaran Berhasil! Nomor Registrasi: <strong>" . htmlspecialchars($res['data']['no_registrasi']) . "</strong> | No. Antrean: <strong>" . $res['data']['nomor_antrean'] . "</strong>";
        $messageType = "success";
        $lastRegistration = $res['data'];
    } else {
        $message = "Gagal memproses pendaftaran: " . htmlspecialchars($res['message']);
        $messageType = "danger";
    }
}

// Ambil data-data pendukung dari Server B
$resPasien = call_api('pasien.php');
$daftarPasien = ($resPasien['status'] === 'success') ? $resPasien['data'] : [];

$resPoli = call_api('poliklinik.php');
$daftarPoli = ($resPoli['status'] === 'success') ? $resPoli['data'] : [];

$resDokter = call_api('dokter.php');
$daftarDokter = ($resDokter['status'] === 'success') ? $resDokter['data'] : [];

$resAsuransi = call_api('asuransi.php');
$daftarAsuransi = ($resAsuransi['status'] === 'success') ? $resAsuransi['data'] : [];

// Ambil data pendaftaran hari ini
$resList = call_api('pendaftaran.php', 'GET', ['tanggal' => date('Y-m-d')]);
$daftarKunjungan = ($resList['status'] === 'success') ? $resList['data'] : [];

require_once __DIR__ . "/includes/header.php";
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="bi bi-clipboard-pulse text-primary me-2"></i>Pendaftaran Pasien Berobat (Rawat Jalan)</h2>
    <p class="text-secondary mb-0">Formulir pendaftaran layanan poliklinik terintegrasi langsung dengan Web Service Server B.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Kolom Kiri: Form Pendaftaran -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>Form Registrasi Kunjungan
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="daftar">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Pasien Terdaftar <span class="text-danger">*</span></label>
                        <select name="id_pasien" class="form-select" required>
                            <option value="">-- Pilih Rekam Medis Pasien --</option>
                            <?php foreach ($daftarPasien as $p): ?>
                                <option value="<?= $p['id_pasien'] ?>">
                                    [<?= htmlspecialchars($p['no_rkm_medis']) ?>] <?= htmlspecialchars($p['nama_pasien']) ?> (NIK: <?= htmlspecialchars($p['nik']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pasien baru belum punya RM? <a href="pasien.php" class="text-decoration-none">Daftarkan di sini</a></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Berobat <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kunjungan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Poliklinik Tujuan <span class="text-danger">*</span></label>
                        <select name="id_poliklinik" class="form-select" required>
                            <option value="">-- Pilih Poliklinik --</option>
                            <?php foreach ($daftarPoli as $poli): ?>
                                <option value="<?= $poli['id_poliklinik'] ?>">
                                    <?= htmlspecialchars($poli['nama_poli']) ?> (<?= htmlspecialchars($poli['gedung'] ?: '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dokter Pemeriksa <span class="text-danger">*</span></label>
                        <select name="id_dokter" class="form-select" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach ($daftarDokter as $doc): ?>
                                <option value="<?= $doc['id_dokter'] ?>">
                                    <?= htmlspecialchars($doc['nama_dokter']) ?> - <?= htmlspecialchars($doc['spesialisasi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Penjamin / Asuransi <span class="text-danger">*</span></label>
                        <select name="id_asuransi" class="form-select" required>
                            <option value="">-- Pilih Penjamin --</option>
                            <?php foreach ($daftarAsuransi as $asr): ?>
                                <option value="<?= $asr['id_asuransi'] ?>">
                                    <?= htmlspecialchars($asr['nama_asuransi']) ?> (<?= $asr['tipe_penjamin'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keluhan Awal / Catatan</label>
                        <textarea name="keluhan_awal" class="form-control" rows="2" placeholder="Tuliskan keluhan atau rujukan jika ada..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-printer-fill me-1"></i> Cetak Antrean & Kirim ke Server B
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Antrean Hari Ini -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-ticket-perforated text-success me-2"></i>Tiket & Antrean Hari Ini
                </h5>
                <span class="badge bg-primary"><?= count($daftarKunjungan) ?> Terdaftar</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Antrean</th>
                                <th>No. Reg</th>
                                <th>Pasien</th>
                                <th>Poli & Dokter</th>
                                <th>Penjamin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftarKunjungan)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-secondary">
                                        Belum ada antrean berobat untuk hari ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftarKunjungan as $k): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-dark fs-6 px-3 py-2">
                                                A-<?= str_pad($k['nomor_antrean'], 2, '0', STR_PAD_LEFT) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($k['no_registrasi']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($k['no_rkm_medis']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($k['nama_pasien']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($k['keluhan_awal'] ?: 'Tidak ada keluhan tertulis') ?></small>
                                        </td>
                                        <td>
                                            <div class="text-primary fw-semibold"><?= htmlspecialchars($k['nama_poli']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($k['nama_dokter']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($k['tipe_penjamin'] == 'BPJS') ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($k['nama_asuransi']) ?>
                                            </span>
                                        </td>
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
