<?php
// ========================================================
// SERVER B: WEB SERVICE LAPORAN & REKAPITULASI SIMRS
// Endpoint: simrssoa/api/laporan.php
// Method: GET
// ========================================================

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", 405, "Method Not Allowed");
}

$jenis_laporan = $_GET['tipe'] ?? 'kunjungan';
$tgl_mulai     = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai   = $_GET['tgl_selesai'] ?? date('Y-m-d');
$id_poli       = !empty($_GET['id_poliklinik']) ? (int)$_GET['id_poliklinik'] : null;

try {
    switch ($jenis_laporan) {
        // 1. Laporan Detail Kunjungan Pasien
        case 'kunjungan':
            $sql = "SELECT pd.id_pendaftaran, pd.no_registrasi, pd.tanggal_kunjungan, pd.nomor_antrean, pd.status_pelayanan, pd.keluhan_awal,
                           ps.no_rkm_medis, ps.nik, ps.nama_pasien, ps.jenis_kelamin, ps.tanggal_lahir,
                           pl.nama_poli, pl.kode_poli,
                           d.nama_dokter, d.spesialisasi,
                           a.nama_asuransi, a.tipe_penjamin,
                           u.nama_lengkap AS petugas_loket
                    FROM pendaftaran pd
                    JOIN pasien ps ON pd.id_pasien = ps.id_pasien
                    JOIN poliklinik pl ON pd.id_poliklinik = pl.id_poliklinik
                    JOIN dokter d ON pd.id_dokter = d.id_dokter
                    JOIN asuransi a ON pd.id_asuransi = a.id_asuransi
                    JOIN pengguna u ON pd.id_petugas = u.id_pengguna
                    WHERE pd.tanggal_kunjungan BETWEEN ? AND ?";
            
            $params = [$tgl_mulai, $tgl_selesai];
            if ($id_poli) {
                $sql .= " AND pd.id_poliklinik = ?";
                $params[] = $id_poli;
            }
            $sql .= " ORDER BY pd.tanggal_kunjungan DESC, pd.nomor_antrean ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            sendResponse("success", 200, "Laporan kunjungan pasien periode $tgl_mulai s/d $tgl_selesai", [
                "periode"   => ["mulai" => $tgl_mulai, "selesai" => $tgl_selesai],
                "total"     => count($data),
                "items"     => $data
            ]);
            break;

        // 2. Laporan Rekapitulasi Pasien Per Poliklinik
        case 'rekap_poli':
            $sql = "SELECT pl.id_poliklinik, pl.kode_poli, pl.nama_poli, COUNT(pd.id_pendaftaran) AS jumlah_pasien
                    FROM poliklinik pl
                    LEFT JOIN pendaftaran pd ON pl.id_poliklinik = pd.id_poliklinik AND pd.tanggal_kunjungan BETWEEN ? AND ?
                    GROUP BY pl.id_poliklinik
                    ORDER BY jumlah_pasien DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([$tgl_mulai, $tgl_selesai]);
            sendResponse("success", 200, "Rekapitulasi kunjungan per poliklinik", $stmt->fetchAll());
            break;

        // 3. Laporan Rekapitulasi Berdasarkan Jenis Penjamin / Asuransi
        case 'rekap_asuransi':
            $sql = "SELECT a.tipe_penjamin, a.nama_asuransi, COUNT(pd.id_pendaftaran) AS jumlah_pasien
                    FROM asuransi a
                    LEFT JOIN pendaftaran pd ON a.id_asuransi = pd.id_asuransi AND pd.tanggal_kunjungan BETWEEN ? AND ?
                    GROUP BY a.id_asuransi
                    ORDER BY jumlah_pasien DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([$tgl_mulai, $tgl_selesai]);
            sendResponse("success", 200, "Rekapitulasi penjamin pasien", $stmt->fetchAll());
            break;

        default:
            sendResponse("error", 400, "Tipe laporan tidak valid. Pilihan: 'kunjungan', 'rekap_poli', 'rekap_asuransi'");
            break;
    }
} catch (PDOException $e) {
    sendResponse("error", 500, "Gagal mengambil data laporan: " . $e->getMessage());
}
