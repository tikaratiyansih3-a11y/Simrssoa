<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD ASURANSI / PENJAMIN
// Endpoint: simrssoa/api/asuransi.php
// Method: GET, POST, PUT, DELETE
// ========================================================

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM asuransi WHERE id_asuransi = ?");
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            $item ? sendResponse("success", 200, "Asuransi ditemukan", $item) : sendResponse("error", 404, "Asuransi tidak ditemukan");
        } else {
            $stmt = $db->query("SELECT * FROM asuransi WHERE status_aktif = 'Y' ORDER BY nama_asuransi ASC");
            sendResponse("success", 200, "Daftar penjamin / asuransi", $stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['kode_asuransi']) || empty($input['nama_asuransi'])) {
            sendResponse("error", 400, "kode_asuransi dan nama_asuransi wajib diisi");
        }

        try {
            $stmt = $db->prepare("INSERT INTO asuransi (kode_asuransi, nama_asuransi, tipe_penjamin, status_aktif) VALUES (?, ?, ?, 'Y')");
            $stmt->execute([$input['kode_asuransi'], $input['nama_asuransi'], $input['tipe_penjamin'] ?? 'UMUM']);
            sendResponse("success", 201, "Asuransi berhasil ditambahkan", ["id_asuransi" => $db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menyimpan asuransi: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_asuransi']) || empty($input['nama_asuransi'])) {
            sendResponse("error", 400, "id_asuransi dan nama_asuransi wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE asuransi SET nama_asuransi = ?, tipe_penjamin = ? WHERE id_asuransi = ?");
            $stmt->execute([$input['nama_asuransi'], $input['tipe_penjamin'] ?? 'UMUM', $input['id_asuransi']]);
            sendResponse("success", 200, "Data asuransi berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui asuransi: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_asuransi'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_asuransi wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM asuransi WHERE id_asuransi = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Asuransi berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus asuransi: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
