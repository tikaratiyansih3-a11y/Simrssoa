<?php
require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", 405, "Method Not Allowed");
}

try {
    $totalPasien     = (int)$db->query("SELECT COUNT(*) FROM pasien")->fetchColumn();
    $totalDokter     = (int)$db->query("SELECT COUNT(*) FROM dokter WHERE status_aktif = 'Y'")->fetchColumn();
    $totalPoli       = (int)$db->query("SELECT COUNT(*) FROM poliklinik WHERE status_aktif = 'Y'")->fetchColumn();
    $totalAsuransi   = (int)$db->query("SELECT COUNT(*) FROM asuransi WHERE status_aktif = 'Y'")->fetchColumn();
    
    $today = date('Y-m-d');
    $stmtToday = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE tanggal_kunjungan = ?");
    $stmtToday->execute([$today]);
    $kunjunganHariIni = (int)$stmtToday->fetchColumn();

    $data = [
        "total_pasien"         => $totalPasien,
        "total_dokter"         => $totalDokter,
        "total_poliklinik"     => $totalPoli,
        "total_asuransi"       => $totalAsuransi,
        "kunjungan_hari_ini"   => $kunjunganHariIni,
        "tanggal_server"       => $today
    ];

    sendResponse("success", 200, "Statistik SIMRS", $data);
} catch (PDOException $e) {
    sendResponse("error", 500, "Gagal mengambil statistik: " . $e->getMessage());
}
