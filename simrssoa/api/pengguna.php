<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD PENGGUNA (USERS & RBAC)
// Endpoint: simrssoa/api/pengguna.php
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
            $stmt = $db->prepare("SELECT id_pengguna, username, nama_lengkap, peran, status_aktif, created_at FROM pengguna WHERE id_pengguna = ?");
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            $item ? sendResponse("success", 200, "Pengguna ditemukan", $item) : sendResponse("error", 404, "Pengguna tidak ditemukan");
        } else {
            $stmt = $db->query("SELECT id_pengguna, username, nama_lengkap, peran, status_aktif, created_at FROM pengguna ORDER BY id_pengguna ASC");
            sendResponse("success", 200, "Daftar pengguna", $stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['username']) || empty($input['password']) || empty($input['nama_lengkap']) || empty($input['peran'])) {
            sendResponse("error", 400, "username, password, nama_lengkap, dan peran wajib diisi");
        }

        try {
            $cek = $db->prepare("SELECT id_pengguna FROM pengguna WHERE username = ?");
            $cek->execute([$input['username']]);
            if ($cek->fetch()) {
                sendResponse("error", 409, "Username sudah digunakan!");
            }

            $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO pengguna (username, password_hash, nama_lengkap, peran, status_aktif) VALUES (?, ?, ?, ?, 'Y')");
            $stmt->execute([
                $input['username'],
                $password_hash,
                $input['nama_lengkap'],
                $input['peran']
            ]);

            sendResponse("success", 201, "Pengguna berhasil didaftarkan", ["id_pengguna" => $db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal membuat pengguna: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_pengguna']) || empty($input['nama_lengkap']) || empty($input['peran'])) {
            sendResponse("error", 400, "id_pengguna, nama_lengkap, dan peran wajib diisi");
        }

        try {
            if (!empty($input['password'])) {
                $hash = password_hash($input['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE pengguna SET nama_lengkap = ?, peran = ?, password_hash = ? WHERE id_pengguna = ?");
                $stmt->execute([$input['nama_lengkap'], $input['peran'], $hash, $input['id_pengguna']]);
            } else {
                $stmt = $db->prepare("UPDATE pengguna SET nama_lengkap = ?, peran = ? WHERE id_pengguna = ?");
                $stmt->execute([$input['nama_lengkap'], $input['peran'], $input['id_pengguna']]);
            }
            sendResponse("success", 200, "Data pengguna berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui pengguna: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_pengguna'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_pengguna wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Pengguna berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus pengguna: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
