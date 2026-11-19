<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD POLIKLINIK
// Endpoint: simrssoa/api/poliklinik.php
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
            $stmt = $db->prepare("SELECT * FROM poliklinik WHERE id_poliklinik = ?");
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            $item ? sendResponse("success", 200, "Poliklinik ditemukan", $item) : sendResponse("error", 404, "Poliklinik tidak ditemukan");
        } else {
            $stmt = $db->query("SELECT * FROM poliklinik WHERE status_aktif = 'Y' ORDER BY nama_poli ASC");
            sendResponse("success", 200, "Daftar poliklinik", $stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['kode_poli']) || empty($input['nama_poli'])) {
            sendResponse("error", 400, "kode_poli dan nama_poli wajib diisi");
        }

        try {
            $stmt = $db->prepare("INSERT INTO poliklinik (kode_poli, nama_poli, gedung, status_aktif) VALUES (?, ?, ?, 'Y')");
            $stmt->execute([$input['kode_poli'], $input['nama_poli'], $input['gedung'] ?? null]);
            sendResponse("success", 201, "Poliklinik berhasil ditambahkan", ["id_poliklinik" => $db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menyimpan poliklinik: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_poliklinik']) || empty($input['nama_poli'])) {
            sendResponse("error", 400, "id_poliklinik dan nama_poli wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE poliklinik SET nama_poli = ?, gedung = ? WHERE id_poliklinik = ?");
            $stmt->execute([$input['nama_poli'], $input['gedung'] ?? null, $input['id_poliklinik']]);
            sendResponse("success", 200, "Data poliklinik berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui poliklinik: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_poliklinik'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_poliklinik wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM poliklinik WHERE id_poliklinik = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Poliklinik berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus poliklinik: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
