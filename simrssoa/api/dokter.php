<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD DOKTER
// Endpoint: simrssoa/api/dokter.php
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
            $stmt = $db->prepare("SELECT * FROM dokter WHERE id_dokter = ?");
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            $item ? sendResponse("success", 200, "Dokter ditemukan", $item) : sendResponse("error", 404, "Dokter tidak ditemukan");
        } else {
            $stmt = $db->query("SELECT * FROM dokter WHERE status_aktif = 'Y' ORDER BY nama_dokter ASC");
            sendResponse("success", 200, "Daftar dokter", $stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['kode_dokter']) || empty($input['nama_dokter']) || empty($input['spesialisasi']) || empty($input['nomor_sip'])) {
            sendResponse("error", 400, "kode_dokter, nama_dokter, spesialisasi, dan nomor_sip wajib diisi");
        }

        try {
            $stmt = $db->prepare("INSERT INTO dokter (kode_dokter, nama_dokter, spesialisasi, nomor_sip, no_telepon, status_aktif) VALUES (?, ?, ?, ?, ?, 'Y')");
            $stmt->execute([
                $input['kode_dokter'],
                $input['nama_dokter'],
                $input['spesialisasi'],
                $input['nomor_sip'],
                $input['no_telepon'] ?? null
            ]);
            sendResponse("success", 201, "Dokter berhasil ditambahkan", ["id_dokter" => $db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menyimpan dokter: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_dokter']) || empty($input['nama_dokter']) || empty($input['spesialisasi'])) {
            sendResponse("error", 400, "id_dokter, nama_dokter, dan spesialisasi wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE dokter SET nama_dokter = ?, spesialisasi = ?, nomor_sip = ?, no_telepon = ? WHERE id_dokter = ?");
            $stmt->execute([
                $input['nama_dokter'],
                $input['spesialisasi'],
                $input['nomor_sip'] ?? '',
                $input['no_telepon'] ?? null,
                $input['id_dokter']
            ]);
            sendResponse("success", 200, "Data dokter berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui data dokter: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_dokter'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_dokter wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM dokter WHERE id_dokter = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Dokter berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus dokter: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
