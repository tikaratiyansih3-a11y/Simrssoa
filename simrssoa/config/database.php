<?php
// ========================================================
// SERVER B: DATABASE CONFIGURATION (simrssoa)
// ========================================================

class Database {
    private $host = "localhost";
    private $db_name = "simrssoa";
    private $username = "root";
    private $password = "";
    private $port = 3306;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            http_response_code(500);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode([
                "status"  => "error",
                "code"    => 500,
                "message" => "Database connection error pada Server B: " . $exception->getMessage() . ". Pastikan database 'simrssoa' sudah diimport di phpMyAdmin!"
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
        return $this->conn;
    }
}
