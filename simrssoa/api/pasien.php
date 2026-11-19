<?php
// ========================================================
// SERVER B: WEB SERVICE CRUD PASIEN (REKAM MEDIS)
// Endpoint: simrssoa/api/pasien.php
// Method: GET, POST, PUT, DELETE
// ========================================================

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/../config/database.php";

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ----------------------------------------------------
    // READ (Ambil Semua / Ambil Per ID / Pencarian)
    // ----------------------------------------------------
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT p.*, a.nama_asuransi, a.tipe_penjamin 
                                  FROM pasien p 
                                  LEFT JOIN asuransi a ON p.id_asuransi_utama = a.id_asuransi 
                                  WHERE p.id_pasien = ?");
            $stmt->execute([$_GET['id']]);
            $item = $stmt->fetch();
            if ($item) {
                sendResponse("success", 200, "Data pasien ditemukan", $item);
            } else {
                sendResponse("error", 404, "Pasien tidak ditemukan");
            }
        } else {
            $search = $_GET['q'] ?? '';
            if (!empty($search)) {
                $term = "%$search%";
                $stmt = $db->prepare("SELECT p.*, a.nama_asuransi 
                                      FROM pasien p 
                                      LEFT JOIN asuransi a ON p.id_asuransi_utama = a.id_asuransi 
                                      WHERE p.nama_pasien LIKE ? OR p.no_rkm_medis LIKE ? OR p.nik LIKE ?
                                      ORDER BY p.id_pasien DESC");
                $stmt->execute([$term, $term, $term]);
            } else {
                $stmt = $db->query("SELECT p.*, a.nama_asuransi 
                                    FROM pasien p 
                                    LEFT JOIN asuransi a ON p.id_asuransi_utama = a.id_asuransi 
                                    ORDER BY p.id_pasien DESC");
            }
            sendResponse("success", 200, "Daftar pasien", $stmt->fetchAll());
        }
        break;

    // ----------------------------------------------------
    // CREATE (Tambah Pasien Baru)
    // ----------------------------------------------------
    case 'POST':
        $input = getJsonInput();
        if (empty($input['nik']) || empty($input['nama_pasien']) || empty($input['jenis_kelamin']) || empty($input['tanggal_lahir']) || empty($input['alamat'])) {
            sendResponse("error", 400, "NIK, nama_pasien, jenis_kelamin, tanggal_lahir, dan alamat wajib diisi");
        }

        try {
            $cekNik = $db->prepare("SELECT id_pasien FROM pasien WHERE nik = ?");
            $cekNik->execute([$input['nik']]);
            if ($cekNik->fetch()) {
                sendResponse("error", 409, "Pasien dengan NIK tersebut sudah terdaftar!");
            }

            // Generate otomatis No. Rekam Medis (RM-xxxxxx)
            $getLast = $db->query("SELECT id_pasien FROM pasien ORDER BY id_pasien DESC LIMIT 1")->fetch();
            $nextId = ($getLast ? $getLast['id_pasien'] : 0) + 1;
            $no_rm = $input['no_rkm_medis'] ?? ('RM-' . str_pad($nextId, 6, '0', STR_PAD_LEFT));

            $stmt = $db->prepare("INSERT INTO pasien 
                (no_rkm_medis, nik, nama_pasien, jenis_kelamin, tanggal_lahir, alamat, no_telepon, id_asuransi_utama, no_kartu_asuransi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $no_rm,
                $input['nik'],
                $input['nama_pasien'],
                $input['jenis_kelamin'],
                $input['tanggal_lahir'],
                $input['alamat'],
                $input['no_telepon'] ?? null,
                !empty($input['id_asuransi_utama']) ? $input['id_asuransi_utama'] : null,
                $input['no_kartu_asuransi'] ?? null
            ]);

            sendResponse("success", 201, "Pasien berhasil didaftarkan", [
                "id_pasien"    => $db->lastInsertId(),
                "no_rkm_medis" => $no_rm
            ]);
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menambahkan pasien: " . $e->getMessage());
        }
        break;

    // ----------------------------------------------------
    // UPDATE (Perbarui Data Pasien)
    // ----------------------------------------------------
    case 'PUT':
        $input = getJsonInput();
        if (empty($input['id_pasien']) || empty($input['nama_pasien']) || empty($input['alamat'])) {
            sendResponse("error", 400, "id_pasien, nama_pasien, dan alamat wajib diisi");
        }

        try {
            $stmt = $db->prepare("UPDATE pasien SET 
                nama_pasien = ?, 
                tanggal_lahir = ?, 
                alamat = ?, 
                no_telepon = ?, 
                id_asuransi_utama = ?, 
                no_kartu_asuransi = ? 
                WHERE id_pasien = ?");
            
            $stmt->execute([
                $input['nama_pasien'],
                $input['tanggal_lahir'],
                $input['alamat'],
                $input['no_telepon'] ?? null,
                !empty($input['id_asuransi_utama']) ? $input['id_asuransi_utama'] : null,
                $input['no_kartu_asuransi'] ?? null,
                $input['id_pasien']
            ]);

            sendResponse("success", 200, "Data pasien berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal memperbarui pasien: " . $e->getMessage());
        }
        break;

    // ----------------------------------------------------
    // DELETE (Hapus Data Pasien)
    // ----------------------------------------------------
    case 'DELETE':
        $input = getJsonInput();
        $id = $input['id_pasien'] ?? $_GET['id'] ?? null;
        if (!$id) {
            sendResponse("error", 400, "Parameter id atau id_pasien wajib disertakan");
        }

        try {
            $stmt = $db->prepare("DELETE FROM pasien WHERE id_pasien = ?");
            $stmt->execute([$id]);
            sendResponse("success", 200, "Data pasien berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse("error", 500, "Gagal menghapus data pasien: " . $e->getMessage());
        }
        break;

    default:
        sendResponse("error", 405, "Method Not Allowed");
        break;
}
