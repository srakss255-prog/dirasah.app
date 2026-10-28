<?php
require_once 'config/db.php';
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
session_set_cookie_params([
    "lifetime" => 2592000,
    "path" => "/",
    "domain" => ".dirasah.my.id",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Lax"
]);
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized: Silakan login terlebih dahulu.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$target_phone = $data['phone'] ?? '';
$custom_message = $data['message'] ?? '';
$prayer_name = $data['prayer'] ?? '';

// 1. Ambil Nama dan Lokasi Terkini Pengguna dari Database
$nama_user = $_SESSION['nama'] ?? 'Sahabat Dirasah';
$lokasi_user = 'Bekasi, West Java';

try {
    $qUser = $pdo->prepare("SELECT nama, username, lokasi, no_wa FROM users WHERE id = ?");
    $qUser->execute([$user_id]);
    $userRow = $qUser->fetch(PDO::FETCH_ASSOC);

    if ($userRow) {
        if (!empty($userRow['nama'])) {
            $nama_user = $userRow['nama'];
        } elseif (!empty($userRow['username'])) {
            $nama_user = $userRow['username'];
        }

        if (!empty($userRow['lokasi'])) {
            $lokasi_user = $userRow['lokasi'];
        }
    }
} catch (Exception $e) {}

// Bersihkan format nama lokasi (hapus koordinat kurung jika ada)
$nama_wilayah = preg_replace('/\s*\(.*?\)/', '', $lokasi_user);
$nama_wilayah = trim($nama_wilayah);
if (empty($nama_wilayah)) {
    $nama_wilayah = 'Bekasi dan sekitarnya';
}

// 2. Normalisasi Nomor WhatsApp ke Format 628xxx
$target_phone = preg_replace('/[^0-9]/', '', $target_phone);
if (str_starts_with($target_phone, '0')) {
    $target_phone = '62' . substr($target_phone, 1);
} elseif (!str_starts_with($target_phone, '62')) {
    $target_phone = '62' . $target_phone;
}

if (strlen($target_phone) < 9) {
    echo json_encode(['status' => false, 'message' => 'Nomor WhatsApp tidak valid.']);
    exit;
}

// Perbarui nomor WA aktif pengguna di database
try {
    $upd = $pdo->prepare("UPDATE users SET no_wa = ? WHERE id = ?");
    $upd->execute([$target_phone, $user_id]);
} catch (Exception $e) {}

// 3. Susun Format Pesan Pengingat Sholat
if (!empty($custom_message)) {
    $final_message = $custom_message;
} else {
    $sholat_label = !empty($prayer_name) ? strtoupper($prayer_name) : 'SHOLAT';
    
    $final_message = "*PENGINGAT WAKTU SHOLAT - DIRASAH.ID*\n\n"
                   . "Assalamu'alaikum *$nama_user*,\n"
                   . "Waktu sholat *$sholat_label* untuk wilayah *$nama_wilayah* telah tiba.\n\n"
                   . "Mari segera mengambil air wudhu dan menunaikan sholat tepat waktu.\n\n"
                   . "_\"Sesungguhnya sholat itu adalah kewajiban yang ditentukan waktunya atas orang-orang yang beriman.\" (QS. An-Nisa: 103)_";
}

// 4. Kirim Request ke Baileys Gateway via Localtunnel
$api_key = 'dirasah123';
$jid = $target_phone . '@s.whatsapp.net';

$payload = json_encode([
    'number'    => $target_phone,
    'recipient' => $target_phone,
    'receiver'  => $jid,
    'jid'       => $jid,
    'message'   => $final_message,
    'text'      => $final_message
]);

$headers = [
    'Content-Type: application/json',
    'Bypass-Tunnel-Reminder: true',
    'x-api-key: ' . $api_key,
    'Authorization: Bearer ' . $api_key
];

// Endpoint Localtunnel yang mengarah ke laptopmu
$url = 'http://172.17.0.1:8000/send-message';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Periksa respons JSON dari Baileys jika ada
$res_data = json_decode($response_body, true);

if ($http_code >= 200 && $http_code < 300 && (!isset($res_data['status']) || $res_data['status'] === true)) {
    echo json_encode([
        'status'  => true,
        'message' => "Pesan pengingat untuk wilayah $nama_wilayah berhasil dikirim ke nomor WhatsApp Anda!",
        'lokasi'  => $nama_wilayah
    ]);
} else {
    echo json_encode([
        'status'  => false,
        'message' => "Gagal kirim via Baileys (HTTP $http_code).",
        'debug'   => $response_body ?: $error
    ]);
}