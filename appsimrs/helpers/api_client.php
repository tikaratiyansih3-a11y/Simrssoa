<?php
// ========================================================
// SERVER A: API CONSUMER HELPER (SOA CLIENT)
// ========================================================

require_once __DIR__ . "/../config/app.php";

/**
 * Fungsi untuk memanggil REST API Web Service di Server B
 *
 * @param string $endpoint Nama endpoint (misal: 'pasien.php', 'auth.php')
 * @param string $method GET, POST, PUT, DELETE
 * @param array $payload Data yang dikirim
 * @return array Hasil respon dari Server B
 */
function call_api($endpoint, $method = 'GET', $payload = []) {
    $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    $method = strtoupper($method);

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

        $headers = [
            'Accept: application/json',
            'User-Agent: SIMRS-SOA-Client/2.0'
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $jsonPayload = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            $headers[] = 'Content-Type: application/json';
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            $jsonPayload = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            $headers[] = 'Content-Type: application/json';
        } elseif ($method === 'GET' && !empty($payload)) {
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $separator . http_build_query($payload);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                "status"  => "error",
                "code"    => 503,
                "message" => "Gagal terhubung ke Server B pada " . $url . ". Penyebab: " . $error . ". Pastikan folder 'simrssoa' ada di htdocs dan Apache aktif.",
                "data"    => null
            ];
        }

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            $preview = substr(strip_tags($response), 0, 180);
            return [
                "status"  => "error",
                "code"    => $httpCode,
                "message" => "Server B tidak merespon format JSON valid (HTTP Code $httpCode). Respons: " . ($preview ?: 'Kosong'),
                "data"    => null
            ];
        }

        return $decoded;
    }

    // Fallback jika ekstensi cURL PHP tidak terpasang
    $opts = [
        'http' => [
            'method'  => $method,
            'header'  => "Accept: application/json\r\nContent-Type: application/json\r\n",
            'timeout' => 12,
            'ignore_errors' => true
        ]
    ];
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $opts['http']['content'] = json_encode($payload);
    } elseif ($method === 'GET' && !empty($payload)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($payload);
    }

    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return [
            "status"  => "error",
            "code"    => 503,
            "message" => "Tidak dapat menghubungi Server B pada $url",
            "data"    => null
        ];
    }

    return json_decode($response, true) ?: [
        "status"  => "error",
        "code"    => 500,
        "message" => "Gagal memproses JSON respons dari Server B",
        "data"    => null
    ];
}
