<?php
// ========================================================
// SERVER A (appsimrs): APP CONFIGURATION
// ========================================================

// Otomatis mendeteksi Host & Port aktif (misal localhost, localhost:8080, atau IP LAN)
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// URL Endpoint Web Service Server B
// Secara default akan mengarah ke http://[host]/simrssoa/api
if (!defined('API_BASE_URL')) {
    // Jika 2 server fisik berbeda, Anda bisa ganti langsung dengan IP Server B:
    // define('API_BASE_URL', 'http://192.168.1.20/simrssoa/api');
    define('API_BASE_URL', 'http://' . $current_host . '/simrssoa/api');
}

define('APP_NAME', 'SIMRS SOA');
define('HOSPITAL_NAME', 'RS Sehat Sejahtera');
define('APP_VERSION', '2.0.0-SOA-RBAC');

// Zona waktu standar
date_default_timezone_set('Asia/Jakarta');
