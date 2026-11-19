<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD TABEL ASOSIATIF 1 (DOKTER-POLI)
// Endpoint: simrssoa/api/dokter_poli.php
// Method: GET, POST, PUT, DELETE
// ========================================================

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = "SELECT dp.*, d.nama_dokter, d.spesialisasi, p.nama_poli, p.kode_poli 
                  FROM dokter_poliklinik dp
                  JOIN dokter d ON dp.id_dokter = d.id_dokter
                  JOIN poliklinik p ON dp.id_poliklinik = p.id_poliklinik";
        
        $params = [];
        $conditions = [];

        if (isset($_GET['id_poliklinik'])) {
            $conditions[] = "dp.id_poliklinik = ?";
            $params[] = $_GET['id_poliklinik'];
        }
        if (isset($_GET['id_dokter'])) {
            $conditions[] = "dp.id_dokter = ?";
            $params[] = $_GET['id_dokter'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " ORDER BY p.nama_poli ASC, dp.hari_praktik ASC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        sendResponse("success", 200, "Jadwal dokter-poliklinik", $stmt->fetchAll());
        break;

    case 'POST':
        $input = getJsonInput();
        if (empty($input['id_dokter']) || empty($input['id_poliklinik']) || empty($input['hari_praktik']) || empty($input['jam_mulai']) || empty($input['jam_selesai'])) {
            sendResponse("error", 400, "id_dokter, id_poliklinik, hari_praktik, jam_mulai, dan jam_selesai wajib diisi");
        }

        try {
            $stmt = $db->prepare("INSERT INTO dokter_poliklinik 
                (id_dokter, id_poliklinik, hari_praktik, jam_mulai, jam_selesai, kuota_pasien) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['id_dokter'],
                $input['id_poliklinik'],
                $input['hari_praktik'],
                $input['jam_mulai'],
                $input['jam_selesai'],
                $input['kuota_pasien'] ?? 30
            ]);
            sendResponse("success", 201, "Jadwal praktik berhasil ditambahkan", ["id" => $db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menambahkan jadwal: " . $e->getMessage());
        }
        break;

    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_dokter_poli']) || empty($input['hari_praktik']) || empty($input['jam_mulai']) || empty($input['jam_selesai'])) {
            sendResponse("error", 400, "id_dokter_poli, hari_praktik, jam_mulai, dan jam_selesai wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE dokter_poliklinik SET 
                hari_praktik = ?, 
                jam_mulai = ?, 
                jam_selesai = ?, 
                kuota_pasien = ? 
                WHERE id_dokter_poli = ?");
            $stmt->execute([
                $input['hari_praktik'],
                $input['jam_mulai'],
                $input['jam_selesai'],
                $input['kuota_pasien'] ?? 30,
                $input['id_dokter_poli']
            ]);
            sendResponse("success", 200, "Jadwal praktik berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui jadwal: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_dokter_poli'] ?? $_GET['id'] ?? null;
        if (!$id) sendResponse("error", 400, "id_dokter_poli wajib disertakan");

        try {
            $stmt = $db->prepare("DELETE FROM dokter_poliklinik WHERE id_dokter_poli = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Jadwal dokter-poli berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus jadwal: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
