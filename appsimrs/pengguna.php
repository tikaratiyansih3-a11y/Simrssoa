<?php
require_once __DIR__ . "/helpers/api_client.php";
require_once __DIR__ . "/includes/header.php";

// RBAC Guard: Hanya peran 'admin' yang boleh mengakses modul ini!
require_role('admin');

$message = null;
$messageType = null;

// Tambah user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $payload = [
        "username"     => trim($_POST['username']),
        "password"     => trim($_POST['password']),
        "nama_lengkap" => trim($_POST['nama_lengkap']),
        "peran"        => $_POST['peran']
    ];

    $res = call_api('pengguna.php', 'POST', $payload);
    if ($res['status'] === 'success') {
        $message = "Pengguna baru berhasil ditambahkan ke Server B!";
        $messageType = "success";
    } else {
        $message = "Gagal menambah pengguna: " . htmlspecialchars($res['message']);
        $messageType = "danger";
    }
}

// Ambil daftar pengguna dari Server B via Web Service
$resUsers = call_api('pengguna.php');
$daftarUsers = ($resUsers['status'] === 'success') ? $resUsers['data'] : [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Manajemen Pengguna & RBAC</h2>
        <p class="text-secondary mb-0">Pengaturan akun staf rumah sakit dan kontrol hak akses bertingkat.</p>
    </div>
    <div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna Baru
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabel Pengguna RBAC -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-2 text-primary"></i>Daftar Akun Pengguna Terdaftar</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Peran (Role RBAC)</th>
                        <th>Hak Akses Modul</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarUsers as $u): ?>
                        <tr>
                            <td class="ps-4"><?= $u['id_pengguna'] ?></td>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                            <td>
                                <?php if ($u['peran'] == 'admin'): ?>
                                    <span class="badge bg-danger text-uppercase"><i class="bi bi-shield-fill me-1"></i> ADMIN</span>
                                <?php elseif ($u['peran'] == 'dokter'): ?>
                                    <span class="badge bg-success text-uppercase"><i class="bi bi-heart-pulse-fill me-1"></i> DOKTER</span>
                                <?php else: ?>
                                    <span class="badge bg-primary text-uppercase"><i class="bi bi-person-badge-fill me-1"></i> PENDAFTARAN</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php if ($u['peran'] == 'admin'): ?>
                                        Semua Modul (Pasien, Poli, Dokter, Asuransi, Antrean, RBAC)
                                    <?php elseif ($u['peran'] == 'dokter'): ?>
                                        Dashboard, Antrean Pasien Poli Saya, Data Dokter & Jadwal
                                    <?php else: ?>
                                        Dashboard, Rekam Medis Pasien, Pendaftaran Rawat Jalan, Cetak Antrean
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-success">Aktif</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Akun Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: dokter_andi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: dr. Andi Wijaya, Sp.B" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peran (Role RBAC) <span class="text-danger">*</span></label>
                        <select name="peran" class="form-select" required>
                            <option value="pendaftaran">Pendaftaran (Staf Loket)</option>
                            <option value="dokter">Dokter (Tenaga Medis)</option>
                            <option value="admin">Administrator (Akses Penuh)</option>
                            <option value="perawat">Perawat</option>
                            <option value="kasir">Kasir Pembayaran</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan ke Server B</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
