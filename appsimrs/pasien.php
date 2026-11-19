<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/helpers/auth_guard.php";

// RBAC Guard: Hanya Admin dan Staf Pendaftaran yang boleh mengelola rekam medis pasien
require_role(['admin', 'pendaftaran']);

$message = null;
$messageType = null;

// Proses Tambah Pasien Baru via POST ke Server B API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $payload = [
        "nik"               => trim($_POST['nik']),
        "nama_pasien"       => trim($_POST['nama_pasien']),
        "jenis_kelamin"     => $_POST['jenis_kelamin'],
        "tanggal_lahir"     => $_POST['tanggal_lahir'],
        "alamat"            => trim($_POST['alamat']),
        "no_telepon"        => trim($_POST['no_telepon']),
        "id_asuransi_utama" => !empty($_POST['id_asuransi_utama']) ? (int)$_POST['id_asuransi_utama'] : null,
        "no_kartu_asuransi" => trim($_POST['no_kartu_asuransi'])
    ];

    $res = call_api('pasien.php', 'POST', $payload);
    if ($res['status'] === 'success') {
        $message = "Pasien berhasil didaftarkan! No. Rekam Medis: <strong>" . htmlspecialchars($res['data']['no_rkm_medis']) . "</strong>";
        $messageType = "success";
    } else {
        $message = "Gagal menambah pasien: " . htmlspecialchars($res['message']);
        $messageType = "danger";
    }
}

// Ambil data pasien dari Server B API
$search = $_GET['q'] ?? '';
$params = !empty($search) ? ['q' => $search] : [];
$resPasien = call_api('pasien.php', 'GET', $params);
$daftarPasien = ($resPasien['status'] === 'success') ? $resPasien['data'] : [];

// Ambil data master asuransi untuk dropdown modal
$resAsuransi = call_api('asuransi.php');
$daftarAsuransi = ($resAsuransi['status'] === 'success') ? $resAsuransi['data'] : [];

require_once __DIR__ . "/includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>Data Pasien (Rekam Medis)</h2>
        <p class="text-secondary mb-0">Manajemen data master pasien melalui Web Service Server B.</p>
    </div>
    <div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPasien">
            <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Pasien Baru
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Form Pencarian -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama pasien, NIK, atau No. RM..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="pasien.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pasien -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No. RM</th>
                        <th>NIK</th>
                        <th>Nama Pasien</th>
                        <th>L/P</th>
                        <th>Tgl Lahir / Usia</th>
                        <th>No. Telp</th>
                        <th>Penjamin Utama</th>
                        <th>Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarPasien)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                Tidak ada data pasien ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarPasien as $p): 
                            $birthDate = new DateTime($p['tanggal_lahir']);
                            $today = new DateTime();
                            $age = $today->diff($birthDate)->y;
                        ?>
                            <tr>
                                <td class="ps-4"><span class="badge bg-primary fs-6"><?= htmlspecialchars($p['no_rkm_medis']) ?></span></td>
                                <td><code><?= htmlspecialchars($p['nik']) ?></code></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_pasien']) ?></td>
                                <td>
                                    <span class="badge <?= ($p['jenis_kelamin'] == 'L') ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' ?>">
                                        <?= $p['jenis_kelamin'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d-m-Y', strtotime($p['tanggal_lahir'])) ?> 
                                    <small class="text-muted">(<?= $age ?> thn)</small>
                                </td>
                                <td><?= htmlspecialchars($p['no_telepon'] ?: '-') ?></td>
                                <td>
                                    <?php if (!empty($p['nama_asuransi'])): ?>
                                        <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($p['nama_asuransi']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Umum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($p['alamat']) ?>">
                                    <?= htmlspecialchars($p['alamat']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pasien -->
<div class="modal fade" id="modalTambahPasien" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Pendaftaran Pasien Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span></label>
                            <input type="text" name="nik" class="form-control" maxlength="16" required placeholder="Contoh: 3201012304890001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pasien" class="form-control" required placeholder="Nama lengkap sesuai KTP">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. Telepon / WA</label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Penjamin / Asuransi Utama</label>
                            <select name="id_asuransi_utama" class="form-select">
                                <option value="">-- Pilih Asuransi --</option>
                                <?php foreach ($daftarAsuransi as $asr): ?>
                                    <option value="<?= $asr['id_asuransi'] ?>"><?= htmlspecialchars($asr['nama_asuransi']) ?> (<?= $asr['tipe_penjamin'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Kartu Asuransi / BPJS</label>
                            <input type="text" name="no_kartu_asuransi" class="form-control" placeholder="Kosongkan jika Umum">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan ke Server</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
