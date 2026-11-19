<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD TABEL ASOSIATIF 2 (PENDAFTARAN TRANSAKSI)
// Endpoint: simrssoa/api/pendaftaran.php
// Method: GET, POST, PUT, DELETE
// ========================================================

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        
        $sql = "SELECT pd.*, 
                       ps.no_rkm_medis, ps.nama_pasien, ps.nik, ps.jenis_kelamin, ps.tanggal_lahir,
                       pl.nama_poli, pl.kode_poli,
                       d.nama_dokter, d.spesialisasi,
                       a.nama_asuransi, a.tipe_penjamin,
                       u.nama_lengkap AS nama_petugas
                FROM pendaftaran pd
                JOIN pasien ps ON pd.id_pasien = ps.id_pasien
                JOIN poliklinik pl ON pd.id_poliklinik = pl.id_poliklinik
                JOIN dokter d ON pd.id_dokter = d.id_dokter
                JOIN asuransi a ON pd.id_asuransi = a.id_asuransi
                JOIN pengguna u ON pd.id_petugas = u.id_pengguna";

        if (isset($_GET['id'])) {
            $sql .= " WHERE pd.id_pendaftaran = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            $item ? sendResponse("success", 200, "Data pendaftaran ditemukan", $item) : sendResponse("error", 404, "Data tidak ditemukan");
        } elseif (isset($_GET['all']) && $_GET['all'] == 'true') {
            $sql .= " ORDER BY pd.id_pendaftaran DESC LIMIT 100";
            $stmt = $db->query($sql);
            sendResponse("success", 200, "Daftar 100 pendaftaran terakhir", $stmt->fetchAll());
        } else {
            $sql .= " WHERE pd.tanggal_kunjungan = ? ORDER BY pd.nomor_antrean ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tanggal]);
            sendResponse("success", 200, "Daftar antrean hari ini ($tanggal)", $stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['id_pasien']) || empty($input['id_poliklinik']) || empty($input['id_dokter']) || empty($input['id_asuransi'])) {
            sendResponse("error", 400, "id_pasien, id_poliklinik, id_dokter, dan id_asuransi wajib diisi");
        }

        $tgl_kunjungan = !empty($input['tanggal_kunjungan']) ? $input['tanggal_kunjungan'] : date('Y-m-d');
        $id_petugas    = !empty($input['id_petugas']) ? $input['id_petugas'] : 1;

        try {
            // Hitung nomor antrean berikutnya per poli & dokter
            $qAntrean = $db->prepare("SELECT MAX(nomor_antrean) AS max_antrean FROM pendaftaran WHERE tanggal_kunjungan = ? AND id_poliklinik = ? AND id_dokter = ?");
            $qAntrean->execute([$tgl_kunjungan, $input['id_poliklinik'], $input['id_dokter']]);
            $antreanRow = $qAntrean->fetch();
            $nomor_antrean = ($antreanRow['max_antrean'] ? (int)$antreanRow['max_antrean'] : 0) + 1;

            // Generate nomor registrasi (REG-YYYYMMDD-XXXX)
            $dateClean = str_replace('-', '', $tgl_kunjungan);
            $qCount = $db->prepare("SELECT COUNT(*) AS total FROM pendaftaran WHERE tanggal_kunjungan = ?");
            $qCount->execute([$tgl_kunjungan]);
            $totalHariIni = $qCount->fetch()['total'] + 1;
            $no_reg = "REG-" . $dateClean . "-" . str_pad($totalHariIni, 4, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("INSERT INTO pendaftaran 
                (no_registrasi, tanggal_kunjungan, id_pasien, id_poliklinik, id_dokter, id_asuransi, id_petugas, nomor_antrean, status_pelayanan, keluhan_awal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu', ?)");

            $stmt->execute([
                $no_reg,
                $tgl_kunjungan,
                $input['id_pasien'],
                $input['id_poliklinik'],
                $input['id_dokter'],
                $input['id_asuransi'],
                $id_petugas,
                $nomor_antrean,
                $input['keluhan_awal'] ?? ''
            ]);

            sendResponse("success", 201, "Pendaftaran berobat berhasil disimpan", [
                "id_pendaftaran"    => $db->lastInsertId(),
                "no_registrasi"     => $no_reg,
                "nomor_antrean"     => $nomor_antrean,
                "tanggal_kunjungan" => $tgl_kunjungan
            ]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memproses pendaftaran: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_pendaftaran']) || empty($input['status_pelayanan'])) {
            sendResponse("error", 400, "id_pendaftaran dan status_pelayanan wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE pendaftaran SET status_pelayanan = ? WHERE id_pendaftaran = ?");
            $stmt->execute([$input['status_pelayanan'], $input['id_pendaftaran']]);
            sendResponse("success", 200, "Status pelayanan pendaftaran berhasil diupdate");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal mengupdate status: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_pendaftaran'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_pendaftaran wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM pendaftaran WHERE id_pendaftaran = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Pendaftaran berhasil dibatalkan / dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus pendaftaran: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
