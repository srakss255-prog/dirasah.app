<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$lat = $input['latitude'] ?? null;
$lon = $input['longitude'] ?? null;
$nama_lokasi = trim($input['nama_lokasi'] ?? '');

if ($lat !== null && $lon !== null) {
    require_once __DIR__ . '/../config/db.php';
    
    $lokasi_str = $nama_lokasi ? "{$nama_lokasi} ({$lat}, {$lon})" : "{$lat}, {$lon}";
    
    $stmt = $pdo->prepare("UPDATE users SET lokasi = ? WHERE id = ?");
    $stmt->execute([$lokasi_str, $_SESSION['user_id']]);
    
    $_SESSION['user_lokasi'] = $lokasi_str;
    echo json_encode(['status' => 'success', 'lokasi' => $lokasi_str]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Koordinat tidak valid']);
